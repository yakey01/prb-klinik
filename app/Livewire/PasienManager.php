<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Diagnosis;
use App\Models\ItemPengambilan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PengambilanObat;
use App\Models\PersyaratanKlaim;
use App\Models\ResepPasien;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PasienManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterDiagnosis = '';
    public string $filterStatus = 'aktif';
    public bool $showKebutuhan = false;

    // Drawer detail state
    public ?int $drawerPasienId = null;

    // Drawer: resep management
    public bool $resepEditing = false;
    public array $resepRows = [];   // [{obat_id,nama_obat,jumlah_default,satuan,resep_id}]

    public bool $showForm = false;
    public ?int $editId = null;
    public string $nama = '';
    public string $no_bpjs = '';
    public string $kategori_diagnosis = '';
    public string $telepon = '';
    public string $alamat = '';
    public string $tanggal_lahir = '';
    public string $jenis_kelamin = '';
    public string $catatan = '';
    public string $jadwal_ambil_obat = '';  // tanggal jadwal pengambilan berikutnya

    protected function queryString(): array
    {
        return ['search' => ['except' => ''], 'filterDiagnosis' => ['except' => '']];
    }

    #[Computed]
    public function pasienList()
    {
        $q = Pasien::query();
        if ($this->filterStatus === 'aktif') $q->where('is_aktif', true);
        if ($this->filterDiagnosis) $q->where('kategori_diagnosis', $this->filterDiagnosis);
        if ($this->search) {
            $q->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('no_bpjs', 'like', "%{$this->search}%");
            });
        }
        return $q->withCount('pengambilan')->orderBy('nama')->paginate(20);
    }

    // Last prescription drugs per patient (keyed by patient ID)
    #[Computed]
    public function obatPerPasien(): array
    {
        $ids = $this->pasienList->pluck('id');
        if ($ids->isEmpty()) return [];

        // Get last selesai pengambilan items per patient
        $latestIds = PengambilanObat::whereIn('pasien_id', $ids)
            ->where('status', 'selesai')
            ->select('pasien_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('pasien_id')
            ->pluck('max_id', 'pasien_id');

        if ($latestIds->isEmpty()) return [];

        $items = ItemPengambilan::whereIn('pengambilan_obat_id', $latestIds->values())
            ->with('obat:id,nama_obat')
            ->get();

        $result = [];
        $pengambilanIdToPassienId = $latestIds->flip(); // max_id => pasien_id
        foreach ($items as $item) {
            $pasienId = $pengambilanIdToPassienId[$item->pengambilan_obat_id] ?? null;
            if ($pasienId) {
                $result[$pasienId][] = [
                    'nama'   => $item->obat?->nama_obat ?? '—',
                    'jumlah' => $item->jumlah_unit,
                    'satuan' => $item->satuan ?: ($item->obat?->satuan ?? 'tablet'),
                ];
            }
        }
        return $result;
    }

    // Aggregated monthly drug needs across all active patients
    #[Computed]
    public function kebutuhanObatBulanan()
    {
        return DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->join('pasien as p', 'po.pasien_id', '=', 'p.id')
            ->where('po.status', 'selesai')
            ->where('p.is_aktif', true)
            ->select(
                'ip.obat_id',
                'o.nama_obat',
                'o.satuan',
                DB::raw('COUNT(DISTINCT po.pasien_id) as jumlah_pasien'),
                DB::raw('ROUND(AVG(ip.jumlah_unit)) as rata_unit_per_kunjungan'),
                DB::raw('COUNT(DISTINCT po.pasien_id) * ROUND(AVG(ip.jumlah_unit)) as estimasi_bulanan')
            )
            ->groupBy('ip.obat_id', 'o.nama_obat', 'o.satuan')
            ->orderByDesc('jumlah_pasien')
            ->get();
    }

    #[Computed]
    public function diagnosisList()
    {
        $db = Diagnosis::where('is_active', true)->orderBy('sort_order')->pluck('nama');
        return $db->isNotEmpty() ? $db : collect(['Diabetes Melitus','Hipertensi','Jantung Koroner','PPOK','Epilepsi','Stroke','Lupus','Skizofrenia','Kanker']);
    }

    #[Computed]
    public function jadwalPerPasien(): array
    {
        $ids = $this->pasienList->pluck('id');
        if ($ids->isEmpty()) return [];
        return PengambilanObat::whereIn('pasien_id', $ids)
            ->where('status', 'dijadwalkan')
            ->orderBy('tanggal_pengambilan')
            ->get(['id','pasien_id','tanggal_pengambilan','status'])
            ->groupBy('pasien_id')
            ->map(fn($g) => $g->first())
            ->toArray();
    }

    #[Computed]
    public function lastPickupPerPasien(): array
    {
        $ids = $this->pasienList->pluck('id');
        if ($ids->isEmpty()) return [];
        return PengambilanObat::whereIn('pasien_id', $ids)
            ->where('status', 'selesai')
            ->orderByDesc('tanggal_pengambilan')
            ->get(['pasien_id','tanggal_pengambilan','total_item'])
            ->groupBy('pasien_id')
            ->map(fn($g) => $g->first())
            ->toArray();
    }

    #[Computed]
    public function drawerPasien(): ?Pasien
    {
        return $this->drawerPasienId ? Pasien::find($this->drawerPasienId) : null;
    }

    #[Computed]
    public function drawerRiwayat()
    {
        if (!$this->drawerPasienId) return collect();
        return PengambilanObat::where('pasien_id', $this->drawerPasienId)
            ->with('items.obat')
            ->orderByDesc('tanggal_pengambilan')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function drawerPersyaratan()
    {
        if (!$this->drawerPasienId || !$this->drawerPasien) return collect();
        return PersyaratanKlaim::forDiagnosis($this->drawerPasien->kategori_diagnosis);
    }

    #[Computed]
    public function drawerResep()
    {
        if (!$this->drawerPasienId) return collect();
        return ResepPasien::where('pasien_id', $this->drawerPasienId)
            ->where('is_aktif', true)
            ->with('obat:id,nama_obat,satuan')
            ->orderBy('urutan')->orderBy('id')
            ->get();
    }

    #[Computed]
    public function obatList()
    {
        return Obat::where('is_active', true)->orderBy('nama_obat')->get(['id','nama_obat','satuan']);
    }

    #[Computed]
    public function stats(): array
    {
        $today = now()->format('Y-m-d');
        return [
            'total_aktif'      => Pasien::where('is_aktif', true)->count(),
            'jadwal_hari_ini'  => PengambilanObat::where('status', 'dijadwalkan')->where('tanggal_pengambilan', $today)->count(),
            'terlambat'        => PengambilanObat::where('status', 'dijadwalkan')->where('tanggal_pengambilan', '<', $today)->count(),
            'baru_bulan_ini'   => Pasien::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function openDrawer(int $id): void
    {
        $this->drawerPasienId = $id;
        $this->resepEditing = false;
        $this->resepRows = [];
        $this->dispatch('open-drawer');
    }

    public function closeDrawer(): void
    {
        $this->drawerPasienId = null;
        $this->resepEditing = false;
        $this->resepRows = [];
    }

    public function startResepEdit(): void
    {
        $resep = ResepPasien::where('pasien_id', $this->drawerPasienId)
            ->where('is_aktif', true)
            ->with('obat:id,nama_obat,satuan')
            ->orderBy('urutan')->orderBy('id')
            ->get();

        $this->resepRows = $resep->map(fn($r) => [
            'resep_id'      => $r->id,
            'obat_id'       => $r->obat_id,
            'jumlah_default'=> $r->jumlah_default,
            'satuan'        => $r->satuan,
        ])->toArray();

        if (empty($this->resepRows)) {
            $this->resepRows = [['resep_id'=>0,'obat_id'=>0,'jumlah_default'=>30,'satuan'=>'tablet']];
        }
        $this->resepEditing = true;
    }

    public function cancelResepEdit(): void
    {
        $this->resepEditing = false;
        $this->resepRows = [];
    }

    public function addResepRow(): void
    {
        $this->resepRows[] = ['resep_id'=>0,'obat_id'=>0,'jumlah_default'=>30,'satuan'=>'tablet'];
    }

    public function removeResepRow(int $i): void
    {
        $row = $this->resepRows[$i] ?? null;
        if ($row && ($row['resep_id'] ?? 0) > 0) {
            ResepPasien::find($row['resep_id'])?->delete();
        }
        unset($this->resepRows[$i]);
        $this->resepRows = array_values($this->resepRows);
    }

    public function updatedResepRows(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.obat_id') && (int)$value > 0) {
            $idx = (int) explode('.', $key)[0];
            $obat = Obat::find((int)$value);
            if ($obat && isset($this->resepRows[$idx])) {
                $this->resepRows[$idx]['satuan'] = $obat->satuan ?: 'tablet';
            }
        }
    }

    public function saveResep(): void
    {
        if (!$this->drawerPasienId) return;

        $this->validate([
            'resepRows.*.obat_id'        => 'required|integer|min:1',
            'resepRows.*.jumlah_default'  => 'required|integer|min:1',
        ], [
            'resepRows.*.obat_id.required'       => 'Pilih obat.',
            'resepRows.*.jumlah_default.required' => 'Jumlah wajib diisi.',
        ]);

        $savedIds = [];
        foreach ($this->resepRows as $i => $row) {
            $data = [
                'pasien_id'      => $this->drawerPasienId,
                'obat_id'        => $row['obat_id'],
                'jumlah_default' => $row['jumlah_default'],
                'satuan'         => $row['satuan'] ?? 'tablet',
                'urutan'         => $i,
                'is_aktif'       => true,
            ];
            if (($row['resep_id'] ?? 0) > 0) {
                ResepPasien::find($row['resep_id'])?->update($data);
                $savedIds[] = $row['resep_id'];
            } else {
                $r = ResepPasien::create($data);
                $savedIds[] = $r->id;
            }
        }

        // Nonaktifkan resep yang dihapus
        ResepPasien::where('pasien_id', $this->drawerPasienId)
            ->whereNotIn('id', $savedIds)
            ->update(['is_aktif' => false]);

        $this->resepEditing = false;
        $this->resepRows = [];
        unset($this->obatList);
        $this->dispatch('toast', type: 'success', message: 'Resep obat pasien disimpan.');
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterDiagnosis(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function openAdd(): void
    {
        $this->reset(['editId','nama','no_bpjs','kategori_diagnosis','telepon','alamat','tanggal_lahir','jenis_kelamin','catatan','jadwal_ambil_obat']);
        $this->jadwal_ambil_obat = now()->addDays(30)->format('Y-m-d'); // default 30 hari
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $p = Pasien::findOrFail($id);
        $this->editId              = $id;
        $this->nama                = $p->nama;
        $this->no_bpjs             = $p->no_bpjs ?? '';
        $this->kategori_diagnosis  = $p->kategori_diagnosis ?? '';
        $this->telepon             = $p->telepon ?? '';
        $this->alamat              = $p->alamat ?? '';
        $this->tanggal_lahir       = $p->tanggal_lahir ? $p->tanggal_lahir->format('Y-m-d') : '';
        $this->jenis_kelamin       = $p->jenis_kelamin ?? '';
        $this->catatan             = $p->catatan ?? '';

        // Load jadwal pengambilan berikutnya yang masih dijadwalkan
        $jadwalAktif = PengambilanObat::where('pasien_id', $id)
            ->where('status', 'dijadwalkan')
            ->where('tanggal_pengambilan', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal_pengambilan')
            ->first();
        $this->jadwal_ambil_obat = $jadwalAktif
            ? $jadwalAktif->tanggal_pengambilan->format('Y-m-d')
            : now()->addDays(30)->format('Y-m-d');

        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->reset(['editId','nama','no_bpjs','kategori_diagnosis','telepon','alamat','tanggal_lahir','jenis_kelamin','catatan','jadwal_ambil_obat']);
    }

    public function save(): void
    {
        $this->validate([
            'nama'               => 'required|min:2|max:150',
            'no_bpjs'            => 'required|string|min:8|max:20|unique:pasien,no_bpjs,' . ($this->editId ?? 'NULL'),
            'kategori_diagnosis' => 'nullable|max:100',
            'telepon'            => ['nullable', 'regex:/^08[0-9]{8,11}$/', 'max:15'],
            'alamat'             => 'nullable|max:255',
            'tanggal_lahir'      => 'nullable|date|before:today',
            'jenis_kelamin'      => 'required|in:L,P',
            'jadwal_ambil_obat'  => 'nullable|date|after_or_equal:today',
        ], [
            'no_bpjs.required'   => 'Nomor BPJS wajib diisi.',
            'no_bpjs.min'        => 'Nomor BPJS minimal 8 digit.',
            'no_bpjs.max'        => 'Nomor BPJS maksimal 20 karakter.',
            'no_bpjs.unique'     => 'Nomor BPJS sudah terdaftar di pasien lain.',
            'telepon.regex'      => 'Format nomor tidak valid. Contoh: 08123456789',
            'tanggal_lahir.date' => 'Format tanggal tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        $data = [
            'nama'               => $this->nama,
            'no_bpjs'            => $this->no_bpjs,
            'kategori_diagnosis' => $this->kategori_diagnosis ?: null,
            'telepon'            => $this->telepon ?: null,
            'alamat'             => $this->alamat ?: null,
            'tanggal_lahir'      => $this->tanggal_lahir ?: null,
            'jenis_kelamin'      => $this->jenis_kelamin,
            'catatan'            => $this->catatan ?: null,
            'is_aktif'           => true,
        ];

        if ($this->editId) {
            Pasien::findOrFail($this->editId)->update($data);
            $pasienId = $this->editId;
            ActivityLog::record('diubah', "Edit pasien: {$this->nama}", 'pasien', $this->editId);
        } else {
            $p = Pasien::create($data);
            $pasienId = $p->id;
            ActivityLog::record('dibuat', "Tambah pasien: {$this->nama}", 'pasien', $p->id);
        }

        // Simpan/update jadwal pengambilan obat
        if ($this->jadwal_ambil_obat) {
            // Hapus jadwal lama yang masih dijadwalkan, ganti dengan yang baru
            PengambilanObat::where('pasien_id', $pasienId)
                ->where('status', 'dijadwalkan')
                ->where('tanggal_pengambilan', '>=', now()->format('Y-m-d'))
                ->delete();

            PengambilanObat::create([
                'pasien_id'           => $pasienId,
                'tanggal_pengambilan' => $this->jadwal_ambil_obat,
                'status'              => 'dijadwalkan',
                'total_item'          => 0,
                'dicatat_oleh'        => auth()->id(),
            ]);
        }

        $this->cancel();
        $this->dispatch('toast', type: 'success', message: 'Data pasien dan jadwal berhasil disimpan.');
    }

    // Dispatch browser event so parent Alpine can switch to Catat Pengambilan tab
    public function catat(int $id): void
    {
        $this->dispatch('catat-pasien', pasienId: $id);
    }

    public function toggleStatus(int $id): void
    {
        $p = Pasien::findOrFail($id);
        $p->update(['is_aktif' => !$p->is_aktif]);
        ActivityLog::record('diubah', ($p->is_aktif ? 'Nonaktifkan' : 'Aktifkan') . " pasien: {$p->nama}", 'pasien', $id);
    }

    public function deletePasien(int $id): void
    {
        $p = Pasien::findOrFail($id);
        ActivityLog::record('dihapus', "Hapus pasien: {$p->nama}", 'pasien', $id);
        $p->delete();
        $this->dispatch('toast', type: 'success', message: 'Pasien dihapus.');
    }

    public function render()
    {
        return view('livewire.pasien-manager');
    }
}
