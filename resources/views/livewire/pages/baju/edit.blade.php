<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Baju;
use App\Models\KategoriBaju;

new class extends Component {
    #[Layout('components.layouts.app')]

    public Baju $baju;
    public $kategori_baju_id = null;
    public string $nama_baju = '';
    public ?string $ukuran = null;
    public ?string $warna = null;
    public string $harga = '';
    public int $stok_tersedia = 0;

    public function mount(Baju $baju)
    {
        $this->baju = $baju;
        $this->kategori_baju_id = $baju->kategori_baju_id;
        $this->nama_baju = $baju->nama_baju;
        $this->ukuran = $baju->ukuran;
        $this->warna = $baju->warna;
        $this->harga = (string) $baju->harga;
        $this->stok_tersedia = (int) $baju->stok_tersedia;
    }

    public function update()
    {
        $validated = $this->validate([
            'kategori_baju_id' => 'nullable|exists:kategori_bajus,id',
            'nama_baju' => 'required|string|max:150',
            'ukuran' => 'nullable|string|max:20',
            'warna' => 'nullable|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok_tersedia' => 'required|integer|min:0',
        ]);

        $this->baju->update($validated);

        session()->flash('message', __('Baju berhasil diperbarui.'));
        return $this->redirect(route('inventory.baju'), navigate: true);
    }

    public function with(): array
    {
        return [
            'categories' => KategoriBaju::orderBy('nama_kategori')->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Edit Baju') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Ubah data baju untuk inventori') }}</p>
                </div>
                <a href="{{ route('inventory.baju') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200" wire:navigate>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    {{ __('Kembali ke Baju') }}
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="ml-3"><p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('message') }}</p></div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-8">
                <form wire:submit="update" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Kategori') }}</label>
                        <select wire:model="kategori_baju_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— {{ __('Tanpa Kategori') }} —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        @error('kategori_baju_id')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Nama Baju') }} <span class="text-red-500">*</span></label>
                        <input wire:model="nama_baju" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="{{ __('Masukkan nama baju...') }}" required>
                        @error('nama_baju')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Ukuran') }}</label>
                            <input wire:model="ukuran" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="S/M/L/XL">
                            @error('ukuran')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Warna') }}</label>
                            <input wire:model="warna" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Hitam/Putih...">
                            @error('warna')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Harga') }} <span class="text-red-500">*</span></label>
                            <input wire:model="harga" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="0" required>
                            @error('harga')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Stok Tersedia') }}</label>
                        <input wire:model="stok_tersedia" type="number" class="w-full max-w-xs px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" min="0">
                        @error('stok_tersedia')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('inventory.baju') }}" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200" wire:navigate>{{ __('Batal') }}</a>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ __('Simpan Perubahan') }}</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
