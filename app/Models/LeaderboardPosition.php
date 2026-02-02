<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaderboard_id',
        'user_id',
        'position',
        'referral_count',
        'prize_amount',
        'prize_awarded',
        'prize_awarded_at',
        'prize_approved',
        'prize_approved_at',
        'prize_approved_by',
        'prize_claimed',
        'prize_claimed_at',
    ];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'prize_awarded' => 'boolean',
        'prize_awarded_at' => 'datetime',
        'prize_approved' => 'boolean',
        'prize_approved_at' => 'datetime',
        'prize_claimed' => 'boolean',
        'prize_claimed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function leaderboard(): BelongsTo
    {
        return $this->belongsTo(Leaderboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prize_approved_by');
    }

    /**
     * Scopes
     */
    public function scopeWinners($query)
    {
        return $query->where('prize_amount', '>', 0);
    }

    public function scopePrizeAwarded($query)
    {
        return $query->where('prize_awarded', true);
    }

    public function scopePrizePending($query)
    {
        return $query->where('prize_amount', '>', 0)
                    ->where('prize_awarded', false);
    }

    public function scopePrizeApproved($query)
    {
        return $query->where('prize_approved', true);
    }

    public function scopePrizePendingClaim($query)
    {
        return $query->where('prize_amount', '>', 0)
                    ->where('prize_approved', true)
                    ->where('prize_claimed', false);
    }

    public function scopePrizeClaimed($query)
    {
        return $query->where('prize_claimed', true);
    }

    public function scopeTopPositions($query, $limit = 10)
    {
        return $query->orderBy('position')->limit($limit);
    }

    /**
     * Accessors
     */
    public function getPositionDisplayAttribute(): string
    {
        return match($this->position) {
            1 => '🥇 1st',
            2 => '🥈 2nd', 
            3 => '🥉 3rd',
            default => "#{$this->position}"
        };
    }

    public function getPositionBadgeClassAttribute(): string
    {
        return match($this->position) {
            1 => 'bg-warning text-dark', // Gold
            2 => 'bg-secondary text-white', // Silver
            3 => 'bg-warning text-dark', // Bronze (using warning for bronze-ish color)
            default => 'bg-primary text-white'
        };
    }

    public function getPrizeStatusBadgeClassAttribute(): string
    {
        if (!$this->prize_amount || $this->prize_amount <= 0) {
            return 'bg-light text-dark';
        }

        if ($this->prize_claimed) {
            return 'bg-success';
        }
        
        if ($this->prize_approved) {
            return 'bg-info';
        }

        return 'bg-warning text-dark';
    }

    public function getPrizeStatusTextAttribute(): string
    {
        if (!$this->prize_amount || $this->prize_amount <= 0) {
            return 'No Prize';
        }

        if ($this->prize_claimed) {
            return 'Claimed';
        }
        
        if ($this->prize_approved) {
            return 'Ready to Claim';
        }

        return 'Pending Approval';
    }

    /**
     * Helper Methods
     */
    public function isWinner(): bool
    {
        return $this->prize_amount > 0;
    }

    public function isPrizeAwarded(): bool
    {
        return $this->prize_awarded;
    }

    public function isPrizePending(): bool
    {
        return $this->isWinner() && !$this->isPrizeAwarded();
    }

    public function isTopThree(): bool
    {
        return $this->position <= 3;
    }

    public function getPositionIcon(): string
    {
        return match($this->position) {
            1 => 'akar-icons:trophy',
            2 => 'iconamoon:medal-duotone',
            3 => 'iconamoon:medal-duotone',
            default => 'iconamoon:hashtag-duotone'
        };
    }

    public function markPrizeAsAwarded(): bool
    {
        return $this->update([
            'prize_awarded' => true,
            'prize_awarded_at' => now(),
        ]);
    }

    public function markPrizeAsApproved(int $approvedBy): bool
    {
        return $this->update([
            'prize_approved' => true,
            'prize_approved_at' => now(),
            'prize_approved_by' => $approvedBy,
        ]);
    }

    public function claimPrize(): bool
    {
        if (!$this->prize_approved || $this->prize_claimed) {
            return false;
        }

        return $this->update([
            'prize_claimed' => true,
            'prize_claimed_at' => now(),
            'prize_awarded' => true,
            'prize_awarded_at' => now(),
        ]);
    }

    public function isPrizeApproved(): bool
    {
        return $this->prize_approved;
    }

    public function isPrizeClaimed(): bool
    {
        return $this->prize_claimed;
    }

    public function isPrizePendingClaim(): bool
    {
        return $this->isWinner() && $this->isPrizeApproved() && !$this->isPrizeClaimed();
    }

    public function getFormattedPrizeAmount(): string
    {
        if (!$this->prize_amount || $this->prize_amount <= 0) {
            return 'No Prize';
        }

        return '$' . number_format($this->prize_amount, 2);
    }
}