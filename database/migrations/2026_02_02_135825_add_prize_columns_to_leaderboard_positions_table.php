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
        Schema::table('leaderboard_positions', function (Blueprint $table) {
            if (!Schema::hasColumn('leaderboard_positions', 'prize_approved')) {
                $table->boolean('prize_approved')->default(false)->after('prize_awarded_at');
            }
            if (!Schema::hasColumn('leaderboard_positions', 'prize_approved_at')) {
                $table->timestamp('prize_approved_at')->nullable()->after('prize_approved');
            }
            if (!Schema::hasColumn('leaderboard_positions', 'prize_approved_by')) {
                $table->unsignedBigInteger('prize_approved_by')->nullable()->after('prize_approved_at');
            }
            if (!Schema::hasColumn('leaderboard_positions', 'prize_claimed')) {
                $table->boolean('prize_claimed')->default(false)->after('prize_approved_by');
            }
            if (!Schema::hasColumn('leaderboard_positions', 'prize_claimed_at')) {
                $table->timestamp('prize_claimed_at')->nullable()->after('prize_claimed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaderboard_positions', function (Blueprint $table) {
            $table->dropColumn(['prize_approved', 'prize_approved_at', 'prize_approved_by', 'prize_claimed', 'prize_claimed_at']);
        });
    }
};
