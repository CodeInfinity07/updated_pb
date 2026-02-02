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
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile');
            $table->string('whatsapp')->nullable();
            $table->string('country')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', ['hot', 'warm', 'cold', 'converted'])->default('cold');
            $table->enum('interest', ['Low', 'Medium', 'High'])->nullable();
            $table->text('notes');
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['source', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
