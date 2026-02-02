<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\AdminLeaderboardController;
use App\Services\LeaderboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateLeaderboardPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboards:calculate-positions 
                            {--dry-run : Show what would be calculated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate positions for all active leaderboards';

    protected $leaderboardService;
    protected $adminController;

    /**
     * Create a new command instance.
     */
    public function __construct(LeaderboardService $leaderboardService)
    {
        parent::__construct();
        $this->leaderboardService = $leaderboardService;
        $this->adminController = new AdminLeaderboardController($leaderboardService);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting leaderboard position calculations...');
        $this->line('');

        try {
            // First, mark any expired leaderboards as completed
            $expiredCount = $this->markExpiredLeaderboardsAsCompleted();
            if ($expiredCount > 0) {
                $this->info("Marked {$expiredCount} expired leaderboard(s) as completed.");
                $this->line('');
            }

            // Get active leaderboards for preview
            $activeLeaderboards = \App\Models\Leaderboard::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            if ($activeLeaderboards->isEmpty()) {
                $this->info('No active leaderboards found.');
                return 0;
            }

            $this->info("Found {$activeLeaderboards->count()} active leaderboard(s):");
            
            // Display leaderboards
            $headers = ['ID', 'Title', 'Type', 'Participants', 'Start Date', 'End Date'];
            $rows = [];
            
            foreach ($activeLeaderboards as $leaderboard) {
                $rows[] = [
                    $leaderboard->id,
                    \Illuminate\Support\Str::limit($leaderboard->title, 30),
                    ucfirst($leaderboard->type),
                    $leaderboard->getParticipantsCount(),
                    $leaderboard->start_date->format('M j, Y H:i'),
                    $leaderboard->end_date->format('M j, Y H:i')
                ];
            }
            
            $this->table($headers, $rows);
            $this->line('');

            // Handle dry run
            if ($this->option('dry-run')) {
                $this->info('DRY RUN: Would calculate positions for the above leaderboards.');
                return 0;
            }

            // Proceed automatically without confirmation (suitable for scheduler)
            $this->info('Proceeding with position calculations...');

            // Start progress bar
            $progressBar = $this->output->createProgressBar($activeLeaderboards->count());
            $progressBar->start();

            // Calculate positions
            $result = $this->adminController->calculateAllActivePositionsConsole();

            $progressBar->finish();
            $this->line('');
            $this->line('');

            // Display results
            if ($result['success']) {
                $this->info('✅ Position calculation completed successfully!');
            } else {
                $this->warn('⚠️  Position calculation completed with some errors.');
            }

            $this->line('');
            $this->info("Summary:");
            $this->line("  Total leaderboards: {$result['total_leaderboards']}");
            $this->line("  Successful: {$result['successful']}");
            $this->line("  Failed: {$result['failed']}");

            // Display errors if any
            if (!empty($result['errors'])) {
                $this->line('');
                $this->error('Errors encountered:');
                foreach ($result['errors'] as $error) {
                    if (isset($error['leaderboard_id'])) {
                        $this->line("  • Leaderboard {$error['leaderboard_id']} ({$error['title']}): {$error['error']}");
                    } else {
                        $this->line("  • {$error['error']}");
                    }
                }
            }

            // Log the command execution
            Log::info('Leaderboard positions calculated via console command', [
                'total' => $result['total_leaderboards'],
                'successful' => $result['successful'],
                'failed' => $result['failed'],
                'command_options' => $this->options()
            ]);

            return $result['success'] ? 0 : 1;

        } catch (\Exception $e) {
            $this->error('❌ Failed to calculate leaderboard positions: ' . $e->getMessage());
            
            Log::error('Console command failed: leaderboards:calculate-positions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    /**
     * Mark expired leaderboards as completed
     * 
     * @return int Number of leaderboards marked as completed
     */
    private function markExpiredLeaderboardsAsCompleted(): int
    {
        $expiredLeaderboards = \App\Models\Leaderboard::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredLeaderboards as $leaderboard) {
            try {
                // Calculate final positions before marking as completed
                $this->leaderboardService->calculatePositions($leaderboard);
                
                $leaderboard->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                
                $count++;
                
                Log::info('Leaderboard marked as completed', [
                    'leaderboard_id' => $leaderboard->id,
                    'title' => $leaderboard->title,
                    'end_date' => $leaderboard->end_date->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to mark leaderboard as completed', [
                    'leaderboard_id' => $leaderboard->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}