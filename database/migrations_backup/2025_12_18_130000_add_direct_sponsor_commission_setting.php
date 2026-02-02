<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key' => 'direct_sponsor_commission',
            'value' => '8',
            'type' => 'float',
            'category' => 'commission',
            'description' => 'Percentage commission for direct sponsor on every investment',
            'is_public' => false,
            'is_encrypted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'direct_sponsor_commission')->delete();
    }
};
