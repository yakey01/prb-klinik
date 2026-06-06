<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPengambilan extends Model
{
    protected $table = 'item_pengambilan';
    protected $fillable = ['pengambilan_obat_id','obat_id','jumlah_unit','satuan','catatan'];
    protected $casts = ['jumlah_unit' => 'integer'];

    public function pengambilan(): BelongsTo
    {
        return $this->belongsTo(PengambilanObat::class, 'pengambilan_obat_id');
    }
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
