<?php

namespace Database\Factories;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use App\Models\ConversionLog;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversionLog>
 */
class ConversionLogFactory extends Factory
{
    protected $model = ConversionLog::class;

    public function definition(): array
    {
        $status = fake()->randomElement(ConversionStatus::cases());

        return [
            'order_id' => Order::factory(),
            'platform' => fake()->randomElement(ConversionPlatform::cases()),
            'event' => fake()->randomElement(['Purchase', 'AddToCart', 'InitiateCheckout', 'Lead']),
            'value' => fake()->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'status' => $status,
            'reason' => $status === ConversionStatus::Failed ? fake()->sentence() : null,
            'url' => fake()->url(),
            'request_payload' => ['event_name' => 'Purchase', 'value' => fake()->randomFloat(2, 10, 500)],
            'response_payload' => $status === ConversionStatus::Sent ? ['events_received' => 1] : null,
            'sent_at' => $status === ConversionStatus::Sent ? now() : null,
        ];
    }
}
