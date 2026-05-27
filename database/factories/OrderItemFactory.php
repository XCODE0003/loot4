<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'quantity' => fake()->numberBetween(1, 3),
            'price' => fake()->randomFloat(2, 5, 200),
            'status' => fake()->randomElement(DeliveryStatus::cases()),
            'form_data' => [
                'platform' => fake()->randomElement(['PS4', 'PS5', 'Xbox', 'PC']),
                'amount' => fake()->randomElement(['10M', '50M', '100M']),
            ],
        ];
    }
}
