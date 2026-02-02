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
        Schema::create('forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('submit_button_text')->default('Submit Application');
            $table->string('success_message')->default('Thank you! We will contact you soon.');
            $table->json('standard_fields')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('submissions_count')->default(0);
            $table->string('slug')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['is_active', 'created_at']);
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
