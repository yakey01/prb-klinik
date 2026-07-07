<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanHarga extends Model
{
    protected $table = 'pengaturan_harga';
    protected $fillable = ['margin_umum_default', 'auto_hitung_jual'];
    protected $casts = [
        'margin_umum_default' => 'float',
        'auto_hitung_jual'    => 'boolean',
    ];

    /** Ambil baris pengaturan tunggal (buat default bila belum ada). */
    public static function get(): self
    {
        return static::first() ?? static::create([
            'margin_umum_default' => 0.20,
            'auto_hitung_jual'    => true,
        ]);
    }
}
