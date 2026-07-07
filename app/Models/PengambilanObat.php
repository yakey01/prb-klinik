<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengambilanObat extends Model
{
    use SoftDeletes;
    protected $table = 'pengambilan_obat';
    protected $fillable = [
        'pasien_id','tanggal_pengambilan','jadwal_berikutnya',
        'status','total_item','dicatat_oleh','catatan',
        'checklist_json','persyaratan_ok',
        'ref_rme','sumber_resep',
    ];
    protected $casts = [
        'tanggal_pengambilan' => 'date',
        'jadwal_berikutnya'   => 'date',
        'checklist_json'      => 'array',
        'persyaratan_ok'      => 'boolean',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }
    public function items(): HasMany
    {
        return $this->hasMany(ItemPengambilan::class, 'pengambilan_obat_id');
    }
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

}
