<?php

namespace Database\Seeders;

use App\Models\SalaryStage;
use Illuminate\Database\Seeder;

class SalaryStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => '1st Salary',
                'stage_order' => 1,
                'direct_members_required' => 15,
                'self_deposit_required' => 100.00,
                'team_required' => 60,
                'salary_amount' => 200.00,
                'is_active' => true,
            ],
            [
                'name' => '2nd Salary',
                'stage_order' => 2,
                'direct_members_required' => 30,
                'self_deposit_required' => 100.00,
                'team_required' => 175,
                'salary_amount' => 400.00,
                'is_active' => true,
            ],
            [
                'name' => '3rd Salary',
                'stage_order' => 3,
                'direct_members_required' => 35,
                'self_deposit_required' => 100.00,
                'team_required' => 500,
                'salary_amount' => 600.00,
                'is_active' => true,
            ],
            [
                'name' => '4th Salary',
                'stage_order' => 4,
                'direct_members_required' => 60,
                'self_deposit_required' => 100.00,
                'team_required' => 1000,
                'salary_amount' => 1000.00,
                'is_active' => true,
            ],
            [
                'name' => '5th Salary',
                'stage_order' => 5,
                'direct_members_required' => 250,
                'self_deposit_required' => 100.00,
                'team_required' => 1530,
                'salary_amount' => 2000.00,
                'is_active' => true,
            ],
            [
                'name' => '6th Salary',
                'stage_order' => 6,
                'direct_members_required' => 300,
                'self_deposit_required' => 100.00,
                'team_required' => 2500,
                'salary_amount' => 4000.00,
                'is_active' => true,
            ],
        ];

        foreach ($stages as $stage) {
            SalaryStage::updateOrCreate(
                ['stage_order' => $stage['stage_order']],
                $stage
            );
        }
    }
}
