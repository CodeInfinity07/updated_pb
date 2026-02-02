<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name', 'PredictionBot');

        return (new MailMessage)
            ->subject('Welcome to ' . $appName . '!')
            ->greeting('Welcome ' . $notifiable->first_name . '!')
            ->line('Thank you for joining ' . $appName . '. We\'re excited to have you on board!')
            ->line('Here\'s what you can do next:')
            ->line('- Complete your KYC verification for full access')
            ->line('- Explore our investment plans')
            ->line('- Start referring friends to earn rewards')
            ->action('Get Started', url('/dashboard'))
            ->line('If you have any questions, our support team is here to help.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to ' . config('app.name', 'PredictionBot') . '!',
            'message' => 'Thank you for joining us. Get started by completing your profile.',
            'url' => '/dashboard',
        ];
    }
}
