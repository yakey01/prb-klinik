<?php

namespace Tests\Feature;

use App\Livewire\PengajuanPengadaan;
use App\Models\Obat;
use App\Models\PengajuanPengadaan as PR;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pengajuan BMHP: kategori BMHP di form + buat item BMHP "on the fly" dari combobox.
 * BMHP (bahan medis habis pakai — mis. kapsul kosong) ditagih sebagai non_kronis,
 * tapi kategori 'bmhp' dipertahankan agar filter & tab konsisten dan bisa dikenali ulang saat edit.
 */
class PengajuanBmhpTest extends TestCase
{
    use RefreshDatabase;

    private function uidBaris0($c): string
    {
        $rows = $c->get('rows');

        return $rows[array_key_first($rows)]['uid'];
    }

    public function test_tambah_bmhp_membuat_item_katalog_dan_mengisi_baris(): void
    {
        $this->actingAs(User::factory()->create());

        $c = Livewire::test(PengajuanPengadaan::class)->call('openAdd', 'ajukan');
        $uid = $this->uidBaris0($c);

        $c->call('tambahBmhp', $uid, '  Kapsul   Kosong No.0  ');

        // Item katalog BMHP terbuat (nama dirapikan, spasi ganda → tunggal).
        $o = Obat::where('tipe_obat', 'bmhp')->first();
        $this->assertNotNull($o);
        $this->assertSame('Kapsul Kosong No.0', $o->nama_obat);
        $this->assertTrue((bool) $o->is_active);
        $this->assertEquals(0.0, (float) $o->klaim_bpjs_per_unit);

        // Baris terisi: obat terpilih, kategori bmhp, penagihan non_kronis.
        $row = $c->get('rows')[array_key_first($c->get('rows'))];
        $this->assertSame((int) $o->id, (int) $row['obat_id']);
        $this->assertSame('bmhp', $row['kategori']);
        $this->assertSame('non_kronis', $row['tipe_obat']);
    }

    public function test_tambah_bmhp_nama_sama_tidak_menduplikasi(): void
    {
        $this->actingAs(User::factory()->create());

        $c = Livewire::test(PengajuanPengadaan::class)->call('openAdd', 'ajukan');
        $uid = $this->uidBaris0($c);

        $c->call('tambahBmhp', $uid, 'Spuit 3cc');
        $c->call('tambahBmhp', $uid, 'spuit 3cc');   // beda kapital → tetap 1 item

        $this->assertSame(1, Obat::where('tipe_obat', 'bmhp')->count());
    }

    public function test_set_kategori_bmhp_reset_obat_dan_tagih_non_kronis(): void
    {
        $this->actingAs(User::factory()->create());

        $c = Livewire::test(PengajuanPengadaan::class)->call('openAdd', 'ajukan');
        $uid = $this->uidBaris0($c);

        $c->call('setTipeRow', $uid, 'bmhp');

        $row = $c->get('rows')[array_key_first($c->get('rows'))];
        $this->assertSame('bmhp', $row['kategori']);
        $this->assertSame('non_kronis', $row['tipe_obat']);
        $this->assertSame(0, (int) $row['obat_id']);
    }

    public function test_simpan_draft_bmhp_lalu_edit_mengenali_kembali_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $c = Livewire::test(PengajuanPengadaan::class)->call('openAdd', 'ajukan');
        $uid = $this->uidBaris0($c);
        $c->call('tambahBmhp', $uid, 'Kapas 250g');

        // Lengkapi harga & simpan sebagai draft.
        $key = array_key_first($c->get('rows'));
        $c->set("rows.$key.jumlah_box", 2)
            ->set("rows.$key.isi_per_box", 10)
            ->set("rows.$key.harga_per_box", 15000)
            ->call('simpan', false);

        $pr = PR::with('items')->latest('id')->first();
        $this->assertNotNull($pr);
        $it = $pr->items->first();
        $this->assertSame('non_kronis', $it->tipe_obat);       // ditagih non-kronis
        $this->assertEquals(0.0, (float) $it->estimasi_klaim);  // BMHP tak diklaim BPJS

        // Edit ulang → kategori dikenali kembali sebagai bmhp (dari obat katalog).
        $c2 = Livewire::test(PengajuanPengadaan::class)->call('openEdit', $pr->id);
        $row = $c2->get('rows')[array_key_first($c2->get('rows'))];
        $this->assertSame('bmhp', $row['kategori']);
    }
}
