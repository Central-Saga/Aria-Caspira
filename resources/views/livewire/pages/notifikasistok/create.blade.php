<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\NotifikasiStok;
use App\Models\Baju;

new class extends Component {
    #[Layout('components.layouts.app')]

    public int $baju_id;
    public string $status = 'warning';
    public ?string $pesan = null;
    public bool $terbaca = false;

    public function mount()
    {
        $this->baju_id = (int) (Baju::query()->value('id') ?? 0);
    }

    public function save()
    {
        $validated = $this->validate([
            'baju_id' => 'required|exists:bajus,id',
            'status' => 'required|in:warning,critical',
            'pesan' => 'nullable|string|max:255',
            'terbaca' => 'boolean',
        ]);

        NotifikasiStok::create($validated);

        session()->flash('message', __('Notifikasi dibuat.'));
        return $this->redirect(route('inventory.notifications'), navigate: true);
    }

    public function with(): array
    {
        return [
            'items' => Baju::orderBy('nama_baju')->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Buat Notifikasi Stok') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Peringatan stok untuk produk tertentu') }}</p>
                </div>
                <a href="{{ route('inventory.notifications') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200" wire:navigate>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Kembali ke Notifikasi') }}
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-8">
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Produk (Baju)') }}</label>
                        <select wire:model="baju_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach($items as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_baju }} — Stok: {{ $b->stok_tersedia }}</option>
                            @endforeach
                        </select>
                        @error('baju_id')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }}</label>
                            <select wire:model="status" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="warning">Warning</option>
                                <option value="critical">Critical</option>
                            </select>
                            @error('status')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pesan (opsional)') }}</label>
                            <input wire:model="pesan" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="stok menipis / habis ...">
                            @error('pesan')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="terbaca" type="checkbox" wire:model="terbaca" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="terbaca" class="text-sm text-gray-700 dark:text-gray-300">{{ __('Tandai sebagai terbaca') }}</label>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('inventory.notifications') }}" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200" wire:navigate>{{ __('Batal') }}</a>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ __('Simpan') }}</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

