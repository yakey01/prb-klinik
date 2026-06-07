<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\ItemPengambilan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PengambilanObat;
use App\Models\PersyaratanKlaim;
use App\Models\ResepPasien;
use App\Models\StokKeluar;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class PengambilanObatForm extends Component
{
    public string $searchPasien = '';
    public ?int $selectedPasienId = null;
    public bool $showPasienDropdown = false;
    public string $tanggalPengambilan = '';
    public string $catatan = '';
    public array $rows = [];
    public ?string $jadwalBerikutnya = null;
    public bool $jadwalSudahDibuat = false;
    public ?int $lastPengambilanId = null;

    // Checklist persyaratan: [['id'=>x,'nama'=>y,'tipe'=>z,'is_wajib'=>bool,'terpenuhi'=>false,'catatan'=>'']]
    public array $checklist = [];

    public function mount(): void
    {
        $this->tanggalPengambilan = now()->format('Y-m-d');
    }

    #[Computed]
    public function pasienSuggestions()
    {
        if (strlen($this->searchPasien) < 2) return collect();
        return Pasien::aktif()
            ->where(function ($q) {
                $q->where('nama', 'like', "%{$this->searchPasien}%")
                  ->orWhere('no_bpjs', 'like', "%{$this->searchPasien}%");
            })
            ->limit(8)
            ->get(['id','nama','no_bpjs','kategori_diagnosis']);
    }

    #[Computed]
    public function selectedPasien(): ?Pasien
    {
        return $this->selectedPasienId ? Pasien::find($this->selectedPasienId) : null;
    }

    #[Computed]
    public function obatList()
    {
        return Obat::where('is_active', true)->where('tipe_obat', 'kronis')->orderBy('nama_obat')->get(['id','nama_obat','satuan','unit_per_bulan','kategori_diagnosis']);
    }

    #[Computed]
    public function riwayatPasien()
    {
        if (!$this->selectedPasienId) return collect();
        return PengambilanObat::where('pasien_id', $this->selectedPasienId)
            ->with('items.obat')
            ->latest('tanggal_pengambilan')
            ->limit(3)
            ->get();
    }

    #[On('catat-pasien')]
    public function handleCatatPasien(int $pasienId): void
    {
        $this->selectPasien($pasienId);
    }

    public function selectPasien(int $id): void
    {
        $this->selectedPasienId = $id;
        $p = Pasien::findOrFail($id);
        $this->searchPasien = $p->nama;
        $this->showPasienDropdown = false;

        // Load persyaratan checklist per diagnosis
        $syarats = PersyaratanKlaim::forDiagnosis($p->kategori_diagnosis);
        $this->checklist = $syarats->map(fn($s) => [
            'id'         => $s->id,
            'nama'       => $s->nama_syarat,
            'deskripsi'  => $s->deskripsi ?? '',
            'tipe'       => $s->tipe,
            'periode'    => $s->periode_bulan,
            'is_wajib'   => $s->is_wajib,
            'terpenuhi'  => false,
            'catatan'    => '',
        ])->toArray();

        // Load drug rows from patient resep (managed in patient detail)
        $resep = ResepPasien::where('pasien_id', $id)
            ->where('is_aktif', true)
            ->with('obat:id,nama_obat,satuan')
            ->orderBy('urutan')->orderBy('id')
            ->get();

        if ($resep->isNotEmpty()) {
            $this->rows = $resep->map(fn($r) => [
                'obat_id'     => $r->obat_id,
                'nama_obat'   => $r->obat?->nama_obat ?? '—',
                'jumlah_unit' => $r->jumlah_default,
                'satuan'      => $r->satuan,
                'catatan'     => '',
            ])->toArray();
        } else {
            $this->rows = [];
        }
    }

    #[Computed]
    public function checklistOk(): bool
    {
        foreach ($this->checklist as $item) {
            if ($item['is_wajib'] && !$item['terpenuhi']) return false;
        }
        return true;
    }

    #[Computed]
    public function wajibBelumChecked(): int
    {
        return collect($this->checklist)->where('is_wajib', true)->where('terpenuhi', false)->count();
    }

    #[Computed]
    public function readyToDispense(): bool
    {
        return $this->selectedPasienId !== null
            && !empty($this->rows)
            && $this->checklistOk;
    }

    public function toggleChecklist(int $index): void
    {
        if (isset($this->checklist[$index])) {
            $this->checklist[$index]['terpenuhi'] = !(bool)($this->checklist[$index]['terpenuhi'] ?? false);
        }
    }

    public function updatedSearchPasien(): void
    {
        $this->showPasienDropdown = strlen($this->searchPasien) >= 2;
        if (!$this->showPasienDropdown) {
            $this->selectedPasienId = null;
            $this->checklist = [];
            $this->rows = [];
        }
    }

    public function clearPasien(): void
    {
        $this->selectedPasienId = null;
        $this->searchPasien = '';
        $this->checklist = [];
        $this->rows = [];
    }

    public function save(): void
    {
        $this->validate([
            'selectedPasienId'   => 'required|exists:pasien,id',
            'tanggalPengambilan' => 'required|date',
            'rows'               => 'required|array|min:1',
            'rows.*.jumlah_unit' => 'required|integer|min:1',
        ], [
            'selectedPasienId.required' => 'Pilih pasien terlebih dahulu.',
            'rows.required'             => 'Pasien belum memiliki resep obat. Tambahkan dulu di halaman Daftar Pasien.',
        ]);

        // Validate semua persyaratan wajib sudah dicentang
        if (!$this->checklistOk) {
            $this->dispatch('toast', type: 'error',
                message: "Lengkapi dulu {$this->wajibBelumChecked} persyaratan klaim yang wajib.");
            return;
        }

        $jadwal = date('Y-m-d', strtotime($this->tanggalPengambilan . ' +30 days'));

        $checklistSnapshot = count($this->checklist) > 0 ? $this->checklist : null;
        $persyaratanOk = count($this->checklist) > 0 ? $this->checklistOk : null;

        $pengambilan = PengambilanObat::create([
            'pasien_id'           => $this->selectedPasienId,
            'tanggal_pengambilan' => $this->tanggalPengambilan,
            'jadwal_berikutnya'   => $jadwal,
            'status'              => 'selesai',
            'total_item'          => count($this->rows),
            'dicatat_oleh'        => auth()->id(),
            'catatan'             => $this->catatan ?: null,
            'checklist_json'      => $checklistSnapshot,
            'persyaratan_ok'      => $persyaratanOk,
        ]);

        foreach ($this->rows as $row) {
            $obat = Obat::find($row['obat_id']);
            $jumlah   = (int) $row['jumlah_unit'];
            $satuan   = $row['satuan'] ?? 'tablet';
            $beliBeli = (float) ($obat?->harga_beli_per_unit ?? 0);
            $klaim    = (float) ($obat?->klaim_bpjs_per_unit ?? 0);
            $faktor   = (float) ($obat?->faktor_jasa_farmasi ?? 1.15);

            ItemPengambilan::create([
                'pengambilan_obat_id'          => $pengambilan->id,
                'obat_id'                      => $row['obat_id'],
                'jumlah_unit'                  => $jumlah,
                'satuan'                       => $satuan,
                'catatan'                      => $row['catatan'] ?? null,
                'harga_beli_snapshot'          => $beliBeli,
                'harga_klaim_bpjs_snapshot'    => $klaim,
                'faktor_jasa_farmasi_snapshot' => $faktor,
            ]);

            // Catat stok keluar otomatis dari penyerahan obat kronis
            StokKeluar::create([
                'obat_id'              => $row['obat_id'],
                'tanggal_keluar'       => $this->tanggalPengambilan,
                'jumlah_unit'          => $jumlah,
                'satuan'               => $satuan,
                'harga_beli_snapshot'  => $beliBeli,
                'harga_jual_per_unit'  => round($klaim * $faktor, 2),
                'keterangan'           => 'Pengambilan: ' . $pasienNama,
                'sumber'               => 'pengambilan',
                'pengambilan_obat_id'  => $pengambilan->id,
                'pasien_id'            => $this->selectedPasienId,
                'dicatat_oleh'         => auth()->id(),
            ]);

            // Kurangi stok aktual
            Obat::where('id', $row['obat_id'])
                ->update(['stok_aktual' => DB::raw('stok_aktual - ' . $jumlah)]);
        }

        $pasienNama = $this->selectedPasien?->nama ?? '';
        ActivityLog::record('dibuat', "Pengambilan obat: {$pasienNama} ({$this->tanggalPengambilan})", 'pengambilan_obat', $pengambilan->id);

        $this->lastPengambilanId = $pengambilan->id;
        $this->jadwalBerikutnya = $jadwal;
        $this->jadwalSudahDibuat = false;
        $this->dispatch('toast', type: 'success', message: "Obat diserahkan ke {$pasienNama}. Klik tombol untuk jadwalkan kunjungan berikutnya.");

        $this->reset(['selectedPasienId','searchPasien','catatan','rows','checklist']);
        $this->tanggalPengambilan = now()->format('Y-m-d');
    }

    public function buatJadwal(): void
    {
        if (!$this->jadwalBerikutnya || $this->jadwalSudahDibuat) return;

        $lastP = $this->lastPengambilanId
            ? PengambilanObat::find($this->lastPengambilanId)
            : null;

        $pasienId = $lastP?->pasien_id;
        if (!$pasienId) {
            $this->dispatch('toast', type: 'error', message: 'Data pengambilan tidak ditemukan.');
            return;
        }

        PengambilanObat::create([
            'pasien_id'           => $pasienId,
            'tanggal_pengambilan' => $this->jadwalBerikutnya,
            'status'              => 'dijadwalkan',
            'total_item'          => 0,
        ]);

        $this->jadwalSudahDibuat = true;
        $this->dispatch('toast', type: 'success',
            message: 'Jadwal berikutnya dibuat: ' . date('d M Y', strtotime($this->jadwalBerikutnya)));
    }

    public function render()
    {
        return view('livewire.pengambilan-obat-form');
    }
}
