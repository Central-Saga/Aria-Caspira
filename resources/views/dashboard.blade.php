<x-layouts.app :title="__('Dashboard')">
    @php
        // KPI statics
        $totalProduk = \App\Models\Baju::count();
        $totalStok = (int) \App\Models\Baju::sum('stok_tersedia');
        $nilaiPersediaan = (float) (\App\Models\Baju::selectRaw('SUM(harga * stok_tersedia) as total')->value('total') ?? 0);

        // KPI period (default: minggu)
        $kpiPeriod = request('kpi_period', 'minggu');
        $kpiStart = match($kpiPeriod) {
            'hari' => now()->startOfDay(),
            'minggu' => now()->startOfWeek(),
            'bulan' => now()->startOfMonth(),
            'tahun' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };
        $kpiEnd = now();
        $mingguMasuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereBetween('tanggal', [$kpiStart, $kpiEnd])->sum('jumlah');
        $mingguKeluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereBetween('tanggal', [$kpiStart, $kpiEnd])->sum('jumlah');

        // Trend per-card filter
        $trendDays = (int) request('trend_days', 7);
        if (!in_array($trendDays, [7,14,30])) { $trendDays = 7; }
        $trend = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $day = \Carbon\Carbon::today()->subDays($i);
            $masuk = (int) \App\Models\Transaksi::where('jenis_transaksi','masuk')->whereDate('tanggal', $day)->sum('jumlah');
            $keluar = (int) \App\Models\Transaksi::where('jenis_transaksi','keluar')->whereDate('tanggal', $day)->sum('jumlah');
            $trend[] = ['label' => $day->format('d M'), 'net' => $masuk - $keluar];
        }
        $maxAbs = max(1, collect($trend)->map(fn($t) => abs($t['net']))->max() ?? 1);
        $barWidthClass = $trendDays <= 14 ? 'w-6' : 'w-3';
        $gapClass = $trendDays <= 14 ? 'gap-3' : 'gap-1';
        $labelStep = $trendDays <= 14 ? 1 : 3;

        // Low stock per-card sort
        $lsSort = request('ls_sort','asc');
        $lowStocks = \App\Models\Baju::orderBy('stok_tersedia', $lsSort === 'desc' ? 'desc' : 'asc')->limit(6)->get();

        // Recent transaksi per-card filters
        $rtJenis = request('rt_jenis','');
        $rtSort = request('rt_sort','desc');
        $rtFrom = request('rt_from');
        $rtTo = request('rt_to');
        if ($rtFrom || $rtTo) {
            $rtStart = $rtFrom ? \Carbon\Carbon::parse($rtFrom)->startOfDay() : now()->subDays(6)->startOfDay();
            $rtEnd = $rtTo ? \Carbon\Carbon::parse($rtTo)->endOfDay() : now();
        } else {
            // default 7 hari terakhir agar relevan
            $rtStart = now()->subDays(6)->startOfDay();
            $rtEnd = now();
        }
        $recent = \App\Models\Transaksi::with(['baju','user'])
            ->when(in_array($rtJenis, ['masuk','keluar']), fn($q) => $q->where('jenis_transaksi', $rtJenis))
            ->whereBetween('tanggal', [$rtStart, $rtEnd])
            ->orderBy('tanggal', $rtSort === 'asc' ? 'asc' : 'desc')
            ->limit(8)
            ->get();

        $unreadNotif = \App\Models\NotifikasiStok::where('terbaca', false)->count();
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Dashboard') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('Ringkasan inventori dan aktivitas terbaru') }}</p>
            </div>

            <!-- Per-card filters: global form dihapus -->

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
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Periode (±)') }}</p>
                            <p class="mt-2 text-3xl font-bold">
                                <span class="text-emerald-600">+{{ number_format($mingguMasuk) }}</span>
                                <span class="text-gray-400">/</span>
                                <span class="text-red-600">-{{ number_format($mingguKeluar) }}</span>
                            </p>
                        </div>
                        <form method="GET" class="ml-2">
                            @foreach(request()->except(['kpi_period']) as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach
                            <select name="kpi_period" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                <option value="hari" {{ $kpiPeriod==='hari' ? 'selected' : '' }}>{{ __('Hari') }}</option>
                                <option value="minggu" {{ $kpiPeriod==='minggu' ? 'selected' : '' }}>{{ __('Minggu') }}</option>
                                <option value="bulan" {{ $kpiPeriod==='bulan' ? 'selected' : '' }}>{{ __('Bulan') }}</option>
                                <option value="tahun" {{ $kpiPeriod==='tahun' ? 'selected' : '' }}>{{ __('Tahun') }}</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Trend + Notif -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Pergerakan Stok') }}</h3>
                        <div class="flex items-center gap-2">
                            <form method="GET" class="flex items-center gap-2">
                                @foreach(request()->except(['trend_days']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <select name="trend_days" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                    <option value="7" {{ $trendDays===7 ? 'selected' : '' }}>{{ __('7 Hari') }}</option>
                                    <option value="14" {{ $trendDays===14 ? 'selected' : '' }}>{{ __('14 Hari') }}</option>
                                    <option value="30" {{ $trendDays===30 ? 'selected' : '' }}>{{ __('30 Hari') }}</option>
                                </select>
                            </form>
                            <span class="text-xs text-gray-500">{{ now()->subDays($trendDays-1)->format('d M') }} - {{ now()->format('d M') }}</span>
                        </div>
                    </div>
                    <div class="flex items-end {{ $gapClass }} h-40">
                        @foreach($trend as $i => $t)
                            <?php $h = max(4, round((abs($t['net']) / $maxAbs) * 140)); $pos = $t['net'] >= 0; ?>
                            <div class="flex flex-col items-center justify-end w-full">
                                <div class="{{ $barWidthClass }} rounded-t-md {{ $pos ? 'bg-emerald-500' : 'bg-red-500' }}" style="height: {{ $h }}px"></div>
                                @if($labelStep === 1 || $i % $labelStep === 0)
                                    <div class="mt-2 text-[10px] text-gray-600 dark:text-gray-300">{{ $t['label'] }}</div>
                                @else
                                    <div class="mt-2 text-[10px] text-transparent select-none">{{ $t['label'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Notifikasi Stok') }}</h3>
                        <a href="{{ route('inventory.notifications') }}" class="text-xs text-emerald-600 hover:underline">{{ __('Lihat semua') }}</a>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mb-1">{{ __('Belum terbaca') }}</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $unreadNotif }}</p>
                    <p class="mt-1 text-[11px] text-gray-500">{{ __('Jumlah peringatan stok menipis/critical') }}</p>
                </div>
            </div>

            <!-- Low stock + Recent transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 lg:col-span-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Stok Rendah') }}</h3>
                        <div class="flex items-center gap-2">
                            <form method="GET">
                                @foreach(request()->except(['ls_sort']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <select name="ls_sort" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                    <option value="asc" {{ $lsSort==='asc' ? 'selected' : '' }}>{{ __('Terkecil → Terbesar') }}</option>
                                    <option value="desc" {{ $lsSort==='desc' ? 'selected' : '' }}>{{ __('Terbesar → Terkecil') }}</option>
                                </select>
                            </form>
                            <a href="{{ route('inventory.baju') }}" class="text-sm text-emerald-600 hover:underline">{{ __('Kelola') }}</a>
                        </div>
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
                        <div class="flex items-center gap-2">
                            <form method="GET" class="flex items-center gap-2">
                                @foreach(request()->except(['rt_from','rt_to','rt_sort','rt_jenis']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <select name="rt_jenis" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                    <option value="" {{ $rtJenis==='' ? 'selected' : '' }}>{{ __('Semua') }}</option>
                                    <option value="masuk" {{ $rtJenis==='masuk' ? 'selected' : '' }}>{{ __('Masuk') }}</option>
                                    <option value="keluar" {{ $rtJenis==='keluar' ? 'selected' : '' }}>{{ __('Keluar') }}</option>
                                </select>
                                <select name="rt_sort" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                    <option value="desc" {{ $rtSort==='desc' ? 'selected' : '' }}>{{ __('Terbaru') }}</option>
                                    <option value="asc" {{ $rtSort==='asc' ? 'selected' : '' }}>{{ __('Terlama') }}</option>
                                </select>
                                <input type="date" name="rt_from" value="{{ $rtFrom }}" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                                <span class="text-xs text-gray-500">-</span>
                                <input type="date" name="rt_to" value="{{ $rtTo }}" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200" onchange="this.form.submit()">
                            </form>
                            <a href="{{ route('inventory.transaksi') }}" class="text-sm text-emerald-600 hover:underline">{{ __('Lihat semua') }}</a>
                        </div>
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
