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
        Schema::create('mass_email_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_id');
            $table->string('name')->index('name');
            $table->string('subject');
            $table->longText('content');
            $table->longText('recipient_groups')->nullable();
            $table->longText('specific_users')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_failed')->default(0);
            $table->enum('status', ['pending', 'scheduled', 'sending', 'completed', 'cancelled', 'failed'])->default('pending')->index('status_2');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'created_at'], 'created_by');
            $table->index(['scheduled_at', 'status'], 'scheduled_at');
            $table->index(['status', 'created_at'], 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mass_email_campaigns');
    }
};
