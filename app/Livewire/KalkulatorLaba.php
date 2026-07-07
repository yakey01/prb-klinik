<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class KalkulatorLaba extends Component
{
    // Input form — draft obat yang sedang diisi
    public string $searchA    = '';
    public ?int   $obatIdA    = null;
    public string $satuanA    = 'tablet';
    public string $tipeA      = 'kronis';
    public string $hargaBeliA = '';
    public string $klaimA     = '';
    public string $faktorJfA  = '0.15';
    public string $volumeA    = '30';

    // Daftar obat yang sudah ditambahkan
    public array $items = [];

    // Tampilkan hasil kalkulasi agregat
    public bool $showKalkulasi = false;

    const SATUAN = [
        'tablet'       => 'Tablet',
        'kapsul'       => 'Kapsul',
        'kaplet'       => 'Kaplet',
        'strip'        => 'Strip',
        'sachet'       => 'Sachet',
        'botol'        => 'Botol',
        'sirup'        => 'Sirup/Botol',
        'fl'           => 'Flakon (fl)',
        'ampul'        => 'Ampul',
        'vial'         => 'Vial',
        'tube'         => 'Tube',
        'suppositoria' => 'Suppositoria',
        'inhaler'      => 'Inhaler',
        'patch'        => 'Patch',
        'syringe'      => 'Syringe',
        'lainnya'      => 'Lainnya',
    ];

    #[Computed]
    public function cariObatA(): array
    {
        if (mb_strlen(trim($this->searchA)) < 2) return [];
        return DB::table('obat')
            ->where('is_active', true)
            ->where('nama_obat', 'like', '%' . $this->searchA . '%')
            ->select('id', 'nama_obat', 'satuan', 'tipe_obat',
                     'harga_beli_per_unit', 'klaim_bpjs_per_unit',
                     'faktor_jasa_farmasi', 'unit_per_bulan', 'kategori_diagnosis')
            ->orderBy('nama_obat')
            ->limit(8)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    public function pilihObatA(int $id): void
    {
        $obat = DB::table('obat')->where('id', $id)->first();
        if (! $obat) return;

        $this->obatIdA    = $obat->id;
        $this->searchA    = $obat->nama_obat;
        $this->satuanA    = $obat->satuan ?? 'tablet';
        $this->tipeA      = $obat->tipe_obat ?? 'kronis';
        $this->hargaBeliA = (string) ($obat->harga_beli_per_unit ?? '');
        $this->klaimA     = (string) ($obat->klaim_bpjs_per_unit ?? '');
        $this->faktorJfA  = (string) ($obat->faktor_jasa_farmasi > 0 ? $obat->faktor_jasa_farmasi : 0.15);
        if ((int) $obat->unit_per_bulan > 0) {
            $this->volumeA = (string) (int) $obat->unit_per_bulan;
        }
        $this->dispatch('search-selected-a');
    }

    public function resetA(): void
    {
        $this->obatIdA    = null;
        $this->searchA    = '';
        $this->satuanA    = 'tablet';
        $this->tipeA      = 'kronis';
        $this->hargaBeliA = '';
        $this->klaimA     = '';
        $this->faktorJfA  = '0.15';
        $this->volumeA    = '30';
        // Beri tahu Alpine untuk tarik ulang nilai default (preview client-side).
        $this->dispatch('search-selected-a');
    }

    public function tambahObat(): void
    {
        $hasil = $this->hasilDraft;
        if (! $hasil['ready']) return;

        $this->items[] = [
            'nama'     => $this->searchA ?: ('Obat ' . (count($this->items) + 1)),
            'satuan'   => $this->satuanA,
            'tipe'     => $this->tipeA,
            'faktorJf' => (float) $this->faktorJfA,
            'volume'   => (int) $this->volumeA,
            'obatId'   => $this->obatIdA,
            'hasil'    => $hasil,
        ];

        $this->resetA();
        unset($this->hasilDraft);
        $this->showKalkulasi = false;
        $this->dispatch('kalk-changed'); // picu auto-save draft (client)
    }

    public function hapusItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->showKalkulasi = false;
        $this->dispatch('kalk-changed');
    }

    public function kalkulasi(): void
    {
        $this->showKalkulasi = true;
    }

    public function resetSemua(): void
    {
        $this->items         = [];
        $this->showKalkulasi = false;
        $this->resetA();
        $this->dispatch('kalk-changed'); // draft kosong → klien hapus localStorage
    }

    /**
     * Pulihkan draft kalkulasi dari localStorage (auto-save klien) saat kembali ke halaman.
     * `hasil` SELALU dihitung ulang dari nilai mentah → tamper-safe & konsisten dgn rumus server.
     */
    public function restoreDraft(array $items = [], array $form = []): void
    {
        // ── Pulihkan daftar obat ──
        $clean = [];
        foreach (array_slice($items, 0, 200) as $it) {
            if (! is_array($it)) continue;
            $h        = is_array($it['hasil'] ?? null) ? $it['hasil'] : [];
            $tipe     = in_array(($it['tipe'] ?? ''), ['kronis', 'non_kronis'], true) ? $it['tipe'] : 'kronis';
            $faktorJf = (string) ($it['faktorJf'] ?? ($h['faktor_jf'] ?? '0.15'));
            $volume   = (string) (int) ($it['volume'] ?? ($h['volume'] ?? 1));
            $harga    = (string) ($h['harga_beli'] ?? 0);
            $klaim    = (string) ($h['klaim'] ?? 0);

            $hasil = $this->hitung($harga, $klaim, $faktorJf, $volume, $tipe);
            if (! ($hasil['ready'] ?? false)) continue;

            $clean[] = [
                'nama'     => mb_substr(trim((string) ($it['nama'] ?? 'Obat')) ?: 'Obat', 0, 120),
                'satuan'   => (string) ($it['satuan'] ?? 'tablet'),
                'tipe'     => $tipe,
                'faktorJf' => (float) $faktorJf,
                'volume'   => (int) $volume,
                'obatId'   => ! empty($it['obatId']) ? (int) $it['obatId'] : null,
                'hasil'    => $hasil,
            ];
        }
        $restoredCount = 0;
        if ($clean) {
            $this->items   = $clean;
            $restoredCount = count($clean);
        }

        // ── Pulihkan form yang sedang diisi (hanya bila form server masih kosong) ──
        if ($form && $this->searchA === '' && $this->hargaBeliA === '' && $this->klaimA === '') {
            $this->searchA    = mb_substr((string) ($form['searchA'] ?? ''), 0, 120);
            $this->obatIdA    = ! empty($form['obatIdA']) ? (int) $form['obatIdA'] : null;
            $this->satuanA    = (string) ($form['satuanA'] ?? 'tablet');
            $this->tipeA      = in_array(($form['tipeA'] ?? ''), ['kronis', 'non_kronis'], true) ? $form['tipeA'] : 'kronis';
            $this->hargaBeliA = (string) ($form['hargaBeliA'] ?? '');
            $this->klaimA     = (string) ($form['klaimA'] ?? '');
            $this->faktorJfA  = (string) ($form['faktorJfA'] ?? '0.15');
            $this->volumeA    = (string) ($form['volumeA'] ?? '30');
        }

        $this->showKalkulasi = false;
        $this->dispatch('search-selected-a'); // suruh Alpine tarik nilai form ke preview client-side
        if ($restoredCount > 0) {
            // Toast global (decoupled dari morph Livewire) — konfirmasi pemulihan.
            $this->dispatch('toast', type: 'success',
                message: "Daftar kalkulasi dipulihkan — {$restoredCount} obat dari sesi sebelumnya.");
        }
    }

    private function hitung(string $hargaBeli, string $klaim, string $faktorJf, string $volume, string $tipe): array
    {
        $hargaBeli = (float) $hargaBeli;
        $klaim     = (float) $klaim;
        $faktorJf  = max(0.01, (float) $faktorJf);
        $volume    = max(1, (int) $volume);

        if ($hargaBeli <= 0 && $klaim <= 0) {
            return ['ready' => false];
        }

        $bayar         = $tipe === 'kronis' ? round($klaim * \App\Models\Obat::jfMultiplier($faktorJf)) : $klaim;
        $labaPerUnit   = $bayar - $hargaBeli;
        $pendapatanBln = $bayar * $volume;
        $biayaBln      = $hargaBeli * $volume;
        $labaBln       = $pendapatanBln - $biayaBln;
        $marginPersen  = $bayar > 0 ? round($labaPerUnit / $bayar * 100, 1) : 0;
        $labaTahun     = $labaBln * 12;

        $status = $labaBln > 0 ? 'profit' : ($labaBln < 0 ? 'loss' : 'bep');

        return [
            'ready'          => true,
            'bayar'          => $bayar,
            'laba_per_unit'  => $labaPerUnit,
            'pendapatan_bln' => $pendapatanBln,
            'biaya_bln'      => $biayaBln,
            'laba_bln'       => $labaBln,
            'margin_persen'  => $marginPersen,
            'laba_tahun'     => $labaTahun,
            'status'         => $status,
            'harga_beli'     => $hargaBeli,
            'klaim'          => $klaim,
            'faktor_jf'      => $faktorJf,
            'volume'         => $volume,
            'tipe'           => $tipe,
        ];
    }

    #[Computed]
    public function hasilDraft(): array
    {
        return $this->hitung($this->hargaBeliA, $this->klaimA, $this->faktorJfA, $this->volumeA, $this->tipeA);
    }

    #[Computed]
    public function ringkasan(): array
    {
        if (empty($this->items)) return ['ready' => false];

        $hasilList       = array_column($this->items, 'hasil');
        $totalPendapatan = array_sum(array_column($hasilList, 'pendapatan_bln'));
        $totalBiaya      = array_sum(array_column($hasilList, 'biaya_bln'));
        $totalLaba       = $totalPendapatan - $totalBiaya;
        $totalTahun      = $totalLaba * 12;
        $status          = $totalLaba > 0 ? 'profit' : ($totalLaba < 0 ? 'loss' : 'bep');
        $margin          = $totalPendapatan > 0 ? round($totalLaba / $totalPendapatan * 100, 1) : 0;

        return [
            'ready'       => true,
            'pendapatan'  => $totalPendapatan,
            'biaya'       => $totalBiaya,
            'laba_bln'    => $totalLaba,
            'laba_tahun'  => $totalTahun,
            'margin'      => $margin,
            'status'      => $status,
            'jumlah_obat' => count($this->items),
        ];
    }

    public function render()
    {
        return view('livewire.kalkulator-laba', [
            'satuanList' => static::SATUAN,
            'hasilDraft' => $this->hasilDraft,
            'cariObatA'  => $this->cariObatA,
            'ringkasan'  => $this->ringkasan,
        ]);
    }
}
