<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\PengambilanObat;

class RencanaAmbilObat extends Component
{
    #[Computed]
    public function jadwal(): Collection
    {
        $today = Carbon::today();

        $rows = PengambilanObat::with(['pasien', 'items.obat'])
            ->where('status', 'dijadwalkan')
            ->orderBy('tanggal_pengambilan')
            ->get();

        return $rows->map(function ($po) use ($today) {
            $tgl   = Carbon::parse($po->tanggal_pengambilan);
            $diff  = $today->diffInDays($tgl, false);

            if ($diff < 0) {
                $urgency = 'overdue';
                $label   = abs((int)$diff) . ' hari terlambat';
            } elseif ($diff === 0) {
                $urgency = 'today';
                $label   = 'Hari ini';
            } elseif ($diff <= 3) {
                $urgency = 'soon';
                $label   = $diff . ' hari lagi';
            } elseif ($diff <= 7) {
                $urgency = 'week';
                $label   = $diff . ' hari lagi';
            } else {
                $urgency = 'future';
                $label   = $diff . ' hari lagi';
            }

            // Use items from THIS pickup if any; otherwise from patient's last selesai
            $drugs = $po->items->map(fn ($it) => [
                'nama'   => $it->obat->nama_obat ?? '—',
                'jumlah' => $it->jumlah_unit,
                'satuan' => $it->satuan,
            ]);

            if ($drugs->isEmpty()) {
                $lastSelesai = PengambilanObat::with('items.obat')
                    ->where('pasien_id', $po->pasien_id)
                    ->where('status', 'selesai')
                    ->orderByDesc('tanggal_pengambilan')
                    ->first();

                if ($lastSelesai) {
                    $drugs = $lastSelesai->items->map(fn ($it) => [
                        'nama'   => $it->obat->nama_obat ?? '—',
                        'jumlah' => $it->jumlah_unit,
                        'satuan' => $it->satuan,
                    ]);
                }
            }

            return [
                'id'           => $po->id,
                'pasien_nama'  => $po->pasien->nama ?? '—',
                'no_bpjs'      => $po->pasien->no_bpjs ?? '—',
                'tanggal'      => $tgl->translatedFormat('d M Y'),
                'tanggal_raw'  => $po->tanggal_pengambilan,
                'diff'         => (int) $diff,
                'urgency'      => $urgency,
                'label'        => $label,
                'drugs'        => $drugs,
                'inisial'      => strtoupper(substr($po->pasien->nama ?? 'P', 0, 1)),
            ];
        });
    }

    #[Computed]
    public function stats(): array
    {
        $j = $this->jadwal;
        return [
            'total'   => $j->count(),
            'overdue' => $j->where('urgency', 'overdue')->count(),
            'today'   => $j->where('urgency', 'today')->count(),
            'soon'    => $j->whereIn('urgency', ['soon', 'week'])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.rencana-ambil-obat');
    }
}
