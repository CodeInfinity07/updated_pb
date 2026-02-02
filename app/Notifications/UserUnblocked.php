<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserUnblocked extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Account Access Restored')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Good news! Your account access has been restored.')
            ->line('You can now log in and use all platform features.')
            ->action('Login to Your Account', url('/login'))
            ->line('Thank you for your patience.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'account_unblocked',
            'title' => 'Account Restored',
            'message' => 'Your account access has been restored.',
            'url' => '/dashboard',
        ];
    }
}
