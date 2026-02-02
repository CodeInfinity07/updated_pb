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
        Schema::create('crypto_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('currency', 10)->index();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('balance', 20, 8)->default(0);
            $table->decimal('usd_rate', 15)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'currency']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_wallets');
    }
};
