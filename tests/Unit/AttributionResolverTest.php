<?php

namespace Tests\Unit;

use App\Services\Attribution\AttributionResolver;
use PHPUnit\Framework\TestCase;

class AttributionResolverTest extends TestCase
{
    private AttributionResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new AttributionResolver;
    }

    public function test_gclid_infers_google_cpc(): void
    {
        $resolved = $this->resolver->resolve(['gclid' => 'Cj0KCQjw']);

        $this->assertSame('google', $resolved['source']);
        $this->assertSame('cpc', $resolved['medium']);
        $this->assertSame('Cj0KCQjw', $resolved['gclid']);
    }

    public function test_fbclid_and_ttclid_infer_their_platforms(): void
    {
        $this->assertSame('facebook', $this->resolver->resolve(['fbclid' => 'abc'])['source']);
        $this->assertSame('tiktok', $this->resolver->resolve(['ttclid' => 'abc'])['source']);
    }

    public function test_utm_params_win_over_click_id_inference(): void
    {
        $resolved = $this->resolver->resolve([
            'source' => 'google',
            'medium' => 'display',
            'campaign' => 'summer',
            'content' => 'banner-1',
            'term' => 'gta cash',
            'gclid' => 'xyz',
        ]);

        $this->assertSame('google', $resolved['source']);
        $this->assertSame('display', $resolved['medium']);
        $this->assertSame('summer', $resolved['campaign']);
        $this->assertSame('banner-1', $resolved['content']);
        $this->assertSame('gta cash', $resolved['term']);
    }

    public function test_external_referrer_host_is_used_as_source(): void
    {
        $resolved = $this->resolver->resolve(
            ['referrer' => 'https://www.reddit.com/r/gaming'],
            'loot4you.gg',
        );

        $this->assertSame('www.reddit.com', $resolved['source']);
        $this->assertSame('referral', $resolved['medium']);
    }

    public function test_own_host_referrer_is_ignored(): void
    {
        $resolved = $this->resolver->resolve(
            ['referrer' => 'https://loot4you.gg/game/gta'],
            'loot4you.gg',
        );

        $this->assertSame('storefront', $resolved['source']);
        $this->assertNull($resolved['medium']);
    }

    public function test_empty_attribution_falls_back_to_storefront(): void
    {
        $resolved = $this->resolver->resolve([]);

        $this->assertSame('storefront', $resolved['source']);
        $this->assertNull($resolved['medium']);
        $this->assertNull($resolved['gclid']);
        $this->assertNull($resolved['first_visit_at']);
    }

    public function test_malformed_values_are_sanitized_not_fatal(): void
    {
        $resolved = $this->resolver->resolve([
            'source' => ['nested' => 'array'],
            'gclid' => str_repeat('a', 600),
            'first_visit_at' => 'not-a-date',
            'landing_page' => str_repeat('https://x.gg/', 40),
        ]);

        $this->assertSame('google', $resolved['source']);
        $this->assertSame(255, strlen($resolved['gclid']));
        $this->assertNull($resolved['first_visit_at']);
        $this->assertLessThanOrEqual(255, strlen($resolved['landing_page']));
    }

    public function test_first_visit_timestamp_is_parsed(): void
    {
        $resolved = $this->resolver->resolve(['first_visit_at' => '2026-06-01T10:00:00Z']);

        $this->assertSame('2026-06-01 10:00:00', $resolved['first_visit_at']?->format('Y-m-d H:i:s'));
    }
}
