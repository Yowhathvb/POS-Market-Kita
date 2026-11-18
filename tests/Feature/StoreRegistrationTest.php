<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
    }

    public function test_web_register_with_nama_toko_and_auto_generated_id_toko_posm(): void
    {
        $response = $this->post('/register', [
            'nama' => 'Budi Santoso',
            'nama_toko' => 'Digital Creative Media',
            'username' => 'budicreative',
            'email' => 'budi.creative@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');

        $user = User::where('username', 'budicreative')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Digital Creative Media', $user->nama_toko);

        // id_users: format POS-XXXXXXXX (>5 chars after prefix)
        $this->assertStringStartsWith('POS-', $user->id_users);

        // id_toko: format POSM-XXXXXXXX (>5 chars after prefix)
        $this->assertNotNull($user->id_toko);
        $this->assertStringStartsWith('POSM-', $user->id_toko);
        $this->assertGreaterThan(9, strlen($user->id_toko));

        // Store website auto-created
        $this->assertDatabaseHas('store_websites', [
            'user_id' => $user->id,
            'store_name' => 'Digital Creative Media',
        ]);
    }

    public function test_api_register_returns_id_toko_and_nama_toko(): void
    {
        $response = $this->postJson('/v1/auth/register', [
            'nama' => 'Siti Aisyah',
            'nama_toko' => 'Aisyah Modest Fashion',
            'username' => 'aisyah_store',
            'email' => 'aisyah@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.user.nama_toko', 'Aisyah Modest Fashion');
        $idToko = $response->json('data.user.id_toko');
        $this->assertNotNull($idToko);
        $this->assertStringStartsWith('POSM-', $idToko);

        // Check login returns id_toko and nama_toko
        $loginRes = $this->postJson('/v1/auth/login', [
            'login' => 'aisyah_store',
            'password' => 'secret123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $loginRes->assertStatus(200);
        $loginRes->assertJsonPath('data.user.id_toko', $idToko);
        $loginRes->assertJsonPath('data.user.nama_toko', 'Aisyah Modest Fashion');

        // Check /me returns id_toko and nama_toko
        $meRes = $this->getJson('/v1/auth/me?id_users=' . $response->json('data.user.id_users'), [
            'X-API-KEY' => $this->apiKey,
        ]);

        $meRes->assertStatus(200);
        $meRes->assertJsonPath('data.user.id_toko', $idToko);
        $meRes->assertJsonPath('data.user.nama_toko', 'Aisyah Modest Fashion');
    }

    public function test_api_rejects_invalid_id_toko_format(): void
    {
        $response = $this->postJson('/v1/auth/register', [
            'nama' => 'Toko Gagal',
            'username' => 'tokogagal',
            'email' => 'gagal@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'id_toko' => 'SALAH_PREFIX_123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_toko']);
    }
}
