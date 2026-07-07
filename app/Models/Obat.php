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
        'jumlah_pasien','unit_per_bulan','satuan','isi_per_box','bentuk_sediaan','komposisi',
        'harga_beli_per_unit','harga_jual_per_unit','sumber_harga',
        'klaim_bpjs_per_unit','faktor_jasa_farmasi','margin_umum',
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

    /**
     * GERBANG ANTI-RUGI (cegah klinik jual di bawah HPP).
     * Untuk obat umum/tunai (non_kronis): kalau harga jual = 0 ATAU di bawah harga beli
     * sementara HPP valid, harga jual otomatis di-set ulang = beli × (1 + margin_umum).
     * Ini menutup akar bug: harga beli naik tapi harga jual lupa dihitung ulang → rugi.
     * Harga jual manual yang SUDAH di atas HPP tidak diutak-atik.
     */
    protected static function booted(): void
    {
        static::saving(function (self $o) {
            if ($o->tipe_obat !== 'non_kronis') {
                return;
            }
            $beli = (float) $o->harga_beli_per_unit;
            $jual = (float) ($o->harga_jual_per_unit ?? 0);
            if ($beli > 0 && ($jual <= 0 || $jual < $beli)) {
                $margin = (float) ($o->margin_umum ?? 0);
                $o->harga_jual_per_unit = round($beli * (1 + $margin));
            }
        });
    }

    // ── GERBANG ANTI-MINUS ────────────────────────────────────────────

    /**
     * Kurangi stok secara ATOMIK & race-safe. Stok HANYA berkurang bila
     * mencukupi (kondisi WHERE stok_aktual >= qty) → mustahil membuat
     * stok_aktual < 0, bahkan saat banyak transaksi bersamaan.
     *
     * @return bool true bila berhasil dikurangi; false bila stok tak cukup.
     */
    public static function kurangiStok(int $id, int $qty): bool
    {
        if ($qty <= 0) {
            return true;
        }
        return static::where('id', $id)
            ->where('stok_aktual', '>=', $qty)
            ->decrement('stok_aktual', $qty) > 0;
    }

    // ── Computed attributes ──────────────────────────────────────────

    public function bayarBpjs(): Attribute
    {
        return Attribute::get(
            fn () => round($this->klaim_bpjs_per_unit * static::jfMultiplier($this->faktor_jasa_farmasi), 2)
        );
    }

    /**
     * Normalisasi faktor jasa farmasi → PENGALI klaim. SATU sumber kebenaran agar
     * SEMUA rumus klaim konsisten, apa pun cara faktor di-input/tersimpan:
     *   pecahan  0.28 = "+28%"  → ×1.28   (mayoritas data)
     *   pengali  1.15 = "×1.15" → ×1.15   (sudah pengali)
     *   1.00 = tanpa jasa (×1.0).  null/≤0/ngaco(>2, mis. 9.99) → default jasa +28% (×1.28).
     * Klaim dibayar = klaim_bpjs × jfMultiplier(faktor).
     */
    public static function jfMultiplier($f): float
    {
        $f = (float) $f;
        if ($f <= 0 || $f > 2) return 1.28;
        return $f < 1 ? 1 + $f : $f;
    }

    /** Fragmen SQL pengali jasa farmasi utk kolom $col (mis. 'o.faktor_jasa_farmasi'). */
    public static function jfSql(string $col): string
    {
        return "(CASE WHEN $col IS NULL OR $col <= 0 OR $col > 2 THEN 1.28"
            . " WHEN $col < 1 THEN 1 + $col ELSE $col END)";
    }

    /** Stok dalam box (pembulatan ke bawah) — untuk tampilan. */
    public function stokBox(): Attribute
    {
        return Attribute::get(function () {
            $isi = max(1, (int) ($this->isi_per_box ?? 1));
            return intdiv((int) $this->stok_aktual, $isi);
        });
    }

    /** Sisa item di luar box utuh (mis. 905 item, isi 100 → 5 item). */
    public function stokSisaItem(): Attribute
    {
        return Attribute::get(function () {
            $isi = max(1, (int) ($this->isi_per_box ?? 1));
            return (int) $this->stok_aktual % $isi;
        });
    }

    /** Label ramah: "905 tablet (9 box + 5)". */
    public function stokLabel(): Attribute
    {
        return Attribute::get(function () {
            $isi = max(1, (int) ($this->isi_per_box ?? 1));
            $sat = $this->satuan ?: 'item';
            if ($isi <= 1) return $this->stok_aktual . ' ' . $sat;
            $box  = intdiv((int) $this->stok_aktual, $isi);
            $sisa = (int) $this->stok_aktual % $isi;
            $boxStr = $box . ' box' . ($sisa > 0 ? ' + ' . $sisa : '');
            return $this->stok_aktual . ' ' . $sat . ' (' . $boxStr . ')';
        });
    }

    /** Harga jual ke pasien umum dihitung otomatis dari margin: beli × (1 + margin_umum). */
    public function hargaJualOtomatis(): Attribute
    {
        return Attribute::get(
            fn () => round(($this->harga_beli_per_unit ?? 0) * (1 + ($this->margin_umum ?? 0)), 0)
        );
    }

    /** Margin umum dalam persen (untuk tampilan). */
    public function marginUmumPersen(): Attribute
    {
        return Attribute::get(fn () => round(($this->margin_umum ?? 0) * 100, 1));
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

    /**
     * Laba per unit — profitabilitas intrinsik obat, lepas dari volume.
     * Tetap bermakna walau belum ada pasien (unit/bln = 0).
     */
    public function labaPerUnit(): Attribute
    {
        return Attribute::get(function () {
            $pendapatanUnit = $this->tipe_obat === 'non_kronis'
                ? ($this->harga_jual_per_unit ?? 0)
                : $this->bayar_bpjs;
            return round($pendapatanUnit - ($this->harga_beli_per_unit ?? 0), 2);
        });
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
