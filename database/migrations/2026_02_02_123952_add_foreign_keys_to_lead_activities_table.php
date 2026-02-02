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
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->foreign(['lead_id'])->references(['id'])->on('leads')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropForeign('lead_activities_lead_id_foreign');
            $table->dropForeign('lead_activities_user_id_foreign');
        });
    }
};
