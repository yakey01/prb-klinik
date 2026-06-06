<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaOperasional extends Model
{
    protected $table = 'biaya_operasional';

    protected $fillable = [
        'bulan', 'tahun',
        'biaya_sdm', 'biaya_utilitas', 'biaya_administrasi',
        'biaya_sewa', 'biaya_lainnya',
    ];

    protected $casts = [
        'biaya_sdm'           => 'float',
        'biaya_utilitas'      => 'float',
        'biaya_administrasi'  => 'float',
        'biaya_sewa'          => 'float',
        'biaya_lainnya'       => 'float',
    ];

    public function getTotalAttribute(): float
    {
        return $this->biaya_sdm + $this->biaya_utilitas
             + $this->biaya_administrasi + $this->biaya_sewa
             + $this->biaya_lainnya;
    }

    public static function currentMonth(): self
    {
        return self::firstOrCreate(
            ['bulan' => now()->month, 'tahun' => now()->year],
            [
                'biaya_sdm'          => 3000000,
                'biaya_utilitas'     => 500000,
                'biaya_administrasi' => 300000,
                'biaya_sewa'         => 1000000,
                'biaya_lainnya'      => 200000,
            ]
        );
    }
}
