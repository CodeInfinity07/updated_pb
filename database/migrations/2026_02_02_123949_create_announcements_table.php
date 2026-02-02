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
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('content');
            $table->string('image_path')->nullable();
            $table->string('type')->default('info');
            $table->string('announcement_type')->default('text');
            $table->string('target_audience')->default('all');
            $table->longText('target_user_ids')->nullable();
            $table->integer('priority')->default(1);
            $table->string('status')->default('active');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('show_once')->default(true);
            $table->boolean('is_dismissible')->default(true);
            $table->string('button_text')->default('Got it');
            $table->string('button_link')->nullable();
            $table->unsignedBigInteger('created_by')->index('created_by');
            $table->timestamps();

            $table->index(['status', 'scheduled_at'], 'status');
            $table->index(['target_audience', 'priority'], 'target_audience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
