<?php

namespace Tests\Feature;

use App\Livewire\PengajuanPengadaan;
use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresi PR-202607-0024: Metformin qty 3700, harga/box tertinggal Rp202,50
 * (harga per BUTIR) padahal isi 100 → total 100× kurang.
 * Akar: ubah isi_per_box hanya menghitung ulang subtotal, harga/box tidak.
 */
class PengajuanHargaBoxTest extends TestCase
{
    use RefreshDatabase;

    private function obat(float $unit = 202.50): Obat
    {
        return Obat::create([
            'nama_obat' => 'Metformin 500mg',
            'kode_obat' => 'MTF500',
            'tipe_obat' => 'kronis',
            'satuan' => 'tablet',
            'is_active' => true,
            'harga_beli_per_unit' => $unit,
            'harga_jual_per_unit' => 300,
            'klaim_bpjs_per_unit' => 207.36,
            'faktor_jasa_farmasi' => 1.15,
            'stok_aktual' => 0,
            'stok_minimum' => 100,
        ]);
    }

    public function test_ubah_isi_setelah_pilih_obat_mengoreksi_harga_box(): void
    {
        $this->actingAs(User::factory()->create());
        $obat = $this->obat(202.50);

        Livewire::test(PengajuanPengadaan::class)
            ->call('addRow')
            ->set('rows.0.isi_per_box', 1)
            ->set('rows.0.obat_id', $obat->id)          // autofill: unit 202,50 → box 202,50
            ->assertSet('rows.0.harga_per_box', 202.5)
            ->set('rows.0.isi_per_box', 100)            // SKENARIO BUG: ubah isi setelahnya
            ->assertSet('rows.0.harga_per_box', 20250.0) // FIX: harga/box ikut isi
            ->set('rows.0.jumlah_box', 37)
            ->assertSet('rows.0.subtotal_beli', 749250.0); // 37 × 20.250 (bukan Rp7.492)
    }

    public function test_ketik_harga_box_memperbarui_anchor_per_butir(): void
    {
        $this->actingAs(User::factory()->create());
        $obat = $this->obat(202.50);

        Livewire::test(PengajuanPengadaan::class)
            ->call('addRow')
            ->set('rows.0.isi_per_box', 100)
            ->set('rows.0.obat_id', $obat->id)
            ->set('rows.0.harga_per_box', 25000)   // user ketik harga/box asli dari faktur
            ->set('rows.0.isi_per_box', 50)        // ganti isi → box ikut anchor (25000/100=250 × 50)
            ->assertSet('rows.0.harga_per_box', 12500.0);
    }
}
