<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'subject',
        'status',
        'last_message_at',
        'admin_last_read_at',
        'user_last_read_at',
        'assigned_at',
        'closed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'admin_last_read_at' => 'datetime',
        'user_last_read_at' => 'datetime',
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function unreadMessagesForAdmin(): int
    {
        return $this->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->count();
    }

    public function unreadMessagesForUser(): int
    {
        return $this->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->count();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('admin_id');
    }
}
