<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPlanProfitSharing extends Model
{
    use HasFactory;

    protected $table = 'investment_plan_profit_sharing';

    protected $fillable = [
        'investment_plan_id',
        'tier_id',
        'percentage',
        'frequency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function investmentPlan(): BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(InvestmentPlanTier::class, 'tier_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    public function getFormattedPercentageAttribute(): string
    {
        return $this->percentage . '%';
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlan($query, $planId)
    {
        return $query->where('investment_plan_id', $planId);
    }

    public function scopeForTier($query, $tierId)
    {
        return $query->where('tier_id', $tierId);
    }
}
