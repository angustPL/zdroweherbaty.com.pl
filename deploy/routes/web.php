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

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Strony statyczne
Volt::route('/dostawa', 'pages.dostawa')->name('dostawa');
Volt::route('/regulamin', 'pages.regulamin')->name('regulamin');
Volt::route('/kontakt', 'pages.kontakt')->name('kontakt');

// Wyszukiwarka produktów
Volt::route('/wyszukaj', 'pages.search')->name('search');

// Koszyk
Route::get('/koszyk', App\Livewire\Pages\Cart::class)->name('koszyk');

// Grupy produktów
Volt::route('/grupa/{group}', 'pages.grupa')->name('grupa');

// Produkt
Volt::route('/towar/{id}/{name?}', 'pages.towar')->name('towar');

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
