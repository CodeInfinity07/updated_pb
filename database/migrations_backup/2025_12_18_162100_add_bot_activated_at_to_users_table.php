<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('bot_activated_at')->nullable()->after('email_verified_at');
        });

        DB::statement("
            UPDATE users 
            SET bot_activated_at = (
                SELECT MIN(created_at) 
                FROM user_investments 
                WHERE user_investments.user_id = users.id
            )
            WHERE bot_activated_at IS NULL 
            AND id IN (SELECT DISTINCT user_id FROM user_investments)
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bot_activated_at');
        });
    }
};
