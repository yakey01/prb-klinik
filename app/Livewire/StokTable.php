<?php
namespace App\Livewire;

use App\Models\Obat;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\Attributes\Computed;

class StokTable extends Component
{
    public string $search     = '';
    public string $filterStok = 'semua';
    public string $sortBy     = 'nama_obat';
    public string $sortDir    = 'asc';

    public function sortBy(string $col): void
    {
        $this->sortBy  = $col;
        $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
    }

    public function updateStok(int $id, int $value): void
    {
        $obat     = Obat::findOrFail($id);
        $old      = $obat->stok_aktual;
        $obat->update(['stok_aktual' => max(0, $value)]);
        ActivityLog::record('updated',"Stok diupdate: {$obat->nama_obat} ({$old}→{$value})",'Obat',$id,
            ['stok_aktual'=>$old],['stok_aktual'=>$value]);
        $this->dispatch('toast', message: 'Stok diperbarui.', type: 'success');
    }

    public function updateMinimum(int $id, int $value): void
    {
        Obat::findOrFail($id)->update(['stok_minimum' => max(0, $value)]);
        $this->dispatch('toast', message: 'Stok minimum diperbarui.', type: 'success');
    }

    public function updateKadaluarsa(int $id, ?string $value): void
    {
        Obat::findOrFail($id)->update(['tanggal_kadaluarsa' => $value ?: null]);
        $this->dispatch('toast', message: 'Tanggal kadaluarsa diperbarui.', type: 'success');
    }

    #[Computed]
    public function obatList()
    {
        $query = Obat::where('is_active', true);

        if ($this->search) {
            $query->where('nama_obat', 'like', '%'.$this->search.'%');
        }

        $list = $query->orderBy($this->sortBy, $this->sortDir)->get();

        return match ($this->filterStok) {
            'habis'  => $list->filter(fn ($o) => $o->stok_aktual <= 0)->values(),
            'kritis' => $list->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->values(),
            'aman'   => $list->filter(fn ($o) => $o->stok_aktual > $o->stok_minimum)->values(),
            'kadaluarsa' => $list->filter(fn ($o) => $o->kadaluarsa_status === 'kadaluarsa' || $o->kadaluarsa_status === 'segera')->values(),
            default  => $list,
        };
    }

    #[Computed]
    public function alertSummary(): array
    {
        $all = Obat::where('is_active', true)->get();
        return [
            'habis'   => $all->filter(fn ($o) => $o->stok_aktual <= 0)->count(),
            'kritis'  => $all->filter(fn ($o) => $o->stok_aktual > 0 && $o->stok_aktual <= $o->stok_minimum)->count(),
            'kadaluarsa' => $all->filter(fn ($o) => in_array($o->kadaluarsa_status, ['kadaluarsa','segera']))->count(),
        ];
    }

    public function render() { return view('livewire.stok-table'); }
}
