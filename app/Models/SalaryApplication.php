<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SalaryApplication extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_FAILED = 'failed';
    const STATUS_GRADUATED = 'graduated';

    protected $fillable = [
        'user_id',
        'salary_stage_id',
        'applied_at',
        'baseline_team_count',
        'baseline_direct_count',
        'baseline_self_deposit',
        'current_period_start',
        'current_period_end',
        'current_target_team',
        'current_target_direct_new',
        'months_completed',
        'status',
        'failed_at',
        'graduated_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'baseline_self_deposit' => 'decimal:2',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'failed_at' => 'datetime',
        'graduated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryStage(): BelongsTo
    {
        return $this->belongsTo(SalaryStage::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SalaryMonthlyEvaluation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function markFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
        ]);
    }

    public function markGraduated(): void
    {
        $this->update([
            'status' => self::STATUS_GRADUATED,
            'graduated_at' => now(),
        ]);
    }

    public function setNextMonthTargets(int $stageTeamRequired, int $currentTeamCount): void
    {
        $nextPeriodStart = Carbon::parse($this->current_period_end)->addDay();
        $nextPeriodEnd = $nextPeriodStart->copy()->endOfMonth();
        
        // Target is 35% of stage requirement (NEW members to add)
        $newTargetTeam = (int) ceil($stageTeamRequired * 0.35);

        $this->update([
            'baseline_team_count' => $currentTeamCount, // Update baseline to current team for next period
            'current_period_start' => $nextPeriodStart,
            'current_period_end' => $nextPeriodEnd,
            'current_target_team' => $newTargetTeam,
            'current_target_direct_new' => 3,
            'months_completed' => $this->months_completed + 1,
        ]);
    }

    public function getCurrentStage(): SalaryStage
    {
        return $this->salaryStage;
    }

    public static function getActiveForUser(int $userId): ?self
    {
        return self::active()->forUser($userId)->first();
    }

    public static function hasActiveApplication(int $userId): bool
    {
        return self::active()->forUser($userId)->exists();
    }
}
