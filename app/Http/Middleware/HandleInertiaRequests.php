<?php

namespace App\Http\Middleware;

use App\Enums\GameStatus;
use App\Models\Game;
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
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
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
            // Live USD exchange rates fetched hourly by FetchExchangeRates job.
            'exchangeRates' => Cache::get('exchange_rates', []),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
