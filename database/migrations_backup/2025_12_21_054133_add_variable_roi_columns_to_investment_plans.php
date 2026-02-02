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
        Schema::table('investment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_plans', 'roi_type')) {
                $table->string('roi_type', 20)->default('fixed')->after('interest_rate');
            }
            if (!Schema::hasColumn('investment_plans', 'min_interest_rate')) {
                $table->decimal('min_interest_rate', 8, 4)->nullable()->after('roi_type');
            }
            if (!Schema::hasColumn('investment_plans', 'max_interest_rate')) {
                $table->decimal('max_interest_rate', 8, 4)->nullable()->after('min_interest_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_plans', function (Blueprint $table) {
            $table->dropColumn(['roi_type', 'min_interest_rate', 'max_interest_rate']);
        });
    }
};
