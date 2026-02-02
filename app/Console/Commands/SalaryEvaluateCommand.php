<?php

namespace App\Console\Commands;

use App\Services\SalaryProgramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SalaryEvaluateCommand extends Command
{
    protected $signature = 'salary:evaluate 
                            {--dry-run : Run without making any changes}
                            {--user= : Evaluate a specific user ID only}
                            {--force : Force evaluation even if period has not ended}';

    protected $description = 'Evaluate monthly salary program targets and process payments';

    protected SalaryProgramService $salaryService;

    public function __construct(SalaryProgramService $salaryService)
    {
        parent::__construct();
        $this->salaryService = $salaryService;
    }

    public function handle(): int
    {
        if (!getSetting('salary_program_enabled', true)) {
            $this->warn('Monthly Salary Program is currently disabled. Skipping evaluation.');
            return Command::SUCCESS;
        }
        
        $this->info('Starting salary program evaluation...');
        
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no changes will be made.');
        }

        if ($force) {
            $this->warn('FORCE mode - evaluating regardless of period end date.');
            $applications = $this->salaryService->getAllActiveApplications();
        } else {
            $applications = $this->salaryService->getActiveApplicationsDueForEvaluation();
        }

        if ($userId) {
            $applications = $applications->where('user_id', (int) $userId);
        }

        if ($applications->isEmpty()) {
            $this->info('No applications due for evaluation.');
            return Command::SUCCESS;
        }

        $this->info("Found {$applications->count()} applications to evaluate.");
        $this->newLine();

        $passed = 0;
        $failed = 0;

        foreach ($applications as $application) {
            $user = $application->user;
            $this->line("Evaluating: {$user->first_name} {$user->last_name} (ID: {$user->id})");

            if ($dryRun) {
                $stats = $this->salaryService->getUserStats($user);
                $newTeamMembers = max(0, $stats['team_count'] - $application->baseline_team_count);
                $newDirectMembers = max(0, $stats['direct_members'] - $application->baseline_direct_count);
                $teamMet = $newTeamMembers >= $application->current_target_team;
                $directMet = $newDirectMembers >= $application->current_target_direct_new;
                
                $this->line("  - New Team Members: {$newTeamMembers} / {$application->current_target_team} " . ($teamMet ? '✓' : '✗'));
                $this->line("  - New Direct Members: {$newDirectMembers} / {$application->current_target_direct_new} " . ($directMet ? '✓' : '✗'));
                
                if ($teamMet && $directMet) {
                    $this->info("  => Would PASS");
                    $passed++;
                } else {
                    $this->error("  => Would FAIL");
                    $failed++;
                }
            } else {
                try {
                    $result = $this->salaryService->evaluateMonthEnd($application);
                    
                    if ($result['passed']) {
                        $this->info("  => PASSED - Salary ${$result['evaluation']->salary_amount} paid");
                        $passed++;
                    } else {
                        $this->error("  => FAILED - Application marked as failed");
                        $failed++;
                    }
                    
                    $this->line("  - Team: {$result['stats']['team_achieved']} / {$result['stats']['team_target']}");
                    $this->line("  - Direct: {$result['stats']['direct_achieved']} / {$result['stats']['direct_target']}");
                } catch (\Exception $e) {
                    $this->error("  => ERROR: {$e->getMessage()}");
                    Log::error("Salary evaluation error for user {$user->id}: {$e->getMessage()}");
                }
            }
            
            $this->newLine();
        }

        $this->newLine();
        $this->info("Evaluation complete:");
        $this->info("  - Passed: {$passed}");
        $this->info("  - Failed: {$failed}");

        return Command::SUCCESS;
    }
}
