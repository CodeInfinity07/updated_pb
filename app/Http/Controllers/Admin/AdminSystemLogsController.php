<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Transaction;
use App\Services\EmailLogService;
use Illuminate\Http\Request;

class AdminSystemLogsController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->input('date_range', 'today');
        $startDate = match($dateRange) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            'all' => null,
            default => now()->startOfDay(),
        };
        
        $endDate = match($dateRange) {
            'yesterday' => now()->subDay()->endOfDay(),
            default => null,
        };

        $depositStats = $this->getTransactionStats(Transaction::TYPE_DEPOSIT, $startDate, $endDate);
        $withdrawalStats = $this->getTransactionStats(Transaction::TYPE_WITHDRAWAL, $startDate, $endDate);
        $investmentStats = $this->getTransactionStats(Transaction::TYPE_INVESTMENT, $startDate, $endDate);
        $emailStats = EmailLogService::getStats($startDate, $endDate);

        return view('admin.system-logs.index', [
            'depositStats' => $depositStats,
            'withdrawalStats' => $withdrawalStats,
            'investmentStats' => $investmentStats,
            'emailStats' => $emailStats,
            'dateRange' => $dateRange,
        ]);
    }

    public function deposits(Request $request)
    {
        return $this->transactionDetails($request, Transaction::TYPE_DEPOSIT, 'Deposits');
    }

    public function withdrawals(Request $request)
    {
        return $this->transactionDetails($request, Transaction::TYPE_WITHDRAWAL, 'Withdrawals');
    }

    public function investments(Request $request)
    {
        return $this->transactionDetails($request, Transaction::TYPE_INVESTMENT, 'Investments');
    }

    public function emails(Request $request)
    {
        $query = EmailLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->appends($request->query());

        $statsQuery = EmailLog::query();
        if ($request->filled('date_from')) {
            $statsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $statsQuery->whereDate('created_at', '<=', $request->date_to);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'sent' => (clone $statsQuery)->sent()->count(),
            'failed' => (clone $statsQuery)->failed()->count(),
            'pending' => (clone $statsQuery)->pending()->count(),
        ];

        return view('admin.system-logs.emails', [
            'logs' => $logs,
            'stats' => $stats,
            'types' => EmailLog::getTypes(),
            'filters' => $request->only(['status', 'type', 'search', 'date_from', 'date_to']),
        ]);
    }

    protected function transactionDetails(Request $request, string $type, string $title)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $period = $request->period;

        if ($period && $period !== 'custom') {
            $dates = $this->getPeriodDates($period);
            $dateFrom = $dates['from'];
            $dateTo = $dates['to'];
        }

        $query = Transaction::with('user')
            ->where('type', $type)
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->paginate(25)->appends($request->query());

        $statsQuery = Transaction::where('type', $type);
        if ($dateFrom) {
            $statsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $statsQuery->whereDate('created_at', '<=', $dateTo);
        }
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'failed' => (clone $statsQuery)->where('status', 'failed')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
        ];

        return view('admin.system-logs.transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
            'title' => $title,
            'type' => $type,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'period' => $period,
            ],
        ]);
    }

    protected function getPeriodDates(string $period): array
    {
        $today = now();
        
        return match($period) {
            'today' => [
                'from' => $today->format('Y-m-d'),
                'to' => $today->format('Y-m-d'),
            ],
            'yesterday' => [
                'from' => $today->subDay()->format('Y-m-d'),
                'to' => $today->format('Y-m-d'),
            ],
            'this_week' => [
                'from' => $today->startOfWeek()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'this_month' => [
                'from' => $today->startOfMonth()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            default => [
                'from' => null,
                'to' => null,
            ],
        };
    }

    protected function getTransactionStats(string $type, $startDate = null, $endDate = null): array
    {
        $query = Transaction::where('type', $type);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->completed()->count();
        $failed = (clone $query)->failed()->count();
        $pending = (clone $query)->pending()->count();
        $cancelled = (clone $query)->cancelled()->count();

        $totalAmount = (clone $query)->completed()->sum('amount');

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'total_amount' => $totalAmount,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }
}
