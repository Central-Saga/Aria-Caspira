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

    // --- TAMBAHKAN SEMUA ROUTE KATEGORI BAJU DI SINI ---
    Volt::route('kategori-baju', 'pages.kategori-baju.index')->name('kategori-baju.index');
    Volt::route('kategori-baju/tambah', 'pages.kategori-baju.create')->name('kategori-baju.create');
    Volt::route('kategori-baju/{kategori}/edit', 'pages.kategori-baju.edit')->name('kategori-baju.edit');
    // --- BATAS PENAMBAHAN ROUTE ---
});

require __DIR__.'/auth.php';