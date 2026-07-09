<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Rentang periode bulanan yang SARGABLE — pengganti whereYear()+whereMonth()
 * (yang membungkus kolom dalam fungsi → tak bisa pakai indeks tanggal).
 * whereBetween(col, Periode::bulan($y,$m)) memakai indeks pada kolom tanggal.
 */
class Periode
{
    /** [awal, akhir] datetime 1 bulan. Benar untuk kolom DATE maupun DATETIME. */
    public static function bulan(int $tahun, int $bulan): array
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        return [$start->toDateTimeString(), $start->copy()->endOfMonth()->toDateTimeString()];
    }
}
