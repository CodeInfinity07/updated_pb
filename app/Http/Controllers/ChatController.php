<?php

namespace App\Http\Controllers;

use App\Events\NewChatMessage;
use App\Events\ConversationUpdated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function getConversation(): JsonResponse
    {
        $user = auth()->user();
        
        $conversation = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->first();

        if (!$conversation) {
            $recentClosed = ChatConversation::where('user_id', $user->id)
                ->where('status', 'closed')
                ->with(['messages' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->latest('updated_at')
                ->first();
            
            if ($recentClosed) {
                return response()->json([
                    'conversation' => [
                        'id' => $recentClosed->id,
                        'status' => $recentClosed->status,
                        'admin_id' => $recentClosed->admin_id,
                    ],
                    'messages' => $recentClosed->messages->map(function ($msg) {
                        return [
                            'id' => $msg->id,
                            'sender_type' => $msg->sender_type,
                            'sender_name' => $msg->sender_name,
                            'message' => $msg->message,
                            'created_at' => $msg->created_at->toIso8601String(),
                        ];
                    }),
                ]);
            }
            
            return response()->json([
                'conversation' => null,
                'messages' => [],
            ]);
        }

        $conversation->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'admin_id' => $conversation->admin_id,
            ],
            'messages' => $conversation->messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_name,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $user = auth()->user();

        $existing = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->first();

        if ($existing) {
            return response()->json([
                'conversation' => [
                    'id' => $existing->id,
                    'status' => $existing->status,
                ],
            ]);
        }

        $conversation = ChatConversation::create([
            'user_id' => $user->id,
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        broadcast(new ConversationUpdated($conversation, 'created'))->toOthers();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
            ],
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $conversation = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
                'status' => 'open',
            ]);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new NewChatMessage($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function closeConversation(): JsonResponse
    {
        $user = auth()->user();

        $conversation = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->first();

        if ($conversation) {
            $conversation->update(['status' => 'closed']);
            broadcast(new ConversationUpdated($conversation, 'closed'))->toOthers();
        }

        return response()->json(['success' => true]);
    }

    public function getUnreadCount(): JsonResponse
    {
        $user = auth()->user();

        $conversation = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->first();

        $count = 0;
        if ($conversation) {
            $count = $conversation->unreadMessagesForUser();
        }

        return response()->json(['unread_count' => $count]);
    }

    public function markRead(): JsonResponse
    {
        $user = auth()->user();

        $conversation = ChatConversation::where('user_id', $user->id)
            ->where('status', '!=', 'closed')
            ->first();

        if ($conversation) {
            $conversation->messages()
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);
            
            $conversation->update(['user_last_read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
