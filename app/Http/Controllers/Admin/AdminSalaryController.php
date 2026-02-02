<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryStage;
use App\Models\SalaryApplication;
use App\Models\SalaryMonthlyEvaluation;
use App\Models\User;
use App\Services\SalaryProgramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminSalaryController extends Controller
{
    public function index()
    {
        $stages = SalaryStage::active()->ordered()->get();
        
        $statistics = [
            'total_applications' => SalaryApplication::count(),
            'active_applications' => SalaryApplication::where('status', 'active')->count(),
            'total_evaluations' => SalaryMonthlyEvaluation::count(),
            'passed_evaluations' => SalaryMonthlyEvaluation::where('passed', true)->count(),
            'total_paid' => SalaryMonthlyEvaluation::where('salary_paid', true)->sum('salary_amount'),
            'pending_payments' => SalaryMonthlyEvaluation::where('passed', true)->where('salary_paid', false)->count(),
        ];

        return view('admin.salary.index', compact('stages', 'statistics'));
    }

    public function history(Request $request)
    {
        $stages = SalaryStage::ordered()->get();
        
        $query = SalaryMonthlyEvaluation::with(['user', 'salaryStage'])
            ->where('salary_paid', true)
            ->orderBy('paid_at', 'desc');

        if ($request->get('stage_id')) {
            $query->where('salary_stage_id', $request->get('stage_id'));
        }

        if ($request->get('date_from')) {
            $query->whereDate('paid_at', '>=', $request->get('date_from'));
        }

        if ($request->get('date_to')) {
            $query->whereDate('paid_at', '<=', $request->get('date_to'));
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $payouts = $query->paginate(20);

        $statistics = [
            'total_paid' => SalaryMonthlyEvaluation::where('salary_paid', true)->sum('salary_amount'),
            'total_payments' => SalaryMonthlyEvaluation::where('salary_paid', true)->count(),
        ];

        $filters = [
            'stage_id' => $request->get('stage_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        return view('admin.salary.history', compact('payouts', 'stages', 'statistics', 'filters'));
    }

    public function exportHistory(Request $request)
    {
        $query = SalaryMonthlyEvaluation::with(['user', 'salaryStage'])
            ->where('salary_paid', true)
            ->orderBy('paid_at', 'desc');

        if ($request->get('stage_id')) {
            $query->where('salary_stage_id', $request->get('stage_id'));
        }

        if ($request->get('date_from')) {
            $query->whereDate('paid_at', '>=', $request->get('date_from'));
        }

        if ($request->get('date_to')) {
            $query->whereDate('paid_at', '<=', $request->get('date_to'));
        }

        $payouts = $query->get();

        $filename = 'salary_payments_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payouts) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'User', 'Email', 'Stage', 'Month', 'Amount', 'Paid At']);

            foreach ($payouts as $payout) {
                fputcsv($file, [
                    $payout->id,
                    $payout->user->first_name . ' ' . $payout->user->last_name,
                    $payout->user->email,
                    $payout->salaryStage->name,
                    $payout->month_number,
                    $payout->salary_amount,
                    $payout->paid_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function userProgress(User $user)
    {
        $salaryService = app(SalaryProgramService::class);
        $eligibility = $salaryService->checkEligibility($user);
        
        $application = SalaryApplication::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        $evaluations = SalaryMonthlyEvaluation::where('user_id', $user->id)
            ->with('salaryStage')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.salary.user-progress', compact('user', 'eligibility', 'application', 'evaluations'));
    }

    public function applications(Request $request)
    {
        $query = SalaryApplication::with(['user', 'salaryStage'])
            ->orderBy('created_at', 'desc');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20);

        $stats = [
            'total' => SalaryApplication::count(),
            'active' => SalaryApplication::where('status', 'active')->count(),
            'failed' => SalaryApplication::where('status', 'failed')->count(),
            'graduated' => SalaryApplication::where('status', 'graduated')->count(),
        ];

        return view('admin.salary.applications', compact('applications', 'stats'));
    }

    public function evaluations(Request $request)
    {
        $query = SalaryMonthlyEvaluation::with(['user', 'salaryStage', 'application'])
            ->orderBy('created_at', 'desc');

        if ($request->get('passed') !== null) {
            $query->where('passed', $request->get('passed') === '1');
        }

        if ($request->get('paid') !== null) {
            $query->where('salary_paid', $request->get('paid') === '1');
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $evaluations = $query->paginate(20);

        $stats = [
            'total' => SalaryMonthlyEvaluation::count(),
            'passed' => SalaryMonthlyEvaluation::where('passed', true)->count(),
            'failed' => SalaryMonthlyEvaluation::where('passed', false)->count(),
            'total_paid' => SalaryMonthlyEvaluation::where('salary_paid', true)->sum('salary_amount'),
            'pending_payments' => SalaryMonthlyEvaluation::where('passed', true)->where('salary_paid', false)->count(),
        ];

        return view('admin.salary.evaluations', compact('evaluations', 'stats'));
    }

    public function runEvaluation(Request $request): JsonResponse
    {
        $salaryProgramService = app(SalaryProgramService::class);
        
        $applications = $salaryProgramService->getActiveApplicationsDueForEvaluation();
        
        if ($applications->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No applications due for evaluation.',
                'evaluated' => 0,
            ]);
        }

        $passed = 0;
        $failed = 0;

        foreach ($applications as $application) {
            try {
                $result = $salaryProgramService->evaluateMonthEnd($application);
                if ($result['passed']) {
                    $passed++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                \Log::error("Salary evaluation error for application {$application->id}: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Evaluation complete. Passed: {$passed}, Failed: {$failed}",
            'passed' => $passed,
            'failed' => $failed,
        ]);
    }

    /**
     * Manually approve and pay a salary evaluation
     */
    public function approveSalaryPayment(Request $request, SalaryMonthlyEvaluation $evaluation): JsonResponse
    {
        if (!$evaluation->passed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot pay salary for failed evaluation.',
            ], 400);
        }

        if ($evaluation->salary_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Salary has already been paid.',
            ], 400);
        }

        $salaryProgramService = app(SalaryProgramService::class);
        
        try {
            $salaryProgramService->paySalaryManually($evaluation, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Salary of \${$evaluation->salary_amount} paid to {$evaluation->user->first_name} {$evaluation->user->last_name}.",
            ]);
        } catch (\Exception $e) {
            \Log::error("Manual salary payment error for evaluation {$evaluation->id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to process salary payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk approve and pay multiple salary evaluations
     */
    public function bulkApproveSalaryPayments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_ids' => 'required|array',
            'evaluation_ids.*' => 'exists:salary_monthly_evaluations,id',
        ]);

        $salaryProgramService = app(SalaryProgramService::class);
        $paid = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($validated['evaluation_ids'] as $evaluationId) {
            $evaluation = SalaryMonthlyEvaluation::find($evaluationId);
            
            if (!$evaluation || !$evaluation->passed || $evaluation->salary_paid) {
                $skipped++;
                continue;
            }

            try {
                $salaryProgramService->paySalaryManually($evaluation, auth()->id());
                $paid++;
            } catch (\Exception $e) {
                \Log::error("Bulk salary payment error for evaluation {$evaluationId}: {$e->getMessage()}");
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk payment complete. Paid: {$paid}, Failed: {$failed}, Skipped: {$skipped}",
            'paid' => $paid,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);
    }
}
