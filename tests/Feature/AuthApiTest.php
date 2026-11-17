<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
    }

    public function test_api_requires_api_key(): void
    {
        // Without API Key -> 401
        $response = $this->postJson('/v1/auth/login', [
            'login' => 'budi',
            'password' => 'secret',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'code' => 401,
            'error' => 'MISSING_API_KEY',
        ]);
    }

    public function test_api_rejects_invalid_api_key(): void
    {
        // With wrong API Key -> 401
        $response = $this->postJson('/v1/auth/login', [
            'login' => 'budi',
            'password' => 'secret',
        ], [
            'X-API-KEY' => 'wrong_api_key_123',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'code' => 401,
            'error' => 'INVALID_API_KEY',
        ]);
    }

    public function test_api_register_creates_user_with_null_key(): void
    {
        $payload = [
            'nama' => 'Ahmad Dhani',
            'username' => 'ahmaddhani',
            'email' => 'dhani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'value' => [
                'phone' => '081122334455',
                'address' => 'Jakarta Selatan',
            ],
        ];

        $response = $this->postJson('/v1/auth/register', $payload, [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('code', 201);
        $response->assertJsonPath('data.user.username', 'ahmaddhani');
        $response->assertJsonPath('data.user.role', 'user');
        $response->assertJsonPath('data.user.key', null); // Key is null until login!
        $response->assertJsonPath('data.user.value.phone', '081122334455');

        $this->assertDatabaseHas('users', [
            'username' => 'ahmaddhani',
            'email' => 'dhani@example.com',
            'role' => 'user',
            'key' => null,
        ]);
    }

    public function test_api_register_validation_failure_returns_422(): void
    {
        $response = $this->postJson('/v1/auth/register', [
            'nama' => '',
            'email' => 'not-an-email',
            'password' => '123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonStructure(['errors' => ['nama', 'username', 'email', 'password']]);
    }

    public function test_api_login_generates_random12_plus_username_and_hashes_to_key(): void
    {
        $user = User::factory()->create([
            'username' => 'siti_aminah',
            'email' => 'siti@example.com',
            'password' => Hash::make('password123'),
            'key' => null,
        ]);

        $response = $this->postJson('/v1/auth/login', [
            'login' => 'siti_aminah',
            'password' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('code', 200);

        // Verify key generation
        $random12 = $response->json('data.login_key_details.random_12');
        $rawCombined = $response->json('data.login_key_details.raw_key_combined');
        $hashedStored = $response->json('data.login_key_details.hashed_key_stored');

        $this->assertNotNull($random12);
        $this->assertEquals(12, strlen($random12));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{12}$/', $random12);
        $this->assertEquals($random12 . 'siti_aminah', $rawCombined);

        // Verify it was hashed and saved to the database
        $user->refresh();
        $this->assertNotNull($user->key);
        $this->assertEquals($hashedStored, $user->key);
        $this->assertTrue(Hash::check($rawCombined, $user->key));
    }

    public function test_api_login_invalid_password_returns_401(): void
    {
        User::factory()->create([
            'username' => 'budi_santoso',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/v1/auth/login', [
            'login' => 'budi_santoso',
            'password' => 'wrong_password',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'code' => 401,
            'error' => 'INVALID_CREDENTIALS',
        ]);
    }

    public function test_api_update_value_json_column(): void
    {
        $user = User::factory()->create([
            'id_users' => 'USR-TEST001',
            'username' => 'testuser',
            'value' => ['initial' => 'data'],
        ]);

        $response = $this->postJson('/v1/auth/update-value', [
            'id_users' => 'USR-TEST001',
            'value' => [
                'theme' => 'dark',
                'language' => 'id',
                'credit' => 500000,
            ],
            'mode' => 'merge',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.value.theme', 'dark');
        $response->assertJsonPath('data.value.credit', 500000);

        $user->refresh();
        $this->assertEquals('dark', $user->value['theme']);
        $this->assertEquals('data', $user->value['initial']);
    }

    public function test_api_status_demo_endpoint_all_codes(): void
    {
        $codes = [200, 201, 203, 400, 401, 403, 404, 422, 500];

        foreach ($codes as $code) {
            $response = $this->getJson("/v1/auth/status-demo/{$code}", [
                'X-API-KEY' => $this->apiKey,
            ]);

            $response->assertStatus($code);
            $response->assertJsonPath('code', $code);
        }
    }
}
