<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengajuan Pengadaan (Purchase Requisition). Usulan belanja obat yang WAJIB
 * disetujui manajer sebelum jadi Purchase Order. Sumber tunggal status approval
 * (dibaca/ditulis manajer SIM via koneksi 'apotik').
 */
class PengajuanPengadaan extends Model
{
    protected $table = 'pengajuan_pengadaan';

    protected $fillable = [
        'no_pengajuan', 'tanggal', 'pemohon_id', 'pemohon_nama', 'distributor_id',
        'prioritas', 'justifikasi', 'status',
        'total_beli', 'total_estimasi_klaim', 'estimasi_laba', 'catatan',
        'approver_id', 'approver_nama', 'approver_sumber', 'approved_at',
        'catatan_approver', 'alasan_tolak', 'submitted_at', 'purchase_order_id', 'created_by',
    ];

    protected $casts = [
        'tanggal'              => 'date',
        'approved_at'          => 'datetime',
        'submitted_at'         => 'datetime',
        'total_beli'           => 'decimal:2',
        'total_estimasi_klaim' => 'decimal:2',
        'estimasi_laba'        => 'decimal:2',
    ];

    // ── Relations ────────────────────────────────────────────────
    public function items(): HasMany        { return $this->hasMany(PengajuanPengadaanItem::class); }
    public function distributor(): BelongsTo { return $this->belongsTo(Distributor::class); }
    public function pemohon(): BelongsTo     { return $this->belongsTo(User::class, 'pemohon_id'); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeStatus($q, string $s)  { return $q->where('status', $s); }
    public function scopeMenunggu($q)            { return $q->where('status', 'diajukan'); }
    public function scopeDisetujui($q)           { return $q->where('status', 'disetujui'); }

    // ── State machine helpers ────────────────────────────────────
    public function bisaDiajukan(): bool   { return in_array($this->status, ['draft', 'revisi'], true); }
    // Bisa diedit: draft/revisi (belum diajukan) + diajukan (menunggu ACC) + DISETUJUI yang
    // belum jadi PO (edit → butuh persetujuan ULANG manajer).
    public function bisaDiedit(): bool
    {
        return in_array($this->status, ['draft', 'revisi', 'diajukan'], true)
            || ($this->status === 'disetujui' && ! $this->purchase_order_id);
    }

    /** Edit pengajuan ini akan menggugurkan persetujuan & minta ACC ulang manajer? */
    public function editButuhReApprove(): bool
    {
        return $this->status === 'disetujui' && ! $this->purchase_order_id;
    }

    /** Sudah disetujui TAPI belum ada faktur/PO (belum direalisasi) → masih boleh diedit. */
    public function belumAdaFaktur(): bool
    {
        return $this->status === 'disetujui' && ! $this->purchase_order_id;
    }
    public function bisaDihapus(): bool    { return in_array($this->status, ['draft', 'revisi', 'ditolak', 'dibatalkan'], true); }
    public function bisaApprove(): bool    { return $this->status === 'diajukan'; }
    public function bisaRealisasi(): bool  { return $this->status === 'disetujui'; }
    public function bisaDibatalkan(): bool { return in_array($this->status, ['diajukan', 'disetujui'], true); }

    /** Nomor PR unik: PR-YYYYMM-#### (reset per bulan). */
    public static function generateNomor(): string
    {
        $ym   = now()->format('Ym');
        $last = static::where('no_pengajuan', 'like', "PR-{$ym}-%")->orderByDesc('id')->first();
        $seq  = $last ? ((int) substr($last->no_pengajuan, -4)) + 1 : 1;
        return "PR-{$ym}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** Hitung ulang ringkasan finansial dari item. */
    public function rekapUlang(): void
    {
        $beli       = (float) $this->items->sum('subtotal_beli');
        $klaim      = (float) $this->items->sum('estimasi_klaim');
        // Laba BPJS hanya dari item KRONIS (non-kronis/umum tak diklaim BPJS → tak masuk laba).
        $beliKronis = (float) $this->items->where('tipe_obat', 'kronis')->sum('subtotal_beli');
        $this->update([
            'total_beli'           => $beli,
            'total_estimasi_klaim' => $klaim,
            'estimasi_laba'        => $klaim - $beliKronis,
        ]);
    }

    // ── Presentasi ───────────────────────────────────────────────
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'       => 'Draft',
            'diajukan'    => 'Menunggu Persetujuan',
            'disetujui'   => 'Disetujui',
            'ditolak'     => 'Ditolak',
            'revisi'      => 'Perlu Revisi',
            'direalisasi' => 'Direalisasi (PO)',
            'dibatalkan'  => 'Dibatalkan',
            default       => ucfirst($this->status),
        };
    }

    /** Warna status (hex) untuk badge/kalender — selaras tema. */
    public function statusColor(): string
    {
        return match ($this->status) {
            'disetujui'   => '#3fcf8e',   // hijau
            'direalisasi' => '#5ce0a4',   // hijau terang
            'diajukan'    => '#5b9bd5',   // biru (menunggu)
            'revisi'      => '#e0a53a',   // emas
            'ditolak'     => '#e8645a',   // merah
            'dibatalkan'  => '#8a9a92',   // abu (dibatalkan)
            default       => '#8a9a92',   // abu (draft)
        };
    }

    public function prioritasLabel(): string
    {
        return match ($this->prioritas) {
            'urgent' => 'Urgent', 'segera' => 'Segera', default => 'Rutin',
        };
    }
}
