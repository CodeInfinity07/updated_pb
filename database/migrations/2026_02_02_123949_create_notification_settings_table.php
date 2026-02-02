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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('class_name')->unique();
            $table->string('description')->nullable();
            $table->json('channels');
            $table->json('settings');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0)->index();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamps();

            $table->index(['class_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
