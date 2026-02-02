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
        Schema::create('user_ranks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rank_id')->index('user_ranks_rank_id_foreign');
            $table->timestamp('achieved_at')->useCurrent();
            $table->boolean('reward_paid')->default(false);
            $table->timestamp('reward_paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'achieved_at']);
            $table->unique(['user_id', 'rank_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ranks');
    }
};
