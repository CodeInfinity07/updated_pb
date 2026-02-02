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
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign(['assigned_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['assigned_to'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lead_id'])->references(['id'])->on('leads')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign('assignments_assigned_by_foreign');
            $table->dropForeign('assignments_assigned_to_foreign');
            $table->dropForeign('assignments_lead_id_foreign');
        });
    }
};
