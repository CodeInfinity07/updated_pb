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
        Schema::create('followups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lead_id');
            $table->date('followup_date');
            $table->enum('type', ['call', 'email', 'meeting', 'whatsapp', 'other'])->default('call');
            $table->text('notes');
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['followup_date', 'completed']);
            $table->index(['lead_id', 'followup_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followups');
    }
};
