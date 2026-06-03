<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConversionLogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/game', [StorefrontController::class, 'game'])->name('game');
Route::get('/game/{slug}', [StorefrontController::class, 'gameBySlug'])->name('game.show');
Route::get('/product/{slug?}', [StorefrontController::class, 'product'])->name('product');

Route::get('/cart/coupon', [CheckoutController::class, 'coupon'])->name('cart.coupon');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store']);
Route::get('/checkout/success/{order:order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
// Pixel-fire debug log written by the success page (Conversion Logs in admin).
Route::post('/checkout/conversion', [ConversionLogController::class, 'store'])
    ->middleware('throttle:60,1')->name('checkout.conversion');
// Some gateways validate the notification URL with a GET/HEAD before sending —
// answer 200 so the webhook URL is accepted.
Route::match(['get', 'head'], '/checkout/webhook', fn () => response()->json(['ok' => true]));
// IceNox delivers the status callback via PATCH; accept POST/PUT too for safety.
Route::match(['post', 'patch', 'put'], '/checkout/webhook', [CheckoutController::class, 'webhook'])->name('checkout.webhook');

// Static content pages (footer + header links).
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/sell', [PageController::class, 'sell'])->name('pages.sell');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/refunds', [PageController::class, 'refunds'])->name('pages.refunds');
Route::get('/payment-policy', [PageController::class, 'paymentPolicy'])->name('pages.payment-policy');
Route::get('/shipping-policy', [PageController::class, 'shippingPolicy'])->name('pages.shipping-policy');
Route::get('/cookies-policy', [PageController::class, 'cookiesPolicy'])->name('pages.cookies-policy');

// Customer account area (storefront-styled personal cabinet).
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AccountController::class, 'order'])->name('orders.show');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
});

// Social login (Google / Discord OAuth2).
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->whereIn('provider', ['google', 'discord'])->name('oauth.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->whereIn('provider', ['google', 'discord'])->name('oauth.callback');

// Keep the legacy dashboard route name pointing at the new account area.
Route::middleware('auth')->get('dashboard', fn () => redirect()->route('account.index'))->name('dashboard');

require __DIR__.'/settings.php';
