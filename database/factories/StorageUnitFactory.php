<?php

namespace Database\Factories;

use App\Enums\StorageUnitStatus;
use App\Models\Product;
use App\Models\StorageUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageUnit>
 */
class StorageUnitFactory extends Factory
{
    protected $model = StorageUnit::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => null,
            'type' => fake()->randomElement(['account', 'key', 'code', 'top-up']),
            'stock' => fake()->numberBetween(1, 100),
            'credentials' => fake()->userName().':'.fake()->password(),
            'delivery_data' => fake()->boolean(50) ? fake()->sentence() : null,
            'status' => StorageUnitStatus::Available,
            'expires_at' => fake()->boolean(30) ? now()->addMonths(fake()->numberBetween(1, 12)) : null,
        ];
    }
}
