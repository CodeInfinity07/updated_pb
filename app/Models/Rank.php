<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'description',
        'display_order',
        'min_self_deposit',
        'min_direct_members',
        'min_direct_member_investment',
        'min_team_members',
        'min_team_member_investment',
        'reward_amount',
        'is_active',
    ];

    protected $casts = [
        'min_self_deposit' => 'decimal:2',
        'min_direct_member_investment' => 'decimal:2',
        'min_team_member_investment' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function userRanks(): HasMany
    {
        return $this->hasMany(UserRank::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function getUsersAchievedCount(): int
    {
        return $this->userRanks()->count();
    }

    public function getTotalRewardsPaid(): float
    {
        return $this->userRanks()
            ->where('reward_paid', true)
            ->count() * $this->reward_amount;
    }

    public static function getNextRank(?int $currentOrder = null): ?self
    {
        $query = self::active()->ordered();
        
        if ($currentOrder !== null) {
            $query->where('display_order', '>', $currentOrder);
        }
        
        return $query->first();
    }

    public static function getByOrder(int $order): ?self
    {
        return self::where('display_order', $order)->first();
    }

    public function getFormattedRewardAttribute(): string
    {
        return '$' . number_format($this->reward_amount, 2);
    }

    public function getFormattedMinSelfDepositAttribute(): string
    {
        return '$' . number_format($this->min_self_deposit, 2);
    }

    public function getRequirementsTextAttribute(): string
    {
        $requirements = [];
        
        if ($this->min_self_deposit > 0) {
            $requirements[] = 'Self Deposit: $' . number_format($this->min_self_deposit, 0);
        }
        
        if ($this->min_direct_members > 0) {
            $requirements[] = 'Direct Members: ' . $this->min_direct_members . ' (Each $' . number_format($this->min_direct_member_investment, 0) . ')';
        }
        
        if ($this->min_team_members > 0) {
            $requirements[] = 'Team: ' . $this->min_team_members . ' (Each $' . number_format($this->min_team_member_investment, 0) . ')';
        }
        
        return implode(' | ', $requirements);
    }
}
