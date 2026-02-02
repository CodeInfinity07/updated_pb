<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRank extends Model
{
    protected $fillable = [
        'user_id',
        'rank_id',
        'achieved_at',
        'reward_paid',
        'reward_paid_at',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'reward_paid' => 'boolean',
        'reward_paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function scopePaid($query)
    {
        return $query->where('reward_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('reward_paid', false);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'reward_paid' => true,
            'reward_paid_at' => now(),
        ]);
    }
}
