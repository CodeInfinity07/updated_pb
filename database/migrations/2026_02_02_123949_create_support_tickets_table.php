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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_number')->unique('ticket_number');
            $table->unsignedBigInteger('user_id')->index('user_id');
            $table->unsignedBigInteger('assigned_to')->nullable()->index('assigned_to');
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'pending_user', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('category')->nullable();
            $table->longText('attachments')->nullable();
            $table->timestamp('last_reply_at')->nullable();
            $table->unsignedBigInteger('last_reply_by')->nullable()->index('last_reply_by');
            $table->timestamps();

            $table->index(['status', 'priority'], 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
