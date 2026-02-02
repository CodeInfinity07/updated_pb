<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RankService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUserRanks extends Command
{
    protected $signature = 'ranks:check {--user= : Check a specific user ID} {--pay-rewards : Auto-pay rewards when rank is achieved}';

    protected $description = 'Check all active users for rank qualifications and award ranks';

    public function handle(RankService $rankService): int
    {
        $userId = $this->option('user');
        $payRewards = $this->option('pay-rewards');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            $this->info("Checking ranks for user: {$user->full_name} ({$user->email})");
            $result = $rankService->checkAndAwardRanks($user, $payRewards);

            $this->info("New ranks awarded: " . count($result['new_ranks']));
            foreach ($result['new_ranks'] as $rank) {
                $this->line("  - {$rank->name}");
            }
            if ($payRewards) {
                $this->info("Rewards paid: \${$result['rewards_paid']}");
            }

            return 0;
        }

        $this->info("Processing all active users for rank qualifications...");

        $stats = $rankService->processAllUsers($payRewards);

        $this->info("Completed!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Users Checked', $stats['users_checked']],
                ['Ranks Awarded', $stats['ranks_awarded']],
                ['Rewards Paid', "\${$stats['rewards_paid']}"],
            ]
        );

        Log::info('Rank check completed', $stats);

        return 0;
    }
}
