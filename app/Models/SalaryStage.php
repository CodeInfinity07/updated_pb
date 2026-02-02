<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStage extends Model
{
    protected $fillable = [
        'name',
        'stage_order',
        'direct_members_required',
        'self_deposit_required',
        'team_required',
        'salary_amount',
        'is_active',
    ];

    protected $casts = [
        'self_deposit_required' => 'decimal:2',
        'salary_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function payouts(): HasMany
    {
        return $this->hasMany(SalaryPayout::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('stage_order');
    }

    public static function getNextStage(int $currentStage): ?self
    {
        return self::active()
            ->where('stage_order', '>', $currentStage)
            ->orderBy('stage_order')
            ->first();
    }

    public static function getStageByOrder(int $order): ?self
    {
        return self::where('stage_order', $order)->first();
    }
}
