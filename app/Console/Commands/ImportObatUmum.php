<?php

namespace App\Console\Commands;

use App\Models\Obat;
use Illuminate\Console\Command;

class ImportObatUmum extends Command
{
    protected $signature = 'obat:import-umum {path? : path JSON} {--dry : tampilkan saja tanpa simpan}';
    protected $description = 'Impor data obat umum (LAPORAN) → tabel obat sebagai non_kronis (idempoten by nama+tipe)';

    private const SATUAN = ['Tablet' => 'box', 'Sirup' => 'botol', 'Salep/Tetes' => 'tube'];

    public function handle(): int
    {
        $path = $this->argument('path') ?: database_path('seeders/data/obat_umum_seed.json');
        if (!is_file($path)) {
            $this->error("File tidak ditemukan: $path");
            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (!is_array($rows)) {
            $this->error('JSON tidak valid.');
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry');
        $created = 0; $updated = 0; $skipped = 0;

        foreach ($rows as $r) {
            $nama = trim($r['nama'] ?? '');
            if ($nama === '') { $skipped++; continue; }

            $beli   = (float) ($r['beli'] ?? 0);
            $margin = (float) ($r['margin'] ?? 0.20);
            $jual   = (float) ($r['jual'] ?? 0) ?: round($beli * (1 + $margin), 0);
            $bentuk = $r['bentuk'] ?? 'Lainnya';
            $stok   = (int) ($r['stok'] ?? 0);

            $payload = [
                'nama_obat'           => $nama,
                'tipe_obat'           => 'non_kronis',
                'bentuk_sediaan'      => $bentuk,
                'satuan'              => self::SATUAN[$bentuk] ?? 'box',
                'harga_beli_per_unit' => $beli,
                'margin_umum'         => $margin,
                'harga_jual_per_unit' => $jual,
                'sumber_harga'        => 'REAL',
                'klaim_bpjs_per_unit' => 0,
                'stok_aktual'         => $stok,
                'stok_minimum'        => 5,
                'is_active'           => true,
            ];

            // Idempoten: cocokkan obat non_kronis dgn nama sama (tak menyentuh obat kronis BPJS).
            $obat = Obat::where('tipe_obat', 'non_kronis')
                ->whereRaw('LOWER(nama_obat) = ?', [mb_strtolower($nama)])
                ->first();

            if ($dry) {
                $this->line(sprintf('%s %-26s beli=%d margin=%.0f%% jual=%d stok=%d',
                    $obat ? 'UPDATE' : 'CREATE', mb_substr($nama, 0, 26), $beli, $margin * 100, $jual, $stok));
                continue;
            }

            if ($obat) { $obat->update($payload); $updated++; }
            else { Obat::create($payload); $created++; }
        }

        $this->info($dry
            ? 'Dry-run selesai.'
            : "Impor selesai — baru $created · update $updated · lewati $skipped (total " . count($rows) . ').');

        return self::SUCCESS;
    }
}
