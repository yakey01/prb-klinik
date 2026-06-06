<?php

namespace App\Http\Controllers;

use App\Services\LabaCalculatorService;
use App\Models\Obat;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private LabaCalculatorService $calc) {}

    public function index()
    {
        $data = $this->calc->getDashboardData();

        $pengeluaranBulanIni = PurchaseOrder::whereMonth('tanggal_po', now()->month)
            ->whereYear('tanggal_po', now()->year)
            ->sum('total_nilai');

        $jumlahPoBulanIni = PurchaseOrder::whereMonth('tanggal_po', now()->month)
            ->whereYear('tanggal_po', now()->year)
            ->count();

        // Alert counts
        $obatAktif = Obat::where('is_active', true)->get();
        $alerts = [
            'rugi'       => $obatAktif->filter(fn ($o) => $o->laba < 0)->count(),
            'stok_habis' => $obatAktif->filter(fn ($o) => $o->stok_aktual <= 0)->count(),
            'stok_kritis'=> $obatAktif->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->count(),
            'kadaluarsa' => $obatAktif->filter(fn ($o) => in_array($o->kadaluarsa_status, ['kadaluarsa', 'segera']))->count(),
        ];

        // 6-month trend
        $trenLabels    = [];
        $trenPendapatan= [];
        $trenPengeluaran=[];
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $trenLabels[]     = $dt->translatedFormat('M Y');
            $trenPendapatan[] = round($this->calc->getPendapatanBulanIni());
            $trenPengeluaran[]= round(
                PurchaseOrder::whereMonth('tanggal_po', $dt->month)
                    ->whereYear('tanggal_po', $dt->year)
                    ->sum('total_nilai')
            );
        }

        return view('dashboard.index', array_merge($data, [
            'pengeluaran_bulan_ini' => round($pengeluaranBulanIni),
            'jumlah_po_bulan_ini'   => $jumlahPoBulanIni,
            'alerts'                => $alerts,
            'tren_labels'           => $trenLabels,
            'tren_pendapatan'       => $trenPendapatan,
            'tren_pengeluaran'      => $trenPengeluaran,
        ]));
    }
}
