<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\LoginLog;
use App\Models\UserEarning;
use App\Models\CryptoWallet;
use App\Models\Lead;
use App\Models\FormSubmission;
use App\Services\PlisioPaymentService;
use App\Services\OnlineUsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect non-super admins to staff dashboard
        if ($user->adminRole && !$user->adminRole->isSuperAdmin()) {
            return $this->staffDashboard();
        }
        
        $dashboardData = $this->getDashboardData();

        return view('admin.dashboard.index', compact('user', 'dashboardData'));
    }
    
    /**
     * Display the staff dashboard (limited view for non-super admin roles)
     */
    public function staffDashboard()
    {
        $user = Auth::user();
        
        $dashboardData = [
            'system_health' => $this->getSystemHealth(),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->select('id', 'first_name', 'last_name', 'email', 'username', 'last_login_at', 'last_login_ip')
                ->orderBy('last_login_at', 'desc')
                ->limit(15)
                ->get(),
            'recent_transactions' => Transaction::with(['user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'email', 'username');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get(),
            'recent_users' => User::with(['profile' => function ($query) {
                    $query->select('id', 'user_id', 'kyc_status', 'country');
                }])
                ->select('id', 'first_name', 'last_name', 'email', 'username', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get(),
            'quick_stats' => [
                'total_users' => User::count(),
                'registered_users' => User::where('excluded_from_stats', false)->count(),
                'dummy_users' => User::where('excluded_from_stats', true)->count(),
                'active_users' => User::where('excluded_from_stats', false)
                    ->whereHas('investments', function($q) { $q->where('status', 'active'); })
                    ->count(),
                'inactive_users' => User::where('excluded_from_stats', false)
                    ->whereDoesntHave('investments', function($q) { $q->where('status', 'active'); })
                    ->count(),
                'online_users' => User::where('last_login_at', '>=', Carbon::now()->subMinutes(5))->count(),
                'pending_kyc' => User::whereHas('profile', function($q) { $q->where('kyc_status', 'pending'); })->count(),
                'today_registrations' => User::whereDate('created_at', Carbon::today())->count(),
            ],
            'support_stats' => [
                'open_chats' => \App\Models\ChatConversation::where('status', 'open')->count(),
                'pending_chats' => \App\Models\ChatConversation::where('status', 'pending')->count(),
                'unassigned_chats' => \App\Models\ChatConversation::whereNull('admin_id')->whereIn('status', ['open', 'pending'])->count(),
                'my_open_chats' => \App\Models\ChatConversation::where('admin_id', $user->id)->whereIn('status', ['open', 'pending'])->count(),
                'open_tickets' => \App\Models\SupportTicket::where('status', 'open')->count(),
                'pending_tickets' => \App\Models\SupportTicket::where('status', 'pending')->count(),
            ],
        ];

        return view('admin.dashboard.staff', compact('user', 'dashboardData'));
    }

    /**
     * Get online users count via AJAX
     */
    public function getOnlineUsersCount()
    {
        return response()->json([
            'count' => OnlineUsersService::getOnlineCount(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Show transaction analytics page
     */
    public function transactionAnalytics()
    {
        $user = Auth::user();
        $dashboardData = $this->getDashboardData();

        // Get transaction type totals for summary cards
        $transactionTotals = [
            'deposits' => (float) Transaction::deposits()->completed()->sum('amount'),
            'withdrawals' => (float) Transaction::withdrawals()->completed()->sum('amount'),
            'commissions' => (float) Transaction::where('type', Transaction::TYPE_COMMISSION)->completed()->sum('amount'),
            'roi' => (float) Transaction::where('type', Transaction::TYPE_ROI)->completed()->sum('amount'),
            'bonus' => (float) Transaction::where('type', Transaction::TYPE_BONUS)->completed()->sum('amount'),
            'investments' => (float) Transaction::where('type', Transaction::TYPE_INVESTMENT)->completed()->sum('amount'),
        ];

        return view('admin.reports.index', compact('user', 'dashboardData', 'transactionTotals'));
    }

    /**
     * Get comprehensive dashboard data - OPTIMIZED with caching
     */
    private function getDashboardData(): array
    {
        // Cache excluded user IDs separately (used by multiple methods)
        $excludedUserIds = \Illuminate\Support\Facades\Cache::remember('excluded_user_ids', 600, function () {
            return User::where('excluded_from_stats', true)->pluck('id')->toArray();
        });
        
        // Cache users with investments separately
        $usersWithInvestments = \Illuminate\Support\Facades\Cache::remember('users_with_investments', 600, function () {
            return \App\Models\UserInvestment::distinct('user_id')->pluck('user_id')->toArray();
        });
        
        return \Illuminate\Support\Facades\Cache::remember('admin_dashboard_data', 600, function () use ($excludedUserIds, $usersWithInvestments) {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            return [
                'user_stats' => $this->getUserStats($today, $thisWeek, $thisMonth, $excludedUserIds, $usersWithInvestments),
                'financial_summary' => $this->getFinancialSummary($today, $thisMonth, $lastMonth, $excludedUserIds),
                'system_health' => $this->getSystemHealth(),
                'recent_activity' => $this->getRecentActivity(),
                'performance_metrics' => $this->getPerformanceMetrics($thisMonth, $lastMonth, $excludedUserIds),
                'alerts' => $this->getKeyAlerts(),
                'charts_data' => [], // Loaded via AJAX for faster initial page load
                'quick_stats' => $this->getQuickStats($today),
                'bot_stats' => $this->getBotActivationStats(),
                'plisio_balances' => [], // Loaded via AJAX for faster initial page load
                'monthly_roi_stats' => [], // Loaded via AJAX for faster initial page load
            ];
        });
    }

    /**
     * Get user statistics - OPTIMIZED with consolidated queries
     * Excludes dummy users (excluded_from_stats = true) from main counts
     * Active = users with investments, Inactive = users without investments
     */
    private function getUserStats($today, $thisWeek, $thisMonth, array $excludedUserIds = [], array $usersWithInvestments = []): array
    {
        $yesterday = Carbon::yesterday()->toDateString();
        
        $statusCounts = User::where('excluded_from_stats', false)
            ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
            SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_registrations,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as yesterday_registrations,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week_registrations,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as month_registrations,
            SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) as online_now,
            SUM(CASE WHEN DATE(last_login_at) = ? THEN 1 ELSE 0 END) as today_logins,
            SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) as active_this_week
        ", [$today, $yesterday, $thisWeek, $thisMonth, Carbon::now()->subMinutes(5), $today, $thisWeek])
        ->first();

        $activeUsers = User::where('excluded_from_stats', false)
            ->whereIn('id', $usersWithInvestments)
            ->count();
        
        $inactiveUsers = User::where('excluded_from_stats', false)
            ->whereNotIn('id', $usersWithInvestments)
            ->count();

        $dummyUsersCount = count($excludedUserIds);

        $dummyStats = [];
        if ($dummyUsersCount > 0) {
            $dummyFinancials = \App\Models\Transaction::whereIn('user_id', $excludedUserIds)
                ->selectRaw("
                    SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN amount ELSE 0 END) as deposits,
                    SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN amount ELSE 0 END) as withdrawals
                ")->first();
            
            $dummyInvestments = \App\Models\UserInvestment::whereIn('user_id', $excludedUserIds)->sum('amount');
            
            $dummyStats = [
                'deposits' => (float)($dummyFinancials->deposits ?? 0),
                'withdrawals' => (float)($dummyFinancials->withdrawals ?? 0),
                'investments' => (float)$dummyInvestments,
            ];
        } else {
            $dummyStats = ['deposits' => 0, 'withdrawals' => 0, 'investments' => 0];
        }

        return [
            'total' => (int) ($statusCounts->total ?? 0),
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'blocked' => (int) ($statusCounts->blocked ?? 0),
            'verified' => (int) ($statusCounts->verified ?? 0),
            'dummy_users' => $dummyUsersCount,
            'dummy_stats' => $dummyStats,
            'kyc_verified' => 0,
            'registrations' => [
                'today' => (int) ($statusCounts->today_registrations ?? 0),
                'yesterday' => (int) ($statusCounts->yesterday_registrations ?? 0),
                'this_week' => (int) ($statusCounts->week_registrations ?? 0),
                'this_month' => (int) ($statusCounts->month_registrations ?? 0),
            ],
            'activity' => [
                'online_now' => (int) ($statusCounts->online_now ?? 0),
                'today_logins' => (int) ($statusCounts->today_logins ?? 0),
                'active_this_week' => (int) ($statusCounts->active_this_week ?? 0),
            ],
        ];
    }

    /**
     * Get bot activation statistics
     * Fetches from transactions table with type 'bot_fee'
     * Also calculates net remaining after expenses (salary, rank_reward, leaderboard_prize)
     */
    private function getBotActivationStats(): array
    {
        $botFeeAmount = \App\Models\InvestmentExpirySetting::getBotFeeAmount();
        
        $botFeeTransactions = Transaction::where('type', 'bot_fee')
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();
        
        $activatedCount = (int) ($botFeeTransactions->count ?? 0);
        $totalIncome = (float) ($botFeeTransactions->total ?? 0);

        $totalExpenses = Transaction::whereIn('type', ['salary', 'rank_reward', 'leaderboard_prize'])
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        $netRemaining = $totalIncome - (float)$totalExpenses;

        return [
            'activated_count' => $activatedCount,
            'fee_per_activation' => $botFeeAmount,
            'total_income' => $totalIncome,
            'total_expenses' => (float)$totalExpenses,
            'net_remaining' => $netRemaining,
        ];
    }

    /**
     * Get financial summary - OPTIMIZED with consolidated queries
     * Excludes users marked as excluded_from_stats
     */
    private function getFinancialSummary($today, $thisMonth, $lastMonth, array $excludedUserIds = []): array
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $thisWeekStart = Carbon::now()->startOfWeek()->toDateTimeString();
        
        $stats = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->selectRaw("
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN amount ELSE 0 END) as total_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as weekly_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as yesterday_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as today_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'pending' THEN amount ELSE 0 END) as pending_deposits,
            SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN 1 ELSE 0 END) as deposit_count,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN amount ELSE 0 END) as total_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as weekly_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as yesterday_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as today_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'pending' THEN amount ELSE 0 END) as pending_withdrawals,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'pending' THEN 1 ELSE 0 END) as pending_withdrawal_count,
            SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' THEN 1 ELSE 0 END) as withdrawal_count,
            SUM(CASE WHEN type = 'commission' AND status = 'completed' THEN amount ELSE 0 END) as total_commissions,
            SUM(CASE WHEN type = 'commission' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_commissions,
            SUM(CASE WHEN type = 'commission' AND status = 'pending' THEN amount ELSE 0 END) as pending_commissions,
            SUM(CASE WHEN type = 'roi' AND status = 'completed' THEN amount ELSE 0 END) as total_roi,
            SUM(CASE WHEN type = 'roi' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_roi,
            SUM(CASE WHEN type = 'bonus' AND status = 'completed' THEN amount ELSE 0 END) as total_bonus,
            SUM(CASE WHEN type = 'bonus' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_bonus,
            SUM(CASE WHEN type = 'investment' AND status = 'completed' THEN amount ELSE 0 END) as total_investments,
            SUM(CASE WHEN type = 'investment' AND status = 'completed' AND created_at >= ? THEN amount ELSE 0 END) as monthly_investments
        ", [$thisMonth, $thisWeekStart, $yesterday, $today, $thisMonth, $thisWeekStart, $yesterday, $today, $thisMonth, $thisMonth, $thisMonth, $thisMonth])->first();

        $totalBalance = CryptoWallet::whereNotIn('user_id', $excludedUserIds)->sum('balance') ?? 0;
        $totalDeposits = (float)($stats->total_deposits ?? 0);
        $totalWithdrawals = (float)($stats->total_withdrawals ?? 0);
        $monthlyDeposits = (float)($stats->monthly_deposits ?? 0);
        $monthlyWithdrawals = (float)($stats->monthly_withdrawals ?? 0);
        $todayDeposits = (float)($stats->today_deposits ?? 0);
        $todayWithdrawals = (float)($stats->today_withdrawals ?? 0);
        
        // Net Cash Position = Money In - Money Out
        $netCashPosition = $totalDeposits - $totalWithdrawals;
        
        // Get bot fee revenue (actual platform revenue)
        $botFeeRevenue = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'bot_fee')
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        // Total payouts to users (ROI + Commissions + Bonuses)
        $totalPayouts = (float)($stats->total_roi ?? 0) + 
                        (float)($stats->total_commissions ?? 0) + 
                        (float)($stats->total_bonus ?? 0);
        
        // Coverage Ratio = Cash Position / User Balances (platform's ability to cover withdrawals)
        $coverageRatio = $totalBalance > 0 ? round(($netCashPosition / $totalBalance) * 100, 2) : 100;
        
        // Payout Ratio = Withdrawals / Deposits (sustainability indicator)
        $payoutRatio = $totalDeposits > 0 ? round(($totalWithdrawals / $totalDeposits) * 100, 2) : 0;
        
        // True Platform Revenue = Bot Fees + any other fee revenue
        $platformRevenue = (float)$botFeeRevenue;
        
        // Platform Liability = Total user balances (what platform owes users)
        $platformLiability = $totalBalance;

        // Get actual investments from user_investments table (excluding bot_fee)
        $investmentStats = \App\Models\UserInvestment::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'investment')
            ->selectRaw("
                SUM(amount) as total_invested,
                SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END) as active_invested,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_invested,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
            ")->first();

        // Get distinct user counts for investments (excluding bot_fee)
        $activeUserCount = \App\Models\UserInvestment::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'investment')
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');
        
        $completedUserCount = \App\Models\UserInvestment::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'investment')
            ->where('status', 'completed')
            ->distinct('user_id')
            ->count('user_id');

        return [
            'deposits' => [
                'total' => $totalDeposits,
                'monthly' => (float)($stats->monthly_deposits ?? 0),
                'weekly' => (float)($stats->weekly_deposits ?? 0),
                'yesterday' => (float)($stats->yesterday_deposits ?? 0),
                'today' => (float)($stats->today_deposits ?? 0),
                'pending' => (float)($stats->pending_deposits ?? 0),
                'count' => (int)($stats->deposit_count ?? 0),
            ],
            'withdrawals' => [
                'total' => $totalWithdrawals,
                'monthly' => (float)($stats->monthly_withdrawals ?? 0),
                'weekly' => (float)($stats->weekly_withdrawals ?? 0),
                'yesterday' => (float)($stats->yesterday_withdrawals ?? 0),
                'today' => (float)($stats->today_withdrawals ?? 0),
                'pending' => (float)($stats->pending_withdrawals ?? 0),
                'pending_count' => (int)($stats->pending_withdrawal_count ?? 0),
                'count' => (int)($stats->withdrawal_count ?? 0),
            ],
            'commissions' => [
                'total' => (float)($stats->total_commissions ?? 0),
                'monthly' => (float)($stats->monthly_commissions ?? 0),
                'pending' => (float)($stats->pending_commissions ?? 0),
            ],
            'roi' => [
                'total' => (float)($stats->total_roi ?? 0),
                'monthly' => (float)($stats->monthly_roi ?? 0),
            ],
            'bonus' => [
                'total' => (float)($stats->total_bonus ?? 0),
                'monthly' => (float)($stats->monthly_bonus ?? 0),
            ],
            'investments' => [
                'total' => (float)($stats->total_investments ?? 0),
                'monthly' => (float)($stats->monthly_investments ?? 0),
            ],
            'actual_investments' => [
                'total' => (float)($investmentStats->total_invested ?? 0),
                'active' => (float)($investmentStats->active_invested ?? 0),
                'active_count' => $activeUserCount,
                'completed' => (float)($investmentStats->completed_invested ?? 0),
                'completed_count' => $completedUserCount,
            ],
            'platform' => [
                'total_balance' => $totalBalance,
                'net_cash_position' => $netCashPosition,
                'coverage_ratio' => $coverageRatio,
                'payout_ratio' => $payoutRatio,
                'platform_revenue' => $platformRevenue,
                'platform_liability' => $platformLiability,
                'total_payouts' => $totalPayouts,
                'available_balance' => $totalBalance,
                'locked_balance' => $walletBalance = $this->calculateNetWalletBalance($excludedUserIds),
                'profit' => $walletBalance - $totalBalance,
            ],
            'cashflow' => [
                'net_total' => $netCashPosition,
                'net_monthly' => $monthlyDeposits - $monthlyWithdrawals,
                'net_today' => $todayDeposits - $todayWithdrawals,
            ],
        ];
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        $diskUsage = $this->getDiskUsage();
        $memoryUsage = $this->getMemoryUsage();

        return [
            'status' => 'healthy',
            'uptime' => $this->getSystemUptime(),
            'disk_usage' => $diskUsage,
            'memory_usage' => $memoryUsage,
            'database' => [
                'status' => $this->checkDatabaseConnection(),
                'queries_today' => 0,
                'connection_pool' => 'active',
            ],
            'queue' => [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'processed_today' => 0,
            ],
            'cache' => [
                'status' => 'active',
                'hit_rate' => 95.5,
                'size' => '24.5 MB',
            ],
            'storage' => [
                'disk_free' => $this->formatBytes(disk_free_space('/')),
                'disk_total' => $this->formatBytes(disk_total_space('/')),
                'uploads_size' => '1.2 GB',
            ],
        ];
    }

    private function getRecentActivity(): array
    {
        // Fixed: Properly load user relationships with sponsor_id for sponsor chain
        $recentTransactions = Transaction::with([
            'user' => function ($query) {
                // ADD 'sponsor_id' to the select
                $query->select('id', 'first_name', 'last_name', 'email', 'username', 'sponsor_id');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Add sponsor chain to transactions
        $recentTransactions->transform(function ($transaction) {
            if ($transaction->user) {
                $transaction->user->sponsor_chain = $this->getSponsorChain($transaction->user);
            }
            return $transaction;
        });

        $recentUsers = User::with([
            'profile' => function ($query) {
                $query->select('id', 'user_id', 'kyc_status', 'country');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentLogins = User::whereNotNull('last_login_at')
            ->select('id', 'first_name', 'last_name', 'email', 'last_login_at', 'last_login_ip')
            ->orderBy('last_login_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'transactions' => $recentTransactions,
            'users' => $recentUsers,
            'logins' => $recentLogins,
            'leads' => Lead::with('createdBy')->orderBy('created_at', 'desc')->limit(5)->get(),
            'form_submissions' => FormSubmission::with('form')->orderBy('created_at', 'desc')->limit(5)->get(),
        ];
    }

    /**
     * Get performance metrics - excludes dummy users and staff
     */
    private function getPerformanceMetrics($thisMonth, $lastMonth, array $excludedUserIds = []): array
    {
        $thisMonthUsers = User::whereNotIn('id', $excludedUserIds)
            ->where('created_at', '>=', $thisMonth)->count();
        $lastMonthUsers = User::whereNotIn('id', $excludedUserIds)
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->count();

        $thisMonthDeposits = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->deposits()->completed()
            ->where('created_at', '>=', $thisMonth)->sum('amount');
        $lastMonthDeposits = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->deposits()->completed()
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->sum('amount');

        return [
            'user_growth' => [
                'current' => $thisMonthUsers,
                'previous' => $lastMonthUsers,
                'percentage' => $lastMonthUsers > 0 ? 
                    round((($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 2) : 
                    ($thisMonthUsers > 0 ? 100 : 0),
            ],
            'deposit_growth' => [
                'current' => $thisMonthDeposits,
                'previous' => $lastMonthDeposits,
                'percentage' => $lastMonthDeposits > 0 ? 
                    round((($thisMonthDeposits - $lastMonthDeposits) / $lastMonthDeposits) * 100, 2) : 
                    ($thisMonthDeposits > 0 ? 100 : 0),
            ],
            'conversion_rate' => $this->getConversionRate(),
            'retention_rate' => $this->getRetentionRate(),
            'avg_transaction_values' => $this->getAvgTransactionValues(),
            'platform_growth' => $this->getPlatformGrowthRate($thisMonth, $lastMonth),
            'mlm_metrics' => $this->getMLMMetrics(),
        ];
    }

    /**
     * Get key alerts
     */
    private function getKeyAlerts(): array
    {
        $alerts = [];

        // Pending withdrawals alert
        $pendingWithdrawals = Transaction::withdrawals()->pending()->count();
        if ($pendingWithdrawals > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Pending Withdrawals',
                'message' => "{$pendingWithdrawals} withdrawal(s) require attention",
                'icon' => 'iconamoon:warning-duotone',
                'action' => '#', // You can add proper routes later
                'priority' => 'high',
            ];
        }

        // New users alert
        $todayRegistrations = User::whereDate('created_at', Carbon::today())->count();
        if ($todayRegistrations > 10) {
            $alerts[] = [
                'type' => 'success',
                'title' => 'High Registration Rate',
                'message' => "{$todayRegistrations} new users registered today",
                'icon' => 'iconamoon:user-plus-duotone',
                'action' => '#',
                'priority' => 'medium',
            ];
        }

        // System health alerts
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage > 80) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'High Disk Usage',
                'message' => "Disk usage is at {$diskUsage}%",
                'icon' => 'iconamoon:hard-drive-duotone',
                'action' => '#',
                'priority' => 'critical',
            ];
        }

        // Large pending transactions
        $largePendingAmount = Transaction::pending()->where('amount', '>', 1000)->sum('amount');
        if ($largePendingAmount > 10000) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Large Pending Transactions',
                'message' => '$' . number_format($largePendingAmount, 2) . ' in large pending transactions',
                'icon' => 'iconamoon:dollar-circle-duotone',
                'action' => '#',
                'priority' => 'high',
            ];
        }

        // KYC verifications needed
        $pendingKyc = User::whereHas('profile', function ($q) {
            $q->whereIn('kyc_status', ['pending', 'submitted', 'under_review']);
        })->count();

        if ($pendingKyc > 5) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'KYC Verifications Pending',
                'message' => "{$pendingKyc} KYC verifications need review",
                'icon' => 'iconamoon:profile-duotone',
                'action' => '#',
                'priority' => 'medium',
            ];
        }

        return $alerts;
    }

    /**
     * Get enhanced charts data for transactions - OPTIMIZED with grouped queries
     */
    private function getChartsData(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('dashboard_charts_data', 300, function () {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            $userRegistrations = User::whereBetween('created_at', [$startDate, $endDate])
                ->where('excluded_from_stats', false)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->whereHas('user', function ($query) {
                    $query->where('excluded_from_stats', false);
                })
                ->selectRaw("DATE(created_at) as date, type, SUM(amount) as total")
                ->groupBy('date', 'type')
                ->get()
                ->groupBy('date');
            
            $last30Days = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $dayData = $transactions->get($date, collect());
                
                $last30Days->push([
                    'date' => $date,
                    'users' => $userRegistrations[$date] ?? 0,
                    'deposits' => (float) $dayData->where('type', 'deposit')->sum('total'),
                    'withdrawals' => (float) $dayData->where('type', 'withdrawal')->sum('total'),
                    'commissions' => (float) $dayData->where('type', 'commission')->sum('total'),
                    'roi' => (float) $dayData->where('type', 'roi')->sum('total'),
                    'bonus' => (float) $dayData->where('type', 'bonus')->sum('total'),
                    'investments' => (float) $dayData->where('type', 'investment')->sum('total'),
                ]);
            }

            return [
                'user_registrations' => $last30Days->pluck('users')->toArray(),
                'daily_deposits' => $last30Days->pluck('deposits')->toArray(),
                'daily_withdrawals' => $last30Days->pluck('withdrawals')->toArray(),
                'daily_commissions' => $last30Days->pluck('commissions')->toArray(),
                'daily_roi' => $last30Days->pluck('roi')->toArray(),
                'daily_bonus' => $last30Days->pluck('bonus')->toArray(),
                'daily_investments' => $last30Days->pluck('investments')->toArray(),
                'labels' => $last30Days->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('M d');
                })->toArray(),
            ];
        });
    }

    /**
     * Get monthly ROI statistics for the last 6 months
     * Calculates actual ROI paid out as percentage of invested amounts
     */
    private function getMonthlyRoiStats(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('monthly_roi_stats', 300, function () {
            $months = [];
            
            // Get dummy user IDs to exclude
            $dummyUserIds = User::where('excluded_from_stats', true)->pluck('id')->toArray();
            
            for ($i = 6; $i >= 0; $i--) {
                $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
                $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
                $monthLabel = $monthStart->format('M Y');
                
                // Get total ROI paid out this month (excluding dummy users)
                $roiQuery = Transaction::where('type', Transaction::TYPE_ROI)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$monthStart, $monthEnd]);
                
                if (!empty($dummyUserIds)) {
                    $roiQuery->whereNotIn('user_id', $dummyUserIds);
                }
                
                $roiPaidOut = $roiQuery->sum('amount');
                
                // Get total invested amount that was earning ROI during this month (excluding dummy users)
                // Count all investments that started on or before the month end
                // (All investments are currently active, so no need to check status)
                $investmentQuery = \App\Models\UserInvestment::where('type', 'investment')
                    ->where('start_date', '<=', $monthEnd);
                
                if (!empty($dummyUserIds)) {
                    $investmentQuery->whereNotIn('user_id', $dummyUserIds);
                }
                
                $activeInvestments = $investmentQuery->sum('amount');
                
                // Calculate percentage: (ROI paid / invested amount) * 100
                $roiPercentage = $activeInvestments > 0 
                    ? round(($roiPaidOut / $activeInvestments) * 100, 2) 
                    : 0;
                
                // Get new users joined this month (excluding dummy users)
                $newUsersQuery = User::where('excluded_from_stats', false)
                    ->whereBetween('created_at', [$monthStart, $monthEnd]);
                $newUsersCount = $newUsersQuery->count();
                
                // Get users that invested this month (excluding dummy users)
                $usersInvestedQuery = \App\Models\UserInvestment::where('type', 'investment')
                    ->whereBetween('start_date', [$monthStart, $monthEnd]);
                if (!empty($dummyUserIds)) {
                    $usersInvestedQuery->whereNotIn('user_id', $dummyUserIds);
                }
                $usersInvestedCount = $usersInvestedQuery->distinct('user_id')->count('user_id');
                
                // Get new investments made this month (excluding dummy users)
                $newInvestmentsQuery = \App\Models\UserInvestment::where('type', 'investment')
                    ->whereBetween('start_date', [$monthStart, $monthEnd]);
                if (!empty($dummyUserIds)) {
                    $newInvestmentsQuery->whereNotIn('user_id', $dummyUserIds);
                }
                $newInvestmentsAmount = $newInvestmentsQuery->sum('amount');
                
                // Get withdrawals this month (excluding dummy users)
                $withdrawalsQuery = Transaction::where('type', Transaction::TYPE_WITHDRAWAL)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$monthStart, $monthEnd]);
                if (!empty($dummyUserIds)) {
                    $withdrawalsQuery->whereNotIn('user_id', $dummyUserIds);
                }
                $withdrawalsAmount = $withdrawalsQuery->sum('amount');
                
                $months[] = [
                    'month' => $monthLabel,
                    'roi_paid' => (float) $roiPaidOut,
                    'invested_amount' => (float) $activeInvestments,
                    'percentage' => $roiPercentage,
                    'new_users' => (int) $newUsersCount,
                    'users_invested' => (int) $usersInvestedCount,
                    'new_investments' => (float) $newInvestmentsAmount,
                    'withdrawals' => (float) $withdrawalsAmount,
                ];
            }
            
            return [
                'months' => $months,
                'labels' => array_column($months, 'month'),
                'percentages' => array_column($months, 'percentage'),
                'roi_amounts' => array_column($months, 'roi_paid'),
                'invested_amounts' => array_column($months, 'invested_amount'),
            ];
        });
    }

    /**
     * Get quick stats
     */
    private function getQuickStats($today): array
    {
        return [
            'online_users' => User::where('last_login_at', '>=', Carbon::now()->subMinutes(5))->count(),
            'pending_kyc' => User::whereHas('profile', function ($q) {
                $q->whereIn('kyc_status', ['pending', 'submitted', 'under_review']);
            })->count(),
            'active_investments' => Transaction::where('type', Transaction::TYPE_INVESTMENT)
                ->completed()->whereDate('created_at', $today)->count(),
            'support_tickets' => 0, // Implement if you have a support system
            'total_transactions_today' => Transaction::whereDate('created_at', $today)->count(),
            'revenue_today' => Transaction::whereDate('created_at', $today)
                ->whereIn('type', [Transaction::TYPE_DEPOSIT, Transaction::TYPE_INVESTMENT])
                ->completed()->sum('amount'),
        ];
    }

    /**
     * ==========================================
     * TRANSACTION ANALYTICS API ENDPOINTS
     * ==========================================
     */

    /**
     * Get transaction chart data for specific period - OPTIMIZED with consolidated query
     */
    public function getTransactionChartData(Request $request)
    {
        $period = $request->get('period', '30d');
        $days = $this->getDaysFromPeriod($period);
        $minute = Carbon::now()->format('Y-m-d-H-i');
        $cacheKey = 'transaction_chart_data_' . $period . '_' . $minute;
        
        return response()->json(\Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($days) {
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->whereHas('user', function ($query) {
                    $query->where('excluded_from_stats', false);
                })
                ->selectRaw("DATE(created_at) as date, type, SUM(amount) as total")
                ->groupBy('date', 'type')
                ->get()
                ->groupBy('date');
            
            $chartData = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $dayData = $transactions->get($date, collect());
                
                $chartData->push([
                    'date' => $date,
                    'deposits' => (float) $dayData->where('type', 'deposit')->sum('total'),
                    'withdrawals' => (float) $dayData->where('type', 'withdrawal')->sum('total'),
                    'commissions' => (float) $dayData->where('type', 'commission')->sum('total'),
                    'roi' => (float) $dayData->where('type', 'roi')->sum('total'),
                    'investments' => (float) $dayData->where('type', 'investment')->sum('total'),
                ]);
            }

            return [
                'series' => [
                    ['name' => 'Deposits', 'data' => $chartData->pluck('deposits')->toArray()],
                    ['name' => 'Withdrawals', 'data' => $chartData->pluck('withdrawals')->toArray()],
                    ['name' => 'Commissions', 'data' => $chartData->pluck('commissions')->toArray()],
                    ['name' => 'ROI', 'data' => $chartData->pluck('roi')->toArray()],
                    ['name' => 'Investments', 'data' => $chartData->pluck('investments')->toArray()]
                ],
                'categories' => $chartData->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray()
            ];
        }));
    }

    /**
     * Get transaction summary by type - OPTIMIZED with single consolidated query
     */
    public function getTransactionSummary(Request $request)
    {
        $period = $request->get('period', '30d');
        $cacheKey = 'transaction_summary_' . $period;
        
        return response()->json(\Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($period) {
            $startDate = $this->getStartDateFromPeriod($period);
            
            $results = Transaction::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->whereHas('user', function ($query) {
                    $query->where('excluded_from_stats', false);
                })
                ->selectRaw("type, SUM(amount) as total, COUNT(*) as count")
                ->groupBy('type')
                ->get()
                ->keyBy('type');

            $types = ['deposit' => 'deposits', 'withdrawal' => 'withdrawals', 'commission' => 'commissions', 
                      'roi' => 'roi', 'bonus' => 'bonus', 'investment' => 'investments'];
            
            $summary = [];
            foreach ($types as $dbType => $key) {
                $row = $results->get($dbType);
                $total = (float) ($row->total ?? 0);
                $count = (int) ($row->count ?? 0);
                $summary[$key] = [
                    'total' => $total,
                    'count' => $count,
                    'avg' => $count > 0 ? $total / $count : 0
                ];
            }

            return $summary;
        }));
    }

    /**
     * API endpoint for live stats updates - OPTIMIZED with consolidated queries
     */
    public function getStats(Request $request)
    {
        return response()->json(\Illuminate\Support\Facades\Cache::remember('admin_live_stats', 60, function () {
            $today = Carbon::today();
            $fiveMinAgo = Carbon::now()->subMinutes(5);
            $oneHourAgo = Carbon::now()->subHour();
            
            $userStats = User::selectRaw("
                SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) as online_users,
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_registrations,
                SUM(CASE WHEN last_login_at >= ? THEN 1 ELSE 0 END) as active_sessions
            ", [$fiveMinAgo, $today, $oneHourAgo])->first();
            
            $txnStats = Transaction::selectRaw("
                SUM(CASE WHEN type = 'withdrawal' AND status = 'pending' THEN 1 ELSE 0 END) as pending_withdrawals,
                SUM(CASE WHEN type = 'deposit' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as today_deposits,
                SUM(CASE WHEN type = 'withdrawal' AND status = 'completed' AND DATE(created_at) = ? THEN amount ELSE 0 END) as today_withdrawals
            ", [$today, $today])->first();
            
            $pendingKyc = DB::table('user_profiles')
                ->whereIn('kyc_status', ['pending', 'submitted', 'under_review'])
                ->count();
            
            return [
                'online_users' => (int) ($userStats->online_users ?? 0),
                'pending_withdrawals' => (int) ($txnStats->pending_withdrawals ?? 0),
                'today_registrations' => (int) ($userStats->today_registrations ?? 0),
                'today_deposits' => (float) ($txnStats->today_deposits ?? 0),
                'today_withdrawals' => (float) ($txnStats->today_withdrawals ?? 0),
                'pending_kyc' => $pendingKyc,
                'active_sessions' => (int) ($userStats->active_sessions ?? 0),
            ];
        }));
    }

    /**
     * Get user counts filtered by time range
     */
    public function getUserCountsByRange(Request $request)
    {
        $range = $request->get('range', 'all');
        
        $query = User::where('excluded_from_stats', false);
        
        switch ($range) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
            case 'all':
            default:
                break;
        }
        
        $userIds = $query->pluck('id')->toArray();
        $usersWithInvestments = \App\Models\UserInvestment::distinct('user_id')
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->toArray();
        
        $total = count($userIds);
        $active = count($usersWithInvestments);
        
        // Rebuild query for blocked count since we consumed the original with pluck
        $blockedQuery = User::where('excluded_from_stats', false);
        switch ($range) {
            case 'today':
                $blockedQuery->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $blockedQuery->whereDate('created_at', Carbon::yesterday());
                break;
            case 'week':
                $blockedQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $blockedQuery->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
        }
        $blocked = $blockedQuery->where('status', 'blocked')->count();
        $inactive = $total - $active - $blocked;
        
        return response()->json([
            'success' => true,
            'total' => $total,
            'active' => $active,
            'inactive' => max(0, $inactive),
            'blocked' => $blocked,
            'range' => $range
        ]);
    }

    /**
     * System health check
     */
    public function systemHealth()
    {
        $health = $this->getSystemHealth();

        // Add real-time checks
        $health['timestamp'] = Carbon::now()->toISOString();
        $health['response_time'] = round((microtime(true) - LARAVEL_START) * 1000, 2) . 'ms';

        return response()->json($health);
    }

    /**
     * AJAX endpoint for dashboard charts data
     */
    public function getChartsDataAjax()
    {
        return response()->json($this->getChartsData());
    }

    /**
     * AJAX endpoint for monthly ROI stats
     */
    public function getMonthlyRoiStatsAjax()
    {
        return response()->json($this->getMonthlyRoiStats());
    }

    /**
     * AJAX endpoint for Plisio balances
     */
    public function getPlisioBalancesAjax()
    {
        return response()->json($this->getPlisioBalances());
    }

    /**
     * Export dashboard data
     */
    public function exportData(Request $request)
    {
        $data = $this->getDashboardData();

        // Add export metadata
        $export = [
            'exported_at' => Carbon::now()->toISOString(),
            'exported_by' => auth()->user()->full_name,
            'period' => $request->get('period', '30d'),
            'data' => $data
        ];

        $filename = 'admin-dashboard-' . Carbon::now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * ==========================================
     * HELPER METHODS
     * ==========================================
     */

    /**
     * Helper methods for period calculations
     */
    private function getDaysFromPeriod(string $period): int
    {
        switch ($period) {
            case '7d':
                return 7;
            case '30d':
                return 30;
            case '90d':
                return 90;
            case '1y':
                return 365;
            default:
                return 30;
        }
    }

    private function getStartDateFromPeriod(string $period): Carbon
    {
        switch ($period) {
            case '7d':
                return Carbon::now()->subDays(7);
            case '30d':
                return Carbon::now()->subDays(30);
            case '90d':
                return Carbon::now()->subDays(90);
            case '1y':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }

    /**
     * Helper methods for system metrics
     */
    private function getDiskUsage(): int
    {
        $bytes = disk_total_space('/') - disk_free_space('/');
        $total = disk_total_space('/');
        return $total > 0 ? round(($bytes / $total) * 100) : 0;
    }

    private function getMemoryUsage(): array
    {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return [
            'current' => $this->formatBytes($memory),
            'peak' => $this->formatBytes($peak),
            'limit' => ini_get('memory_limit'),
            'usage_percentage' => $this->getMemoryUsagePercentage(),
        ];
    }

    private function getMemoryUsagePercentage(): float
    {
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $current = memory_get_usage(true);

        return $limit > 0 ? round(($current / $limit) * 100, 2) : 0;
    }

    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1')
            return PHP_INT_MAX;

        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));

        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function getSystemUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return 'Unknown';
    }

    private function checkDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = round((microtime(true) - $startTime) * 1000, 2);
            return $queryTime < 100 ? 'excellent' : ($queryTime < 500 ? 'good' : 'slow');
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getConversionRate(): float
    {
        // Exclude users marked for exclusion from stats
        $excludedUserIds = User::where('excluded_from_stats', true)
            ->pluck('id')->toArray();
        
        $totalUsers = User::whereNotIn('id', $excludedUserIds)->count();
        $usersWithDeposits = User::whereNotIn('id', $excludedUserIds)
            ->whereHas('transactions', function ($q) {
                $q->where('type', Transaction::TYPE_DEPOSIT)->where('status', 'completed');
            })->count();

        return $totalUsers > 0 ? round(($usersWithDeposits / $totalUsers) * 100, 2) : 0;
    }

    private function getRetentionRate(): float
    {
        // Exclude users marked for exclusion from stats
        $excludedUserIds = User::where('excluded_from_stats', true)
            ->pluck('id')->toArray();
        
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $usersFromMonth = User::whereNotIn('id', $excludedUserIds)
            ->where('created_at', '<=', $thirtyDaysAgo)->count();
        $activeFromMonth = User::whereNotIn('id', $excludedUserIds)
            ->where('created_at', '<=', $thirtyDaysAgo)
            ->where('last_login_at', '>=', $thirtyDaysAgo)->count();

        return $usersFromMonth > 0 ? round(($activeFromMonth / $usersFromMonth) * 100, 2) : 0;
    }

    /**
     * Calculate net wallet balance: sum of all incomings (deposits only) - sum of all outgoings (withdrawals only)
     * Excludes users marked as excluded_from_stats
     */
    private function calculateNetWalletBalance(array $excludedUserIds): float
    {
        // Incoming: only deposits (real money coming in)
        $totalIncomings = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        // Outgoing: only withdrawals (real money going out)
        $totalOutgoings = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_WITHDRAWAL)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        return (float)$totalIncomings - (float)$totalOutgoings;
    }

    /**
     * Get average transaction values by type (more meaningful metrics)
     */
    private function getAvgTransactionValues(): array
    {
        $excludedUserIds = User::where('excluded_from_stats', true)->pluck('id')->toArray();
        
        $avgDeposit = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('status', 'completed')
            ->avg('amount') ?? 0;
        
        $avgWithdrawal = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_WITHDRAWAL)
            ->where('status', 'completed')
            ->avg('amount') ?? 0;
        
        $avgInvestment = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_INVESTMENT)
            ->where('status', 'completed')
            ->avg('amount') ?? 0;
        
        return [
            'deposit' => round((float)$avgDeposit, 2),
            'withdrawal' => round((float)$avgWithdrawal, 2),
            'investment' => round((float)$avgInvestment, 2),
        ];
    }

    /**
     * Get platform growth rate - only deposits count as new money in
     */
    private function getPlatformGrowthRate($thisMonth, $lastMonth): array
    {
        $excludedUserIds = User::where('excluded_from_stats', true)->pluck('id')->toArray();
        
        // Only count deposits as revenue (not investments which are internal transfers)
        $thisMonthDeposits = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');

        $lastMonthDeposits = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('status', 'completed')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)
            ->sum('amount');

        return [
            'deposit_growth' => $lastMonthDeposits > 0 ?
                round((($thisMonthDeposits - $lastMonthDeposits) / $lastMonthDeposits) * 100, 2) : 
                ($thisMonthDeposits > 0 ? 100 : 0),
            'transaction_growth' => $this->getTransactionGrowthRate($thisMonth, $lastMonth),
        ];
    }

    private function getTransactionGrowthRate($thisMonth, $lastMonth): float
    {
        $excludedUserIds = User::where('excluded_from_stats', true)->pluck('id')->toArray();
        
        $thisMonthTx = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)->count();
        $lastMonthTx = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('status', 'completed')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->count();

        return $lastMonthTx > 0 ? round((($thisMonthTx - $lastMonthTx) / $lastMonthTx) * 100, 2) : 
            ($thisMonthTx > 0 ? 100 : 0);
    }
    
    /**
     * Get MLM-specific metrics for investment platform
     */
    private function getMLMMetrics(): array
    {
        $excludedUserIds = User::where('excluded_from_stats', true)->pluck('id')->toArray();
        
        // Commission liability (pending commissions)
        $pendingCommissions = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'commission')
            ->where('status', 'pending')
            ->sum('amount') ?? 0;
        
        // ROI liability - estimate based on active investments
        $activeInvestments = \App\Models\UserInvestment::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'investment')
            ->where('status', 'active')
            ->sum('amount') ?? 0;
        
        // Get average ROI rate for estimation (simplified)
        $avgDailyRoiRate = 0.5; // Default 0.5% daily, can be made dynamic
        $dailyRoiLiability = $activeInvestments * ($avgDailyRoiRate / 100);
        
        // Active investors count by level (simplified - count users with active investments)
        $activeInvestorCount = \App\Models\UserInvestment::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'investment')
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');
        
        // Commission payout ratio (commissions paid / total deposits)
        $totalDeposits = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        $totalCommissionsPaid = Transaction::whereNotIn('user_id', $excludedUserIds)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
        
        $commissionPayoutRatio = $totalDeposits > 0 ? 
            round(($totalCommissionsPaid / $totalDeposits) * 100, 2) : 0;
        
        return [
            'pending_commissions' => (float)$pendingCommissions,
            'active_investments' => (float)$activeInvestments,
            'daily_roi_liability' => round($dailyRoiLiability, 2),
            'active_investor_count' => $activeInvestorCount,
            'commission_payout_ratio' => $commissionPayoutRatio,
        ];
    }

    private function formatBytes($size, $precision = 2): string
    {
        if ($size == 0)
            return '0 B';

        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    /**
     * Get filtered transactions for dashboard (AJAX)
     */
    public function getFilteredTransactions(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $query = Transaction::with([
            'user' => function ($query) {
                // ADD 'sponsor_id' to the select
                $query->select('id', 'first_name', 'last_name', 'email', 'username', 'sponsor_id');
            }
        ])->select('*');

        // Apply filters
        if ($request->type) {
            $query->where('type', $request->type);
        } else {
            // When "All Types" is selected, exclude ROI and Profit Share unless toggle is enabled
            $showRoiProfitShare = $request->get('show_roi_profit_share', '0') === '1';
            if (!$showRoiProfitShare) {
                $query->whereNotIn('type', ['roi', 'profit_share']);
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Apply custom date range filter
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform transactions to include sponsor chain
        $transactions->getCollection()->transform(function ($transaction) {
            if ($transaction->user) {
                $transaction->user->sponsor_chain = $this->getSponsorChain($transaction->user);
            }
            return $transaction;
        });

        // Build transactions HTML
        $html = $this->buildTransactionsHTML($transactions);

        // Build pagination HTML
        $paginationHtml = $this->buildPaginationHTML($transactions);

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'count' => $transactions->count(),
            'total' => $transactions->total(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage()
        ]);
    }

    /**
     * Build transactions table HTML
     */
    private function buildTransactionsHTML($transactions)
    {
        if ($transactions->count() === 0) {
            return '<tr>
            <td colspan="7" class="text-center py-4">
                <iconify-icon icon="iconamoon:history-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
                <h6 class="text-muted">No Transactions Found</h6>
                <p class="text-muted mb-0">No transactions match the selected filters.</p>
            </td>
        </tr>';
        }

        $html = '';

        foreach ($transactions as $transaction) {
            // Determine badge colors based on type
            $typeColors = [
                'deposit' => 'success',
                'withdrawal' => 'warning',
                'commission' => 'primary',
                'profit_share' => 'purple',
                'roi' => 'info',
                'bonus' => 'secondary',
                'investment' => 'danger',
                'salary' => 'success',
                'leaderboard_prize' => 'warning'
            ];
            $typeColor = $typeColors[$transaction->type] ?? 'dark';
            $typeLabel = $transaction->type === 'profit_share' ? 'Profit Share' : ucfirst(str_replace('_', ' ', $transaction->type));

            // Determine badge colors based on status
            $statusColors = [
                'completed' => 'success',
                'pending' => 'warning',
                'processing' => 'info',
                'failed' => 'danger'
            ];
            $statusColor = $statusColors[$transaction->status] ?? 'secondary';

            // Amount styling
            $amountClass = in_array($transaction->type, ['withdrawal']) ? 'text-danger' : 'text-success';
            $amountSign = in_array($transaction->type, ['withdrawal']) ? '-' : '+';

            // User info
            $userInitials = $transaction->user ? $transaction->user->initials : 'U';
            $userFullName = $transaction->user ? $transaction->user->full_name : 'Unknown User';
            $transactionIdShort = \Str::limit($transaction->transaction_id, 15);

            $html .= '<tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm rounded-circle bg-primary me-2">
                        <span class="avatar-title text-white">' . e($userInitials) . '</span>
                    </div>
                    <div>';
            if ($transaction->user) {
                $html .= '<h6 class="mb-0"><a href="javascript:void(0)" class="clickable-user" onclick="showUserDetails(\'' . $transaction->user->id . '\')">' . e($userFullName) . '</a></h6>';
            } else {
                $html .= '<h6 class="mb-0 text-muted">' . e($userFullName) . '</h6>';
            }
            $html .= '<code class="small">' . e($transactionIdShort) . '...</code>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-' . $typeColor . '-subtle text-' . $typeColor . ' p-1">
                    ' . $typeLabel . '
                </span>
            </td>
            <td>
                <strong class="' . $amountClass . '">
                    ' . $amountSign . $transaction->formatted_amount . '
                </strong>
            </td>';

            // ADD THIS: Sponsor Chain Column
            $html .= '<td>';
            if ($transaction->user && !empty($transaction->user->sponsor_chain)) {
                $chain = collect($transaction->user->sponsor_chain);
                $firstLevel = $chain->first();
                $lastLevel = $chain->last();
                $hasMultipleLevels = $chain->count() > 1;

                $sponsorNames = $chain->map(function ($sponsor) {
                    return 'L' . $sponsor['level'] . ': ' . $sponsor['user']->first_name . ' ' . $sponsor['user']->last_name;
                })->implode(' → ');

                $html .= '<div class="small" title="' . e($sponsorNames) . '" style="cursor: help;">
                ' . \Str::limit($firstLevel['user']->first_name . ' ' . $firstLevel['user']->last_name, 12);

                if ($hasMultipleLevels) {
                    $html .= ' <span class="text-primary">...</span> ' .
                        \Str::limit($lastLevel['user']->first_name . ' ' . $lastLevel['user']->last_name, 12);
                }

                $html .= '<div class="mt-1">
                <span class="badge bg-info">' . $chain->count() . ' level' . ($chain->count() > 1 ? 's' : '') . '</span>
            </div></div>';
            } else {
                $html .= '<span class="text-muted small">Direct signup</span>';
            }
            $html .= '</td>';

            $html .= '<td>
                ' . $transaction->created_at->format('d M, y') . '
                <small class="text-muted d-block">' . $transaction->created_at->format('h:i:s A') . '</small>
            </td>
            <td>
                <span class="badge bg-' . $statusColor . '-subtle text-' . $statusColor . ' p-1">
                    ' . ucfirst($transaction->status) . '
                </span>
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="showTransactionDetails(\'' . $transaction->id . '\')">
                                <iconify-icon icon="iconamoon:eye-duotone" class="me-2"></iconify-icon>View Details
                            </a>
                        </li>';

            // Add view user details and impersonate user options if user exists
            if ($transaction->user) {
                $userName = e($transaction->user->first_name . ' ' . $transaction->user->last_name);
                $userEmail = e($transaction->user->email);
                $html .= '<li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="viewUserDetails(\'' . $transaction->user->id . '\')">
                                <iconify-icon icon="iconamoon:information-circle-duotone" class="me-2"></iconify-icon>View User Details
                            </a>
                        </li>';
                $html .= '<li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="impersonateUser(\'' . $transaction->user->id . '\', \'' . $userName . '\', \'' . $userEmail . '\')">
                                <iconify-icon icon="iconamoon:profile-duotone" class="me-2"></iconify-icon>Impersonate User
                            </a>
                        </li>';
            }

            if ($transaction->status !== 'completed') {
                $html .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-success" href="javascript:void(0)" onclick="updateTransactionStatusDashboard(\'' . $transaction->id . '\', \'completed\')">
                                <iconify-icon icon="iconamoon:check-circle-duotone" class="me-2"></iconify-icon>Mark Completed
                            </a>
                        </li>';
            }

            $html .= '</ul>
                </div>
            </td>
        </tr>';
        }

        return $html;
    }

    /**
     * Build smart pagination HTML with ellipsis to prevent overflow
     */
    private function buildPaginationHTML($transactions)
    {
        if (!$transactions->hasPages()) {
            return '';
        }

        $currentPage = $transactions->currentPage();
        $lastPage = $transactions->lastPage();

        $html = '<div class="card-footer border-top border-light">
        <div class="align-items-center justify-content-between row text-center text-sm-start">
            <div class="col-sm">
                <div class="text-muted">
                    Showing
                    <span class="fw-semibold text-body">' . $transactions->firstItem() . '</span>
                    to
                    <span class="fw-semibold text-body">' . $transactions->lastItem() . '</span>
                    of
                    <span class="fw-semibold">' . $transactions->total() . '</span>
                    Transactions
                </div>
            </div>
            <div class="col-sm-auto mt-3 mt-sm-0">
                <ul class="pagination pagination-boxed pagination-sm mb-0 justify-content-center">';

        // Previous button
        if ($transactions->onFirstPage()) {
            $html .= '<li class="page-item disabled">
            <span class="page-link"><i class="bx bxs-chevron-left"></i></span>
        </li>';
        } else {
            $html .= '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="loadDashboardTransactionsPage(' . ($currentPage - 1) . ')">
                <i class="bx bxs-chevron-left"></i>
            </a>
        </li>';
        }

        // Smart pagination - calculate pages to show
        $pagesToShow = $this->calculatePagesToShow($currentPage, $lastPage);

        // Render page numbers
        foreach ($pagesToShow as $page) {
            if ($page === '...') {
                $html .= '<li class="page-item disabled">
                <span class="page-link">...</span>
            </li>';
            } elseif ($page == $currentPage) {
                $html .= '<li class="page-item active">
                <span class="page-link">' . $page . '</span>
            </li>';
            } else {
                $html .= '<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadDashboardTransactionsPage(' . $page . ')">' . $page . '</a>
            </li>';
            }
        }

        // Next button
        if ($transactions->hasMorePages()) {
            $html .= '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="loadDashboardTransactionsPage(' . ($currentPage + 1) . ')">
                <i class="bx bxs-chevron-right"></i>
            </a>
        </li>';
        } else {
            $html .= '<li class="page-item disabled">
            <span class="page-link"><i class="bx bxs-chevron-right"></i></span>
        </li>';
        }

        $html .= '</ul>
            </div>
        </div>
    </div>';

        return $html;
    }

    /**
     * Calculate which page numbers to show with ellipsis
     * Shows max 7 page buttons to prevent overflow
     */
    private function calculatePagesToShow($currentPage, $lastPage)
    {
        $pagesToShow = [];

        if ($lastPage <= 7) {
            // Show all pages if 7 or less
            return range(1, $lastPage);
        }

        // Always show first page
        $pagesToShow[] = 1;

        if ($currentPage > 4) {
            // Add ellipsis after first page
            $pagesToShow[] = '...';
        }

        // Calculate range around current page
        $start = max(2, $currentPage - 1);
        $end = min($lastPage - 1, $currentPage + 1);

        // Adjust if we're near the beginning
        if ($currentPage <= 4) {
            $start = 2;
            $end = min(6, $lastPage - 1);
        }

        // Adjust if we're near the end
        if ($currentPage >= $lastPage - 3) {
            $start = max(2, $lastPage - 5);
            $end = $lastPage - 1;
        }

        // Add middle pages
        for ($i = $start; $i <= $end; $i++) {
            $pagesToShow[] = $i;
        }

        if ($currentPage < $lastPage - 3) {
            // Add ellipsis before last page
            $pagesToShow[] = '...';
        }

        // Always show last page
        $pagesToShow[] = $lastPage;

        return $pagesToShow;
    }

    /**
     * Get filtered users for dashboard (AJAX)
     */
    public function getFilteredUsers(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $query = User::with([
            'profile',
            'cryptoWallets' => function ($q) {
                $q->where('crypto_wallets.is_active', true)->with([
                    'cryptocurrency' => function ($subq) {
                        $subq->where('cryptocurrencies.is_active', true);
                    }
                ]);
            },
            'investments' => function ($q) {
                $q->select('user_id', 'status', 'amount', 'paid_return', 'created_at')
                    ->latest()
                    ->limit(3);
            }
        ])->select('id', 'first_name', 'last_name', 'email', 'username', 'status', 'created_at', 'last_login_at', 'sponsor_id', 'email_verified_at');

        // Apply filters
        if ($request->investment_status) {
            switch ($request->investment_status) {
                case 'has_investments':
                    $query->whereHas('investments');
                    break;
                case 'no_investments':
                    $query->whereDoesntHave('investments');
                    break;
                case 'active_investments':
                    $query->whereHas('investments', function ($q) {
                        $q->where('status', 'active');
                    });
                    break;
            }
        }

        if ($request->verification) {
            switch ($request->verification) {
                case 'email_verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'email_unverified':
                    $query->whereNull('email_verified_at');
                    break;
                case 'kyc_verified':
                    $query->whereHas('profile', function ($q) {
                        $q->where('kyc_status', 'verified');
                    });
                    break;
            }
        }

        // Apply status filter - NEW LOGIC: Active = has investments, Inactive = no investments
        if ($request->status) {
            switch ($request->status) {
                case 'active':
                    // Active = has investments
                    $query->whereHas('investments');
                    break;
                case 'inactive':
                    // Inactive = no investments
                    $query->whereDoesntHave('investments');
                    break;
                case 'blocked':
                    // Blocked = blocked status in database
                    $query->where('status', 'blocked');
                    break;
            }
        }

        // Apply custom date range filter
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add sponsor chain and wallet data
        $users->getCollection()->transform(function ($user) {
            $user->sponsor_chain = $this->getSponsorChain($user);
            $user->total_wallet_balance_usd = $user->cryptoWallets
                ->where('is_active', true)
                ->sum(function ($wallet) {
                    return $wallet->balance * ($wallet->usd_rate ?? 0);
                });
            $user->primary_wallet = $user->cryptoWallets
                ->where('is_active', true)
                ->sortByDesc(function ($wallet) {
                    $priority = str_contains($wallet->currency, 'USDT') ? 1000000 : 0;
                    return $priority + ($wallet->balance * ($wallet->usd_rate ?? 1));
                })
                ->first();
            return $user;
        });

        // Build users HTML
        $html = $this->buildUsersHTML($users);

        // Build pagination HTML
        $paginationHtml = $this->buildUsersPaginationHTML($users);

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'count' => $users->count(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage()
        ]);
    }

    /**
     * Build users table HTML
     */
    private function buildUsersHTML($users)
    {
        if ($users->count() === 0) {
            return '<tr>
            <td colspan="7" class="text-center py-4">
                <iconify-icon icon="iconamoon:profile-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
                <h6 class="text-muted">No Users Found</h6>
                <p class="text-muted mb-0">No users match the selected filters.</p>
            </td>
        </tr>';
        }

        $html = '';

        foreach ($users as $user) {
            $userInitials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
            $statusColor = $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning');

            // Investment stats
            $activeInvestments = $user->investments->where('status', 'active')->count();
            $totalInvested = $user->investments->sum('amount');
            $totalReturns = $user->investments->sum('paid_return');

            $html .= '<tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm rounded-circle bg-primary me-2">
                        <span class="avatar-title text-white">' . e($userInitials) . '</span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0"><a href="javascript:void(0)" class="clickable-user" onclick="showUserDetails(\'' . $user->id . '\')">' . e($user->full_name) . '</a></h6>';

            // Status icons
            if ($user->profile && $user->profile->kyc_status === 'verified') {
                $html .= '<iconify-icon icon="material-symbols:verified" class="text-success" style="font-size: 1rem;" title="KYC Verified"></iconify-icon>';
            }
            if ($user->investments && $user->investments->isNotEmpty()) {
                $html .= '<iconify-icon icon="iconamoon:coin-duotone" class="text-success" style="font-size: 1rem;" title="Has Investments"></iconify-icon>';
            }
            if ($user->hasVerifiedEmail()) {
                $html .= '<iconify-icon icon="material-symbols:mark-email-read-rounded" class="text-info" style="font-size: 0.9rem;" title="Email Verified"></iconify-icon>';
            }

            $html .= '</div>
                        <small class="text-muted">' . e($user->email) . '</small>
                    </div>
                </div>
            </td>
            <td>';

            // Investments column
            if ($user->investments && $user->investments->isNotEmpty()) {
                $html .= '<div class="small text-center">';
                if ($activeInvestments > 0) {
                    $html .= '<span class="badge bg-primary">' . $activeInvestments . ' Active</span> ';
                }
                $html .= '<div class="text-muted mt-1">
                <strong>$' . number_format($totalInvested, 2) . '</strong> invested';
                if ($totalReturns > 0) {
                    $html .= '<br><small class="text-success">$' . number_format($totalReturns, 2) . ' earned</small>';
                }
                $html .= '</div></div>';
            } else {
                $html .= '<div class="text-center">
                <iconify-icon icon="iconamoon:sign-minus-duotone" class="text-muted fs-5"></iconify-icon>
                <div class="small text-muted">No investments</div>
            </div>';
            }

            $html .= '</td>
            <td>';

            // Wallet balance
            if ($user->total_wallet_balance_usd > 0) {
                $html .= '<div class="text-center">
                <strong class="text-success">$' . number_format($user->total_wallet_balance_usd, 2) . '</strong>
            </div>';
            } else {
                $html .= '<div class="text-center text-muted">$0.00</div>';
            }

            $html .= '</td>
            <td>';

            // Sponsor chain
            if (!empty($user->sponsor_chain)) {
                $chain = collect($user->sponsor_chain);
                $firstLevel = $chain->first();
                $lastLevel = $chain->last();
                $hasMultipleLevels = $chain->count() > 1;

                $sponsorNames = $chain->map(function ($sponsor) {
                    return 'L' . $sponsor['level'] . ': ' . $sponsor['user']->first_name . ' ' . $sponsor['user']->last_name;
                })->implode(' → ');

                $html .= '<div class="small" title="' . e($sponsorNames) . '" style="cursor: help;">
                ' . \Str::limit($firstLevel['user']->first_name . ' ' . $firstLevel['user']->last_name, 12);

                if ($hasMultipleLevels) {
                    $html .= ' <span class="text-primary">...</span> ' .
                        \Str::limit($lastLevel['user']->first_name . ' ' . $lastLevel['user']->last_name, 12);
                }

                $html .= '<div class="mt-1">
                <span class="badge bg-info">' . $chain->count() . ' level' . ($chain->count() > 1 ? 's' : '') . '</span>
            </div></div>';
            } else {
                $html .= '<span class="text-muted small">Direct signup</span>';
            }

            $html .= '</td>
            <td>
                <div class="small">
                    <div>' . $user->created_at->format('M d, Y') . '</div>
                    <div class="text-muted">' . $user->created_at->diffForHumans() . '</div>
                </div>
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <iconify-icon icon="iconamoon:menu-kebab-vertical-duotone"></iconify-icon>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="viewUserDetails(\'' . $user->id . '\')">
                                <iconify-icon icon="iconamoon:information-circle-duotone" class="me-2"></iconify-icon>View Details
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="' . route('admin.users.edit', $user->id) . '">
                                <iconify-icon icon="iconamoon:edit-duotone" class="me-2"></iconify-icon>Edit User
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>';
        }

        return $html;
    }

    /**
     * Build users pagination HTML
     */
    private function buildUsersPaginationHTML($users)
    {
        if (!$users->hasPages()) {
            return '';
        }

        $currentPage = $users->currentPage();
        $lastPage = $users->lastPage();

        $html = '<div class="card-footer border-top">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted small">
                Showing ' . $users->firstItem() . ' to ' . $users->lastItem() . ' of ' . $users->total() . ' users
            </div>
            <div>
                <ul class="pagination pagination-sm mb-0">';

        // Previous button
        if ($users->onFirstPage()) {
            $html .= '<li class="page-item disabled"><span class="page-link"><i class="bx bxs-chevron-left"></i></span></li>';
        } else {
            $html .= '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="loadDashboardUsersPage(' . ($currentPage - 1) . ')">
                <i class="bx bxs-chevron-left"></i>
            </a>
        </li>';
        }

        // Page numbers with ellipsis
        $pagesToShow = $this->calculatePagesToShow($currentPage, $lastPage);

        foreach ($pagesToShow as $page) {
            if ($page === '...') {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } elseif ($page == $currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="loadDashboardUsersPage(' . $page . ')">' . $page . '</a>
            </li>';
            }
        }

        // Next button
        if ($users->hasMorePages()) {
            $html .= '<li class="page-item">
            <a class="page-link" href="javascript:void(0)" onclick="loadDashboardUsersPage(' . ($currentPage + 1) . ')">
                <i class="bx bxs-chevron-right"></i>
            </a>
        </li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link"><i class="bx bxs-chevron-right"></i></span></li>';
        }

        $html .= '</ul>
            </div>
        </div>
    </div>';

        return $html;
    }

    /**
     * Get Plisio wallet balances
     */
    private function getPlisioBalances(): array
    {
        try {
            $plisioService = new PlisioPaymentService();
            return $plisioService->getAllBalances();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch Plisio balances', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'balances' => [],
                'total_usdt' => 0,
                'total_usdt_formatted' => '$0.00',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get sponsor chain for a user
     */
    private function getSponsorChain(User $user, int $levels = 3): array
    {
        $chain = [];
        $currentUser = $user;
        $level = 1;

        while ($level <= $levels && $currentUser->sponsor_id) {
            $sponsor = User::with('profile')->find($currentUser->sponsor_id);

            if (!$sponsor) {
                break;
            }

            $chain[] = [
                'user' => $sponsor,
                'level' => $level
            ];

            $currentUser = $sponsor;
            $level++;
        }

        return $chain;
    }

    /**
     * Display the budget overview page
     */
    public function budget()
    {
        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $thisWeek = Carbon::now()->startOfWeek()->toDateTimeString();
        $yesterday = Carbon::yesterday()->toDateString();

        // Bot Activation Income
        $botFeeStats = Transaction::where('type', 'bot_fee')
            ->where('status', 'completed')
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as today_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as yesterday_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as weekly_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as monthly_amount
            ", [$today, $yesterday, $thisWeek, $thisMonth])
            ->first();

        // Distributed Prizes - Salary
        $salaryStats = Transaction::where('type', 'salary')
            ->where('status', 'completed')
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as today_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as yesterday_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as weekly_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as monthly_amount
            ", [$today, $yesterday, $thisWeek, $thisMonth])
            ->first();

        // Distributed Prizes - Rank Rewards
        $rankRewardStats = Transaction::where('type', 'rank_reward')
            ->where('status', 'completed')
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as today_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as yesterday_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as weekly_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as monthly_amount
            ", [$today, $yesterday, $thisWeek, $thisMonth])
            ->first();

        // Distributed Prizes - Leaderboard Prizes
        $leaderboardStats = Transaction::where('type', 'leaderboard_prize')
            ->where('status', 'completed')
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as today_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END), 0) as yesterday_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as weekly_amount,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END), 0) as monthly_amount
            ", [$today, $yesterday, $thisWeek, $thisMonth])
            ->first();

        // Calculate totals
        $totalIncome = (float)($botFeeStats->total_amount ?? 0);
        $totalExpenses = (float)($salaryStats->total_amount ?? 0) + 
                       (float)($rankRewardStats->total_amount ?? 0) + 
                       (float)($leaderboardStats->total_amount ?? 0);
        $netBudget = $totalIncome - $totalExpenses;

        $todayIncome = (float)($botFeeStats->today_amount ?? 0);
        $todayExpenses = (float)($salaryStats->today_amount ?? 0) + 
                       (float)($rankRewardStats->today_amount ?? 0) + 
                       (float)($leaderboardStats->today_amount ?? 0);
        $todayNet = $todayIncome - $todayExpenses;

        $yesterdayIncome = (float)($botFeeStats->yesterday_amount ?? 0);
        $yesterdayExpenses = (float)($salaryStats->yesterday_amount ?? 0) + 
                           (float)($rankRewardStats->yesterday_amount ?? 0) + 
                           (float)($leaderboardStats->yesterday_amount ?? 0);
        $yesterdayNet = $yesterdayIncome - $yesterdayExpenses;

        $weeklyIncome = (float)($botFeeStats->weekly_amount ?? 0);
        $weeklyExpenses = (float)($salaryStats->weekly_amount ?? 0) + 
                        (float)($rankRewardStats->weekly_amount ?? 0) + 
                        (float)($leaderboardStats->weekly_amount ?? 0);
        $weeklyNet = $weeklyIncome - $weeklyExpenses;

        $monthlyIncome = (float)($botFeeStats->monthly_amount ?? 0);
        $monthlyExpenses = (float)($salaryStats->monthly_amount ?? 0) + 
                         (float)($rankRewardStats->monthly_amount ?? 0) + 
                         (float)($leaderboardStats->monthly_amount ?? 0);
        $monthlyNet = $monthlyIncome - $monthlyExpenses;

        return view('admin.dashboard.budget', [
            'botFeeStats' => $botFeeStats,
            'salaryStats' => $salaryStats,
            'rankRewardStats' => $rankRewardStats,
            'leaderboardStats' => $leaderboardStats,
            'totals' => [
                'income' => $totalIncome,
                'expenses' => $totalExpenses,
                'net' => $netBudget,
            ],
            'today' => [
                'income' => $todayIncome,
                'expenses' => $todayExpenses,
                'net' => $todayNet,
            ],
            'yesterday' => [
                'income' => $yesterdayIncome,
                'expenses' => $yesterdayExpenses,
                'net' => $yesterdayNet,
            ],
            'weekly' => [
                'income' => $weeklyIncome,
                'expenses' => $weeklyExpenses,
                'net' => $weeklyNet,
            ],
            'monthly' => [
                'income' => $monthlyIncome,
                'expenses' => $monthlyExpenses,
                'net' => $monthlyNet,
            ],
        ]);
    }

    /**
     * Get system logs for staff dashboard (paginated)
     */
    public function getSystemLogs(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $logs = collect();

        $defaultDateFrom = $dateFrom ?: Carbon::now()->subDays(7)->toDateString();

        $loginQuery = LoginLog::with('user:id,first_name,last_name,email')
            ->select('id', 'user_id', 'is_successful', 'failure_reason', 'ip_address', 'device_type', 'browser', 'login_at as created_at')
            ->selectRaw("'login' as event_type")
            ->selectRaw("CASE WHEN is_successful THEN 'success' ELSE 'failed' END as status_label")
            ->selectRaw("NULL as amount");

        $depositQuery = Transaction::with('user:id,first_name,last_name,email')
            ->where('type', 'deposit')
            ->select('id', 'user_id', 'status', 'description', 'amount', 'created_at')
            ->selectRaw("'deposit' as event_type")
            ->selectRaw("CASE WHEN status = 'completed' THEN 'success' WHEN status IN ('failed', 'cancelled') THEN 'failed' ELSE 'pending' END as status_label")
            ->selectRaw("NULL as ip_address");

        $withdrawalQuery = Transaction::with('user:id,first_name,last_name,email')
            ->where('type', 'withdrawal')
            ->select('id', 'user_id', 'status', 'description', 'amount', 'created_at')
            ->selectRaw("'withdrawal' as event_type")
            ->selectRaw("CASE WHEN status = 'completed' THEN 'success' WHEN status IN ('failed', 'cancelled') THEN 'failed' ELSE 'pending' END as status_label")
            ->selectRaw("NULL as ip_address");

        $investmentQuery = Transaction::with('user:id,first_name,last_name,email')
            ->where('type', 'investment')
            ->select('id', 'user_id', 'status', 'description', 'amount', 'created_at')
            ->selectRaw("'investment' as event_type")
            ->selectRaw("CASE WHEN status = 'completed' THEN 'success' WHEN status IN ('failed', 'cancelled') THEN 'failed' ELSE 'pending' END as status_label")
            ->selectRaw("NULL as ip_address");

        if ($dateFrom) {
            $loginQuery->whereDate('login_at', '>=', $dateFrom);
            $depositQuery->whereDate('created_at', '>=', $dateFrom);
            $withdrawalQuery->whereDate('created_at', '>=', $dateFrom);
            $investmentQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $loginQuery->whereDate('login_at', '<=', $dateTo);
            $depositQuery->whereDate('created_at', '<=', $dateTo);
            $withdrawalQuery->whereDate('created_at', '<=', $dateTo);
            $investmentQuery->whereDate('created_at', '<=', $dateTo);
        }

        if ($status === 'success') {
            $loginQuery->where('is_successful', true);
            $depositQuery->where('status', 'completed');
            $withdrawalQuery->where('status', 'completed');
            $investmentQuery->where('status', 'completed');
        } elseif ($status === 'failed') {
            $loginQuery->where('is_successful', false);
            $depositQuery->whereIn('status', ['failed', 'cancelled']);
            $withdrawalQuery->whereIn('status', ['failed', 'cancelled']);
            $investmentQuery->whereIn('status', ['failed', 'cancelled']);
        }

        if ($type === 'login') {
            $logs = $loginQuery->orderByDesc('login_at')->paginate($perPage);
        } elseif ($type === 'deposit') {
            $logs = $depositQuery->orderByDesc('created_at')->paginate($perPage);
        } elseif ($type === 'withdrawal') {
            $logs = $withdrawalQuery->orderByDesc('created_at')->paginate($perPage);
        } elseif ($type === 'investment') {
            $logs = $investmentQuery->orderByDesc('created_at')->paginate($perPage);
        } else {
            if (!$dateFrom) {
                $loginQuery->whereDate('login_at', '>=', $defaultDateFrom);
                $depositQuery->whereDate('created_at', '>=', $defaultDateFrom);
                $withdrawalQuery->whereDate('created_at', '>=', $defaultDateFrom);
                $investmentQuery->whereDate('created_at', '>=', $defaultDateFrom);
            }

            $maxPerType = 100;
            
            $loginLogs = $loginQuery->orderByDesc('login_at')->limit($maxPerType)->get()->map(function ($log) {
                return [
                    'id' => 'login_' . $log->id,
                    'user' => $log->user,
                    'event_type' => 'login',
                    'status' => $log->is_successful ? 'success' : 'failed',
                    'details' => $log->is_successful ? 'Successful login' : ($log->failure_reason ?? 'Login failed'),
                    'amount' => null,
                    'ip_address' => $log->ip_address,
                    'device' => $log->device_type,
                    'browser' => $log->browser,
                    'created_at' => $log->created_at,
                ];
            });

            $depositLogs = $depositQuery->orderByDesc('created_at')->limit($maxPerType)->get()->map(function ($log) {
                return [
                    'id' => 'deposit_' . $log->id,
                    'user' => $log->user,
                    'event_type' => 'deposit',
                    'status' => $log->status === 'completed' ? 'success' : (in_array($log->status, ['failed', 'cancelled']) ? 'failed' : 'pending'),
                    'details' => $log->description ?? 'Deposit ' . $log->status,
                    'amount' => $log->amount,
                    'ip_address' => null,
                    'device' => null,
                    'browser' => null,
                    'created_at' => $log->created_at,
                ];
            });

            $withdrawalLogs = $withdrawalQuery->orderByDesc('created_at')->limit($maxPerType)->get()->map(function ($log) {
                return [
                    'id' => 'withdrawal_' . $log->id,
                    'user' => $log->user,
                    'event_type' => 'withdrawal',
                    'status' => $log->status === 'completed' ? 'success' : (in_array($log->status, ['failed', 'cancelled']) ? 'failed' : 'pending'),
                    'details' => $log->description ?? 'Withdrawal ' . $log->status,
                    'amount' => $log->amount,
                    'ip_address' => null,
                    'device' => null,
                    'browser' => null,
                    'created_at' => $log->created_at,
                ];
            });

            $investmentLogs = $investmentQuery->orderByDesc('created_at')->limit($maxPerType)->get()->map(function ($log) {
                return [
                    'id' => 'investment_' . $log->id,
                    'user' => $log->user,
                    'event_type' => 'investment',
                    'status' => $log->status === 'completed' ? 'success' : (in_array($log->status, ['failed', 'cancelled']) ? 'failed' : 'pending'),
                    'details' => $log->description ?? 'Investment ' . $log->status,
                    'amount' => $log->amount,
                    'ip_address' => null,
                    'device' => null,
                    'browser' => null,
                    'created_at' => $log->created_at,
                ];
            });

            $allLogs = $loginLogs->concat($depositLogs)->concat($withdrawalLogs)->concat($investmentLogs)
                ->sortByDesc('created_at')
                ->values();

            $page = $request->get('page', 1);
            $total = $allLogs->count();
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                $allLogs->forPage($page, $perPage)->values(),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($logs);
        }

        return $logs;
    }
}