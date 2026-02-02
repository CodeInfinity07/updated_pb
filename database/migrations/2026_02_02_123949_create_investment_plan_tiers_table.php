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
        Schema::create('investment_plan_tiers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('investment_plan_id')->index('investment_plan_id');
            $table->integer('tier_level')->default(1);
            $table->string('name')->nullable();
            $table->decimal('minimum_amount', 20)->nullable()->default(0);
            $table->decimal('maximum_amount', 20)->nullable()->default(0);
            $table->decimal('interest_rate')->nullable()->default(0);
            $table->json('features')->nullable();
            $table->integer('sort_order')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_plan_tiers');
    }
};
