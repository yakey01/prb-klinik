<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\PengambilanObat;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PasienJadwal extends Component
{
    use WithPagination;

    public string $filterPeriode = 'bulan_ini';

    #[Computed]
    public function jadwalList()
    {
        $q = PengambilanObat::with(['pasien'])
            ->whereIn('status', ['dijadwalkan','lewat'])
            ->orderBy('tanggal_pengambilan');

        if ($this->filterPeriode === 'minggu_ini') {
            $q->whereBetween('tanggal_pengambilan', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->filterPeriode === 'bulan_ini') {
            $q->whereYear('tanggal_pengambilan', now()->year)
              ->whereMonth('tanggal_pengambilan', now()->month);
        } elseif ($this->filterPeriode === 'semua_mendatang') {
            $q->where('tanggal_pengambilan', '>=', now()->format('Y-m-d'));
        } elseif ($this->filterPeriode === 'terlewat') {
            $q->where('tanggal_pengambilan', '<', now()->format('Y-m-d'))
              ->where('status', 'dijadwalkan');
        }

        return $q->paginate(25);
    }

    #[Computed]
    public function stats(): array
    {
        $today = now()->format('Y-m-d');
        return [
            'hari_ini'   => PengambilanObat::where('status', 'dijadwalkan')->where('tanggal_pengambilan', $today)->count(),
            'minggu_ini' => PengambilanObat::where('status', 'dijadwalkan')
                ->whereBetween('tanggal_pengambilan', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'bulan_ini'  => PengambilanObat::where('status', 'dijadwalkan')
                ->whereYear('tanggal_pengambilan', now()->year)
                ->whereMonth('tanggal_pengambilan', now()->month)->count(),
            'terlewat'   => PengambilanObat::where('status', 'dijadwalkan')
                ->where('tanggal_pengambilan', '<', $today)->count(),
        ];
    }

    public function updatedFilterPeriode(): void { $this->resetPage(); }

    public function selesaikan(int $id): void
    {
        $j = PengambilanObat::findOrFail($id);
        $j->update(['status' => 'selesai', 'tanggal_pengambilan' => now()->format('Y-m-d')]);
        ActivityLog::record('diubah', "Jadwal diselesaikan: {$j->pasien?->nama}", 'pengambilan_obat', $id);
        $this->dispatch('toast', type: 'success', message: 'Jadwal ditandai selesai.');
    }

    public function tandaiLewat(int $id): void
    {
        PengambilanObat::findOrFail($id)->update(['status' => 'lewat']);
        $this->dispatch('toast', type: 'info', message: 'Jadwal ditandai lewat.');
    }

    public function render()
    {
        return view('livewire.pasien-jadwal');
    }
}
