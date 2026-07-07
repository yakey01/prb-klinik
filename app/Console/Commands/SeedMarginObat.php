<?php

namespace App\Console\Commands;

use App\Models\Obat;
use Illuminate\Console\Command;

/**
 * Seed margin keuntungan (margin_umum) per obat berdasarkan TIER harga beli,
 * lalu hitung ulang harga jual umum = beli × (1 + margin). Harga jual inilah
 * yang dipakai kasir di SIM (tersinkron via prb:sync-katalog).
 *
 * Tier default (bisa di-override via opsi):
 *   harga beli  < 500          → margin 35%
 *   harga beli  500 .. 1000    → margin 25%   (inklusif)
 *   harga beli  > 1000         → margin 20%
 *
 * Hanya obat AKTIF. Idempoten (boleh dijalankan ulang). Pakai --dry utk preview.
 */
class SeedMarginObat extends Command
{
    protected $signature = 'obat:seed-margin
        {--dry : preview saja, tidak menyimpan}
        {--low=35 : margin % utk harga beli < batas-bawah}
        {--mid=25 : margin % utk harga beli batas-bawah..batas-atas}
        {--high=20 : margin % utk harga beli > batas-atas}
        {--lower=500 : batas bawah (Rp)}
        {--upper=1000 : batas atas (Rp)}';

    protected $description = 'Seed margin keuntungan per obat berdasarkan tier harga beli → hitung ulang harga jual (kasir SIM)';

    public function handle(): int
    {
        $low   = (float) $this->option('low')  / 100;
        $mid   = (float) $this->option('mid')  / 100;
        $high  = (float) $this->option('high') / 100;
        $lower = (float) $this->option('lower');
        $upper = (float) $this->option('upper');
        $dry   = (bool) $this->option('dry');

        $this->info(sprintf(
            'Tier: <%s → %d%% · %s–%s → %d%% · >%s → %d%%   (obat aktif%s)',
            number_format($lower, 0, ',', '.'), $this->option('low'),
            number_format($lower, 0, ',', '.'), number_format($upper, 0, ',', '.'), $this->option('mid'),
            number_format($upper, 0, ',', '.'), $this->option('high'),
            $dry ? ' · DRY-RUN' : ''
        ));

        $counts = ['low' => 0, 'mid' => 0, 'high' => 0];
        $n = 0;

        foreach (Obat::where('is_active', true)->orderBy('nama_obat')->get() as $o) {
            $beli = (float) $o->harga_beli_per_unit;

            if ($beli < $lower)      { $margin = $low;  $tier = 'low'; }
            elseif ($beli <= $upper) { $margin = $mid;  $tier = 'mid'; }
            else                     { $margin = $high; $tier = 'high'; }

            $jual = round($beli * (1 + $margin), 0);
            $counts[$tier]++;
            $n++;

            if (!$dry) {
                $o->update(['margin_umum' => round($margin, 4), 'harga_jual_per_unit' => $jual]);
            }
        }

        $this->newLine();
        $this->line(sprintf(
            'Total %d obat → %s%d obat @%d%%%s · %d obat @%d%% · %d obat @%d%%',
            $n, '', $counts['low'], $this->option('low'), '',
            $counts['mid'], $this->option('mid'),
            $counts['high'], $this->option('high')
        ));
        $this->info($dry ? 'DRY-RUN selesai (tidak ada perubahan disimpan).' : 'Selesai. Harga jual diperbarui — jalankan sync ke SIM bila perlu.');

        return self::SUCCESS;
    }
}
