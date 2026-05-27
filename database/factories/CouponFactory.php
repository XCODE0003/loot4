<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $type = fake()->randomElement(CouponType::cases());

        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $type === CouponType::Percentage
                ? fake()->numberBetween(5, 50)
                : fake()->randomFloat(2, 5, 40),
            'min_order_amount' => fake()->boolean(40) ? fake()->randomFloat(2, 20, 100) : null,
            'usage_limit' => fake()->boolean(50) ? fake()->numberBetween(50, 1000) : null,
            'used_count' => 0,
            'per_user_limit' => fake()->boolean(30) ? 1 : null,
            'starts_at' => null,
            'expires_at' => fake()->boolean(60) ? now()->addDays(fake()->numberBetween(7, 90)) : null,
            'is_active' => true,
        ];
    }
}
