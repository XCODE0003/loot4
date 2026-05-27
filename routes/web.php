<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/game', [StorefrontController::class, 'game'])->name('game');
Route::get('/product/{slug?}', [StorefrontController::class, 'product'])->name('product');

Route::get('/cart/coupon', [CheckoutController::class, 'coupon'])->name('cart.coupon');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store']);
Route::get('/checkout/success/{order:order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/checkout/webhook', [CheckoutController::class, 'webhook'])->name('checkout.webhook');

// Customer account area (storefront-styled personal cabinet).
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AccountController::class, 'order'])->name('orders.show');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
});

// Keep the legacy dashboard route name pointing at the new account area.
Route::middleware('auth')->get('dashboard', fn () => redirect()->route('account.index'))->name('dashboard');

require __DIR__.'/settings.php';
