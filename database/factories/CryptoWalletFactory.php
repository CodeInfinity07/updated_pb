<?php

namespace Database\Factories;

use App\Models\CryptoWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoWalletFactory extends Factory
{
    protected $model = CryptoWallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency' => 'USDT_BEP20',
            'name' => 'USDT BEP20 Wallet',
            'address' => null,
            'balance' => 0,
            'usd_rate' => 1.00,
            'is_active' => true,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
        ]);
    }

    public function withAddress(string $address): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => $address,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function usdt(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USDT_BEP20',
            'name' => 'USDT BEP20 Wallet',
        ]);
    }
}
