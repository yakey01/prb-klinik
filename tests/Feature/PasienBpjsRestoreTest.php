<?php

namespace Tests\Feature;

use App\Livewire\PasienManager;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresi: pasien di-soft-delete lalu No.BPJS-nya dipakai lagi. Dulu ditolak
 * ("Nomor BPJS sudah terdaftar") karena unique index/validasi ikut menghitung
 * baris terhapus. Sekarang → pasien lama DIPULIHKAN, bukan gagal.
 */
class PasienBpjsRestoreTest extends TestCase
{
    use RefreshDatabase;

    private function isi($c, string $bpjs, string $nama): void
    {
        $c->set('nama', $nama)
            ->set('no_bpjs', $bpjs)
            ->set('alamat', 'Jl. Contoh 123')
            ->set('tanggal_lahir', '1970-01-01')
            ->set('jenis_kelamin', 'P')
            ->set('kategori_diagnosis', 'Hipertensi');
    }

    public function test_bpjs_pasien_terhapus_bisa_dipakai_lagi_dan_memulihkan(): void
    {
        $this->actingAs(User::factory()->create());
        $bpjs = '0002064764204';

        $lama = Pasien::create([
            'nama' => 'Siti Umayah', 'no_bpjs' => $bpjs, 'alamat' => 'lama',
            'tanggal_lahir' => '1973-09-10', 'jenis_kelamin' => 'P', 'is_aktif' => true,
        ]);
        $lama->delete(); // soft delete

        $this->assertSoftDeleted('pasien', ['id' => $lama->id]);

        $c = Livewire::test(PasienManager::class)->call('openAdd');
        $this->isi($c, $bpjs, 'Siti Umayyah');
        $c->call('save')->assertHasNoErrors();

        // Pasien lama dipulihkan (bukan baris baru) + data diperbarui.
        $this->assertDatabaseCount('pasien', 1);
        $p = Pasien::where('no_bpjs', $bpjs)->firstOrFail();
        $this->assertSame($lama->id, $p->id);          // baris yang SAMA (id lama)
        $this->assertNull($p->deleted_at);             // sudah hidup lagi
        $this->assertSame('Siti Umayyah', $p->nama);   // data ter-update
    }

    public function test_bpjs_pasien_HIDUP_tetap_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $bpjs = '0001111111111';
        Pasien::create([
            'nama' => 'Aktif', 'no_bpjs' => $bpjs, 'alamat' => 'x',
            'tanggal_lahir' => '1980-01-01', 'jenis_kelamin' => 'L', 'is_aktif' => true,
        ]);

        $c = Livewire::test(PasienManager::class)->call('openAdd');
        $this->isi($c, $bpjs, 'Duplikat');
        $c->call('save')->assertHasErrors(['no_bpjs']); // masih ditolak (pasien hidup)
    }
}
