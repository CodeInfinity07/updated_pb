<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminChatStats extends Model
{
    protected $fillable = [
        'admin_id',
        'total_chats_handled',
        'chats_closed_today',
        'average_response_time',
        'last_active_at',
        'stats_date',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'stats_date' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function getOrCreateForAdmin(int $adminId): self
    {
        return self::firstOrCreate(
            ['admin_id' => $adminId],
            [
                'total_chats_handled' => 0,
                'chats_closed_today' => 0,
                'stats_date' => now()->toDateString(),
            ]
        );
    }

    public function resetDailyCounterIfNeeded(): void
    {
        if ($this->stats_date && !$this->stats_date->isToday()) {
            $this->update([
                'chats_closed_today' => 0,
                'stats_date' => now()->toDateString(),
            ]);
        }
    }
}
