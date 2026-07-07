<?php

namespace App\Livewire;

use App\Models\HomeVisite;
use App\Models\Pasien;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class HomeVisiteManager extends Component
{
    public bool   $showForm         = false;
    public ?int   $editId           = null;
    public string $pasienSearch     = '';
    public ?int   $pasien_id        = null;
    public string $pasienNama       = '';
    public ?int   $assigned_to      = null;
    public string $tanggal_visite   = '';
    public string $alamat_tujuan    = '';
    public string $lat_tujuan       = '';
    public string $lng_tujuan       = '';
    public ?int   $pengambilan_obat_id = null;
    public string $catatan_admin    = '';
    public string $filterStatus     = '';
    public string $filterTanggal    = '';
    public ?int   $showRiwayatId    = null;
    public bool   $showBatalkanConfirm = false;
    public ?int   $batalkanId       = null;

    public function mount(): void
    {
        $this->tanggal_visite = today()->format('Y-m-d');
        $this->filterTanggal  = today()->format('Y-m-d');
    }

    #[Computed]
    public function visitaList()
    {
        $q = HomeVisite::with(['pasien:id,nama', 'kurir:id,name'])
            ->orderByRaw("FIELD(status,'dalam_perjalanan','sampai','ditugaskan','selesai','dibatalkan')")
            ->orderBy('tanggal_visite', 'desc');

        if ($this->filterStatus) {
            $q->where('status', $this->filterStatus);
        }
        if ($this->filterTanggal) {
            $q->whereDate('tanggal_visite', $this->filterTanggal);
        }

        return $q->get();
    }

    #[Computed]
    public function kurirList()
    {
        return User::whereIn('role', ['kurir', 'apoteker'])->orderBy('name')->get(['id', 'name', 'role']);
    }

    #[Computed]
    public function pasienList()
    {
        if (strlen($this->pasienSearch) < 2) return collect();
        return Pasien::where('is_aktif', true)
            ->where(function ($q) {
                $q->where('nama', 'like', "%{$this->pasienSearch}%")
                  ->orWhere('no_bpjs', 'like', "%{$this->pasienSearch}%");
            })
            ->limit(8)
            ->get(['id', 'nama', 'alamat', 'no_bpjs']);
    }

    #[Computed]
    public function stats(): array
    {
        $today = HomeVisite::whereDate('tanggal_visite', today());
        return [
            'aktif'   => (clone $today)->whereIn('status', ['dalam_perjalanan', 'sampai'])->count(),
            'selesai' => (clone $today)->where('status', 'selesai')->count(),
            'pending' => (clone $today)->where('status', 'ditugaskan')->count(),
        ];
    }

    public function selectPasien(int $id, string $nama, string $alamat): void
    {
        $this->pasien_id     = $id;
        $this->pasienNama    = $nama;
        $this->pasienSearch  = $nama;
        $this->alamat_tujuan = $alamat;
    }

    public function openAssign(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'pasien_id'     => 'required|exists:pasien,id',
            'assigned_to'   => 'required|exists:users,id',
            'tanggal_visite'=> 'required|date',
            'alamat_tujuan' => 'required|string|max:500',
            'lat_tujuan'    => 'nullable|numeric',
            'lng_tujuan'    => 'nullable|numeric',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $data = [
            'pasien_id'     => $this->pasien_id,
            'assigned_to'   => $this->assigned_to,
            'assigned_by'   => Auth::id(),
            'tanggal_visite'=> $this->tanggal_visite,
            'alamat_tujuan' => $this->alamat_tujuan,
            'lat_tujuan'    => $this->lat_tujuan ?: null,
            'lng_tujuan'    => $this->lng_tujuan ?: null,
            'catatan_admin' => $this->catatan_admin ?: null,
            'status'        => 'ditugaskan',
        ];

        $visite = HomeVisite::create($data);
        ActivityLog::record('created', "Home visite ditugaskan ke kurir #{$this->assigned_to} untuk pasien #{$this->pasien_id}", 'HomeVisite', $visite->id);

        $this->dispatch('toast', message: 'Home visite berhasil ditugaskan.', type: 'success');
        $this->cancel();
    }

    public function confirmBatalkan(int $id): void
    {
        $this->batalkanId           = $id;
        $this->showBatalkanConfirm  = true;
    }

    public function batalkan(): void
    {
        if (!$this->batalkanId) return;

        $visite = HomeVisite::findOrFail($this->batalkanId);
        $old    = $visite->only('status');
        $visite->update(['status' => 'dibatalkan']);

        ActivityLog::record('updated', "Home visite #{$this->batalkanId} dibatalkan oleh admin", 'HomeVisite', $this->batalkanId, $old, ['status' => 'dibatalkan']);
        $this->dispatch('toast', message: 'Visite dibatalkan.', type: 'success');
        $this->showBatalkanConfirm = false;
        $this->batalkanId          = null;
    }

    public function openRiwayat(int $id): void
    {
        $this->showRiwayatId = $id;
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editId              = null;
        $this->pasienSearch        = '';
        $this->pasien_id           = null;
        $this->pasienNama          = '';
        $this->assigned_to         = null;
        $this->tanggal_visite      = today()->format('Y-m-d');
        $this->alamat_tujuan       = '';
        $this->lat_tujuan          = '';
        $this->lng_tujuan          = '';
        $this->pengambilan_obat_id = null;
        $this->catatan_admin       = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.home-visite-manager');
    }
}
