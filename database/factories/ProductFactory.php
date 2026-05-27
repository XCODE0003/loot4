<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Currency;
use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 5, 200);

        return [
            'game_id' => Game::factory(),
            'currency_id' => Currency::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'type' => fake()->randomElement(ProductType::cases()),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'html_description' => '<p>'.fake()->paragraph().'</p>',
            'price' => $price,
            'compare_price' => fake()->boolean(60) ? round($price * fake()->randomFloat(2, 1.1, 1.6), 2) : null,
            'status' => fake()->randomElement([ProductStatus::Active, ProductStatus::Active, ProductStatus::Draft]),
            'auto_delivery' => fake()->boolean(50),
            'delivery_instructions' => fake()->boolean(40) ? fake()->sentence() : null,
            'allowed_payment_methods' => fake()->randomElements(['stripe', 'paypal', 'crypto', 'apple_pay'], 2),
            'visibility' => true,
            'featured' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['featured' => true]);
    }
}
