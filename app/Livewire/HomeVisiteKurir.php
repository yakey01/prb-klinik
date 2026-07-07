<?php

namespace App\Livewire;

use App\Models\HomeVisite;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class HomeVisiteKurir extends Component
{
    public ?int $confirmingId     = null;
    public string $confirmAction  = '';
    public string $catatanKaryawan = '';

    #[Computed]
    public function myVisites()
    {
        return HomeVisite::byKurir(Auth::id())
            ->with(['pasien:id,nama,alamat,telepon'])
            ->whereDate('tanggal_visite', '>=', today()->subDays(1))
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->orderByRaw("FIELD(status,'dalam_perjalanan','sampai','ditugaskan')")
            ->orderBy('tanggal_visite')
            ->get();
    }

    #[Computed]
    public function riwayatVisites()
    {
        return HomeVisite::byKurir(Auth::id())
            ->with(['pasien:id,nama'])
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->whereDate('tanggal_visite', '>=', today()->subDays(7))
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function activeVisite()
    {
        return HomeVisite::byKurir(Auth::id())
            ->where('status', 'dalam_perjalanan')
            ->with(['pasien:id,nama,alamat,telepon'])
            ->first();
    }

    public function mulai(int $id): void
    {
        $visite = HomeVisite::findOrFail($id);
        $this->guardOwnership($visite);

        if (!$visite->canStart()) {
            $this->dispatch('toast', message: 'Tidak bisa memulai visite ini.', type: 'error');
            return;
        }

        $visite->update(['status' => 'dalam_perjalanan', 'started_at' => now()]);
        ActivityLog::record('updated', "Home visite #{$id} dimulai", 'HomeVisite', $id);
        $this->dispatch('toast', message: 'Perjalanan dimulai. GPS aktif.', type: 'success');
        $this->dispatch('visite-started', visiteId: $id);
    }

    public function sampai(int $id): void
    {
        $visite = HomeVisite::findOrFail($id);
        $this->guardOwnership($visite);

        if (!$visite->canArrive()) {
            $this->dispatch('toast', message: 'Status tidak valid.', type: 'error');
            return;
        }

        $visite->update(['status' => 'sampai', 'arrived_at' => now()]);
        ActivityLog::record('updated', "Home visite #{$id} sampai tujuan", 'HomeVisite', $id);
        $this->dispatch('toast', message: 'Anda telah sampai di tujuan.', type: 'success');
    }

    public function selesai(int $id): void
    {
        $visite = HomeVisite::findOrFail($id);
        $this->guardOwnership($visite);

        if (!$visite->canFinish()) {
            $this->dispatch('toast', message: 'Status tidak valid.', type: 'error');
            return;
        }

        $visite->update([
            'status'           => 'selesai',
            'completed_at'     => now(),
            'catatan_karyawan' => $this->catatanKaryawan ?: null,
        ]);

        if ($visite->pengambilan_obat_id) {
            $visite->pengambilanObat?->update(['status' => 'selesai']);
        }

        ActivityLog::record('updated', "Home visite #{$id} selesai", 'HomeVisite', $id);
        $this->dispatch('toast', message: 'Visite selesai. Terima kasih!', type: 'success');
        $this->dispatch('visite-selesai', visiteId: $id);
        $this->catatanKaryawan = '';
    }

    public function batalkan(int $id): void
    {
        $visite = HomeVisite::findOrFail($id);
        $this->guardOwnership($visite);

        if ($visite->isDone()) {
            $this->dispatch('toast', message: 'Tidak bisa membatalkan visite yang sudah selesai.', type: 'error');
            return;
        }

        $visite->update(['status' => 'dibatalkan']);
        ActivityLog::record('updated', "Home visite #{$id} dibatalkan oleh kurir", 'HomeVisite', $id);
        $this->dispatch('toast', message: 'Visite dibatalkan.', type: 'success');
        $this->dispatch('visite-selesai', visiteId: $id);
    }

    private function guardOwnership(HomeVisite $visite): void
    {
        abort_if($visite->assigned_to !== Auth::id(), 403, 'Bukan tugas Anda');
    }

    public function render()
    {
        return view('livewire.home-visite-kurir');
    }
}
