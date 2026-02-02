<?php

namespace App\Services;

use App\Models\User;
use App\Models\InvestmentExpirySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReferralQualificationService
{
    public function qualifiesForExtendedMultiplier(User $user): bool
    {
        $cacheKey = "user_extended_multiplier_qualified_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            $option1 = InvestmentExpirySetting::getQualificationOption1();
            $option2 = InvestmentExpirySetting::getQualificationOption2();

            if ($this->checkOption1Qualification($user, $option1)) {
                return true;
            }

            if ($this->checkOption2Qualification($user, $option2)) {
                return true;
            }

            return false;
        });
    }

    public function getExpiryMultiplier(User $user): int
    {
        if ($this->qualifiesForExtendedMultiplier($user)) {
            return InvestmentExpirySetting::getExtendedMultiplier();
        }
        return InvestmentExpirySetting::getBaseMultiplier();
    }

    private function checkOption1Qualification(User $user, array $option): bool
    {
        if (!isset($option['type']) || $option['type'] !== 'direct_referrals') {
            return false;
        }

        $requiredCount = $option['count'] ?? 30;
        $minInvestment = $option['min_investment'] ?? 50;
        $qualifiedReferrals = $this->getQualifiedDirectReferralCount($user, $minInvestment);

        return $qualifiedReferrals >= $requiredCount;
    }

    /**
     * Get count of direct referrals with minimum investment requirement
     * Calculates actual invested amount from user_investments table (excludes bot_fee type)
     */
    public function getQualifiedDirectReferralCount(User $user, float $minInvestment = 50): int
    {
        return User::where('sponsor_id', $user->id)
            ->where('status', 'active')
            ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM user_investments WHERE user_investments.user_id = users.id AND user_investments.status IN (?, ?) AND (user_investments.type != ? OR user_investments.type IS NULL)) >= ?', ['active', 'completed', 'bot_fee', $minInvestment])
            ->count();
    }

    private function checkOption2Qualification(User $user, array $option): bool
    {
        if (!isset($option['type']) || $option['type'] !== 'tiered_referrals') {
            return false;
        }

        $levels = $option['levels'] ?? [];
        if (empty($levels)) {
            return false;
        }

        $referralCounts = $this->getReferralCountsByLevel($user, max(array_keys($levels)));

        foreach ($levels as $level => $requiredCount) {
            $actualCount = $referralCounts[$level] ?? 0;
            if ($actualCount < $requiredCount) {
                return false;
            }
        }

        return true;
    }

    public function getDirectReferralCount(User $user): int
    {
        return User::where('sponsor_id', $user->id)
            ->where('status', 'active')
            ->count();
    }

    public function getReferralCountsByLevel(User $user, int $maxLevel = 5): array
    {
        $counts = [];
        $currentLevelUsers = [$user->id];

        for ($level = 1; $level <= $maxLevel; $level++) {
            $nextLevelUsers = User::whereIn('sponsor_id', $currentLevelUsers)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();

            $counts[$level] = count($nextLevelUsers);
            $currentLevelUsers = $nextLevelUsers;

            if (empty($currentLevelUsers)) {
                break;
            }
        }

        for ($level = 1; $level <= $maxLevel; $level++) {
            if (!isset($counts[$level])) {
                $counts[$level] = 0;
            }
        }

        return $counts;
    }

    public function getQualificationStatus(User $user): array
    {
        $option1 = InvestmentExpirySetting::getQualificationOption1();
        $option2 = InvestmentExpirySetting::getQualificationOption2();
        
        $requiredCount = $option1['count'] ?? 30;
        $minInvestment = $option1['min_investment'] ?? 50;
        $qualifiedDirectCount = $this->getQualifiedDirectReferralCount($user, $minInvestment);
        
        $maxLevel = 5;
        if (isset($option2['levels'])) {
            $maxLevel = max(array_keys($option2['levels']));
        }
        $tieredCounts = $this->getReferralCountsByLevel($user, $maxLevel);

        $option1Met = $qualifiedDirectCount >= $requiredCount;
        $option1Progress = [
            'required' => $requiredCount,
            'current' => $qualifiedDirectCount,
            'min_investment' => $minInvestment,
            'met' => $option1Met
        ];

        $option2Progress = [];
        $option2AllMet = true;
        foreach (($option2['levels'] ?? []) as $level => $required) {
            $current = $tieredCounts[$level] ?? 0;
            $met = $current >= $required;
            $option2Progress[$level] = [
                'required' => $required,
                'current' => $current,
                'met' => $met
            ];
            if (!$met) {
                $option2AllMet = false;
            }
        }

        $qualifies = $option1Met || $option2AllMet;
        
        $qualifiedBy = [];
        if ($option1Met) {
            $qualifiedBy[] = [
                'option' => 1,
                'name' => 'Direct Referrals',
                'description' => "{$qualifiedDirectCount} referrals with \${$minInvestment}+ invested each"
            ];
        }
        if ($option2AllMet) {
            $levelsSummary = [];
            foreach ($option2Progress as $level => $data) {
                $levelsSummary[] = "Level {$level}: {$data['current']} referrals";
            }
            $qualifiedBy[] = [
                'option' => 2,
                'name' => 'Tiered Referrals',
                'description' => implode(', ', $levelsSummary)
            ];
        }

        return [
            'qualifies' => $qualifies,
            'current_multiplier' => $this->getExpiryMultiplier($user),
            'qualified_by' => $qualifiedBy,
            'option_1' => $option1Progress,
            'option_2' => [
                'levels' => $option2Progress,
                'all_met' => $option2AllMet
            ]
        ];
    }

    public function clearCache(User $user): void
    {
        Cache::forget("user_extended_multiplier_qualified_{$user->id}");
    }
}
