<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Conversion delivery is stubbed until real platform SDKs are wired up.
        $this->app->bind(
            \App\Services\Conversions\ConversionDispatcher::class,
            \App\Services\Conversions\StubConversionDispatcher::class,
        );

        // IceNox Pay gateway, configured from services config.
        $this->app->singleton(
            \App\Services\Payments\IceNoxGateway::class,
            fn () => new \App\Services\Payments\IceNoxGateway(
                config('services.icenox.key'),
                config('services.icenox.merchant'),
                config('services.icenox.url', 'https://imp.icenox.com'),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Super Admin bypasses every authorization check.
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('Super Admin') ? true : null);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
