<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        :root{--emerald:#10b981;--teal:#14b8a6;--red:#ef4444;--zinc:#0b1220;}
        *{box-sizing:border-box} body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue","Noto Sans","Apple Color Emoji","Segoe UI Emoji";margin:0;background:#f8fafc;color:#0f172a}
        .container{max-width:1000px;margin:24px auto;padding:0 24px}
        .header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px}
        .title{font-size:28px;margin:0;background:linear-gradient(90deg,var(--emerald),var(--teal));-webkit-background-clip:text;background-clip:text;color:transparent}
        .meta{color:#475569;font-size:12px;margin-top:6px}
        .badge{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#ecfeff;color:#0e7490;font-weight:600;font-size:12px}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 6px 18px rgba(2,6,23,.06);overflow:hidden}
        .card h3{margin:0 0 12px 0}
        .filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
        .pill{background:#f1f5f9;color:#0f172a;border-radius:999px;padding:6px 12px;font-size:12px}
        table{width:100%;border-collapse:collapse;font-size:12px}
        thead th{background:#f1f5f9;text-align:left;color:#475569;padding:10px;border-bottom:1px solid #e2e8f0}
        tbody td{padding:10px;border-bottom:1px solid #eef2f7}
        .jumlah.plus{color:#059669;font-weight:700}
        .jumlah.minus{color:#dc2626;font-weight:700}
        .footer{display:flex;justify-content:flex-end;gap:16px;margin-top:14px}
        .stat{padding:10px 14px;border-radius:12px;color:#fff}
        .stat.plus{background:linear-gradient(90deg,#34d399,#10b981)}
        .stat.minus{background:linear-gradient(90deg,#f87171,#ef4444)}
        @media print{.no-print{display:none!important} body{background:white} .card{box-shadow:none;border:1px solid #e2e8f0}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="title">Laporan Transaksi Stok</h1>
                <div class="meta">Dicetak: {{ now()->format('d M Y H:i') }}</div>
            </div>
            <div class="badge">Inventori • Aria Caspira</div>
        </div>

        <div class="filters">
            @if(($filters['period'] ?? '') !== '')
                <span class="pill">Periode: {{ ucfirst($filters['period']) }}</span>
            @endif
            @if(($filters['from'] ?? '') || ($filters['to'] ?? ''))
                <span class="pill">Rentang: {{ $filters['from'] ?: '—' }} → {{ $filters['to'] ?: '—' }}</span>
            @endif
            @if(($filters['jenis'] ?? '') !== '')
                <span class="pill">Jenis: {{ ucfirst($filters['jenis']) }}</span>
            @endif
            @if(($filters['search'] ?? '') !== '')
                <span class="pill">Cari: “{{ $filters['search'] }}”</span>
            @endif
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="width:140px">Tanggal</th>
                        <th>Produk</th>
                        <th>Jenis</th>
                        <th style="width:100px">Jumlah</th>
                        <th>Pencatat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $t)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y H:i') }}</td>
                        <td>{{ $t->baju?->nama_baju }}</td>
                        <td>{{ ucfirst($t->jenis_transaksi) }}</td>
                        <td class="jumlah {{ $t->jenis_transaksi==='masuk' ? 'plus' : 'minus' }}">{{ $t->jenis_transaksi==='masuk' ? '+' : '-' }}{{ $t->jumlah }}</td>
                        <td>{{ $t->user?->name }}</td>
                        <td>{{ $t->keterangan ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:#64748b;padding:20px">Tidak ada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div class="stat plus">Total Masuk: +{{ number_format($totMasuk) }}</div>
            <div class="stat minus">Total Keluar: -{{ number_format($totKeluar) }}</div>
        </div>

        <div class="no-print" style="margin-top:16px;display:flex;justify-content:space-between;gap:10px">
            <a href="{{ route('inventory.transaksi') }}" style="padding:10px 16px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#0f172a;font-weight:600;text-decoration:none">← Kembali ke Transaksi</a>
            <button onclick="window.print()" style="padding:10px 16px;border-radius:10px;border:0;background:linear-gradient(90deg,#2563eb,#7c3aed);color:white;font-weight:600;cursor:pointer">Cetak / Simpan PDF</button>
        </div>
    </div>
</body>
</html>
