<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OnlineUsersService
{
    private const CACHE_PREFIX = 'user_online_';
    private const KEYS_CACHE_KEY = 'online_user_keys';
    private const ACTIVITY_WINDOW_MINUTES = 5;

    public static function trackActivity(int $userId): void
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        
        Cache::put($cacheKey, [
            'user_id' => $userId,
            'last_activity' => now()->timestamp,
        ], now()->addMinutes(10));

        $keys = Cache::get(self::KEYS_CACHE_KEY, []);
        if (!in_array($userId, $keys)) {
            $keys[] = $userId;
            Cache::put(self::KEYS_CACHE_KEY, $keys, now()->addMinutes(15));
        }
    }

    public static function getOnlineUserIds(): array
    {
        $onlineUserIds = [];
        $threshold = now()->subMinutes(self::ACTIVITY_WINDOW_MINUTES)->timestamp;
        
        $keys = Cache::get(self::KEYS_CACHE_KEY, []);
        $activeKeys = [];
        
        foreach ($keys as $userId) {
            $cacheKey = self::CACHE_PREFIX . $userId;
            $data = Cache::get($cacheKey);
            
            if ($data && $data['last_activity'] >= $threshold) {
                $onlineUserIds[] = $userId;
                $activeKeys[] = $userId;
            }
        }

        if (count($activeKeys) !== count($keys)) {
            Cache::put(self::KEYS_CACHE_KEY, $activeKeys, now()->addMinutes(15));
        }
        
        return $onlineUserIds;
    }

    public static function getOnlineCount(): int
    {
        return count(self::getOnlineUserIds());
    }

    public static function isUserOnline(int $userId): bool
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        $data = Cache::get($cacheKey);
        
        if (!$data) {
            return false;
        }
        
        $threshold = now()->subMinutes(self::ACTIVITY_WINDOW_MINUTES)->timestamp;
        return $data['last_activity'] >= $threshold;
    }
}
