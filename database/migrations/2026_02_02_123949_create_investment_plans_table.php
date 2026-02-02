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
        Schema::create('investment_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('minimum_amount', 15);
            $table->decimal('maximum_amount', 15)->nullable();
            $table->decimal('roi_percentage', 5);
            $table->integer('duration_days');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->string('frequency')->nullable()->default('daily');
            $table->boolean('principal_return')->nullable()->default(false);
            $table->double('principal_hold')->nullable()->default(0);
            $table->unsignedBigInteger('parent_plan_id')->nullable();
            $table->double('referral_percentage')->nullable()->default(0);
            $table->json('details')->nullable();
            $table->integer('diff_in_seconds')->nullable()->default(86400);
            $table->integer('old_package_id')->nullable();
            $table->decimal('interest_rate')->nullable()->default(0);
            $table->string('interest_type', 50)->nullable()->default('daily');
            $table->string('return_type', 50)->nullable()->default('daily');
            $table->boolean('capital_return')->nullable()->default(true);
            $table->string('status', 50)->nullable()->default('active');
            $table->integer('total_investors')->nullable()->default(0);
            $table->decimal('total_invested', 20)->nullable()->default(0);
            $table->json('features')->nullable();
            $table->string('badge', 100)->nullable();
            $table->string('color_scheme', 100)->nullable();
            $table->boolean('is_tiered')->nullable()->default(false);
            $table->integer('max_tier_level')->nullable()->default(1);
            $table->decimal('base_interest_rate')->nullable()->default(0);
            $table->json('tier_settings')->nullable();
            $table->boolean('profit_sharing_enabled')->nullable()->default(false);
            $table->string('roi_type', 20)->nullable()->default('fixed');
            $table->decimal('min_interest_rate', 8, 4)->nullable();
            $table->decimal('max_interest_rate', 8, 4)->nullable();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_plans');
    }
};
