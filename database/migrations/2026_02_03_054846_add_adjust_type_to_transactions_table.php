<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit','withdrawal','commission','roi','investment','bonus','fee','adjust','profit_share','salary','rank_reward') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit','withdrawal','commission','roi','investment','bonus','fee','profit_share','salary','rank_reward') NOT NULL");
    }
};
