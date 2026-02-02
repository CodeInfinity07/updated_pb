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
        Schema::table('user_investments', function (Blueprint $table) {
            $table->foreign(['investment_plan_id'])->references(['id'])->on('investment_plans')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_investments', function (Blueprint $table) {
            $table->dropForeign('user_investments_investment_plan_id_foreign');
            $table->dropForeign('user_investments_user_id_foreign');
        });
    }
};
