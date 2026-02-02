<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $targetTeam;
    protected int $achievedTeam;
    protected int $targetDirect;
    protected int $achievedDirect;

    public function __construct(int $targetTeam, int $achievedTeam, int $targetDirect, int $achievedDirect)
    {
        $this->targetTeam = $targetTeam;
        $this->achievedTeam = $achievedTeam;
        $this->targetDirect = $targetDirect;
        $this->achievedDirect = $achievedDirect;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Salary Program Update')
            ->greeting("Hello {$notifiable->first_name},")
            ->line('Unfortunately, you did not meet your monthly targets for the Salary Program.');

        if ($this->achievedTeam < $this->targetTeam) {
            $message->line("Team members: {$this->achievedTeam} / {$this->targetTeam} required");
        }

        if ($this->achievedDirect < $this->targetDirect) {
            $message->line("New direct referrals: {$this->achievedDirect} / {$this->targetDirect} required");
        }

        return $message
            ->line('Don\'t worry! You can re-apply for the program once you meet the Stage 1 requirements again.')
            ->action('View Salary Program', url('/salary'))
            ->salutation('Best regards, The Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Salary Program Target Not Met',
            'message' => 'You did not meet your monthly targets. You can re-apply when eligible.',
            'target_team' => $this->targetTeam,
            'achieved_team' => $this->achievedTeam,
            'target_direct' => $this->targetDirect,
            'achieved_direct' => $this->achievedDirect,
            'action_url' => '/salary',
            'type' => 'salary_failed',
        ];
    }
}
