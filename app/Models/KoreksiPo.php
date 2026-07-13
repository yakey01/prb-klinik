<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Koreksi PO (perlu persetujuan manajer). Setelah disetujui, apply() merekonsiliasi
 * stok (delta unit) + total PO + tagihan per tipe, dengan jejak audit. Idempoten via
 * flag applied.
 */
class KoreksiPo extends Model
{
    protected $table = 'koreksi_po';

    protected $fillable = [
        'purchase_order_id', 'pemohon_nama', 'pemohon_id', 'alasan',
        'total_lama', 'total_baru', 'payload', 'status',
        'approver_nama', 'approver_id', 'approved_at', 'alasan_tolak',
        'applied', 'applied_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'approved_at' => 'datetime',
        'applied_at'  => 'datetime',
        'applied'     => 'boolean',
        'total_lama'  => 'decimal:2',
        'total_baru'  => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'diajukan'  => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'disetujui' => '#3fcf8e',
            'diajukan'  => '#f2c14e',
            'ditolak'   => '#e8645a',
            default     => '#8a9a92',
        };
    }

    /**
     * Terapkan koreksi yang SUDAH disetujui ke stok + total PO + tagihan. Idempoten:
     * hanya jalan bila status 'disetujui' & belum applied. Rekonsiliasi stok pakai DELTA
     * (unit baru − unit lama), bukan menimpa.
     */
    public function apply(): bool
    {
        if ($this->status !== 'disetujui' || $this->applied) return false;

        DB::transaction(function () {
            $po = PurchaseOrder::with('items', 'tagihan')->whereKey($this->purchase_order_id)->lockForUpdate()->first();
            if (! $po) return;
            $data = $this->payload;

            foreach (($data['rows'] ?? []) as $r) {
                $item     = $po->items->firstWhere('id', $r['item_id'] ?? 0);
                $oriUnits = (int) ($r['ori_box'] ?? 0) * (int) ($r['ori_isi'] ?? 1);

                if (! empty($r['hapus'])) {
                    Obat::where('id', (int) ($r['obat_id'] ?? 0))->update([
                        'stok_aktual' => DB::raw('GREATEST(0, stok_aktual - ' . $oriUnits . ')'),
                    ]);
                    $item?->delete();
                    continue;
                }

                $box  = max(0, (int) ($r['box'] ?? 0));
                $isi  = max(1, (int) ($r['isi'] ?? 1));
                $hbox = (float) ($r['harga'] ?? 0);
                $delta = ($box * $isi) - $oriUnits;

                $item?->update([
                    'jumlah_box' => $box, 'isi_per_box' => $isi,
                    'harga_per_box' => $hbox, 'subtotal' => $box * $hbox,
                ]);
                $upd = [
                    'harga_beli_per_unit' => $hbox / $isi,
                    'sumber_harga'        => 'PO',
                    'stok_aktual'         => DB::raw('GREATEST(0, stok_aktual + (' . $delta . '))'),
                ];
                if (! empty($r['expiry'])) $upd['tanggal_kadaluarsa'] = $r['expiry'];
                Obat::where('id', (int) ($r['obat_id'] ?? 0))->update($upd);
            }

            $po->load('items');
            $total = (float) $po->items->sum('subtotal');
            $po->update([
                'nomor_invoice' => ($data['faktur'] ?? null) ?: $po->nomor_invoice,
                'tanggal_po'    => $data['tanggal_po'] ?? $po->tanggal_po,
                'total_nilai'   => $total,
                'catatan'       => trim(($po->catatan ? $po->catatan . ' · ' : '')
                    . 'Koreksi disetujui ' . now()->format('d/m/y H:i') . ': ' . $this->alasan),
            ]);

            $perTipe = ['kronis' => 0.0, 'non_kronis' => 0.0];
            foreach ($po->items as $it) {
                $t = ($it->tipe_obat ?? 'kronis') === 'kronis' ? 'kronis' : 'non_kronis';
                $perTipe[$t] += (float) $it->subtotal;
            }
            foreach ($po->tagihan as $tag) {
                $newTotal = (float) ($perTipe[$tag->tipe_obat] ?? 0);
                if ($newTotal <= 0) { $tag->update(['total_tagihan' => 0, 'status' => 'lunas']); continue; }
                $dib = (float) $tag->jumlah_dibayar;
                $status = $dib <= 0 ? 'belum_bayar' : ($dib >= $newTotal ? 'lunas' : 'sebagian');
                $tag->update(['total_tagihan' => (int) $newTotal, 'status' => $status]);
            }

            $this->update(['applied' => true, 'applied_at' => now()]);
        });

        return true;
    }

    /** Terapkan semua koreksi disetujui yang belum applied (dipanggil apotik saat load Riwayat / cron). */
    public static function terapkanYangDisetujui(): int
    {
        $n = 0;
        static::where('status', 'disetujui')->where('applied', false)->get()
            ->each(function ($k) use (&$n) { if ($k->apply()) $n++; });
        return $n;
    }
}
