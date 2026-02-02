<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionSetting;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class AdminCommissionController extends Controller
{
    /**
     * Display commission settings dashboard.
     */
    public function index(): View
    {
        try {
            $user = \Auth::user();

            $commissionTiers = CommissionSetting::orderBy('level')
                ->get()
                ->map(function ($tier) {
                    $tier->users_count = User::whereHas('profile', function ($query) use ($tier) {
                        $query->where('level', $tier->level);
                    })->count();
                    return $tier;
                });

            // Get platform statistics
            $totalUsers = User::count();
            $totalActiveUsers = User::where('status', 'active')->count();
            $totalCommissionsPaid = Transaction::where('type', 'commission')->where('status', 'completed')->sum('amount') ?? 0;

            // Add Level 0 users count
            $level0Count = User::whereHas('profile', function ($query) {
                $query->where('level', 0);
            })->count();

            // Calculate commission simulation for $1000 transaction
            $simulationAmount = 1000;
            $commissionSimulation = [];

            foreach ($commissionTiers as $tier) {
                $commissionSimulation[$tier->level] = [
                    'level_1' => ($simulationAmount * ($tier->commission_level_1 ?? 0)) / 100,
                    'level_2' => ($simulationAmount * ($tier->commission_level_2 ?? 0)) / 100,
                    'level_3' => ($simulationAmount * ($tier->commission_level_3 ?? 0)) / 100,
                ];
                $commissionSimulation[$tier->level]['total'] =
                    $commissionSimulation[$tier->level]['level_1'] +
                    $commissionSimulation[$tier->level]['level_2'] +
                    $commissionSimulation[$tier->level]['level_3'];
            }

            return view('admin.referrals.commission.index', compact(
                'commissionTiers',
                'totalUsers',
                'totalActiveUsers',
                'totalCommissionsPaid',
                'simulationAmount',
                'commissionSimulation',
                'level0Count',
                'user'
            ));

        } catch (Exception $e) {
            Log::error('Commission settings page load failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.referrals.commission.index', [
                'commissionTiers' => collect([]),
                'totalUsers' => 0,
                'totalActiveUsers' => 0,
                'totalCommissionsPaid' => 0,
                'simulationAmount' => 1000,
                'commissionSimulation' => [],
                'level0Count' => 0
            ])->with('error', 'Failed to load commission settings.');
        }
    }

    /**
     * Store a newly created tier.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'level' => 'required|integer|min:1|unique:commission_settings,level',
            'name' => 'required|string|max:255',
            'min_investment' => 'required|numeric|min:0',
            'min_direct_referrals' => 'required|integer|min:0',
            'min_indirect_referrals' => 'required|integer|min:0',
            'commission_level_1' => 'required|numeric|min:0|max:100',
            'commission_level_2' => 'required|numeric|min:0|max:100',
            'commission_level_3' => 'required|numeric|min:0|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $data['sort_order'] = CommissionSetting::max('sort_order') + 1;
            $data['is_active'] = $request->boolean('is_active', true);

            $tier = CommissionSetting::create($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commission tier created successfully.',
                    'data' => $tier
                ]);
            }

            return redirect()->route('admin.commission.index')
                ->with('success', 'Commission tier created successfully.');

        } catch (Exception $e) {
            Log::error('Commission tier creation failed', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create commission tier.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create commission tier.')
                ->withInput();
        }
    }

    /**
     * Update the specified tier.
     */
    public function update(Request $request, CommissionSetting $commissionSetting)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'level' => ['required', 'integer', 'min:1', Rule::unique('commission_settings')->ignore($commissionSetting->id)],
            'name' => 'required|string|max:255',
            'min_investment' => 'required|numeric|min:0',
            'min_direct_referrals' => 'required|integer|min:0',
            'min_indirect_referrals' => 'required|integer|min:0',
            'commission_level_1' => 'required|numeric|min:0|max:100',
            'commission_level_2' => 'required|numeric|min:0|max:100',
            'commission_level_3' => 'required|numeric|min:0|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $data['is_active'] = $request->boolean('is_active', false);

            $commissionSetting->update($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Commission tier updated successfully.',
                    'data' => $commissionSetting->fresh()
                ]);
            }

            return redirect()->route('admin.commission.index')
                ->with('success', 'Commission tier updated successfully.');

        } catch (Exception $e) {
            Log::error('Commission tier update failed', [
                'tier_id' => $commissionSetting->id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update commission tier.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update commission tier.')
                ->withInput();
        }
    }

    /**
     * Remove the specified tier.
     */
    public function destroy(CommissionSetting $commissionSetting)
    {
        try {
            // Check if any users are currently assigned to this tier
            $usersCount = User::whereHas('profile', function ($query) use ($commissionSetting) {
                $query->where('level', $commissionSetting->level);
            })->count();

            if ($usersCount > 0) {
                $message = "Cannot delete tier. {$usersCount} users are currently assigned to this tier.";

                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return redirect()->route('admin.commission.index')
                    ->with('error', $message);
            }

            $tierName = $commissionSetting->name;
            $commissionSetting->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Commission tier '{$tierName}' deleted successfully."
                ]);
            }

            return redirect()->route('admin.commission.index')
                ->with('success', "Commission tier '{$tierName}' deleted successfully.");

        } catch (Exception $e) {
            Log::error('Commission tier deletion failed', [
                'tier_id' => $commissionSetting->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete commission tier.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete commission tier.');
        }
    }

    /**
     * Toggle tier status.
     */
    public function toggleStatus(CommissionSetting $commissionSetting): JsonResponse
    {
        try {
            $newStatus = !$commissionSetting->is_active;
            $commissionSetting->update(['is_active' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => $newStatus ? 'Tier activated successfully.' : 'Tier deactivated successfully.',
                'is_active' => $newStatus
            ]);

        } catch (Exception $e) {
            Log::error('Commission tier status toggle failed', [
                'tier_id' => $commissionSetting->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update tier status.'
            ], 500);
        }
    }

    /**
     * Calculate commission preview.
     */
    public function calculatePreview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'tier_id' => 'required|exists:commission_settings,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tier = CommissionSetting::findOrFail($request->tier_id);
            $amount = $request->amount;

            $commissions = [
                'level_1' => ($amount * ($tier->commission_level_1 ?? 0)) / 100,
                'level_2' => ($amount * ($tier->commission_level_2 ?? 0)) / 100,
                'level_3' => ($amount * ($tier->commission_level_3 ?? 0)) / 100
            ];

            $commissions['total'] = $commissions['level_1'] + $commissions['level_2'] + $commissions['level_3'];
            $commissions['remaining'] = $amount - $commissions['total'];

            return response()->json([
                'success' => true,
                'data' => [
                    'amount' => $amount,
                    'commissions' => $commissions,
                    'tier' => [
                        'id' => $tier->id,
                        'name' => $tier->name,
                        'level' => $tier->level
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Commission calculation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate commission preview.'
            ], 500);
        }
    }

    /**
     * Bulk update user tiers based on qualifications.
     */
    public function updateUserTiers(): JsonResponse
    {
        try {
            $updated = 0;
            $upgrades = 0;
            $downgrades = 0;

            $tiers = CommissionSetting::where('is_active', true)
                ->orderBy('level', 'desc')
                ->get();

            if ($tiers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active commission tiers found.'
                ]);
            }

            Log::info('Starting bulk user tier update via admin', [
                'active_tiers_count' => $tiers->count()
            ]);

            $users = User::with(['profile', 'referrals'])->get();

            foreach ($users as $user) {
                $directReferrals = $user->referrals()->count();
                $indirectReferrals = $this->countIndirectReferrals($user);
                $totalInvestment = $user->investments()->where('status', 'active')->sum('amount');

                $currentLevel = $user->profile->level ?? 0;
                $newLevel = 0;

                foreach ($tiers as $tier) {
                    if ($totalInvestment >= $tier->min_investment &&
                        $directReferrals >= $tier->min_direct_referrals &&
                        $indirectReferrals >= $tier->min_indirect_referrals) {
                        $newLevel = $tier->level;
                        break;
                    }
                }

                if ($newLevel !== $currentLevel) {
                    if ($user->profile) {
                        $user->profile->update(['level' => $newLevel]);
                    }
                    $updated++;

                    if ($newLevel > $currentLevel) {
                        $upgrades++;
                    } else {
                        $downgrades++;
                    }
                }
            }

            Log::info('Bulk user tier update completed', [
                'total_updated' => $updated,
                'upgrades' => $upgrades,
                'downgrades' => $downgrades
            ]);

            return response()->json([
                'success' => true,
                'message' => "User tiers updated. {$updated} users modified ({$upgrades} upgrades, {$downgrades} downgrades).",
                'data' => [
                    'updated' => $updated,
                    'upgrades' => $upgrades,
                    'downgrades' => $downgrades
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Bulk user tier update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user tiers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Count indirect referrals (level 2+) for a user.
     */
    private function countIndirectReferrals(User $user, int $maxDepth = 3): int
    {
        $count = 0;
        $currentLevel = $user->referrals;

        for ($depth = 2; $depth <= $maxDepth; $depth++) {
            $nextLevel = collect();
            foreach ($currentLevel as $referral) {
                $nextLevel = $nextLevel->merge($referral->referrals);
            }
            $count += $nextLevel->count();
            $currentLevel = $nextLevel;

            if ($currentLevel->isEmpty()) {
                break;
            }
        }

        return $count;
    }

    /**
     * Seed default commission tiers.
     */
    public function seedDefaults(): JsonResponse
    {
        try {
            $defaults = [
                [
                    'level' => 1,
                    'name' => 'Starter',
                    'min_investment' => 0,
                    'min_direct_referrals' => 0,
                    'min_indirect_referrals' => 0,
                    'commission_level_1' => 5,
                    'commission_level_2' => 2,
                    'commission_level_3' => 1,
                    'color' => '#6c757d',
                    'description' => 'Default tier for new users',
                    'is_active' => true,
                    'sort_order' => 1
                ],
                [
                    'level' => 2,
                    'name' => 'Bronze',
                    'min_investment' => 100,
                    'min_direct_referrals' => 2,
                    'min_indirect_referrals' => 0,
                    'commission_level_1' => 6,
                    'commission_level_2' => 3,
                    'commission_level_3' => 1.5,
                    'color' => '#cd7f32',
                    'description' => 'Bronze tier with enhanced commissions',
                    'is_active' => true,
                    'sort_order' => 2
                ],
                [
                    'level' => 3,
                    'name' => 'Silver',
                    'min_investment' => 500,
                    'min_direct_referrals' => 5,
                    'min_indirect_referrals' => 5,
                    'commission_level_1' => 7,
                    'commission_level_2' => 4,
                    'commission_level_3' => 2,
                    'color' => '#c0c0c0',
                    'description' => 'Silver tier with better rewards',
                    'is_active' => true,
                    'sort_order' => 3
                ],
                [
                    'level' => 4,
                    'name' => 'Gold',
                    'min_investment' => 1000,
                    'min_direct_referrals' => 10,
                    'min_indirect_referrals' => 20,
                    'commission_level_1' => 8,
                    'commission_level_2' => 5,
                    'commission_level_3' => 2.5,
                    'color' => '#ffd700',
                    'description' => 'Gold tier for power users',
                    'is_active' => true,
                    'sort_order' => 4
                ],
                [
                    'level' => 5,
                    'name' => 'Diamond',
                    'min_investment' => 5000,
                    'min_direct_referrals' => 25,
                    'min_indirect_referrals' => 50,
                    'commission_level_1' => 10,
                    'commission_level_2' => 6,
                    'commission_level_3' => 3,
                    'color' => '#b9f2ff',
                    'description' => 'Elite tier with maximum benefits',
                    'is_active' => true,
                    'sort_order' => 5
                ]
            ];

            foreach ($defaults as $default) {
                CommissionSetting::updateOrCreate(
                    ['level' => $default['level']],
                    $default
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Default commission tiers seeded successfully.'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to seed default tiers', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to seed default tiers.'
            ], 500);
        }
    }

    /**
     * Export commission settings to CSV.
     */
    public function export()
    {
        try {
            $tiers = CommissionSetting::orderBy('level')->get();

            $filename = 'commission_tiers_' . now()->format('Y_m_d_H_i_s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($tiers) {
                $file = fopen('php://output', 'w');

                fputcsv($file, [
                    'Level',
                    'Name',
                    'Min Investment',
                    'Min Direct Referrals',
                    'Min Indirect Referrals',
                    'Commission L1 %',
                    'Commission L2 %',
                    'Commission L3 %',
                    'Status',
                    'Users Count'
                ]);

                foreach ($tiers as $tier) {
                    $usersCount = User::whereHas('profile', function ($query) use ($tier) {
                        $query->where('level', $tier->level);
                    })->count();

                    fputcsv($file, [
                        $tier->level,
                        $tier->name,
                        $tier->min_investment,
                        $tier->min_direct_referrals,
                        $tier->min_indirect_referrals,
                        $tier->commission_level_1,
                        $tier->commission_level_2,
                        $tier->commission_level_3,
                        $tier->is_active ? 'Active' : 'Inactive',
                        $usersCount
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Commission export failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to export commission settings.');
        }
    }
}
