<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPengambilan extends Model
{
    protected $table = 'item_pengambilan';
    protected $fillable = [
        'pengambilan_obat_id', 'obat_id', 'jumlah_unit', 'satuan', 'catatan',
        'harga_beli_snapshot', 'harga_klaim_bpjs_snapshot', 'faktor_jasa_farmasi_snapshot',
    ];
    protected $casts = [
        'jumlah_unit'                  => 'integer',
        'harga_beli_snapshot'          => 'float',
        'harga_klaim_bpjs_snapshot'    => 'float',
        'faktor_jasa_farmasi_snapshot' => 'float',
    ];

    public function pengambilan(): BelongsTo
    {
        return $this->belongsTo(PengambilanObat::class, 'pengambilan_obat_id');
    }
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    // Biaya HPP aktual untuk item ini
    public function getTotalBiayaAttribute(): float
    {
        return round($this->jumlah_unit * $this->harga_beli_snapshot, 2);
    }

    // Proyeksi klaim BPJS untuk item ini (unit × klaim × pengali jasa farmasi ternormalisasi)
    public function getProyeksiKlaimAttribute(): float
    {
        return round($this->jumlah_unit * $this->harga_klaim_bpjs_snapshot
            * Obat::jfMultiplier($this->faktor_jasa_farmasi_snapshot), 2);
    }
}
