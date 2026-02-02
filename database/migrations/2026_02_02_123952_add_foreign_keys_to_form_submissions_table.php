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
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreign(['form_id'])->references(['id'])->on('forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lead_id'])->references(['id'])->on('leads')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropForeign('form_submissions_form_id_foreign');
            $table->dropForeign('form_submissions_lead_id_foreign');
        });
    }
};
