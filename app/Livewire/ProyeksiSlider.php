<?php

namespace App\Livewire;

use App\Models\BiayaOperasional;
use App\Services\LabaCalculatorService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProyeksiSlider extends Component
{
    public float $biaya_sdm = 3000000;
    public float $biaya_utilitas = 500000;
    public float $biaya_administrasi = 300000;
    public float $biaya_sewa = 1000000;
    public float $biaya_lainnya = 200000;

    public float $labaKotor = 0;

    public function mount(float $labaKotor): void
    {
        $this->labaKotor = $labaKotor;

        $biaya = BiayaOperasional::currentMonth();
        $this->biaya_sdm          = $biaya->biaya_sdm;
        $this->biaya_utilitas     = $biaya->biaya_utilitas;
        $this->biaya_administrasi = $biaya->biaya_administrasi;
        $this->biaya_sewa         = $biaya->biaya_sewa;
        $this->biaya_lainnya      = $biaya->biaya_lainnya;
    }

    #[Computed]
    public function totalBiayaOps(): float
    {
        return $this->biaya_sdm + $this->biaya_utilitas
             + $this->biaya_administrasi + $this->biaya_sewa
             + $this->biaya_lainnya;
    }

    #[Computed]
    public function labaBersih(): float
    {
        return $this->labaKotor - $this->totalBiayaOps;
    }

    public function simpanBiaya(): void
    {
        BiayaOperasional::updateOrCreate(
            ['bulan' => now()->month, 'tahun' => now()->year],
            [
                'biaya_sdm'          => $this->biaya_sdm,
                'biaya_utilitas'     => $this->biaya_utilitas,
                'biaya_administrasi' => $this->biaya_administrasi,
                'biaya_sewa'         => $this->biaya_sewa,
                'biaya_lainnya'      => $this->biaya_lainnya,
            ]
        );

        $this->dispatch('toast', message: 'Biaya operasional tersimpan.', type: 'success');
    }

    public function render()
    {
        return view('livewire.proyeksi-slider');
    }
}
