<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['stripe', 'paypal', 'crypto', 'apple_pay']),
            'transaction_id' => strtoupper(fake()->bothify('TXN-########')),
            'amount' => fake()->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'status' => fake()->randomElement(PaymentStatus::cases()),
        ];
    }
}
