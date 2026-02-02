<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral_commission_levels')) {
            return;
        }
        
        Schema::create('referral_commission_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default 10 levels
        $levels = [
            ['level' => 1, 'percentage' => 5.00, 'is_active' => true],
            ['level' => 2, 'percentage' => 3.00, 'is_active' => true],
            ['level' => 3, 'percentage' => 2.00, 'is_active' => true],
            ['level' => 4, 'percentage' => 1.50, 'is_active' => true],
            ['level' => 5, 'percentage' => 1.00, 'is_active' => true],
            ['level' => 6, 'percentage' => 0.75, 'is_active' => true],
            ['level' => 7, 'percentage' => 0.50, 'is_active' => true],
            ['level' => 8, 'percentage' => 0.40, 'is_active' => true],
            ['level' => 9, 'percentage' => 0.30, 'is_active' => true],
            ['level' => 10, 'percentage' => 0.20, 'is_active' => true],
        ];

        foreach ($levels as $level) {
            DB::table('referral_commission_levels')->insert([
                'level' => $level['level'],
                'percentage' => $level['percentage'],
                'is_active' => $level['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commission_levels');
    }
};
