<?php

namespace App\Console\Commands;

use App\Services\SalaryProgramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SalaryNotifyEligibleCommand extends Command
{
    protected $signature = 'salary:notify-eligible 
                            {--dry-run : Show eligible users without sending notifications}';

    protected $description = 'Notify eligible users about the salary program at the start of each month';

    protected SalaryProgramService $salaryService;

    public function __construct(SalaryProgramService $salaryService)
    {
        parent::__construct();
        $this->salaryService = $salaryService;
    }

    public function handle(): int
    {
        if (!getSetting('salary_program_enabled', true)) {
            $this->warn('Monthly Salary Program is currently disabled. Skipping notifications.');
            return Command::SUCCESS;
        }
        
        $this->info('Checking for eligible users to notify...');
        
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no notifications will be sent.');
        }

        $eligibleUsers = $this->salaryService->getUsersEligibleForNotification();

        if ($eligibleUsers->isEmpty()) {
            $this->info('No eligible users to notify.');
            return Command::SUCCESS;
        }

        $this->info("Found {$eligibleUsers->count()} eligible users.");
        $this->newLine();

        foreach ($eligibleUsers as $user) {
            $this->line("User: {$user->first_name} {$user->last_name} ({$user->email})");
            
            if (!$dryRun) {
                try {
                    $user->notify(new \App\Notifications\SalaryEligibleNotification());
                    $this->info("  => Notification sent");
                } catch (\Exception $e) {
                    $this->error("  => Failed: {$e->getMessage()}");
                    Log::error("Failed to send salary eligibility notification to user {$user->id}: {$e->getMessage()}");
                }
            } else {
                $this->info("  => Would send notification");
            }
        }

        $this->newLine();
        $this->info("Notification process complete.");

        return Command::SUCCESS;
    }
}
