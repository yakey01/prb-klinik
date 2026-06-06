<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'purchase_order_id', 'distributor_id', 'nomor_tagihan',
        'tipe_obat', 'periode_bulan', 'tanggal_tagihan',
        'tanggal_jatuh_tempo', 'total_tagihan', 'status',
        'tanggal_bayar', 'jumlah_dibayar', 'catatan_bayar',
    ];

    protected $casts = [
        'tanggal_tagihan'      => 'date',
        'tanggal_jatuh_tempo'  => 'date',
        'tanggal_bayar'        => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    // ── Computed attributes ───────────────────────────────────────────

    public function getSisaTagihanAttribute(): int
    {
        return max(0, $this->total_tagihan - $this->jumlah_dibayar);
    }

    public function getAgingAttribute(): string
    {
        if ($this->status === 'lunas') return 'lunas';
        $hari = now()->diffInDays($this->tanggal_jatuh_tempo, false);
        if ($hari > 30)  return 'aman';
        if ($hari > 7)   return 'perhatian';
        if ($hari >= 0)  return 'segera';
        return 'overdue';
    }

    public function getAgingHariAttribute(): int
    {
        return (int) now()->diffInDays($this->tanggal_jatuh_tempo, false);
    }

    public function getLabelTipeAttribute(): string
    {
        return $this->tipe_obat === 'kronis' ? 'Kronis' : 'Non-Kronis';
    }

    // ── Static helpers ────────────────────────────────────────────────

    public static function generateNomor(string $tipe): string
    {
        $prefix = $tipe === 'kronis' ? 'TGK' : 'TGN';
        $ym     = now()->format('Ym');
        $last   = static::where('nomor_tagihan', 'like', "{$prefix}-{$ym}-%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->nomor_tagihan, -4)) + 1 : 1;
        return "{$prefix}-{$ym}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeKronis($q)     { return $q->where('tipe_obat', 'kronis'); }
    public function scopeNonKronis($q)  { return $q->where('tipe_obat', 'non_kronis'); }
    public function scopeAktif($q)      { return $q->whereIn('status', ['belum_bayar','sebagian']); }
    public function scopeOverdue($q)    { return $q->where('status','!=','lunas')->where('tanggal_jatuh_tempo','<',now()->toDateString()); }
    public function scopePeriode($q, string $ym) { return $q->where('periode_bulan', $ym); }
}
