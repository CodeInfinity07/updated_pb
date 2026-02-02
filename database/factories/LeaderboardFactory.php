<?php

namespace Database\Factories;

use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LeaderboardFactory extends Factory
{
    protected $model = Leaderboard::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->subDays(rand(1, 5));
        $endDate = Carbon::now()->addDays(rand(5, 30));

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => 'active',
            'type' => 'competitive',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'show_to_users' => true,
            'max_positions' => 50,
            'referral_type' => 'direct',
            'max_referral_level' => 1,
            'min_investment_amount' => null,
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
            'target_referrals' => null,
            'target_prize_amount' => null,
            'target_tiers' => null,
            'max_winners' => null,
            'prizes_distributed' => false,
            'created_by' => null,
        ];
    }

    public function competitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'competitive',
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
            'target_referrals' => null,
            'target_prize_amount' => null,
            'target_tiers' => null,
        ]);
    }

    public function target(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'target',
            'prize_structure' => null,
            'target_referrals' => 10,
            'target_prize_amount' => 100.00,
            'target_tiers' => null,
        ]);
    }

    public function multiTier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'target',
            'prize_structure' => null,
            'target_referrals' => null,
            'target_prize_amount' => null,
            'target_tiers' => [
                ['target' => 5, 'amount' => 50],
                ['target' => 10, 'amount' => 100],
                ['target' => 20, 'amount' => 250],
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'end_date' => Carbon::now()->subDays(1),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(35),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_to_users' => false,
        ]);
    }

    public function multiLevel(int $maxLevel = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'referral_type' => 'multi_level',
            'max_referral_level' => $maxLevel,
        ]);
    }

    public function withMinInvestment(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'min_investment_amount' => $amount,
        ]);
    }

    public function withMaxWinners(int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'max_winners' => $max,
        ]);
    }

    public function prizesDistributed(): static
    {
        return $this->state(fn (array $attributes) => [
            'prizes_distributed' => true,
            'prizes_distributed_at' => now(),
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
