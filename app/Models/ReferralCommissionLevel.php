<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCommissionLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'percentage',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'percentage' => 'decimal:2',
            'is_active' => 'boolean'
        ];
    }

    public static function getDefaultLevels(): array
    {
        return [
            ['level' => 1, 'percentage' => 5.00, 'is_active' => true],
            ['level' => 2, 'percentage' => 3.00, 'is_active' => true],
            ['level' => 3, 'percentage' => 2.00, 'is_active' => true],
            ['level' => 4, 'percentage' => 1.50, 'is_active' => true],
            ['level' => 5, 'percentage' => 1.00, 'is_active' => true],
            ['level' => 6, 'percentage' => 0.75, 'is_active' => true],
            ['level' => 7, 'percentage' => 0.50, 'is_active' => true],
            ['level' => 8, 'percentage' => 0.40, 'is_active' => true],
            ['level' => 9, 'percentage' => 0.30, 'is_active' => true],
            ['level' => 10, 'percentage' => 0.20, 'is_active' => true],
        ];
    }

    public static function seedDefaults(): void
    {
        foreach (self::getDefaultLevels() as $levelData) {
            self::updateOrCreate(
                ['level' => $levelData['level']],
                $levelData
            );
        }
    }

    public static function getPercentageForLevel(int $level): float
    {
        $setting = self::where('level', $level)
            ->where('is_active', true)
            ->first();
        
        return $setting ? floatval($setting->percentage) : 0;
    }

    public static function getAllActivePercentages(): array
    {
        return self::where('is_active', true)
            ->orderBy('level')
            ->pluck('percentage', 'level')
            ->toArray();
    }
}
