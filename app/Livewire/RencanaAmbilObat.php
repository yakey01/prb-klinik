<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\PengambilanObat;

class RencanaAmbilObat extends Component
{
    use WithPagination;

    protected const PER_PAGE = 10;

    #[Computed]
    public function jadwal()
    {
        $today = Carbon::today();

        $paginator = PengambilanObat::with(['pasien', 'pasien.resepAktif.obat'])
            ->whereHas('pasien')
            ->where('status', 'dijadwalkan')
            ->orderBy('tanggal_pengambilan')
            ->paginate(self::PER_PAGE);

        return $paginator->through(function ($po) use ($today) {
            $tgl  = Carbon::parse($po->tanggal_pengambilan);
            $diff = $today->diffInDays($tgl, false);

            if ($diff < 0) {
                $urgency = 'overdue';
                $label   = abs((int) $diff) . ' hari terlambat';
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

            $drugs = ($po->pasien?->resepAktif ?? collect())->map(fn ($r) => [
                'nama'   => $r->obat->nama_obat ?? '—',
                'jumlah' => $r->jumlah_default,
                'satuan' => $r->satuan,
            ]);

            return [
                'id'          => $po->id,
                'pasien_nama' => $po->pasien->nama ?? '—',
                'no_bpjs'     => $po->pasien->no_bpjs ?? '—',
                'tanggal'     => $tgl->translatedFormat('d M Y'),
                'tanggal_raw' => $po->tanggal_pengambilan,
                'diff'        => (int) $diff,
                'urgency'     => $urgency,
                'label'       => $label,
                'drugs'       => $drugs,
                'inisial'     => strtoupper(substr($po->pasien->nama ?? 'P', 0, 1)),
            ];
        });
    }

    #[Computed]
    public function stats(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $base  = fn () => PengambilanObat::whereHas('pasien')->where('status', 'dijadwalkan');

        return [
            'total'   => $base()->count(),
            'overdue' => $base()->where('tanggal_pengambilan', '<', $today)->count(),
            'today'   => $base()->where('tanggal_pengambilan', $today)->count(),
            'soon'    => $base()->whereBetween('tanggal_pengambilan', [
                Carbon::today()->addDay()->format('Y-m-d'),
                Carbon::today()->addDays(7)->format('Y-m-d'),
            ])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.rencana-ambil-obat');
    }
}
