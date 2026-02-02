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
        Schema::create('salary_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('salary_stage_id')->index('salary_applications_salary_stage_id_foreign');
            $table->timestamp('applied_at');
            $table->integer('baseline_team_count');
            $table->integer('baseline_direct_count');
            $table->decimal('baseline_self_deposit', 15);
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->integer('current_target_team');
            $table->integer('current_target_direct_new')->default(3);
            $table->integer('months_completed')->default(0);
            $table->enum('status', ['active', 'failed', 'graduated'])->default('active');
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('graduated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'current_period_end']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_applications');
    }
};
