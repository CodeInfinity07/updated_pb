<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardPosition;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UserPrizeClaimController extends Controller
{
    public function getPendingPrizes(): JsonResponse
    {
        $user = Auth::user();
        
        $pendingPrizes = LeaderboardPosition::where('user_id', $user->id)
            ->prizePendingClaim()
            ->with('leaderboard:id,title,type')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'leaderboard_name' => $position->leaderboard->title ?? 'Leaderboard',
                    'position' => $position->position,
                    'position_display' => $position->position_display,
                    'prize_amount' => $position->prize_amount,
                    'formatted_amount' => '$' . number_format($position->prize_amount, 2),
                    'approved_at' => $position->prize_approved_at?->format('M d, Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'prizes' => $pendingPrizes,
            'count' => $pendingPrizes->count(),
            'total_amount' => $pendingPrizes->sum('prize_amount'),
        ]);
    }

    public function claimPrize(LeaderboardPosition $position): JsonResponse
    {
        $user = Auth::user();

        if ($position->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'This prize does not belong to you.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $lockedPosition = LeaderboardPosition::where('id', $position->id)
                ->where('user_id', $user->id)
                ->where('prize_approved', true)
                ->where('prize_claimed', false)
                ->where('prize_amount', '>', 0)
                ->lockForUpdate()
                ->first();

            if (!$lockedPosition) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This prize is not available for claiming or has already been claimed.'
                ]);
            }

            $affectedRows = LeaderboardPosition::where('id', $lockedPosition->id)
                ->where('user_id', $user->id)
                ->where('prize_approved', true)
                ->where('prize_claimed', false)
                ->update([
                    'prize_claimed' => true,
                    'prize_claimed_at' => now(),
                    'prize_awarded' => true,
                    'prize_awarded_at' => now(),
                ]);

            if ($affectedRows === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Prize has already been claimed.'
                ]);
            }

            $leaderboard = $lockedPosition->leaderboard;
            $wallet = $user->getOrCreateWallet('USDT_BEP20');
            $wallet->increment('balance', $lockedPosition->prize_amount);

            Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'LB-' . strtoupper(uniqid()),
                'type' => Transaction::TYPE_LEADERBOARD_PRIZE,
                'amount' => $lockedPosition->prize_amount,
                'currency' => 'USDT_BEP20',
                'status' => Transaction::STATUS_COMPLETED,
                'description' => "Claimed leaderboard prize from '{$leaderboard->title}' - Position #{$lockedPosition->position}",
                'metadata' => [
                    'leaderboard_id' => $leaderboard->id,
                    'leaderboard_title' => $leaderboard->title,
                    'position' => $lockedPosition->position,
                    'position_id' => $lockedPosition->id,
                    'claimed_at' => now()->toISOString(),
                ],
                'processed_at' => now(),
            ]);

            DB::commit();

            Log::info('User claimed leaderboard prize', [
                'user_id' => $user->id,
                'position_id' => $lockedPosition->id,
                'amount' => $lockedPosition->prize_amount,
                'leaderboard_id' => $leaderboard->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Congratulations! Prize of $' . number_format($lockedPosition->prize_amount, 2) . ' has been added to your wallet.',
                'amount' => $lockedPosition->prize_amount,
                'new_balance' => $wallet->fresh()->balance,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to claim prize', [
                'user_id' => $user->id,
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to claim prize. Please try again.'
            ], 500);
        }
    }

    public function claimAllPrizes(): JsonResponse
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $pendingPrizes = LeaderboardPosition::where('user_id', $user->id)
                ->where('prize_approved', true)
                ->where('prize_claimed', false)
                ->where('prize_amount', '>', 0)
                ->lockForUpdate()
                ->with('leaderboard')
                ->get();

            if ($pendingPrizes->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No prizes available to claim.'
                ]);
            }

            $totalAmount = 0;
            $claimedCount = 0;
            $wallet = $user->getOrCreateWallet('USDT_BEP20');

            foreach ($pendingPrizes as $position) {
                $affectedRows = LeaderboardPosition::where('id', $position->id)
                    ->where('user_id', $user->id)
                    ->where('prize_approved', true)
                    ->where('prize_claimed', false)
                    ->where('prize_amount', '>', 0)
                    ->update([
                        'prize_claimed' => true,
                        'prize_claimed_at' => now(),
                        'prize_awarded' => true,
                        'prize_awarded_at' => now(),
                    ]);

                if ($affectedRows === 0) {
                    continue;
                }

                $leaderboard = $position->leaderboard;

                $wallet->increment('balance', $position->prize_amount);

                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'LB-' . strtoupper(uniqid()),
                    'type' => Transaction::TYPE_LEADERBOARD_PRIZE,
                    'amount' => $position->prize_amount,
                    'currency' => 'USDT_BEP20',
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => "Claimed leaderboard prize from '{$leaderboard->title}' - Position #{$position->position}",
                    'metadata' => [
                        'leaderboard_id' => $leaderboard->id,
                        'leaderboard_title' => $leaderboard->title,
                        'position' => $position->position,
                        'position_id' => $position->id,
                        'claimed_at' => now()->toISOString(),
                    ],
                    'processed_at' => now(),
                ]);

                $totalAmount += $position->prize_amount;
                $claimedCount++;
            }

            if ($claimedCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No prizes were claimed. They may have already been claimed.'
                ]);
            }

            DB::commit();

            Log::info('User claimed all leaderboard prizes', [
                'user_id' => $user->id,
                'count' => $claimedCount,
                'total_amount' => $totalAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Congratulations! {$claimedCount} prize(s) totaling \$" . number_format($totalAmount, 2) . " have been added to your wallet.",
                'claimed_count' => $claimedCount,
                'total_amount' => $totalAmount,
                'new_balance' => $wallet->fresh()->balance,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to claim all prizes', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to claim prizes. Please try again.'
            ], 500);
        }
    }
}
