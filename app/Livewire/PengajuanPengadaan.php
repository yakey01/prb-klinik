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
    public string $formMode = 'ajukan';   // 'ajukan' (perlu approval) | 'langsung' (buat PO langsung, koreksi/darurat)
    public ?int $editId = null;
    public string $editStatus = '';   // status pengajuan yg sedang diedit (utk label tombol)
    public string $tanggal = '';
    public int $distributor_id = 0;
    public string $prioritas = 'rutin';
    public string $justifikasi = '';
    public string $catatan = '';
    public array $rows = [];
    public int   $rowSeq = 0;   // penghasil uid baris unik & stabil (untuk wire:key)

    // Detail drawer
    public ?int $detailId = null;

    // Input Faktur Pengadaan (realisasi → PO) — menarik data dari pengajuan yang sudah disetujui
    public bool   $showFaktur = false;
    public string $fakturMode = 'buat';   // 'buat' (realisasi → PO) | 'lengkapi' (isi faktur PO legacy)
    public ?int   $fakturPrId = null;
    public string $nomorFaktur = '';
    public string $tanggalFaktur = '';
    // Baris faktur AKTUAL — default ditarik dari pengajuan disetujui, tapi bisa disesuaikan
    // (qty/harga barang yang benar-benar datang bisa beda dari yang disetujui).
    public array  $fakturRows = [];

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
        // Datang dari /pengadaan/baru (digabung) → langsung buka form mode Input Langsung.
        if (request()->query('mode') === 'langsung') {
            $this->openAdd('langsung');
        }
    }

    // ── Data ────────────────────────────────────────────────────
    #[Computed]
    public function obatList()
    {
        // Batasi ke lingkup obat user (kronis/non/keduanya) — user hanya melihat
        // & bisa memilih obat sesuai izinnya. Admin melihat semua.
        $tipes = Auth::user()?->lingkupTipes() ?? ['kronis', 'non_kronis'];
        return Obat::where('is_active', true)
            ->whereIn('tipe_obat', $tipes)
            ->orderBy('nama_obat')
            ->get(['id', 'nama_obat', 'kode_obat', 'tipe_obat', 'satuan', 'stok_aktual', 'stok_minimum', 'harga_beli_per_unit', 'harga_jual_per_unit', 'klaim_bpjs_per_unit', 'faktor_jasa_farmasi']);
    }

    /** Guard lingkup: pastikan semua baris sesuai izin obat user. Return true jika lolos. */
    private function guardLingkup(array $rows): bool
    {
        $u = Auth::user();
        if (! $u) return true;
        $tipes = $u->lingkupTipes();
        foreach ($rows as $r) {
            $tipe = ($r['tipe_obat'] ?? 'kronis') === 'kronis' ? 'kronis' : 'non_kronis';
            if (! in_array($tipe, $tipes, true)) {
                $this->dispatch('toast', type: 'error', message: 'Lingkup Anda "' . $u->lingkupLabel() . '" — tidak boleh mengadakan obat ' . ($tipe === 'kronis' ? 'kronis' : 'non-kronis') . '.');
                return false;
            }
        }
        return true;
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
        return PR::with(['distributor', 'pemohon', 'purchaseOrder'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) =>
                $w->where('no_pengajuan', 'like', "%{$this->search}%")
                  ->orWhere('justifikasi', 'like', "%{$this->search}%")))
            ->when($this->filterStatus !== 'semua', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(12);
    }

    // ── Form draft ──────────────────────────────────────────────
    public function openAdd(?string $mode = null): void
    {
        $this->reset(['editId', 'editStatus', 'distributor_id', 'prioritas', 'justifikasi', 'catatan', 'rows']);
        $this->formMode  = in_array($mode, ['ajukan', 'langsung'], true) ? $mode : 'ajukan';
        $this->tanggal   = now()->format('Y-m-d');
        $this->prioritas = 'rutin';
        $this->rows      = [];
        $this->addRow();
        $this->showForm  = true;
    }

    /** Ganti mode form Ajukan ↔ Input Langsung (tanpa reset item). */
    public function setMode(string $mode): void
    {
        if (in_array($mode, ['ajukan', 'langsung'], true) && ! $this->editId) {
            $this->formMode = $mode;
        }
    }

    public function openEdit(int $id): void
    {
        $p = PR::with('items')->findOrFail($id);
        if (! $p->bisaDiedit()) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan sudah ' . $p->statusLabel() . ' — tidak bisa diedit.');
            return;
        }
        $this->formMode       = 'ajukan';
        $this->editId         = $p->id;
        $this->editStatus     = $p->status;
        $this->tanggal        = $p->tanggal->format('Y-m-d');
        $this->distributor_id = (int) $p->distributor_id;
        $this->prioritas      = $p->prioritas;
        $this->justifikasi    = (string) $p->justifikasi;
        $this->catatan        = (string) $p->catatan;
        $this->rows = $p->items->map(fn ($it) => [
            'uid'                 => 'r' . (++$this->rowSeq),
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
            'uid' => 'r' . (++$this->rowSeq),
            'obat_id' => 0, 'nama_obat' => '', 'tipe_obat' => 'kronis',
            'jumlah_box' => 1, 'isi_per_box' => 1, 'harga_per_box' => 0,
            'klaim_bpjs_per_unit' => 0, 'faktor_jasa_farmasi' => 1.15, 'harga_jual' => 0,
            'subtotal_beli' => 0, 'estimasi_klaim' => 0, 'tanggal_kadaluarsa' => '', 'catatan' => '',
        ];
    }

    public function removeRow(int $i): void
    {
        // JANGAN array_values() — biarkan kunci numerik baris yang tersisa STABIL agar
        // index Alpine (obatPicker) & wire:model tidak bergeser (mencegah data "loncat" baris).
        unset($this->rows[$i]);
        if (empty($this->rows)) $this->addRow();
    }

    public function updatedRows($value, $key): void
    {
        // key mis. "0.obat_id"
        [$i, $field] = array_pad(explode('.', $key), 2, null);
        $i = (int) $i;
        if ($field === 'tipe_obat') {
            // Ganti kategori Kronis/Non-Kronis → reset obat (daftar obat difilter per tipe).
            $this->rows[$i]['obat_id']             = 0;
            $this->rows[$i]['nama_obat']           = '';
            $this->rows[$i]['klaim_bpjs_per_unit'] = 0;
            $this->rows[$i]['harga_per_box']       = 0;
            $this->rows[$i]['harga_jual']          = 0;
            $this->rows[$i]['stok_aktual']         = null;
            $this->rows[$i]['stok_minimum']        = null;
            $this->rows[$i]['satuan']              = '';
        }
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
                $this->rows[$i]['stok_aktual']         = (int) ($o->stok_aktual ?? 0);
                $this->rows[$i]['stok_minimum']        = (int) ($o->stok_minimum ?? 0);
                $this->rows[$i]['satuan']              = (string) ($o->satuan ?? '');
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
        // KRONIS → diklaim ke BPJS (klaim × jasa farmasi). NON-KRONIS (umum) → TIDAK diklaim
        // BPJS, jadi tak ada estimasi klaim / laba BPJS (pembelian murni).
        $kronis = ($r['tipe_obat'] ?? 'kronis') === 'kronis';
        $this->rows[$i]['estimasi_klaim'] = $kronis
            ? $units * (float) ($r['klaim_bpjs_per_unit'] ?? 0) * Obat::jfMultiplier($r['faktor_jasa_farmasi'] ?? 1.15)
            : 0.0;
    }

    #[Computed]
    public function formTotal(): array
    {
        $beliKronis = 0.0; $beliUmum = 0.0; $klaim = 0.0;
        foreach ($this->rows as $r) {
            $sub = (float) ($r['subtotal_beli'] ?? 0);
            if (($r['tipe_obat'] ?? 'kronis') === 'kronis') {
                $beliKronis += $sub;
                $klaim      += (float) ($r['estimasi_klaim'] ?? 0);
            } else {
                $beliUmum   += $sub;
            }
        }
        $labaBpjs = $klaim - $beliKronis;   // laba BPJS HANYA dari kronis
        return [
            'beli'        => $beliKronis + $beliUmum,
            'beli_kronis' => $beliKronis,
            'beli_umum'   => $beliUmum,
            'klaim'       => $klaim,
            'laba'        => $labaBpjs,
            'margin'      => $klaim > 0 ? round($labaBpjs / $klaim * 100, 1) : 0,
            'ada_kronis'  => $beliKronis > 0 || $klaim > 0,
            'ada_umum'    => $beliUmum > 0,
        ];
    }

    /** Simpan sebagai draft (atau update draft). */
    public function simpan(bool $ajukan = false): void
    {
        try {
            $this->validate([
                'tanggal'            => 'required|date',
                'rows'               => 'required|array|min:1',
                'rows.*.obat_id'     => 'required|integer|min:1',
                'rows.*.jumlah_box'  => 'required|integer|min:1',
                'rows.*.isi_per_box' => 'required|integer|min:1',
                'rows.*.harga_per_box' => 'required|numeric|min:1',
            ], [
                'rows.*.obat_id.required' => 'Pilih obat pada setiap baris.',
                'rows.*.obat_id.min'      => 'Pilih obat pada setiap baris.',
                'rows.*.harga_per_box.min'=> 'Isi harga beli/box (> 0).',
            ], [
                'rows.*.obat_id' => 'obat', 'rows.*.jumlah_box' => 'jumlah box',
                'rows.*.harga_per_box' => 'harga beli/box',
            ]);
            if ($ajukan) {
                $this->validate([
                    'distributor_id' => 'required|integer|min:1',
                    'justifikasi'    => 'required|string|min:5',
                ], [
                    'distributor_id.required' => 'Pilih distributor sebelum mengajukan.',
                    'distributor_id.min'      => 'Pilih distributor sebelum mengajukan.',
                    'justifikasi.required'    => 'Isi justifikasi/alasan belanja.',
                    'justifikasi.min'         => 'Justifikasi minimal 5 karakter.',
                ], ['justifikasi' => 'justifikasi/alasan', 'distributor_id' => 'distributor']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Feedback jelas & dekat aksi — cegah kesan "tombol tidak bisa diklik".
            $this->dispatch('toast', type: 'error', message: 'Belum bisa ' . ($ajukan ? 'diajukan' : 'disimpan') . ': ' . collect($e->errors())->flatten()->first());
            throw $e;
        }

        // Guard lingkup obat: user hanya boleh mengajukan sesuai izinnya.
        if (! $this->guardLingkup(collect($this->rows)->filter(fn ($r) => (int) ($r['obat_id'] ?? 0) > 0)->all())) {
            return;
        }

        $reAppr = false;
        DB::transaction(function () use ($ajukan, &$reAppr) {
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
            // Deteksi: mengedit pengajuan yang SUDAH disetujui (belum jadi PO) → butuh ACC ulang.
            $wasApproved = $p->exists && $p->status === 'disetujui' && ! $p->purchase_order_id;
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
                $p->alasan_tolak = null;
            } elseif ($wasApproved) {
                // Persetujuan lama GUGUR → kembali ke antrean, manajer wajib ACC ulang.
                // PERTAHANKAN approved_at (+ approver lama) sebagai jejak "pernah diputus" agar
                // inbox SIM mendeteksi ini sbg RE-APPROVAL (is_reapproval = diajukan & approved_at≠null),
                // bukan pengajuan baru. Keputusan baru manajer akan menimpanya.
                $p->status           = 'diajukan';
                $p->submitted_at     = now();
                $p->catatan_approver = null;
                $p->alasan_tolak     = null;
                $reAppr = true;
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
        $msg = $ajukan
            ? 'Pengajuan diajukan — menunggu persetujuan manajer.'
            : ($reAppr
                ? 'Perubahan tersimpan — pengajuan dikembalikan untuk PERSETUJUAN ULANG manajer SIM.'
                : ($this->editStatus === 'diajukan'
                    ? 'Perubahan tersimpan — pengajuan tetap menunggu persetujuan manajer.'
                    : 'Draft pengajuan disimpan.'));
        $this->dispatch('toast', message: $msg, type: $reAppr ? 'info' : 'success');
    }

    /** recalc dari array lepas (dipakai saat simpan agar konsisten walau updatedRows tak terpicu). */
    private function recalcRowExternal(array &$r): void
    {
        $box = max(0, (int) ($r['jumlah_box'] ?? 0));
        $isi = max(1, (int) ($r['isi_per_box'] ?? 1));
        $r['subtotal_beli'] = $box * (float) ($r['harga_per_box'] ?? 0);
        // Non-kronis (umum) tidak diklaim BPJS → estimasi_klaim 0.
        $kronis = ($r['tipe_obat'] ?? 'kronis') === 'kronis';
        $r['estimasi_klaim'] = $kronis
            ? ($box * $isi) * (float) ($r['klaim_bpjs_per_unit'] ?? 0) * Obat::jfMultiplier($r['faktor_jasa_farmasi'] ?? 1.15)
            : 0.0;
    }

    public function ajukanLangsung(): void  { $this->simpan(ajukan: true); }

    /**
     * Mode INPUT LANGSUNG: buat PO langsung dari item (tanpa alur persetujuan) —
     * untuk koreksi / pembelian darurat yang sudah disepakati. Menambah stok + tagihan.
     */
    public function simpanLangsung(): void
    {
        try {
            $this->validate([
                'distributor_id'     => 'required|integer|min:1',
                'rows'               => 'required|array|min:1',
                'rows.*.obat_id'     => 'required|integer|min:1',
                'rows.*.jumlah_box'  => 'required|integer|min:1',
                'rows.*.isi_per_box' => 'required|integer|min:1',
                'rows.*.harga_per_box' => 'required|numeric|min:1',
            ], [
                'distributor_id.required' => 'Pilih distributor.',
                'distributor_id.min'      => 'Pilih distributor.',
                'rows.*.obat_id.required'  => 'Pilih obat pada setiap baris.',
                'rows.*.obat_id.min'       => 'Pilih obat pada setiap baris.',
                'rows.*.harga_per_box.min' => 'Isi harga beli/box (> 0).',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: 'Belum bisa disimpan: ' . collect($e->errors())->flatten()->first());
            throw $e;
        }

        $rows = collect($this->rows)
            ->filter(fn ($r) => (int) ($r['obat_id'] ?? 0) > 0 && (int) ($r['jumlah_box'] ?? 0) > 0)
            ->values()->all();

        if (! $this->guardLingkup($rows)) return;

        $u = Auth::user();
        DB::transaction(function () use ($rows, $u) {
            $total = array_sum(array_map(fn ($r) => (int) $r['jumlah_box'] * (float) $r['harga_per_box'], $rows));
            $po = PurchaseOrder::create([
                'distributor_id'   => $this->distributor_id,
                'nomor_invoice'    => null,
                'tanggal_po'       => $this->tanggal ?: now()->toDateString(),
                'total_nilai'      => $total,
                'catatan'          => 'Input langsung (tanpa pengajuan)' . ($this->catatan ? ' · ' . $this->catatan : ''),
                'status_bayar'     => 'belum',
                'dibuat_oleh_id'   => $u?->id,
                'dibuat_oleh_nama' => $u?->name,
                'sumber'           => 'langsung',
            ]);
            $perTipe = [];
            foreach ($rows as $r) {
                $box = (int) $r['jumlah_box']; $isi = max(1, (int) $r['isi_per_box']); $hbox = (float) $r['harga_per_box'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id, 'obat_id' => (int) $r['obat_id'],
                    'tipe_obat' => $r['tipe_obat'] ?? 'kronis', 'jumlah_box' => $box,
                    'isi_per_box' => $isi, 'harga_per_box' => $hbox, 'subtotal' => $box * $hbox,
                ]);
                $upd = ['harga_beli_per_unit' => $hbox / $isi, 'sumber_harga' => 'PO',
                        'stok_aktual' => DB::raw('stok_aktual + ' . ($box * $isi))];
                if (! empty($r['tanggal_kadaluarsa'])) $upd['tanggal_kadaluarsa'] = $r['tanggal_kadaluarsa'];
                Obat::where('id', (int) $r['obat_id'])->update($upd);
                $t = ($r['tipe_obat'] ?? 'kronis') === 'kronis' ? 'kronis' : 'non_kronis';
                $perTipe[$t] = ($perTipe[$t] ?? 0) + $box * $hbox;
            }
            foreach ($perTipe as $tipeTag => $tot) {
                if ($tot <= 0) continue;
                Tagihan::create([
                    'purchase_order_id' => $po->id, 'distributor_id' => $this->distributor_id,
                    'nomor_tagihan' => Tagihan::generateNomor($tipeTag), 'tipe_obat' => $tipeTag,
                    'periode_bulan' => now()->format('Y-m'), 'tanggal_tagihan' => $this->tanggal ?: now()->toDateString(),
                    'tanggal_jatuh_tempo' => \Carbon\Carbon::parse($this->tanggal ?: now())->addDays(30)->toDateString(),
                    'total_tagihan' => (int) $tot, 'status' => 'belum_bayar',
                ]);
            }
        });

        $this->showForm = false;
        $this->dispatch('toast', message: 'PO langsung dibuat — stok & tagihan diperbarui.', type: 'success');
    }

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
            $this->dispatch('toast', type: 'error', message: 'Pengajuan berstatus ' . $p->statusLabel() . ' tidak bisa dihapus. Batalkan dulu jika masih menunggu/disetujui.');
            return;
        }
        DB::transaction(function () use ($p) {
            $p->items()->delete();
            $p->delete();
        });
        if ($this->detailId === $id) $this->detailId = null;
        $this->dispatch('toast', message: "{$p->no_pengajuan} dihapus.", type: 'success');
    }

    /** Batalkan / tarik pengajuan (diajukan/disetujui → dibatalkan). Arsip tetap ada. */
    public function batalkan(int $id): void
    {
        $p = PR::findOrFail($id);
        if (! $p->bisaDibatalkan()) {
            $this->dispatch('toast', type: 'error', message: 'Hanya pengajuan menunggu / disetujui yang bisa dibatalkan.');
            return;
        }
        if ($p->purchase_order_id) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan sudah jadi PO — tak bisa dibatalkan.');
            return;
        }
        $p->update([
            'status'       => 'dibatalkan',
            'alasan_tolak' => null,
        ]);
        $this->dispatch('toast', message: "{$p->no_pengajuan} dibatalkan — ditarik dari antrean manajer SIM.", type: 'success');
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

    // Persetujuan (setujui/tolak/revisi) HANYA di manajer SIM — apotek tidak menyetujui sendiri.

    // ── Input Faktur Pengadaan (realisasi → PO) ─────────────────
    /** Buka modal input faktur — hanya untuk pengajuan DISETUJUI yang belum ada faktur/PO. */
    public function mintaRealisasi(int $id): void
    {
        $p = PR::with('items')->findOrFail($id);
        if (! $p->bisaRealisasi()) {
            $this->dispatch('toast', type: 'error', message: 'Hanya pengajuan DISETUJUI yang bisa dibuatkan faktur/PO.');
            return;
        }
        if (! $p->distributor_id) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan belum punya distributor — tak bisa jadi PO.');
            return;
        }
        if ($p->items->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan tidak punya item — tak bisa direalisasikan.');
            return;
        }
        if ($p->items->contains(fn ($it) => empty($it->obat_id))) {
            $this->dispatch('toast', type: 'error', message: 'Ada item obat yang belum ada di katalog. Tambahkan obatnya ke Katalog dulu.');
            return;
        }
        $this->fakturPrId    = $p->id;
        $this->fakturMode    = 'buat';
        $this->nomorFaktur   = '';
        $this->tanggalFaktur = now()->format('Y-m-d');
        // Tarik item disetujui sbg DEFAULT aktual (qty/harga bisa disesuaikan bila barang beda).
        $this->fakturRows = $p->items->map(fn ($it) => [
            'item_id'        => $it->id,
            'obat_id'        => (int) $it->obat_id,
            'nama_obat'      => $it->nama_obat,
            'tipe_obat'      => $it->tipe_obat,
            'app_box'        => (int) $it->jumlah_box,        // disetujui (referensi selisih)
            'app_harga'      => (float) $it->harga_per_box,
            'jumlah_box'     => (int) $it->jumlah_box,        // aktual (editable)
            'isi_per_box'    => (int) $it->isi_per_box,
            'harga_per_box'  => (float) $it->harga_per_box,
            'subtotal'       => (float) $it->subtotal_beli,
            'klaim_bpjs_per_unit' => (float) $it->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi' => (float) ($it->faktor_jasa_farmasi ?? 1.15),
            'tanggal_kadaluarsa'  => optional($it->tanggal_kadaluarsa)->format('Y-m-d') ?? '',
        ])->all();
        $this->resetValidation();
        $this->showFaktur    = true;
    }

    /** Recalc subtotal baris faktur saat qty/harga aktual diubah. */
    public function updatedFakturRows($value, $key): void
    {
        [$i, $field] = array_pad(explode('.', $key), 2, null);
        if ($field === null || ! isset($this->fakturRows[(int) $i])) return;
        $i = (int) $i;
        $box  = max(0, (int) ($this->fakturRows[$i]['jumlah_box'] ?? 0));
        $harga = (float) ($this->fakturRows[$i]['harga_per_box'] ?? 0);
        $this->fakturRows[$i]['subtotal'] = $box * $harga;
    }

    /** Ringkasan faktur: total disetujui vs total aktual (selisih). */
    #[Computed]
    public function fakturTotal(): array
    {
        $app = 0.0; $act = 0.0;
        foreach ($this->fakturRows as $r) {
            $app += (int) ($r['app_box'] ?? 0) * (float) ($r['app_harga'] ?? 0);
            $act += (int) ($r['jumlah_box'] ?? 0) * (float) ($r['harga_per_box'] ?? 0);
        }
        return ['disetujui' => $app, 'aktual' => $act, 'selisih' => $act - $app];
    }

    public function tutupFaktur(): void { $this->showFaktur = false; $this->fakturPrId = null; $this->fakturMode = 'buat'; }

    /** Pengajuan yang sedang di-input-faktur (data ditarik untuk ringkasan modal). */
    #[Computed]
    public function fakturPr()
    {
        return $this->fakturPrId ? PR::with(['items', 'distributor'])->find($this->fakturPrId) : null;
    }

    /** Buat PO dari pengajuan disetujui + faktur yang diinput. Data ditarik dari pengajuan. */
    public function konfirmRealisasi(): void
    {
        $this->validate(
            ['nomorFaktur' => 'required|string|max:100', 'tanggalFaktur' => 'required|date'],
            ['nomorFaktur.required' => 'Nomor faktur/invoice wajib diisi.', 'tanggalFaktur.required' => 'Tanggal faktur wajib.'],
            ['nomorFaktur' => 'nomor faktur', 'tanggalFaktur' => 'tanggal faktur']
        );
        if ($this->fakturMode === 'lengkapi') {
            // Legacy: PO sudah ada tapi faktur kosong → isi faktur ke PO yang sudah ada.
            $p = PR::with('purchaseOrder')->findOrFail($this->fakturPrId);
            if ($p->purchaseOrder) {
                $p->purchaseOrder->update(['nomor_invoice' => $this->nomorFaktur, 'tanggal_po' => $this->tanggalFaktur]);
            }
            $this->showFaktur = false; $this->fakturPrId = null; $this->fakturMode = 'buat';
            $this->dispatch('toast', message: "Faktur {$this->nomorFaktur} dilengkapi ke PO {$p->no_pengajuan}.", type: 'success');
            return;
        }
        $this->realisasi((int) $this->fakturPrId, $this->nomorFaktur, $this->tanggalFaktur);
    }

    /** Lengkapi faktur untuk pengajuan yang SUDAH direalisasi tapi PO-nya belum ada nomor faktur (legacy). */
    public function mintaLengkapiFaktur(int $prId): void
    {
        $p = PR::with('purchaseOrder')->findOrFail($prId);
        if ($p->status !== 'direalisasi' || ! $p->purchase_order_id) {
            $this->dispatch('toast', type: 'error', message: 'Hanya pengajuan yang sudah jadi PO yang bisa dilengkapi fakturnya.');
            return;
        }
        $this->fakturPrId    = $p->id;
        $this->fakturMode    = 'lengkapi';
        $this->nomorFaktur   = (string) ($p->purchaseOrder->nomor_invoice ?? '');
        $this->tanggalFaktur = optional($p->purchaseOrder->tanggal_po)->format('Y-m-d') ?: now()->format('Y-m-d');
        $this->resetValidation();
        $this->showFaktur    = true;
    }

    // ── Realisasi → Purchase Order (gerbang belanja) ────────────
    protected function realisasi(int $id, ?string $faktur = null, ?string $tglFaktur = null): void
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
        // K7: item kosong → jangan buat PO kosong.
        if ($p->items->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan tidak punya item — tak bisa direalisasikan.');
            return;
        }
        // K2: item obat baru (belum di katalog) → PO item wajib obat_id. Beri pesan JELAS (bukan gagal senyap).
        if ($p->items->contains(fn ($it) => empty($it->obat_id))) {
            $this->dispatch('toast', type: 'error', message: 'Ada item obat yang belum ada di katalog. Tambahkan obatnya ke Katalog dulu, lalu edit & ajukan ulang.');
            return;
        }

        // Nilai AKTUAL dari faktur (bisa beda dari yang disetujui). Hanya baris valid (obat + box>0).
        $rows = collect($this->fakturRows)
            ->filter(fn ($r) => (int) ($r['obat_id'] ?? 0) > 0 && (int) ($r['jumlah_box'] ?? 0) > 0)
            ->values()->all();
        if (empty($rows)) {
            $this->dispatch('toast', type: 'error', message: 'Minimal 1 baris dengan obat & jumlah box > 0.');
            return;
        }

        $no = $p->no_pengajuan;
        $tglPo = $tglFaktur ?: now()->toDateString();
        try {
            DB::transaction(function () use ($id, $faktur, $tglPo, $rows) {
                // K1: kunci baris + re-check DI DALAM transaksi → idempoten, anti double-PO/stok/tagihan.
                $p = PR::whereKey($id)->lockForUpdate()->first();
                if (! $p || $p->status !== 'disetujui' || $p->purchase_order_id) {
                    throw new \RuntimeException('SUDAH_DIREALISASI');
                }

                $totalAktual = array_sum(array_map(fn ($r) => (int) $r['jumlah_box'] * (float) $r['harga_per_box'], $rows));
                $po = PurchaseOrder::create([
                    'distributor_id'   => $p->distributor_id,
                    'nomor_invoice'    => $faktur ?: null,
                    'tanggal_po'       => $tglPo,
                    'total_nilai'      => $totalAktual,           // nilai AKTUAL faktur
                    'catatan'          => 'Realisasi pengajuan ' . $p->no_pengajuan,
                    'status_bayar'     => 'belum',
                    // Jejak pembuat = pemohon pengajuan (yang menginisiasi pengadaan).
                    'dibuat_oleh_id'   => $p->pemohon_id,
                    'dibuat_oleh_nama' => $p->pemohon_nama,
                    'sumber'           => 'pengajuan',
                ]);

                foreach ($rows as $r) {
                    $box   = (int) $r['jumlah_box'];
                    $isi   = max(1, (int) $r['isi_per_box']);
                    $hbox  = (float) $r['harga_per_box'];
                    $sub   = $box * $hbox;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'obat_id'           => (int) $r['obat_id'],
                        'tipe_obat'         => $r['tipe_obat'] ?? 'kronis',
                        'jumlah_box'        => $box,
                        'isi_per_box'       => $isi,
                        'harga_per_box'     => $hbox,
                        'subtotal'          => $sub,
                    ]);
                    $upd = [
                        'harga_beli_per_unit' => $hbox / $isi,     // harga AKTUAL per unit
                        'sumber_harga'        => 'PO',
                        'stok_aktual'         => DB::raw('stok_aktual + ' . ($box * $isi)),
                    ];
                    if (! empty($r['tanggal_kadaluarsa'])) $upd['tanggal_kadaluarsa'] = $r['tanggal_kadaluarsa'];
                    Obat::where('id', (int) $r['obat_id'])->update($upd);
                }

                // Auto-split tagihan per tipe dari nilai AKTUAL.
                $perTipe = [];
                foreach ($rows as $r) {
                    $t = ($r['tipe_obat'] ?? 'kronis') === 'kronis' ? 'kronis' : 'non_kronis';
                    $perTipe[$t] = ($perTipe[$t] ?? 0) + (int) $r['jumlah_box'] * (float) $r['harga_per_box'];
                }
                foreach ($perTipe as $tipeTag => $total) {
                    if ($total <= 0) continue;
                    Tagihan::create([
                        'purchase_order_id'   => $po->id,
                        'distributor_id'      => $p->distributor_id,
                        'nomor_tagihan'       => Tagihan::generateNomor($tipeTag),
                        'tipe_obat'           => $tipeTag,
                        'periode_bulan'       => now()->format('Y-m'),
                        'tanggal_tagihan'     => $tglPo,
                        'tanggal_jatuh_tempo' => \Carbon\Carbon::parse($tglPo)->addDays(30)->toDateString(),
                        'total_tagihan'       => (int) $total,
                        'status'              => 'belum_bayar',
                    ]);
                }

                $p->update(['status' => 'direalisasi', 'purchase_order_id' => $po->id]);
            });
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'error', message: 'Pengajuan sudah direalisasikan sebelumnya (dicegah dobel).');
            return;
        }

        $this->showFaktur = false;
        $this->fakturPrId = null;
        $this->fakturRows = [];
        $fakturTxt = $faktur ? " (faktur {$faktur})" : '';
        $this->dispatch('toast', message: "{$no} → PO dibuat{$fakturTxt}. Stok & tagihan diperbarui.", type: 'success');
    }

    public function render()
    {
        return view('livewire.pengajuan-pengadaan');
    }
}
