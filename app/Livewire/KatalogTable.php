<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Diagnosis;
use App\Models\Obat;
use App\Models\PengaturanHarga;
use App\Models\ResepPasien;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;

class KatalogTable extends Component
{
    use WithFileUploads;

    public $csvFile = null;
    public bool $showImport = false;
    public bool $showAudit  = false;
    public bool $showHarga  = false;
    public float $marginGlobal = 0.20;

    public string $search       = '';
    public string $filter       = 'semua';
    public string $filterTipe   = 'semua';   // semua | kronis | non_kronis
    public string $sortBy       = 'nama_obat';
    public string $sortDir      = 'asc';
    public bool   $showInactive = false;
    public bool   $groupMode    = false;   // mode grup per kategori diagnosis (+ subtotal)
    public int    $pageNum      = 1;       // paginasi manual (koleksi sudah difilter di PHP)
    public int    $perPage      = 25;

    /** Reset ke halaman 1 saat filter/pencarian/urut berubah → hindari halaman kosong. */
    public function updated($name): void
    {
        if (in_array($name, ['search', 'filter', 'filterTipe', 'showInactive', 'perPage'], true)) {
            $this->pageNum = 1;
        }
    }

    public function nextPage(): void { $this->pageNum = min($this->pageNum + 1, max(1, (int) ceil($this->obatList->count() / max(1, $this->perPage)))); }
    public function prevPage(): void { $this->pageNum = max(1, $this->pageNum - 1); }
    public function gotoPage(int $n): void { $this->pageNum = max(1, $n); }

    public bool   $showForm             = false;
    public ?int   $editId               = null;
    public string $nama_obat            = '';
    public string $kode_obat            = '';
    public string $kategori_diagnosis   = '';
    public int    $jumlah_pasien        = 0;
    public float  $unit_per_bulan       = 0;
    public float  $harga_beli_per_unit  = 0;
    public string $sumber_harga         = 'EST';
    public float  $klaim_bpjs_per_unit  = 0;
    public float  $faktor_jasa_farmasi  = 0.28;   // +28% jasa (pecahan); ternormalisasi via Obat::jfMultiplier
    public string $tipe_obat            = 'kronis';
    public string $bentuk_sediaan       = '';
    public string $komposisi            = '';
    public bool   $is_active            = true;

    public const KATEGORIS = [
        'Diabetes', 'Hipertensi', 'Jantung', 'Dislipidemia',
        'Asma & PPOK', 'Psikiatri', 'Imunosupresan', 'Gout', 'Lainnya',
    ];

