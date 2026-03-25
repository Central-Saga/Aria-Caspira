<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('beranda')
        : redirect()->route('login');
})->name('home');

Volt::route('beranda', 'pages.beranda')
    ->middleware(['auth', 'verified'])
    ->name('beranda');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Inventori: Kategori Baju
    Volt::route('inventori/kategori-baju', 'pages.kategori-baju.index')
        ->name('inventory.categories');
    Volt::route('inventori/kategori-baju/create', 'pages.kategori-baju.create')
        ->name('kategori.create');
    Volt::route('inventori/kategori-baju/edit/{kategori}', 'pages.kategori-baju.edit')
        ->name('kategori.edit');

    // Inventori: Baju
    Volt::route('inventori/baju', 'pages.baju.index')
        ->name('inventory.baju');
    Volt::route('inventori/baju/create', 'pages.baju.create')
        ->name('baju.create');
    Volt::route('inventori/baju/edit/{baju}', 'pages.baju.edit')
        ->name('baju.edit');

    // Inventori: Transaksi
    Volt::route('inventori/transaksi', 'pages.transaksi.index')
        ->name('inventory.transaksi');
    Volt::route('inventori/transaksi/create', 'pages.transaksi.create')
        ->name('transaksi.create');
    Volt::route('inventori/transaksi/edit/{transaksi}', 'pages.transaksi.edit')
        ->name('transaksi.edit');

    // Inventori: Notifikasi Stok
    Volt::route('inventori/notifikasi-stok', 'pages.notifikasistok.index')
        ->name('inventory.notifications');
    Volt::route('inventori/notifikasi-stok/create', 'pages.notifikasistok.create')
        ->name('notifikasi.create');
    Volt::route('inventori/notifikasi-stok/edit/{notifikasi}', 'pages.notifikasistok.edit')
        ->name('notifikasi.edit');

    // Manajemen Pengguna
    Volt::route('pengguna', 'pages.user.index')
        ->name('users.index');
    Volt::route('pengguna/create', 'pages.user.create')
        ->name('users.create');
    Volt::route('pengguna/edit/{user}', 'pages.user.edit')
        ->name('users.edit');

    // Manajemen Roles (Super Admin only)
    Volt::route('roles', 'pages.roles.index')
        ->name('roles.index');
    Volt::route('roles/create', 'pages.roles.create')
        ->name('roles.create');
    Volt::route('roles/edit/{role}', 'pages.roles.edit')
        ->name('roles.edit');

    // Activity Logs
    Volt::route('activity/logs', 'pages.activity.index')
        ->name('activity.index');

    // Laporan Transaksi (printable HTML -> simpan PDF via browser)
    Route::get('laporan/transaksi/print', [\App\Http\Controllers\ReportController::class, 'transaksi'])
        ->name('transaksi.print');
});

require __DIR__.'/auth.php';
