<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\NotifikasiStok;

new class extends Component {
    use WithPagination;

    #[Layout('components.layouts.app')]

    public string $search = '';
    public string $status = '';
    public string $read = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingRead() { $this->resetPage(); }

    public function toggleRead(int $id)
    {
        $n = NotifikasiStok::find($id);
        if (!$n) return;
        $n->update(['terbaca' => ! $n->terbaca]);
    }

    public function with(): array
    {
        $query = NotifikasiStok::with('baju');
        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('pesan', 'like', $s)
                  ->orWhereHas('baju', fn($b) => $b->where('nama_baju', 'like', $s));
            });
        }
        if (in_array($this->status, ['warning','critical'])) {
            $query->where('status', $this->status);
        }
        if (in_array($this->read, ['read','unread'])) {
            $query->where('terbaca', $this->read === 'read');
        }

        return [
            'notifications' => $query->orderByDesc('created_at')->paginate(10),
            'stats' => [
                'total' => NotifikasiStok::count(),
                'unread' => NotifikasiStok::where('terbaca', false)->count(),
                'critical' => NotifikasiStok::where('status', 'critical')->count(),
            ],
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Notifikasi Stok') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Peringatan stok menipis/critical per produk') }}</p>
                </div>
                <a href="{{ route('notifikasi.create') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Buat Notifikasi') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum Terbaca') }}</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats['unread'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Critical') }}</p>
                <p class="text-2xl font-bold text-red-600 mt-2">{{ $stats['critical'] }}</p>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" class="block w-full pl-12 pr-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-lg" placeholder="{{ __('Cari baju atau pesan...') }}">
            </div>
            <div>
                <select wire:model.live="status" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="warning">{{ __('Warning') }}</option>
                    <option value="critical">{{ __('Critical') }}</option>
                </select>
            </div>
            <div>
                <select wire:model.live="read" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Notifikasi') }}</option>
                    <option value="unread">{{ __('Belum Terbaca') }}</option>
                    <option value="read">{{ __('Sudah Terbaca') }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($notifications as $n)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <span class="h-3 w-3 rounded-full {{ $n->status==='critical' ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $n->created_at?->format('d M Y H:i') }}</p>
                                <p class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $n->baju?->nama_baju }}
                                    <span class="ml-2 text-xs font-medium inline-flex px-2 py-0.5 rounded-full {{ $n->status==='critical' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">{{ ucfirst($n->status) }}</span>
                                    @if(!$n->terbaca)
                                        <span class="ml-2 text-xs text-blue-600">• {{ __('Baru') }}</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $n->pesan ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="toggleRead({{ $n->id }})" class="px-3 py-1.5 rounded-lg text-sm {{ $n->terbaca ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' : 'bg-blue-600 text-white' }}">
                                {{ $n->terbaca ? __('Tandai belum dibaca') : __('Tandai terbaca') }}
                            </button>
                            <a href="{{ route('notifikasi.edit', $n) }}" class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white text-sm">{{ __('Edit') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tidak ada notifikasi') }}</div>
                @endforelse
            </div>
            @if($notifications->hasPages())
                <div class="px-6 py-4">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</div>

