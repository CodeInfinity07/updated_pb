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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 45)->index();
            $table->string('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('is_successful')->default(true)->index();
            $table->string('failure_reason')->nullable();
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();

            $table->index(['user_id', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
