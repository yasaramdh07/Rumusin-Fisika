<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Masuk ke Rumusin');
        $this->get('/daftar')->assertOk()->assertSee('Buat akun baru');
    }

    public function test_user_can_login_with_username_and_redirect_to_home(): void
    {
        $secret = Str::random(16);
        $user = User::factory()->create([
            'username' => 'usersaturumusin',
            'email' => 'user1@rumusin.test',
            'password' => $secret,
        ]);

        $response = $this->post('/masuk', [
            'identity' => 'usersaturumusin',
            'password' => $secret,
        ]);

        $response->assertRedirect('/beranda');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_email_case_insensitively(): void
    {
        $secret = Str::random(16);
        $user = User::factory()->create([
            'username' => 'userduarumusin',
            'email' => 'user2@rumusin.test',
            'password' => $secret,
        ]);

        $response = $this->post('/masuk', [
            'identity' => 'USER2@rumusin.test',
            'password' => $secret,
        ]);

        $response->assertRedirect('/beranda');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_without_email_and_redirect_to_admin(): void
    {
        $secret = Str::random(16);
        $admin = User::factory()->admin()->create([
            'username' => 'admin_rumusin',
            'email' => null,
            'password' => $secret,
        ]);

        $response = $this->post('/masuk', [
            'identity' => 'admin_rumusin',
            'password' => $secret,
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('Dasbor Admin');
    }

    public function test_login_is_throttled_after_five_failed_attempts_per_identity_and_ip(): void
    {
        $secret = Str::random(16);
        User::factory()->create([
            'username' => 'usertigarumusin',
            'password' => $secret,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', [
                'identity' => 'usertigarumusin',
                'password' => 'wrong-attempt-'.$i,
            ])->assertSessionHasErrors('identity');
        }

        $response = $this->from('/masuk')->post('/masuk', [
            'identity' => 'usertigarumusin',
            'password' => $secret,
        ]);

        $response->assertRedirect('/masuk');
        $response->assertSessionHasErrors('identity');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk',
            (string) session('errors')?->first('identity')
        );
        $this->assertGuest();
    }

    public function test_non_admin_user_receives_403_on_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_valid_registration_creates_user_role_and_logs_in(): void
    {
        $secret = Str::random(16);

        $response = $this->post('/daftar', [
            'name' => 'Pelajar Fisika',
            'username' => 'pelajar_01',
            'email' => 'Pelajar@Sekolah.ID',
            'password' => $secret,
            'password_confirmation' => $secret,
        ]);

        $response->assertRedirect('/beranda');
        $this->assertAuthenticated();

        $user = User::where('username', 'pelajar_01')->firstOrFail();
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertSame('pelajar@sekolah.id', $user->email);
        $this->assertTrue($user->is_active);
    }

    public function test_invalid_registration_returns_indonesian_validation_errors(): void
    {
        User::factory()->create([
            'username' => 'sudah_ada',
            'email' => 'ada@rumusin.test',
        ]);

        $response = $this->from('/daftar')->post('/daftar', [
            'name' => '',
            'username' => 'HurufBesar!',
            'email' => 'ada@rumusin.test',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ]);

        $response->assertRedirect('/daftar');
        $response->assertSessionHasErrors(['name', 'username', 'email', 'password']);

        $errors = session('errors');
        $this->assertSame('Nama wajib diisi.', $errors->first('name'));
        $this->assertStringContainsString('huruf kecil, angka, dan garis bawah', $errors->first('username'));
        $this->assertStringContainsString('Email sudah terdaftar', $errors->first('email'));
        $this->assertStringContainsString('minimal 8 karakter', $errors->first('password'));
        $this->assertGuest();
    }

    public function test_user_can_logout_via_post_and_update_profile(): void
    {
        $oldSecret = Str::random(16);
        $newSecret = Str::random(16);
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'password' => $oldSecret,
        ]);

        $this->actingAs($user)
            ->get('/profil')
            ->assertOk()
            ->assertSee('Nama Lama');

        $this->actingAs($user)
            ->put('/profil', [
                'name' => 'Nama Baru',
                'current_password' => $oldSecret,
                'password' => $newSecret,
                'password_confirmation' => $newSecret,
            ])
            ->assertRedirect('/profil');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertTrue(Hash::check($newSecret, $user->password));

        $this->actingAs($user)
            ->post('/keluar')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
