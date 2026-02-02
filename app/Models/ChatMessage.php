<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getSenderNameAttribute(): string
    {
        $user = User::find($this->sender_id);
        if (!$user) {
            return $this->sender_type === 'user' ? 'Unknown User' : 'Support Staff';
        }
        return $user->first_name . ' ' . $user->last_name;
    }
}
