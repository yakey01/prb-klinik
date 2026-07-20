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

    public function test_overlay_masih_bisa_dibuka_alpine(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('display:block;opacity:1', false);
    }
}
