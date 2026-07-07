<?php

namespace App\Livewire;

use App\Models\HomeVisite;
use Livewire\Component;
use Livewire\Attributes\Computed;

class HomeVisiteMap extends Component
{
    public ?int $selectedVisiteId  = null;

    #[Computed]
    public function aktifList()
    {
        return HomeVisite::aktif()
            ->with(['pasien:id,nama', 'kurir:id,name'])
            ->orderByRaw("FIELD(status,'dalam_perjalanan','sampai','ditugaskan')")
            ->get();
    }

    public function selectVisite(int $id): void
    {
        $this->selectedVisiteId = $this->selectedVisiteId === $id ? null : $id;
    }

    public function render()
    {
        return view('livewire.home-visite-map');
    }
}
