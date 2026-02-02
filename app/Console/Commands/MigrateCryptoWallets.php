<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\CryptoWallet;
use App\Models\Cryptocurrency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateCryptoWallets extends Command
{
    protected $signature = 'migrate:crypto-wallets {--connection=old_db} {--dry-run}';
    protected $description = 'Migrate user balances from old database to crypto wallets';

    private $userMapping = [];
    private $paymentMethodMapping = [];
    private $errors = [];
    private $stats = [
        'total' => 0,
        'migrated' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('╔════════════════════════════════════════╗');
            $this->warn('║      DRY RUN MODE - NO DATA SAVED     ║');
            $this->warn('╚════════════════════════════════════════╝');
            $this->newLine();
        }

        $this->info('Starting crypto wallet migration...');

        try {
            // Test connection
            $this->info('Testing database connection...');
            DB::connection($this->option('connection'))
                ->select('SELECT COUNT(*) as count FROM user_balances LIMIT 1');
            $this->info('✓ Connection successful!');
            $this->newLine();

            // Build user mapping (old user_id -> new user_id)
            $this->buildUserMapping();

            // Setup payment method mapping
            $this->setupPaymentMethodMapping($dryRun);

            // Get old user balances
            $oldBalances = DB::connection($this->option('connection'))
                ->table('user_balances')
                ->orderBy('id')
                ->get();

            $this->stats['total'] = $oldBalances->count();
            $this->info("Found {$oldBalances->count()} wallet records to migrate");
            $this->newLine();

            if ($oldBalances->isEmpty()) {
                $this->warn('No wallet records found to migrate!');
                return 0;
            }

            // Show sample data
            $this->showSampleData($oldBalances->first());

            if (!$dryRun && !$this->confirm('Do you want to proceed with the migration?', true)) {
                $this->info('Migration cancelled by user.');
                return 0;
            }

            $bar = $this->output->createProgressBar($oldBalances->count());
            $bar->start();

            DB::beginTransaction();

            foreach ($oldBalances as $oldBalance) {
                try {
                    $this->migrateWallet($oldBalance, $dryRun);
                    $this->stats['migrated']++;
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $this->errors[] = [
                        'old_id' => $oldBalance->id,
                        'old_user_id' => $oldBalance->user_id,
                        'payment_method_id' => $oldBalance->payment_method_id,
                        'error' => $e->getMessage()
                    ];
                    $this->newLine();
                    $this->error("Error migrating wallet {$oldBalance->id}: " . $e->getMessage());
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($dryRun) {
                DB::rollBack();
                $this->newLine();
                $this->warn('╔════════════════════════════════════════╗');
                $this->warn('║   DRY RUN - All changes rolled back    ║');
                $this->warn('╚════════════════════════════════════════╝');
            } else {
                DB::commit();
                $this->newLine();
                $this->info('╔════════════════════════════════════════╗');
                $this->info('║     Migration completed successfully   ║');
                $this->info('╚════════════════════════════════════════╝');
            }

            $this->displayResults($dryRun);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('╔════════════════════════════════════════╗');
            $this->error('║         Migration failed!              ║');
            $this->error('╚════════════════════════════════════════╝');
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }

        return 0;
    }

    private function buildUserMapping()
    {
        $this->info('Building user mapping...');

        // Get all user IDs from current database
        // Assumes user IDs are the same between old and new database
        $users = User::pluck('id')->toArray();
        
        // Create direct 1:1 mapping (old_id = new_id)
        foreach ($users as $userId) {
            $this->userMapping[$userId] = $userId;
        }

        $this->info("Found " . count($users) . " users in database");
        $this->info("Using direct ID mapping (old user_id = new user_id)");
        $this->newLine();
    }

    private function setupPaymentMethodMapping($dryRun = false)
    {
        $this->info('Setting up payment method mapping...');
        $this->newLine();

        // Fetch USDT_BEP20 from cryptocurrencies table
        $crypto = Cryptocurrency::where('symbol', 'USDT_BEP20')->first();
        
        if (!$crypto) {
            $this->error('USDT_BEP20 cryptocurrency not found in database!');
            $this->error('Please add it to the cryptocurrencies table first.');
            exit(1);
        }

        $this->info("✓ Found cryptocurrency: {$crypto->name} ({$crypto->symbol})");
        $this->line("  Network: {$crypto->network}");
        $this->line("  Contract: {$crypto->contract_address}");
        $this->newLine();

        // Map all old payment method IDs to USDT_BEP20
        $this->table(
            ['Payment Method ID', 'Currency', 'Name'],
            [
                ['19', $crypto->symbol, $crypto->name],
                ['23', $crypto->symbol, $crypto->name],
            ]
        );

        $this->newLine();
        $this->info('All balances will be migrated as:');
        $this->line("  - Currency: {$crypto->symbol}");
        $this->line("  - Name: {$crypto->name}");
        $this->newLine();

        if (!$dryRun && !$this->confirm('Is this mapping correct?', true)) {
            $this->error('Migration cancelled.');
            exit(1);
        }

        // Map payment_method_id to cryptocurrency from database
        $this->paymentMethodMapping = [
            19 => ['currency' => $crypto->symbol, 'name' => $crypto->name, 'crypto_id' => $crypto->id],
            23 => ['currency' => $crypto->symbol, 'name' => $crypto->name, 'crypto_id' => $crypto->id],
        ];
    }

    private function showSampleData($sample)
    {
        $this->info('Sample wallet data:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $sample->id],
                ['User ID', $sample->user_id],
                ['Payment Method ID', $sample->payment_method_id],
                ['Total', $sample->total],
                ['Balance', $sample->balance],
                ['Faucet', $sample->faucet],
            ]
        );
        $this->newLine();
    }

    private function migrateWallet($oldBalance, $dryRun = false)
    {
        // Check if user exists in mapping (skip if user doesn't exist in current database)
        if (!isset($this->userMapping[$oldBalance->user_id])) {
            $this->stats['skipped']++;
            return; // User doesn't exist in current database, skip silently
        }

        $newUserId = $this->userMapping[$oldBalance->user_id];

        // Check if payment method is mapped
        if (!isset($this->paymentMethodMapping[$oldBalance->payment_method_id])) {
            $this->stats['skipped']++;
            throw new \Exception("Payment method ID {$oldBalance->payment_method_id} not mapped.");
        }

        $currencyData = $this->paymentMethodMapping[$oldBalance->payment_method_id];
        $currency = $currencyData['currency'];
        $currencyName = $currencyData['name'];
        $cryptoId = $currencyData['crypto_id'];

        // Check if wallet already exists by ID or user+currency
        $existingWalletById = CryptoWallet::find($oldBalance->id);
        $existingWalletByUser = CryptoWallet::where('user_id', $newUserId)
            ->where('currency', $currency)
            ->first();

        if ($existingWalletById || $existingWalletByUser) {
            $this->stats['skipped']++;
            return;
        }

        // Get user's created_at for consistency
        $user = User::find($newUserId);
        $createdAt = $user ? $user->created_at : now();

        if ($dryRun) {
            $this->line("\n[DRY RUN] Would create wallet ID {$oldBalance->id}:");
            $this->line("  User ID: {$newUserId} (old: {$oldBalance->user_id})");
            $this->line("  Currency: {$currency}");
            $this->line("  Balance: " . $this->sanitizeBalance($oldBalance->balance));
            $this->line("  Total: {$oldBalance->total}");
            $this->line("  Faucet: {$oldBalance->faucet}");
            return;
        }

        // Insert wallet with exact ID from old database using raw insert
        DB::table('crypto_wallets')->insert([
            'id' => $oldBalance->id,
            'user_id' => $newUserId,
            'currency' => $currency,
            'name' => $currencyName,
            'address' => null,
            'balance' => $this->sanitizeBalance($oldBalance->balance),
            'is_active' => true,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function sanitizeBalance($balance)
    {
        // Convert to float, preserve negative balances
        return (float) $balance;
    }

    private function displayResults($dryRun = false)
    {
        $this->newLine(2);
        $this->info('═══════════════════════════════════════════');
        $this->info('           MIGRATION SUMMARY');
        $this->info('═══════════════════════════════════════════');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Wallet Records Found', $this->stats['total']],
                ['Successfully Migrated', $this->stats['migrated']],
                ['Skipped (Zero/Negative Balance or Exists)', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('ERRORS ENCOUNTERED:');
            $this->table(
                ['Old ID', 'Old User ID', 'Payment Method', 'Error'],
                array_map(function ($error) {
                    return [
                        $error['old_id'],
                        $error['old_user_id'],
                        $error['payment_method_id'],
                        \Illuminate\Support\Str::limit($error['error'], 50)
                    ];
                }, $this->errors)
            );
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. No data was migrated.');
            $this->info('Run without --dry-run to perform actual migration:');
            $this->line('php artisan migrate:crypto-wallets --connection=old_db');
        } else {
            $this->newLine();
            $this->info('✓ Migration completed successfully!');
            
            // Show summary by currency
            $this->newLine();
            $this->info('Wallets created by currency:');
            $summary = CryptoWallet::select('currency', DB::raw('COUNT(*) as count'), DB::raw('SUM(balance) as total_balance'))
                ->groupBy('currency')
                ->get();
            
            $this->table(
                ['Currency', 'Wallet Count', 'Total Balance'],
                $summary->map(function($item) {
                    return [
                        $item->currency,
                        $item->count,
                        number_format($item->total_balance, 8)
                    ];
                })->toArray()
            );
        }
    }
}