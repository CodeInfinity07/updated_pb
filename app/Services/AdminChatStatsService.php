<?php

namespace App\Services;

use App\Models\AdminChatStats;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminChatStatsService
{
    const MAX_OPEN_CHATS = 3;

    public function getOpenChatsCount(int $adminId): int
    {
        return ChatConversation::where('admin_id', $adminId)
            ->whereIn('status', ['open', 'pending'])
            ->count();
    }

    public function canAcceptNewChat(int $adminId): bool
    {
        return $this->getOpenChatsCount($adminId) < self::MAX_OPEN_CHATS;
    }

    public function assignChatToAdmin(ChatConversation $conversation, int $adminId, bool $isSuperAdmin = false): bool
    {
        return DB::transaction(function () use ($conversation, $adminId, $isSuperAdmin) {
            $conv = ChatConversation::where('id', $conversation->id)
                ->whereNull('admin_id')
                ->lockForUpdate()
                ->first();

            if (!$conv) {
                return false;
            }

            if (!$isSuperAdmin && !$this->canAcceptNewChat($adminId)) {
                return false;
            }

            $conv->update([
                'admin_id' => $adminId,
                'assigned_at' => now(),
            ]);

            $stats = AdminChatStats::getOrCreateForAdmin($adminId);
            $stats->update(['last_active_at' => now()]);

            return true;
        });
    }

    public function onChatClosed(ChatConversation $conversation): void
    {
        if (!$conversation->admin_id) {
            return;
        }

        $conversation->update(['closed_at' => now()]);

        $stats = AdminChatStats::getOrCreateForAdmin($conversation->admin_id);
        $stats->resetDailyCounterIfNeeded();

        $stats->increment('total_chats_handled');
        $stats->increment('chats_closed_today');
        $stats->update(['last_active_at' => now()]);

        $this->updateAverageResponseTime($stats, $conversation);
    }

    protected function updateAverageResponseTime(AdminChatStats $stats, ChatConversation $conversation): void
    {
        if (!$conversation->assigned_at || !$conversation->closed_at) {
            return;
        }

        $duration = abs($conversation->assigned_at->diffInSeconds($conversation->closed_at));
        
        if ($duration <= 0) {
            $duration = 1;
        }
        
        $totalHandled = $stats->total_chats_handled;

        if ($stats->average_response_time && $totalHandled > 1) {
            $newAverage = (($stats->average_response_time * ($totalHandled - 1)) + $duration) / $totalHandled;
            $stats->update(['average_response_time' => (int) $newAverage]);
        } else {
            $stats->update(['average_response_time' => $duration]);
        }
    }

    public function getStaffChatStats(int $adminId): array
    {
        $stats = AdminChatStats::getOrCreateForAdmin($adminId);
        $stats->resetDailyCounterIfNeeded();

        return [
            'open_chats' => $this->getOpenChatsCount($adminId),
            'max_chats' => self::MAX_OPEN_CHATS,
            'total_handled' => $stats->total_chats_handled,
            'closed_today' => $stats->chats_closed_today,
            'average_response_time' => $stats->average_response_time,
            'can_accept_new' => $this->canAcceptNewChat($adminId),
        ];
    }

    public function getConversationsForStaff(int $adminId, bool $isSuperAdmin = false): array
    {
        $myChats = ChatConversation::with(['user', 'latestMessage'])
            ->where('admin_id', $adminId)
            ->orderByDesc('last_message_at')
            ->get();

        $availableChats = collect();

        if ($isSuperAdmin || $this->canAcceptNewChat($adminId)) {
            $availableChats = ChatConversation::with(['user', 'latestMessage'])
                ->whereNull('admin_id')
                ->whereIn('status', ['open', 'pending'])
                ->orderByDesc('last_message_at')
                ->get();
        }

        return [
            'my_chats' => $myChats,
            'available_chats' => $availableChats,
        ];
    }

    public function getAllStaffStats(): array
    {
        return User::whereNotNull('admin_role_id')
            ->with(['adminChatStats'])
            ->get()
            ->map(function ($admin) {
                $stats = $admin->adminChatStats ?? new AdminChatStats(['admin_id' => $admin->id]);
                return [
                    'admin' => $admin,
                    'stats' => [
                        'open_chats' => $this->getOpenChatsCount($admin->id),
                        'total_handled' => $stats->total_chats_handled ?? 0,
                        'closed_today' => $stats->chats_closed_today ?? 0,
                        'average_response_time' => $stats->average_response_time,
                        'last_active_at' => $stats->last_active_at,
                    ],
                ];
            })
            ->toArray();
    }

    public function getStaffChatHistory(int $adminId): \Illuminate\Database\Eloquent\Collection
    {
        return ChatConversation::with(['user'])
            ->where('admin_id', $adminId)
            ->orderByDesc('updated_at')
            ->get();
    }
}
