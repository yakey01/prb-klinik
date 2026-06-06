<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResepPasien extends Model
{
    protected $table = 'resep_pasien';
    protected $fillable = ['pasien_id','obat_id','jumlah_default','satuan','catatan','urutan','is_aktif'];
    protected $casts = ['is_aktif' => 'boolean'];

    public function pasien(): BelongsTo { return $this->belongsTo(Pasien::class, 'pasien_id'); }
    public function obat(): BelongsTo  { return $this->belongsTo(Obat::class, 'obat_id'); }
}
