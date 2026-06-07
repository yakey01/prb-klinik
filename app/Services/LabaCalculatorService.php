<?php

namespace App\Services;

use App\Models\Obat;
use App\Models\BiayaOperasional;
use Illuminate\Support\Collection;

class LabaCalculatorService
{
    public function getDashboardData(): array
    {
        $obatList = Obat::where('is_active', true)->get();
        $biaya = BiayaOperasional::currentMonth();

        $labaKotor = $obatList->sum(fn ($o) => $o->laba);
        $pendapatanBpjs = $obatList->sum(fn ($o) => $o->pendapatan_bulan);
        $biayaBeli = $obatList->sum(fn ($o) => $o->biaya_bulan);
        $totalPasien = $obatList->sum('jumlah_pasien');
        $totalBiayaOps = $biaya->total;
        $labasBersih = $labaKotor - $totalBiayaOps;

        $byDiagnosis = $obatList->groupBy('kategori_diagnosis')
            ->filter(fn ($items, $key) => !is_null($key) && $key !== '')
            ->map(fn ($items) => $items->sum(fn ($o) => $o->pendapatan_bulan))
            ->filter(fn ($v) => $v > 0)
            ->sortByDesc(fn ($v) => $v);

        $rankingObat = $obatList
            ->filter(fn ($o) => $o->laba != 0)
            ->sortByDesc(fn ($o) => $o->laba)
            ->values()
            ->map(fn ($o) => [
                'id'    => $o->id,
                'nama'  => $o->nama_obat,
                'laba'  => round($o->laba),
                'status'=> $o->status_laba,
            ]);

        return [
            'laba_kotor'        => round($labaKotor),
            'pendapatan_bpjs'   => round($pendapatanBpjs),
            'biaya_beli'        => round($biayaBeli),
            'total_pasien'      => $totalPasien,
            'total_biaya_ops'   => round($totalBiayaOps),
            'laba_bersih'       => round($labasBersih),
            'by_diagnosis'      => $byDiagnosis->toArray(),
            'ranking_obat'      => $rankingObat->toArray(),
            'biaya'             => $biaya,
            'jumlah_obat_aktif' => $obatList->count(),
        ];
    }

    public function getPendapatanBulanIni(): float
    {
        return Obat::where('is_active', true)->get()
            ->sum(fn ($o) => $o->pendapatan_bulan);
    }
}
