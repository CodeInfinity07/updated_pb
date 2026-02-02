<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserInvestment;
use App\Models\InvestmentPlan;
use App\Models\Cryptocurrency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class MigratePackageDeposits extends Command
{
    protected $signature = 'migrate:package-deposits 
                           {--connection=old_db : Old database connection name} 
                           {--dry-run : Preview migration without saving data}
                           {--batch-size=100 : Number of records to process per batch}
                           {--force : Skip confirmation prompts}';

    protected $description = 'Migrate package_deposits from legacy database to user_investments table';

    private array $userMapping = [];
    private array $errors = [];
    private ?int $activeInvestmentPlanId = null;
    private ?string $activeCryptoAddress = null;
    private array $stats = [
        'total_found' => 0,
        'migrated' => 0,
        'skipped_existing' => 0,
        'skipped_no_user' => 0,
        'errors' => 0,
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force');

        $this->showHeader($isDryRun);

        try {
            $this->setupMappings();
            $this->testConnection();
            $totalDeposits = $this->getTotalCount();
            
            if ($totalDeposits === 0) {
                $this->warn('No package deposits found to migrate!');
                return Command::SUCCESS;
            }

            $this->stats['total_found'] = $totalDeposits;
            $this->info("Found {$totalDeposits} package deposits to migrate");

            $this->showSampleData();
            
            if (!$force && !$this->confirm('Proceed with migration?', true)) {
                $this->info('Migration cancelled.');
                return Command::SUCCESS;
            }

            $this->processMigration($batchSize, $isDryRun);
            $this->showResults($isDryRun);

        } catch (Exception $e) {
            $this->handleError($e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function showHeader(bool $isDryRun): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║     Package Deposits to User Investments Migration     ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        if ($isDryRun) {
            $this->warn('┌────────────────────────────────────────┐');
            $this->warn('│      DRY RUN MODE - NO DATA SAVED      │');
            $this->warn('└────────────────────────────────────────┘');
            $this->newLine();
        }
    }

    private function setupMappings(): void
    {
        $this->info('Setting up mappings...');
        
        $this->buildUserMapping();
        $this->getActiveInvestmentPlan();
        $this->getActiveCryptoAddress();
        
        $this->newLine();
    }

    private function buildUserMapping(): void
    {
        $this->info('  → Loading existing user IDs...');

        $existingUserIds = User::pluck('id')->toArray();
        $this->userMapping = array_flip($existingUserIds);

        $this->info("    ✓ Found " . count($existingUserIds) . " existing users");
    }

    private function getActiveInvestmentPlan(): void
    {
        $this->info('  → Finding active investment plan...');

        $plan = InvestmentPlan::where('status', 'active')->first();
        
        if (!$plan) {
            throw new Exception('No active investment plan found. Please create one first.');
        }

        $this->activeInvestmentPlanId = $plan->id;
        $this->info("    ✓ Using plan: {$plan->name} (ID: {$plan->id})");
    }

    private function getActiveCryptoAddress(): void
    {
        $this->info('  → Finding active cryptocurrency...');

        $crypto = Cryptocurrency::where('is_active', true)->first();
        
        if ($crypto && $crypto->crypto_address) {
            $this->activeCryptoAddress = $crypto->crypto_address;
            $this->info("    ✓ Using crypto address: " . substr($this->activeCryptoAddress, 0, 20) . '...');
        } else {
            $this->activeCryptoAddress = null;
            $this->warn("    ⚠ No active cryptocurrency found, txn_id field will be skipped");
        }
    }

    private function testConnection(): void
    {
        $this->info('Testing old database connection...');
        DB::connection($this->option('connection'))
            ->select('SELECT COUNT(*) as count FROM package_deposits LIMIT 1');
        $this->info('  ✓ Connection successful!');
        $this->newLine();
    }

    private function getTotalCount(): int
    {
        return DB::connection($this->option('connection'))
            ->table('package_deposits')
            ->count();
    }

    private function showSampleData(): void
    {
        $sample = DB::connection($this->option('connection'))
            ->table('package_deposits')
            ->first();

        if (!$sample) return;

        $this->newLine();
        $this->info('Sample package_deposit data:');
        $userExists = isset($this->userMapping[$sample->user_id]) ? 'YES (same ID)' : 'NO - will skip';
        $this->table(['Field', 'Old Value', 'Mapped To'], [
            ['id', $sample->id ?? 'N/A', 'N/A (new ID generated)'],
            ['user_id', $sample->user_id ?? 'N/A', $userExists],
            ['plan_id', $sample->plan_id ?? 'N/A', $this->activeInvestmentPlanId],
            ['package_id', $sample->package_id ?? 'N/A', 'Stored in notes'],
            ['amount', $sample->amount ?? 'N/A', $sample->amount ?? 'N/A'],
            ['status', $sample->status ?? 'N/A', $this->mapStatus($sample->status ?? 0)],
            ['datetime', $sample->datetime ?? 'N/A', 'started_at'],
            ['last_earningDateTime', $sample->last_earningDateTime ?? 'N/A', 'last_payout_date'],
            ['auto_reinvest', $sample->auto_reinvest ?? 'N/A', 'Stored in notes'],
            ['compound', $sample->compound ?? 'N/A', 'Stored in notes'],
        ]);
        $this->newLine();
    }

    private function processMigration(int $batchSize, bool $isDryRun): void
    {
        $totalBatches = ceil($this->stats['total_found'] / $batchSize);
        $this->info("Processing {$totalBatches} batches of {$batchSize} deposits each...");
        
        $progressBar = $this->output->createProgressBar($this->stats['total_found']);
        $progressBar->start();

        if (!$isDryRun) {
            DB::beginTransaction();
        }

        try {
            DB::connection($this->option('connection'))
                ->table('package_deposits')
                ->orderBy('id')
                ->chunk($batchSize, function (Collection $batch) use ($progressBar, $isDryRun) {
                    foreach ($batch as $oldDeposit) {
                        try {
                            $this->migrateDeposit($oldDeposit, $isDryRun);
                        } catch (Exception $e) {
                            $this->recordError($oldDeposit, $e);
                        }
                        $progressBar->advance();
                    }
                });

            if (!$isDryRun) {
                DB::commit();
                $this->newLine(2);
                $this->info('✓ Migration committed to database');
            } else {
                $this->newLine(2);
                $this->warn('Dry run complete - no data was saved');
            }

        } catch (Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }
            throw $e;
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function migrateDeposit(object $oldDeposit, bool $isDryRun): void
    {
        $userId = $oldDeposit->user_id;

        if (!isset($this->userMapping[$userId])) {
            $this->stats['skipped_no_user']++;
            return;
        }

        $existingInvestment = UserInvestment::where('user_id', $userId)
            ->where('amount', (float) $oldDeposit->amount)
            ->where('created_at', $this->parseTimestamp($oldDeposit->datetime))
            ->first();

        if ($existingInvestment) {
            $this->stats['skipped_existing']++;
            return;
        }

        $startDate = $this->parseTimestamp($oldDeposit->datetime);
        $lastPayoutDate = $this->parseTimestamp($oldDeposit->last_earningDateTime);

        $plan = InvestmentPlan::find($this->activeInvestmentPlanId);
        $roiPercentage = $plan ? ($plan->interest_rate ?? 0.5) : 0.5;
        $durationDays = $plan ? ($plan->duration_days ?? 90) : 90;

        $investmentData = [
            'user_id' => $userId,
            'investment_plan_id' => $this->activeInvestmentPlanId,
            'amount' => (float) $oldDeposit->amount,
            'roi_percentage' => $roiPercentage,
            'duration_days' => $durationDays,
            'total_return' => (float) ($oldDeposit->amount * $roiPercentage / 100 * $durationDays),
            'daily_return' => (float) ($oldDeposit->amount * $roiPercentage / 100),
            'status' => $this->mapStatus($oldDeposit->status),
            'start_date' => $startDate ? $startDate->format('Y-m-d') : now()->format('Y-m-d'),
            'end_date' => $startDate ? $startDate->copy()->addDays($durationDays)->format('Y-m-d') : now()->addDays($durationDays)->format('Y-m-d'),
            'last_payout_date' => $lastPayoutDate ? $lastPayoutDate->format('Y-m-d') : null,
            'created_at' => $startDate ?? now(),
            'updated_at' => $this->parseTimestamp($oldDeposit->updated_at) ?? now(),
            'paid_return' => 0,
            'earnings_accumulated' => 0,
            'commission_earned' => 0,
            'expiry_multiplier' => 3,
            'bot_fee_applied' => false,
        ];

        if (!$isDryRun) {
            UserInvestment::create($investmentData);
        }

        $this->stats['migrated']++;
    }

    private function mapStatus($oldStatus): string
    {
        return match ((int) $oldStatus) {
            1 => 'active',
            2 => 'completed',
            3 => 'cancelled',
            default => 'active',
        };
    }

    private function parseTimestamp($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            if ($value instanceof Carbon) {
                return $value;
            }
            
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp($value);
            }
            
            return Carbon::parse($value);
        } catch (Exception $e) {
            return null;
        }
    }

    private function recordError(object $deposit, Exception $e): void
    {
        $this->stats['errors']++;
        $this->errors[] = [
            'old_id' => $deposit->id,
            'user_id' => $deposit->user_id,
            'amount' => $deposit->amount,
            'error' => $e->getMessage(),
        ];

        Log::error("Package deposit migration error", [
            'old_id' => $deposit->id,
            'error' => $e->getMessage(),
        ]);
    }

    private function showResults(bool $isDryRun): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║           Migration Results            ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        $this->table(['Metric', 'Count'], [
            ['Total Found', $this->stats['total_found']],
            ['Successfully Migrated', $this->stats['migrated']],
            ['Skipped (Already Exists)', $this->stats['skipped_existing']],
            ['Skipped (User Not Found)', $this->stats['skipped_no_user']],
            ['Errors', $this->stats['errors']],
        ]);

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Errors encountered:');
            $this->table(
                ['Old ID', 'User ID', 'Amount', 'Error'],
                array_slice($this->errors, 0, 10)
            );

            if (count($this->errors) > 10) {
                $this->warn('... and ' . (count($this->errors) - 10) . ' more errors (check logs)');
            }
        }

        $this->newLine();
        if ($isDryRun) {
            $this->warn('This was a DRY RUN - no data was actually saved.');
            $this->info('Run without --dry-run to perform the actual migration.');
        } else {
            $this->info('Migration completed successfully!');
        }
    }

    private function handleError(Exception $e): void
    {
        $this->newLine();
        $this->error('╔════════════════════════════════════════╗');
        $this->error('║           Migration Failed!            ║');
        $this->error('╚════════════════════════════════════════╝');
        $this->newLine();
        $this->error('Error: ' . $e->getMessage());
        $this->error('File: ' . $e->getFile() . ':' . $e->getLine());

        Log::error('Package deposits migration failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
