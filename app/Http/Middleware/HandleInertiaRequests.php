<?php

namespace App\Http\Middleware;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Setting;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $adminPanel = Filament::getPanel('admin');
        $canAccessAdmin = $user !== null && $user->canAccessPanel($adminPanel);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'canAccessAdmin' => $canAccessAdmin,
                // Only leak the obscure admin URL to users who can actually use it.
                'adminUrl' => $canAccessAdmin ? $adminPanel->getUrl() : null,
            ],
            // Active storefront locale (en/ru/de) — from cookie for SSR-safe i18n.
            'locale' => in_array($request->cookie('locale'), ['en', 'es', 'nl', 'ar', 'de', 'it', 'fr'], true)
                ? $request->cookie('locale')
                : 'en',
            // Active games for the storefront header "Choose Game" dropdown.
            'navGames' => fn () => Game::query()
                ->where('status', GameStatus::Active->value)
                ->orderBy('sort_order')
                ->take(8)
                ->get(['id', 'name', 'slug'])
                ->map(fn (Game $g): array => [
                    'name' => $g->name,
                    'slug' => $g->slug,
                    'icon' => $g->getFirstMediaUrl('icon') ?: $g->getFirstMediaUrl('image') ?: null,
                ])
                ->all(),
            // Marketing/analytics tag IDs (all public by nature — they ship in
            // page source on any site). Trackers load only after cookie consent.
            'tracking' => [
                'gaId' => Setting::get('ga_measurement_id'),
                'googleAdsId' => Setting::get('google_ads_conversion_id'),
                'googleAdsLabel' => Setting::get('google_ads_conversion_label'),
                'facebookPixelId' => Setting::get('facebook_pixel_id'),
                'tiktokPixelId' => Setting::get('tiktok_pixel_id'),
            ],
            // Live USD exchange rates fetched hourly by FetchExchangeRates job.
            'exchangeRates' => Cache::get('exchange_rates', []),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
