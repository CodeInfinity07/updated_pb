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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed'])->default('inactive');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('show_to_users')->default(true);
            $table->integer('max_positions')->default(10);
            $table->string('referral_type', 50)->default('all');
            $table->integer('max_referral_level')->nullable();
            $table->decimal('min_investment_amount', 15)->nullable();
            $table->enum('type', ['competitive', 'target'])->default('competitive');
            $table->integer('target_referrals')->nullable();
            $table->decimal('target_prize_amount', 10)->nullable();
            $table->json('target_tiers')->nullable();
            $table->integer('max_winners')->nullable();
            $table->longText('prize_structure')->nullable();
            $table->boolean('prizes_distributed')->default(false);
            $table->unsignedBigInteger('created_by')->index('created_by');
            $table->timestamp('prizes_distributed_at')->nullable();
            $table->unsignedBigInteger('prizes_distributed_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_date', 'end_date'], 'status');
            $table->index(['status', 'show_to_users'], 'status_2');
            $table->index(['type', 'status'], 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
