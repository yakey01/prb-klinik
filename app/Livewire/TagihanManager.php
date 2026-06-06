<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\Tagihan;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class TagihanManager extends Component
{
    use WithPagination;

    public string $viewMode   = 'semua';   // semua | mingguan | bulanan
    public string $filterTipe = 'semua';   // semua | kronis | non_kronis
    public string $filterStatus = 'aktif'; // aktif | semua | lunas
    public int    $filterDist = 0;
    public string $filterPeriode = '';

    // Bayar modal
    public bool   $showBayar  = false;
    public ?int   $bayarId    = null;
    public int    $bayarJumlah = 0;
    public string $bayarTanggal = '';
    public string $bayarCatatan = '';

    public function mount(): void
    {
        $this->filterPeriode = now()->format('Y-m');
        $this->bayarTanggal  = now()->format('Y-m-d');
    }

    #[Computed]
    public function distributors()
    {
        return Distributor::orderBy('name')->get(['id','name']);
    }

    #[Computed]
    public function kpiCards(): array
    {
        $base = Tagihan::query();
        return [
            'total_terutang'   => (clone $base)->whereIn('status',['belum_bayar','sebagian'])->sum('total_tagihan') - (clone $base)->whereIn('status',['belum_bayar','sebagian'])->sum('jumlah_dibayar'),
            'overdue'          => (clone $base)->overdue()->sum('total_tagihan'),
            'jatuh_tempo_7'    => (clone $base)->whereIn('status',['belum_bayar','sebagian'])->whereBetween('tanggal_jatuh_tempo',[now()->toDateString(), now()->addDays(7)->toDateString()])->sum('total_tagihan'),
            'kronis_terutang'  => (clone $base)->kronis()->whereIn('status',['belum_bayar','sebagian'])->sum('total_tagihan'),
            'non_kronis_terutang' => (clone $base)->nonKronis()->whereIn('status',['belum_bayar','sebagian'])->sum('total_tagihan'),
            'lunas_bulan_ini'  => (clone $base)->where('status','lunas')->where('periode_bulan', now()->format('Y-m'))->sum('total_tagihan'),
            'count_overdue'    => (clone $base)->overdue()->count(),
            'count_aktif'      => (clone $base)->whereIn('status',['belum_bayar','sebagian'])->count(),
        ];
    }

    #[Computed]
    public function tagihanList()
    {
        // Paginate by PO (faktur), showing newest PO first
        // Each PO may have 1-2 tagihan (kronis and/or non_kronis)
        $q = Tagihan::with(['distributor', 'purchaseOrder'])
            ->orderByDesc('purchase_order_id')
            ->orderBy('tipe_obat');  // kronis before non_kronis within same PO

        if ($this->filterTipe === 'kronis')     $q->kronis();
        if ($this->filterTipe === 'non_kronis') $q->nonKronis();

        if ($this->filterStatus === 'aktif') $q->whereIn('status', ['belum_bayar','sebagian']);
        if ($this->filterStatus === 'lunas') $q->where('status', 'lunas');

        if ($this->filterDist > 0) $q->where('distributor_id', $this->filterDist);

        if ($this->viewMode === 'mingguan') {
            $q->whereBetween('tanggal_jatuh_tempo', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->addDays(7)->toDateString(),
            ]);
        } elseif ($this->viewMode === 'bulanan') {
            $q->where('periode_bulan', $this->filterPeriode);
        }

        return $q->paginate(20);
    }

    #[Computed]
    public function tagihanGrouped(): \Illuminate\Support\Collection
    {
        // Group tagihanList by purchase_order_id for per-faktur display
        return collect($this->tagihanList->items())->groupBy('purchase_order_id');
    }

    #[Computed]
    public function bulananList(): array
    {
        // Group by periode untuk view bulanan
        return Tagihan::selectRaw('periode_bulan, tipe_obat, status, SUM(total_tagihan) as total, SUM(jumlah_dibayar) as dibayar, COUNT(*) as jumlah')
            ->groupBy('periode_bulan','tipe_obat','status')
            ->orderByDesc('periode_bulan')
            ->limit(24)
            ->get()
            ->groupBy('periode_bulan')
            ->map(fn($g) => [
                'periode'     => $g->first()->periode_bulan,
                'kronis'      => $g->where('tipe_obat','kronis')->sum('total'),
                'non_kronis'  => $g->where('tipe_obat','non_kronis')->sum('total'),
                'total'       => $g->sum('total'),
                'lunas'       => $g->where('status','lunas')->sum('total'),
                'terutang'    => $g->whereIn('status',['belum_bayar','sebagian'])->sum(fn($r) => $r->total - $r->dibayar),
                'count'       => $g->sum('jumlah'),
            ])
            ->values()
            ->toArray();
    }

    public function openBayar(int $id): void
    {
        $tagihan = Tagihan::findOrFail($id);
        $this->bayarId      = $id;
        $this->bayarJumlah  = $tagihan->sisa_tagihan;
        $this->bayarTanggal = now()->format('Y-m-d');
        $this->bayarCatatan = '';
        $this->showBayar    = true;
    }

    public function bayar(): void
    {
        $this->validate([
            'bayarJumlah'  => 'required|integer|min:1',
            'bayarTanggal' => 'required|date',
        ]);

        $tagihan = Tagihan::findOrFail($this->bayarId);
        $totalDibayar = $tagihan->jumlah_dibayar + $this->bayarJumlah;

        $status = $totalDibayar >= $tagihan->total_tagihan ? 'lunas' : 'sebagian';

        $tagihan->update([
            'jumlah_dibayar' => min($totalDibayar, $tagihan->total_tagihan),
            'status'         => $status,
            'tanggal_bayar'  => $this->bayarTanggal,
            'catatan_bayar'  => $this->bayarCatatan ?: null,
        ]);

        $this->showBayar = false;
        $this->bayarId   = null;
        $this->dispatch('toast', message: "Tagihan {$tagihan->nomor_tagihan} berhasil dicatat sebagai {$status}.", type: 'success');
    }

    public function konfirm(int $id): void
    {
        // Ubah status draft → belum_bayar
        $t = Tagihan::findOrFail($id);
        if ($t->status === 'draft') {
            $t->update(['status' => 'belum_bayar']);
            $this->dispatch('toast', message: "Tagihan {$t->nomor_tagihan} dikonfirmasi.", type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.tagihan-manager');
    }
}
