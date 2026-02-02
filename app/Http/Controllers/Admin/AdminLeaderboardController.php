<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminLeaderboardController extends Controller
{
    protected $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Display leaderboards dashboard.
     */
    public function index(Request $request): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');

        // Build query
        $query = Leaderboard::with(['creator', 'positions']);

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Apply sorting
        $query->orderBy($sort_by, $sort_order);

        // Get leaderboards with pagination
        $leaderboards = $query->paginate(10)->withQueryString();

        // Get statistics
        $stats = $this->leaderboardService->getStatistics();

        return view('admin.leaderboards.index', compact(
            'leaderboards',
            'stats',
            'search',
            'status',
            'user',
            'sort_by',
            'sort_order'
        ));
    }

    /**
     * Show create leaderboard form.
     */
    public function create(): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        return view('admin.leaderboards.create', compact('user'));
    }

    /**
     * Store new leaderboard.
     */

    public function store(Request $request): RedirectResponse
    {
        $this->checkAdminAccess();

        // Pre-filter empty tiers before validation
        if ($request->input('type') === 'target' && $request->has('target_tiers')) {
            $tiers = array_values(array_filter($request->input('target_tiers'), function ($tier) {
                return !empty($tier['target']) && !empty($tier['amount']);
            }));
            $request->merge(['target_tiers' => $tiers]);
        }

        // Base validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:active,inactive',
            'show_to_users' => 'boolean',
            'max_positions' => 'required|integer|min:1|max:100',
            'referral_type' => 'required|string|in:direct,multi_level,all,first_level,verified_only',
            'max_referral_level' => 'nullable|integer|min:2|max:20',
            'min_investment_amount' => 'nullable|numeric|min:0',
            'type' => 'required|string|in:competitive,target',
        ];

        // Require max_referral_level for multi_level referral type
        if ($request->input('referral_type') === 'multi_level') {
            $rules['max_referral_level'] = 'required|integer|min:2|max:20';
        }

        // Conditional validation based on leaderboard type
        if ($request->input('type') === 'competitive') {
            $rules['prize_structure'] = 'nullable|array';
            $rules['prize_structure.*.position'] = 'nullable|integer|min:1';
            $rules['prize_structure.*.amount'] = 'nullable|numeric|min:0';
        } else if ($request->input('type') === 'target') {
            $rules['target_tiers'] = 'required|array|min:1';
            $rules['target_tiers.*.target'] = 'required|integer|min:1|max:10000';
            $rules['target_tiers.*.amount'] = 'required|numeric|min:0.01|max:100000';
            $rules['max_winners'] = 'nullable|integer|min:1|max:10000';
        }

        $validated = $request->validate($rules);

        try {
            // Process data based on type
            if ($validated['type'] === 'competitive') {
                // Process prize structure for competitive leaderboards
                if (isset($validated['prize_structure'])) {
                    // Filter out empty prizes and reindex array
                    $validated['prize_structure'] = array_values(array_filter($validated['prize_structure'], function ($prize) {
                        return isset($prize['amount']) && $prize['amount'] > 0;
                    }));

                    // If no valid prizes, set to null
                    if (empty($validated['prize_structure'])) {
                        $validated['prize_structure'] = null;
                    }
                }

                // Clear target-based fields
                $validated['target_referrals'] = null;
                $validated['target_prize_amount'] = null;
                $validated['target_tiers'] = null;
                $validated['max_winners'] = null;
            } else {
                // For target-based leaderboards, clear prize structure
                $validated['prize_structure'] = null;

                // Process target_tiers - filter empty and sort by target
                if (isset($validated['target_tiers'])) {
                    $tiers = array_values(array_filter($validated['target_tiers'], function ($tier) {
                        return isset($tier['target']) && $tier['target'] > 0 && 
                               isset($tier['amount']) && $tier['amount'] > 0;
                    }));
                    
                    // Sort by target ascending
                    usort($tiers, function ($a, $b) {
                        return $a['target'] - $b['target'];
                    });
                    
                    $validated['target_tiers'] = $tiers;
                    
                    // Set legacy fields from first tier for backwards compatibility
                    if (!empty($tiers)) {
                        $validated['target_referrals'] = $tiers[0]['target'];
                        $validated['target_prize_amount'] = $tiers[0]['amount'];
                    }
                }

                // Set default max_winners if not provided
                if (!isset($validated['max_winners'])) {
                    $validated['max_winners'] = null; // No limit
                }
            }

            // Create leaderboard
            $leaderboard = Leaderboard::create(array_merge($validated, [
                'created_by' => auth()->id(),
            ]));

            Log::info('Leaderboard created', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'type' => $leaderboard->type,
                'created_by' => auth()->id()
            ]);

            return redirect()
                ->route('admin.leaderboards.show', $leaderboard)
                ->with('success', 'Leaderboard created successfully!');

        } catch (Exception $e) {
            Log::error('Failed to create leaderboard', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->withErrors(['error' => 'Failed to create leaderboard. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show leaderboard details.
     */
    public function show(Leaderboard $leaderboard): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $leaderboard->load(['creator', 'positions.user', 'prizeDistributor']);

        // Get leaderboard statistics
        $leaderboardStats = [
            'total_participants' => $leaderboard->getParticipantsCount(),
            'total_winners' => $leaderboard->getWinnersCount(),
            'total_prize_amount' => $leaderboard->total_prize_amount,
            'awarded_prize_amount' => $leaderboard->positions()->where('prize_awarded', true)->sum('prize_amount'),
            'pending_prize_amount' => $leaderboard->positions()->where('prize_awarded', false)->where('prize_amount', '>', 0)->sum('prize_amount'),
            'duration_days' => $leaderboard->start_date->diffInDays($leaderboard->end_date) + 1,
            'days_remaining' => $leaderboard->days_remaining,
            'progress' => $leaderboard->getProgress(),
        ];

        // Get top positions
        $topPositions = $leaderboard->positions()
            ->with('user')
            ->orderBy('position')
            ->limit(10)
            ->get();

        return view('admin.leaderboards.show', compact(
            'leaderboard',
            'leaderboardStats',
            'topPositions',
            'user'
        ));
    }

    /**
     * Show edit leaderboard form.
     */
    public function edit(Leaderboard $leaderboard): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        return view('admin.leaderboards.edit', compact('leaderboard', 'user'));
    }

    /**
     * Update leaderboard.
     */
    public function update(Request $request, Leaderboard $leaderboard): RedirectResponse
    {
        $this->checkAdminAccess();

        $leaderboardType = $request->input('type', $leaderboard->type);

        // Pre-filter empty tiers before validation
        if ($leaderboardType === 'target' && $request->has('target_tiers')) {
            $tiers = array_values(array_filter($request->input('target_tiers'), function ($tier) {
                return !empty($tier['target']) && !empty($tier['amount']);
            }));
            $request->merge(['target_tiers' => $tiers]);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:active,inactive,completed',
            'show_to_users' => 'boolean',
            'max_positions' => 'required|integer|min:1|max:100',
            'referral_type' => 'required|string|in:direct,multi_level,all,first_level,verified_only',
            'max_referral_level' => 'nullable|integer|min:2|max:20',
            'min_investment_amount' => 'nullable|numeric|min:0',
            'type' => 'required|string|in:competitive,target',
        ];

        // Require max_referral_level for multi_level referral type
        if ($request->input('referral_type') === 'multi_level') {
            $rules['max_referral_level'] = 'required|integer|min:2|max:20';
        }

        if ($leaderboardType === 'competitive') {
            $rules['prize_structure'] = 'nullable|array';
            $rules['prize_structure.*.position'] = 'nullable|integer|min:1';
            $rules['prize_structure.*.from_position'] = 'nullable|integer|min:1';
            $rules['prize_structure.*.to_position'] = 'nullable|integer|min:1';
            $rules['prize_structure.*.amount'] = 'nullable|numeric|min:0';
        } else {
            $rules['target_tiers'] = 'required|array|min:1';
            $rules['target_tiers.*.target'] = 'required|integer|min:1|max:10000';
            $rules['target_tiers.*.amount'] = 'required|numeric|min:0.01|max:100000';
            $rules['max_winners'] = 'nullable|integer|min:1|max:10000';
        }

        $validated = $request->validate($rules);

        try {
            if ($validated['type'] === 'competitive') {
                if (isset($validated['prize_structure'])) {
                    $validated['prize_structure'] = array_values(array_filter($validated['prize_structure'], function ($prize) {
                        return isset($prize['amount']) && $prize['amount'] > 0;
                    }));
                    if (empty($validated['prize_structure'])) {
                        $validated['prize_structure'] = null;
                    }
                }
                $validated['target_referrals'] = null;
                $validated['target_prize_amount'] = null;
                $validated['target_tiers'] = null;
                $validated['max_winners'] = null;
            } else {
                $validated['prize_structure'] = null;
                
                // Process target_tiers - filter empty and sort by target
                if (isset($validated['target_tiers'])) {
                    $tiers = array_values(array_filter($validated['target_tiers'], function ($tier) {
                        return isset($tier['target']) && $tier['target'] > 0 && 
                               isset($tier['amount']) && $tier['amount'] > 0;
                    }));
                    
                    // Sort by target ascending
                    usort($tiers, function ($a, $b) {
                        return $a['target'] - $b['target'];
                    });
                    
                    $validated['target_tiers'] = $tiers;
                    
                    // Set legacy fields from first tier for backwards compatibility
                    if (!empty($tiers)) {
                        $validated['target_referrals'] = $tiers[0]['target'];
                        $validated['target_prize_amount'] = $tiers[0]['amount'];
                    }
                }
                
                if (!isset($validated['max_winners'])) {
                    $validated['max_winners'] = null;
                }
            }

            $leaderboard->update(array_merge($validated, [
                'show_to_users' => $request->has('show_to_users'),
            ]));

            Log::info('Leaderboard updated', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'type' => $leaderboard->type,
                'updated_by' => auth()->id()
            ]);

            return redirect()
                ->route('admin.leaderboards.show', $leaderboard)
                ->with('success', 'Leaderboard updated successfully!');

        } catch (Exception $e) {
            Log::error('Failed to update leaderboard', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->withErrors(['error' => 'Failed to update leaderboard. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Delete leaderboard.
     */
    public function destroy(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            // Check if prizes have been distributed
            if ($leaderboard->prizes_distributed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete leaderboard with distributed prizes.'
                ], 400);
            }

            $leaderboard->delete();

            Log::info('Leaderboard deleted', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leaderboard deleted successfully.'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete leaderboard', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leaderboard.'
            ], 500);
        }
    }

    /**
     * Toggle leaderboard status.
     */
    public function toggleStatus(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            $newStatus = match ($leaderboard->status) {
                'active' => 'inactive',
                'inactive' => 'active',
                'completed' => 'completed', // Cannot change completed status
            };

            if ($newStatus === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status of completed leaderboard.'
                ], 400);
            }

            $leaderboard->update(['status' => $newStatus]);

            Log::info('Leaderboard status toggled', [
                'leaderboard_id' => $leaderboard->id,
                'old_status' => $leaderboard->status,
                'new_status' => $newStatus,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Leaderboard {$newStatus} successfully.",
                'status' => $newStatus,
                'badge_class' => $leaderboard->status_badge_class
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle leaderboard status', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update leaderboard status.'
            ], 500);
        }
    }

    /**
     * Calculate leaderboard positions.
     */
    public function calculatePositions(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            $this->leaderboardService->calculatePositions($leaderboard);

            return response()->json([
                'success' => true,
                'message' => 'Leaderboard positions calculated successfully.',
                'participants' => $leaderboard->getParticipantsCount()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to calculate leaderboard positions', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate positions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribute prizes to winners.
     */
    public function distributePrizes(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            if (!$leaderboard->canDistributePrizes()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prizes cannot be distributed for this leaderboard.'
                ], 400);
            }

            $success = $this->leaderboardService->distributePrizes($leaderboard);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prizes distributed successfully to all winners.',
                    'total_amount' => $leaderboard->positions()->where('prize_awarded', true)->sum('prize_amount')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to distribute prizes.'
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Failed to distribute leaderboard prizes', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to distribute prizes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete leaderboard.
     */
    public function complete(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            if (!$leaderboard->canComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leaderboard cannot be completed.'
                ], 400);
            }

            // Calculate final positions
            $this->leaderboardService->calculatePositions($leaderboard);

            // Mark as completed
            $leaderboard->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Leaderboard completed successfully.',
                'participants' => $leaderboard->getParticipantsCount()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to complete leaderboard', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete leaderboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $this->checkAdminAccess();

        $stats = $this->leaderboardService->getStatistics();

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Auto-complete expired leaderboards.
     */
    public function autoCompleteExpired(): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            $completed = $this->leaderboardService->autoCompleteExpiredLeaderboards();

            return response()->json([
                'success' => true,
                'message' => "Auto-completed {$completed} expired leaderboards.",
                'completed_count' => $completed
            ]);

        } catch (Exception $e) {
            Log::error('Failed to auto-complete expired leaderboards', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-complete expired leaderboards.'
            ], 500);
        }
    }

    /**
     * Check admin access.
     */
    private function checkAdminAccess(): void
    {
        if (!auth()->user()->canAccessAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
    }

    /**
     * Calculate positions for all active leaderboards.
     */
    public function calculateAllActivePositions(): JsonResponse
    {
        $this->checkAdminAccess();

        try {
            $activeLeaderboards = Leaderboard::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($activeLeaderboards as $leaderboard) {
                try {
                    $this->leaderboardService->calculatePositions($leaderboard);
                    $successCount++;

                    Log::info('Positions calculated for leaderboard', [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'participants' => $leaderboard->getParticipantsCount()
                    ]);
                } catch (Exception $e) {
                    $failureCount++;
                    $errors[] = [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to calculate positions for leaderboard', [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Bulk position calculation completed', [
                'total_leaderboards' => $activeLeaderboards->count(),
                'successful' => $successCount,
                'failed' => $failureCount,
                'calculated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Position calculation completed. Success: {$successCount}, Failed: {$failureCount}",
                'total_leaderboards' => $activeLeaderboards->count(),
                'successful' => $successCount,
                'failed' => $failureCount,
                'errors' => $errors
            ]);

        } catch (Exception $e) {
            Log::error('Failed to calculate positions for active leaderboards', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate positions for active leaderboards: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate positions for all active leaderboards (for console usage).
     * This version doesn't require authentication and returns array instead of JSON.
     */
    public function calculateAllActivePositionsConsole(): array
    {
        try {
            $activeLeaderboards = Leaderboard::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($activeLeaderboards as $leaderboard) {
                try {
                    $this->leaderboardService->calculatePositions($leaderboard);
                    $successCount++;

                    Log::info('Positions calculated for leaderboard', [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'participants' => $leaderboard->getParticipantsCount(),
                        'source' => 'console'
                    ]);
                } catch (Exception $e) {
                    $failureCount++;
                    $errors[] = [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to calculate positions for leaderboard', [
                        'leaderboard_id' => $leaderboard->id,
                        'title' => $leaderboard->title,
                        'error' => $e->getMessage(),
                        'source' => 'console'
                    ]);
                }
            }

            Log::info('Bulk position calculation completed', [
                'total_leaderboards' => $activeLeaderboards->count(),
                'successful' => $successCount,
                'failed' => $failureCount,
                'source' => 'console'
            ]);

            return [
                'success' => $failureCount === 0,
                'message' => "Position calculation completed. Success: {$successCount}, Failed: {$failureCount}",
                'total_leaderboards' => $activeLeaderboards->count(),
                'successful' => $successCount,
                'failed' => $failureCount,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            Log::error('Failed to calculate positions for active leaderboards', [
                'error' => $e->getMessage(),
                'source' => 'console'
            ]);

            return [
                'success' => false,
                'message' => 'Failed to calculate positions for active leaderboards: ' . $e->getMessage(),
                'total_leaderboards' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [['error' => $e->getMessage()]]
            ];
        }
    }

    /**
     * Display pending prizes page for admin review and release
     */
    public function pendingPrizes(Request $request): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        // Get all completed leaderboards with pending approval (only those that have ended)
        $leaderboardsWithPendingPrizes = Leaderboard::where('end_date', '<', now())
        ->whereHas('positions', function($query) {
            $query->where('prize_amount', '>', 0)
                  ->where('prize_approved', false);
        })
        ->with(['positions' => function($query) {
            $query->where('prize_amount', '>', 0)
                  ->where('prize_approved', false)
                  ->with('user')
                  ->orderBy('position');
        }])
        ->orderBy('end_date', 'desc')
        ->get();

        // Get ongoing leaderboards with current positions (upcoming prizes)
        $ongoingLeaderboards = Leaderboard::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('status', 'active')
            ->whereHas('positions', function($query) {
                $query->where('prize_amount', '>', 0);
            })
            ->with(['positions' => function($query) {
                $query->where('prize_amount', '>', 0)
                      ->with('user')
                      ->orderBy('position');
            }])
            ->orderBy('end_date', 'asc')
            ->get();

        // Calculate summary statistics (only for leaderboards that have ended - pending approval)
        $totalPendingAmount = LeaderboardPosition::where('prize_amount', '>', 0)
            ->where('prize_approved', false)
            ->whereHas('leaderboard', function($q) {
                $q->where('end_date', '<', now());
            })
            ->sum('prize_amount');
        $totalPendingCount = LeaderboardPosition::where('prize_amount', '>', 0)
            ->where('prize_approved', false)
            ->whereHas('leaderboard', function($q) {
                $q->where('end_date', '<', now());
            })
            ->count();
        $totalAwardedThisMonth = LeaderboardPosition::prizeAwarded()
            ->whereMonth('prize_awarded_at', now()->month)
            ->whereYear('prize_awarded_at', now()->year)
            ->sum('prize_amount');

        // Calculate upcoming prize amounts from ongoing leaderboards
        $totalUpcomingAmount = $ongoingLeaderboards->sum(function($lb) {
            return $lb->positions->sum('prize_amount');
        });
        $totalUpcomingCount = $ongoingLeaderboards->sum(function($lb) {
            return $lb->positions->count();
        });

        return view('admin.leaderboards.pending-prizes', compact(
            'leaderboardsWithPendingPrizes',
            'ongoingLeaderboards',
            'totalPendingAmount',
            'totalPendingCount',
            'totalAwardedThisMonth',
            'totalUpcomingAmount',
            'totalUpcomingCount',
            'user'
        ));
    }

    /**
     * Approve a single prize for user to claim
     */
    public function approvePrize(LeaderboardPosition $position): JsonResponse
    {
        $this->checkAdminAccess();

        if ($position->prize_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Prize has already been approved'
            ]);
        }

        if (!$position->isWinner()) {
            return response()->json([
                'success' => false,
                'message' => 'This position has no prize to approve'
            ]);
        }

        try {
            $user = $position->user;
            $leaderboard = $position->leaderboard;

            if (!$user) {
                throw new Exception('User not found for this position');
            }

            $position->markPrizeAsApproved(Auth::id());

            Log::info('Prize approved by admin', [
                'position_id' => $position->id,
                'user_id' => $user->id,
                'amount' => $position->prize_amount,
                'leaderboard_id' => $leaderboard->id,
                'approved_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Prize of \${$position->prize_amount} approved for {$user->full_name}. User can now claim it."
            ]);

        } catch (Exception $e) {
            Log::error('Failed to approve prize', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve prize: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Release a single prize to a winner (legacy - direct release without claim)
     */
    public function releasePrize(LeaderboardPosition $position): JsonResponse
    {
        $this->checkAdminAccess();

        if (!$position->isPrizePending()) {
            return response()->json([
                'success' => false,
                'message' => 'Prize is not pending or has already been awarded'
            ]);
        }

        DB::beginTransaction();

        try {
            $user = $position->user;
            $leaderboard = $position->leaderboard;

            if (!$user) {
                throw new Exception('User not found for this position');
            }

            // Add prize to user's wallet (USDT_BEP20)
            $wallet = $user->getOrCreateWallet('USDT_BEP20');
            $wallet->increment('balance', $position->prize_amount);

            // Mark prize as awarded
            $position->update([
                'prize_awarded' => true,
                'prize_awarded_at' => now(),
            ]);

            // Create transaction record
            $this->createPrizeTransaction($user, $position, $leaderboard);

            DB::commit();

            Log::info('Prize manually released by admin', [
                'position_id' => $position->id,
                'user_id' => $user->id,
                'amount' => $position->prize_amount,
                'leaderboard_id' => $leaderboard->id,
                'released_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Prize of \${$position->prize_amount} released to {$user->full_name}"
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to release prize', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to release prize: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Release all prizes for a leaderboard
     */
    public function releaseAllPrizes(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        $pendingPrizes = $leaderboard->positions()
            ->where('prize_amount', '>', 0)
            ->where('prize_awarded', false)
            ->with('user')
            ->get();

        if ($pendingPrizes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending prizes to release for this leaderboard'
            ]);
        }

        DB::beginTransaction();

        try {
            $successCount = 0;
            $totalAmount = 0;

            foreach ($pendingPrizes as $position) {
                $user = $position->user;
                
                if (!$user) {
                    Log::warning('User not found for position during bulk release', [
                        'position_id' => $position->id
                    ]);
                    continue;
                }

                // Add prize to user's wallet (USDT_BEP20)
                $wallet = $user->getOrCreateWallet('USDT_BEP20');
                $wallet->increment('balance', $position->prize_amount);

                // Mark prize as awarded
                $position->update([
                    'prize_awarded' => true,
                    'prize_awarded_at' => now(),
                ]);

                // Create transaction record
                $this->createPrizeTransaction($user, $position, $leaderboard);

                $successCount++;
                $totalAmount += $position->prize_amount;
            }

            // Mark leaderboard prizes as distributed
            $leaderboard->update([
                'prizes_distributed' => true,
                'prizes_distributed_at' => now(),
                'prizes_distributed_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('All prizes released for leaderboard by admin', [
                'leaderboard_id' => $leaderboard->id,
                'success_count' => $successCount,
                'total_amount' => $totalAmount,
                'released_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Released {$successCount} prizes totaling \${$totalAmount} for {$leaderboard->title}"
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to release all prizes', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to release prizes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Approve all prizes for a leaderboard (users can then claim them)
     */
    public function approveAllPrizes(Leaderboard $leaderboard): JsonResponse
    {
        $this->checkAdminAccess();

        $pendingPrizes = $leaderboard->positions()
            ->where('prize_amount', '>', 0)
            ->where('prize_approved', false)
            ->with('user')
            ->get();

        if ($pendingPrizes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending prizes to approve for this leaderboard'
            ]);
        }

        try {
            $successCount = 0;
            $totalAmount = 0;

            foreach ($pendingPrizes as $position) {
                $user = $position->user;
                
                if (!$user) {
                    Log::warning('User not found for position during bulk approval', [
                        'position_id' => $position->id
                    ]);
                    continue;
                }

                $position->markPrizeAsApproved(Auth::id());

                $successCount++;
                $totalAmount += $position->prize_amount;
            }

            Log::info('All prizes approved for leaderboard by admin', [
                'leaderboard_id' => $leaderboard->id,
                'success_count' => $successCount,
                'total_amount' => $totalAmount,
                'approved_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Approved {$successCount} prizes totaling \${$totalAmount} for {$leaderboard->title}. Users can now claim them."
            ]);

        } catch (Exception $e) {
            Log::error('Failed to approve all prizes', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve prizes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create transaction record for prize release
     */
    private function createPrizeTransaction($user, LeaderboardPosition $position, Leaderboard $leaderboard): void
    {
        try {
            $transactionId = 'LEADERBOARD_' . $leaderboard->id . '_POS_' . $position->position . '_' . time();
            
            $description = $leaderboard->type === 'target' 
                ? "Target Achievement Prize - {$leaderboard->title} (Reached {$position->referral_count} qualified referrals)"
                : "Leaderboard Prize - Position #{$position->position} in {$leaderboard->title}";

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => \App\Models\Transaction::TYPE_LEADERBOARD_PRIZE,
                'amount' => $position->prize_amount,
                'currency' => 'USDT_BEP20',
                'status' => \App\Models\Transaction::STATUS_COMPLETED,
                'description' => $description,
                'transaction_id' => $transactionId,
                'metadata' => json_encode([
                    'leaderboard_id' => $leaderboard->id,
                    'leaderboard_title' => $leaderboard->title,
                    'leaderboard_type' => $leaderboard->type,
                    'position' => $position->position,
                    'referral_count' => $position->referral_count,
                    'released_by' => Auth::id(),
                    'released_at' => now()->toIso8601String(),
                ])
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create prize transaction record', [
                'user_id' => $user->id,
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}