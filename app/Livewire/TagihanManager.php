<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\Obat;
use App\Models\PembayaranTagihan;
use App\Models\PurchaseOrder;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public bool   $filterDokumen = false;  // hanya tampilkan tagihan dibayar yg dokumennya belum lengkap

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
    public bool   $bayarLengkapi = false;            // mode: melengkapi dokumen pembayaran legacy (retroaktif)

    // Koreksi pembayaran (edit / void) — jejak audit enterprise
    public ?int   $editPembayaranId = null;          // >0 → modal dalam mode edit pembayaran arsip
    public ?int   $voidId    = null;                 // pembayaran yg sedang diminta pembatalan
    public string $voidAlasan = '';                  // alasan pembatalan (wajib)

    // Hapus faktur/PO (koreksi entry dobel) — reversal stok + jejak audit
    public ?int   $hapusPoId  = null;                // PO yg sedang diminta penghapusan
    public string $hapusAlasan = '';                 // alasan penghapusan (wajib)

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

    /** Ringkasan Guardian AI (di-cache) untuk banner deteksi anomali. */
    #[Computed]
    public function guardian(): array
    {
        return app(\App\Services\Guardian\GuardianEngine::class)->summary();
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

    /** ID tagihan dibayar (lunas/sebagian) yang dokumennya belum lengkap — audit. */
    #[Computed]
    public function docIssueIds(): array
    {
        return Tagihan::whereIn('status', ['lunas', 'sebagian'])
            ->with('pembayaran')
            ->get()
            ->filter->dokumenBermasalah()
            ->pluck('id')->all();
    }

    /** Rincian audit dokumen: hitungan per kategori masalah. */
    #[Computed]
    public function auditDokumen(): array
    {
        $rows = Tagihan::whereIn('status', ['lunas', 'sebagian'])->with('pembayaran')->get();
        $c = ['tanpa_arsip' => 0, 'kurang_faktur' => 0, 'kurang_bukti' => 0, 'total' => 0, 'nilai' => 0.0];
        foreach ($rows as $t) {
            $s = $t->dokumenStatus();
            if (in_array($s, ['na', 'lengkap'], true)) continue;
            $c[$s] = ($c[$s] ?? 0) + 1;
            $c['total']++;
            $c['nilai'] += (float) $t->jumlah_dibayar;
        }
        return $c;
    }

    #[Computed]
    public function tagihanList()
    {
        // Paginate by PO (faktur), showing newest PO first
        // Each PO may have 1-2 tagihan (kronis and/or non_kronis)
        $q = Tagihan::with(['distributor', 'purchaseOrder.items.obat:id,nama_obat,satuan', 'pembayaran'])
            ->orderByDesc('purchase_order_id')
            ->orderBy('tipe_obat');  // kronis before non_kronis within same PO

        if ($this->filterTipe === 'kronis')     $q->kronis();
        if ($this->filterTipe === 'non_kronis') $q->nonKronis();

        if ($this->filterStatus === 'aktif') $q->whereIn('status', ['belum_bayar','sebagian']);
        if ($this->filterStatus === 'lunas') $q->where('status', 'lunas');

        // Audit: hanya tagihan dibayar yang dokumennya belum lengkap.
        if ($this->filterDokumen) $q->whereIn('id', $this->docIssueIds);

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

    /**
     * ID PO yang terindikasi ENTRY DOBEL (faktur kembar) — untuk ditandai di UI.
     * Kriteria: PO lain dgn distributor + nomor_invoice sama (non-kosong),
     * ATAU distributor + tanggal_po + total_nilai sama persis. Global (bukan cuma halaman ini).
     */
    #[Computed]
    public function duplikatPoIds(): array
    {
        $ids = [];
        $collect = function ($rows) use (&$ids) {
            foreach ($rows as $r) {
                foreach (explode(',', (string) $r->ids) as $i) {
                    if ($i !== '') $ids[(int) $i] = true;
                }
            }
        };
        // Kembar berdasarkan nomor invoice (paling kuat).
        $collect(PurchaseOrder::whereNotNull('nomor_invoice')->where('nomor_invoice', '!=', '')
            ->selectRaw('GROUP_CONCAT(id) as ids')
            ->groupBy('distributor_id', 'nomor_invoice')
            ->havingRaw('COUNT(*) > 1')->get());
        // Kembar berdasarkan tanggal + nominal (kasus invoice kosong).
        $collect(PurchaseOrder::selectRaw('GROUP_CONCAT(id) as ids')
            ->groupBy('distributor_id', 'tanggal_po', 'total_nilai')
            ->havingRaw('COUNT(*) > 1')->get());

        return array_keys($ids);
    }

    public function konfirmHapusFaktur(int $poId): void
    {
        $this->hapusPoId  = $poId;
        $this->hapusAlasan = '';
        $this->resetValidation();
    }

    public function batalHapusFaktur(): void
    {
        $this->hapusPoId = null;
        $this->hapusAlasan = '';
    }

    /** Data PO yang akan dihapus (preview di modal konfirmasi). */
    #[Computed]
    public function hapusPreview(): ?PurchaseOrder
    {
        return $this->hapusPoId
            ? PurchaseOrder::with(['items.obat:id,nama_obat,satuan', 'tagihan.pembayaran', 'distributor'])->find($this->hapusPoId)
            : null;
    }

    /**
     * Hapus faktur/PO yang terlanjur di-entry dobel. MENGEMBALIKAN stok (reversal
     * qty per item, guard non-negatif), lalu hapus PO (cascade: items, tagihan,
     * pembayaran). DIBLOKIR bila ada pembayaran aktif — batalkan pembayaran dulu.
     */
    public function hapusFaktur(): void
    {
        $this->validate(
            ['hapusAlasan' => 'required|string|min:3|max:300'],
            ['hapusAlasan.required' => 'Isi alasan penghapusan (jejak audit wajib).', 'hapusAlasan.min' => 'Alasan minimal 3 karakter.'],
            ['hapusAlasan' => 'alasan']
        );

        $po = PurchaseOrder::with(['items', 'tagihan.pembayaran'])->findOrFail($this->hapusPoId);

        // Guard: ada pembayaran AKTIF (belum dibatalkan) → tak boleh hapus.
        $adaBayar = $po->tagihan->flatMap->pembayaran->where('dibatalkan', false)->where('jumlah', '>', 0)->count() > 0;
        if ($adaBayar) {
            $this->dispatch('toast', type: 'error', message: 'Tidak bisa hapus: ada pembayaran aktif pada faktur ini. Batalkan pembayarannya dulu.');
            return;
        }

        \DB::transaction(function () use ($po) {
            // Kembalikan stok untuk tiap item (qty box × isi), guard tak negatif.
            foreach ($po->items as $it) {
                $unit = (int) $it->jumlah_box * max(1, (int) $it->isi_per_box);
                if ($unit > 0 && $it->obat_id) {
                    Obat::where('id', $it->obat_id)
                        ->update(['stok_aktual' => \DB::raw('GREATEST(0, stok_aktual - ' . $unit . ')')]);
                }
            }
            Log::info('[Tagihan] Faktur/PO dihapus (koreksi entry dobel)', [
                'po_id'   => $po->id,
                'invoice' => $po->nomor_invoice,
                'total'   => $po->total_nilai,
                'items'   => $po->items->count(),
                'oleh'    => Auth::user()?->name,
                'alasan'  => $this->hapusAlasan,
            ]);
            $po->delete();   // cascade: purchase_order_items, tagihan, pembayaran_tagihan
        });

        $no = $this->hapusPoId;
        $this->hapusPoId = null;
        $this->hapusAlasan = '';
        $this->resetPage();
        $this->dispatch('toast', type: 'success', message: "Faktur PO #{$no} dihapus & stok obat dikembalikan.");
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
        $this->bayarLengkapi = false;
        $this->editPembayaranId = null;   // mode: tambah pembayaran baru
        $this->voidId = null; $this->voidAlasan = '';
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

    /**
     * Mode "Lengkapi Dokumen" — untuk tagihan yang SUDAH dibayar (lunas/sebagian)
     * tapi belum punya arsip pembayaran (legacy). Mencatat pembayaran retroaktif
     * senilai yang sudah dibayar, LENGKAP dengan faktur & bukti transfer. Status
     * tagihan tidak berubah (Σ arsip = jumlah_dibayar).
     */
    public function openLengkapi(int $id): void
    {
        $this->openBayar($id);
        $t = Tagihan::find($id);
        $this->bayarLengkapi = true;
        $this->bayarJumlah   = (int) ($t->jumlah_dibayar ?? 0);   // senilai yg sudah dibayar
        $this->bayarTanggal  = optional($t->tanggal_bayar)->format('Y-m-d') ?: now()->format('Y-m-d');
    }

    /** Riwayat pembayaran tagihan yang sedang dibuka (arsip lengkap — termasuk yg dibatalkan). */
    #[Computed]
    public function riwayatBayar()
    {
        return $this->bayarId ? PembayaranTagihan::where('tagihan_id', $this->bayarId)->orderByDesc('id')->get() : collect();
    }

    /**
     * Muat 1 pembayaran arsip ke form untuk DIKOREKSI (mis. menambahkan link
     * faktur pada pembayaran lama yang belum punya faktur). Tidak menghapus
     * arsip — hanya mengubah baris terpilih dengan jejak "diubah_oleh".
     */
    public function editBayar(int $id): void
    {
        $p = PembayaranTagihan::findOrFail($id);
        if ($p->tagihan_id !== $this->bayarId || $p->dibatalkan) {
            $this->dispatch('toast', type: 'error', message: 'Pembayaran ini tidak bisa diedit.');
            return;
        }
        $this->editPembayaranId = $p->id;
        $this->bayarJumlah    = (int) $p->jumlah;
        $this->bayarTanggal   = optional($p->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $this->bayarJam       = $p->waktu ? substr($p->waktu, 0, 5) : '';
        $this->bayarMetode    = $p->metode;
        $this->bayarBank      = (string) $p->bank_nama;
        $this->bayarNoRef     = (string) $p->nomor_referensi;
        $this->bayarRekening  = (string) $p->rekening_tujuan;
        $this->bayarAtasNama  = (string) $p->atas_nama;
        $this->bayarLinkBukti = (string) $p->link_bukti;
        $this->bayarLinkFaktur = (string) $p->link_faktur;
        $this->bayarPemutihan = (bool) $p->pemutihan;
        $this->bayarCatatan   = (string) $p->catatan;
        $this->voidId = null; $this->voidAlasan = '';
        $this->resetValidation();
    }

    /** Batalkan mode edit → kembali ke form "tambah pembayaran baru". */
    public function batalEditBayar(): void
    {
        $this->editPembayaranId = null;
        $this->resetValidation();
        $t = $this->bayarId ? Tagihan::find($this->bayarId) : null;
        $this->bayarJumlah   = $t?->sisa_tagihan ?? 0;
        $this->bayarTanggal  = now()->format('Y-m-d');
        $this->bayarJam      = now()->format('H:i');
        $this->bayarMetode   = 'transfer_bank';
        $this->bayarNoRef    = '';
        $this->bayarLinkBukti = '';
        $this->bayarLinkFaktur = '';
        $this->bayarPemutihan = false;
        $this->bayarCatatan  = '';
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
        $edit    = $this->editPembayaranId;

        \DB::transaction(function () use ($tagihan, $edit) {
            $data = [
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
            ];

            if ($edit) {
                // Koreksi baris arsip — pertahankan pencatat asli, tambah jejak pengubah.
                $p = PembayaranTagihan::where('id', $edit)->where('tagihan_id', $tagihan->id)->firstOrFail();
                abort_if($p->dibatalkan, 403);
                $p->update($data + [
                    'diubah_at'   => now(),
                    'diubah_oleh' => Auth::user()?->name,
                ]);
            } else {
                // Pembayaran baru (1 baris = 1 transaksi).
                PembayaranTagihan::create($data + [
                    'tagihan_id'   => $tagihan->id,
                    'dicatat_oleh' => Auth::user()?->name,
                ]);
            }

            $this->recomputeTagihan($tagihan);
        });

        $this->showBayar = false;
        $no = $tagihan->nomor_tagihan;
        $wasEdit = (bool) $edit;
        $this->bayarId = null;
        $this->editPembayaranId = null;
        $wasLengkapi = $this->bayarLengkapi;
        $this->bayarLengkapi = false;
        $this->dispatch('toast', message: $wasLengkapi ? "Dokumen pembayaran {$no} dilengkapi & diarsipkan." : ($wasEdit ? "Pembayaran {$no} diperbarui & terarsip." : "Pembayaran {$no} tercatat & diarsipkan."), type: 'success');
    }

    /** Buka konfirmasi pembatalan (void) pada 1 baris arsip. */
    public function mintaBatal(int $id): void
    {
        $this->voidId = $id;
        $this->voidAlasan = '';
        $this->editPembayaranId = null;
        $this->resetValidation();
    }

    public function tutupBatal(): void { $this->voidId = null; $this->voidAlasan = ''; }

    /**
     * Batalkan (void) 1 pembayaran — arsip TIDAK dihapus, hanya ditandai
     * dibatalkan + alasan + pelaku. Σ dihitung ulang dari pembayaran aktif.
     */
    public function batalkanBayar(): void
    {
        $this->validate(
            ['voidAlasan' => 'required|string|min:3|max:300'],
            ['voidAlasan.required' => 'Isi alasan pembatalan (jejak audit wajib).', 'voidAlasan.min' => 'Alasan minimal 3 karakter.'],
            ['voidAlasan' => 'alasan pembatalan']
        );
        $p = PembayaranTagihan::findOrFail($this->voidId);
        if ($p->tagihan_id !== $this->bayarId || $p->dibatalkan) {
            $this->dispatch('toast', type: 'error', message: 'Pembayaran ini sudah dibatalkan / tidak valid.');
            $this->voidId = null;
            return;
        }
        \DB::transaction(function () use ($p) {
            $p->update([
                'dibatalkan'      => true,
                'dibatalkan_at'   => now(),
                'dibatalkan_oleh' => Auth::user()?->name,
                'alasan_batal'    => $this->voidAlasan,
            ]);
            $this->recomputeTagihan($p->tagihan);
        });
        $this->voidId = null; $this->voidAlasan = '';
        $this->dispatch('toast', message: 'Pembayaran dibatalkan — arsip tetap tersimpan.', type: 'success');
    }

    /** Rekap ulang tagihan dari pembayaran AKTIF (belum dibatalkan). */
    private function recomputeTagihan(Tagihan $tagihan): void
    {
        $totalDibayar = (float) $tagihan->pembayaran()->aktif()->sum('jumlah');
        $total = (float) $tagihan->total_tagihan;
        $status = $totalDibayar <= 0 ? 'belum_bayar' : ($totalDibayar >= $total ? 'lunas' : 'sebagian');
        $last = $tagihan->pembayaran()->aktif()->orderByDesc('tanggal')->orderByDesc('id')->first();
        $tagihan->update([
            'jumlah_dibayar' => min($totalDibayar, $total),
            'status'         => $status,
            'tanggal_bayar'  => $last?->tanggal?->format('Y-m-d'),
        ]);
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
