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
        Schema::create('investment_plan_profit_sharings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('investment_plan_id')->index('investment_plan_id');
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->decimal('percentage')->nullable()->default(0);
            $table->string('frequency', 50)->nullable()->default('daily');
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_plan_profit_sharings');
    }
};
