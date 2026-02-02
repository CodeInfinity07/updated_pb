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
        Schema::create('commission_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('level')->unique('level');
            $table->string('name');
            $table->decimal('min_investment', 15)->default(0);
            $table->integer('min_direct_referrals')->default(0);
            $table->integer('min_indirect_referrals')->default(0);
            $table->decimal('commission_level_1', 5)->default(0);
            $table->decimal('commission_level_2', 5)->default(0);
            $table->decimal('commission_level_3', 5)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('color', 7)->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'is_active');
            $table->index(['level', 'is_active'], 'level_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_settings');
    }
};
