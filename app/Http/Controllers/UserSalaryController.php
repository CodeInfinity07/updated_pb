<?php

namespace App\Http\Controllers;

use App\Services\SalaryProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class UserSalaryController extends Controller
{
    protected SalaryProgramService $salaryService;

    public function __construct(SalaryProgramService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    protected function checkProgramEnabled()
    {
        if (!getSetting('salary_program_enabled', true)) {
            abort(403, 'The Monthly Salary Program is currently unavailable.');
        }
    }

    public function index(): View
    {
        $this->checkProgramEnabled();
        
        $user = Auth::user();
        $application = $user->activeSalaryApplication();
        
        if ($application) {
            $progress = $this->salaryService->getApplicationProgress($application);
            return view('salary.progress', compact('user', 'application', 'progress'));
        }
        
        $eligibility = $this->salaryService->checkEligibility($user);
        return view('salary.index', compact('user', 'eligibility'));
    }

    public function apply(Request $request): RedirectResponse
    {
        $this->checkProgramEnabled();
        
        $user = Auth::user();
        
        $result = $this->salaryService->apply($user);
        
        if ($result['success']) {
            return redirect()->route('salary.index')
                ->with('success', $result['message']);
        }
        
        return redirect()->route('salary.index')
            ->with('error', $result['message']);
    }

    public function checkEligibility(): JsonResponse
    {
        if (!getSetting('salary_program_enabled', true)) {
            return response()->json(['error' => 'Program is currently unavailable'], 403);
        }
        
        $user = Auth::user();
        $eligibility = $this->salaryService->checkEligibility($user);
        
        return response()->json($eligibility);
    }

    public function history(): View
    {
        $this->checkProgramEnabled();
        
        $user = Auth::user();
        
        $evaluations = $user->salaryEvaluations()
            ->with(['salaryStage', 'application'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $totalEarned = $user->salaryEvaluations()
            ->where('salary_paid', true)
            ->sum('salary_amount');
        
        return view('salary.history', compact('user', 'evaluations', 'totalEarned'));
    }
}
