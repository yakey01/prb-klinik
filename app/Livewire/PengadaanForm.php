<?php

namespace App\Livewire;

use App\Models\Distributor;
use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PengadaanForm extends Component
{
    use WithPagination;
    public int $distributor_id = 0;
    public string $nomor_invoice = '';
    public string $tanggal_po = '';
    public string $catatan = '';
    public array $rows = [];

    public function mount(): void
    {
        $this->tanggal_po = now()->format('Y-m-d');
        $this->addRow();
    }

    public function addRow(): void
    {
        $this->rows[] = [
            'obat_id'            => 0,
            'tipe_obat'          => 'kronis',
            'jumlah_box'         => 1,
            'isi_per_box'        => 1,
            'harga_per_box'      => 0,
            'subtotal'           => 0,
            'tanggal_kadaluarsa' => '',
        ];
    }

    public function removeRow(int $index): void
    {
        array_splice($this->rows, $index, 1);
        if (empty($this->rows)) {
            $this->addRow();
        }
    }

    public function updatedRows($value, $key): void
    {
        // $key bisa "0.obat_id" (per-field) atau kadang hanya "0" (seluruh baris) —
        // guard agar tidak "Undefined array key 1".
        [$index, $field] = array_pad(explode('.', $key), 2, null);
        if ($field === null || ! isset($this->rows[(int)$index])) {
            return;
        }
        $row = &$this->rows[(int)$index];

        if (in_array($field, ['jumlah_box', 'isi_per_box', 'harga_per_box'])) {
            $row['subtotal'] = (float)$row['jumlah_box'] * (float)$row['harga_per_box'];
        }

        if ($field === 'obat_id' && $value) {
            $obat = Obat::find($value);
            if ($obat) {
                $row['isi_per_box'] = 10;
                // tipe_obat NOT auto-set — user chooses BPJS or UMUM manually
            }
        }
    }

    #[Computed]
    public function grandTotal(): float
    {
        return collect($this->rows)->sum('subtotal');
    }

    #[Computed]
    public function subtotalKronis(): float
    {
        return collect($this->rows)
            ->where('tipe_obat', 'kronis')
            ->sum('subtotal');
    }

    #[Computed]
    public function subtotalNonKronis(): float
    {
        return collect($this->rows)
            ->where('tipe_obat', 'non_kronis')
            ->sum('subtotal');
    }

    #[Computed]
    public function obatList()
    {
        return Obat::where('is_active', true)
            ->orderBy('tipe_obat')
            ->orderBy('nama_obat')
            ->get(['id', 'nama_obat', 'tipe_obat', 'kategori_diagnosis']);
    }

    #[Computed]
    public function distributors()
    {
        return Distributor::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function recentOrders()
    {
        return PurchaseOrder::with(['distributor', 'items.obat'])
            ->orderByDesc('tanggal_po')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function save(): void
    {
        $this->validate([
            'distributor_id'   => 'required|exists:distributors,id',
            'tanggal_po'       => 'required|date',
            'rows'             => 'required|array|min:1',
            'rows.*.obat_id'   => 'required|exists:obat,id',
            'rows.*.jumlah_box'   => 'required|integer|min:1',
            'rows.*.isi_per_box'  => 'required|integer|min:1',
            'rows.*.harga_per_box'=> 'required|numeric|min:1',
        ]);

        DB::transaction(function () {
            $po = PurchaseOrder::create([
                'distributor_id' => $this->distributor_id,
                'nomor_invoice'  => $this->nomor_invoice ?: null,
                'tanggal_po'     => $this->tanggal_po,
                'total_nilai'    => $this->grandTotal,
                'catatan'        => $this->catatan ?: null,
            ]);

            foreach ($this->rows as $row) {
                $hargaPerUnit = $row['isi_per_box'] > 0
                    ? $row['harga_per_box'] / $row['isi_per_box']
                    : 0;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'obat_id'           => $row['obat_id'],
                    'tipe_obat'         => $row['tipe_obat'] ?? 'kronis',
                    'jumlah_box'        => $row['jumlah_box'],
                    'isi_per_box'       => $row['isi_per_box'],
                    'harga_per_box'     => $row['harga_per_box'],
                    'subtotal'          => $row['subtotal'],
                ]);

                $updateData = [
                    'harga_beli_per_unit' => $hargaPerUnit,
                    'sumber_harga'        => 'PO',
                    'stok_aktual'         => \DB::raw('stok_aktual + ' . ((int)$row['jumlah_box'] * (int)$row['isi_per_box'])),
                ];
                if (!empty($row['tanggal_kadaluarsa'])) {
                    $updateData['tanggal_kadaluarsa'] = $row['tanggal_kadaluarsa'];
                }
                Obat::where('id', $row['obat_id'])->update($updateData);
            }

            // ── Auto-split tagihan per tipe_obat ─────────────────────────
            $periode = now()->format('Y-m');
            $jatuhTempo = now()->addDays(30)->toDateString();
            $tanggal    = now()->toDateString();

            $subtotalPerTipe = collect($this->rows)
                ->groupBy('tipe_obat')
                ->map(fn($g) => $g->sum('subtotal'));

            foreach ($subtotalPerTipe as $tipe => $total) {
                if ($total <= 0) continue;
                Tagihan::create([
                    'purchase_order_id'   => $po->id,
                    'distributor_id'      => $this->distributor_id,
                    'nomor_tagihan'       => Tagihan::generateNomor($tipe),
                    'tipe_obat'           => $tipe,
                    'periode_bulan'       => $periode,
                    'tanggal_tagihan'     => $tanggal,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'total_tagihan'       => (int) $total,
                    'status'              => 'belum_bayar',
                ]);
            }
        });

        // Reset form, stay on page
        $this->distributor_id = 0;
        $this->nomor_invoice  = '';
        $this->tanggal_po     = now()->format('Y-m-d');
        $this->catatan        = '';
        $this->rows           = [];
        $this->addRow();
        $this->resetPage();
        $this->dispatch('toast', message: 'Purchase Order berhasil disimpan!', type: 'success');
    }

    public function render()
    {
        return view('livewire.pengadaan-form');
    }
}
