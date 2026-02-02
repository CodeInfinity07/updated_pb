<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBlocked extends Notification implements ShouldQueue
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
        $reason = $this->data['reason'] ?? null;

        $message = (new MailMessage)
            ->subject('Account Access Suspended')
            ->greeting('Hello ' . $notifiable->first_name)
            ->line('Your account access has been temporarily suspended.');

        if ($reason) {
            $message->line('**Reason:** ' . $reason);
        }

        $message->line('If you believe this is an error, please contact our support team.')
            ->action('Contact Support', url('/support'))
            ->line('We apologize for any inconvenience.');

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'account_blocked',
            'title' => 'Account Suspended',
            'message' => 'Your account access has been suspended.',
            'reason' => $this->data['reason'] ?? null,
            'url' => '/support',
        ];
    }
}
