<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewChatMessage;
use App\Events\ConversationUpdated;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\AdminChatStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminChatController extends Controller
{
    protected AdminChatStatsService $statsService;

    public function __construct(AdminChatStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index(): View
    {
        $admin = auth()->user();
        $isSuperAdmin = $admin->admin_role_id === 1;

        $chatData = $this->statsService->getConversationsForStaff($admin->id, $isSuperAdmin);
        $staffStats = $this->statsService->getStaffChatStats($admin->id);

        if ($isSuperAdmin) {
            $stats = [
                'total' => ChatConversation::count(),
                'open' => ChatConversation::where('status', 'open')->count(),
                'pending' => ChatConversation::where('status', 'pending')->count(),
                'unassigned' => ChatConversation::whereNull('admin_id')->whereIn('status', ['open', 'pending'])->count(),
            ];
        } else {
            $stats = [
                'total' => ChatConversation::where('admin_id', $admin->id)->count(),
                'open' => ChatConversation::where('admin_id', $admin->id)->where('status', 'open')->count(),
                'pending' => ChatConversation::where('admin_id', $admin->id)->where('status', 'pending')->count(),
                'unassigned' => ChatConversation::whereNull('admin_id')->whereIn('status', ['open', 'pending'])->count(),
            ];
        }

        return view('admin.chat.index', compact('chatData', 'staffStats', 'stats', 'isSuperAdmin'));
    }

    public function show(ChatConversation $conversation): View
    {
        $admin = auth()->user();
        $isSuperAdmin = $admin->admin_role_id === 1;

        $conversation->load(['user', 'admin', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        $conversation->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if (!$conversation->admin_id) {
            $this->statsService->assignChatToAdmin($conversation, $admin->id, $isSuperAdmin);
            $conversation->refresh();
        }

        $staffStats = $this->statsService->getStaffChatStats($admin->id);

        return view('admin.chat.show', compact('conversation', 'staffStats', 'isSuperAdmin'));
    }

    public function getConversations(Request $request): JsonResponse
    {
        $admin = auth()->user();
        $isSuperAdmin = $admin->admin_role_id === 1;

        $chatData = $this->statsService->getConversationsForStaff($admin->id, $isSuperAdmin);

        $formatConversation = function ($conv) {
            return [
                'id' => $conv->id,
                'user_name' => $conv->user ? $conv->user->first_name . ' ' . $conv->user->last_name : 'Unknown',
                'user_email' => $conv->user ? $conv->user->email : '',
                'status' => $conv->status,
                'admin_id' => $conv->admin_id,
                'admin_name' => $conv->admin ? $conv->admin->first_name . ' ' . $conv->admin->last_name : null,
                'unread_count' => $conv->unreadMessagesForAdmin(),
                'last_message' => $conv->latestMessage ? [
                    'message' => substr($conv->latestMessage->message, 0, 50),
                    'sender_type' => $conv->latestMessage->sender_type,
                    'created_at' => $conv->latestMessage->created_at->diffForHumans(),
                ] : null,
                'last_message_at' => $conv->last_message_at ? $conv->last_message_at->diffForHumans() : null,
            ];
        };

        return response()->json([
            'my_chats' => $chatData['my_chats']->map($formatConversation),
            'available_chats' => $chatData['available_chats']->map($formatConversation),
            'staff_stats' => $this->statsService->getStaffChatStats($admin->id),
        ]);
    }

    public function getMessages(ChatConversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        $conversation->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages' => $messages->map(function ($msg) {
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

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $admin = auth()->user();

        if (!$conversation->admin_id) {
            $isSuperAdmin = $admin->admin_role_id === 1;
            $this->statsService->assignChatToAdmin($conversation, $admin->id, $isSuperAdmin);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
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

    public function updateStatus(Request $request, ChatConversation $conversation): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,closed,pending',
        ]);

        $oldStatus = $conversation->status;
        $conversation->update(['status' => $request->status]);

        if ($request->status === 'closed' && $oldStatus !== 'closed') {
            $this->statsService->onChatClosed($conversation);
        }

        broadcast(new ConversationUpdated($conversation, 'status_changed'))->toOthers();

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, ChatConversation $conversation): JsonResponse
    {
        $admin = auth()->user();
        $isSuperAdmin = $admin->admin_role_id === 1;

        $assigned = $this->statsService->assignChatToAdmin($conversation, $admin->id, $isSuperAdmin);

        if (!$assigned) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to assign chat. Either it is already assigned or you have reached the maximum of 3 open chats.',
            ], 422);
        }

        broadcast(new ConversationUpdated($conversation, 'assigned'))->toOthers();

        return response()->json(['success' => true]);
    }

    public function staffChats(): View
    {
        $staffStats = $this->statsService->getAllStaffStats();

        $totalStats = [
            'total_staff' => count($staffStats),
            'total_open_chats' => ChatConversation::whereIn('status', ['open', 'pending'])->count(),
            'total_handled_today' => collect($staffStats)->sum(fn($s) => $s['stats']['closed_today']),
            'unassigned_chats' => ChatConversation::whereNull('admin_id')->whereIn('status', ['open', 'pending'])->count(),
        ];

        return view('admin.chat.staff-chats', compact('staffStats', 'totalStats'));
    }

    public function staffChatHistory(User $staff): View
    {
        $conversations = $this->statsService->getStaffChatHistory($staff->id);
        $staffChatStats = $this->statsService->getStaffChatStats($staff->id);

        return view('admin.chat.staff-history', compact('staff', 'conversations', 'staffChatStats'));
    }
}
