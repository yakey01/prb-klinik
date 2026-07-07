<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KalkulatorSimulasi extends Command
{
    protected $signature   = 'kalkulator:simulasi {--filter= : Filter kategori_diagnosis}';
    protected $description = 'Simulasi profitabilitas semua obat aktif — output JSON lines (untuk streaming WebSocket)';

    public function handle(): int
    {
        // Matikan output buffering agar setiap baris langsung dikirim
        while (ob_get_level() > 0) ob_end_clean();

        $filter = $this->option('filter');

        $query = DB::table('obat')
            ->where('is_active', true)
            ->orderBy('kategori_diagnosis')
            ->orderBy('nama_obat')
            ->select(
                'id', 'nama_obat', 'kode_obat', 'satuan', 'tipe_obat',
                'kategori_diagnosis', 'harga_beli_per_unit', 'klaim_bpjs_per_unit',
                'faktor_jasa_farmasi', 'unit_per_bulan', 'stok_aktual', 'stok_minimum'
            );

        if ($filter) {
            $query->where('kategori_diagnosis', $filter);
        }

        $obatList = $query->get();
        $total    = $obatList->count();

        $this->emit(['type' => 'meta', 'total' => $total, 'filter' => $filter ?: null]);

        $stats           = ['profit' => 0, 'loss' => 0, 'bep' => 0, 'skip' => 0];
        $totalPendapatan = 0;
        $totalBiaya      = 0;
        $diagnosisMap    = [];

        foreach ($obatList as $i => $obat) {
            $hargaBeli = (float) ($obat->harga_beli_per_unit  ?? 0);
            $klaim     = (float) ($obat->klaim_bpjs_per_unit  ?? 0);
            $faktorJf  = (float) ($obat->faktor_jasa_farmasi  ?? 0.15);
            $volume    = max(1, (int) ($obat->unit_per_bulan  ?? 30));
            $tipe      = $obat->tipe_obat ?? 'kronis';
            $diagnosa  = $obat->kategori_diagnosis ?? '—';

            $hasil = $this->hitung($hargaBeli, $klaim, $faktorJf, $volume, $tipe);

            if ($hasil['ready']) {
                $status = $hasil['status'];
                $stats[$status]++;
                $totalPendapatan += $hasil['pendapatan_bln'];
                $totalBiaya      += $hasil['biaya_bln'];

                // Akumulasi per diagnosis
                if (! isset($diagnosisMap[$diagnosa])) {
                    $diagnosisMap[$diagnosa] = ['profit' => 0, 'loss' => 0, 'laba_bln' => 0, 'count' => 0];
                }
                $diagnosisMap[$diagnosa][$status === 'profit' ? 'profit' : 'loss']++;
                $diagnosisMap[$diagnosa]['laba_bln'] += $hasil['laba_bln'];
                $diagnosisMap[$diagnosa]['count']++;
            } else {
                $stats['skip']++;
            }

            $this->emit([
                'type'    => 'obat',
                'index'   => $i + 1,
                'total'   => $total,
                'id'      => $obat->id,
                'nama'    => $obat->nama_obat,
                'kode'    => $obat->kode_obat ?? '',
                'satuan'  => $obat->satuan    ?? 'tablet',
                'tipe'    => $tipe,
                'diagnosa'=> $diagnosa,
                'volume'  => $volume,
                'stok'    => (int) ($obat->stok_aktual ?? 0),
                'minimum' => (int) ($obat->stok_minimum ?? 0),
                'hasil'   => $hasil,
            ]);

            usleep(25000); // 25ms — delay agar streaming terlihat satu per satu
        }

        $totalLaba = $totalPendapatan - $totalBiaya;

        $this->emit([
            'type'           => 'summary',
            'stats'          => $stats,
            'total_obat'     => $total,
            'pendapatan_bln' => $totalPendapatan,
            'biaya_bln'      => $totalBiaya,
            'laba_bln'       => $totalLaba,
            'laba_tahun'     => $totalLaba * 12,
            'margin'         => $totalPendapatan > 0
                                ? round($totalLaba / $totalPendapatan * 100, 1)
                                : 0,
            'per_diagnosis'  => array_map(fn($d, $v) => ['diagnosa' => $d, ...$v], array_keys($diagnosisMap), $diagnosisMap),
        ]);

        return self::SUCCESS;
    }

    private function emit(array $data): void
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        flush();
    }

    private function hitung(float $hargaBeli, float $klaim, float $faktorJf, int $volume, string $tipe): array
    {
        $faktorJf = max(0.01, $faktorJf);

        if ($hargaBeli <= 0 && $klaim <= 0) {
            return ['ready' => false];
        }

        $bayar         = $tipe === 'kronis' ? round($klaim * \App\Models\Obat::jfMultiplier($faktorJf)) : $klaim;
        $labaPerUnit   = $bayar - $hargaBeli;
        $pendapatanBln = $bayar * $volume;
        $biayaBln      = $hargaBeli * $volume;
        $labaBln       = $pendapatanBln - $biayaBln;
        $marginPersen  = $bayar > 0 ? round($labaPerUnit / $bayar * 100, 1) : 0;

        return [
            'ready'          => true,
            'status'         => $labaBln > 0 ? 'profit' : ($labaBln < 0 ? 'loss' : 'bep'),
            'bayar'          => $bayar,
            'laba_per_unit'  => $labaPerUnit,
            'pendapatan_bln' => $pendapatanBln,
            'biaya_bln'      => $biayaBln,
            'laba_bln'       => $labaBln,
            'margin_persen'  => $marginPersen,
            'harga_beli'     => $hargaBeli,
            'klaim'          => $klaim,
            'faktor_jf'      => $faktorJf,
            'volume'         => $volume,
            'tipe'           => $tipe,
        ];
    }
}
