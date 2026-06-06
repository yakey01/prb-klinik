<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'obat_id', 'tipe_obat',
        'jumlah_box', 'isi_per_box', 'harga_per_box', 'subtotal',
    ];

    protected $casts = [
        'harga_per_box' => 'float',
        'subtotal'      => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function getHargaPerUnitAttribute(): float
    {
        return $this->isi_per_box > 0
            ? $this->harga_per_box / $this->isi_per_box
            : 0;
    }
}
