<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycStatusUpdated extends Notification implements ShouldQueue
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
        $status = $this->data['status'] ?? 'updated';
        $reason = $this->data['reason'] ?? null;

        $message = (new MailMessage)
            ->subject('KYC Verification Status Update');

        if ($status === 'approved' || $status === 'verified') {
            $message->greeting('Congratulations ' . $notifiable->first_name . '!')
                ->line('Your KYC verification has been **approved**.')
                ->line('You now have full access to all platform features.');
        } elseif ($status === 'rejected') {
            $message->greeting('Hello ' . $notifiable->first_name)
                ->line('Unfortunately, your KYC verification has been **rejected**.');
            if ($reason) {
                $message->line('**Reason:** ' . $reason);
            }
            $message->line('Please submit new documents to complete verification.');
        } else {
            $message->greeting('Hello ' . $notifiable->first_name)
                ->line('Your KYC verification status has been updated to: **' . ucfirst($status) . '**');
        }

        $message->action('View KYC Status', url('/kyc'))
            ->line('If you have questions, please contact support.');

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'kyc_status_update',
            'title' => 'KYC Verification Update',
            'message' => 'Your KYC status has been updated to: ' . ucfirst($this->data['status'] ?? 'updated'),
            'status' => $this->data['status'] ?? null,
            'reason' => $this->data['reason'] ?? null,
            'url' => '/kyc',
        ];
    }
}
