<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $status = fake()->randomElement(QuoteStatus::cases());

        return [
            'user_id' => null,
            'product_id' => null,
            'assigned_to' => null,
            'email' => fake()->safeEmail(),
            'fields' => [
                'platform' => fake()->randomElement(['PS4', 'PS5', 'Xbox', 'PC']),
                'quantity' => fake()->numberBetween(1, 10),
            ],
            'message' => fake()->paragraph(),
            'status' => $status,
            'quoted_price' => in_array($status, [QuoteStatus::Approved, QuoteStatus::Pending], true)
                ? fake()->randomFloat(2, 20, 300)
                : null,
            'currency' => 'USD',
            'manager_response' => $status === QuoteStatus::Approved ? fake()->sentence() : null,
        ];
    }
}
