<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailLogService
{
    public function send(
        Mailable $mailable,
        string $recipientEmail,
        ?string $recipientName = null,
        string $type = EmailLog::TYPE_GENERAL,
        ?User $user = null,
        array $metadata = []
    ): EmailLog {
        $log = EmailLog::create([
            'user_id' => $user?->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $this->getMailableSubject($mailable),
            'type' => $type,
            'status' => EmailLog::STATUS_PENDING,
            'mailable_class' => get_class($mailable),
            'metadata' => $metadata,
        ]);

        try {
            Mail::to($recipientEmail, $recipientName)->send($mailable);
            
            $log->update([
                'status' => EmailLog::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => EmailLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $log;
    }

    public function sendToUser(
        Mailable $mailable,
        User $user,
        string $type = EmailLog::TYPE_GENERAL,
        array $metadata = []
    ): EmailLog {
        return $this->send(
            $mailable,
            $user->email,
            $user->first_name . ' ' . $user->last_name,
            $type,
            $user,
            $metadata
        );
    }

    public function logManual(
        string $recipientEmail,
        string $subject,
        string $type,
        string $status,
        ?User $user = null,
        ?string $errorMessage = null,
        array $metadata = []
    ): EmailLog {
        return EmailLog::create([
            'user_id' => $user?->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $user ? $user->first_name . ' ' . $user->last_name : null,
            'subject' => $subject,
            'type' => $type,
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => $metadata,
            'sent_at' => $status === EmailLog::STATUS_SENT ? now() : null,
        ]);
    }

    protected function getMailableSubject(Mailable $mailable): string
    {
        try {
            if (method_exists($mailable, 'envelope')) {
                $envelope = $mailable->envelope();
                if ($envelope && $envelope->subject) {
                    return $envelope->subject;
                }
            }
            
            $reflection = new \ReflectionProperty($mailable, 'subject');
            $reflection->setAccessible(true);
            $subject = $reflection->getValue($mailable);
            
            if ($subject) {
                return $subject;
            }
        } catch (Throwable $e) {
        }

        return class_basename($mailable);
    }

    public static function getStats(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = EmailLog::query();
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = (clone $query)->count();
        $sent = (clone $query)->sent()->count();
        $failed = (clone $query)->failed()->count();
        $pending = (clone $query)->pending()->count();

        $byType = [];
        foreach (EmailLog::getTypes() as $type => $label) {
            $typeQuery = (clone $query)->ofType($type);
            $byType[$type] = [
                'label' => $label,
                'total' => (clone $typeQuery)->count(),
                'sent' => (clone $typeQuery)->sent()->count(),
                'failed' => (clone $typeQuery)->failed()->count(),
            ];
        }

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 1) : 0,
            'by_type' => $byType,
        ];
    }
}
