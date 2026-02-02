<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_type_check");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('deposit', 'withdrawal', 'commission', 'roi', 'investment', 'bonus', 'adjust'))");
        } else {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'commission', 'roi', 'investment', 'bonus', 'adjust') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_type_check");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('deposit', 'withdrawal', 'commission', 'roi', 'investment', 'bonus'))");
        } else {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'commission', 'roi', 'investment', 'bonus') NOT NULL");
        }
    }
};
