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
        Schema::create('referral_tree', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('level');
            $table->timestamps();

            $table->index(['sponsor_id', 'level']);
            $table->unique(['sponsor_id', 'user_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_tree');
    }
};
