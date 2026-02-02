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
        Schema::create('user_referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('level')->default(1)->index();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->decimal('commission_earned', 15)->default(0);
            $table->timestamps();

            $table->index(['sponsor_id', 'status']);
            $table->unique(['sponsor_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_referrals');
    }
};
