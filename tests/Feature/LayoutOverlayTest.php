<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi: overlay drawer mobile pernah menutup SELURUH halaman (gelap + blur +
 * tak bisa diklik) karena hanya disembunyikan oleh binding Alpine, sedangkan
 * Alpine baru boot setelah @livewireScripts di akhir <body>.
 */
class LayoutOverlayTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlay_drawer_mobile_tersembunyi_secara_default(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

        // Buang komentar CSS supaya kata "display:none" di dalam komentar
        // tidak membuat test lulus palsu.
        $html = preg_replace('#/\*.*?\*/#s', '', $html);

        $this->assertMatchesRegularExpression(
            '/\.mobile-nav-overlay\s*\{[^}]*display:\s*none/s',
            $html,
            'Overlay drawer WAJIB display:none by default, jika tidak halaman gelap & terkunci sebelum Alpine boot.'
        );
    }

    /**
     * Blade meng-compile direktif ber-awalan @ WALAU di dalam komentar CSS.
     * Pernah terjadi: komentar berisi nama direktif skrip Livewire membuat seluruh
     * tag <script> Livewire ter-render di dalam <style> → tak pernah dieksekusi →
     * window.Livewire undefined → SEMUA tombol mati.
     */
    public function test_tidak_ada_script_di_dalam_blok_style(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

        preg_match_all('#<style\b[^>]*>(.*?)</style>#is', $html, $m);

        foreach ($m[1] as $isi) {
            $this->assertStringNotContainsString(
                '<script',
                $isi,
                'Ada <script> ter-render di dalam <style> — hampir pasti direktif Blade (@...) tertulis di komentar CSS.'
            );
        }
    }

    public function test_skrip_livewire_ter_render_sekali_dan_di_luar_style(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

        // Buang seluruh isi <style> — skrip Livewire harus tetap ada di luar.
        $tanpaStyle = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);

        $this->assertMatchesRegularExpression(
            '#<script[^>]+src="[^"]*livewire[^"]*\.js#i',
            $tanpaStyle,
            'Skrip Livewire tidak ter-render di luar <style> — tanpa ini Alpine/Livewire tak boot dan semua wire:click mati.'
        );
    }

    public function test_overlay_masih_bisa_dibuka_alpine(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('display:block;opacity:1', false);
    }
}
