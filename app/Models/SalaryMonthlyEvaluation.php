<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryMonthlyEvaluation extends Model
{
    protected $fillable = [
        'user_id',
        'salary_application_id',
        'salary_stage_id',
        'month_number',
        'period_start',
        'period_end',
        'target_team',
        'achieved_team_new',
        'starting_team_count',
        'target_direct_new',
        'achieved_direct_new',
        'starting_direct_count',
        'passed',
        'salary_amount',
        'salary_paid',
        'paid_at',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'passed' => 'boolean',
        'salary_amount' => 'decimal:2',
        'salary_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(SalaryApplication::class, 'salary_application_id');
    }

    public function salaryStage(): BelongsTo
    {
        return $this->belongsTo(SalaryStage::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    public function scopePaid($query)
    {
        return $query->where('salary_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('salary_paid', false)->where('passed', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function isPaid(): bool
    {
        return $this->salary_paid;
    }

    public function markPaid(int $transactionId): void
    {
        $this->update([
            'salary_paid' => true,
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);
    }

    public function metTeamTarget(): bool
    {
        return $this->achieved_team_new >= $this->target_team;
    }

    public function metDirectTarget(): bool
    {
        return $this->achieved_direct_new >= $this->target_direct_new;
    }
}
