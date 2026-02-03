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
        'investment_plan_tier_id',
        'level_1_commission',
        'level_2_commission',
        'level_3_commission',
        'max_commission_cap',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_1_commission' => 'decimal:2',
            'level_2_commission' => 'decimal:2',
            'level_3_commission' => 'decimal:2',
            'max_commission_cap' => 'decimal:2',
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
        return $this->belongsTo(InvestmentPlanTier::class, 'investment_plan_tier_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    public function getFormattedLevel1CommissionAttribute(): string
    {
        return $this->level_1_commission . '%';
    }

    public function getFormattedLevel2CommissionAttribute(): string
    {
        return $this->level_2_commission . '%';
    }

    public function getFormattedLevel3CommissionAttribute(): string
    {
        return $this->level_3_commission . '%';
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
        return $query->where('investment_plan_tier_id', $tierId);
    }
}
