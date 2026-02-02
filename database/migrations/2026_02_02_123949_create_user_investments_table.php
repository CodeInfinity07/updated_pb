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
        Schema::create('user_investments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('old_package_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('investment_plan_id')->index('user_investments_investment_plan_id_foreign');
            $table->string('type', 20)->default('investment');
            $table->decimal('amount', 15);
            $table->decimal('roi_percentage', 5)->nullable();
            $table->integer('duration_days');
            $table->decimal('total_return', 15)->default(0);
            $table->decimal('daily_return', 15)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('last_payout_date')->nullable()->index();
            $table->timestamps();
            $table->decimal('paid_return', 20)->nullable()->default(0);
            $table->decimal('earnings_accumulated', 15)->nullable()->default(0);
            $table->decimal('commission_earned', 15)->nullable()->default(0);
            $table->integer('expiry_multiplier')->nullable()->default(3);
            $table->boolean('bot_fee_applied')->nullable()->default(false);
            $table->string('status_reason')->nullable();

            $table->index(['status', 'end_date']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_investments');
    }
};
