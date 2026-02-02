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
            $table->foreign(['prize_approved_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaderboard_positions', function (Blueprint $table) {
            $table->dropForeign('leaderboard_positions_prize_approved_by_foreign');
        });
    }
};
