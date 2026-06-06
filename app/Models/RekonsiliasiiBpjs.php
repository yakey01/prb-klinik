<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RekonsiliasiiBpjs extends Model
{
    protected $table = 'rekonsiliasi_bpjs';
    protected $fillable = [
        'bulan','tahun','proyeksi_pendapatan','tagihan_diajukan','tagihan_dibayar',
        'status','catatan','tanggal_pengajuan','tanggal_pembayaran'
    ];
    protected $casts = [
        'tanggal_pengajuan'  => 'date',
        'tanggal_pembayaran' => 'date',
        'proyeksi_pendapatan'=> 'decimal:2',
        'tagihan_diajukan'   => 'decimal:2',
        'tagihan_dibayar'    => 'decimal:2',
    ];

    public function selisih(): Attribute
    {
        return Attribute::get(fn () => $this->tagihan_dibayar - $this->tagihan_diajukan);
    }

    public function namaBulan(): Attribute
    {
        return Attribute::get(fn () => \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F Y'));
    }

    public static function bulanLabels(): array
    {
        return [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    }
}
