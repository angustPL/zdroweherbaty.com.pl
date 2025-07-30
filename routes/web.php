<?php

use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'pages.welcome')->name('home');
Volt::route('/dostawa', 'pages.dostawa')->name('dostawa');
Volt::route('/regulamin', 'pages.regulamin')->name('regulamin');
Volt::route('/kontakt', 'pages.kontakt')->name('kontakt');
Volt::route('/grupa/{grupa}', 'pages.grupa')->name('grupa');
Volt::route('/towar/{id}', 'pages.towar')->name('towar');
Volt::route('/test-cart', 'pages.test-cart')->name('test-cart');
Volt::route('/koszyk', 'pages.koszyk')->name('koszyk');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
