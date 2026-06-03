<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAttributionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $attribution
     */
    private function checkout(array $attribution = []): Order
    {
        Product::factory()->create(['slug' => 'gta-cash', 'status' => ProductStatus::Active, 'price' => 20]);

        $payload = [
            'email' => 'buyer@example.com',
            'items' => [['slug' => 'gta-cash', 'qty' => 1]],
        ];

        if ($attribution !== []) {
            $payload['attribution'] = $attribution;
        }

        $this->post('/checkout', $payload)->assertRedirect();

        return Order::where('email', 'buyer@example.com')->firstOrFail();
    }

    public function test_checkout_stores_google_ads_attribution_from_gclid(): void
    {
        $order = $this->checkout([
            'gclid' => 'Cj0KCQjwTEST',
            'landing_page' => 'https://loot4you.gg/game/gta?gclid=Cj0KCQjwTEST',
            'first_visit_at' => '2026-06-01T10:00:00Z',
        ]);

        $this->assertSame('google', $order->source);
        $this->assertSame('cpc', $order->medium);
        $this->assertSame('Cj0KCQjwTEST', $order->gclid);
        $this->assertSame('https://loot4you.gg/game/gta?gclid=Cj0KCQjwTEST', $order->landing_page);
        $this->assertSame('2026-06-01 10:00:00', $order->first_visit_at?->format('Y-m-d H:i:s'));
    }

    public function test_checkout_stores_utm_attribution(): void
    {
        $order = $this->checkout([
            'source' => 'google',
            'medium' => 'cpc',
            'campaign' => 'summer-sale',
            'content' => 'ad-variant-b',
            'term' => 'buy gta money',
        ]);

        $this->assertSame('google', $order->source);
        $this->assertSame('cpc', $order->medium);
        $this->assertSame('summer-sale', $order->campaign);
        $this->assertSame('ad-variant-b', $order->content);
        $this->assertSame('buy gta money', $order->term);
    }

    public function test_checkout_infers_facebook_source_from_fbclid(): void
    {
        $order = $this->checkout(['fbclid' => 'IwAR123']);

        $this->assertSame('facebook', $order->source);
        $this->assertSame('IwAR123', $order->fbclid);
    }

    public function test_checkout_without_attribution_defaults_to_storefront(): void
    {
        $order = $this->checkout();

        $this->assertSame('storefront', $order->source);
        $this->assertNull($order->gclid);
    }

    public function test_checkout_is_not_blocked_by_malformed_attribution(): void
    {
        $order = $this->checkout([
            'gclid' => str_repeat('x', 999),
            'first_visit_at' => 'garbage',
            'campaign' => ['unexpected' => 'array'],
        ]);

        $this->assertSame('google', $order->source);
        $this->assertSame(255, strlen((string) $order->gclid));
        $this->assertNull($order->first_visit_at);
        $this->assertNull($order->campaign);
    }
}
