<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\PembayaranTagihan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
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

    // Fokus 1 tanggal barang masuk (deep-link dari kalender Barang Masuk Harian → /tagihan?tanggal=Y-m-d)
    #[Url(as: 'tanggal', history: true)]
    public string $tanggal = '';

    // Bayar modal (arsip pembayaran kelas enterprise)
    public bool   $showBayar    = false;
    public ?int   $bayarId      = null;
    public int    $bayarJumlah  = 0;
    public string $bayarTanggal = '';
    public string $bayarJam     = '';
    public string $bayarMetode  = 'transfer_bank';   // transfer_bank|tunai|qris|giro|cek|lainnya
    public string $bayarBank    = '';
    public string $bayarNoRef   = '';
    public string $bayarRekening = '';
    public string $bayarAtasNama = '';
    public string $bayarLinkBukti  = '';             // WAJIB non-tunai
    public string $bayarLinkFaktur = '';             // WAJIB kecuali pemutihan
    public bool   $bayarPemutihan  = false;
    public string $bayarCatatan = '';

    /** Daftar bank umum Indonesia untuk datalist. */
    public array $bankList = ['BCA', 'Mandiri', 'BRI', 'BNI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Panin', 'Maybank', 'OCBC NISP', 'Bank Jatim', 'Muamalat', 'Mega'];

    public function mount(): void
    {
        $this->filterPeriode = now()->format('Y-m');
        $this->bayarTanggal  = now()->format('Y-m-d');

        // Datang dari kalender harian: tampilkan SEMUA status (lunas & belum) untuk hari itu.
        if ($this->tanggal !== '') {
            $this->filterStatus = 'semua';
            $this->viewMode     = 'semua';
        }
    }

    public function clearTanggal(): void
    {
        $this->tanggal = '';
        $this->resetPage();
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

        // Fokus tanggal barang masuk (dari kalender) — via PO.tanggal_po. Mengabaikan window minggu/bulan.
        if ($this->tanggal !== '') {
            $q->whereHas('purchaseOrder', fn ($p) => $p->whereDate('tanggal_po', $this->tanggal));
        } elseif ($this->viewMode === 'mingguan') {
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
        $tagihan = Tagihan::with('distributor')->findOrFail($id);
        $this->bayarId       = $id;
        $this->bayarJumlah   = $tagihan->sisa_tagihan;
        $this->bayarTanggal  = now()->format('Y-m-d');
        $this->bayarJam      = now()->format('H:i');
        $this->bayarMetode   = 'transfer_bank';
        $this->bayarCatatan  = '';
        $this->bayarLinkBukti = '';
        $this->bayarLinkFaktur = '';
        $this->bayarPemutihan  = false;
        // Prefetch (fetching dulu): prefill bank/rekening/atas nama dari pembayaran terakhir ke PBF yang sama.
        $last = PembayaranTagihan::whereHas('tagihan', fn ($q) => $q->where('distributor_id', $tagihan->distributor_id))
            ->where('metode', 'transfer_bank')->latest('id')->first();
        $this->bayarBank     = $last->bank_nama ?? '';
        $this->bayarRekening = $last->rekening_tujuan ?? '';
        $this->bayarAtasNama = $last->atas_nama ?? ($tagihan->distributor->name ?? '');
        $this->bayarNoRef    = '';
        $this->resetValidation();
        $this->showBayar     = true;
    }

    /** Riwayat pembayaran tagihan yang sedang dibuka (arsip). */
    #[Computed]
    public function riwayatBayar()
    {
        return $this->bayarId ? PembayaranTagihan::where('tagihan_id', $this->bayarId)->orderByDesc('id')->get() : collect();
    }

    public function bayar(): void
    {
        $nonTunai = $this->bayarMetode !== 'tunai';
        $rules = [
            'bayarJumlah'  => 'required|integer|min:1',
            'bayarTanggal' => 'required|date',
            'bayarJam'     => 'nullable',
            'bayarMetode'  => 'required|in:transfer_bank,tunai,qris,giro,cek,lainnya',
            // Bukti transfer WAJIB untuk non-tunai (URL, mis. Google Drive).
            'bayarLinkBukti' => [$nonTunai ? 'required' : 'nullable', 'nullable', 'url', 'max:600'],
            // Faktur pembelian WAJIB kecuali dicentang "pemutihan".
            'bayarLinkFaktur' => [$this->bayarPemutihan ? 'nullable' : 'required', 'nullable', 'url', 'max:600'],
        ];
        if ($this->bayarMetode === 'transfer_bank') {
            $rules['bayarBank']  = 'required|string|max:60';
            $rules['bayarNoRef'] = 'required|string|max:100';
        }
        $this->validate($rules, [
            'bayarLinkBukti.required'  => 'Link bukti transfer wajib (upload dulu ke Google Drive, tempel link-nya).',
            'bayarLinkBukti.url'       => 'Link bukti harus URL valid (https://…).',
            'bayarLinkFaktur.required' => 'Link faktur pembelian wajib (scan & upload), kecuali dicentang pemutihan.',
            'bayarLinkFaktur.url'      => 'Link faktur harus URL valid (https://…).',
            'bayarBank.required'       => 'Pilih/isi bank untuk transfer.',
            'bayarNoRef.required'      => 'Isi nomor referensi/transaksi transfer.',
        ], [
            'bayarJumlah' => 'jumlah', 'bayarBank' => 'bank', 'bayarNoRef' => 'nomor referensi',
        ]);

        $tagihan = Tagihan::findOrFail($this->bayarId);

        \DB::transaction(function () use ($tagihan) {
            // Arsipkan pembayaran (1 baris = 1 transaksi bayar).
            PembayaranTagihan::create([
                'tagihan_id'      => $tagihan->id,
                'tanggal'         => $this->bayarTanggal,
                'waktu'           => $this->bayarJam ?: null,
                'metode'          => $this->bayarMetode,
                'bank_nama'       => $this->bayarBank ?: null,
                'nomor_referensi' => $this->bayarNoRef ?: null,
                'rekening_tujuan' => $this->bayarRekening ?: null,
                'atas_nama'       => $this->bayarAtasNama ?: null,
                'jumlah'          => $this->bayarJumlah,
                'link_bukti'      => $this->bayarLinkBukti ?: null,
                'link_faktur'     => $this->bayarLinkFaktur ?: null,
                'pemutihan'       => $this->bayarPemutihan,
                'catatan'         => $this->bayarCatatan ?: null,
                'dicatat_oleh'    => Auth::user()?->name,
            ]);

            // Rekap: jumlah_dibayar = Σ pembayaran (sumber kebenaran). Cap di total.
            $totalDibayar = (float) $tagihan->pembayaran()->sum('jumlah');
            $status = $totalDibayar >= (float) $tagihan->total_tagihan ? 'lunas' : 'sebagian';
            $tagihan->update([
                'jumlah_dibayar' => min($totalDibayar, (float) $tagihan->total_tagihan),
                'status'         => $status,
                'tanggal_bayar'  => $this->bayarTanggal,
                'catatan_bayar'  => $this->bayarCatatan ?: null,
            ]);
        });

        $this->showBayar = false;
        $no = $tagihan->nomor_tagihan;
        $this->bayarId = null;
        $this->dispatch('toast', message: "Pembayaran {$no} tercatat & diarsipkan.", type: 'success');
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
