<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'impersonated_user_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
        'reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function impersonatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonated_user_id');
    }

    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('impersonated_user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            if ($this->ended_at && $this->started_at) {
                $seconds = $this->ended_at->diffInSeconds($this->started_at);
            } else {
                return 'Active';
            }
        } else {
            $seconds = $this->duration_seconds;
        }

        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
    }
}
