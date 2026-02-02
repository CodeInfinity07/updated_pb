<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            [
                'name' => 'Genesis Rock',
                'icon' => 'game-icons:rock',
                'description' => 'The foundation of your journey begins here.',
                'display_order' => 1,
                'min_self_deposit' => 150,
                'min_direct_members' => 3,
                'min_direct_member_investment' => 100,
                'min_team_members' => 0,
                'min_team_member_investment' => 100,
                'reward_amount' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Quantum Stone',
                'icon' => 'game-icons:crystal-ball',
                'description' => 'Harness the power of growth and expansion.',
                'display_order' => 2,
                'min_self_deposit' => 200,
                'min_direct_members' => 5,
                'min_direct_member_investment' => 100,
                'min_team_members' => 0,
                'min_team_member_investment' => 100,
                'reward_amount' => 70,
                'is_active' => true,
            ],
            [
                'name' => 'Mythic Ore',
                'icon' => 'game-icons:ore',
                'description' => 'Legendary materials for legendary achievements.',
                'display_order' => 3,
                'min_self_deposit' => 250,
                'min_direct_members' => 10,
                'min_direct_member_investment' => 100,
                'min_team_members' => 0,
                'min_team_member_investment' => 100,
                'reward_amount' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Celestial Peak',
                'icon' => 'game-icons:mountain-climbing',
                'description' => 'Rise above and reach for the stars.',
                'display_order' => 4,
                'min_self_deposit' => 300,
                'min_direct_members' => 15,
                'min_direct_member_investment' => 100,
                'min_team_members' => 0,
                'min_team_member_investment' => 100,
                'reward_amount' => 300,
                'is_active' => true,
            ],
            [
                'name' => 'Imperial Crown',
                'icon' => 'game-icons:crown',
                'description' => 'The ultimate symbol of leadership and success.',
                'display_order' => 5,
                'min_self_deposit' => 500,
                'min_direct_members' => 25,
                'min_direct_member_investment' => 100,
                'min_team_members' => 0,
                'min_team_member_investment' => 100,
                'reward_amount' => 500,
                'is_active' => true,
            ],
        ];

        foreach ($ranks as $rankData) {
            Rank::updateOrCreate(
                ['name' => $rankData['name']],
                $rankData
            );
        }
    }
}
