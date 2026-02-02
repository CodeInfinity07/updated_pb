<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\ReferralCommissionLevel;
use App\Models\CommissionSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminCommissionSimulatorController extends Controller
{
    public function index()
    {
        $users = User::with(['profile', 'investments' => function($q) {
            $q->where('status', 'active');
        }])
        ->where('status', 'active')
        ->orderBy('username')
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'level' => $user->profile->level ?? 0,
                'total_invested' => $user->total_invested ?? 0,
                'active_investments' => $user->investments->count()
            ];
        });

        return view('admin.referrals.simulator.index', compact('users'));
    }

    public function simulate(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'downline_investment_amount' => 'nullable|numeric|min:0'
        ]);

        $user = User::with(['profile', 'investments' => function($q) {
            $q->where('status', 'active')->with('investmentPlan');
        }])->find($request->user_id);

        $downlineInvestmentAmount = floatval($request->downline_investment_amount ?? 1000);

        $result = [
            'user' => $this->getUserInfo($user),
            'tomorrow_roi' => $this->calculateTomorrowRoi($user),
            'sponsor_chain_profit_share' => $this->calculateSponsorChainProfitShare($user),
            'referral_commission' => $this->calculateReferralCommission($user, $downlineInvestmentAmount),
            'downline_investment_amount' => $downlineInvestmentAmount
        ];

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    private function getUserInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'level' => $user->profile->level ?? 0,
            'total_invested' => $user->total_invested ?? 0,
            'total_earned' => $user->total_earned ?? 0,
            'sponsor_id' => $user->sponsor_id,
            'sponsor_username' => $user->sponsor ? $user->sponsor->username : null,
            'active_investments_count' => $user->investments->count(),
            'active_investments_value' => $user->investments->sum('amount')
        ];
    }

    private function calculateTomorrowRoi(User $user): array
    {
        $activeInvestments = $user->investments->where('status', 'active');
        $roiDetails = [];
        $totalRoi = 0;

        $baseMultiplier = (float) Setting::getValue('package_expiry_multiplier', 3);

        foreach ($activeInvestments as $investment) {
            $plan = $investment->investmentPlan;
            if (!$plan) continue;

            $dailyRoi = $plan->roi_percentage ?? 0;
            $roiAmount = round(($investment->amount * $dailyRoi) / 100, 4);
            
            $expiryCap = $investment->amount * $baseMultiplier;
            $earningsAccumulated = $investment->earnings_accumulated ?? 0;
            $remainingCap = $expiryCap - $earningsAccumulated;
            
            if ($remainingCap <= 0) {
                $roiAmount = 0;
                $status = 'expired';
            } elseif ($roiAmount > $remainingCap) {
                $roiAmount = $remainingCap;
                $status = 'capped';
            } else {
                $status = 'active';
            }

            $roiDetails[] = [
                'investment_id' => $investment->id,
                'plan_name' => $plan->name,
                'amount' => $investment->amount,
                'roi_percentage' => $dailyRoi,
                'roi_amount' => $roiAmount,
                'earnings_accumulated' => $earningsAccumulated,
                'expiry_cap' => $expiryCap,
                'remaining_cap' => max(0, $remainingCap),
                'status' => $status
            ];

            $totalRoi += $roiAmount;
        }

        return [
            'total_roi' => round($totalRoi, 4),
            'investments' => $roiDetails
        ];
    }

    private function calculateSponsorChainProfitShare(User $user): array
    {
        $tomorrowRoi = $this->calculateTomorrowRoi($user);
        $roiAmount = $tomorrowRoi['total_roi'];

        if ($roiAmount <= 0) {
            return [
                'roi_amount' => 0,
                'chain' => [],
                'total_distributed' => 0,
                'message' => 'No ROI to distribute - user has no active investments or all are expired'
            ];
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

        $profitSharingShieldEnabled = Setting::getValue('profit_sharing_shield_enabled', false);
        $profitSharingShieldMinInvestment = (float) Setting::getValue('profit_sharing_shield_min_investment', 0);

        $chain = [];
        $currentUser = $user;
        $level = 1;
        $totalDistributed = 0;
        $maxLevels = 3;

        while ($level <= $maxLevels && $currentUser->sponsor_id) {
            $sponsor = User::with('profile')->find($currentUser->sponsor_id);
            
            if (!$sponsor) break;

            $percentage = $commissionLevels[$level] ?? 0;
            $commissionAmount = round(($roiAmount * $percentage) / 100, 4);

            $skipReason = null;
            $willReceive = true;

            if ($sponsor->status !== 'active') {
                $skipReason = 'Sponsor is not active';
                $willReceive = false;
            } elseif ($sponsor->commission_disabled) {
                $skipReason = 'Commission disabled for this user';
                $willReceive = false;
            } elseif ($profitSharingShieldEnabled) {
                $shieldCheck = $this->checkProfitSharingShieldRequirement($sponsor, $level, $profitSharingShieldMinInvestment);
                if (!$shieldCheck['passed']) {
                    $skipReason = "Shield requirement not met: {$shieldCheck['reason']} (needs {$level} direct referrals with combined investment >= \${$profitSharingShieldMinInvestment})";
                    $willReceive = false;
                }
            }

            if ($willReceive) {
                $totalDistributed += $commissionAmount;
            }

            $chain[] = [
                'level' => $level,
                'user_id' => $sponsor->id,
                'username' => $sponsor->username,
                'full_name' => $sponsor->full_name,
                'user_level' => $sponsor->profile->level ?? 0,
                'percentage' => $percentage,
                'commission_amount' => $commissionAmount,
                'will_receive' => $willReceive,
                'skip_reason' => $skipReason
            ];

            $currentUser = $sponsor;
            $level++;
        }

        return [
            'roi_amount' => $roiAmount,
            'chain' => $chain,
            'total_distributed' => round($totalDistributed, 4),
            'profit_sharing_shield_enabled' => $profitSharingShieldEnabled,
            'profit_sharing_shield_min_investment' => $profitSharingShieldMinInvestment
        ];
    }

    private function checkProfitSharingShieldRequirement(User $user, int $level, float $minInvestment): array
    {
        $directReferrals = User::where('sponsor_id', $user->id)
            ->withSum(['investments as total_investment' => function($query) {
                $query->where('status', 'active');
            }], 'amount')
            ->orderByDesc('total_investment')
            ->get();

        $referralCount = $directReferrals->count();

        if ($referralCount < $level) {
            return [
                'passed' => false,
                'reason' => "insufficient_referrals ({$referralCount}/{$level})",
                'referral_count' => $referralCount,
                'combined_investment' => 0
            ];
        }

        if ($minInvestment <= 0) {
            return [
                'passed' => true,
                'reason' => 'no_investment_required',
                'referral_count' => $referralCount,
                'combined_investment' => 0
            ];
        }

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
            'reason' => "insufficient_investment (\${$combinedInvestment}/\${$minInvestment})",
            'referral_count' => $referralCount,
            'combined_investment' => $combinedInvestment
        ];
    }

    private function calculateReferralCommission(User $user, float $investmentAmount): array
    {
        $directSponsorPercentage = (float) Setting::getValue('direct_sponsor_commission', 8);
        $directCommission = round(($investmentAmount * $directSponsorPercentage) / 100, 4);

        $userTier = $this->getCommissionTierForUser($user);

        $tierCommissions = [];
        if ($userTier) {
            $tierCommissions = [
                'level_1' => [
                    'percentage' => $userTier->commission_level_1,
                    'amount' => round(($investmentAmount * $userTier->commission_level_1) / 100, 4)
                ],
                'level_2' => [
                    'percentage' => $userTier->commission_level_2,
                    'amount' => round(($investmentAmount * $userTier->commission_level_2) / 100, 4)
                ],
                'level_3' => [
                    'percentage' => $userTier->commission_level_3,
                    'amount' => round(($investmentAmount * $userTier->commission_level_3) / 100, 4)
                ]
            ];
        }

        $skipReason = null;
        $willReceive = true;

        if ($user->status !== 'active') {
            $skipReason = 'User (sponsor) is not active';
            $willReceive = false;
        } elseif ($user->commission_disabled) {
            $skipReason = 'Commission disabled for this user (sponsor)';
            $willReceive = false;
        }

        $hasActiveWallet = \App\Models\CryptoWallet::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
        
        if (!$hasActiveWallet) {
            $skipReason = 'User has no active crypto wallet to receive commission';
            $willReceive = false;
        }

        return [
            'investment_amount' => $investmentAmount,
            'direct_sponsor_commission' => [
                'percentage' => $directSponsorPercentage,
                'amount' => $directCommission,
                'will_receive' => $willReceive,
                'skip_reason' => $skipReason,
                'has_active_wallet' => $hasActiveWallet
            ],
            'tier_based_commission' => [
                'tier_name' => $userTier ? $userTier->name : 'No Tier',
                'tier_level' => $userTier ? $userTier->level : null,
                'user_profile_level' => $user->profile->level ?? 0,
                'commissions' => $tierCommissions,
                'total_tier_commission' => $userTier ? round(
                    ($investmentAmount * ($userTier->commission_level_1 + $userTier->commission_level_2 + $userTier->commission_level_3)) / 100, 4
                ) : 0,
                'qualifies_for_tier' => $userTier ? $this->checkTierQualification($user, $userTier) : null
            ],
            'note' => 'This user is the sponsor receiving commission when their downline invests. Direct sponsor commission (8%) is paid immediately. Tier-based commission was used in the old system.'
        ];
    }

    private function checkTierQualification(User $user, CommissionSetting $tier): array
    {
        $directReferrals = User::where('sponsor_id', $user->id)->count();
        $indirectReferrals = $this->getIndirectReferralCount($user);
        $activeInvestment = UserInvestment::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount');

        $meetsInvestment = $activeInvestment >= ($tier->min_investment ?? 0);
        $meetsDirectReferrals = $directReferrals >= ($tier->min_direct_referrals ?? 0);
        $meetsIndirectReferrals = $indirectReferrals >= ($tier->min_indirect_referrals ?? 0);

        return [
            'qualifies' => $meetsInvestment && $meetsDirectReferrals && $meetsIndirectReferrals,
            'active_investment' => $activeInvestment,
            'required_investment' => $tier->min_investment ?? 0,
            'meets_investment' => $meetsInvestment,
            'direct_referrals' => $directReferrals,
            'required_direct_referrals' => $tier->min_direct_referrals ?? 0,
            'meets_direct_referrals' => $meetsDirectReferrals,
            'indirect_referrals' => $indirectReferrals,
            'required_indirect_referrals' => $tier->min_indirect_referrals ?? 0,
            'meets_indirect_referrals' => $meetsIndirectReferrals
        ];
    }

    private function getIndirectReferralCount(User $user, int $maxDepth = 10): int
    {
        $count = 0;
        $currentLevelIds = [$user->id];
        $depth = 0;

        while ($depth < $maxDepth && !empty($currentLevelIds)) {
            $nextLevelIds = User::whereIn('sponsor_id', $currentLevelIds)->pluck('id')->toArray();
            if ($depth > 0) {
                $count += count($nextLevelIds);
            }
            $currentLevelIds = $nextLevelIds;
            $depth++;
        }

        return $count;
    }

    private function getCommissionTierForUser(User $user): ?CommissionSetting
    {
        $userLevel = $user->profile ? $user->profile->level : 0;

        return CommissionSetting::where('is_active', true)
            ->where('level', $userLevel)
            ->first();
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        $users = User::with('profile')
            ->where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => "{$user->username} - {$user->full_name} ({$user->email})",
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'email' => $user->email
                ];
            });

        return response()->json(['results' => $users]);
    }
}
