<?php

use App\Jobs\FetchExchangeRates;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('exchange-rates:fetch', function () {
    (new FetchExchangeRates)->handle();
    $this->info('Exchange rates fetched and cached.');
})->purpose('Fetch live USD exchange rates and store in cache');

Schedule::job(new FetchExchangeRates)->hourly();

// Remind customers who left an order unpaid (once per order, ~1h after checkout).
Schedule::command('orders:remind-abandoned')->everyFifteenMinutes()->withoutOverlapping();
