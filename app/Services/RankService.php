<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rank;
use App\Models\UserRank;
use App\Models\UserReferral;
use App\Models\Transaction;
use App\Models\CryptoWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RankService
{
    public function checkAndAwardRanks(User $user, bool $payRewards = true): array
    {
        $results = [
            'checked' => true,
            'new_ranks' => [],
            'rewards_paid' => 0,
        ];

        try {
            $ranks = Rank::active()->ordered()->get();
            
            foreach ($ranks as $rank) {
                if ($user->hasRank($rank->id)) {
                    continue;
                }

                if ($this->qualifiesForRank($user, $rank)) {
                    $userRank = $this->awardRank($user, $rank);
                    if ($userRank) {
                        $results['new_ranks'][] = $rank;
                        
                        if ($payRewards) {
                            $reward = $this->payRankReward($user, $userRank);
                            if ($reward > 0) {
                                $results['rewards_paid'] += $reward;
                            }
                        }
                    }
                }
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Error checking ranks for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return $results;
        }
    }

    public function qualifiesForRank(User $user, Rank $rank): bool
    {
        $selfDeposit = $this->getUserActiveInvestment($user);
        if ($selfDeposit < $rank->min_self_deposit) {
            return false;
        }

        if ($rank->min_direct_members > 0) {
            $qualifiedDirectMembers = $this->getQualifiedDirectMembers(
                $user, 
                $rank->min_direct_member_investment
            );
            if ($qualifiedDirectMembers < $rank->min_direct_members) {
                return false;
            }
        }

        if ($rank->min_team_members > 0) {
            $qualifiedTeamMembers = $this->getQualifiedTeamMembers(
                $user, 
                $rank->min_team_member_investment
            );
            if ($qualifiedTeamMembers < $rank->min_team_members) {
                return false;
            }
        }

        $previousRanks = Rank::active()
            ->where('display_order', '<', $rank->display_order)
            ->pluck('id');
        
        foreach ($previousRanks as $prevRankId) {
            if (!$user->hasRank($prevRankId)) {
                return false;
            }
        }

        return true;
    }

    public function getUserActiveInvestment(User $user): float
    {
        return $user->investments()
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');
    }

    public function getQualifiedDirectMembers(User $user, float $minInvestment): int
    {
        return $user->directReferrals()
            ->get()
            ->filter(function ($member) use ($minInvestment) {
                $totalInvestment = $member->investments()
                    ->whereIn('status', ['active', 'completed'])
                    ->sum('amount');
                return $totalInvestment >= $minInvestment;
            })
            ->count();
    }

    public function getQualifiedTeamMembers(User $user, float $minInvestment, int $maxLevel = 10): int
    {
        $teamMemberIds = $this->getLevel2And3MemberIds($user);
        
        if (empty($teamMemberIds)) {
            return 0;
        }

        return User::whereIn('id', $teamMemberIds)
            ->get()
            ->filter(function ($member) use ($minInvestment) {
                $totalInvestment = $member->investments()
                    ->whereIn('status', ['active', 'completed'])
                    ->sum('amount');
                return $totalInvestment >= $minInvestment;
            })
            ->count();
    }

    private function getLevel2And3MemberIds(User $user): array
    {
        $level1UserIds = $user->directReferrals()->pluck('id')->toArray();
        
        if (empty($level1UserIds)) {
            return [];
        }

        $level2UserIds = User::whereIn('sponsor_id', $level1UserIds)
            ->pluck('id')
            ->toArray();

        if (empty($level2UserIds)) {
            return [];
        }

        $level3UserIds = User::whereIn('sponsor_id', $level2UserIds)
            ->pluck('id')
            ->toArray();

        return array_unique(array_merge($level2UserIds, $level3UserIds));
    }

    public function awardRank(User $user, Rank $rank): ?UserRank
    {
        try {
            return UserRank::create([
                'user_id' => $user->id,
                'rank_id' => $rank->id,
                'achieved_at' => now(),
                'reward_paid' => false,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to award rank', [
                'user_id' => $user->id,
                'rank_id' => $rank->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function payRankReward(User $user, UserRank $userRank): float
    {
        if ($userRank->reward_paid) {
            return 0;
        }

        $rank = $userRank->rank;
        if ($rank->reward_amount <= 0) {
            $userRank->markAsPaid();
            return 0;
        }

        try {
            DB::beginTransaction();

            $wallet = CryptoWallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => 'USDT_BEP20'],
                ['balance' => 0]
            );

            $wallet->increment('balance', $rank->reward_amount);

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'transaction_id' => 'RANK_' . $rank->id . '_' . time() . '_' . $user->id . '_' . uniqid(),
                'type' => 'rank_reward',
                'amount' => $rank->reward_amount,
                'currency' => 'USDT_BEP20',
                'status' => 'completed',
                'description' => "Rank reward for achieving {$rank->name}",
                'metadata' => [
                    'rank_id' => $rank->id,
                    'rank_name' => $rank->name,
                    'user_rank_id' => $userRank->id,
                ]
            ]);

            $userRank->markAsPaid();

            DB::commit();

            Log::info('Rank reward paid', [
                'user_id' => $user->id,
                'rank_id' => $rank->id,
                'rank_name' => $rank->name,
                'amount' => $rank->reward_amount
            ]);

            return $rank->reward_amount;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to pay rank reward', [
                'user_id' => $user->id,
                'rank_id' => $rank->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getUserRankProgress(User $user, ?Rank $singleRank = null): array
    {
        if ($singleRank) {
            return $this->getSingleRankProgress($user, $singleRank);
        }

        $progress = [];
        $ranks = Rank::active()->ordered()->get();

        foreach ($ranks as $rank) {
            $progress[$rank->id] = $this->getSingleRankProgress($user, $rank);
        }

        return $progress;
    }

    public function getSingleRankProgress(User $user, Rank $rank): array
    {
        $userRank = $user->ranks()->where('rank_id', $rank->id)->first();
        $selfDeposit = $this->getUserActiveInvestment($user);
        $directMembers = $this->getQualifiedDirectMembers($user, $rank->min_direct_member_investment);
        $teamMembers = $this->getQualifiedTeamMembers($user, $rank->min_team_member_investment);

        return [
            'rank' => $rank,
            'achieved' => $userRank !== null,
            'achieved_at' => $userRank ? $userRank->achieved_at : null,
            'reward_paid' => $userRank ? $userRank->reward_paid : false,
            'self_deposit' => [
                'current' => $selfDeposit,
                'required' => $rank->min_self_deposit,
                'met' => $selfDeposit >= $rank->min_self_deposit,
                'percentage' => $rank->min_self_deposit > 0 
                    ? min(100, ($selfDeposit / $rank->min_self_deposit) * 100) 
                    : 100,
            ],
            'direct_members' => [
                'current' => $directMembers,
                'required' => $rank->min_direct_members,
                'met' => $directMembers >= $rank->min_direct_members,
                'percentage' => $rank->min_direct_members > 0 
                    ? min(100, ($directMembers / $rank->min_direct_members) * 100) 
                    : 100,
            ],
            'team_members' => [
                'current' => $teamMembers,
                'required' => $rank->min_team_members,
                'met' => $teamMembers >= $rank->min_team_members,
                'percentage' => $rank->min_team_members > 0 
                    ? min(100, ($teamMembers / $rank->min_team_members) * 100) 
                    : 100,
            ],
        ];
    }

    public function processAllUsers(bool $payRewards = true): array
    {
        $stats = [
            'users_checked' => 0,
            'ranks_awarded' => 0,
            'rewards_paid' => 0,
        ];

        $users = User::where('status', 'active')
            ->where('excluded_from_stats', false)
            ->chunk(100, function ($users) use (&$stats, $payRewards) {
                foreach ($users as $user) {
                    $stats['users_checked']++;
                    $result = $this->checkAndAwardRanks($user, $payRewards);
                    $stats['ranks_awarded'] += count($result['new_ranks']);
                    $stats['rewards_paid'] += $result['rewards_paid'];
                }
            });

        return $stats;
    }
}
