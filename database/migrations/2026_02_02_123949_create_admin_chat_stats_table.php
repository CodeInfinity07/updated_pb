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
        Schema::create('admin_chat_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->unique();
            $table->integer('total_chats_handled')->default(0);
            $table->integer('chats_closed_today')->default(0);
            $table->integer('average_response_time')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->date('stats_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_chat_stats');
    }
};
