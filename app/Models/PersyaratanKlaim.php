<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PersyaratanKlaim extends Model
{
    protected $table = 'persyaratan_klaim';
    protected $fillable = [
        'diagnosis','nama_syarat','deskripsi','tipe',
        'periode_bulan','is_wajib','urutan','is_aktif',
    ];
    protected $casts = ['is_wajib' => 'boolean', 'is_aktif' => 'boolean'];

    public function scopeAktif($q) { return $q->where('is_aktif', true); }
    public function scopeByDiagnosis($q, string $diag) { return $q->where('diagnosis', $diag); }

    public static function forDiagnosis(?string $diag): \Illuminate\Support\Collection
    {
        if (!$diag) return collect();
        return static::aktif()->byDiagnosis($diag)->orderBy('urutan')->orderBy('id')->get();
    }
}
