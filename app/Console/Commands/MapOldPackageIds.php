<?php

namespace App\Console\Commands;

use App\Models\UserInvestment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MapOldPackageIds extends Command
{
    protected $signature = 'migrate:map-old-package-ids 
                           {--connection=old_db : Old database connection name} 
                           {--dry-run : Preview mapping without saving data}
                           {--batch-size=500 : Number of records to process per batch}
                           {--force : Skip confirmation prompts}';

    protected $description = 'Map old package IDs to new user_investments using user_id and amount matching';

    private array $stats = [
        'total_investments' => 0,
        'matched' => 0,
        'already_mapped' => 0,
        'no_match' => 0,
        'multiple_matches' => 0,
        'errors' => 0,
    ];

    private array $unmatchedInvestments = [];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force');

        $this->showHeader($isDryRun);

        try {
            $this->testConnections();
            
            $totalInvestments = UserInvestment::count();
            
            if ($totalInvestments === 0) {
                $this->warn('No investments found to map!');
                return Command::SUCCESS;
            }

            $this->stats['total_investments'] = $totalInvestments;
            $this->info("Found {$totalInvestments} investments to process");

            $this->showSampleData();
            
            if (!$force && !$this->confirm('Proceed with mapping?', true)) {
                $this->info('Mapping cancelled.');
                return Command::SUCCESS;
            }

            $this->processMapping($batchSize, $isDryRun);
            $this->showResults($isDryRun);

        } catch (Exception $e) {
            $this->handleError($e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function showHeader(bool $isDryRun): void
    {
        $this->info('Old Package ID Mapping Tool');
        $this->info('===========================');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No data will be saved!');
            $this->newLine();
        }
    }

    private function testConnections(): void
    {
        $this->info('Testing database connections...');
        
        $oldDbConnection = $this->option('connection');
        DB::connection($oldDbConnection)->select('SELECT 1');
        $this->info("Old database ({$oldDbConnection}): Connected");
        
        DB::connection()->select('SELECT 1');
        $this->info('New database: Connected');
        
        $oldPackagesCount = DB::connection($oldDbConnection)
            ->table('package_deposits')
            ->count();
        $this->info("Old package_deposits table has {$oldPackagesCount} records");
    }

    private function showSampleData(): void
    {
        $this->info('Sample data from old package_deposits table:');
        
        $samples = DB::connection($this->option('connection'))
            ->table('package_deposits')
            ->limit(5)
            ->get();

        if ($samples->isEmpty()) {
            $this->warn('No packages found in old database!');
            return;
        }

        $tableData = [];
        foreach ($samples as $sample) {
            $datetime = $sample->datetime ?? null;
            $dateOnly = $datetime ? Carbon::parse($datetime)->format('Y-m-d') : 'N/A';
            $tableData[] = [
                $sample->id ?? 'N/A',
                $sample->user_id ?? 'N/A',
                $sample->amount ?? 'N/A',
                $dateOnly,
            ];
        }

        $this->table(['ID', 'User ID', 'Amount', 'Date'], $tableData);
        $this->newLine();

        $this->info('Sample data from new user_investments table:');
        
        $newSamples = UserInvestment::with('user')
            ->limit(5)
            ->get();

        $newTableData = [];
        foreach ($newSamples as $investment) {
            $startDate = $investment->start_date ? Carbon::parse($investment->start_date)->format('Y-m-d') : 'N/A';
            $newTableData[] = [
                $investment->id,
                $investment->user_id,
                $investment->amount,
                $investment->old_package_id ?? 'NOT SET',
                $startDate,
            ];
        }

        $this->table(['ID', 'User ID', 'Amount', 'Old Package ID', 'Start Date'], $newTableData);
        $this->newLine();
    }

    private function processMapping(int $batchSize, bool $isDryRun): void
    {
        $this->info("Processing investments in batches of {$batchSize}...");
        
        $progressBar = $this->output->createProgressBar($this->stats['total_investments']);
        $progressBar->start();

        $oldDbConnection = $this->option('connection');

        UserInvestment::chunk($batchSize, function ($investments) use ($progressBar, $isDryRun, $oldDbConnection) {
            foreach ($investments as $investment) {
                try {
                    $this->mapInvestment($investment, $oldDbConnection, $isDryRun);
                } catch (Exception $e) {
                    $this->stats['errors']++;
                    Log::warning('Package mapping error', [
                        'investment_id' => $investment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);
    }

    private function mapInvestment(UserInvestment $investment, string $oldDbConnection, bool $isDryRun): void
    {
        if (!empty($investment->old_package_id)) {
            $this->stats['already_mapped']++;
            return;
        }

        $oldPackages = DB::connection($oldDbConnection)
            ->table('package_deposits')
            ->where('user_id', $investment->user_id)
            ->whereRaw('ROUND(amount, 2) = ?', [round($investment->amount, 2)])
            ->get();

        if ($oldPackages->isEmpty()) {
            $oldPackages = DB::connection($oldDbConnection)
                ->table('package_deposits')
                ->where('user_id', $investment->user_id)
                ->whereRaw('ABS(amount - ?) < 0.01', [$investment->amount])
                ->get();
        }

        if ($oldPackages->isEmpty()) {
            $this->stats['no_match']++;
            $this->unmatchedInvestments[] = [
                'investment_id' => $investment->id,
                'user_id' => $investment->user_id,
                'amount' => $investment->amount,
                'start_date' => $investment->start_date,
            ];
            return;
        }

        if ($oldPackages->count() > 1) {
            $matchedPackage = $this->findBestMatch($investment, $oldPackages);
            
            if (!$matchedPackage) {
                $this->stats['multiple_matches']++;
                return;
            }
        } else {
            $matchedPackage = $oldPackages->first();
        }

        if (!$isDryRun) {
            $investment->old_package_id = $matchedPackage->id;
            $investment->save();
        }

        $this->stats['matched']++;
    }

    private function findBestMatch(UserInvestment $investment, $oldPackages)
    {
        if (!$investment->start_date) {
            return $oldPackages->first();
        }

        $investmentDate = Carbon::parse($investment->start_date)->startOfDay();
        $bestMatch = null;
        $smallestDiff = PHP_INT_MAX;

        foreach ($oldPackages as $package) {
            if (empty($package->datetime)) {
                continue;
            }

            try {
                $packageDate = Carbon::parse($package->datetime)->startOfDay();
                $diff = abs($investmentDate->diffInDays($packageDate));
                
                if ($diff < $smallestDiff) {
                    $smallestDiff = $diff;
                    $bestMatch = $package;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        if ($smallestDiff <= 1) {
            return $bestMatch;
        }

        return $oldPackages->first();
    }

    private function handleError(Exception $e): void
    {
        $this->error('CRITICAL ERROR: Mapping failed!');
        $this->error("Error: {$e->getMessage()}");
        $this->error("File: {$e->getFile()}:{$e->getLine()}");
        
        Log::error('Critical package mapping error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    private function showResults(bool $isDryRun): void
    {
        $this->newLine();
        $this->info('MAPPING SUMMARY');
        $this->info('===============');
        
        $this->table(['Metric', 'Count'], [
            ['Total Investments', number_format($this->stats['total_investments'])],
            ['Successfully Matched', number_format($this->stats['matched'])],
            ['Already Mapped', number_format($this->stats['already_mapped'])],
            ['No Match Found', number_format($this->stats['no_match'])],
            ['Multiple Matches (Best Selected)', number_format($this->stats['multiple_matches'])],
            ['Errors', number_format($this->stats['errors'])],
        ]);

        if ($isDryRun) {
            $this->warn('This was a DRY RUN - no data was modified.');
            $this->info('Run without --dry-run to apply changes.');
        }

        if (!empty($this->unmatchedInvestments) && count($this->unmatchedInvestments) <= 20) {
            $this->newLine();
            $this->warn('Unmatched Investments:');
            $this->table(
                ['Investment ID', 'User ID', 'Amount', 'Start Date'],
                array_map(function ($inv) {
                    return [
                        $inv['investment_id'],
                        $inv['user_id'],
                        $inv['amount'],
                        $inv['start_date'],
                    ];
                }, $this->unmatchedInvestments)
            );
        } elseif (!empty($this->unmatchedInvestments)) {
            $this->warn('Too many unmatched investments to display. Check logs for details.');
            Log::info('Unmatched investments', $this->unmatchedInvestments);
        }
    }
}
