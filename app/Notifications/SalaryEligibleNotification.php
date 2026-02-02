<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryEligibleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You Are Eligible for the Monthly Salary Program!')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line('Great news! You have met all the requirements to join our Monthly Salary Program.')
            ->line('By joining, you can earn a recurring monthly income by maintaining 35% growth in your team each month.')
            ->action('Apply Now', url('/salary'))
            ->line('Don\'t miss this opportunity to earn extra income every month!')
            ->salutation('Best regards, The Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Salary Program Eligibility',
            'message' => 'You are now eligible to join the Monthly Salary Program! Apply now to start earning.',
            'action_url' => '/salary',
            'type' => 'salary_eligible',
        ];
    }
}
