<?php

namespace App\Services;

use App\Models\User;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\UserInvestment;
use App\Models\CryptoWallet;
use App\Models\CommissionSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectSponsorCommissionService
{
    public function distributeCommission(UserInvestment $investment, ?float $investmentAmount = null): ?Transaction
    {
        try {
            // Skip bot_fee type investments - no commission for bot fees
            if ($investment->type === UserInvestment::TYPE_BOT_FEE) {
                Log::info('Skipping bot_fee investment for commission', [
                    'investment_id' => $investment->id
                ]);
                return null;
            }

            $user = $investment->user;
            $amount = $investmentAmount ?? $investment->amount;

            Log::info('=== STARTING DIRECT SPONSOR COMMISSION ===', [
                'investor_id' => $user->id,
                'investor_username' => $user->username,
                'investment_id' => $investment->id,
                'investment_amount' => $amount,
                'sponsor_id' => $user->sponsor_id
            ]);

            if (!$user->sponsor_id) {
                Log::info('No sponsor to pay commission to', [
                    'user_id' => $user->id,
                    'investment_id' => $investment->id
                ]);
                return null;
            }

            $sponsor = User::find($user->sponsor_id);

            if (!$sponsor) {
                Log::warning('Sponsor user not found', [
                    'user_id' => $user->id,
                    'sponsor_id' => $user->sponsor_id,
                    'investment_id' => $investment->id
                ]);
                return null;
            }

            // Skip if sponsor has commission disabled (dummy user)
            if ($sponsor->commission_disabled) {
                Log::info('Skipping sponsor with commission disabled', [
                    'sponsor_id' => $sponsor->id,
                    'investment_id' => $investment->id
                ]);
                return null;
            }

            // Get sponsor's tier based on their qualifications
            $sponsorTier = $this->getCommissionTierForUser($sponsor);
            
            // Use tier-based commission_level_1 for direct sponsor
            $commissionPercentage = $sponsorTier ? (float) $sponsorTier->commission_level_1 : 0;
            $tierName = $sponsorTier ? $sponsorTier->name : 'No Tier';

            Log::info('Tier-based commission lookup', [
                'sponsor_id' => $sponsor->id,
                'tier_name' => $tierName,
                'tier_level' => $sponsorTier ? $sponsorTier->level : null,
                'commission_percentage' => $commissionPercentage
            ]);

            if ($commissionPercentage <= 0) {
                Log::info('No commission for this tier', [
                    'investment_id' => $investment->id,
                    'tier_name' => $tierName
                ]);
                return null;
            }

            $commissionAmount = round(($amount * $commissionPercentage) / 100, 4);

            if ($commissionAmount <= 0) {
                return null;
            }

            Log::info('Commission calculation', [
                'investment_amount' => $amount,
                'commission_percentage' => $commissionPercentage,
                'commission_amount' => $commissionAmount,
                'sponsor_id' => $sponsor->id,
                'sponsor_username' => $sponsor->username
            ]);

            return DB::transaction(function () use ($sponsor, $user, $investment, $amount, $commissionAmount, $commissionPercentage) {
                $wallet = CryptoWallet::where('user_id', $sponsor->id)
                    ->where('is_active', true)
                    ->first();

                Log::info('Sponsor wallet lookup', [
                    'sponsor_id' => $sponsor->id,
                    'wallet_found' => $wallet ? true : false,
                    'wallet_id' => $wallet ? $wallet->id : null,
                    'wallet_currency' => $wallet ? $wallet->currency : null,
                    'wallet_balance_before' => $wallet ? $wallet->balance : null
                ]);

                if (!$wallet) {
                    Log::error('No active crypto wallet found for sponsor', [
                        'sponsor_id' => $sponsor->id,
                        'investment_id' => $investment->id
                    ]);
                    return null;
                }

                $oldBalance = $wallet->balance;
                
                $wallet->increment('balance', $commissionAmount);
                
                $newBalance = $wallet->fresh()->balance;

                Log::info('=== WALLET BALANCE UPDATED ===', [
                    'wallet_id' => $wallet->id,
                    'sponsor_id' => $sponsor->id,
                    'currency' => $wallet->currency,
                    'old_balance' => $oldBalance,
                    'commission_amount' => $commissionAmount,
                    'new_balance' => $newBalance
                ]);

                $transaction = Transaction::create([
                    'user_id' => $sponsor->id,
                    'transaction_id' => 'SPCOM_' . time() . '_' . $sponsor->id . '_' . uniqid(),
                    'type' => 'commission',
                    'amount' => $commissionAmount,
                    'currency' => $wallet->currency,
                    'status' => 'completed',
                    'description' => "Direct sponsor commission ({$commissionPercentage}%) from {$user->username}'s investment",
                    'balance_after' => $newBalance,
                    'metadata' => [
                        'source_user_id' => $user->id,
                        'source_username' => $user->username,
                        'investment_id' => $investment->id,
                        'investment_amount' => $amount,
                        'commission_percentage' => $commissionPercentage,
                        'commission_type' => 'direct_sponsor',
                        'wallet_id' => $wallet->id,
                        'old_balance' => $oldBalance,
                        'new_balance' => $newBalance
                    ]
                ]);

                Log::info('=== DIRECT SPONSOR COMMISSION COMPLETED ===', [
                    'sponsor_id' => $sponsor->id,
                    'sponsor_username' => $sponsor->username,
                    'from_user_id' => $user->id,
                    'from_username' => $user->username,
                    'investment_id' => $investment->id,
                    'investment_amount' => $amount,
                    'commission_percentage' => $commissionPercentage,
                    'commission_amount' => $commissionAmount,
                    'wallet_id' => $wallet->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'transaction_id' => $transaction->transaction_id
                ]);

                return $transaction;
            });

        } catch (\Exception $e) {
            Log::error('Failed to distribute direct sponsor commission', [
                'investment_id' => $investment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public static function getCommissionPercentage(): float
    {
        return (float) Setting::getValue('direct_sponsor_commission', 8);
    }

    /**
     * Get the commission tier for a user based on their qualifications
     */
    private function getCommissionTierForUser(User $user): ?CommissionSetting
    {
        $directReferrals = User::where('sponsor_id', $user->id)->count();
        $indirectReferrals = $this->getIndirectReferralCount($user);
        $activeInvestment = UserInvestment::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount');

        // Get all active tiers, ordered by level descending (highest first)
        $tiers = CommissionSetting::where('is_active', true)
            ->orderBy('level', 'desc')
            ->get();

        // Find the highest tier the user qualifies for
        foreach ($tiers as $tier) {
            $meetsInvestment = $activeInvestment >= ($tier->min_investment ?? 0);
            $meetsDirectReferrals = $directReferrals >= ($tier->min_direct_referrals ?? 0);
            $meetsIndirectReferrals = $indirectReferrals >= ($tier->min_indirect_referrals ?? 0);

            if ($meetsInvestment && $meetsDirectReferrals && $meetsIndirectReferrals) {
                return $tier;
            }
        }

        // Fall back to lowest tier if user doesn't qualify for any
        return CommissionSetting::where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
    }

    /**
     * Get count of indirect referrals (Level 2+)
     */
    private function getIndirectReferralCount(User $user, int $maxDepth = 10): int
    {
        $count = 0;
        $currentLevelIds = [$user->id];
        $depth = 0;

        while ($depth < $maxDepth && !empty($currentLevelIds)) {
            $nextLevelIds = User::whereIn('sponsor_id', $currentLevelIds)->pluck('id')->toArray();
            if ($depth > 0) { // Skip direct referrals (depth 0)
                $count += count($nextLevelIds);
            }
            $currentLevelIds = $nextLevelIds;
            $depth++;
        }

        return $count;
    }
}
