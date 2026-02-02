<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    return (int) $user->id === (int) $conversation->user_id;
});

Broadcast::channel('user.chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

