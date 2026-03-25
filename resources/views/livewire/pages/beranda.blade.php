<?php

use App\Models\Baju;
use App\Models\NotifikasiStok;
use App\Models\Transaksi;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'totalProduk' => Baju::count(),
            'totalStok' => (int) Baju::sum('stok_tersedia'),
            'unreadNotif' => NotifikasiStok::where('terbaca', false)->count(),
            'transaksiHariIni' => Transaksi::whereDate('tanggal', now())->count(),
            'lowStockItems' => Baju::with('kategori')
                ->where('stok_tersedia', '<', 10)
                ->orderBy('stok_tersedia')
                ->limit(4)
                ->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <div class="grid gap-8 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-600 dark:text-emerald-400">{{ __('Beranda') }}</p>
                    <h1 class="mt-4 max-w-2xl text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                        {{ __('Selamat datang, :name', ['name' => auth()->user()->name]) }}
                    </h1>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 dark:text-slate-300 sm:text-[17px]">
                        {{ __('Website inventori terbaru Aria Caspira hadir sebagai pusat kendali digital untuk memastikan setiap koleksi streetwear kami terkelola dengan akurasi tinggi dan efisiensi maksimal. Melalui sistem pemantauan stok secara real-time, kami kini dapat mengintegrasikan data produksi mulai dari kategori boxy fit hingga desain unisex secara lebih terukur, guna meminimalisir kesalahan manual dan menjaga ketersediaan produk bagi pelanggan. Langkah digitalisasi ini merupakan komitmen nyata kami dalam memperkuat fondasi operasional brand, sehingga proses distribusi menjadi lebih transparan dan profesional seiring dengan berkembangnya kreativitas Aria Caspira di industri apparel.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('inventory.baju') }}" wire:navigate class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-500">
                            {{ __('Kelola Baju') }}
                        </a>
                        <a href="{{ route('inventory.transaksi') }}" wire:navigate class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:border-emerald-500 dark:hover:text-emerald-300">
                            {{ __('Lihat Transaksi') }}
                        </a>
                        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center rounded-2xl border border-transparent px-5 py-3 text-sm font-semibold text-slate-600 transition hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-300">
                            {{ __('Buka Dashboard Lengkap') }}
                        </a>
                    </div>
                </div>

                <div class="grid gap-4 rounded-[1.5rem] bg-gray-900 p-5 text-white shadow-xl">
                    <div class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3">
                        <span class="text-sm text-gray-300">{{ __('Produk Terdaftar') }}</span>
                        <span class="text-2xl font-bold">{{ number_format($totalProduk) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3">
                        <span class="text-sm text-gray-300">{{ __('Total Stok') }}</span>
                        <span class="text-2xl font-bold">{{ number_format($totalStok) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3">
                        <span class="text-sm text-gray-300">{{ __('Notifikasi Baru') }}</span>
                        <span class="text-2xl font-bold text-amber-300">{{ number_format($unreadNotif) }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-emerald-500/20 px-4 py-3">
                        <span class="text-sm text-emerald-100">{{ __('Transaksi Hari Ini') }}</span>
                        <span class="text-2xl font-bold text-emerald-300">{{ number_format($transaksiHariIni) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Akses Cepat') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Shortcut ke menu yang paling sering dipakai.') }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('kategori.create') }}" wire:navigate class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:hover:border-emerald-500">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tambah Kategori') }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Buat kategori baru untuk mengelompokkan produk.') }}</p>
                    </a>
                    <a href="{{ route('baju.create') }}" wire:navigate class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:hover:border-emerald-500">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Tambah Baju') }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Masukkan produk baru lengkap dengan stok dan gambar.') }}</p>
                    </a>
                    <a href="{{ route('transaksi.create') }}" wire:navigate class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:hover:border-emerald-500">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Input Transaksi') }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Catat barang masuk atau keluar dengan cepat.') }}</p>
                    </a>
                    <a href="{{ route('inventory.notifications') }}" wire:navigate class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:hover:border-emerald-500">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Cek Notifikasi') }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Pantau stok menipis dan produk yang perlu perhatian.') }}</p>
                    </a>
                </div>
            </div>

            <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Perlu Perhatian') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Produk dengan stok paling rendah saat ini.') }}</p>
                    </div>
                    <a href="{{ route('inventory.baju') }}" wire:navigate class="text-sm font-semibold text-emerald-600 hover:text-emerald-500">{{ __('Lihat Semua') }}</a>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($lowStockItems as $item)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-800">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $item->nama_baju }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $item->kategori?->nama_kategori ?? __('Tanpa kategori') }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300">
                                {{ __('Stok :stok', ['stok' => $item->stok_tersedia]) }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            {{ __('Belum ada produk dengan stok kritis.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
