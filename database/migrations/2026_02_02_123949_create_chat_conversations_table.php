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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('chat_conversations_user_id_foreign');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('admin_last_read_at')->nullable();
            $table->timestamp('user_last_read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
