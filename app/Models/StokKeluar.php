<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokKeluar extends Model
{
    protected $table = 'stok_keluar';

    protected $fillable = [
        'obat_id', 'tanggal_keluar', 'jumlah_unit', 'satuan',
        'harga_beli_snapshot', 'harga_jual_per_unit',
        'keterangan', 'dicatat_oleh',
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
