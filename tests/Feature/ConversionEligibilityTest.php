<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\Conversions\ConversionEligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_click_id_restricts_to_google_ads(): void
    {
        $order = Order::factory()->make(['source' => 'google', 'gclid' => 'abc', 'fbclid' => null, 'ttclid' => null]);

        $this->assertSame(['google_ads'], ConversionEligibility::for($order));
    }

    public function test_facebook_source_restricts_to_facebook_pixel(): void
    {
        $order = Order::factory()->make(['source' => 'facebook', 'gclid' => null, 'fbclid' => null, 'ttclid' => null]);

        $this->assertSame(['facebook_pixel'], ConversionEligibility::for($order));
    }

    public function test_tiktok_click_id_restricts_to_tiktok(): void
    {
        $order = Order::factory()->make(['source' => 'organic', 'gclid' => null, 'fbclid' => null, 'ttclid' => 'tt1']);

        $this->assertSame(['tiktok'], ConversionEligibility::for($order));
    }

    public function test_organic_order_has_no_restriction(): void
    {
        $order = Order::factory()->make(['source' => 'direct', 'gclid' => null, 'fbclid' => null, 'ttclid' => null]);

        $this->assertNull(ConversionEligibility::for($order));
    }
}
