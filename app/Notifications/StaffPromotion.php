<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffPromotion extends Notification implements ShouldQueue
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
        $role = ucfirst($this->data['new_role'] ?? 'Staff');
        $promotedBy = $this->data['promoted_by'] ?? 'Admin';
        $reason = $this->data['reason'] ?? null;

        $message = (new MailMessage)
            ->subject('Congratulations! You\'ve Been Promoted to ' . $role)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Great news! You have been promoted to the role of **' . $role . '**.')
            ->line('This promotion was made by ' . $promotedBy . '.');

        if ($reason) {
            $message->line('**Reason:** ' . $reason);
        }

        $message->line('You now have access to additional features and responsibilities in the admin panel.')
            ->action('Access Admin Panel', url('/admin/dashboard'))
            ->line('Thank you for being a valued member of our team!');

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'staff_promotion',
            'title' => 'You\'ve Been Promoted!',
            'message' => 'Congratulations! You have been promoted to ' . ucfirst($this->data['new_role'] ?? 'Staff') . '.',
            'new_role' => $this->data['new_role'] ?? null,
            'promoted_by' => $this->data['promoted_by'] ?? null,
            'reason' => $this->data['reason'] ?? null,
            'url' => '/admin/dashboard',
        ];
    }
}
