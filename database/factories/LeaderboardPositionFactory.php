<?php

namespace Database\Factories;

use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaderboardPositionFactory extends Factory
{
    protected $model = LeaderboardPosition::class;

    public function definition(): array
    {
        return [
            'leaderboard_id' => Leaderboard::factory(),
            'user_id' => User::factory(),
            'position' => fake()->numberBetween(1, 50),
            'referral_count' => fake()->numberBetween(1, 100),
            'prize_amount' => 0,
            'prize_awarded' => false,
            'prize_awarded_at' => null,
        ];
    }

    public function forLeaderboard(Leaderboard $leaderboard): static
    {
        return $this->state(fn (array $attributes) => [
            'leaderboard_id' => $leaderboard->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    public function withReferralCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'referral_count' => $count,
        ]);
    }

    public function withPrize(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_amount' => $amount,
        ]);
    }

    public function prizeAwarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_awarded' => true,
            'prize_awarded_at' => now(),
        ]);
    }

    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 1,
            'prize_amount' => 500,
            'prize_awarded' => false,
        ]);
    }

    public function topThree(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => fake()->numberBetween(1, 3),
            'prize_amount' => fake()->randomElement([500, 300, 200]),
        ]);
    }
}
