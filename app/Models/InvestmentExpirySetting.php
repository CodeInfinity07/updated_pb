<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvestmentExpirySetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'description'];

    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    public static function getJsonValue(string $key, $default = null)
    {
        $value = self::getValue($key);
        if ($value) {
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $default;
        }
        return $default;
    }

    public static function setValue(string $key, $value, string $description = null): bool
    {
        $data = ['setting_value' => is_array($value) ? json_encode($value) : $value];
        if ($description) {
            $data['description'] = $description;
        }
        
        self::updateOrCreate(['setting_key' => $key], $data);
        Cache::forget('investment_expiry_settings');
        return true;
    }

    public static function getBaseMultiplier(): int
    {
        return (int) self::getValue('base_multiplier', 3);
    }

    public static function getExtendedMultiplier(): int
    {
        return (int) self::getValue('extended_multiplier', 6);
    }

    public static function getBotFeeAmount(): float
    {
        return (float) self::getValue('bot_fee_amount', 10);
    }

    public static function getQualificationOption1(): array
    {
        return self::getJsonValue('qualification_option_1', ['type' => 'direct_referrals', 'count' => 30]);
    }

    public static function getQualificationOption2(): array
    {
        return self::getJsonValue('qualification_option_2', [
            'type' => 'tiered_referrals',
            'levels' => ['1' => 10, '2' => 8, '3' => 5, '4' => 3, '5' => 1]
        ]);
    }

    public static function getAllSettings(): array
    {
        return [
            'base_multiplier' => self::getBaseMultiplier(),
            'extended_multiplier' => self::getExtendedMultiplier(),
            'bot_fee_amount' => self::getBotFeeAmount(),
            'qualification_option_1' => self::getQualificationOption1(),
            'qualification_option_2' => self::getQualificationOption2(),
        ];
    }
}
