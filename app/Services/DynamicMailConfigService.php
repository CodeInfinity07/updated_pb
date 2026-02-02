<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class DynamicMailConfigService
{
    /**
     * Apply mail configuration from .env to Laravel's mail configuration
     * This ensures the mail config is properly set at runtime
     */
    public static function configure()
    {
        try {
            // Use .env values directly
            $mailConfig = [
                'mailer' => env('MAIL_MAILER', 'smtp'),
                'host' => env('MAIL_HOST', 'smtp.hostinger.com'),
                'port' => (int) env('MAIL_PORT', 587),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@predictionbot.net'),
                'from_name' => env('MAIL_FROM_NAME', 'OnyxRock'),
            ];

            // Apply configuration
            Config::set([
                'mail.default' => $mailConfig['mailer'],
                'mail.mailers.smtp.host' => $mailConfig['host'],
                'mail.mailers.smtp.port' => $mailConfig['port'],
                'mail.mailers.smtp.encryption' => $mailConfig['encryption'],
                'mail.mailers.smtp.username' => $mailConfig['username'],
                'mail.mailers.smtp.password' => $mailConfig['password'],
                'mail.mailers.smtp.timeout' => 30,
                'mail.mailers.smtp.verify_peer' => false,
                'mail.mailers.smtp.verify_peer_name' => false,
                'mail.from.address' => $mailConfig['from_address'],
                'mail.from.name' => $mailConfig['from_name'],
            ]);

            // Force Laravel to recreate mail manager to pick up new config
            app()->forgetInstance('mail.manager');
            app()->forgetInstance('mailer');
            
            Log::info('Mail configuration applied from .env', [
                'host' => $mailConfig['host'],
                'port' => $mailConfig['port'],
                'from' => $mailConfig['from_address'],
                'username' => $mailConfig['username'],
                'password_set' => !empty($mailConfig['password'])
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to apply mail settings', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if mail is properly configured
     */
    public static function isConfigured(): bool
    {
        return !empty(env('MAIL_HOST')) && !empty(env('MAIL_USERNAME'));
    }

    /**
     * Check if database has mail settings (always false now since we use .env)
     */
    public static function hasDatabaseOverrides(): bool
    {
        return false;
    }

    /**
     * Get current mail configuration being used
     */
    public static function getCurrentConfig()
    {
        return [
            'source' => 'env',
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'password_set' => !empty(config('mail.mailers.smtp.password'))
        ];
    }
}
