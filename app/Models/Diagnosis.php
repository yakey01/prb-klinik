<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diagnosis extends Model
{
    use HasFactory;
    protected $table = 'diagnoses';
    protected $fillable = ['nama', 'warna', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public static function aktif(): \Illuminate\Support\Collection
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('nama')->pluck('nama');
    }
}
