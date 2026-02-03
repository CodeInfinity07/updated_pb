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
        DB::statement("ALTER TABLE user_investments MODIFY COLUMN type ENUM('investment', 'bot_fee', 'completed') NOT NULL DEFAULT 'investment'");
        
        DB::statement("UPDATE user_investments SET type = 'investment' WHERE type = 'completed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE user_investments SET type = 'completed' WHERE type = 'investment'");
        
        DB::statement("ALTER TABLE user_investments MODIFY COLUMN type ENUM('completed', 'bot_fee') NOT NULL DEFAULT 'completed'");
    }
};
