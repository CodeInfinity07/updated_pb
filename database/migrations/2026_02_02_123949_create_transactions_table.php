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
        Schema::create('transactions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('user_id');
            $table->string('transaction_id')->index();
            $table->string('type', 50)->index();
            $table->decimal('amount', 18, 4)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending')->index();
            $table->string('payment_method')->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('crypto_txid')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable()->index('idx_transactions_processed_by');
            $table->timestamp('created_at')->nullable()->index();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['transaction_id']);
            $table->index(['user_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
