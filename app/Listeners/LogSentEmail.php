<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentEmail
{
    public function handle(MessageSent $event): void
    {
        try {
            $recipients = [];
            $subject = 'No Subject';
            $messageId = null;
            
            if (isset($event->sent) && method_exists($event->sent, 'getSymfonySentMessage')) {
                $sentMessage = $event->sent->getSymfonySentMessage();
                if ($sentMessage) {
                    $messageId = $sentMessage->getMessageId();
                    $originalMessage = $sentMessage->getOriginalMessage();
                    if ($originalMessage) {
                        $recipients = array_merge(
                            $recipients,
                            $this->extractRecipients($originalMessage->getTo()),
                            $this->extractRecipients($originalMessage->getCc()),
                            $this->extractRecipients($originalMessage->getBcc())
                        );
                        $subject = $originalMessage->getSubject() ?? 'No Subject';
                    }
                }
            }
            
            if (empty($recipients) && isset($event->message)) {
                $message = $event->message;
                $recipients = array_merge(
                    $recipients,
                    $this->extractRecipients($message->getTo()),
                    $this->extractRecipients($message->getCc()),
                    $this->extractRecipients($message->getBcc())
                );
                $subject = $message->getSubject() ?? 'No Subject';
            }
            
            if (empty($recipients)) {
                Log::warning('Could not extract recipients from email event');
                return;
            }
            
            $type = $this->determineEmailType($subject, $event->data ?? []);
            $mailable = isset($event->data['__mailable']) ? get_class($event->data['__mailable']) : null;
            
            foreach ($recipients as $recipient) {
                $user = User::where('email', $recipient['email'])->first();
                
                EmailLog::create([
                    'user_id' => $user?->id,
                    'recipient_email' => $recipient['email'],
                    'recipient_name' => $recipient['name'],
                    'subject' => $subject,
                    'type' => $type,
                    'status' => EmailLog::STATUS_SENT,
                    'mailable_class' => $mailable,
                    'metadata' => $messageId ? ['message_id' => $messageId] : null,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to log sent email: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
        }
    }
    
    protected function extractRecipients($addresses): array
    {
        $recipients = [];
        if ($addresses) {
            foreach ($addresses as $address) {
                $recipients[] = [
                    'email' => $address->getAddress(),
                    'name' => $address->getName(),
                ];
            }
        }
        return $recipients;
    }
    
    protected function determineEmailType(string $subject, array $data): string
    {
        $subjectLower = strtolower($subject);
        
        if (str_contains($subjectLower, 'welcome')) {
            return EmailLog::TYPE_WELCOME;
        }
        if (str_contains($subjectLower, 'password') || str_contains($subjectLower, 'reset')) {
            return EmailLog::TYPE_PASSWORD_RESET;
        }
        if (str_contains($subjectLower, 'verify') || str_contains($subjectLower, 'verification')) {
            return EmailLog::TYPE_VERIFICATION;
        }
        if (str_contains($subjectLower, 'kyc')) {
            return EmailLog::TYPE_KYC;
        }
        if (str_contains($subjectLower, 'transaction') || str_contains($subjectLower, 'deposit') || str_contains($subjectLower, 'withdraw')) {
            return EmailLog::TYPE_TRANSACTION;
        }
        if (str_contains($subjectLower, 'support') || str_contains($subjectLower, 'ticket')) {
            return EmailLog::TYPE_SUPPORT;
        }
        
        if (isset($data['campaign_id'])) {
            return EmailLog::TYPE_MASS_EMAIL;
        }
        
        return EmailLog::TYPE_GENERAL;
    }
}
