<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPixel extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'pixel_id',
        'pixel_code',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_GOOGLE = 'google';
    public const PLATFORM_TIKTOK = 'tiktok';

    public static function getAvailablePlatforms(): array
    {
        return [
            self::PLATFORM_FACEBOOK => [
                'name' => 'Facebook Pixel',
                'icon' => 'logos:facebook',
                'description' => 'Track conversions and create audiences for Facebook ads',
                'id_label' => 'Pixel ID',
                'id_placeholder' => 'Enter your Facebook Pixel ID',
            ],
            self::PLATFORM_GOOGLE => [
                'name' => 'Google Analytics / Tag',
                'icon' => 'logos:google-analytics',
                'description' => 'Track website analytics and conversions with Google',
                'id_label' => 'Tracking ID / Measurement ID',
                'id_placeholder' => 'e.g., G-XXXXXXXXXX or UA-XXXXXXXX-X',
            ],
            self::PLATFORM_TIKTOK => [
                'name' => 'TikTok Pixel',
                'icon' => 'logos:tiktok-icon',
                'description' => 'Track conversions and optimize TikTok ad campaigns',
                'id_label' => 'Pixel ID',
                'id_placeholder' => 'Enter your TikTok Pixel ID',
            ],
        ];
    }

    public static function getPixel(string $platform): ?self
    {
        return self::where('platform', $platform)->first();
    }

    public static function getActivePixels()
    {
        return self::where('is_active', true)->get();
    }

    public function getPlatformInfo(): array
    {
        return self::getAvailablePlatforms()[$this->platform] ?? [];
    }
}
