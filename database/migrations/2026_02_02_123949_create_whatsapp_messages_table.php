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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id')->nullable();
            $table->string('user_phone', 20)->nullable()->index();
            $table->text('message_text')->nullable();
            $table->enum('message_type', ['incoming', 'outgoing'])->nullable()->index();
            $table->integer('timestamp')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
