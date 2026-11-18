<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
        $response->assertSee('Username atau Email');
    }

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Registrasi Akun Baru');
        $response->assertSee('Nama Lengkap');
        $response->assertSee('Data Kolom \'value\' (JSON Type)', false);
        // Ensure no role select option exists in registration view
        $response->assertDontSee('<select id="role"', false);
    }

    public function test_web_register_creates_user_with_automatic_pos_id_and_default_user_role(): void
    {
        $response = $this->post('/register', [
            'nama' => 'Joko Widodo',
            'username' => 'jokowi',
            'email' => 'jokowi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'address' => 'Solo',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'username' => 'jokowi',
            'email' => 'jokowi@example.com',
            'role' => 'user', // Default role is automatically user
            'key' => null, // Key is null on register!
        ]);

        $user = User::where('username', 'jokowi')->first();
        $this->assertNotNull($user->id_users);
        $this->assertStringStartsWith('POS-', $user->id_users);
        // Prefix POS- (4 chars) + random chars (>5 chars) = length > 9
        $this->assertGreaterThan(9, strlen($user->id_users));
        $this->assertEquals('user', $user->role);
        $this->assertEquals('081234567890', $user->value['phone']);
        $this->assertEquals('Solo', $user->value['address']);
    }

    public function test_web_login_updates_key_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'citra_dewi',
            'email' => 'citra@example.com',
            'password' => Hash::make('password123'),
            'key' => null,
        ]);

        $response = $this->post('/login', [
            'login' => 'citra_dewi',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify key was updated in DB
        $user->refresh();
        $this->assertNotNull($user->key);
    }

    public function test_dashboard_renders_sidebar_with_usecase_menu(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Verify sidebar use-case menu items are present
        $response->assertSeeText('Data Produk');
        $response->assertSeeText('Mengelola Produk');
        $response->assertSeeText('Setting Web Penjual');
        $response->assertSeeText('Checkout & Transaksi');
        $response->assertSeeText('Mengelola Saldo (Wallet)');
        $response->assertSeeText('Dokumentasi API');

        // Verify removed menu items are NOT in sidebar
        $response->assertDontSee('Payment (Pembayaran)');
        $response->assertDontSee('Magic Link Produk');

        // Verify Landing Page is NOT included in the sidebar menu
        $response->assertDontSee('Melihat Landing Page');
    }

    public function test_docs_page_is_accessible(): void
    {
        // 1. Guest (unauthenticated) can access public docs without login
        $guestResponse = $this->get('/docs');
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('Dokumentasi Lengkap API POS Market Kita');
        $guestResponse->assertSee('Public Docs');
        $guestResponse->assertSee('Daftar Isi');
        $guestResponse->assertSee('/v1/auth/register');
        $guestResponse->assertSee('/v1/auth/login');
        $guestResponse->assertSee('/v1/products');
        $guestResponse->assertSee('Khusus Produk Digital');
        $guestResponse->assertSee('203 Non-Authoritative Information');

        // 2. Authenticated user can also access public docs
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/docs');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Dashboard Penjual');
        $authResponse->assertSee('Dokumentasi Lengkap API POS Market Kita');
    }

    public function test_web_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