    /** Bentuk/sediaan obat — dikelompokkan per kategori untuk picker premium super lengkap. */
    public const BENTUK_SEDIAAN = [
        'Oral Padat' => [
            ['label' => 'Tablet',              'icon' => 'pill'],
            ['label' => 'Kaplet',              'icon' => 'pill'],
            ['label' => 'Kapsul',              'icon' => 'capsule'],
            ['label' => 'Tablet Salut',        'icon' => 'pill'],
            ['label' => 'Tablet Kunyah',       'icon' => 'pill'],
            ['label' => 'Tablet Effervescent', 'icon' => 'pill'],
            ['label' => 'Tablet Hisap',        'icon' => 'pill'],
            ['label' => 'Sublingual',          'icon' => 'pill'],
            ['label' => 'Pil',                 'icon' => 'pill'],
            ['label' => 'Serbuk',              'icon' => 'powder'],
            ['label' => 'Granul',              'icon' => 'powder'],
            ['label' => 'Pulveres',            'icon' => 'powder'],
        ],
        'Oral Cair' => [
            ['label' => 'Sirup',     'icon' => 'bottle'],
            ['label' => 'Suspensi',  'icon' => 'bottle'],
            ['label' => 'Emulsi',    'icon' => 'bottle'],
            ['label' => 'Eliksir',   'icon' => 'bottle'],
            ['label' => 'Drops Oral','icon' => 'drop'],
        ],
        'Injeksi & Parenteral' => [
            ['label' => 'Pen',     'icon' => 'syringe'],
            ['label' => 'Vial',    'icon' => 'vial'],
            ['label' => 'Ampul',   'icon' => 'vial'],
            ['label' => 'Injeksi', 'icon' => 'syringe'],
            ['label' => 'Infus',   'icon' => 'iv'],
            ['label' => 'Flexpen', 'icon' => 'syringe'],
        ],
        'Topikal' => [
            ['label' => 'Krim',   'icon' => 'tube'],
            ['label' => 'Salep',  'icon' => 'tube'],
            ['label' => 'Gel',    'icon' => 'tube'],
            ['label' => 'Lotion', 'icon' => 'bottle'],
            ['label' => 'Pasta',  'icon' => 'tube'],
            ['label' => 'Foam',   'icon' => 'spray'],
        ],
        'Mata / THT' => [
            ['label' => 'Tetes Mata',    'icon' => 'drop'],
            ['label' => 'Salep Mata',    'icon' => 'tube'],
            ['label' => 'Tetes Telinga', 'icon' => 'drop'],
            ['label' => 'Tetes Hidung',  'icon' => 'drop'],
            ['label' => 'Spray Hidung',  'icon' => 'spray'],
        ],
        'Rektal / Vaginal' => [
            ['label' => 'Suppositoria', 'icon' => 'capsule'],
            ['label' => 'Ovula',        'icon' => 'capsule'],
            ['label' => 'Enema',        'icon' => 'bottle'],
        ],
        'Inhalasi' => [
            ['label' => 'Inhaler',    'icon' => 'inhaler'],
            ['label' => 'Nebule',     'icon' => 'inhaler'],
            ['label' => 'Turbuhaler', 'icon' => 'inhaler'],
            ['label' => 'Aerosol',    'icon' => 'spray'],
        ],
        'Lainnya' => [
            ['label' => 'Patch',   'icon' => 'patch'],
            ['label' => 'Spray',   'icon' => 'spray'],
            ['label' => 'Implant', 'icon' => 'capsule'],
        ],
    ];

    /** Flat list semua label bentuk sediaan (untuk deteksi custom). */
    public static function bentukLabels(): array
    {
        return array_merge(...array_map(
            fn ($group) => array_column($group, 'label'),
            array_values(self::BENTUK_SEDIAAN)
        ));
    }

    #[Computed]
    public function kategoriList(): \Illuminate\Support\Collection
    {
        $db = Diagnosis::aktif();
        return $db->isNotEmpty() ? $db : collect(self::KATEGORIS);
    }

