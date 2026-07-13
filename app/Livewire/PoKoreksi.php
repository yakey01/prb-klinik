<?php

namespace App\Livewire;

use App\Models\KoreksiPo;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
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

        // Cegah dobel usulan koreksi yang masih menunggu untuk PO yang sama.
        if (KoreksiPo::where('purchase_order_id', $this->poId)->where('status', 'diajukan')->exists()) {
            $this->dispatch('toast', type: 'error', message: 'Sudah ada usulan koreksi PO ini yang menunggu persetujuan manajer.');
            $this->show = false; $this->poId = null;
            return;
        }

        $r = $this->ringkas;
        // KOREKSI TIDAK langsung diterapkan — diajukan ke manajer SIM untuk disetujui.
        // Payload menyimpan perubahan (before/after) untuk dinilai + diterapkan saat disetujui.
        $payload = [
            'faktur'     => $this->nomorFaktur ?: null,
            'tanggal_po' => $this->tanggalPo,
            'rows'       => array_map(fn ($row) => [
                'item_id'  => $row['item_id'] ?? null,
                'obat_id'  => (int) ($row['obat_id'] ?? 0),
                'nama'     => $row['nama_obat'] ?? '',
                'tipe'     => $row['tipe_obat'] ?? 'kronis',
                'ori_box'  => (int) ($row['ori_box'] ?? 0),
                'ori_isi'  => (int) ($row['ori_isi'] ?? 1),
                'ori_harga' => (float) ($row['ori_harga'] ?? 0),
                'box'      => (int) ($row['jumlah_box'] ?? 0),
                'isi'      => (int) ($row['isi_per_box'] ?? 1),
                'harga'    => (float) ($row['harga_per_box'] ?? 0),
                'expiry'   => $row['tanggal_kadaluarsa'] ?? '',
                'hapus'    => (bool) ($row['hapus'] ?? false),
            ], $this->rows),
        ];

        KoreksiPo::create([
            'purchase_order_id' => $this->poId,
            'pemohon_nama'      => Auth::user()?->name,
            'pemohon_id'        => Auth::id(),
            'alasan'            => $this->alasan,
            'total_lama'        => $r['lama'],
            'total_baru'        => $r['baru'],
            'payload'           => $payload,
            'status'            => 'diajukan',
            'applied'           => false,
        ]);

        $this->show = false;
        $this->poId = null;
        $this->dispatch('toast', message: 'Koreksi PO diajukan — menunggu PERSETUJUAN manajer SIM. Stok/tagihan belum berubah.', type: 'info');
        $this->dispatch('po-updated');
    }

    public function render()
    {
        return view('livewire.po-koreksi');
    }
}
