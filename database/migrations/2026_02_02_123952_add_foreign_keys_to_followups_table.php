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
        Schema::table('followups', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lead_id'])->references(['id'])->on('leads')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followups', function (Blueprint $table) {
            $table->dropForeign('followups_created_by_foreign');
            $table->dropForeign('followups_lead_id_foreign');
        });
    }
};
