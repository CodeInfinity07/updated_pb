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
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->index('admin_id');
            $table->string('type')->index('type');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('recipients')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->string('target_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable()->index('created_at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
