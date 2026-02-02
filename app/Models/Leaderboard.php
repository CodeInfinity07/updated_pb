<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'type',
        'start_date',
        'end_date',
        'show_to_users',
        'max_positions',
        'referral_type',
        'max_referral_level',
        'min_investment_amount',
        'prize_structure',
        'target_referrals',
        'target_prize_amount',
        'target_tiers',
        'max_winners',
        'prizes_distributed',
        'created_by',
        'prizes_distributed_at',
        'prizes_distributed_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'show_to_users' => 'boolean',
        'prizes_distributed' => 'boolean',
        'prize_structure' => 'array',
        'target_tiers' => 'array',
        'target_prize_amount' => 'decimal:2',
        'min_investment_amount' => 'decimal:2',
        'prizes_distributed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prizeDistributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prizes_distributed_by');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(LeaderboardPosition::class);
    }

    public function topPositions(): HasMany
    {
        return $this->hasMany(LeaderboardPosition::class)->orderBy('position');
    }

    public function qualifiedPositions(): HasMany
    {
        return $this->hasMany(LeaderboardPosition::class)
            ->where('referral_count', '>=', $this->target_referrals ?? 0);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVisible($query)
    {
        return $query->where('show_to_users', true);
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeCompetitive($query)
    {
        return $query->where('type', 'competitive');
    }

    public function scopeTarget($query)
    {
        return $query->where('type', 'target');
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-success',
            'completed' => 'bg-primary',
            'inactive' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'competitive' => 'bg-primary',
            'target' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'competitive' => 'Competitive Ranking',
            'target' => 'Target Achievement',
            default => 'Competitive Ranking'
        };
    }

    public function getReferralTypeDisplayAttribute(): string
    {
        $display = match($this->referral_type) {
            'direct' => 'Direct Referrals Only',
            'multi_level' => 'Multi-Level Referrals',
            'all' => 'All Referrals',
            'first_level' => 'First Level Only',
            'verified_only' => 'Verified Users Only',
            default => $this->referral_type ?? 'All Referrals'
        };

        if ($this->referral_type === 'multi_level' && $this->max_referral_level) {
            $display .= " (Up to Level {$this->max_referral_level})";
        }

        return $display;
    }

    public function getDurationDisplayAttribute(): string
    {
        $start = $this->start_date->format('M d, Y');
        $end = $this->end_date->format('M d, Y');
        
        if ($start === $end) {
            return $start;
        }
        
        return "{$start} - {$end}";
    }

    public function getDaysRemainingAttribute(): int
    {
        $today = now()->startOfDay();
        $endDay = $this->end_date->startOfDay();
        
        // If we're past the end date
        if ($today->greaterThan($endDay)) {
            return 0;
        }

        // Calculate days from today until the end date (inclusive of today as a potential "last day")
        // If today is the end date, return 1 (last day)
        // Otherwise return the number of days between today and end date
        return max(1, (int) $today->diffInDays($endDay));
    }

    public function getTotalPrizeAmountAttribute(): float
    {
        if ($this->type === 'target') {
            // For target-based, show sum of all tier amounts as potential prize pool
            $tiers = $this->getSortedTiers();
            if (!empty($tiers)) {
                return collect($tiers)->sum('amount');
            }
            return (float) ($this->target_prize_amount ?? 0);
        }

        if (!$this->prize_structure) {
            return 0.0;
        }

        return collect($this->prize_structure)->sum('amount');
    }

    public function getMaxPossiblePrizeAmountAttribute(): float
    {
        if ($this->type === 'target') {
            // Calculate maximum possible if all users reach target
            $maxWinners = $this->max_winners ?: 1000; // Default reasonable limit
            return $this->target_prize_amount * $maxWinners;
        }

        return $this->getTotalPrizeAmountAttribute();
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->end_date < now();
    }

    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    public function isCompetitive(): bool
    {
        return $this->type === 'competitive';
    }

    public function isTarget(): bool
    {
        return $this->type === 'target';
    }

    public function canActivate(): bool
    {
        return $this->status === 'inactive' && $this->start_date <= now();
    }

    public function canComplete(): bool
    {
        return $this->status === 'active' && $this->end_date < now();
    }

    public function canDistributePrizes(): bool
    {
        return $this->status === 'completed' && 
               !$this->prizes_distributed && 
               $this->positions()->exists();
    }

    public function hasEnded(): bool
    {
        return $this->end_date < now();
    }

    public function getProgress(): int
    {
        if ($this->isUpcoming()) {
            return 0;
        }

        if ($this->hasEnded()) {
            return 100;
        }

        $total = $this->start_date->diffInSeconds($this->end_date);
        $elapsed = $this->start_date->diffInSeconds(now());

        return round(($elapsed / $total) * 100);
    }

    public function getUserPosition(User $user): ?LeaderboardPosition
    {
        return $this->positions()->where('user_id', $user->id)->first();
    }

    public function getUserRank(User $user): ?int
    {
        $position = $this->getUserPosition($user);
        return $position ? $position->position : null;
    }

    public function getParticipantsCount(): int
    {
        return $this->positions()->count();
    }

    public function getWinnersCount(): int
    {
        if ($this->type === 'target') {
            return $this->getQualifiedCount();
        }

        return $this->positions()->where('prize_amount', '>', 0)->count();
    }

    public function getQualifiedCount(): int
    {
        if ($this->type !== 'target') {
            return 0;
        }

        $minTarget = $this->getMinimumTargetReferrals();
        return $this->positions()
            ->where('referral_count', '>=', $minTarget)
            ->count();
    }

    public function getTierBreakdown(): array
    {
        if ($this->type !== 'target') {
            return [];
        }

        $tiers = $this->getSortedTiers();
        $positions = $this->positions()->get();
        
        $breakdown = [];
        foreach ($tiers as $index => $tier) {
            $tierNumber = $index + 1;
            $count = $positions->filter(function ($position) use ($tier, $tiers, $index) {
                $refCount = $position->referral_count;
                $meetsThisTier = $refCount >= $tier['target'];
                $nextTierIndex = $index + 1;
                $belowNextTier = !isset($tiers[$nextTierIndex]) || $refCount < $tiers[$nextTierIndex]['target'];
                return $meetsThisTier && $belowNextTier;
            })->count();
            
            $breakdown[] = [
                'tier' => $tierNumber,
                'target' => $tier['target'],
                'amount' => $tier['amount'],
                'count' => $count,
            ];
        }
        
        return $breakdown;
    }

    public function getQualifiedPrizeAmount(): float
    {
        if ($this->type !== 'target') {
            return 0;
        }

        return $this->positions()
            ->where('prize_amount', '>', 0)
            ->sum('prize_amount');
    }

    public function userQualifies(User $user): bool
    {
        if ($this->type !== 'target') {
            return false;
        }

        $position = $this->getUserPosition($user);
        $minTarget = $this->getMinimumTargetReferrals();
        return $position && $position->referral_count >= $minTarget;
    }

    public function getTargetProgress(User $user): float
    {
        if ($this->type !== 'target') {
            return 0;
        }

        $position = $this->getUserPosition($user);
        if (!$position) {
            return 0;
        }

        $nextTier = $this->getNextTierForReferralCount($position->referral_count);
        $targetReferrals = $nextTier ? $nextTier['target'] : $this->getMinimumTargetReferrals();
        
        return min(100, ($position->referral_count / $targetReferrals) * 100);
    }

    /**
     * Check if leaderboard has multiple reward tiers
     */
    public function hasMultipleTiers(): bool
    {
        return $this->type === 'target' && !empty($this->target_tiers) && count($this->target_tiers) > 0;
    }

    /**
     * Get sorted target tiers (ascending by target)
     */
    public function getSortedTiers(): array
    {
        if (!$this->hasMultipleTiers()) {
            if ($this->target_referrals && $this->target_prize_amount) {
                return [['target' => (int) $this->target_referrals, 'amount' => (float) $this->target_prize_amount]];
            }
            return [];
        }

        $tiers = collect($this->target_tiers)->map(function ($tier) {
            return [
                'target' => (int) $tier['target'],
                'amount' => (float) $tier['amount']
            ];
        })->sortBy('target')->values()->toArray();

        return $tiers;
    }

    /**
     * Get minimum target referrals to qualify for any prize
     */
    public function getMinimumTargetReferrals(): int
    {
        $tiers = $this->getSortedTiers();
        return !empty($tiers) ? $tiers[0]['target'] : ($this->target_referrals ?? 0);
    }

    /**
     * Get the tier applicable for a given referral count (highest achieved tier)
     */
    public function getTierForReferralCount(int $referralCount): ?array
    {
        $tiers = $this->getSortedTiers();
        $achievedTier = null;

        foreach ($tiers as $tier) {
            if ($referralCount >= $tier['target']) {
                $achievedTier = $tier;
            }
        }

        return $achievedTier;
    }

    /**
     * Get the next tier to achieve for a given referral count
     */
    public function getNextTierForReferralCount(int $referralCount): ?array
    {
        $tiers = $this->getSortedTiers();

        foreach ($tiers as $tier) {
            if ($referralCount < $tier['target']) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * Get prize amount for a given referral count based on tiers
     */
    public function getPrizeAmountForReferralCount(int $referralCount): float
    {
        $tier = $this->getTierForReferralCount($referralCount);
        return $tier ? $tier['amount'] : 0.0;
    }

    /**
     * Calculate and update leaderboard positions
     */
    public function calculatePositions(): void
    {
        app('App\Services\LeaderboardService')->calculatePositions($this);
    }

    /**
     * Distribute prizes to winners
     */
    public function distributePrizes(): bool
    {
        return app('App\Services\LeaderboardService')->distributePrizes($this);
    }

    /**
     * Get formatted prize information for display
     */
    public function getPrizeInfoAttribute(): string
    {
        if ($this->type === 'target') {
            $tiers = $this->getSortedTiers();
            
            if (count($tiers) > 1) {
                $tierInfo = collect($tiers)->map(function($tier) {
                    return $tier['target'] . ' refs = $' . number_format($tier['amount']);
                })->join(', ');
                $info = "Multi-tier: {$tierInfo}";
            } elseif (count($tiers) === 1) {
                $info = "Target: {$tiers[0]['target']} referrals = $" . number_format($tiers[0]['amount'], 2);
            } else {
                $info = "Target: {$this->target_referrals} referrals = $" . number_format($this->target_prize_amount, 2);
            }
            
            if ($this->max_winners) {
                $info .= " (Max {$this->max_winners} winners)";
            }
            return $info;
        }

        if (!$this->prize_structure) {
            return 'No prizes configured';
        }

        $prizeCount = count($this->prize_structure);
        $totalAmount = $this->getTotalPrizeAmountAttribute();
        
        return "{$prizeCount} prizes totaling $" . number_format($totalAmount, 2);
    }
}