    /** Hitungan obat per tipe (mengikuti toggle nonaktif) untuk chip filter. */
    #[Computed]
    public function tipeCounts(): array
    {
        $q = Obat::query();
        if (! $this->showInactive) $q->where('is_active', true);
        return [
            'kronis'     => (clone $q)->where('tipe_obat', 'kronis')->count(),
            'non_kronis' => (clone $q)->where('tipe_obat', 'non_kronis')->count(),
        ];
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'asc';
        }
        $this->pageNum = 1;
    }

    public function updatePasien(int $id, $value): void
    {
        Obat::findOrFail($id)->update(['jumlah_pasien' => max(0, (int) $value)]);
        $this->dispatch('toast', message: 'Jumlah pasien diperbarui.', type: 'success');
    }

    public function updateUnit(int $id, $value): void
    {
        Obat::findOrFail($id)->update(['unit_per_bulan' => max(0, (float) $value)]);
        $this->dispatch('toast', message: 'Unit/bulan diperbarui.', type: 'success');
    }

    public function updateKlaim(int $id, $value): void
    {
        Obat::findOrFail($id)->update(['klaim_bpjs_per_unit' => max(0, (float) $value)]);
        $this->dispatch('toast', message: 'Klaim BPJS/unit diperbarui.', type: 'success');
    }

    public function updateHarga(int $id, $value): void
    {
        Obat::findOrFail($id)->update([
            'harga_beli_per_unit' => max(0, (float) $value),
            'sumber_harga'        => 'REAL',
        ]);
        $this->dispatch('toast', message: 'Harga beli diperbarui (sumber → REAL).', type: 'success');
    }

    public function updateFaktor(int $id, $value): void
    {
        $value = max(0.01, min(9.99, (float) $value));
        Obat::findOrFail($id)->update(['faktor_jasa_farmasi' => $value]);
        $this->dispatch('toast', message: 'Faktor jasa farmasi diperbarui.', type: 'success');
    }

    /* ── Audit data: obat dengan data bermasalah ──────────────────── */
    public function toggleGroupMode(): void { $this->groupMode = !$this->groupMode; $this->pageNum = 1; }

    /**
     * Baris yang BENAR-BENAR dirender (slice halaman aktif) — kunci performa:
     * obatList penuh dipakai utk TOTAL & subtotal grup, tapi tabel cuma render ~perPage baris
     * → payload Livewire turun ~10× → morph DOM cepat. Mode grup render penuh (overview).
     */
    #[Computed]
    public function pagedList()
    {
        $all = $this->obatList;
        if ($this->groupMode || $this->perPage >= 9999) return $all;
        return $all->forPage($this->pageNum, $this->perPage)->values();
    }

    #[Computed]
    public function totalPages(): int
    {
        return max(1, (int) ceil($this->obatList->count() / max(1, $this->perPage)));
    }

    /* ── Bandingkan obat (compare 2+ obat) ────────────────────────── */
    public array $compareIds = [];
    public bool  $showCompare = false;
    public const COMPARE_MAX = 5;

    public function toggleCompare($id): void
    {
        $id = (int) $id;
        if (in_array($id, $this->compareIds, true)) {
            $this->compareIds = array_values(array_filter($this->compareIds, fn ($v) => $v !== $id));
        } elseif (count($this->compareIds) < self::COMPARE_MAX) {
            $this->compareIds[] = $id;
        } else {
            $this->dispatch('toast', message: 'Maksimal ' . self::COMPARE_MAX . ' obat untuk dibandingkan.', type: 'error');
        }
    }

    public function clearCompare(): void { $this->compareIds = []; $this->showCompare = false; }
    public function closeCompare(): void { $this->showCompare = false; }

    public function openCompare(): void
    {
        if (count($this->compareIds) >= 2) {
            $this->showCompare = true;
        } else {
            $this->dispatch('toast', message: 'Pilih minimal 2 obat untuk dibandingkan.', type: 'error');
        }
    }

    /** Buka panel banding dgn daftar id dari pilihan client-side (Alpine) — 1 round-trip saja. */
    public function openCompareWith($ids): void
    {
        $ids = collect((array) $ids)->map(fn ($v) => (int) $v)->filter()->unique()->take(self::COMPARE_MAX)->values()->all();
        if (count($ids) < 2) {
            $this->dispatch('toast', message: 'Pilih minimal 2 obat untuk dibandingkan.', type: 'error');
            return;
        }
        $this->compareIds  = $ids;
        $this->showCompare = true;
    }

    /** Obat terpilih (urut sesuai urutan dipilih) untuk panel bandingkan. */
    #[Computed]
    public function compareList()
    {
        if (empty($this->compareIds)) return collect();
        $obats = Obat::whereIn('id', $this->compareIds)->get()->keyBy('id');
        return collect($this->compareIds)->map(fn ($id) => $obats->get($id))->filter()->values();
    }

    public function openAudit(): void  { $this->showAudit = true; }
    public function closeAudit(): void { $this->showAudit = false; }

    #[Computed]
    public function auditList()
    {
        return Obat::where('is_active', true)
            ->where(function ($q) {
                $q->where(function ($w) {        // faktor jasa farmasi ngaco (≤0 atau >2, mis. 9.99)
                    $w->where('tipe_obat', 'kronis')
                      ->where(fn ($f) => $f->whereNull('faktor_jasa_farmasi')
                          ->orWhere('faktor_jasa_farmasi', '<=', 0)
                          ->orWhere('faktor_jasa_farmasi', '>', 2));
                })->orWhere(function ($w) {       // klaim BPJS kosong utk obat kronis
                    $w->where('tipe_obat', 'kronis')
                      ->where(fn ($k) => $k->whereNull('klaim_bpjs_per_unit')->orWhere('klaim_bpjs_per_unit', '<=', 0));
                })->orWhereRaw('(klaim_bpjs_per_unit * ' . Obat::jfSql('faktor_jasa_farmasi') . ') < harga_beli_per_unit AND klaim_bpjs_per_unit > 0');
            })
            ->orderBy('nama_obat')
            ->get()
            ->map(function ($o) {
                $f = (float) $o->faktor_jasa_farmasi;
                $issue = $o->klaim_bpjs_per_unit <= 0
                    ? 'klaim_kosong'
                    : (($f <= 0 || $f > 2) ? 'faktor_invalid' : 'rugi');
                return (object) [
                    'id'      => $o->id,
                    'nama'    => $o->nama_obat,
                    'tipe'    => $o->tipe_obat,
                    'kategori'=> $o->kategori_diagnosis,
                    'beli'    => (float) $o->harga_beli_per_unit,
                    'klaim'   => (float) $o->klaim_bpjs_per_unit,
                    'faktor'  => (float) $o->faktor_jasa_farmasi,
                    'unit'    => (float) $o->unit_per_bulan,
                    'laba'    => (float) $o->laba,
                    'issue'   => $issue,
                ];
            });
    }

    /* ── Harga & Margin Umum (profit faktor pasien umum) ──────────── */
    public function openHarga(): void
    {
        $this->marginGlobal = (float) PengaturanHarga::get()->margin_umum_default;
        $this->showHarga = true;
    }
    public function closeHarga(): void { $this->showHarga = false; }

    /** Ubah margin per-obat → hitung ulang harga jual umum. */
    public function updateMargin(int $id, $persen): void
    {
        $persen = max(0, min(500, (float) $persen));
        $margin = round($persen / 100, 4);
        $o = Obat::findOrFail($id);
        $o->update([
            'margin_umum'         => $margin,
            'harga_jual_per_unit' => round(($o->harga_beli_per_unit ?? 0) * (1 + $margin), 0),
        ]);
        $this->dispatch('toast', message: 'Margin ' . round($persen, 1) . '% → harga jual diperbarui.', type: 'success');
    }

    /** Set margin default global (%). */
    public function setMarginGlobal($persen): void
    {
        $persen = max(0, min(500, (float) $persen));
        PengaturanHarga::get()->update(['margin_umum_default' => round($persen / 100, 4)]);
        $this->marginGlobal = round($persen / 100, 4);
        $this->dispatch('toast', message: 'Margin default disimpan (' . round($persen, 1) . '%).', type: 'success');
    }

    /** Terapkan margin default ke SEMUA obat aktif → hitung ulang harga jual (kasir SIM). */
    public function applyMarginAll(): void
    {
        $m = (float) PengaturanHarga::get()->margin_umum_default;
        $n = 0;
        foreach (Obat::where('is_active', true)->get() as $o) {
            $o->update([
                'margin_umum'         => $m,
                'harga_jual_per_unit' => round(($o->harga_beli_per_unit ?? 0) * (1 + $m), 0),
            ]);
            $n++;
        }
        $this->dispatch('toast', message: "Margin " . round($m * 100, 1) . "% diterapkan ke $n obat.", type: 'success');
    }

    #[Computed]
    public function hargaList()
    {
        // SEMUA obat aktif — margin → harga jual yang diakses kasir SIM (umum/tunai),
        // berlaku utk kronis, non_kronis, maupun BMHP. Pencarian dilakukan client-side
        // di modal (Alpine), jadi JSON selalu memuat seluruh obat aktif.
        return Obat::where('is_active', true)
            ->orderBy('nama_obat')
            ->get()
            ->map(fn ($o) => (object) [
                'id'     => $o->id,
                'nama'   => $o->nama_obat,
                'tipe'   => $o->tipe_obat,
                'bentuk' => $o->bentuk_sediaan,
                'satuan' => $o->satuan,
                'beli'   => (float) $o->harga_beli_per_unit,
                'margin' => (float) $o->margin_umum,
                'jual'   => (float) $o->harga_jual_per_unit,
                'stok'   => (int) $o->stok_aktual,
            ]);
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $obat = Obat::findOrFail($id);
        $this->editId              = $id;
        $this->nama_obat           = $obat->nama_obat;
        $this->kode_obat           = $obat->kode_obat ?? '';
        $this->kategori_diagnosis  = $obat->kategori_diagnosis ?? '';
        $this->jumlah_pasien       = $obat->jumlah_pasien;
        $this->unit_per_bulan      = (float) $obat->unit_per_bulan;
        $this->harga_beli_per_unit = (float) $obat->harga_beli_per_unit;
        $this->sumber_harga        = $obat->sumber_harga;
        $this->klaim_bpjs_per_unit = (float) $obat->klaim_bpjs_per_unit;
        $this->faktor_jasa_farmasi = (float) $obat->faktor_jasa_farmasi;
        $this->tipe_obat           = $obat->tipe_obat ?? 'kronis';
        $this->bentuk_sediaan      = $obat->bentuk_sediaan ?? '';
        $this->komposisi           = $obat->komposisi ?? '';
        $this->is_active           = (bool)  $obat->is_active;
        $this->showForm            = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_obat'           => 'required|min:2|max:200',
            'kategori_diagnosis'  => 'nullable|max:150',
            'harga_beli_per_unit' => 'required|numeric|min:0',
            'klaim_bpjs_per_unit' => 'required|numeric|min:0',
            'faktor_jasa_farmasi' => 'required|numeric|min:0.01|max:9.99',
            'bentuk_sediaan'      => 'nullable|max:60',
            'komposisi'           => 'nullable|max:255',
        ], [
            'harga_beli_per_unit.min' => 'Harga beli tidak boleh negatif.',
            'klaim_bpjs_per_unit.min' => 'Klaim BPJS tidak boleh negatif.',
        ]);

        $data = [
            'nama_obat'           => trim($this->nama_obat),
            'kode_obat'           => trim($this->kode_obat) ?: null,
            'kategori_diagnosis'  => trim($this->kategori_diagnosis) ?: null,
            'bentuk_sediaan'      => trim($this->bentuk_sediaan) ?: null,
            'komposisi'           => trim($this->komposisi) ?: null,
            'harga_beli_per_unit' => $this->harga_beli_per_unit,
            'klaim_bpjs_per_unit' => $this->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi' => $this->faktor_jasa_farmasi,
            'tipe_obat'           => $this->tipe_obat,
            'is_active'           => $this->is_active,
        ];

        if ($this->editId) {
            Obat::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', message: "Obat \"{$this->nama_obat}\" berhasil diperbarui.", type: 'success');
        } else {
            Obat::create($data);
            $this->dispatch('toast', message: "Obat \"{$this->nama_obat}\" berhasil ditambahkan.", type: 'success');
        }

        $this->cancel();
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function importCsv(): void
    {
        $this->validate(['csvFile' => 'required|file|mimes:csv,txt|max:2048']);

        $path   = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');
        $header = array_map('trim', fgetcsv($handle));
        $count  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (empty($data['nama_obat'])) continue;

            Obat::updateOrCreate(
                ['nama_obat' => trim($data['nama_obat'])],
                [
                    'kategori_diagnosis'  => trim($data['kategori_diagnosis']  ?? 'Lainnya'),
                    'kode_obat'           => trim($data['kode_obat']           ?? '') ?: null,
                    'jumlah_pasien'       => (int)   ($data['jumlah_pasien']   ?? 0),
                    'unit_per_bulan'      => (float) ($data['unit_per_bulan']  ?? 0),
                    'harga_beli_per_unit' => (float) ($data['harga_beli_per_unit'] ?? 0),
                    'sumber_harga'        => in_array(strtoupper($data['sumber_harga'] ?? ''), ['PO','REAL','EST'])
                                            ? strtoupper($data['sumber_harga']) : 'EST',
                    'klaim_bpjs_per_unit' => (float) ($data['klaim_bpjs_per_unit'] ?? 0),
                    'faktor_jasa_farmasi' => (float) ($data['faktor_jasa_farmasi'] ?? 1.15),
                    'tipe_obat'           => in_array($data['tipe_obat'] ?? '', ['kronis','non_kronis']) ? $data['tipe_obat'] : 'kronis',
                    'bentuk_sediaan'      => trim($data['bentuk_sediaan'] ?? '') ?: null,
                    'komposisi'           => trim($data['komposisi'] ?? '') ?: null,
                    'is_active'           => true,
                ]
            );
            $count++;
        }
        fclose($handle);

        ActivityLog::record('import', "Import CSV: {$count} obat diproses");
        $this->csvFile    = null;
        $this->showImport = false;
        $this->dispatch('toast', message: "{$count} obat berhasil diimpor dari CSV.", type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $obat   = Obat::findOrFail($id);
        $newVal = !$obat->is_active;
        $obat->update(['is_active' => $newVal]);
        $status = $newVal ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('toast', message: "Obat \"{$obat->nama_obat}\" {$status}.", type: 'success');
    }

    public function delete(int $id): void
    {
        $obat = Obat::findOrFail($id);

        if (ResepPasien::where('obat_id', $id)->exists()) {
            $this->dispatch('toast', message: "Obat \"{$obat->nama_obat}\" tidak dapat dihapus karena masih ada resep pasien.", type: 'error');
            return;
        }

        $nama = $obat->nama_obat;
        try {
            $obat->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // FK constraint: obat masih punya riwayat (stok keluar / pengambilan / PO) → tidak boleh hard-delete.
            $this->dispatch('toast', message: "Obat \"{$nama}\" tidak bisa dihapus karena masih ada riwayat transaksi (stok keluar/pengambilan/PO). Pakai tombol Nonaktif untuk menyembunyikannya.", type: 'error');
            return;
        }
        $this->dispatch('toast', message: "Obat \"{$nama}\" berhasil dihapus.", type: 'success');
    }

    /**
     * Klasifikasi keparahan (untuk KPI cockpit + accent bar + urutan "butuh tindakan").
     * Selaras dengan keterangan baris: no_price → rugi → perlu_cek → potensi → laba → netral.
     */
    public function severityOf($o): string
    {
        // Urutan = selaras kolom Keterangan: data belum lengkap dulu, baru rugi sebenarnya.
        if ((float) $o->harga_beli_per_unit <= 0)                                  return 'no_price';
        if ($o->tipe_obat === 'kronis' && (float) $o->klaim_bpjs_per_unit <= 0)    return 'perlu_cek';
        if ((float) $o->laba_per_unit < 0)                                         return 'rugi';
        if ((float) $o->laba_per_unit > 0 && (float) $o->unit_per_bulan <= 0)      return 'potensi';
        if ((float) $o->laba > 0)                                                  return 'laba';
        return 'netral';
    }

    private const SEV_RANK = ['rugi' => 0, 'perlu_cek' => 1, 'no_price' => 2, 'potensi' => 3, 'laba' => 4, 'netral' => 5];

    /** Koleksi dasar (aktif + pencarian + tipe + sinkron resep) — sebelum filter keparahan. Dipakai KPI & obatList. */
    #[Computed]
    public function baseList()
    {
        $query = Obat::query();
        if (!$this->showInactive) $query->where('is_active', true);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama_obat', 'like', '%' . $this->search . '%')
                  ->orWhere('kode_obat', 'like', '%' . $this->search . '%')
                  ->orWhere('komposisi', 'like', '%' . $this->search . '%');
            });
        }
        $list = $query->orderBy($this->sortBy, $this->sortDir)->get();

        $resepStats = \Illuminate\Support\Facades\DB::table('resep_pasien')
            ->where('is_aktif', true)
            ->select('obat_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT pasien_id) AS real_pasien'),
                \Illuminate\Support\Facades\DB::raw('SUM(jumlah_default) AS real_unit'))
            ->groupBy('obat_id')->get()->keyBy('obat_id');

        $list->each(function ($obat) use ($resepStats) {
            if (isset($resepStats[$obat->id])) {
                $obat->jumlah_pasien  = (int)   $resepStats[$obat->id]->real_pasien;
                $obat->unit_per_bulan = (float) $resepStats[$obat->id]->real_unit;
                $obat->dari_resep     = true;
            } else {
                $obat->dari_resep = false;
            }
        });

        // Sumbu filter terpisah: tipe obat (kronis / non-kronis).
        if ($this->filterTipe !== 'semua') {
            $list = $list->where('tipe_obat', $this->filterTipe)->values();
        }
        return $list;
    }

    /** KPI cockpit: hitungan per kategori keparahan + total margin/bln (dari baseList). */
    #[Computed]
    public function kpi(): array
    {
        $c = ['rugi' => 0, 'perlu_cek' => 0, 'potensi' => 0, 'laba' => 0, 'no_price' => 0, 'margin' => 0.0, 'total' => 0];
        foreach ($this->baseList as $o) {
            $s = $this->severityOf($o);
            if (isset($c[$s])) $c[$s]++;
            $c['margin'] += (float) $o->laba;
            $c['total']++;
        }
        return $c;
    }

    #[Computed]
    public function obatList()
    {
        $list = $this->baseList;

        // Filter keparahan (dari KPI cockpit) atau kategori diagnosis.
        $list = match ($this->filter) {
            'laba'      => $list->filter(fn ($o) => $this->severityOf($o) === 'laba')->values(),
            'rugi'      => $list->filter(fn ($o) => $this->severityOf($o) === 'rugi')->values(),
            'potensi'   => $list->filter(fn ($o) => $this->severityOf($o) === 'potensi')->values(),
            'no_price'  => $list->filter(fn ($o) => $this->severityOf($o) === 'no_price')->values(),
            'perlu_cek' => $list->filter(fn ($o) => $this->severityOf($o) === 'perlu_cek')->values(),
            'butuh_tindakan' => $list->filter(fn ($o) => in_array($this->severityOf($o), ['rugi', 'no_price', 'perlu_cek'], true))->values(),
            default     => ($this->filter !== 'semua')
                           ? $list->where('kategori_diagnosis', $this->filter)->values()
                           : $list,
        };

        if ($this->groupMode) {
            // Mode grup: urut per kategori diagnosis (lalu nama).
            $list = $list->sortBy([
                fn ($a, $b) => strcasecmp($a->kategori_diagnosis ?: 'zzz', $b->kategori_diagnosis ?: 'zzz')
                    ?: strcasecmp($a->nama_obat, $b->nama_obat),
            ])->values();
        } elseif ($this->sortBy === 'nama_obat' && $this->sortDir === 'asc') {
            // DEFAULT: urut "butuh tindakan" (rugi → cek → potensi → laba), lalu nama.
            // Sorting via header kolom akan menimpa ini.
            $list = $list->sortBy([
                fn ($a, $b) => (self::SEV_RANK[$this->severityOf($a)] <=> self::SEV_RANK[$this->severityOf($b)])
                    ?: strcasecmp($a->nama_obat, $b->nama_obat),
            ])->values();
        }

        return $list;
    }

    private function resetForm(): void
    {
        $this->editId              = null;
        $this->nama_obat           = '';
        $this->kode_obat           = '';
        $this->kategori_diagnosis  = '';
        $this->jumlah_pasien       = 0;
        $this->unit_per_bulan      = 0;
        $this->harga_beli_per_unit = 0;
        $this->sumber_harga        = 'EST';
        $this->klaim_bpjs_per_unit = 0;
        $this->faktor_jasa_farmasi = 0.28;   // default jasa farmasi +28% (pecahan, ×1.28)
        $this->tipe_obat           = 'kronis';
        $this->bentuk_sediaan      = '';
        $this->komposisi           = '';
        $this->is_active           = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.katalog-table');
    }
}
