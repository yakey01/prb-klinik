<?php

namespace Tests\Feature;

use App\Livewire\RekapPengadaan;
use App\Models\Distributor;
use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RekapPengadaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_untung_kronis_dihitung_klaim_minus_beli(): void
    {
        $this->actingAs(User::factory()->create());
        $dist = Distributor::create(['name' => 'PBF Test', 'is_active' => true]);
        $th = (int) now()->format('Y');

        $obat = Obat::create([
            'nama_obat' => 'Amlodipin 10mg', 'kode_obat' => 'AML10', 'tipe_obat' => 'kronis',
            'is_active' => true, 'klaim_bpjs_per_unit' => 200, 'faktor_jasa_farmasi' => 1.15,
            'harga_beli_per_unit' => 150, 'harga_jual_per_unit' => 300, 'stok_aktual' => 0, 'stok_minimum' => 0,
        ]);
        $po = PurchaseOrder::create([
            'distributor_id' => $dist->id, 'nomor_invoice' => 'INV-T1',
            'tanggal_po' => now()->startOfMonth()->format('Y-m-d'), 'total_nilai' => 150000, 'status_bayar' => 'belum',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id, 'obat_id' => $obat->id, 'tipe_obat' => 'kronis',
            'jumlah_box' => 10, 'isi_per_box' => 100, 'harga_per_box' => 15000, 'harga_per_unit' => 150,
            'subtotal' => 150000,
        ]);

        // qty=1000; klaim=1000×200×1.15=230.000; untung=230.000−150.000=80.000
        $comp = Livewire::test(RekapPengadaan::class);
        $rekap = $comp->get('rekap');

        $this->assertCount(1, $rekap);
        $this->assertSame((int) now()->format('n'), $rekap[0]['bln']);
        $this->assertEqualsWithDelta(150000, $rekap[0]['kronis_beli'], 0.5);
        $this->assertEqualsWithDelta(230000, $rekap[0]['kronis_klaim'], 0.5);
        $this->assertEqualsWithDelta(80000, $rekap[0]['kronis_untung'], 0.5);

        $ring = $comp->get('ringkasan');
        $this->assertEqualsWithDelta(80000, $ring['untung'], 0.5);
        $this->assertEqualsWithDelta(34.8, $ring['margin'], 0.2); // 80.000/230.000

        $this->assertSame($th, $comp->get('tahun'));
    }

    public function test_non_kronis_dipisah_dari_untung_kronis(): void
    {
        $this->actingAs(User::factory()->create());
        $dist = Distributor::create(['name' => 'PBF Test', 'is_active' => true]);

        $non = Obat::create([
            'nama_obat' => 'Paracetamol', 'kode_obat' => 'PCT', 'tipe_obat' => 'non_kronis',
            'is_active' => true, 'klaim_bpjs_per_unit' => 0, 'faktor_jasa_farmasi' => 1.15,
            'harga_beli_per_unit' => 100, 'harga_jual_per_unit' => 500, 'stok_aktual' => 0, 'stok_minimum' => 0,
        ]);
        $po = PurchaseOrder::create([
            'distributor_id' => $dist->id, 'nomor_invoice' => 'INV-T2',
            'tanggal_po' => now()->startOfMonth()->format('Y-m-d'), 'total_nilai' => 100000, 'status_bayar' => 'belum',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id, 'obat_id' => $non->id, 'tipe_obat' => 'non_kronis',
            'jumlah_box' => 10, 'isi_per_box' => 100, 'harga_per_box' => 10000, 'harga_per_unit' => 100,
            'subtotal' => 100000,
        ]);

        $ring = Livewire::test(RekapPengadaan::class)->get('ringkasan');
        // Untung KRONIS harus 0 (non-kronis tidak masuk hitungan kronis).
        $this->assertEqualsWithDelta(0, $ring['untung'], 0.5);
        $this->assertEqualsWithDelta(0, $ring['klaim'], 0.5);
    }
}
