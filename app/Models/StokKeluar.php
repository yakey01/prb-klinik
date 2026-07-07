<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokKeluar extends Model
{
    protected $table = 'stok_keluar';

    protected $fillable = [
        'ref',
        'obat_id', 'tanggal_keluar', 'jumlah_unit', 'stok_sebelum', 'stok_sesudah', 'satuan',
        'harga_beli_snapshot', 'harga_jual_per_unit',
        'keterangan', 'dicatat_oleh',
        'sumber', 'pengambilan_obat_id', 'pasien_id',
    ];

    protected $casts = [
        'tanggal_keluar'      => 'date',
        'jumlah_unit'         => 'integer',
        'harga_beli_snapshot' => 'float',
        'harga_jual_per_unit' => 'float',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function pengambilanObat(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PengambilanObat::class, 'pengambilan_obat_id');
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pasien::class, 'pasien_id');
    }

    public function scopeKronis($q)
    {
        return $q->where('sumber', 'pengambilan');
    }

    public function scopeNonKronis($q)
    {
        return $q->where('sumber', 'manual');
    }

    /**
     * Segmen TUNAI/UMUM = penjualan kanal tunai: input manual PRB + resep UMUM dari SIM.
     * SENGAJA mengecualikan sumber='pengambilan' (kronis BPJS) karena baris itu
     * ditulis berbarengan dengan item_pengambilan → sudah dihitung di Segmen A.
     * Tanpa filter ini, laporan Segmen B double-count pengambilan kronis sebagai "tunai".
     */
    public function scopeTunai($q)
    {
        return $q->whereIn('sumber', ['manual', 'sim_resep']);
    }

    public function getTotalPendapatanAttribute(): float
    {
        return round($this->jumlah_unit * $this->harga_jual_per_unit, 2);
    }

    public function getTotalBiayaAttribute(): float
    {
        return round($this->jumlah_unit * $this->harga_beli_snapshot, 2);
    }

    public function getLabaAttribute(): float
    {
        return $this->total_pendapatan - $this->total_biaya;
    }
}
