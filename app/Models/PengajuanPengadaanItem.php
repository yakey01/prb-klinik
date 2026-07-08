<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPengadaanItem extends Model
{
    protected $table = 'pengajuan_pengadaan_items';

    protected $fillable = [
        'pengajuan_pengadaan_id', 'obat_id', 'nama_obat', 'tipe_obat',
        'jumlah_box', 'isi_per_box', 'harga_per_box', 'harga_per_unit', 'subtotal_beli',
        'klaim_bpjs_per_unit', 'faktor_jasa_farmasi', 'estimasi_klaim',
        'tanggal_kadaluarsa', 'catatan',
    ];

    protected $casts = [
        'tanggal_kadaluarsa'  => 'date',
        'harga_per_box'       => 'decimal:2',
        'harga_per_unit'      => 'decimal:2',
        'subtotal_beli'       => 'decimal:2',
        'klaim_bpjs_per_unit' => 'decimal:2',
        'estimasi_klaim'      => 'decimal:2',
    ];

    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanPengadaan::class, 'pengajuan_pengadaan_id'); }
    public function obat(): BelongsTo      { return $this->belongsTo(Obat::class); }
}
