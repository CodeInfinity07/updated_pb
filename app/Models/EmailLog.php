<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'type',
        'status',
        'error_message',
        'mailable_class',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    const TYPE_WELCOME = 'welcome';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_VERIFICATION = 'verification';
    const TYPE_NOTIFICATION = 'notification';
    const TYPE_MASS_EMAIL = 'mass_email';
    const TYPE_TRANSACTION = 'transaction';
    const TYPE_KYC = 'kyc';
    const TYPE_SUPPORT = 'support';
    const TYPE_GENERAL = 'general';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_WELCOME => 'Welcome',
            self::TYPE_PASSWORD_RESET => 'Password Reset',
            self::TYPE_VERIFICATION => 'Verification',
            self::TYPE_NOTIFICATION => 'Notification',
            self::TYPE_MASS_EMAIL => 'Mass Email',
            self::TYPE_TRANSACTION => 'Transaction',
            self::TYPE_KYC => 'KYC',
            self::TYPE_SUPPORT => 'Support',
            self::TYPE_GENERAL => 'General',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_SENT => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_PENDING => 'warning',
            default => 'secondary',
        };
    }
}
