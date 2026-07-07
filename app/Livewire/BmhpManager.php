<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Obat;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BmhpManager extends Component
{
    public string $search        = '';
    public string $filterKategori = 'semua';
    public string $filterStok    = 'semua';
    public string $sortBy        = 'nama_obat';
    public string $sortDir       = 'asc';

    // Form tambah/edit
    public bool   $showForm           = false;
    public ?int   $editId             = null;
    public string $nama_obat          = '';
    public string $kategori_diagnosis = 'Injeksi';
    public string $satuan             = 'pcs';
    public int    $isi_per_box        = 1;
    public float  $harga_beli_per_unit = 0;
    public float  $harga_jual_per_unit = 0;
    public int    $stok_aktual        = 0;
    public int    $stok_minimum       = 10;

    public array $kategoriOpsi = ['Injeksi','Infus','Proteksi Diri','Perawatan Luka','Urologi','Oksigenasi','Antiseptik','Lain-lain'];

    public function sortBy(string $col): void
    {
        if ($this->sortBy === $col) $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        else { $this->sortBy = $col; $this->sortDir = 'asc'; }
    }

    private function base()
    {
        return Obat::where('is_active', true)->where('tipe_obat', 'bmhp');
    }

    #[Computed]
    public function kategoriList(): array
    {
        return $this->base()->distinct()->orderBy('kategori_diagnosis')->pluck('kategori_diagnosis')->filter()->values()->all();
    }

    #[Computed]
    public function bmhpList()
    {
        $q = $this->base();
        if ($this->search) $q->where('nama_obat', 'like', '%'.$this->search.'%');
        if ($this->filterKategori !== 'semua') $q->where('kategori_diagnosis', $this->filterKategori);

        $list = $q->orderBy($this->sortBy, $this->sortDir)->get();

        return match ($this->filterStok) {
            'habis'  => $list->filter(fn ($o) => $o->stok_aktual <= 0)->values(),
            'kritis' => $list->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->values(),
            'aman'   => $list->filter(fn ($o) => $o->stok_aktual > $o->stok_minimum)->values(),
            default  => $list,
        };
    }

    #[Computed]
    public function summary(): array
    {
        $all = $this->base()->get();
        return [
            'jenis'          => $all->count(),
            'habis'          => $all->filter(fn ($o) => $o->stok_aktual <= 0)->count(),
            'kritis'         => $all->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->count(),
            'nilai_inventori'=> $all->sum(fn ($o) => (int) $o->stok_aktual * (float) $o->harga_beli_per_unit),
        ];
    }

    public function updateStok(int $id, $value): void
    {
        $o = $this->findBmhp($id); if (!$o) return;
        $value = max(0, (int) $value);
        $old = $o->stok_aktual;
        $o->update(['stok_aktual' => $value]);
        ActivityLog::record('updated', "Stok BMHP: {$o->nama_obat} ({$old}→{$value})", 'Obat', $id);
        $this->dispatch('toast', message: 'Stok BMHP diperbarui.', type: 'success');
    }

    public function updateMinimum(int $id, $value): void
    {
        $o = $this->findBmhp($id); if (!$o) return;
        $o->update(['stok_minimum' => max(0, (int) $value)]);
        $this->dispatch('toast', message: 'Stok minimum diperbarui.', type: 'success');
    }

    public function updateIsiPerBox(int $id, $value): void
    {
        $o = $this->findBmhp($id); if (!$o) return;
        $o->update(['isi_per_box' => max(1, (int) $value)]);
        $this->dispatch('toast', message: 'Isi per box diperbarui.', type: 'success');
    }

    public function tambahBox(int $id, int $box): void
    {
        $o = $this->findBmhp($id); if (!$o) return;
        $tambah = max(0, $box) * max(1, (int) $o->isi_per_box);
        $o->increment('stok_aktual', $tambah);
        $this->dispatch('toast', message: "+{$box} box ({$tambah} {$o->satuan}) ditambahkan.", type: 'success');
    }

    private function findBmhp(int $id): ?Obat
    {
        return Obat::where('id', $id)->where('tipe_obat', 'bmhp')->first();
    }

    public function openAdd(): void { $this->resetForm(); $this->showForm = true; }

    public function openEdit(int $id): void
    {
        $o = $this->findBmhp($id); if (!$o) return;
        $this->editId = $id;
        $this->nama_obat = $o->nama_obat;
        $this->kategori_diagnosis = $o->kategori_diagnosis ?: 'Injeksi';
        $this->satuan = $o->satuan ?: 'pcs';
        $this->isi_per_box = (int) $o->isi_per_box;
        $this->harga_beli_per_unit = (float) $o->harga_beli_per_unit;
        $this->harga_jual_per_unit = (float) $o->harga_jual_per_unit;
        $this->stok_aktual = (int) $o->stok_aktual;
        $this->stok_minimum = (int) $o->stok_minimum;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_obat'           => 'required|string|max:120',
            'kategori_diagnosis'  => 'required|string|max:60',
            'satuan'              => 'required|string|max:20',
            'isi_per_box'         => 'required|integer|min:1',
            'harga_beli_per_unit' => 'required|numeric|min:0',
            'harga_jual_per_unit' => 'required|numeric|min:0',
            'stok_aktual'         => 'required|integer|min:0',
            'stok_minimum'        => 'required|integer|min:0',
        ]);

        $margin = $this->harga_beli_per_unit > 0
            ? round(($this->harga_jual_per_unit - $this->harga_beli_per_unit) / $this->harga_beli_per_unit, 4) : 0;

        $data = [
            'nama_obat'           => $this->nama_obat,
            'tipe_obat'           => 'bmhp',
            'kategori_diagnosis'  => $this->kategori_diagnosis,
            'satuan'              => $this->satuan,
            'isi_per_box'         => $this->isi_per_box,
            'harga_beli_per_unit' => $this->harga_beli_per_unit,
            'harga_jual_per_unit' => $this->harga_jual_per_unit,
            'margin_umum'         => $margin,
            'klaim_bpjs_per_unit' => 0,
            'faktor_jasa_farmasi' => 1,
            'stok_aktual'         => $this->stok_aktual,
            'stok_minimum'        => $this->stok_minimum,
            'is_active'           => true,
            'sumber_harga'        => 'EST',
        ];

        if ($this->editId) {
            $this->findBmhp($this->editId)?->update($data);
            $this->dispatch('toast', message: 'BMHP diperbarui.', type: 'success');
        } else {
            $data['kode_obat'] = 'BMHP' . str_pad((string) (Obat::where('tipe_obat', 'bmhp')->count() + 1), 3, '0', STR_PAD_LEFT);
            $data['jumlah_pasien'] = 0; $data['unit_per_bulan'] = 0;
            $o = Obat::create($data);
            ActivityLog::record('created', "BMHP baru: {$o->nama_obat}", 'Obat', $o->id);
            $this->dispatch('toast', message: 'BMHP baru ditambahkan.', type: 'success');
        }
        $this->cancel();
    }

    public function delete(int $id): void
    {
        if (!auth()->user()?->canEdit()) {
            $this->dispatch('toast', message: 'Tidak punya izin menghapus.', type: 'error'); return;
        }
        $o = $this->findBmhp($id); if (!$o) return;
        $o->update(['is_active' => false]); // soft hide
        $this->dispatch('toast', message: 'BMHP dinonaktifkan.', type: 'success');
    }

    public function cancel(): void { $this->showForm = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->editId = null; $this->nama_obat = ''; $this->kategori_diagnosis = 'Injeksi';
        $this->satuan = 'pcs'; $this->isi_per_box = 1; $this->harga_beli_per_unit = 0;
        $this->harga_jual_per_unit = 0; $this->stok_aktual = 0; $this->stok_minimum = 10;
        $this->resetValidation();
    }

    public function render() { return view('livewire.bmhp-manager'); }
}
