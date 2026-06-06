<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Obat;
use App\Models\StokKeluar;
use Livewire\Component;
use Livewire\Attributes\Computed;

class StokKeluarManager extends Component
{
    // Form
    public bool   $showForm          = false;
    public ?int   $editId            = null;
    public int    $obat_id           = 0;
    public string $tanggal_keluar    = '';
    public int    $jumlah_unit       = 1;
    public string $satuan            = 'tablet';
    public float  $harga_jual_per_unit = 0;
    public string $keterangan        = '';

    // Filter
    public string $search    = '';
    public string $filterBulan = '';

    public function mount(): void
    {
        $this->tanggal_keluar = now()->format('Y-m-d');
        $this->filterBulan    = now()->format('Y-m');
    }

    #[Computed]
    public function obatList()
    {
        // Show all active obat — stok keluar bisa untuk obat apapun (kronis maupun non-kronis)
        return Obat::where('is_active', true)->orderBy('tipe_obat')->orderBy('nama_obat')->get();
    }

    #[Computed]
    public function records()
    {
        $query = StokKeluar::with('obat')
            ->when($this->filterBulan, fn ($q) =>
                $q->whereRaw("DATE_FORMAT(tanggal_keluar,'%Y-%m') = ?", [$this->filterBulan])
            )
            ->when($this->search, fn ($q) =>
                $q->whereHas('obat', fn ($o) => $o->where('nama_obat', 'like', '%'.$this->search.'%'))
            )
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('id');

        return $query->get();
    }

    #[Computed]
    public function summary(): array
    {
        $rows = $this->records;
        return [
            'total_pendapatan' => $rows->sum(fn ($r) => $r->total_pendapatan),
            'total_biaya'      => $rows->sum(fn ($r) => $r->total_biaya),
            'total_laba'       => $rows->sum(fn ($r) => $r->laba),
            'total_item'       => $rows->sum('jumlah_unit'),
            'jumlah_transaksi' => $rows->count(),
        ];
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $r = StokKeluar::findOrFail($id);
        $this->editId             = $id;
        $this->obat_id            = $r->obat_id;
        $this->tanggal_keluar     = $r->tanggal_keluar->format('Y-m-d');
        $this->jumlah_unit        = $r->jumlah_unit;
        $this->satuan             = $r->satuan;
        $this->harga_jual_per_unit = $r->harga_jual_per_unit;
        $this->keterangan         = $r->keterangan ?? '';
        $this->showForm           = true;
    }

    public function updatedObatId(int $value): void
    {
        if ($value) {
            $obat = Obat::find($value);
            if ($obat) {
                $this->satuan             = $obat->satuan ?? 'tablet';
                $this->harga_jual_per_unit = (float) ($obat->harga_jual_per_unit ?? 0);
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'obat_id'            => 'required|exists:obat,id',
            'tanggal_keluar'     => 'required|date',
            'jumlah_unit'        => 'required|integer|min:1',
            'harga_jual_per_unit'=> 'required|numeric|min:0',
        ]);

        $obat = Obat::findOrFail($this->obat_id);

        $data = [
            'obat_id'              => $this->obat_id,
            'tanggal_keluar'       => $this->tanggal_keluar,
            'jumlah_unit'          => $this->jumlah_unit,
            'satuan'               => $this->satuan ?: $obat->satuan,
            'harga_beli_snapshot'  => (float) $obat->harga_beli_per_unit,
            'harga_jual_per_unit'  => $this->harga_jual_per_unit,
            'keterangan'           => $this->keterangan ?: null,
            'dicatat_oleh'         => auth()->id(),
        ];

        if ($this->editId) {
            StokKeluar::findOrFail($this->editId)->update($data);
            ActivityLog::record('updated', 'Stok keluar diperbarui', 'StokKeluar', $this->editId);
            $this->dispatch('toast', message: 'Data stok keluar diperbarui.', type: 'success');
        } else {
            $sk = StokKeluar::create($data);
            // Kurangi stok aktual
            Obat::where('id', $this->obat_id)
                ->update(['stok_aktual' => \DB::raw('stok_aktual - ' . $this->jumlah_unit)]);
            ActivityLog::record('created', "Stok keluar: {$obat->nama_obat} {$this->jumlah_unit} {$this->satuan}", 'StokKeluar', $sk->id);
            $this->dispatch('toast', message: 'Stok keluar berhasil dicatat.', type: 'success');
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        $sk = StokKeluar::with('obat')->findOrFail($id);
        // Kembalikan stok
        Obat::where('id', $sk->obat_id)
            ->update(['stok_aktual' => \DB::raw('stok_aktual + ' . $sk->jumlah_unit)]);
        ActivityLog::record('deleted', "Stok keluar dihapus: ID {$id}", 'StokKeluar', $id);
        $sk->delete();
        $this->dispatch('toast', message: 'Data dihapus dan stok dikembalikan.', type: 'success');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editId              = null;
        $this->obat_id             = 0;
        $this->tanggal_keluar      = now()->format('Y-m-d');
        $this->jumlah_unit         = 1;
        $this->satuan              = 'tablet';
        $this->harga_jual_per_unit = 0;
        $this->keterangan          = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.stok-keluar-manager');
    }
}
