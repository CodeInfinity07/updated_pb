<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leaderboards', function (Blueprint $table) {
            if (!Schema::hasColumn('leaderboards', 'target_tiers')) {
                $table->json('target_tiers')->nullable()->after('target_prize_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaderboards', function (Blueprint $table) {
            if (Schema::hasColumn('leaderboards', 'target_tiers')) {
                $table->dropColumn('target_tiers');
            }
        });
    }
};
