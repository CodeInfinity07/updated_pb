<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminImpersonationController extends Controller
{
    /**
     * Display impersonation dashboard.
     */
    public function index(Request $request): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        // Get filter parameters
        $search = $request->get('search');
        $status = $request->get('status');
        $role = $request->get('role');
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');

        // Build query for users (exclude current admin and other admins for security)
        $query = User::with(['profile'])
            ->where('id', '!=', auth()->id())
            ->where('role', '!=', User::ROLE_ADMIN);

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        if ($status) {
            if ($status === 'active') {
                // Active = status active AND has investments
                $query->where('status', 'active')
                    ->whereHas('investments');
            } else {
                // Other statuses (inactive, suspended, etc.)
                $query->where('status', $status);
            }
        }

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        // Apply sorting
        $query->orderBy($sort_by, $sort_order);

        // Get users with pagination
        $users = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = $this->getImpersonationStatistics();

        // Get current impersonation status
        $currentImpersonation = $this->getCurrentImpersonationStatus();

        return view('admin.impersonation.index', compact(
            'users',
            'stats',
            'search',
            'status',
            'role',
            'user',
            'sort_by',
            'sort_order',
            'currentImpersonation'
        ));
    }

    /**
     * Search users for impersonation.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'search' => 'required|string|min:2|max:50'
        ]);

        try {
            $users = User::with(['profile'])
                ->where('id', '!=', auth()->id())
                ->where('role', '!=', User::ROLE_ADMIN)
                ->where(function ($query) use ($validated) {
                    $query->where('first_name', 'LIKE', '%' . $validated['search'] . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $validated['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $validated['search'] . '%')
                        ->orWhere('username', 'LIKE', '%' . $validated['search'] . '%');
                })
                ->limit(20)
                ->get(['id', 'first_name', 'last_name', 'email', 'username', 'status', 'role'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'status' => $user->status,
                        'role' => $user->role,
                        'avatar' => $user->profile->avatar_url ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start impersonating a user.
     */
    public function startImpersonation(Request $request): JsonResponse
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $targetUser = User::findOrFail($validated['user_id']);
            $adminUser = Auth::user();

            // Security check: prevent impersonating admins
            if ($targetUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot impersonate admin users for security reasons.'
                ], 403);
            }

            // Security check: prevent impersonating yourself
            if ($targetUser->id === $adminUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot impersonate yourself.'
                ], 400);
            }

            // Create impersonation log record
            $impersonationLog = ImpersonationLog::create([
                'admin_id' => $adminUser->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Store original admin ID and log ID in session
            Session::put('impersonation.original_admin_id', $adminUser->id);
            Session::put('impersonation.target_user_id', $targetUser->id);
            Session::put('impersonation.started_at', now());
            Session::put('impersonation.log_id', $impersonationLog->id);

            // Log the impersonation
            Log::info('Admin started impersonating user', [
                'admin_id' => $adminUser->id,
                'admin_email' => $adminUser->email,
                'admin_name' => $adminUser->full_name,
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'target_user_name' => $targetUser->full_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'log_id' => $impersonationLog->id
            ]);

            // Login as target user
            Auth::login($targetUser);

            return response()->json([
                'success' => true,
                'message' => "Successfully impersonating {$targetUser->full_name}",
                'redirect_url' => route('dashboard')
            ]);

        } catch (Exception $e) {
            Log::error('Failed to start impersonation', [
                'admin_id' => auth()->id(),
                'target_user_id' => $validated['user_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start impersonation. Please try again.'
            ], 500);
        }
    }

    /**
     * Stop impersonating and return to admin account.
     */
    public function stopImpersonation(): RedirectResponse
    {
        $originalAdminId = Session::get('impersonation.original_admin_id');
        $targetUserId = Session::get('impersonation.target_user_id');
        $startedAt = Session::get('impersonation.started_at');
        $logId = Session::get('impersonation.log_id');

        if (!$originalAdminId) {
            return redirect()->route('dashboard')
                ->with('error', 'No impersonation session found.');
        }

        try {
            $originalAdmin = User::findOrFail($originalAdminId);
            $currentUser = Auth::user();

            // Update impersonation log record
            if ($logId) {
                $impersonationLog = ImpersonationLog::find($logId);
                if ($impersonationLog) {
                    $durationSeconds = $startedAt ? now()->diffInSeconds($startedAt) : 0;
                    $impersonationLog->update([
                        'ended_at' => now(),
                        'duration_seconds' => $durationSeconds,
                    ]);
                }
            }

            // Log the end of impersonation
            Log::info('Admin stopped impersonating user', [
                'admin_id' => $originalAdminId,
                'admin_name' => $originalAdmin->full_name,
                'target_user_id' => $targetUserId,
                'target_user_name' => $currentUser ? $currentUser->full_name : 'Unknown',
                'current_user_id' => $currentUser ? $currentUser->id : null,
                'duration_minutes' => $startedAt ? now()->diffInMinutes($startedAt) : null,
                'ip_address' => request()->ip(),
                'log_id' => $logId
            ]);

            // Clear impersonation session data
            Session::forget('impersonation.original_admin_id');
            Session::forget('impersonation.target_user_id');
            Session::forget('impersonation.started_at');
            Session::forget('impersonation.log_id');

            // Login back as original admin
            Auth::login($originalAdmin);

            return redirect()->route('admin.impersonation.index')
                ->with('success', 'Impersonation ended successfully. Welcome back!');

        } catch (Exception $e) {
            Log::error('Failed to stop impersonation', [
                'original_admin_id' => $originalAdminId,
                'current_user_id' => auth()->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback: logout and redirect to login
            Auth::logout();
            Session::flush();

            return redirect()->route('login')
                ->with('error', 'Impersonation session ended. Please login again.');
        }
    }

    /**
     * Get impersonation history.
     */
    public function history(Request $request): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $dateRange = $request->get('date_range', '7');

        $query = ImpersonationLog::with(['admin', 'impersonatedUser'])
            ->orderBy('started_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('admin', function ($adminQuery) use ($search) {
                    $adminQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%");
                })->orWhereHas('impersonatedUser', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->get('admin_id'));
        }

        // Apply date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('started_at', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($dateRange && $dateRange !== 'all') {
            $this->applyHistoryDateRangeFilter($query, $dateRange);
        }

        $logs = $query->paginate(20)->withQueryString();

        $admins = User::whereIn('id', ImpersonationLog::distinct()->pluck('admin_id'))
            ->get(['id', 'first_name', 'last_name', 'email']);

        $statistics = $this->getHistoryStatistics($request);

        $dateRanges = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 3 months',
            'all' => 'All time'
        ];

        return view('admin.impersonation.history', compact('user', 'logs', 'admins', 'statistics', 'dateRanges', 'dateRange'));
    }

    /**
     * Filter history logs via AJAX
     */
    public function historyFilter(Request $request): JsonResponse
    {
        $this->checkAdminAccess();

        $perPage = $request->get('per_page', 20);
        $dateRange = $request->get('date_range', '7');

        $query = ImpersonationLog::with(['admin', 'impersonatedUser'])
            ->orderBy('started_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('admin', function ($adminQuery) use ($search) {
                    $adminQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })->orWhereHas('impersonatedUser', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('started_at', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($dateRange && $dateRange !== 'all' && $dateRange !== 'custom') {
            $this->applyHistoryDateRangeFilter($query, $dateRange);
        }

        $logs = $query->paginate($perPage);

        $html = $this->buildHistoryTableHTML($logs);
        $paginationHtml = $this->buildHistoryPaginationHTML($logs);
        $statistics = $this->getHistoryStatistics($request);

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'statistics' => $statistics,
            'total' => $logs->total(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage()
        ]);
    }

    private function applyHistoryDateRangeFilter($query, string $dateRange): void
    {
        switch ($dateRange) {
            case 'today':
                $query->whereDate('started_at', today());
                break;
            case 'yesterday':
                $query->whereDate('started_at', today()->subDay());
                break;
            default:
                $days = (int) $dateRange;
                if ($days > 0) {
                    $query->where('started_at', '>=', now()->subDays($days));
                }
                break;
        }
    }

    private function getHistoryStatistics(Request $request): array
    {
        $baseQuery = ImpersonationLog::query();

        if ($request->filled('admin_id')) {
            $baseQuery->where('admin_id', $request->admin_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->whereHas('admin', function ($adminQuery) use ($search) {
                    $adminQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })->orWhereHas('impersonatedUser', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            });
        }

        $dateRange = $request->get('date_range', '7');
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $baseQuery->whereBetween('started_at', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($dateRange && $dateRange !== 'all') {
            $this->applyHistoryDateRangeFilter($baseQuery, $dateRange);
        }

        return [
            'total_logs' => (clone $baseQuery)->count(),
            'today_logs' => ImpersonationLog::whereDate('started_at', today())->count(),
            'active_sessions' => ImpersonationLog::whereNull('ended_at')->count(),
            'unique_admins' => (clone $baseQuery)->distinct('admin_id')->count('admin_id'),
        ];
    }

    private function buildHistoryTableHTML($logs): string
    {
        if ($logs->count() === 0) {
            return '<tr><td colspan="7" class="text-center py-5">
                <iconify-icon icon="iconamoon:history-duotone" class="text-muted" style="font-size: 4rem;"></iconify-icon>
                <h5 class="mt-3 text-muted">No Impersonation Logs</h5>
                <p class="text-muted">No logs match your filters</p>
            </td></tr>';
        }

        $html = '';
        foreach ($logs as $log) {
            $adminName = ($log->admin->first_name ?? '') . ' ' . ($log->admin->last_name ?? '');
            $adminEmail = $log->admin->email ?? 'N/A';
            $userName = ($log->impersonatedUser->first_name ?? '') . ' ' . ($log->impersonatedUser->last_name ?? '');
            $userUsername = $log->impersonatedUser->username ?? 'N/A';
            $userEmail = $log->impersonatedUser->email ?? 'N/A';
            $startedAt = $log->started_at->format('M d, Y H:i:s');
            $endedAt = $log->ended_at ? $log->ended_at->format('M d, Y H:i:s') : '<span class="text-muted">-</span>';
            $duration = $log->formatted_duration;
            $ip = $log->ip_address ?? 'N/A';
            $statusBadge = $log->ended_at 
                ? '<span class="badge bg-success">Completed</span>' 
                : '<span class="badge bg-warning">Active</span>';

            $html .= "<tr>
                <td>
                    <div class='d-flex align-items-center'>
                        <div>
                            <div class='fw-semibold'>" . e(trim($adminName)) . "</div>
                            <small class='text-muted'>" . e($adminEmail) . "</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class='d-flex align-items-center'>
                        <div>
                            <div class='fw-semibold'>" . e(trim($userName)) . "</div>
                            <small class='text-muted'>" . e($userUsername) . " - " . e($userEmail) . "</small>
                        </div>
                    </div>
                </td>
                <td>{$startedAt}</td>
                <td>{$endedAt}</td>
                <td class='text-center'><span class='badge bg-secondary'>{$duration}</span></td>
                <td><code>" . e($ip) . "</code></td>
                <td class='text-center'>{$statusBadge}</td>
            </tr>";
        }
        return $html;
    }

    private function buildHistoryPaginationHTML($logs): string
    {
        if (!$logs->hasPages()) {
            return '';
        }

        $currentPage = $logs->currentPage();
        $lastPage = $logs->lastPage();

        $html = '<div class="card-footer bg-white border-top">
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-muted">
                    Showing <span class="fw-semibold">' . $logs->firstItem() . '</span>
                    to <span class="fw-semibold">' . $logs->lastItem() . '</span>
                    of <span class="fw-semibold">' . $logs->total() . '</span> records
                </div>
                <ul class="pagination pagination-sm mb-0">';

        if ($logs->onFirstPage()) {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadHistoryPage(' . ($currentPage - 1) . ')">&laquo;</a></li>';
        }

        for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++) {
            if ($i == $currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadHistoryPage(' . $i . ')">' . $i . '</a></li>';
            }
        }

        if ($logs->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadHistoryPage(' . ($currentPage + 1) . ')">&raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }

        $html .= '</ul></div></div>';
        return $html;
    }

    /**
     * Get current impersonation status.
     */
    public function getStatus(): JsonResponse
    {
        $status = $this->getCurrentImpersonationStatus();

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Check admin access.
     */
    private function checkAdminAccess(): void
    {
        if (!auth()->user() || !auth()->user()->canAccessAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
    }

    /**
     * Get impersonation statistics.
     */
    private function getImpersonationStatistics(): array
    {
        $totalUsers = User::where('role', '!=', User::ROLE_ADMIN)->count();
        // Active users = status 'active' AND have investments
        $activeUsers = User::where('role', '!=', User::ROLE_ADMIN)
            ->where('status', 'active')
            ->whereHas('investments')
            ->count();
        $verifiedUsers = User::where('role', '!=', User::ROLE_ADMIN)->verified()->count();

        // Count KYC verified users using the model's method
        $kycVerifiedUsers = User::where('role', '!=', User::ROLE_ADMIN)
            ->get()
            ->filter(function ($user) {
                return $user->isKycVerified();
            })
            ->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'kyc_verified_users' => $kycVerifiedUsers,
        ];
    }

    /**
     * Get current impersonation status.
     */
    private function getCurrentImpersonationStatus(): ?array
    {
        $originalAdminId = Session::get('impersonation.original_admin_id');
        $targetUserId = Session::get('impersonation.target_user_id');
        $startedAt = Session::get('impersonation.started_at');

        if (!$originalAdminId || !$targetUserId) {
            return null;
        }

        try {
            $originalAdmin = User::find($originalAdminId);
            $currentUser = Auth::user();

            // Validate session integrity
            if (!$originalAdmin || !$currentUser || $currentUser->id != $targetUserId) {
                // Clear invalid session data
                Session::forget('impersonation.original_admin_id');
                Session::forget('impersonation.target_user_id');
                Session::forget('impersonation.started_at');
                return null;
            }

            return [
                'is_impersonating' => true,
                'original_admin' => [
                    'id' => $originalAdmin->id,
                    'name' => $originalAdmin->full_name,
                    'email' => $originalAdmin->email,
                    'role' => $originalAdmin->role,
                ],
                'current_user' => [
                    'id' => $currentUser->id,
                    'name' => $currentUser->full_name,
                    'email' => $currentUser->email,
                    'role' => $currentUser->role,
                    'status' => $currentUser->status,
                ],
                'started_at' => $startedAt,
                'duration' => $startedAt ? now()->diffForHumans($startedAt, true) : null,
                'duration_minutes' => $startedAt ? now()->diffInMinutes($startedAt) : 0,
            ];
        } catch (Exception $e) {
            Log::warning('Error getting impersonation status', [
                'error' => $e->getMessage(),
                'original_admin_id' => $originalAdminId,
                'target_user_id' => $targetUserId
            ]);

            // Clear session on any error
            Session::forget('impersonation.original_admin_id');
            Session::forget('impersonation.target_user_id');
            Session::forget('impersonation.started_at');

            return null;
        }
    }

    /**
     * Get user profile information for display.
     */
    private function getUserProfileInfo(User $user): array
    {
        $profile = $user->profile;

        return [
            'avatar_url' => $profile ? $profile->avatar_url : null,
            'country' => $profile ? $profile->country_name : null,
            'kyc_status' => $user->kyc_status,
            'kyc_verified' => $user->isKycVerified(),
            'phone_verified' => $profile ? $profile->isPhoneVerified() : false,
            'total_balance' => $user->total_balance,
            'total_investments' => $profile ? $profile->total_investments : 0,
            'referral_count' => $profile ? $profile->referral_count : 0,
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
        ];
    }

    /**
     * Get detailed user information for impersonation confirmation.
     */
    public function getUserDetails(Request $request): JsonResponse
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::with(['profile'])->findOrFail($validated['user_id']);

            // Security check: prevent getting admin details
            if ($user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot access admin user details.'
                ], 403);
            }

            $profileInfo = $this->getUserProfileInfo($user);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'status' => $user->status,
                    'role' => $user->role_display_name,
                    'created_at' => $user->formatted_registration_date,
                    'profile' => $profileInfo,
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get impersonation permissions for current admin.
     */
    public function getPermissions(): JsonResponse
    {
        $this->checkAdminAccess();

        $user = Auth::user();
        $permissions = [
            'can_impersonate_users' => $user->canAccessAdmin(),
            'can_impersonate_staff' => $user->isAdmin(), // Only admin can impersonate staff
            'can_view_logs' => $user->hasStaffPrivileges(),
            'available_roles' => $this->getImpersonatableRoles($user),
        ];

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Get roles that current admin can impersonate.
     */
    private function getImpersonatableRoles(User $admin): array
    {
        $allRoles = [
            User::ROLE_USER => 'User',
            User::ROLE_MODERATOR => 'Moderator',
            User::ROLE_SUPPORT => 'Support',
        ];

        // Only admin can impersonate staff
        if (!$admin->isAdmin()) {
            unset($allRoles[User::ROLE_MODERATOR], $allRoles[User::ROLE_SUPPORT]);
        }

        return $allRoles;
    }

    /**
     * Validate impersonation session periodically.
     */
    public function validateSession(): JsonResponse
    {
        $status = $this->getCurrentImpersonationStatus();

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'No valid impersonation session found.'
            ], 404);
        }

        // Check if session has been running too long (optional security measure)
        $maxDurationMinutes = config('admin.impersonation.max_duration_minutes', 480); // 8 hours default

        if ($status['duration_minutes'] > $maxDurationMinutes) {
            return response()->json([
                'success' => false,
                'message' => 'Impersonation session has exceeded maximum duration.',
                'should_terminate' => true
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }
}