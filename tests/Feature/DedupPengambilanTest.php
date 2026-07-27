<?php

namespace Tests\Feature;

use App\Livewire\RiwayatPengambilan;
use App\Models\ItemPengambilan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\PengambilanObat;
use App\Models\StokKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Bersihkan duplikat pengambilan: sisakan 1 per (pasien+tanggal), hapus sisanya,
 * dan KEMBALIKAN stok yang salah terpotong (kelas dunia — bukan asal soft-delete).
 */
class DedupPengambilanTest extends TestCase
{
    use RefreshDatabase;

    public function test_bersihkan_duplikat_hapus_redundan_dan_kembalikan_stok(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        $obat = Obat::create([
            'nama_obat' => 'Amlodipin 5mg', 'kode_obat' => 'A5', 'tipe_obat' => 'kronis',
            'is_active' => true, 'stok_aktual' => 100, 'stok_minimum' => 0,
            'harga_beli_per_unit' => 100, 'klaim_bpjs_per_unit' => 200, 'faktor_jasa_farmasi' => 1.15,
        ]);
        $pasien = Pasien::create([
            'nama' => 'Kolik Mawardi', 'no_bpjs' => '0000672433929', 'alamat' => 'x',
            'tanggal_lahir' => '1970-01-01', 'jenis_kelamin' => 'L', 'is_aktif' => true,
        ]);

        // Dua pengambilan DUPLIKAT (pasien+tanggal sama), masing-masing potong stok 10.
        $ids = [];
        foreach ([1, 2] as $n) {
            $po = PengambilanObat::create([
                'pasien_id' => $pasien->id, 'tanggal_pengambilan' => '2026-07-02',
                'status' => 'selesai', 'total_item' => 1,
            ]);
            ItemPengambilan::create([
                'pengambilan_obat_id' => $po->id, 'obat_id' => $obat->id, 'jumlah_unit' => 10, 'satuan' => 'tablet',
            ]);
            StokKeluar::create([
                'obat_id' => $obat->id, 'tanggal_keluar' => '2026-07-02', 'jumlah_unit' => 10,
                'stok_sebelum' => 100, 'stok_sesudah' => 90, 'satuan' => 'tablet',
                'harga_beli_snapshot' => 100, 'harga_jual_per_unit' => 230,
                'sumber' => 'pengambilan', 'pengambilan_obat_id' => $po->id, 'pasien_id' => $pasien->id,
            ]);
            $obat->decrement('stok_aktual', 10); // stok terpotong 2x (duplikat) → 80
            $ids[] = $po->id;
        }
        $this->assertSame(80, $obat->fresh()->stok_aktual);

        Livewire::test(RiwayatPengambilan::class)->call('bersihkanSemuaDuplikat');

        // Sisakan yang PERTAMA (id terkecil), hapus yang kedua.
        $this->assertNotSoftDeleted('pengambilan_obat', ['id' => $ids[0]]);
        $this->assertSoftDeleted('pengambilan_obat', ['id' => $ids[1]]);
        // Stok yang salah terpotong (10) dikembalikan → 90.
        $this->assertSame(90, $obat->fresh()->stok_aktual);
        // StokKeluar milik yang dihapus dibuang; milik yang disisakan tetap.
        $this->assertSame(0, StokKeluar::where('pengambilan_obat_id', $ids[1])->count());
        $this->assertSame(1, StokKeluar::where('pengambilan_obat_id', $ids[0])->count());
    }
}
