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
        Schema::create('tracking_pixels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('platform')->unique();
            $table->string('pixel_id')->nullable();
            $table->text('pixel_code')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_pixels');
    }
};
