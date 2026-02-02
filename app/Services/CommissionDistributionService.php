<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommissionLevel;
use App\Models\CryptoWallet;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CommissionDistributionService
{
    /**
     * Distribute ROI-based commissions to upline chain (up to 10 levels)
     * Called when a user earns daily ROI from their investment
     */
    public function distributeRoiCommissions(User $earner, float $roiAmount, string $description = ''): array
    {
        try {
            $distributedCommissions = [];
            
            DB::beginTransaction();

            $sponsorChain = $this->getSponsorChain($earner, 10);
            
            if (empty($sponsorChain)) {
                Log::info('No upline found for ROI commission distribution', [
                    'earner_id' => $earner->id,
                    'roi_amount' => $roiAmount
                ]);
                DB::commit();
                return $distributedCommissions;
            }

            $commissionLevels = ReferralCommissionLevel::where('is_active', true)
                ->orderBy('level')
                ->pluck('percentage', 'level')
                ->toArray();

            if (empty($commissionLevels)) {
                ReferralCommissionLevel::seedDefaults();
                $commissionLevels = ReferralCommissionLevel::where('is_active', true)
                    ->orderBy('level')
                    ->pluck('percentage', 'level')
                    ->toArray();
            }

            // Check if profit sharing shield is enabled
            $profitSharingShieldEnabled = Setting::getValue('profit_sharing_shield_enabled', false);
            $profitSharingShieldMinInvestment = (float) Setting::getValue('profit_sharing_shield_min_investment', 0);

            Log::info('Starting profit share distribution', [
                'earner_id' => $earner->id,
                'earner_name' => $earner->full_name,
                'roi_amount' => $roiAmount,
                'upline_count' => count($sponsorChain),
                'profit_sharing_shield_enabled' => $profitSharingShieldEnabled,
                'profit_sharing_shield_min_investment' => $profitSharingShieldMinInvestment
            ]);

            foreach ($sponsorChain as $levelIndex => $sponsorData) {
                $sponsor = $sponsorData['user'];
                $level = $levelIndex + 1;

                if ($sponsor->status !== 'active') {
                    Log::info('Skipping inactive sponsor', [
                        'sponsor_id' => $sponsor->id,
                        'level' => $level
                    ]);
                    continue;
                }

                // Skip sponsors with commission disabled (dummy users)
                if ($sponsor->commission_disabled) {
                    Log::info('Skipping sponsor with commission disabled', [
                        'sponsor_id' => $sponsor->id,
                        'level' => $level
                    ]);
                    continue;
                }

                // Check profit sharing shield requirement
                // User must have at least N direct referrals with combined investment >= minimum
                if ($profitSharingShieldEnabled) {
                    $shieldCheck = $this->checkProfitSharingShieldRequirement($sponsor, $level, $profitSharingShieldMinInvestment);
                    if (!$shieldCheck['passed']) {
                        Log::info('Skipping sponsor due to profit sharing shield requirement', [
                            'sponsor_id' => $sponsor->id,
                            'sponsor_name' => $sponsor->full_name,
                            'level' => $level,
                            'required_referrals' => $level,
                            'actual_referrals' => $shieldCheck['referral_count'],
                            'required_investment' => $profitSharingShieldMinInvestment,
                            'combined_investment' => $shieldCheck['combined_investment'],
                            'reason' => $shieldCheck['reason']
                        ]);
                        continue;
                    }
                }

                $percentage = $commissionLevels[$level] ?? 0;
                
                if ($percentage <= 0) {
                    continue;
                }

                $commissionAmount = round(($roiAmount * $percentage) / 100, 4);
                
                if ($commissionAmount <= 0) {
                    continue;
                }

                $success = $this->addCommissionToWallet(
                    $sponsor,
                    $commissionAmount,
                    $earner,
                    $level,
                    $roiAmount,
                    $description
                );

                if ($success) {
                    $distributedCommissions[] = [
                        'sponsor_id' => $sponsor->id,
                        'sponsor_name' => $sponsor->full_name,
                        'level' => $level,
                        'percentage' => $percentage,
                        'amount' => $commissionAmount
                    ];

                    Log::info('ROI commission distributed', [
                        'sponsor_id' => $sponsor->id,
                        'level' => $level,
                        'percentage' => $percentage,
                        'commission' => $commissionAmount,
                        'roi_amount' => $roiAmount
                    ]);
                }
            }

            DB::commit();

            Log::info('ROI commission distribution completed', [
                'earner_id' => $earner->id,
                'total_distributed' => count($distributedCommissions),
                'total_commission' => array_sum(array_column($distributedCommissions, 'amount'))
            ]);

            return $distributedCommissions;

        } catch (Exception $e) {
            DB::rollback();
            
            Log::error('ROI commission distribution failed', [
                'earner_id' => $earner->id,
                'roi_amount' => $roiAmount,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Get sponsor chain up to specified levels
     */
    private function getSponsorChain(User $user, int $levels = 10): array
    {
        $chain = [];
        $currentUser = $user;
        $level = 1;

        while ($level <= $levels && $currentUser->sponsor_id) {
            $sponsor = User::find($currentUser->sponsor_id);
            
            if (!$sponsor) {
                break;
            }

            $chain[] = [
                'user' => $sponsor,
                'level' => $level
            ];

            $currentUser = $sponsor;
            $level++;
        }

        return $chain;
    }

    /**
     * Get count of direct referrals (Level 1) for a user
     */
    private function getDirectReferralCount(User $user): int
    {
        return User::where('sponsor_id', $user->id)->count();
    }

    /**
     * Check if user meets profit sharing shield requirements for a given level
     * Requirements:
     * - Must have at least N direct referrals for Level N
     * - If min investment is set, combined investment of N referrals must meet minimum
     */
    private function checkProfitSharingShieldRequirement(User $user, int $level, float $minInvestment): array
    {
        // Get direct referrals with their total investments, sorted by investment amount descending
        $directReferrals = User::where('sponsor_id', $user->id)
            ->withSum(['packages as total_investment' => function($query) {
                $query->where('status', 'active');
            }], 'amount')
            ->orderByDesc('total_investment')
            ->get();

        $referralCount = $directReferrals->count();

        // First check: must have at least N referrals
        if ($referralCount < $level) {
            return [
                'passed' => false,
                'reason' => 'insufficient_referrals',
                'referral_count' => $referralCount,
                'combined_investment' => 0
            ];
        }

        // If no minimum investment requirement, just check referral count
        if ($minInvestment <= 0) {
            return [
                'passed' => true,
                'reason' => 'no_investment_required',
                'referral_count' => $referralCount,
                'combined_investment' => 0
            ];
        }

        // Calculate combined investment of top N referrals
        $topNReferrals = $directReferrals->take($level);
        $combinedInvestment = $topNReferrals->sum('total_investment') ?? 0;

        if ($combinedInvestment >= $minInvestment) {
            return [
                'passed' => true,
                'reason' => 'requirement_met',
                'referral_count' => $referralCount,
                'combined_investment' => $combinedInvestment
            ];
        }

        return [
            'passed' => false,
            'reason' => 'insufficient_investment',
            'referral_count' => $referralCount,
            'combined_investment' => $combinedInvestment
        ];
    }

    /**
     * Add commission to user's crypto wallet
     */
    private function addCommissionToWallet(
        User $sponsor, 
        float $commissionAmount, 
        User $earner, 
        int $level, 
        float $roiAmount,
        string $description = ''
    ): bool {
        try {
            $wallet = CryptoWallet::where('user_id', $sponsor->id)
                ->whereIn('currency', ['USDT_TRC20', 'USDT_BEP20', 'USDT_ERC20'])
                ->where('is_active', true)
                ->orderByRaw("FIELD(currency, 'USDT_TRC20', 'USDT_BEP20', 'USDT_ERC20')")
                ->first();

            if (!$wallet) {
                $wallet = CryptoWallet::where('user_id', $sponsor->id)
                    ->where('is_active', true)
                    ->first();
            }

            if (!$wallet) {
                $wallet = CryptoWallet::create([
                    'user_id' => $sponsor->id,
                    'currency' => 'USDT_TRC20',
                    'name' => 'Tether (TRC20)',
                    'balance' => 0,
                    'usd_rate' => 1,
                    'is_active' => true
                ]);
            }

            $oldBalance = $wallet->balance;
            $wallet->increment('balance', $commissionAmount);
            $newBalance = $wallet->fresh()->balance;

            $transactionId = 'PROFITSHARE_L' . $level . '_' . time() . '_' . $sponsor->id . '_' . uniqid();

            $levelText = $this->getLevelText($level);
            $txDescription = "Profit Share from {$levelText} - {$earner->full_name} earned $" . number_format($roiAmount, 4);
            if ($description) {
                $txDescription .= " ({$description})";
            }

            Transaction::create([
                'user_id' => $sponsor->id,
                'transaction_id' => $transactionId,
                'type' => Transaction::TYPE_PROFIT_SHARE,
                'amount' => $commissionAmount,
                'currency' => $wallet->currency,
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => 'profit_share',
                'description' => $txDescription,
                'processed_at' => now(),
                'metadata' => [
                    'wallet_id' => $wallet->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'profit_share_level' => $level,
                    'earner_id' => $earner->id,
                    'earner_name' => $earner->full_name,
                    'roi_amount' => $roiAmount,
                    'profit_share_source' => 'roi',
                    'currency' => $wallet->currency,
                    'distributed_at' => now()->toISOString()
                ]
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to add profit share to wallet', [
                'sponsor_id' => $sponsor->id,
                'profit_share_amount' => $commissionAmount,
                'level' => $level,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get readable text for level number
     */
    private function getLevelText(int $level): string
    {
        return match($level) {
            1 => 'Direct Referral',
            2 => '2nd Level',
            3 => '3rd Level',
            default => "{$level}th Level"
        };
    }

    /**
     * Get profit share statistics for a user
     */
    public function getProfitShareStats(User $user): array
    {
        $baseQuery = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_PROFIT_SHARE)
            ->where('status', Transaction::STATUS_COMPLETED);

        $levelEarnings = [];
        for ($i = 1; $i <= 10; $i++) {
            $levelEarnings["level_{$i}"] = (clone $baseQuery)
                ->where('metadata->profit_share_level', $i)
                ->sum('amount');
        }

        return array_merge([
            'total_earned' => (clone $baseQuery)->sum('amount'),
            'total_transactions' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->sum('amount'),
            'today' => (clone $baseQuery)->whereDate('created_at', now()->toDateString())->sum('amount'),
        ], $levelEarnings);
    }

    /**
     * Get recent profit share transactions for a user
     */
    public function getRecentProfitShares(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_PROFIT_SHARE)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
