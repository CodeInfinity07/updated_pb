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
        Schema::create('leaderboard_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('leaderboard_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('position');
            $table->integer('referral_count');
            $table->decimal('prize_amount', 10)->nullable();
            $table->boolean('prize_awarded')->default(false);
            $table->timestamp('prize_awarded_at')->nullable();
            $table->boolean('prize_approved')->default(false);
            $table->timestamp('prize_approved_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('prize_approved_by')->nullable()->index('leaderboard_positions_prize_approved_by_foreign');
            $table->boolean('prize_claimed')->default(false);
            $table->timestamp('prize_claimed_at')->nullable();

            $table->unique(['leaderboard_id', 'user_id'], 'leaderboard_id');
            $table->index(['leaderboard_id', 'position'], 'leaderboard_id_2');
            $table->index(['user_id', 'prize_approved', 'prize_claimed']);
            $table->index(['user_id', 'prize_awarded'], 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_positions');
    }
};
