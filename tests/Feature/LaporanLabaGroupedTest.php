<?php

namespace Tests\Feature;

use App\Livewire\LaporanBulanan;
use App\Models\ItemPengambilan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PengambilanObat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Laporan laba per obat: dikelompokkan per diagnosis (grup rugi di atas) dan
 * bisa di-drill-down per pasien (siapa yang bikin rugi/surplus).
 */
class LaporanLabaGroupedTest extends TestCase
{
    use RefreshDatabase;

    private function obat(string $nama, string $diag, int $klaim, int $beli): Obat
    {
        return Obat::create([
            'nama_obat' => $nama, 'kode_obat' => strtoupper(substr(md5($nama), 0, 6)),
            'tipe_obat' => 'kronis', 'kategori_diagnosis' => $diag, 'is_active' => true,
            'stok_aktual' => 1000, 'stok_minimum' => 0,
            'harga_beli_per_unit' => $beli, 'klaim_bpjs_per_unit' => $klaim, 'faktor_jasa_farmasi' => 1.0,
        ]);
    }

    private function serah(Pasien $p, Obat $o, int $unit): void
    {
        $po = PengambilanObat::create([
            'pasien_id' => $p->id, 'tanggal_pengambilan' => '2026-07-10',
            'status' => 'selesai', 'total_item' => 1,
        ]);
        ItemPengambilan::create([
            'pengambilan_obat_id' => $po->id, 'obat_id' => $o->id, 'jumlah_unit' => $unit, 'satuan' => 'tablet',
            'harga_beli_snapshot' => $o->harga_beli_per_unit,
            'harga_klaim_bpjs_snapshot' => $o->klaim_bpjs_per_unit,
            'faktor_jasa_farmasi_snapshot' => 1.0,
        ]);
    }

    public function test_grup_per_diagnosis_rugi_di_atas_dan_drilldown_pasien(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        // Diabetes: untung (klaim 300 > beli 100).
        $metformin = $this->obat('Metformin', 'Diabetes', 300, 100);
        // Hipertensi: rugi (klaim 100 < beli 700).
        $amlodipin = $this->obat('Amlodipin', 'Hipertensi', 100, 700);

        $andi = Pasien::create(['nama' => 'Andi', 'no_bpjs' => '111', 'alamat' => 'x', 'tanggal_lahir' => '1970-01-01', 'jenis_kelamin' => 'L', 'is_aktif' => true]);
        $budi = Pasien::create(['nama' => 'Budi', 'no_bpjs' => '222', 'alamat' => 'x', 'tanggal_lahir' => '1970-01-01', 'jenis_kelamin' => 'L', 'is_aktif' => true]);

        $this->serah($andi, $metformin, 10);   // laba +2000
        $this->serah($andi, $amlodipin, 5);     // laba -3000
        $this->serah($budi, $amlodipin, 1);     // laba -600 (Andi > Budi kontribusi rugi)

        $c = Livewire::test(LaporanBulanan::class)->set('tahun', 2026)->set('bulan', 7)->set('activeTab', 'kronis');
        $groups = $c->instance()->detailBpjsGrouped();

        // Grup RUGI (Hipertensi) harus di indeks 0 (paling rugi di atas).
        $this->assertSame('Hipertensi', $groups[0]['kategori']);
        $this->assertSame('Diabetes', $groups[1]['kategori']);
        $this->assertTrue($groups[0]['laba'] < 0);
        $this->assertSame(1, $groups[0]['rugi_count']);

        // Drill-down Amlodipin: Andi (rugi -3000) harus di atas Budi (-600).
        $c->call('toggleObatDetail', $amlodipin->id);
        $per = $c->instance()->pasienPerObat();
        $this->assertSame('Andi', $per[0]['nama']);
        $this->assertSame(-3000.0, $per[0]['laba']);
        $this->assertSame('Budi', $per[1]['nama']);

        // Toggle lagi = tutup.
        $c->call('toggleObatDetail', $amlodipin->id);
        $this->assertEmpty($c->instance()->pasienPerObat());
    }
}
