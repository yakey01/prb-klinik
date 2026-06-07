<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasien extends Model
{
    use SoftDeletes;
    protected $table = 'pasien';
    protected $fillable = [
        'nama','no_bpjs','kategori_diagnosis','telepon','alamat',
        'tanggal_lahir','jenis_kelamin','is_aktif','catatan',
    ];
    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_aktif'      => 'boolean',
    ];

    public function pengambilan(): HasMany
    {
        return $this->hasMany(PengambilanObat::class, 'pasien_id');
    }

    public function resep(): HasMany
    {
        return $this->hasMany(ResepPasien::class, 'pasien_id')->orderBy('urutan')->orderBy('id');
    }

    public function resepAktif(): HasMany
    {
        return $this->resep()->where('is_aktif', true);
    }

    public function pengambilanTerakhir(): ?PengambilanObat
    {
        return $this->pengambilan()->latest('tanggal_pengambilan')->first();
    }

    public function scopeAktif($q) { return $q->where('is_aktif', true); }
}
