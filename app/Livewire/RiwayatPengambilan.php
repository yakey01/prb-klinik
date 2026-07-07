<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\PengambilanObat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Riwayat Pengambilan Obat (global) — daftar semua pengambilan + detail per-obat untung/rugi.
 * Sumber: pengambilan_obat + item_pengambilan (snapshot harga saat penyerahan).
 *
 * CRUD: edit (tanggal/status/jadwal/catatan) & hapus (soft delete) — utamanya untuk
 * membersihkan entri duplikat. Soft-deleted bisa dipulihkan kembali.
 */
class RiwayatPengambilan extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterBulan    = '';
    public bool   $filterDuplikat = false;
    public bool   $showDeleted    = false;
    public ?int   $expandedId     = null;
    public string $sortField      = 'tanggal';   // tanggal | pasien | item | laba
    public string $sortDir        = 'desc';      // asc | desc

    // ── Edit state ──
    public ?int   $editId      = null;
    public string $editTanggal = '';
    public string $editJadwal  = '';
    public string $editStatus  = 'selesai';
    public string $editCatatan = '';

    public const STATUS = [
        'dijadwalkan' => 'Dijadwalkan',
        'selesai'     => 'Selesai',
        'batal'       => 'Batal',
    ];

    public function updatingSearch()        { $this->resetPage(); }
    public function updatingFilterBulan()   { $this->resetPage(); }
    public function updatingFilterDuplikat(){ $this->resetPage(); }
    public function updatingShowDeleted()   { $this->resetPage(); }

    /** Klik header: toggle arah bila field sama, atau ganti field dgn arah default. */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = $field === 'pasien' ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function toggleDetail(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    private function canEdit(): bool
    {
        return (bool) auth()->user()?->canEdit();
    }

    /** ID pengambilan yang duplikat (pasien + tanggal sama, >1 entri aktif). */
    #[Computed]
    public function duplikatIds(): array
    {
        return PengambilanObat::query()
            ->where('status', 'selesai')
            ->selectRaw('GROUP_CONCAT(id) AS ids')
            ->groupBy('pasien_id', 'tanggal_pengambilan')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('ids')
            ->flatMap(fn ($ids) => explode(',', (string) $ids))
            ->map(fn ($x) => (int) $x)
            ->all();
    }

    #[Computed]
    public function duplikatCount(): int
    {
        return count($this->duplikatIds);
    }

    #[Computed]
    public function rows()
    {
        // RIWAYAT PENYERAHAN = hanya yang SUDAH diserahkan (status 'selesai').
        // Jadwal mendatang (status 'dijadwalkan') TIDAK ditampilkan di sini — itu milik
        // halaman Jadwal & Reminder. Tanpa filter ini, 1 penyerahan tampak "double"
        // (selesai + auto jadwal-berikutnya) dan rawan jadwal terhapus tak sengaja.
        $q = PengambilanObat::query()->with(['pasien', 'items.obat'])
            ->where('status', 'selesai');

        if ($this->showDeleted) {
            $q->onlyTrashed();
        }
        if ($this->filterDuplikat) {
            $q->whereIn('id', $this->duplikatIds ?: [0]);
        }

        $q->when($this->search, fn ($w) => $w->whereHas('pasien',
                fn ($p) => $p->where('nama', 'like', '%' . $this->search . '%')
                             ->orWhere('no_bpjs', 'like', '%' . $this->search . '%')))
          ->when($this->filterBulan, fn ($w) =>
                $w->whereRaw("DATE_FORMAT(tanggal_pengambilan,'%Y-%m') = ?", [$this->filterBulan]));

        // ── Sortir (header kolom) ──
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';
        switch ($this->sortField) {
            case 'pasien':
                $q->orderBy(DB::table('pasien')->whereColumn('pasien.id', 'pengambilan_obat.pasien_id')->select('nama'), $dir);
                break;
            case 'item':
                $q->orderBy('total_item', $dir);
                break;
            case 'laba':
                $q->orderBy(DB::table('item_pengambilan')
                    ->whereColumn('item_pengambilan.pengambilan_obat_id', 'pengambilan_obat.id')
                    ->selectRaw('COALESCE(SUM(jumlah_unit * (harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('faktor_jasa_farmasi_snapshot') . ' - harga_beli_snapshot)), 0)'), $dir);
                break;
            default: // tanggal
                $q->orderBy('tanggal_pengambilan', $dir);
        }

        return $q->orderByDesc('id')->paginate(15);
    }

    /** Total P&L global (semua pengambilan selesai, ber-snapshot, belum dihapus). */
    #[Computed]
    public function totals(): array
    {
        $base = DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->where('po.status', 'selesai')
            ->whereNull('po.deleted_at')
            ->when($this->search, fn ($q) => $q->whereExists(fn ($s) =>
                $s->select(DB::raw(1))->from('pasien')
                  ->whereColumn('pasien.id', 'po.pasien_id')
                  ->where('pasien.nama', 'like', '%' . $this->search . '%')))
            ->when($this->filterBulan, fn ($q) =>
                $q->whereRaw("DATE_FORMAT(po.tanggal_pengambilan,'%Y-%m') = ?", [$this->filterBulan]));

        $row = (clone $base)->selectRaw('
            COALESCE(SUM(ip.jumlah_unit * ip.harga_beli_snapshot), 0) as biaya,
            COALESCE(SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . '), 0) as klaim,
            COALESCE(SUM(ip.jumlah_unit), 0) as item,
            COUNT(DISTINCT po.id) as kunjungan
        ')->first();

        $biaya = (float) ($row->biaya ?? 0);
        $klaim = (float) ($row->klaim ?? 0);
        $laba  = $klaim - $biaya;

        return [
            'biaya'     => $biaya,
            'klaim'     => $klaim,
            'laba'      => $laba,
            'margin'    => $klaim > 0 ? round($laba / $klaim * 100, 1) : 0,
            'kunjungan' => (int) ($row->kunjungan ?? 0),
            'item'      => (int) ($row->item ?? 0),
        ];
    }

    /* ── EDIT ─────────────────────────────────────────────────────── */
    public function openEdit(int $id): void
    {
        if (! $this->canEdit()) {
            $this->dispatch('toast', message: 'Tidak punya akses mengubah riwayat.', type: 'error');
            return;
        }
        $po = PengambilanObat::withTrashed()->findOrFail($id);
        $this->editId      = $po->id;
        $this->editTanggal = optional($po->tanggal_pengambilan)->format('Y-m-d') ?? '';
        $this->editJadwal  = optional($po->jadwal_berikutnya)->format('Y-m-d') ?? '';
        $this->editStatus  = $po->status ?? 'selesai';
        $this->editCatatan = (string) $po->catatan;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'editTanggal', 'editJadwal', 'editStatus', 'editCatatan']);
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        if (! $this->canEdit() || ! $this->editId) return;

        $this->validate([
            'editTanggal' => 'required|date',
            'editJadwal'  => 'nullable|date',
            'editStatus'  => 'required|in:dijadwalkan,selesai,batal',
            'editCatatan' => 'nullable|string|max:500',
        ], messages: [
            'editTanggal.required' => 'Tanggal pengambilan wajib diisi.',
            'editStatus.in'        => 'Status tidak valid.',
        ]);

        $po  = PengambilanObat::withTrashed()->findOrFail($this->editId);
        $old = $po->only('tanggal_pengambilan', 'jadwal_berikutnya', 'status', 'catatan');
        $po->update([
            'tanggal_pengambilan' => $this->editTanggal,
            'jadwal_berikutnya'   => $this->editJadwal ?: null,
            'status'              => $this->editStatus,
            'catatan'             => $this->editCatatan ?: null,
        ]);
        ActivityLog::record('updated', "Riwayat pengambilan #{$po->id} diperbarui", 'PengambilanObat', $po->id,
            $old, $po->only('tanggal_pengambilan', 'jadwal_berikutnya', 'status', 'catatan'));
        $this->cancelEdit();
        $this->dispatch('toast', message: 'Riwayat pengambilan diperbarui.', type: 'success');
    }

    /* ── DELETE (soft) / RESTORE ──────────────────────────────────── */
    public function deletePengambilan(int $id): void
    {
        if (! $this->canEdit()) {
            $this->dispatch('toast', message: 'Tidak punya akses menghapus riwayat.', type: 'error');
            return;
        }
        $po   = PengambilanObat::findOrFail($id);
        $nama = $po->pasien->nama ?? '—';
        $tgl  = optional($po->tanggal_pengambilan)->format('d M Y');
        ActivityLog::record('deleted', "Riwayat pengambilan #{$id} dihapus: {$nama} @ {$tgl}", 'PengambilanObat', $id);
        $po->delete();
        if ($this->expandedId === $id) $this->expandedId = null;
        $this->dispatch('toast', message: "Pengambilan \"{$nama}\" ({$tgl}) dihapus. Bisa dipulihkan via filter 'Terhapus'.", type: 'success');
    }

    public function restorePengambilan(int $id): void
    {
        if (! $this->canEdit()) return;
        $po = PengambilanObat::onlyTrashed()->findOrFail($id);
        $po->restore();
        ActivityLog::record('updated', "Riwayat pengambilan #{$id} dipulihkan", 'PengambilanObat', $id);
        $this->dispatch('toast', message: 'Pengambilan dipulihkan.', type: 'success');
    }

    public function render()
    {
        return view('livewire.riwayat-pengambilan', ['statusList' => self::STATUS]);
    }
}
