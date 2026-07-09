<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Arsip 1 pembayaran tagihan (cicilan/parsial + jejak audit). */
class PembayaranTagihan extends Model
{
    protected $table = 'pembayaran_tagihan';

    protected $fillable = [
        'tagihan_id', 'tanggal', 'waktu', 'metode', 'bank_nama', 'nomor_referensi',
        'rekening_tujuan', 'atas_nama', 'jumlah', 'link_bukti', 'link_faktur',
        'pemutihan', 'catatan', 'dicatat_oleh',
        'dibatalkan', 'dibatalkan_at', 'dibatalkan_oleh', 'alasan_batal',
        'diubah_at', 'diubah_oleh',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'jumlah'        => 'decimal:2',
        'pemutihan'     => 'boolean',
        'dibatalkan'    => 'boolean',
        'dibatalkan_at' => 'datetime',
        'diubah_at'     => 'datetime',
    ];

    public function tagihan(): BelongsTo { return $this->belongsTo(Tagihan::class); }

    /** Hanya pembayaran yang masih berlaku (belum dibatalkan). */
    public function scopeAktif($q) { return $q->where('dibatalkan', false); }

    public function metodeLabel(): string
    {
        return match ($this->metode) {
            'transfer_bank' => 'Transfer Bank',
            'tunai'         => 'Tunai',
            'qris'          => 'QRIS',
            'giro'          => 'Giro',
            'cek'           => 'Cek',
            default         => 'Lainnya',
        };
    }

    /** Metode yang WAJIB melampirkan bukti (link) — semua kecuali tunai. */
    public static function wajibBukti(string $metode): bool
    {
        return $metode !== 'tunai';
    }
}
