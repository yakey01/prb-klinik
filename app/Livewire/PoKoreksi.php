<?php

namespace App\Livewire;

use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Koreksi Purchase Order — perbaiki qty/harga/expiry item saat barang atau harga
 * di faktur tidak sesuai. Merekonsiliasi STOK (selisih unit) dan TAGIHAN (per tipe),
 * dengan jejak audit. Standar farmasi: PO sudah menambah stok & tagihan, jadi koreksi
 * harus menyesuaikan keduanya, bukan sekadar mengubah angka.
 */
class PoKoreksi extends Component
{
    public bool $show = false;
    public ?int $poId = null;
    public string $nomorFaktur = '';
    public string $tanggalPo = '';
    public string $alasan = '';
    public array $rows = [];

    /** Buka modal koreksi untuk 1 PO (via tombol di halaman Riwayat). */
    #[On('koreksi-po')]
    public function open(int $poId): void
    {
        $po = PurchaseOrder::with('items.obat')->findOrFail($poId);
        $this->poId        = $po->id;
        $this->nomorFaktur = (string) ($po->nomor_invoice ?? '');
        $this->tanggalPo   = optional($po->tanggal_po)->format('Y-m-d') ?: now()->format('Y-m-d');
        $this->alasan      = '';
        $this->rows = $po->items->map(fn ($it) => [
            'item_id'       => $it->id,
            'obat_id'       => (int) $it->obat_id,
            'nama_obat'     => $it->obat->nama_obat ?? '—',
            'tipe_obat'     => $it->tipe_obat,
            'ori_box'       => (int) $it->jumlah_box,
            'ori_isi'       => (int) $it->isi_per_box,
            'ori_harga'     => (float) $it->harga_per_box,
            'jumlah_box'    => (int) $it->jumlah_box,
            'isi_per_box'   => (int) $it->isi_per_box,
            'harga_per_box' => (float) $it->harga_per_box,
            'tanggal_kadaluarsa' => optional($it->obat?->tanggal_kadaluarsa)->format('Y-m-d') ?? '',
            'hapus'         => false,
        ])->all();
        $this->resetValidation();
        $this->show = true;
    }

    public function tutup(): void { $this->show = false; $this->poId = null; }

    #[Computed]
    public function po()
    {
        return $this->poId ? PurchaseOrder::with(['tagihan', 'distributor'])->find($this->poId) : null;
    }

    /** Ringkasan: total lama vs baru + info tagihan sudah dibayar (peringatan). */
    #[Computed]
    public function ringkas(): array
    {
        $lama = 0.0; $baru = 0.0;
        foreach ($this->rows as $r) {
            $lama += (int) $r['ori_box'] * (float) $r['ori_harga'];
            if (! ($r['hapus'] ?? false)) $baru += (int) $r['jumlah_box'] * (float) $r['harga_per_box'];
        }
        $dibayar = (float) ($this->po?->tagihan->sum('jumlah_dibayar') ?? 0);
        return ['lama' => $lama, 'baru' => $baru, 'selisih' => $baru - $lama, 'sudah_dibayar' => $dibayar];
    }

    public function simpan(): void
    {
        $this->validate(
            ['alasan' => 'required|string|min:3|max:300', 'nomorFaktur' => 'nullable|string|max:100', 'tanggalPo' => 'required|date'],
            ['alasan.required' => 'Isi alasan koreksi (jejak audit wajib).', 'alasan.min' => 'Alasan minimal 3 karakter.'],
            ['alasan' => 'alasan koreksi', 'tanggalPo' => 'tanggal PO']
        );

        $sisa = collect($this->rows)->reject(fn ($r) => $r['hapus'] ?? false);
        if ($sisa->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'PO harus punya minimal 1 item. Hapus PO lewat tombol Hapus bila memang batal.');
            return;
        }

        DB::transaction(function () {
            $po = PurchaseOrder::with('items')->whereKey($this->poId)->lockForUpdate()->firstOrFail();

            foreach ($this->rows as $r) {
                $item = $po->items->firstWhere('id', $r['item_id']);
                $oriUnits = (int) $r['ori_box'] * (int) $r['ori_isi'];

                if ($r['hapus'] ?? false) {
                    // Hapus item → tarik kembali stok yang dulu ditambah.
                    Obat::where('id', (int) $r['obat_id'])->update([
                        'stok_aktual' => DB::raw('GREATEST(0, stok_aktual - ' . $oriUnits . ')'),
                    ]);
                    $item?->delete();
                    continue;
                }

                $box = max(0, (int) $r['jumlah_box']);
                $isi = max(1, (int) $r['isi_per_box']);
                $hbox = (float) $r['harga_per_box'];
                $newUnits = $box * $isi;
                $delta = $newUnits - $oriUnits;   // penyesuaian stok

                if ($item) {
                    $item->update([
                        'jumlah_box' => $box, 'isi_per_box' => $isi,
                        'harga_per_box' => $hbox, 'subtotal' => $box * $hbox,
                    ]);
                }
                $upd = [
                    'harga_beli_per_unit' => $hbox / $isi,
                    'sumber_harga'        => 'PO',
                    'stok_aktual'         => DB::raw('GREATEST(0, stok_aktual + (' . $delta . '))'),
                ];
                if (! empty($r['tanggal_kadaluarsa'])) $upd['tanggal_kadaluarsa'] = $r['tanggal_kadaluarsa'];
                Obat::where('id', (int) $r['obat_id'])->update($upd);
            }

            // Rekap ulang PO total dari item tersisa.
            $po->load('items');
            $total = (float) $po->items->sum('subtotal');
            $po->update([
                'nomor_invoice' => $this->nomorFaktur ?: null,
                'tanggal_po'    => $this->tanggalPo,
                'total_nilai'   => $total,
                'catatan'       => trim(($po->catatan ? $po->catatan . ' · ' : '')
                    . 'Dikoreksi ' . now()->format('d/m/y H:i') . ' oleh ' . (Auth::user()?->name ?? '-') . ': ' . $this->alasan),
            ]);

            // Rekonsiliasi TAGIHAN per tipe (total ikut item aktual; status dihitung ulang).
            $perTipe = ['kronis' => 0.0, 'non_kronis' => 0.0];
            foreach ($po->items as $it) {
                $t = ($it->tipe_obat ?? 'kronis') === 'kronis' ? 'kronis' : 'non_kronis';
                $perTipe[$t] += (float) $it->subtotal;
            }
            foreach ($po->tagihan as $tag) {
                $newTotal = (float) ($perTipe[$tag->tipe_obat] ?? 0);
                if ($newTotal <= 0) {
                    // Tipe ini tak lagi ada → tagihan jadi 0 (kosongkan, tandai lunas bila sudah dibayar).
                    $tag->update(['total_tagihan' => 0, 'status' => 'lunas']);
                    continue;
                }
                $dib = (float) $tag->jumlah_dibayar;
                $status = $dib <= 0 ? 'belum_bayar' : ($dib >= $newTotal ? 'lunas' : 'sebagian');
                $tag->update(['total_tagihan' => (int) $newTotal, 'status' => $status]);
            }
        });

        $this->show = false;
        $this->poId = null;
        $this->dispatch('toast', message: 'PO dikoreksi — stok & tagihan diselaraskan.', type: 'success');
        $this->dispatch('po-updated');   // pemicu refresh halaman bila perlu
    }

    public function render()
    {
        return view('livewire.po-koreksi');
    }
}
