<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Transaksi;
use App\Models\Baju;

new class extends Component {
    use WithPagination;

    #[Layout('components.layouts.app')]

    public string $search = '';
    public string $filterJenis = '';
    public string $period = ''; // '', 'hari','minggu','bulan','tahun'
    public ?string $fromDate = null; // Y-m-d
    public ?string $toDate = null;   // Y-m-d

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterJenis(): void { $this->resetPage(); }
    public function updatingPeriod(): void { $this->resetPage(); $this->fromDate = $this->toDate = null; }
    public function updatedFromDate(): void { $this->period = ''; $this->resetPage(); }
    public function updatedToDate(): void { $this->period = ''; $this->resetPage(); }

    public function with(): array
    {
        $query = Transaksi::with(['baju', 'user']);

        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('keterangan', 'like', $s)
                  ->orWhereHas('baju', fn($b) => $b->where('nama_baju', 'like', $s));
            });
        }
        if (in_array($this->filterJenis, ['masuk','keluar'])) {
            $query->where('jenis_transaksi', $this->filterJenis);
        }

        // Filter tanggal berdasarkan period atau rentang tanggal
        if (in_array($this->period, ['hari','minggu','bulan','tahun'])) {
            $start = match($this->period) {
                'hari' => now()->startOfDay(),
                'minggu' => now()->startOfWeek(),
                'bulan' => now()->startOfMonth(),
                'tahun' => now()->startOfYear(),
                default => null,
            };
            if ($start) {
                $query->whereBetween('tanggal', [$start, now()]);
            }
        } elseif ($this->fromDate || $this->toDate) {
            $from = $this->fromDate ? \Carbon\Carbon::parse($this->fromDate)->startOfDay() : Transaksi::min('tanggal');
            $to = $this->toDate ? \Carbon\Carbon::parse($this->toDate)->endOfDay() : now();
            if ($from && $to) {
                $query->whereBetween('tanggal', [$from, $to]);
            }
        }

        $totalMasuk = (int) Transaksi::where('jenis_transaksi','masuk')->sum('jumlah');
        $totalKeluar = (int) Transaksi::where('jenis_transaksi','keluar')->sum('jumlah');

        return [
            'items' => $query->orderByDesc('tanggal')->paginate(10),
            'stats' => [
                'totalTransaksi' => Transaksi::count(),
                'totalMasuk' => $totalMasuk,
                'totalKeluar' => $totalKeluar,
            ],
            'printUrl' => route('transaksi.print', [
                'search' => $this->search,
                'jenis' => $this->filterJenis,
                'period' => $this->period,
                'from' => $this->fromDate,
                'to' => $this->toDate,
            ]),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Transaksi Stok') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Catatan keluar/masuk stok untuk setiap baju') }}</p>
                </div>
                <a href="{{ route('transaksi.create') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Tambah Transaksi') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Transaksi') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['totalTransaksi'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Masuk') }}</p>
                <p class="text-2xl font-bold text-emerald-600 mt-2">+{{ number_format($stats['totalMasuk']) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Keluar') }}</p>
                <p class="text-2xl font-bold text-red-600 mt-2">-{{ number_format($stats['totalKeluar']) }}</p>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    
                </span>
                <input
                    wire:model.live="search"
                    type="text"
                    class="block w-full pl-11 pr-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-lg"
                    placeholder="{{ __('Cari keterangan atau nama baju...') }}"
                />
            </div>
            <div>
                <select wire:model.live="filterJenis" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Jenis') }}</option>
                    <option value="masuk">{{ __('Masuk') }}</option>
                    <option value="keluar">{{ __('Keluar') }}</option>
                </select>
            </div>
            <div class="grid grid-cols-1 gap-3">
                <div class="flex items-center gap-2">
                    <select wire:model.live="period" class="flex-1 px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg {{ ($fromDate||$toDate) ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($fromDate || $toDate)
                        <option value="">{{ __('Pilih Periode') }}</option>
                        <option value="hari">{{ __('Hari ini') }}</option>
                        <option value="minggu">{{ __('Minggu ini') }}</option>
                        <option value="bulan">{{ __('Bulan ini') }}</option>
                        <option value="tahun">{{ __('Tahun ini') }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input wire:model.live="fromDate" type="date" class="px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg {{ $period ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($period !== '')>
                    <input wire:model.live="toDate" type="date" class="px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg {{ $period ? 'opacity-50 cursor-not-allowed' : '' }}" @disabled($period !== '')>
                </div>
            </div>
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                @if($period)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('Periode') }}: {{ ucfirst($period) }}</span>
                @elseif($fromDate || $toDate)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('Rentang') }}: {{ $fromDate ?? '—' }} → {{ $toDate ?? '—' }}</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ __('Tanpa filter tanggal') }}</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ $printUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow-lg hover:shadow-xl transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H5a2 2 0 01-2-2v-5h18v5a2 2 0 01-2 2h-1m-12 0h12v4H6v-4z"/></svg>
                    {{ __('Cetak Laporan (PDF)') }}
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Tanggal') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Baju') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Jenis') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Jumlah') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Pencatat') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Keterangan') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $t)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $t->tanggal?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $t->baju?->nama_baju }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $t->jenis_transaksi === 'masuk' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                        {{ ucfirst($t->jenis_transaksi) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $t->jenis_transaksi==='masuk' ? 'text-emerald-600' : 'text-red-600' }}">{{ $t->jenis_transaksi==='masuk' ? '+' : '-' }}{{ $t->jumlah }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $t->user?->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $t->keterangan ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('transaksi.edit', $t) }}" class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white">{{ __('Edit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Belum ada transaksi') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="px-6 py-4">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</div>
