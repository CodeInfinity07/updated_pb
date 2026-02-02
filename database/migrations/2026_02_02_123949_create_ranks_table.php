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
        Schema::create('ranks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->decimal('min_self_deposit', 15)->default(0);
            $table->integer('min_direct_members')->default(0);
            $table->decimal('min_direct_member_investment', 15)->default(100);
            $table->integer('min_team_members')->default(0);
            $table->decimal('min_team_member_investment', 15)->default(100);
            $table->decimal('reward_amount', 15)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
