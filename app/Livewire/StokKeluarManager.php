<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Obat;
use App\Models\StokKeluar;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class StokKeluarManager extends Component
{
    public string $activeTab = 'kronis';

    // Form — only for non-kronis manual entries
    public bool   $showForm           = false;
    public ?int   $editId             = null;
    public int    $obat_id            = 0;
    public string $tanggal_keluar     = '';
    public int    $jumlah_unit        = 1;
    public string $satuan             = 'tablet';
    public float  $harga_jual_per_unit = 0;
    public string $keterangan         = '';

    // Filters
    public string $search      = '';
    public string $filterBulan = '';

    public function mount(): void
    {
        $this->tanggal_keluar = now()->format('Y-m-d');
        $this->filterBulan    = now()->format('Y-m');
    }

    #[Computed]
    public function obatList()
    {
        return Obat::where('is_active', true)
            ->where('tipe_obat', 'non_kronis')
            ->orderBy('nama_obat')
            ->get(['id', 'nama_obat', 'satuan', 'harga_jual_per_unit']);
    }

    #[Computed]
    public function records()
    {
        $sumber = $this->activeTab === 'kronis' ? 'pengambilan' : 'manual';

        return StokKeluar::with(['obat', 'pasien'])
            ->where('sumber', $sumber)
            ->when($this->filterBulan, fn ($q) =>
                $q->whereRaw("DATE_FORMAT(tanggal_keluar,'%Y-%m') = ?", [$this->filterBulan])
            )
            ->when($this->search, fn ($q) =>
                $q->where(function ($inner) {
                    $inner->whereHas('obat', fn ($o) => $o->where('nama_obat', 'like', '%'.$this->search.'%'))
                          ->orWhereHas('pasien', fn ($p) => $p->where('nama', 'like', '%'.$this->search.'%'));
                })
            )
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function tabCounts(): array
    {
        $base = StokKeluar::when($this->filterBulan, fn ($q) =>
            $q->whereRaw("DATE_FORMAT(tanggal_keluar,'%Y-%m') = ?", [$this->filterBulan])
        );

        return [
            'kronis'     => (clone $base)->where('sumber', 'pengambilan')->count(),
            'non_kronis' => (clone $base)->where('sumber', 'manual')->count(),
        ];
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

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->showForm  = false;
        $this->resetForm();
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $r = StokKeluar::findOrFail($id);
        if ($r->sumber === 'pengambilan') return;

        $this->editId              = $id;
        $this->obat_id             = $r->obat_id;
        $this->tanggal_keluar      = $r->tanggal_keluar->format('Y-m-d');
        $this->jumlah_unit         = $r->jumlah_unit;
        $this->satuan              = $r->satuan;
        $this->harga_jual_per_unit = $r->harga_jual_per_unit;
        $this->keterangan          = $r->keterangan ?? '';
        $this->showForm            = true;
    }

    public function updatedObatId(int $value): void
    {
        if ($value) {
            $obat = Obat::find($value);
            if ($obat) {
                $this->satuan              = $obat->satuan ?? 'tablet';
                $this->harga_jual_per_unit = (float) ($obat->harga_jual_per_unit ?? 0);
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'obat_id'             => 'required|exists:obat,id',
            'tanggal_keluar'      => 'required|date',
            'jumlah_unit'         => 'required|integer|min:1',
            'harga_jual_per_unit' => 'required|numeric|min:0',
        ]);

        $obat = Obat::findOrFail($this->obat_id);

        $data = [
            'obat_id'              => $this->obat_id,
            'tanggal_keluar'       => $this->tanggal_keluar,
            'jumlah_unit'          => $this->jumlah_unit,
            'satuan'               => $this->satuan ?: ($obat->satuan ?? 'tablet'),
            'harga_beli_snapshot'  => (float) ($obat->harga_beli_per_unit ?? 0),
            'harga_jual_per_unit'  => $this->harga_jual_per_unit,
            'keterangan'           => $this->keterangan ?: null,
            'dicatat_oleh'         => auth()->id(),
            'sumber'               => 'manual',
        ];

        if ($this->editId) {
            StokKeluar::findOrFail($this->editId)->update($data);
            ActivityLog::record('updated', "Stok keluar non-kronis diperbarui: ID {$this->editId}", 'StokKeluar', $this->editId);
            $this->dispatch('toast', message: 'Data stok keluar diperbarui.', type: 'success');
        } else {
            $sk = StokKeluar::create($data);
            Obat::where('id', $this->obat_id)
                ->update(['stok_aktual' => DB::raw('stok_aktual - ' . $this->jumlah_unit)]);
            ActivityLog::record('created', "Stok keluar: {$obat->nama_obat} {$this->jumlah_unit} {$this->satuan}", 'StokKeluar', $sk->id);
            $this->dispatch('toast', message: 'Stok keluar berhasil dicatat.', type: 'success');
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        if (!auth()->user()?->canEdit()) {
            $this->dispatch('toast', message: 'Tidak memiliki izin untuk menghapus data.', type: 'error');
            return;
        }
        $sk = StokKeluar::with('obat')->findOrFail($id);

        if ($sk->sumber === 'pengambilan') {
            $this->dispatch('toast', message: 'Entri dari pengambilan obat tidak dapat dihapus manual.', type: 'error');
            return;
        }

        Obat::where('id', $sk->obat_id)
            ->update(['stok_aktual' => DB::raw('stok_aktual + ' . $sk->jumlah_unit)]);
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
