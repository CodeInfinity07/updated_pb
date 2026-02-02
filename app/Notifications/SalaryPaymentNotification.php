<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected float $amount;
    protected int $monthNumber;
    protected string $stageName;

    public function __construct(float $amount, int $monthNumber, string $stageName)
    {
        $this->amount = $amount;
        $this->monthNumber = $monthNumber;
        $this->stageName = $stageName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Monthly Salary Payment Received!')
            ->greeting("Congratulations {$notifiable->first_name}!")
            ->line("Your monthly salary payment of \${$this->amount} has been credited to your account.")
            ->line("This is your Month {$this->monthNumber} payment at {$this->stageName} level.")
            ->line('Keep up the great work and continue growing your team to maintain your salary!')
            ->action('View Your Progress', url('/salary'))
            ->salutation('Best regards, The Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Salary Payment Received',
            'message' => "Your monthly salary of \${$this->amount} has been credited to your account.",
            'amount' => $this->amount,
            'month_number' => $this->monthNumber,
            'stage_name' => $this->stageName,
            'action_url' => '/salary',
            'type' => 'salary_payment',
        ];
    }
}
