<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registrasi publik SENGAJA DIMATIKAN (routes/auth.php) — akun klinik hanya
 * dibuat administrator lewat Manajemen Akun. Test ini mengunci properti
 * keamanan tersebut agar tidak terbuka lagi tanpa sengaja.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_disabled_and_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_public_registration_does_not_create_a_user(): void
    {
        $before = User::count();

        $response = $this->post('/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login', absolute: false));
        $this->assertGuest();
        $this->assertSame($before, User::count(), 'Registrasi publik tidak boleh membuat akun.');
        $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
    }
}
