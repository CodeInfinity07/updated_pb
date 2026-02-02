<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDummyUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('excluded_from_stats', true)
            ->with('profile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        $dummyUsers = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => User::where('excluded_from_stats', true)->count(),
            'withdraw_disabled' => User::where('excluded_from_stats', true)->where('withdraw_disabled', true)->count(),
            'roi_disabled' => User::where('excluded_from_stats', true)->where('roi_disabled', true)->count(),
            'commission_disabled' => User::where('excluded_from_stats', true)->where('commission_disabled', true)->count(),
            'referral_disabled' => User::where('excluded_from_stats', true)->where('referral_disabled', true)->count(),
        ];

        return view('admin.dummy-users.index', compact('dummyUsers', 'stats'));
    }

    public function toggleRestriction(Request $request, User $user)
    {
        $request->validate([
            'field' => 'required|in:withdraw_disabled,roi_disabled,commission_disabled,referral_disabled',
        ]);

        if (!$user->excluded_from_stats) {
            return response()->json(['success' => false, 'message' => 'User is not a dummy user'], 400);
        }

        $field = $request->field;
        $user->$field = !$user->$field;
        $user->save();

        Log::info('Dummy user restriction toggled', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'field' => $field,
            'new_value' => $user->$field,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $field)) . ' has been ' . ($user->$field ? 'enabled' : 'disabled'),
            'value' => $user->$field,
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'action' => 'required|in:enable_withdraw_disabled,disable_withdraw_disabled,enable_roi_disabled,disable_roi_disabled,enable_commission_disabled,disable_commission_disabled,enable_referral_disabled,disable_referral_disabled,enable_all,disable_all',
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;

        $users = User::whereIn('id', $userIds)
            ->where('excluded_from_stats', true)
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid dummy users found'], 400);
        }

        $updateData = [];
        
        if ($action === 'enable_all') {
            $updateData = [
                'withdraw_disabled' => true,
                'roi_disabled' => true,
                'commission_disabled' => true,
                'referral_disabled' => true,
            ];
        } elseif ($action === 'disable_all') {
            $updateData = [
                'withdraw_disabled' => false,
                'roi_disabled' => false,
                'commission_disabled' => false,
                'referral_disabled' => false,
            ];
        } else {
            preg_match('/(enable|disable)_(.+)/', $action, $matches);
            if (count($matches) === 3) {
                $updateData[$matches[2]] = $matches[1] === 'enable';
            }
        }

        User::whereIn('id', $users->pluck('id'))->update($updateData);

        Log::info('Bulk dummy user restriction action', [
            'admin_id' => auth()->id(),
            'user_ids' => $users->pluck('id')->toArray(),
            'action' => $action,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bulk action applied to ' . $users->count() . ' users',
            'affected_count' => $users->count(),
        ]);
    }

    public function markAsDummy(Request $request, User $user)
    {
        $user->excluded_from_stats = true;
        $user->save();

        Log::info('User marked as dummy', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User has been marked as dummy',
        ]);
    }

    public function unmarkAsDummy(Request $request, User $user)
    {
        $user->excluded_from_stats = false;
        $user->withdraw_disabled = false;
        $user->roi_disabled = false;
        $user->commission_disabled = false;
        $user->referral_disabled = false;
        $user->save();

        Log::info('User unmarked as dummy', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User has been removed from dummy users',
        ]);
    }

    public function stats(Request $request)
    {
        $startDate = $request->filled('start_date') 
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay() 
            : null;
        $endDate = $request->filled('end_date') 
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay() 
            : null;

        $excludedUserIds = User::where('excluded_from_stats', true)->pluck('id');

        $depositQuery = \App\Models\Transaction::whereIn('user_id', $excludedUserIds)
            ->where('type', \App\Models\Transaction::TYPE_DEPOSIT)
            ->where('status', \App\Models\Transaction::STATUS_COMPLETED);

        $withdrawalQuery = \App\Models\Transaction::whereIn('user_id', $excludedUserIds)
            ->where('type', \App\Models\Transaction::TYPE_WITHDRAWAL)
            ->where('status', \App\Models\Transaction::STATUS_COMPLETED);

        if ($startDate) {
            $depositQuery->where('created_at', '>=', $startDate);
            $withdrawalQuery->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $depositQuery->where('created_at', '<=', $endDate);
            $withdrawalQuery->where('created_at', '<=', $endDate);
        }

        $stats = [
            'total_users' => $excludedUserIds->count(),
            'total_deposits' => (float) (clone $depositQuery)->sum('amount'),
            'deposit_count' => (clone $depositQuery)->count(),
            'total_withdrawals' => (float) (clone $withdrawalQuery)->sum('amount'),
            'withdrawal_count' => (clone $withdrawalQuery)->count(),
        ];
        $stats['net_position'] = $stats['total_deposits'] - $stats['total_withdrawals'];

        $recentDeposits = (clone $depositQuery)
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentWithdrawals = (clone $withdrawalQuery)
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dummy-users.stats', compact(
            'stats', 
            'recentDeposits', 
            'recentWithdrawals',
            'startDate',
            'endDate'
        ));
    }
}
