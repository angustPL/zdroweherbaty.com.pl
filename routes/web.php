<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Volt::route('/', 'pages.welcome')->name('home');

// Strony statyczne
Volt::route('/dostawa', 'pages.delivery')->name('delivery');
Volt::route('/regulamin', 'pages.terms')->name('terms');
Volt::route('/kontakt', 'pages.contact')->name('contact');
Volt::route('/polityka-prywatnosci', 'pages.privacy')->name('privacy');

// Produkty
Volt::route('/grupa/{group}', 'pages.group')->name('group');
Volt::route('/towar/{id}/{name?}', 'pages.product')->name('product');
Volt::route('/wyszukaj', 'pages.search')->name('search');

// Koszyk
Route::get('/koszyk', App\Livewire\Pages\Cart::class)->name('cart');

// Zamówienie
Volt::route('/zamawianie', 'pages.order-create')->name('order.create');
Volt::route('/zamowienie/{ext_order_id}', 'pages.order-info')
    ->middleware([\App\Http\Middleware\SetOrderNotFoundStatus::class])
    ->name('order.info');
Route::get('/zamowienie/{ext_order_id}/email-preview', [App\Http\Controllers\OrderEmailPreviewController::class, 'preview'])
    ->name('order.email-preview');

// PayU callbacks
Route::post('/payu/notify', [App\Http\Controllers\PayuController::class, 'notify'])->name('payu.notify');
Route::get('/payu/success', [App\Http\Controllers\PayuController::class, 'success'])->name('payu.success');
Route::get('/zamowienie/{ext_order_id}/retry-payment', [App\Http\Controllers\PayuController::class, 'retry'])->name('order.retry-payment');

// Cache management
Route::prefix('cache')->group(function () {
    Route::get('/', [App\Http\Controllers\CacheController::class, 'index'])->name('cache.index');
    Route::get('/status/{type}', [App\Http\Controllers\CacheController::class, 'status'])->name('cache.status');
    Route::post('/clear/{type}', [App\Http\Controllers\CacheController::class, 'clear'])->name('cache.clear');
    Route::post('/clear/{type}/{param}', [App\Http\Controllers\CacheController::class, 'clear'])->name('cache.clear.param');
    Route::post('/clear/all', function () {
        return app(App\Http\Controllers\CacheController::class)->clear(request(), 'all');
    })->name('cache.clear.all');
});

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Ustawienia - tylko dla admina
    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::redirect('settings', 'settings/profile');
        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    });

    // Promocje - zarządzanie
    Volt::route('promocje', 'pages.promotions')->name('promotions');
});

// Newsletter routes
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{id}/{token}', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

require __DIR__ . '/auth.php';
