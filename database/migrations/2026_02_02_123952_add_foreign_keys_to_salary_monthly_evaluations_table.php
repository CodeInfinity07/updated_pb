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
        Schema::table('salary_monthly_evaluations', function (Blueprint $table) {
            $table->foreign(['salary_application_id'])->references(['id'])->on('salary_applications')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['salary_stage_id'])->references(['id'])->on('salary_stages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_monthly_evaluations', function (Blueprint $table) {
            $table->dropForeign('salary_monthly_evaluations_salary_application_id_foreign');
            $table->dropForeign('salary_monthly_evaluations_salary_stage_id_foreign');
            $table->dropForeign('salary_monthly_evaluations_user_id_foreign');
        });
    }
};
