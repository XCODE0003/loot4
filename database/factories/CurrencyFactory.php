<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $code = fake()->unique()->currencyCode();

        return [
            'title' => $code,
            'code' => $code,
            'symbol' => fake()->randomElement(['$', '€', '£', '¥']),
            'exchange_rate' => fake()->randomFloat(4, 0.5, 1.5),
            'auto_update' => fake()->boolean(30),
            'is_default' => false,
            'last_updated_at' => now(),
        ];
    }
}
