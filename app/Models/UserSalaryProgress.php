<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSalaryProgress extends Model
{
    protected $table = 'user_salary_progress';

    protected $fillable = [
        'user_id',
        'current_stage',
        'last_completed_stage',
        'used_referrals',
    ];

    protected $casts = [
        'used_referrals' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUsedReferralIds(): array
    {
        return $this->used_referrals ?? [];
    }

    public function addUsedReferrals(array $referralIds, int $stageOrder): void
    {
        $usedReferrals = $this->used_referrals ?? [];
        $usedReferrals["stage_{$stageOrder}"] = $referralIds;
        $this->used_referrals = $usedReferrals;
        $this->save();
    }

    public function getAllUsedReferralIds(): array
    {
        if (!$this->used_referrals) {
            return [];
        }
        
        $allIds = [];
        foreach ($this->used_referrals as $stageReferrals) {
            if (is_array($stageReferrals)) {
                $allIds = array_merge($allIds, $stageReferrals);
            }
        }
        
        return array_unique($allIds);
    }

    public static function getOrCreateForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_stage' => 0,
                'last_completed_stage' => 0,
                'used_referrals' => [],
            ]
        );
    }
}
