<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\Obat;
use App\Models\PengajuanPengadaan as PR;
use App\Models\PengajuanPengadaanItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pengajuan Pengadaan (Purchase Requisition) — apotek mengajukan usulan belanja,
 * manajer menyetujui (di SIM / lokal), lalu direalisasikan jadi PO. Gerbang belanja.
 */
class PengajuanPengadaan extends Component
{
    use WithPagination;

    // List
    public string $search = '';
    public string $filterStatus = 'semua';

    // Form (draft)
    public bool $showForm = false;
    public ?int $editId = null;
    public string $tanggal = '';
    public int $distributor_id = 0;
    public string $prioritas = 'rutin';
    public string $justifikasi = '';
    public string $catatan = '';
    public array $rows = [];

    // Detail drawer
    public ?int $detailId = null;

    // Approval
    public bool $showApprove = false;
    public bool $showTolak = false;
    public ?int $approveId = null;
    public string $catatanApprover = '';
    public string $alasanTolak = '';

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
    }

    // ── Data ────────────────────────────────────────────────────
    #[Computed]
    public function obatList()
    {
        return Obat::where('is_active', true)->orderBy('nama_obat')
            ->get(['id', 'nama_obat', 'tipe_obat', 'satuan', 'harga_beli_per_unit', 'harga_jual_per_unit', 'klaim_bpjs_per_unit', 'faktor_jasa_farmasi']);
    }

    #[Computed]
    public function distributors()
    {
        return Distributor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function kpi(): array
    {
        return [
            'menunggu'   => PR::where('status', 'diajukan')->count(),
            'disetujui'  => PR::where('status', 'disetujui')->count(),
            'nilai_menunggu' => (float) PR::where('status', 'diajukan')->sum('total_beli'),
            'draft'      => PR::where('status', 'draft')->count(),
        ];
    }

    #[Computed]
    public function daftar()
    {
        return PR::with(['distributor', 'pemohon'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) =>
                $w->where('no_pengajuan', 'like', "%{$this->search}%")
                  ->orWhere('justifikasi', 'like', "%{$this->search}%")))
            ->when($this->filterStatus !== 'semua', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(12);
    }

    public function isManajer(): bool
    {
        $u = Auth::user();
        if (! $u) return false;
        $role = strtolower((string) ($u->role ?? ''));
        $jab  = strtolower((string) ($u->jabatan ?? ''));
        return in_array($role, ['admin', 'manajer', 'manager', 'owner', 'pemilik'], true)
            || str_contains($jab, 'manajer') || str_contains($jab, 'manager');
    }

    // ── Form draft ──────────────────────────────────────────────
    public function openAdd(): void
    {
        $this->reset(['editId', 'distributor_id', 'prioritas', 'justifikasi', 'catatan', 'rows']);
        $this->tanggal   = now()->format('Y-m-d');
        $this->prioritas = 'rutin';
        $this->rows      = [];
        $this->addRow();
        $this->showForm  = true;
    }

    public function openEdit(int $id): void
    {
        $p = PR::with('items')->findOrFail($id);
        if (! $p->bisaDiedit()) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan sudah ' . $p->statusLabel() . ' — tidak bisa diedit.');
            return;
        }
        $this->editId         = $p->id;
        $this->tanggal        = $p->tanggal->format('Y-m-d');
        $this->distributor_id = (int) $p->distributor_id;
        $this->prioritas      = $p->prioritas;
        $this->justifikasi    = (string) $p->justifikasi;
        $this->catatan        = (string) $p->catatan;
        $this->rows = $p->items->map(fn ($it) => [
            'obat_id'             => (int) $it->obat_id,
            'nama_obat'           => $it->nama_obat,
            'tipe_obat'           => $it->tipe_obat,
            'jumlah_box'          => (int) $it->jumlah_box,
            'isi_per_box'         => (int) $it->isi_per_box,
            'harga_per_box'       => (float) $it->harga_per_box,
            'klaim_bpjs_per_unit' => (float) $it->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi' => (float) ($it->faktor_jasa_farmasi ?? 1.15),
            'harga_jual'          => 0,
            'subtotal_beli'       => (float) $it->subtotal_beli,
            'estimasi_klaim'      => (float) $it->estimasi_klaim,
            'tanggal_kadaluarsa'  => optional($it->tanggal_kadaluarsa)->format('Y-m-d') ?? '',
            'catatan'             => (string) $it->catatan,
        ])->all();
        if (empty($this->rows)) $this->addRow();
        $this->showForm = true;
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'obat_id' => 0, 'nama_obat' => '', 'tipe_obat' => 'kronis',
            'jumlah_box' => 1, 'isi_per_box' => 1, 'harga_per_box' => 0,
            'klaim_bpjs_per_unit' => 0, 'faktor_jasa_farmasi' => 1.15, 'harga_jual' => 0,
            'subtotal_beli' => 0, 'estimasi_klaim' => 0, 'tanggal_kadaluarsa' => '', 'catatan' => '',
        ];
    }

    public function removeRow(int $i): void
    {
        unset($this->rows[$i]);
        $this->rows = array_values($this->rows);
        if (empty($this->rows)) $this->addRow();
    }

    public function updatedRows($value, $key): void
    {
        // key mis. "0.obat_id"
        [$i, $field] = array_pad(explode('.', $key), 2, null);
        $i = (int) $i;
        if ($field === 'obat_id') {
            $o = $this->obatList->firstWhere('id', (int) $value);
            if ($o) {
                $this->rows[$i]['nama_obat']           = $o->nama_obat;
                $this->rows[$i]['tipe_obat']           = $o->tipe_obat ?: 'kronis';
                $this->rows[$i]['isi_per_box']         = max(1, (int) ($this->rows[$i]['isi_per_box'] ?? 1));
                $this->rows[$i]['harga_per_box']       = (float) ($o->harga_beli_per_unit ?? 0) * (int) $this->rows[$i]['isi_per_box'];
                $this->rows[$i]['klaim_bpjs_per_unit'] = (float) ($o->klaim_bpjs_per_unit ?? 0);
                $this->rows[$i]['faktor_jasa_farmasi'] = (float) ($o->faktor_jasa_farmasi ?? 1.15);
                $this->rows[$i]['harga_jual']          = (float) ($o->harga_jual_per_unit ?? 0);
            }
        }
        $this->recalcRow($i);
    }

    private function recalcRow(int $i): void
    {
        $r = $this->rows[$i] ?? null;
        if (! $r) return;
        $box   = max(0, (int) ($r['jumlah_box'] ?? 0));
        $isi   = max(1, (int) ($r['isi_per_box'] ?? 1));
        $hbox  = (float) ($r['harga_per_box'] ?? 0);
        $units = $box * $isi;
        $this->rows[$i]['subtotal_beli'] = $box * $hbox;
        $kronis = ($r['tipe_obat'] ?? 'kronis') === 'kronis';
        $perUnit = $kronis
            ? (float) ($r['klaim_bpjs_per_unit'] ?? 0) * Obat::jfMultiplier($r['faktor_jasa_farmasi'] ?? 1.15)
            : (float) ($r['harga_jual'] ?? 0);
        $this->rows[$i]['estimasi_klaim'] = $units * $perUnit;
    }

    #[Computed]
    public function formTotal(): array
    {
        $beli  = array_sum(array_map(fn ($r) => (float) ($r['subtotal_beli'] ?? 0), $this->rows));
        $klaim = array_sum(array_map(fn ($r) => (float) ($r['estimasi_klaim'] ?? 0), $this->rows));
        return ['beli' => $beli, 'klaim' => $klaim, 'laba' => $klaim - $beli,
                'margin' => $klaim > 0 ? round(($klaim - $beli) / $klaim * 100, 1) : 0];
    }

    /** Simpan sebagai draft (atau update draft). */
    public function simpan(bool $ajukan = false): void
    {
        $this->validate([
            'tanggal'            => 'required|date',
            'rows'               => 'required|array|min:1',
            'rows.*.obat_id'     => 'required|integer|min:1',
            'rows.*.jumlah_box'  => 'required|integer|min:1',
            'rows.*.isi_per_box' => 'required|integer|min:1',
            'rows.*.harga_per_box' => 'required|numeric|min:1',
        ], [], [
            'rows.*.obat_id' => 'obat', 'rows.*.jumlah_box' => 'jumlah box',
            'rows.*.harga_per_box' => 'harga beli/box',
        ]);
        if ($ajukan) {
            $this->validate([
                'justifikasi'    => 'required|string|min:5',
                'distributor_id' => 'required|integer|min:1',
            ], [], ['justifikasi' => 'justifikasi/alasan', 'distributor_id' => 'distributor']);
        }

        DB::transaction(function () use ($ajukan) {
            $u = Auth::user();
            $p = $this->editId ? PR::findOrFail($this->editId) : new PR();
            if (! $this->editId) {
                $p->no_pengajuan = PR::generateNomor();
                $p->created_by   = $u?->id;
                $p->pemohon_id   = $u?->id;
                $p->pemohon_nama = $u?->name;
                $p->status       = 'draft';
            }
            if ($p->exists && ! $p->bisaDiedit()) {
                abort(403);
            }
            $p->fill([
                'tanggal'        => $this->tanggal,
                'distributor_id' => $this->distributor_id ?: null,
                'prioritas'      => $this->prioritas,
                'justifikasi'    => $this->justifikasi ?: null,
                'catatan'        => $this->catatan ?: null,
            ]);
            if ($ajukan) {
                $p->status = 'diajukan';
                $p->submitted_at = now();
                // reset jejak approval bila re-submit dari revisi
                $p->alasan_tolak = null;
            }
            $p->save();

            $p->items()->delete();
            foreach ($this->rows as $r) {
                $this->recalcRowExternal($r);
                $isi = max(1, (int) $r['isi_per_box']);
                PengajuanPengadaanItem::create([
                    'pengajuan_pengadaan_id' => $p->id,
                    'obat_id'             => $r['obat_id'] ?: null,
                    'nama_obat'           => $r['nama_obat'] ?: (Obat::find($r['obat_id'])->nama_obat ?? '—'),
                    'tipe_obat'           => $r['tipe_obat'] ?? 'kronis',
                    'jumlah_box'          => (int) $r['jumlah_box'],
                    'isi_per_box'         => $isi,
                    'harga_per_box'       => (float) $r['harga_per_box'],
                    'harga_per_unit'      => (float) $r['harga_per_box'] / $isi,
                    'subtotal_beli'       => (float) $r['subtotal_beli'],
                    'klaim_bpjs_per_unit' => (float) $r['klaim_bpjs_per_unit'],
                    'faktor_jasa_farmasi' => (float) ($r['faktor_jasa_farmasi'] ?? 1.15),
                    'estimasi_klaim'      => (float) $r['estimasi_klaim'],
                    'tanggal_kadaluarsa'  => $r['tanggal_kadaluarsa'] ?: null,
                    'catatan'             => $r['catatan'] ?: null,
                ]);
            }
            $p->load('items');
            $p->rekapUlang();
        });

        $this->showForm = false;
        $this->dispatch('toast', message: $ajukan ? 'Pengajuan diajukan — menunggu persetujuan manajer.' : 'Draft pengajuan disimpan.', type: 'success');
    }

    /** recalc dari array lepas (dipakai saat simpan agar konsisten walau updatedRows tak terpicu). */
    private function recalcRowExternal(array &$r): void
    {
        $box = max(0, (int) ($r['jumlah_box'] ?? 0));
        $isi = max(1, (int) ($r['isi_per_box'] ?? 1));
        $r['subtotal_beli'] = $box * (float) ($r['harga_per_box'] ?? 0);
        $kronis = ($r['tipe_obat'] ?? 'kronis') === 'kronis';
        $perUnit = $kronis
            ? (float) ($r['klaim_bpjs_per_unit'] ?? 0) * Obat::jfMultiplier($r['faktor_jasa_farmasi'] ?? 1.15)
            : (float) ($r['harga_jual'] ?? 0);
        $r['estimasi_klaim'] = ($box * $isi) * $perUnit;
    }

    public function ajukanLangsung(): void  { $this->simpan(ajukan: true); }

    public function cancel(): void { $this->showForm = false; }

    /** Ajukan dari daftar/detail (draft/revisi → diajukan). */
    public function ajukan(int $id): void
    {
        $p = PR::findOrFail($id);
        if (! $p->bisaDiajukan()) return;
        if (blank($p->justifikasi)) {
            $this->dispatch('toast', type: 'error', message: 'Isi justifikasi dulu (edit pengajuan) sebelum diajukan.');
            return;
        }
        if (! $p->distributor_id) {
            $this->dispatch('toast', type: 'error', message: 'Pilih distributor dulu (edit pengajuan) sebelum diajukan.');
            return;
        }
        $p->update(['status' => 'diajukan', 'submitted_at' => now(), 'alasan_tolak' => null]);
        $this->dispatch('toast', message: "{$p->no_pengajuan} diajukan — menunggu persetujuan.", type: 'success');
    }

    public function hapus(int $id): void
    {
        $p = PR::findOrFail($id);
        if (! $p->bisaDihapus()) {
            $this->dispatch('toast', type: 'error', message: 'Hanya draft/ditolak yang bisa dihapus.');
            return;
        }
        $p->delete();
        if ($this->detailId === $id) $this->detailId = null;
        $this->dispatch('toast', message: 'Pengajuan dihapus.', type: 'success');
    }

    // ── Detail ──────────────────────────────────────────────────
    public function openDetail(int $id): void { $this->detailId = $id; }
    public function closeDetail(): void { $this->detailId = null; }

    #[Computed]
    public function detail()
    {
        return $this->detailId
            ? PR::with(['items.obat', 'distributor', 'pemohon', 'purchaseOrder'])->find($this->detailId)
            : null;
    }

    // ── Approval (manajer) ──────────────────────────────────────
    public function openApprove(int $id): void
    {
        if (! $this->isManajer()) { $this->dispatch('toast', type: 'error', message: 'Hanya manajer yang bisa menyetujui.'); return; }
        $this->approveId = $id; $this->catatanApprover = ''; $this->showApprove = true;
    }

    public function setujui(): void
    {
        if (! $this->isManajer()) { abort(403); }
        $p = PR::findOrFail($this->approveId);
        if (! $p->bisaApprove()) { $this->dispatch('toast', type: 'error', message: 'Status bukan menunggu persetujuan.'); return; }
        $u = Auth::user();
        $p->update([
            'status' => 'disetujui', 'approver_id' => $u?->id, 'approver_nama' => $u?->name,
            'approver_sumber' => 'APOTIK', 'approved_at' => now(), 'catatan_approver' => $this->catatanApprover ?: null,
        ]);
        $this->showApprove = false;
        $this->dispatch('toast', message: "{$p->no_pengajuan} DISETUJUI — siap direalisasikan jadi PO.", type: 'success');
    }

    public function openTolak(int $id): void
    {
        if (! $this->isManajer()) { $this->dispatch('toast', type: 'error', message: 'Hanya manajer yang bisa menolak.'); return; }
        $this->approveId = $id; $this->alasanTolak = ''; $this->showTolak = true;
    }

    public function tolak(): void
    {
        if (! $this->isManajer()) { abort(403); }
        $this->validate(['alasanTolak' => 'required|string|min:3'], [], ['alasanTolak' => 'alasan penolakan']);
        $p = PR::findOrFail($this->approveId);
        if (! $p->bisaApprove()) return;
        $u = Auth::user();
        $p->update([
            'status' => 'ditolak', 'approver_id' => $u?->id, 'approver_nama' => $u?->name,
            'approver_sumber' => 'APOTIK', 'approved_at' => now(), 'alasan_tolak' => $this->alasanTolak,
        ]);
        $this->showTolak = false;
        $this->dispatch('toast', message: "{$p->no_pengajuan} ditolak.", type: 'success');
    }

    public function mintaRevisi(int $id): void
    {
        if (! $this->isManajer()) { abort(403); }
        $p = PR::findOrFail($id);
        if (! $p->bisaApprove()) return;
        $p->update(['status' => 'revisi', 'approver_id' => Auth::id(), 'approver_nama' => Auth::user()?->name, 'approver_sumber' => 'APOTIK', 'approved_at' => now()]);
        $this->dispatch('toast', message: "{$p->no_pengajuan} dikembalikan untuk revisi.", type: 'success');
    }

    // ── Realisasi → Purchase Order (gerbang belanja) ────────────
    public function realisasi(int $id): void
    {
        $p = PR::with('items')->findOrFail($id);
        if (! $p->bisaRealisasi()) {
            $this->dispatch('toast', type: 'error', message: 'Hanya pengajuan DISETUJUI yang bisa direalisasikan.');
            return;
        }
        if (! $p->distributor_id) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan belum punya distributor — tak bisa jadi PO.');
            return;
        }

        DB::transaction(function () use ($p) {
            $po = PurchaseOrder::create([
                'distributor_id' => $p->distributor_id,
                'nomor_invoice'  => null,
                'tanggal_po'     => now()->toDateString(),
                'total_nilai'    => $p->total_beli,
                'catatan'        => 'Realisasi pengajuan ' . $p->no_pengajuan,
                'status_bayar'   => 'belum',
            ]);

            foreach ($p->items as $it) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'obat_id'           => $it->obat_id,
                    'tipe_obat'         => $it->tipe_obat,
                    'jumlah_box'        => $it->jumlah_box,
                    'isi_per_box'       => $it->isi_per_box,
                    'harga_per_box'     => $it->harga_per_box,
                    'subtotal'          => $it->subtotal_beli,
                ]);
                if ($it->obat_id) {
                    $upd = [
                        'harga_beli_per_unit' => $it->harga_per_unit,
                        'sumber_harga'        => 'PO',
                        'stok_aktual'         => DB::raw('stok_aktual + ' . ((int) $it->jumlah_box * (int) $it->isi_per_box)),
                    ];
                    if ($it->tanggal_kadaluarsa) $upd['tanggal_kadaluarsa'] = $it->tanggal_kadaluarsa->format('Y-m-d');
                    Obat::where('id', $it->obat_id)->update($upd);
                }
            }

            // Auto-split tagihan per tipe (identik alur Pengadaan Baru)
            $subtotalPerTipe = $p->items->groupBy('tipe_obat')->map(fn ($g) => $g->sum('subtotal_beli'));
            foreach ($subtotalPerTipe as $tipe => $total) {
                if ($total <= 0) continue;
                $tipeTag = $tipe === 'kronis' ? 'kronis' : 'non_kronis';
                Tagihan::create([
                    'purchase_order_id'   => $po->id,
                    'distributor_id'      => $p->distributor_id,
                    'nomor_tagihan'       => Tagihan::generateNomor($tipeTag),
                    'tipe_obat'           => $tipeTag,
                    'periode_bulan'       => now()->format('Y-m'),
                    'tanggal_tagihan'     => now()->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
                    'total_tagihan'       => (int) $total,
                    'status'              => 'belum_bayar',
                ]);
            }

            $p->update(['status' => 'direalisasi', 'purchase_order_id' => $po->id]);
        });

        $this->dispatch('toast', message: "{$p->no_pengajuan} direalisasikan → PO dibuat, stok & tagihan diperbarui.", type: 'success');
    }

    public function render()
    {
        return view('livewire.pengajuan-pengadaan');
    }
}
