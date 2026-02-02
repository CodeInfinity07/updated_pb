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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->json('form_data');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->enum('status', ['new', 'processed', 'converted'])->default('new');
            $table->timestamps();

            $table->index(['form_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
