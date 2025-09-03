<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Baju;

new class extends Component {
    use WithPagination;

    #[Layout('components.layouts.app')]

    public string $search = '';
    public bool $showDeleteModal = false;
    public ?Baju $bajuToDelete = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $bajuId): void
    {
        try {
            $this->bajuToDelete = Baju::findOrFail($bajuId);
            $this->showDeleteModal = true;
            $this->dispatch('modal-opened');
        } catch (\Exception $e) {
            session()->flash('error', __('Item not found.'));
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->bajuToDelete = null;
    }

    public function deleteBaju(int $bajuId): void
    {
        try {
            $baju = Baju::findOrFail($bajuId);
            $baju->delete();
            session()->flash('message', __('Baju berhasil dihapus.'));
        } catch (\Exception $e) {
            session()->flash('error', __('Gagal menghapus baju: ') . $e->getMessage());
        }

        $this->cancelDelete();
    }

    public function with(): array
    {
        $query = Baju::with('kategori');

        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('nama_baju', 'like', $s)
                  ->orWhere('ukuran', 'like', $s)
                  ->orWhere('warna', 'like', $s);
            });
        }

        return [
            'items' => $query->orderByDesc('created_at')->paginate(9),
            'stats' => [
                'totalItems' => Baju::count(),
                'totalStock' => (int) Baju::sum('stok_tersedia'),
            ],
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        {{ __('Baju') }}
                    </h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">
                        {{ __('Kelola data baju untuk inventori') }}
                    </p>
                </div>
                <a href="{{ route('baju.create') }}"
                   class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                   title="{{ __('Buat Baju') }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('Buat Baju') }}
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Baju') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['totalItems'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Stok') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['totalStock']) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pencarian aktif') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $search !== '' ? 'Ya' : 'Tidak' }}</p>
            </div>
        </div>

        <div class="mb-8">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input wire:model.live="search" type="text"
                       class="block w-full pl-12 pr-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-lg"
                       placeholder="{{ __('Cari baju...') }}" title="{{ __('Cari baju...') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($items as $item)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div class="h-12 w-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2l2 2h4l2-2h2a2 2 0 012 2v2l-2 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6L4 8V6z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $item->nama_baju }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $item->kategori?->nama_kategori ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                                <a href="{{ route('baju.edit', $item) }}"
                                   class="p-2 text-gray-600 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-lg transition-colors"
                                   title="{{ __('Edit Baju') }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $item->id }})"
                                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="{{ __('Hapus Baju') }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Ukuran') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->ukuran ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Warna') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->warna ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Harga') }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($item->harga, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Stok') }}</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->stok_tersedia }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2l2 2h4l2-2h2a2 2 0 012 2v2l-2 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6L4 8V6z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('Tidak ada data') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Mulai dengan menambahkan baju baru.') }}</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="mt-8">{{ $items->links() }}</div>
        @endif
    </div>

    <div style="display: {{ $showDeleteModal ? 'block' : 'none' }};">
        <div class="fixed inset-0 backdrop-blur-md transition-opacity z-50" wire:click="cancelDelete"></div>

        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Hapus Baju') }}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($bajuToDelete)
                                        {{ __('Yakin ingin menghapus') }}
                                        <strong class="text-gray-900 dark:text-white">{{ $bajuToDelete->nama_baju }}</strong>?
                                    @else
                                        {{ __('Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="deleteBaju({{ $bajuToDelete ? $bajuToDelete->id : 0 }})" type="button" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors sm:w-auto">
                            {{ __('Hapus') }}
                        </button>
                        <button wire:click="cancelDelete" type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-gray-100 dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white shadow-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors sm:mt-0 sm:w-auto">
                            {{ __('Batal') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
