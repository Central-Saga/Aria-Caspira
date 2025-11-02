<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function transaksi(Request $request)
    {
        $query = Transaksi::with(['baju','user']);

        if ($search = $request->string('search')->toString()) {
            $s = "%{$search}%";
            $query->where(function ($q) use ($s) {
                $q->where('keterangan', 'like', $s)
                  ->orWhereHas('baju', fn($b) => $b->where('nama_baju', 'like', $s));
            });
        }
        if (in_array($request->string('jenis')->toString(), ['masuk','keluar'])) {
            $query->where('jenis_transaksi', $request->string('jenis')->toString());
        }

        $period = $request->string('period')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();
        if (in_array($period, ['hari','minggu','bulan','tahun'])) {
            $start = match($period) {
                'hari' => now()->startOfDay(),
                'minggu' => now()->startOfWeek(),
                'bulan' => now()->startOfMonth(),
                'tahun' => now()->startOfYear(),
                default => null,
            };
            if ($start) {
                $query->whereBetween('tanggal', [$start, now()]);
            }
        } elseif ($from || $to) {
            $fromDate = $from ? \Carbon\Carbon::parse($from)->startOfDay() : Transaksi::min('tanggal');
            $toDate = $to ? \Carbon\Carbon::parse($to)->endOfDay() : now();
            $query->whereBetween('tanggal', [$fromDate, $toDate]);
        }

        $items = $query->orderBy('tanggal')->get();

        $totMasuk = (int) $items->where('jenis_transaksi','masuk')->sum('jumlah');
        $totKeluar = (int) $items->where('jenis_transaksi','keluar')->sum('jumlah');

        return view('reports.transaksi-print', [
            'items' => $items,
            'filters' => [
                'search' => $search ?? '',
                'jenis' => $request->string('jenis')->toString(),
                'period' => $period,
                'from' => $from,
                'to' => $to,
            ],
            'totMasuk' => $totMasuk,
            'totKeluar' => $totKeluar,
        ]);
    }
}

