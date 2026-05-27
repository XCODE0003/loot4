<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $discount = fake()->boolean(30) ? round($subtotal * 0.1, 2) : 0;
        $createdAt = fake()->dateTimeBetween('-60 days', 'now');

        $paymentStatus = fake()->randomElement([
            PaymentStatus::Paid, PaymentStatus::Paid, PaymentStatus::Paid,
            PaymentStatus::Pending, PaymentStatus::Failed, PaymentStatus::Refunded,
        ]);

        return [
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'ip' => fake()->ipv4(),
            'country' => fake()->countryCode(),
            'status' => $this->statusFor($paymentStatus),
            'payment_status' => $paymentStatus,
            'delivery_status' => $paymentStatus === PaymentStatus::Paid
                ? fake()->randomElement([DeliveryStatus::Delivered, DeliveryStatus::Pending])
                : DeliveryStatus::Pending,
            'currency' => 'USD',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'source' => fake()->randomElement(['google', 'facebook', 'tiktok', 'direct', 'organic']),
            'medium' => fake()->randomElement(['cpc', 'social', 'referral', 'none']),
            'campaign' => fake()->boolean(60) ? fake()->word() : null,
            'fbclid' => fake()->boolean(30) ? fake()->uuid() : null,
            'ttclid' => fake()->boolean(20) ? fake()->uuid() : null,
            'landing_page' => fake()->boolean(70) ? '/'.fake()->slug() : null,
            'first_visit_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    private function statusFor(PaymentStatus $paymentStatus): OrderStatus
    {
        return match ($paymentStatus) {
            PaymentStatus::Paid => fake()->randomElement([OrderStatus::Completed, OrderStatus::Processing]),
            PaymentStatus::Refunded => OrderStatus::Refunded,
            PaymentStatus::Failed => OrderStatus::Cancelled,
            default => OrderStatus::Pending,
        };
    }
}
