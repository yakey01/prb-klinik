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
    public array $formResepRows = [];       // resep saat tambah pasien baru

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

    // Aggregated monthly drug needs — from resep_pasien (active prescriptions, same source as /pengadaan/kebutuhan)
    #[Computed]
    public function kebutuhanObatBulanan()
    {
        return DB::table('resep_pasien as rp')
            ->join('obat as o', 'rp.obat_id', '=', 'o.id')
            ->join('pasien as p', 'rp.pasien_id', '=', 'p.id')
            ->where('rp.is_aktif', true)
            ->where('p.is_aktif', true)
            ->whereNull('p.deleted_at')
            ->where('o.is_active', true)
            ->select(
                'rp.obat_id',
                'o.nama_obat',
                'o.satuan',
                DB::raw('COUNT(DISTINCT rp.pasien_id) as jumlah_pasien'),
                DB::raw('ROUND(SUM(rp.jumlah_default) / COUNT(DISTINCT rp.pasien_id)) as rata_unit_per_kunjungan'),
                DB::raw('SUM(rp.jumlah_default) as estimasi_bulanan')
            )
            ->groupBy('rp.obat_id', 'o.nama_obat', 'o.satuan')
            ->orderByDesc('jumlah_pasien')
            ->get();
    }

    #[Computed]
    public function diagnosisList()
    {
        $db = Diagnosis::where('is_active', true)->orderBy('sort_order')->pluck('nama');
        return $db->isNotEmpty() ? $db : collect(['Diabetes','Hipertensi','Jantung','Dislipidemia','Asma & PPOK','Psikiatri','Imunosupresan','Gout','Lainnya']);
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

    // Resep aktif per pasien (dari resep_pasien, bukan item_pengambilan)
    #[Computed]
    public function resepPerPasien(): array
    {
        $ids = $this->pasienList->pluck('id');
        if ($ids->isEmpty()) return [];

        $reseps = ResepPasien::whereIn('pasien_id', $ids)
            ->where('is_aktif', true)
            ->with('obat:id,nama_obat')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $result = [];
        foreach ($reseps as $r) {
            $result[$r->pasien_id][] = [
                'nama'   => $r->obat?->nama_obat ?? '—',
                'jumlah' => $r->jumlah_default,
                'satuan' => $r->satuan ?: 'tablet',
            ];
        }
        return $result;
    }

    #[Computed]
    public function drawerPasien(): ?Pasien
    {
        return $this->drawerPasienId ? Pasien::find($this->drawerPasienId) : null;
    }

    #[Computed]
    public function drawerStats(): array
    {
        if (!$this->drawerPasienId) return [];

        $jadwal = PengambilanObat::where('pasien_id', $this->drawerPasienId)
            ->where('status', 'dijadwalkan')
            ->orderBy('tanggal_pengambilan')
            ->first(['tanggal_pengambilan']);

        $lastPickup = PengambilanObat::where('pasien_id', $this->drawerPasienId)
            ->where('status', 'selesai')
            ->orderByDesc('tanggal_pengambilan')
            ->first(['tanggal_pengambilan', 'total_item']);

        return [
            'jadwal'           => $jadwal?->tanggal_pengambilan,
            'last_pickup'      => $lastPickup?->tanggal_pengambilan,
            'last_pickup_item' => $lastPickup?->total_item ?? 0,
            'resep_count'      => ResepPasien::where('pasien_id', $this->drawerPasienId)
                                    ->where('is_aktif', true)->count(),
        ];
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

    // ── Riwayat Pengambilan Obat — accordion inline per baris pasien (detail untung/rugi) ──
    public ?int $expandedRiwayatId = null;

    public function toggleRiwayat(int $id): void
    {
        $this->expandedRiwayatId = $this->expandedRiwayatId === $id ? null : $id;
    }

    /** Daftar pengambilan (selesai) + item ber-snapshot harga untuk pasien yang sedang di-expand. */
    #[Computed]
    public function riwayatRows()
    {
        if (!$this->expandedRiwayatId) return collect();
        return PengambilanObat::where('pasien_id', $this->expandedRiwayatId)
            ->where('status', 'selesai')
            ->with('items.obat')
            ->orderByDesc('tanggal_pengambilan')
            ->limit(12)
            ->get();
    }

    /** Total kumulatif P&L pasien yang sedang di-expand (HPP, klaim, laba, margin, kunjungan). */
    #[Computed]
    public function riwayatTotals(): array
    {
        if (!$this->expandedRiwayatId) {
            return ['biaya' => 0, 'klaim' => 0, 'laba' => 0, 'margin' => 0, 'kunjungan' => 0, 'item' => 0];
        }

        $row = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->where('po.pasien_id', $this->expandedRiwayatId)
            ->where('po.status', 'selesai')
            ->whereNull('po.deleted_at')
            ->selectRaw('
                COALESCE(SUM(ip.jumlah_unit * ip.harga_beli_snapshot), 0) as biaya,
                COALESCE(SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . '), 0) as klaim,
                COALESCE(SUM(ip.jumlah_unit), 0) as item,
                COUNT(DISTINCT po.id) as kunjungan
            ')
            ->first();

        $biaya = (float) ($row->biaya ?? 0);
        $klaim = (float) ($row->klaim ?? 0);
        $laba  = $klaim - $biaya;

        return [
            'biaya'     => $biaya,
            'klaim'     => $klaim,
            'laba'      => $laba,
            'margin'    => $klaim > 0 ? round($laba / $klaim * 100, 1) : 0,
            'kunjungan' => (int) ($row->kunjungan ?? 0),
            'item'      => (int) ($row->item ?? 0),
        ];
    }

    #[Computed]
    public function drawerPnl(): array
    {
        if (!$this->drawerPasienId) {
            return ['total_biaya' => 0, 'total_klaim' => 0, 'total_laba' => 0, 'count' => 0];
        }

        $row = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->where('po.pasien_id', $this->drawerPasienId)
            ->where('po.status', 'selesai')
            ->whereNull('po.deleted_at')
            ->selectRaw('
                COALESCE(SUM(ip.jumlah_unit * ip.harga_beli_snapshot), 0) as total_biaya,
                COALESCE(SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . '), 0) as total_klaim,
                COUNT(DISTINCT po.id) as count_kunjungan
            ')
            ->first();

        $biaya = (float)($row->total_biaya ?? 0);
        $klaim = (float)($row->total_klaim ?? 0);

        return [
            'total_biaya' => $biaya,
            'total_klaim' => $klaim,
            'total_laba'  => $klaim - $biaya,
            'count'       => (int)($row->count_kunjungan ?? 0),
        ];
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
            'baru_bulan_ini'   => Pasien::whereBetween('created_at', \App\Support\Periode::bulan(now()->year, now()->month))->count(),
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
        $this->jadwal_ambil_obat = now()->addDays(30)->format('Y-m-d');
        $this->formResepRows = [['obat_id' => 0, 'jumlah_default' => 30, 'satuan' => 'tablet']];
        $this->showForm = true;
    }

    public function addFormResepRow(): void
    {
        $this->formResepRows[] = ['obat_id' => 0, 'jumlah_default' => 30, 'satuan' => 'tablet'];
    }

    public function removeFormResepRow(int $i): void
    {
        unset($this->formResepRows[$i]);
        $this->formResepRows = array_values($this->formResepRows);
    }

    public function updatedFormResepRows(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.obat_id') && (int)$value > 0) {
            $idx = (int) explode('.', $key)[0];
            $obat = Obat::find((int)$value);
            if ($obat && isset($this->formResepRows[$idx])) {
                $this->formResepRows[$idx]['satuan'] = $obat->satuan ?: 'tablet';
            }
        }
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
        $this->formResepRows = [];
        $this->reset(['editId','nama','no_bpjs','kategori_diagnosis','telepon','alamat','tanggal_lahir','jenis_kelamin','catatan','jadwal_ambil_obat']);
    }

    public function save(): void
    {
        $this->validate([
            'nama'               => 'required|min:2|max:150',
            'no_bpjs'            => 'required|string|digits:13|unique:pasien,no_bpjs,' . ($this->editId ?? 'NULL'),
            'kategori_diagnosis' => 'nullable|max:100',
            'telepon'            => ['nullable', 'regex:/^08[0-9]{8,11}$/', 'max:15'],
            'alamat'             => 'required|min:5|max:255',
            'tanggal_lahir'      => 'required|date|before:today',
            'jenis_kelamin'      => 'required|in:L,P',
            'jadwal_ambil_obat'  => 'nullable|date|after_or_equal:today',
        ], [
            'nama.required'          => 'Nama pasien wajib diisi.',
            'no_bpjs.required'       => 'Nomor BPJS wajib diisi.',
            'no_bpjs.digits'         => 'Nomor BPJS harus tepat 13 digit angka.',
            'no_bpjs.unique'         => 'Nomor BPJS sudah terdaftar di pasien lain.',
            'telepon.regex'          => 'Format tidak valid. Contoh: 08123456789 (10–13 digit).',
            'alamat.required'        => 'Alamat wajib diisi.',
            'alamat.min'             => 'Alamat terlalu singkat (minimal 5 karakter).',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date'     => 'Format tanggal tidak valid.',
            'tanggal_lahir.before'   => 'Tanggal lahir harus sebelum hari ini.',
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

            // Simpan resep awal jika diisi
            foreach ($this->formResepRows as $i => $row) {
                if (($row['obat_id'] ?? 0) > 0) {
                    ResepPasien::create([
                        'pasien_id'      => $pasienId,
                        'obat_id'        => $row['obat_id'],
                        'jumlah_default' => $row['jumlah_default'] ?? 30,
                        'satuan'         => $row['satuan'] ?? 'tablet',
                        'urutan'         => $i,
                        'is_aktif'       => true,
                    ]);
                }
            }
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
                'jadwal_berikutnya'   => $this->jadwal_ambil_obat,
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
        if (!auth()->user()?->isAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'Hanya admin yang dapat menghapus pasien.');
            return;
        }
        $p = Pasien::findOrFail($id);
        ActivityLog::record('dihapus', "Hapus pasien: {$p->nama}", 'pasien', $id);

        // Cancel all pending schedules before soft-deleting patient
        PengambilanObat::where('pasien_id', $id)
            ->where('status', 'dijadwalkan')
            ->delete();

        // Deactivate all prescriptions so kebutuhan obat no longer counts this patient
        ResepPasien::where('pasien_id', $id)->update(['is_aktif' => false]);

        $p->delete();
        $this->dispatch('toast', type: 'success', message: 'Pasien dihapus.');
    }

    public function render()
    {
        return view('livewire.pasien-manager');
    }
}
