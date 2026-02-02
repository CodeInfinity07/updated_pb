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
        Schema::create('salary_payouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('salary_stage_id')->index('salary_payouts_salary_stage_id_foreign');
            $table->decimal('amount', 15);
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('admin_id')->nullable()->index('salary_payouts_admin_id_foreign');
            $table->timestamp('payout_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'salary_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payouts');
    }
};
