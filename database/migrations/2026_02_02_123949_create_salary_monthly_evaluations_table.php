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
        Schema::create('salary_monthly_evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('salary_application_id');
            $table->unsignedBigInteger('salary_stage_id')->index('salary_monthly_evaluations_salary_stage_id_foreign');
            $table->integer('month_number');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('target_team');
            $table->integer('starting_team_count')->default(0);
            $table->integer('achieved_team_new');
            $table->integer('target_direct_new');
            $table->integer('achieved_direct_new');
            $table->integer('starting_direct_count');
            $table->boolean('passed')->default(false);
            $table->decimal('salary_amount', 15)->nullable();
            $table->boolean('salary_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['salary_application_id', 'month_number'], 'sal_eval_app_month_idx');
            $table->unique(['salary_application_id', 'month_number'], 'sal_eval_app_month_unique');
            $table->index(['user_id', 'period_start'], 'sal_eval_user_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_monthly_evaluations');
    }
};
