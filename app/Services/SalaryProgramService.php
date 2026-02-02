<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalaryStage;
use App\Models\SalaryApplication;
use App\Models\SalaryMonthlyEvaluation;
use App\Models\Transaction;
use App\Notifications\SalaryPaymentNotification;
use App\Notifications\SalaryFailedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryProgramService
{
    const GROWTH_RATE = 1.35;
    const MONTHLY_DIRECT_REQUIRED = 3;
    const MIN_INVESTMENT_PER_REFERRAL = 50;

    public function checkEligibility(User $user): array
    {
        if (SalaryApplication::hasActiveApplication($user->id)) {
            return [
                'eligible' => false,
                'reason' => 'already_enrolled',
                'message' => 'You are already enrolled in the salary program.',
            ];
        }

        $stage1 = SalaryStage::getStageByOrder(1);
        if (!$stage1 || !$stage1->is_active) {
            return [
                'eligible' => false,
                'reason' => 'program_unavailable',
                'message' => 'The salary program is currently not available.',
            ];
        }

        $userStats = $this->getUserStats($user);

        $meetsDirectMembers = $userStats['direct_members'] >= $stage1->direct_members_required;
        $meetsSelfDeposit = $userStats['self_deposit'] >= $stage1->self_deposit_required;
        $meetsTeam = $userStats['team_count'] >= $stage1->team_required;

        $allMet = $meetsDirectMembers && $meetsSelfDeposit && $meetsTeam;

        return [
            'eligible' => $allMet,
            'reason' => $allMet ? 'eligible' : 'requirements_not_met',
            'stage' => $stage1,
            'requirements' => [
                'direct_members' => [
                    'required' => $stage1->direct_members_required,
                    'current' => $userStats['direct_members'],
                    'met' => $meetsDirectMembers,
                ],
                'self_deposit' => [
                    'required' => $stage1->self_deposit_required,
                    'current' => $userStats['self_deposit'],
                    'met' => $meetsSelfDeposit,
                ],
                'team' => [
                    'required' => $stage1->team_required,
                    'current' => $userStats['team_count'],
                    'met' => $meetsTeam,
                ],
            ],
            'user_stats' => $userStats,
        ];
    }

    public function getUserStats(User $user): array
    {
        $directReferrals = User::where('sponsor_id', $user->id)
            ->whereHas('investments', function ($q) {
                $q->where('status', 'active')
                  ->where('amount', '>=', self::MIN_INVESTMENT_PER_REFERRAL);
            })
            ->count();

        $selfDeposit = $user->investments()
            ->where('status', 'active')
            ->sum('amount');

        $teamCount = $this->getTeamCount($user);

        return [
            'direct_members' => $directReferrals,
            'self_deposit' => (float) $selfDeposit,
            'team_count' => $teamCount,
        ];
    }

    protected function getTeamCount(User $user): int
    {
        $totalTeam = 0;
        $currentLevelIds = [$user->id];

        for ($level = 1; $level <= 20; $level++) {
            $nextLevelUsers = User::whereIn('sponsor_id', $currentLevelIds)
                ->whereHas('investments', function ($q) {
                    $q->where('status', 'active')
                      ->where('amount', '>=', self::MIN_INVESTMENT_PER_REFERRAL);
                })
                ->pluck('id')
                ->toArray();

            if (empty($nextLevelUsers)) {
                break;
            }

            $totalTeam += count($nextLevelUsers);
            $currentLevelIds = $nextLevelUsers;
        }

        return $totalTeam;
    }

    public function apply(User $user): array
    {
        $eligibility = $this->checkEligibility($user);

        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['message'] ?? 'You are not eligible to apply.',
            ];
        }

        $stage = $eligibility['stage'];
        $stats = $eligibility['user_stats'];

        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();

        // Calculate target as NEW members to add (35% of stage requirement)
        // User must bring in this many NEW team members after enrollment
        $stageTeamRequired = $stage->team_required;
        $targetNewMembers = (int) ceil($stageTeamRequired * (self::GROWTH_RATE - 1)); // 35% growth = 0.35

        $application = SalaryApplication::create([
            'user_id' => $user->id,
            'salary_stage_id' => $stage->id,
            'applied_at' => now(),
            'baseline_team_count' => $stats['team_count'], // User's actual team at enrollment
            'baseline_direct_count' => $stats['direct_members'],
            'baseline_self_deposit' => $stats['self_deposit'],
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'current_target_team' => $targetNewMembers, // This is now NEW members needed
            'current_target_direct_new' => self::MONTHLY_DIRECT_REQUIRED,
            'months_completed' => 0,
            'status' => SalaryApplication::STATUS_ACTIVE,
        ]);

        return [
            'success' => true,
            'message' => 'Successfully enrolled in the salary program!',
            'application' => $application,
            'targets' => [
                'team' => $targetNewMembers,
                'direct_new' => self::MONTHLY_DIRECT_REQUIRED,
                'period_end' => $periodEnd->format('M d, Y'),
            ],
        ];
    }

    public function evaluateMonthEnd(SalaryApplication $application): array
    {
        if ($this->hasEvaluationForPeriod($application)) {
            throw new \Exception("Evaluation already exists for this period");
        }
        
        $user = $application->user;
        $currentStats = $this->getUserStats($user);

        // Calculate starting counts for this period
        $startingTeamCount = $application->baseline_team_count;
        $startingDirectCount = $application->baseline_direct_count;
        
        if ($application->months_completed > 0) {
            $lastEval = $application->evaluations()
                ->orderBy('month_number', 'desc')
                ->first();
            if ($lastEval) {
                // Update starting counts based on last evaluation's ending counts
                $startingTeamCount = $lastEval->starting_team_count + $lastEval->achieved_team_new;
                $startingDirectCount = $lastEval->starting_direct_count + $lastEval->achieved_direct_new;
            }
        }

        // Calculate NEW members added since period start
        $newTeamMembers = max(0, $currentStats['team_count'] - $startingTeamCount);
        $newDirectMembers = max(0, $currentStats['direct_members'] - $startingDirectCount);

        // Check if targets are met (targets are for NEW members, not total)
        $metTeamTarget = $newTeamMembers >= $application->current_target_team;
        $metDirectTarget = $newDirectMembers >= $application->current_target_direct_new;
        $passed = $metTeamTarget && $metDirectTarget;

        // Use the application's current stage for THIS evaluation's salary
        // User must receive current stage salary before advancing
        $evaluationStage = $application->salaryStage;
        
        // Determine if user qualifies for next stage (for advancement AFTER this evaluation)
        $nextStage = $this->determineCurrentStage($currentStats, $application->salaryStage);

        $evaluation = SalaryMonthlyEvaluation::create([
            'user_id' => $user->id,
            'salary_application_id' => $application->id,
            'salary_stage_id' => $evaluationStage->id,
            'month_number' => $application->months_completed + 1,
            'period_start' => $application->current_period_start,
            'period_end' => $application->current_period_end,
            'target_team' => $application->current_target_team,
            'achieved_team_new' => $newTeamMembers,
            'starting_team_count' => $startingTeamCount,
            'target_direct_new' => $application->current_target_direct_new,
            'achieved_direct_new' => $newDirectMembers,
            'starting_direct_count' => $startingDirectCount,
            'passed' => $passed,
            'salary_amount' => $passed ? $evaluationStage->salary_amount : null,
        ]);

        if ($passed) {
            // Salary payment is now manual - admin must approve and pay
            // The evaluation is created with salary_paid = false by default
            
            // Advance to next stage AFTER recording current stage's salary
            if ($application->salary_stage_id !== $nextStage->id) {
                $application->update(['salary_stage_id' => $nextStage->id]);
            }
            
            // Use the new stage's team requirement for target, current team as new baseline
            $application->setNextMonthTargets($nextStage->team_required, $currentStats['team_count']);
            
            // Note: Payment notification will be sent when admin manually approves the salary
        } else {
            $application->markFailed();
            
            try {
                $user->notify(new SalaryFailedNotification(
                    $application->current_target_team,
                    $currentStats['team_count'],
                    $application->current_target_direct_new,
                    $newDirectMembers
                ));
            } catch (\Exception $e) {
                Log::error("Failed to send salary failed notification: {$e->getMessage()}");
            }
        }

        return [
            'passed' => $passed,
            'evaluation' => $evaluation,
            'evaluated_stage' => $evaluationStage,
            'next_stage' => $passed ? $nextStage : null,
            'stats' => [
                'team_target' => $application->current_target_team,
                'team_achieved' => $currentStats['team_count'],
                'direct_target' => $application->current_target_direct_new,
                'direct_achieved' => $newDirectMembers,
            ],
        ];
    }

    protected function determineCurrentStage(array $stats, ?SalaryStage $currentApplicationStage = null): SalaryStage
    {
        $stages = SalaryStage::active()->ordered()->get();
        
        if (!$currentApplicationStage) {
            return $stages->first();
        }
        
        $currentStageOrder = $currentApplicationStage->stage_order;
        $nextStage = SalaryStage::active()
            ->where('stage_order', $currentStageOrder + 1)
            ->first();
        
        if (!$nextStage) {
            return $currentApplicationStage;
        }
        
        $meetsNextStage = $stats['direct_members'] >= $nextStage->direct_members_required
            && $stats['self_deposit'] >= $nextStage->self_deposit_required
            && $stats['team_count'] >= $nextStage->team_required;
        
        if ($meetsNextStage) {
            return $nextStage;
        }
        
        return $currentApplicationStage;
    }

    /**
     * Manually pay salary for an evaluation (admin approval)
     */
    public function paySalaryManually(SalaryMonthlyEvaluation $evaluation, ?int $approvedByAdminId = null): void
    {
        if ($evaluation->salary_paid) {
            throw new \Exception("Salary has already been paid for this evaluation.");
        }

        if (!$evaluation->passed) {
            throw new \Exception("Cannot pay salary for failed evaluation.");
        }

        DB::transaction(function () use ($evaluation, $approvedByAdminId) {
            $user = $evaluation->user;
            $amount = $evaluation->salary_amount;

            $user->increment('balance', $amount);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'salary',
                'amount' => $amount,
                'status' => 'completed',
                'description' => "Monthly salary payment - Stage {$evaluation->salaryStage->name} (Month {$evaluation->month_number})",
                'metadata' => [
                    'evaluation_id' => $evaluation->id,
                    'stage_id' => $evaluation->salary_stage_id,
                    'month_number' => $evaluation->month_number,
                    'approved_by_admin_id' => $approvedByAdminId,
                ],
            ]);

            $evaluation->update([
                'salary_paid' => true,
                'paid_at' => now(),
                'transaction_id' => $transaction->id,
            ]);

            try {
                $user->notify(new SalaryPaymentNotification(
                    (float) $evaluation->salary_amount,
                    $evaluation->month_number,
                    $evaluation->salaryStage->name
                ));
            } catch (\Exception $e) {
                Log::error("Failed to send salary payment notification: {$e->getMessage()}");
            }
        });
    }

    /**
     * @deprecated Use paySalaryManually instead - salary payments now require admin approval
     */
    protected function paySalary(SalaryMonthlyEvaluation $evaluation): void
    {
        DB::transaction(function () use ($evaluation) {
            $user = $evaluation->user;
            $amount = $evaluation->salary_amount;

            $user->increment('balance', $amount);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'salary',
                'amount' => $amount,
                'status' => 'completed',
                'description' => "Monthly salary payment - Stage {$evaluation->salaryStage->name} (Month {$evaluation->month_number})",
                'metadata' => [
                    'evaluation_id' => $evaluation->id,
                    'stage_id' => $evaluation->salary_stage_id,
                    'month_number' => $evaluation->month_number,
                ],
            ]);

            $evaluation->markPaid($transaction->id);
        });
    }

    public function getApplicationProgress(SalaryApplication $application): array
    {
        $user = $application->user;
        $currentStats = $this->getUserStats($user);

        // Get starting counts for this period
        $startingTeamCount = $application->baseline_team_count;
        $startingDirectCount = $application->baseline_direct_count;
        
        if ($application->months_completed > 0) {
            $lastEval = $application->evaluations()
                ->orderBy('month_number', 'desc')
                ->first();
            if ($lastEval) {
                $startingTeamCount = $lastEval->starting_team_count + $lastEval->achieved_team_new;
                $startingDirectCount = $lastEval->starting_direct_count + $lastEval->achieved_direct_new;
            }
        }

        // Calculate NEW members added since period start
        $newTeamMembers = max(0, $currentStats['team_count'] - $startingTeamCount);
        $newDirectMembers = max(0, $currentStats['direct_members'] - $startingDirectCount);

        // Progress toward NEW member targets
        $teamProgress = $application->current_target_team > 0 
            ? min(100, round(($newTeamMembers / $application->current_target_team) * 100, 1))
            : 0;
        $directProgress = $application->current_target_direct_new > 0
            ? min(100, round(($newDirectMembers / $application->current_target_direct_new) * 100, 1))
            : 0;

        $daysRemaining = (int) max(0, Carbon::now()->diffInDays($application->current_period_end, false));
        
        // Current stage is what the user is actually enrolled at
        $currentStage = $application->salaryStage;
        
        // Next stage is what they could advance to after passing this month
        $nextStage = $this->determineCurrentStage($currentStats, $application->salaryStage);
        $canAdvance = $nextStage->id !== $currentStage->id;

        return [
            'application' => $application,
            'current_stage' => $currentStage,
            'next_stage' => $canAdvance ? $nextStage : null,
            'can_advance' => $canAdvance,
            'period' => [
                'start' => $application->current_period_start->format('M d, Y'),
                'end' => $application->current_period_end->format('M d, Y'),
                'days_remaining' => $daysRemaining,
            ],
            'team' => [
                'target' => $application->current_target_team,
                'current' => $newTeamMembers,
                'progress' => $teamProgress,
                'met' => $newTeamMembers >= $application->current_target_team,
            ],
            'direct' => [
                'target' => $application->current_target_direct_new,
                'current' => $newDirectMembers,
                'progress' => $directProgress,
                'met' => $newDirectMembers >= $application->current_target_direct_new,
            ],
            'current_salary' => $currentStage->salary_amount,
            'next_salary' => $canAdvance ? $nextStage->salary_amount : null,
            'months_completed' => $application->months_completed,
            'evaluations' => $application->evaluations()->orderBy('month_number', 'desc')->get(),
        ];
    }

    public function getUsersEligibleForNotification(): \Illuminate\Support\Collection
    {
        $stage1 = SalaryStage::getStageByOrder(1);
        if (!$stage1 || !$stage1->is_active) {
            return collect();
        }

        $enrolledUserIds = SalaryApplication::active()->pluck('user_id');

        return User::where('status', 'active')
            ->whereNotIn('id', $enrolledUserIds)
            ->get()
            ->filter(function ($user) {
                $eligibility = $this->checkEligibility($user);
                return $eligibility['eligible'];
            });
    }

    public function getActiveApplicationsDueForEvaluation(): \Illuminate\Support\Collection
    {
        return SalaryApplication::active()
            ->where('current_period_end', '<=', Carbon::yesterday())
            ->whereDoesntHave('evaluations', function ($q) {
                $q->whereColumn('period_end', 'salary_applications.current_period_end');
            })
            ->with(['user', 'salaryStage'])
            ->get();
    }

    public function getAllActiveApplications(): \Illuminate\Support\Collection
    {
        return SalaryApplication::active()
            ->whereDoesntHave('evaluations', function ($q) {
                $q->whereColumn('period_end', 'salary_applications.current_period_end');
            })
            ->with(['user', 'salaryStage'])
            ->get();
    }

    public function hasEvaluationForPeriod(SalaryApplication $application): bool
    {
        return SalaryMonthlyEvaluation::where('salary_application_id', $application->id)
            ->where('period_end', $application->current_period_end)
            ->exists();
    }
}
