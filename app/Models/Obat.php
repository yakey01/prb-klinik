<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obat extends Model
{
    use HasFactory;
    protected $table = 'obat';
    protected $fillable = [
        'nama_obat','kode_obat','kategori_diagnosis','tipe_obat',
        'jumlah_pasien','unit_per_bulan','satuan',
        'harga_beli_per_unit','harga_jual_per_unit','sumber_harga',
        'klaim_bpjs_per_unit','faktor_jasa_farmasi',
        'is_active',
        'stok_aktual','stok_minimum','tanggal_kadaluarsa',
    ];
    protected $casts = [
        'is_active'          => 'boolean',
        'tanggal_kadaluarsa' => 'date',
        'jumlah_pasien'      => 'integer',
        'stok_aktual'        => 'integer',
        'stok_minimum'       => 'integer',
    ];

    // ── Computed attributes ──────────────────────────────────────────

    public function bayarBpjs(): Attribute
    {
        return Attribute::get(
            fn () => round($this->klaim_bpjs_per_unit * $this->faktor_jasa_farmasi, 2)
        );
    }

    public function pendapatanBulan(): Attribute
    {
        return Attribute::get(function () {
            if ($this->tipe_obat === 'non_kronis') {
                return round(($this->harga_jual_per_unit ?? 0) * $this->unit_per_bulan, 2);
            }
            return round($this->bayar_bpjs * $this->unit_per_bulan, 2);
        });
    }

    public function biayaBulan(): Attribute
    {
        return Attribute::get(
            fn () => round($this->harga_beli_per_unit * $this->unit_per_bulan, 2)
        );
    }

    public function laba(): Attribute
    {
        return Attribute::get(
            fn () => round($this->pendapatan_bulan - $this->biaya_bulan, 2)
        );
    }

    public function isKronis(): bool   { return $this->tipe_obat === 'kronis'; }
    public function isNonKronis(): bool { return $this->tipe_obat === 'non_kronis'; }

    public function scopeKronis($q)    { return $q->where('tipe_obat', 'kronis'); }
    public function scopeNonKronis($q) { return $q->where('tipe_obat', 'non_kronis'); }

    public function statusLaba(): Attribute
    {
        return Attribute::get(function () {
            $l = $this->laba;
            if ($l > 0)  return 'Laba';
            if ($l < 0)  return 'Rugi';
            return 'Perlu Cek';
        });
    }

    public function stokStatus(): Attribute
    {
        return Attribute::get(function () {
            if ($this->stok_aktual <= 0)            return 'habis';
            if ($this->stok_aktual <= $this->stok_minimum) return 'kritis';
            return 'aman';
        });
    }

    public function kadaluarsaStatus(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tanggal_kadaluarsa) return null;
            $days = now()->diffInDays($this->tanggal_kadaluarsa, false);
            if ($days < 0)   return 'kadaluarsa';
            if ($days <= 30) return 'segera';
            if ($days <= 90) return 'perhatian';
            return 'aman';
        });
    }
}
