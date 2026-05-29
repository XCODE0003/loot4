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
        $this->configureMailFromSettings();

        // Super Admin bypasses every authorization check.
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('Super Admin') ? true : null);
    }

    /**
     * Override the SMTP mailer with credentials managed in the admin Settings page.
     * Best-effort: never breaks boot if the settings table is missing (fresh install).
     */
    protected function configureMailFromSettings(): void
    {
        try {
            $host = \App\Models\Setting::get('mail_host');

            if (blank($host)) {
                return;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) (\App\Models\Setting::get('mail_port') ?: 587),
                'mail.mailers.smtp.username' => \App\Models\Setting::get('mail_username'),
                'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password'),
                'mail.mailers.smtp.encryption' => \App\Models\Setting::get('mail_encryption') ?: 'tls',
                'mail.from.address' => \App\Models\Setting::get('mail_from_address') ?: config('mail.from.address'),
                'mail.from.name' => \App\Models\Setting::get('mail_from_name') ?: 'Loot4You',
            ]);
        } catch (\Throwable) {
            // Settings table not available yet (e.g. during initial migration).
        }
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
