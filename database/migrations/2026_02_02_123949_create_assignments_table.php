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
        Schema::create('assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lead_id')->index();
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('assigned_to');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_by', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
