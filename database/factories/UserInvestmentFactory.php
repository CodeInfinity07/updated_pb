<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserInvestment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class UserInvestmentFactory extends Factory
{
    protected $model = UserInvestment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 5000);
        $roiPercentage = fake()->randomFloat(2, 1, 5);
        $durationDays = fake()->randomElement([30, 60, 90, 180, 365]);

        return [
            'user_id' => User::factory(),
            'investment_plan_id' => null,
            'type' => UserInvestment::TYPE_INVESTMENT,
            'amount' => $amount,
            'roi_percentage' => $roiPercentage,
            'duration_days' => $durationDays,
            'total_return' => $amount * ($roiPercentage / 100) * $durationDays,
            'daily_return' => $amount * ($roiPercentage / 100),
            'paid_return' => 0,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays($durationDays),
            'last_payout_date' => null,
            'earnings_accumulated' => 0,
            'commission_earned' => 0,
            'expiry_multiplier' => 3,
            'bot_fee_applied' => false,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'end_date' => Carbon::now()->subDays(1),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    public function botFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserInvestment::TYPE_BOT_FEE,
            'amount' => 10,
            'bot_fee_applied' => true,
        ]);
    }

    public function createdAt(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $date,
            'start_date' => $date,
        ]);
    }
}
