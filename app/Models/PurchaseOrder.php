<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'distributor_id','nomor_invoice','tanggal_po','total_nilai','catatan',
        'status_bayar','tanggal_jatuh_tempo','tanggal_bayar','jumlah_bayar',
    ];
    protected $casts = [
        'tanggal_po'          => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar'       => 'date',
        'total_nilai'         => 'decimal:2',
        'jumlah_bayar'        => 'decimal:2',
    ];

    public function distributor(): BelongsTo { return $this->belongsTo(Distributor::class); }
    public function items(): HasMany          { return $this->hasMany(PurchaseOrderItem::class); }
    public function tagihan(): HasMany        { return $this->hasMany(Tagihan::class); }

    public function sisaTagihan(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->total_nilai - $this->jumlah_bayar));
    }

    public function statusBayarLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->status_bayar) {
            'lunas'   => 'Lunas',
            'sebagian'=> 'Sebagian',
            default   => 'Belum Bayar',
        });
    }

    public function statusBayarBadge(): Attribute
    {
        return Attribute::get(fn () => match ($this->status_bayar) {
            'lunas'   => 'badge-laba',
            'sebagian'=> 'badge-est',
            default   => 'badge-rugi',
        });
    }
}
