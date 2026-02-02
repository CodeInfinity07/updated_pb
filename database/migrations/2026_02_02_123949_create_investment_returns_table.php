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
        Schema::create('investment_returns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_id');
            $table->unsignedBigInteger('user_investment_id')->index('user_investment_id');
            $table->decimal('amount', 20)->default(0);
            $table->string('type', 50)->nullable()->default('daily_return');
            $table->string('status', 50)->nullable()->default('pending')->index('status');
            $table->date('due_date')->nullable()->index('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_returns');
    }
};
