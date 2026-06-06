<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Diagnosis;
use App\Models\Obat;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;

class KatalogTable extends Component
{
    use WithFileUploads;

    public $csvFile = null;
    public bool $showImport = false;

    public string $search       = '';
    public string $filter       = 'semua';
    public string $sortBy       = 'nama_obat';
    public string $sortDir      = 'asc';
    public bool   $showInactive = false;

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
    public float  $faktor_jasa_farmasi  = 1.15;
    public bool   $is_active            = true;

    public const KATEGORIS = [
        'Diabetes', 'Hipertensi', 'Jantung', 'Dislipidemia',
        'Asma & PPOK', 'Psikiatri', 'Imunosupresan', 'Gout', 'Lainnya',
    ];

    #[Computed]
    public function kategoriList(): \Illuminate\Support\Collection
    {
        $db = Diagnosis::aktif();
        return $db->isNotEmpty() ? $db : collect(self::KATEGORIS);
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'asc';
        }
    }

    public function updatePasien(int $id, int $value): void
    {
        Obat::findOrFail($id)->update(['jumlah_pasien' => max(0, $value)]);
        $this->dispatch('toast', message: 'Jumlah pasien diperbarui.', type: 'success');
    }

    public function updateUnit(int $id, float $value): void
    {
        Obat::findOrFail($id)->update(['unit_per_bulan' => max(0, $value)]);
        $this->dispatch('toast', message: 'Unit/bulan diperbarui.', type: 'success');
    }

    public function updateKlaim(int $id, float $value): void
    {
        Obat::findOrFail($id)->update(['klaim_bpjs_per_unit' => max(0, $value)]);
        $this->dispatch('toast', message: 'Klaim BPJS/unit diperbarui.', type: 'success');
    }

    public function updateHarga(int $id, float $value): void
    {
        Obat::findOrFail($id)->update([
            'harga_beli_per_unit' => max(0, $value),
            'sumber_harga'        => 'REAL',
        ]);
        $this->dispatch('toast', message: 'Harga beli diperbarui (sumber → REAL).', type: 'success');
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
        $this->is_active           = (bool)  $obat->is_active;
        $this->showForm            = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_obat'           => 'required|min:2|max:200',
            'kategori_diagnosis'  => 'nullable|max:150',
            'klaim_bpjs_per_unit' => 'required|numeric|min:0',
            'faktor_jasa_farmasi' => 'required|numeric|min:0.01|max:9.99',
        ]);

        $data = [
            'nama_obat'           => trim($this->nama_obat),
            'kode_obat'           => trim($this->kode_obat) ?: null,
            'kategori_diagnosis'  => trim($this->kategori_diagnosis) ?: null,
            'klaim_bpjs_per_unit' => $this->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi' => $this->faktor_jasa_farmasi,
            'is_active'           => $this->is_active,
            'tipe_obat'           => 'kronis', // default; tipe ditentukan saat pengadaan
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
                    'tipe_obat'           => 'kronis',
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

    #[Computed]
    public function obatList()
    {
        $query = Obat::query();

        if (!$this->showInactive) {
            $query->where('is_active', true);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama_obat', 'like', '%' . $this->search . '%')
                  ->orWhere('kode_obat', 'like', '%' . $this->search . '%');
            });
        }

        $list = $query->orderBy($this->sortBy, $this->sortDir)->get();

        // Sinkronisasi real-time: ambil jumlah_pasien & unit_per_bulan dari resep aktif
        $resepStats = \Illuminate\Support\Facades\DB::table('resep_pasien')
            ->where('is_aktif', true)
            ->select(
                'obat_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT pasien_id) AS real_pasien'),
                \Illuminate\Support\Facades\DB::raw('SUM(jumlah_default) AS real_unit')
            )
            ->groupBy('obat_id')
            ->get()
            ->keyBy('obat_id');

        $list->each(function ($obat) use ($resepStats) {
            if (isset($resepStats[$obat->id])) {
                $obat->jumlah_pasien  = (int)   $resepStats[$obat->id]->real_pasien;
                $obat->unit_per_bulan = (float) $resepStats[$obat->id]->real_unit;
                $obat->dari_resep     = true;
            } else {
                $obat->dari_resep = false;
            }
        });

        return match ($this->filter) {
            'laba'      => $list->filter(fn ($o) => $o->laba > 0)->values(),
            'rugi'      => $list->filter(fn ($o) => $o->laba < 0)->values(),
            'perlu_cek' => $list->filter(fn ($o) => $o->laba == 0)->values(),
            default     => ($this->filter !== 'semua')
                           ? $list->where('kategori_diagnosis', $this->filter)->values()
                           : $list,
        };
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
        $this->faktor_jasa_farmasi = 1.15;
        $this->is_active           = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.katalog-table');
    }
}
