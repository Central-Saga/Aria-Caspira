<x-layouts.app :title="__('Dashboard')">
    @php
        $totalProduk = \App\Models\Baju::count();
        $totalStok = (int) \App\Models\Baju::sum('stok_tersedia');
        $nilaiPersediaan = (float) (\App\Models\Baju::selectRaw('SUM(harga * stok_tersedia) as total')->value('total') ?? 0);

        $mingguMasuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereBetween('tanggal', [now()->startOfWeek(), now()])->sum('jumlah');
        $mingguKeluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereBetween('tanggal', [now()->startOfWeek(), now()])->sum('jumlah');

        // Data 7 hari terakhir (net: masuk - keluar)
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = \Carbon\Carbon::today()->subDays($i);
            $masuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereDate('tanggal', $day)->sum('jumlah');
            $keluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereDate('tanggal', $day)->sum('jumlah');
            $net = $masuk - $keluar;
            $trend[] = ['label' => $day->format('d M'), 'net' => $net];
        }
        $maxAbs = max(1, collect($trend)->map(fn($t) => abs($t['net']))->max() ?? 1);

        $lowStocks = \App\Models\Baju::orderBy('stok_tersedia')->limit(6)->get();
        $recent = \App\Models\Transaksi::with(['baju','user'])->orderByDesc('tanggal')->limit(8)->get();
        $unreadNotif = \App\Models\NotifikasiStok::where('terbaca', false)->count();
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Dashboard') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('Ringkasan inventori dan aktivitas terbaru') }}</p>
            </div>

            <!-- Global Filters -->
            @php
                $periodQ = request('period','');
                $fromQ = request('from');
                $toQ = request('to');
                $jenisQ = request('jenis','');
                $tSortQ = request('t_sort','desc');
                $stockSortQ = request('stock_sort','asc');

                // Range untuk kalkulasi transaksi
                if (in_array($periodQ, ['hari','minggu','bulan','tahun'])) {
                    $startRange = match($periodQ) {
                        'hari' => now()->startOfDay(),
                        'minggu' => now()->startOfWeek(),
                        'bulan' => now()->startOfMonth(),
                        'tahun' => now()->startOfYear(),
                        default => now()->startOfWeek(),
                    };
                    $endRange = now();
                } elseif ($fromQ || $toQ) {
                    $startRange = $fromQ ? \Carbon\Carbon::parse($fromQ)->startOfDay() : now()->subDays(6)->startOfDay();
                    $endRange = $toQ ? \Carbon\Carbon::parse($toQ)->endOfDay() : now();
                } else {
                    $startRange = now()->startOfWeek();
                    $endRange = now();
                }

                // Hitung ulang KPI transaksi sesuai filter
                $mingguMasuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereBetween('tanggal', [$startRange, $endRange])->sum('jumlah');
                $mingguKeluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereBetween('tanggal', [$startRange, $endRange])->sum('jumlah');

                // Trend maks 14 hari mundur dari endRange atau startRange
                $daysWindow = min(14, max(1, $startRange->diffInDays($endRange) + 1));
                $trend = [];
                for ($i = $daysWindow - 1; $i >= 0; $i--) {
                    $day = (clone $endRange)->startOfDay()->subDays($i);
                    $masuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereDate('tanggal', $day)->sum('jumlah');
                    $keluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereDate('tanggal', $day)->sum('jumlah');
                    $trend[] = ['label' => $day->format('d M'), 'net' => $masuk - $keluar];
                }
                $maxAbs = max(1, collect($trend)->map(fn($t) => abs($t['net']))->max() ?? 1);

                // Low stock & recent menggunakan sort dan filter
                $lowStocks = \App\Models\Baju::orderBy('stok_tersedia', $stockSortQ === 'desc' ? 'desc' : 'asc')->limit(6)->get();
                $recent = \App\Models\Transaksi::with(['baju','user'])
                    ->when(in_array($jenisQ, ['masuk','keluar']), fn($q) => $q->where('jenis_transaksi', $jenisQ))
                    ->whereBetween('tanggal', [$startRange, $endRange])
                    ->orderBy('tanggal', $tSortQ === 'asc' ? 'asc' : 'desc')
                    ->limit(8)
                    ->get();
            @endphp

            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-8">
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Periode') }}</label>
                    <select name="period" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        <option value="" {{ $periodQ==='' ? 'selected' : '' }}>{{ __('(Custom / Default)') }}</option>
                        <option value="hari" {{ $periodQ==='hari' ? 'selected' : '' }}>{{ __('Hari ini') }}</option>
                        <option value="minggu" {{ $periodQ==='minggu' ? 'selected' : '' }}>{{ __('Minggu ini') }}</option>
                        <option value="bulan" {{ $periodQ==='bulan' ? 'selected' : '' }}>{{ __('Bulan ini') }}</option>
                        <option value="tahun" {{ $periodQ==='tahun' ? 'selected' : '' }}>{{ __('Tahun ini') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Dari Tanggal') }}</label>
                    <input name="from" type="date" value="{{ $fromQ }}" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Sampai Tanggal') }}</label>
                    <input name="to" type="date" value="{{ $toQ }}" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
                </div>
                <div class="grid grid-cols-2 gap-3 md:col-span-1">
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Jenis Transaksi') }}</label>
                        <select name="jenis" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="" {{ $jenisQ==='' ? 'selected' : '' }}>{{ __('Semua') }}</option>
                            <option value="masuk" {{ $jenisQ==='masuk' ? 'selected' : '' }}>{{ __('Masuk') }}</option>
                            <option value="keluar" {{ $jenisQ==='keluar' ? 'selected' : '' }}>{{ __('Keluar') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Urut Transaksi') }}</label>
                        <select name="t_sort" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="desc" {{ $tSortQ==='desc' ? 'selected' : '' }}>{{ __('Terbaru') }}</option>
                            <option value="asc" {{ $tSortQ==='asc' ? 'selected' : '' }}>{{ __('Terlama') }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 md:col-span-4">
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Urut Stok Rendah') }}</label>
                        <select name="stock_sort" class="mt-1 w-full max-w-xs px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="asc" {{ $stockSortQ==='asc' ? 'selected' : '' }}>{{ __('Terkecil → Terbesar') }}</option>
                            <option value="desc" {{ $stockSortQ==='desc' ? 'selected' : '' }}>{{ __('Terbesar → Terkecil') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">{{ __('Reset') }}</a>
                        <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold shadow">{{ __('Terapkan') }}</button>
                    </div>
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Produk') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalProduk) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Stok') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalStok) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nilai Persediaan') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Minggu Ini (±)') }}</p>
                    <p class="mt-2 text-3xl font-bold">
                        <span class="text-emerald-600">+{{ number_format($mingguMasuk) }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="text-red-600">-{{ number_format($mingguKeluar) }}</span>
                    </p>
                </div>
            </div>

            <!-- Trend + Notif -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Pergerakan Stok 7 Hari Terakhir') }}</h3>
                        <span class="text-xs text-gray-500">{{ now()->subDays(6)->format('d M') }} - {{ now()->format('d M') }}</span>
                    </div>
                    <div class="flex items-end gap-3 h-40">
                        @foreach($trend as $t)
                            <?php $h = max(4, round((abs($t['net']) / $maxAbs) * 140)); $pos = $t['net'] >= 0; ?>
                            <div class="flex flex-col items-center justify-end w-full">
                                <div class="w-6 rounded-t-md {{ $pos ? 'bg-emerald-500' : 'bg-red-500' }}" style="height: {{ $h }}px"></div>
                                <div class="mt-2 text-[11px] text-gray-600 dark:text-gray-300">{{ $t['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Notifikasi Stok') }}</h3>
                        <a href="{{ route('inventory.notifications') }}" class="text-sm text-emerald-600 hover:underline">{{ __('Lihat semua') }}</a>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ __('Belum terbaca') }}</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $unreadNotif }}</p>
                    <p class="mt-2 text-xs text-gray-500">{{ __('Jumlah peringatan stok menipis/critical') }}</p>
                </div>
            </div>

            <!-- Low stock + Recent transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 lg:col-span-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Stok Rendah') }}</h3>
                        <a href="{{ route('inventory.baju') }}" class="text-sm text-emerald-600 hover:underline">{{ __('Kelola') }}</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($lowStocks as $b)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $b->nama_baju }}</p>
                                    <p class="text-xs text-gray-500">{{ $b->kategori?->nama_kategori ?? '—' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $b->stok_tersedia < 10 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">{{ $b->stok_tersedia }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('Tidak ada data') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Transaksi Terbaru') }}</h3>
                        <a href="{{ route('inventory.transaksi') }}" class="text-sm text-emerald-600 hover:underline">{{ __('Lihat semua') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-xs text-gray-500">
                                    <th class="py-2 text-left">{{ __('Tanggal') }}</th>
                                    <th class="py-2 text-left">{{ __('Baju') }}</th>
                                    <th class="py-2 text-left">{{ __('Jenis') }}</th>
                                    <th class="py-2 text-left">{{ __('Jumlah') }}</th>
                                    <th class="py-2 text-left">{{ __('Pencatat') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($recent as $t)
                                    <tr class="text-sm">
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $t->tanggal?->format('d M Y H:i') }}</td>
                                        <td class="py-2 font-medium text-gray-900 dark:text-white">{{ $t->baju?->nama_baju }}</td>
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $t->jenis_transaksi === 'masuk' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">{{ ucfirst($t->jenis_transaksi) }}</span>
                                        </td>
                                        <td class="py-2 {{ $t->jenis_transaksi==='masuk' ? 'text-emerald-600' : 'text-red-600' }}">{{ $t->jenis_transaksi==='masuk' ? '+' : '-' }}{{ $t->jumlah }}</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $t->user?->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-6 text-center text-gray-500">{{ __('Tidak ada transaksi') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
