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
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('symbol', 10)->unique();
            $table->string('icon')->nullable();
            $table->string('network')->nullable();
            $table->string('contract_address')->nullable();
            $table->tinyInteger('decimal_places')->default(8);
            $table->decimal('min_withdrawal', 20, 8)->default(0);
            $table->decimal('max_withdrawal', 20, 8)->nullable();
            $table->decimal('withdrawal_fee', 20, 8)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};
