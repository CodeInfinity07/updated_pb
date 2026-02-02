<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatConversation $conversation;
    public string $action;

    public function __construct(ChatConversation $conversation, string $action = 'updated')
    {
        $this->conversation = $conversation;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.chat.' . $this->conversation->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'status' => $this->conversation->status,
            'admin_id' => $this->conversation->admin_id,
            'action' => $this->action,
            'unread_count' => $this->conversation->unreadMessagesForAdmin(),
        ];
    }
}
