<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Inventori: Kategori Baju
    Volt::route('inventori/kategori-baju', 'pages.kategori-baju.index')->name('inventory.categories');
    Volt::route('inventori/kategori-baju/create', 'pages.kategori-baju.create')->name('kategori.create');
    Volt::route('inventori/kategori-baju/edit/{kategori}', 'pages.kategori-baju.edit')->name('kategori.edit');
});

require __DIR__.'/auth.php';
