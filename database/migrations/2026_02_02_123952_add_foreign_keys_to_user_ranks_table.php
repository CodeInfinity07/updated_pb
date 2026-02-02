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
        Schema::table('user_ranks', function (Blueprint $table) {
            $table->foreign(['rank_id'])->references(['id'])->on('ranks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ranks', function (Blueprint $table) {
            $table->dropForeign('user_ranks_rank_id_foreign');
            $table->dropForeign('user_ranks_user_id_foreign');
        });
    }
};
