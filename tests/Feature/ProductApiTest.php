<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
        $_ENV['API_KEY'] = $this->apiKey;
    }

    public function test_product_api_requires_api_key(): void
    {
        $response = $this->postJson('/v1/products', [
            'name' => 'E-Book Tanpa API Key',
            'price' => 50000,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'code' => 401,
        ]);
    }

    public function test_product_api_can_create_digital_product_with_erd_value_json(): void
    {
        $userKey = 'pos_user_login_key_sample123';
        $user = User::factory()->create([
            'id_users' => 'POS-DEV01',
            'role' => 'user',
            'key' => $userKey,
        ]);

        $payload = [
            'name' => 'Source Code Aplikasi Kasir POS Pro',
            'price' => 250000,
            'key' => $userKey,
            'file_url' => 'https://github.com/myorg/pos-pro-release.zip',
            'deskripsi' => 'Full source code Laravel 12 & Tailwind CSS.',
            'gambar' => 'https://images.unsplash.com/photo-1556742049-0a67c5574f73',
            'id_users' => $user->id_users,
            'is_active' => true,
        ];

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('code', 201);
        $response->assertJsonPath('data.name', 'Source Code Aplikasi Kasir POS Pro');
        $response->assertJsonPath('data.product_type', 'digital');
        $response->assertJsonPath('data.user_key', $userKey);
        $response->assertJsonPath('data.user.key', $userKey);
        $response->assertJsonPath('data.value.file_url', 'https://github.com/myorg/pos-pro-release.zip');
        $response->assertJsonPath('data.value.deskripsi', 'Full source code Laravel 12 & Tailwind CSS.');

        $this->assertDatabaseHas('products', [
            'name' => 'Source Code Aplikasi Kasir POS Pro',
            'user_id' => $user->id,
            'product_type' => 'digital',
        ]);
    }

    public function test_product_api_can_create_product_with_stock(): void
    {
        $userKey = 'stock_user_key_555';
        $user = User::factory()->create([
            'id_users' => 'POS-STOCK01',
            'key' => $userKey,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', [
                'name' => 'E-Book Edisi Terbatas',
                'price' => 150000,
                'stock' => 50,
                'key' => $userKey,
                'id_users' => $user->id_users,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.stock', 50);

        $this->assertDatabaseHas('products', [
            'name' => 'E-Book Edisi Terbatas',
            'user_id' => $user->id,
            'stock' => 50,
        ]);
    }

    public function test_product_api_create_fails_without_user_key(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-NOKEY01']);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', [
                'name' => 'Produk Tanpa Key User',
                'price' => 10000,
                'id_users' => $user->id_users,
            ]);

        $response->assertStatus(401);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('error', 'MISSING_USER_KEY');
    }

    public function test_product_api_create_fails_with_invalid_user_key(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-INVKEY01',
            'key' => 'actual_valid_key',
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', [
                'name' => 'Produk Key Salah',
                'price' => 10000,
                'id_users' => $user->id_users,
                'key' => 'wrong_invalid_key',
            ]);

        $response->assertStatus(401);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('error', 'INVALID_USER_KEY');
    }

    public function test_product_api_can_create_product_using_x_user_key_header(): void
    {
        $userKey = 'secret_header_key_999';
        $user = User::factory()->create([
            'id_users' => 'POS-HDR01',
            'key' => $userKey,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->withHeader('X-USER-KEY', $userKey)
            ->postJson('/v1/products', [
                'name' => 'Produk Via Header User Key',
                'price' => 75000,
                'id_users' => $user->id_users,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', 'Produk Via Header User Key');
        $response->assertJsonPath('data.user_key', $userKey);
    }

    public function test_product_api_can_create_product_with_raw_login_key_against_bcrypt_hash(): void
    {
        $keyData = \App\Services\AuthHelper::generateLoginKey('andrian');
        $user = User::factory()->create([
            'id_users' => 'POS-BCRYPT01',
            'username' => 'andrian',
            'key' => $keyData['hashed_key'],
        ]);

        // Kirimkan raw key (12 karakter acak + username)
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', [
                'name' => 'Template Notion Produktivitas 2026',
                'price' => 50000,
                'key' => $keyData['raw_key'],
                'id_users' => $user->id_users,
                'file_url' => 'https://notion.so/template-produktivitas',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', 'Template Notion Produktivitas 2026');
        $response->assertJsonPath('data.user_key', $keyData['hashed_key']);
        $response->assertJsonPath('data.value.user_key', $keyData['hashed_key']);
    }

    public function test_product_api_validation_failure_returns_422(): void
    {
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/products', [
                // 'name' is missing
                'price' => 'not-a-number',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 422);
        $response->assertJsonStructure([
            'errors' => ['name', 'price'],
        ]);
    }

    public function test_product_api_list_fails_without_id_users(): void
    {
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/products');

        $response->assertStatus(400);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('error', 'MISSING_USER_IDENTIFIER');
    }

    public function test_product_api_list_fails_with_nonexistent_id_users(): void
    {
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/products?id_users=NON-EXISTENT-USER');

        $response->assertStatus(404);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('error', 'USER_NOT_FOUND');
    }

    public function test_product_api_can_list_products_scoped_to_specific_user_without_key(): void
    {
        $user1 = User::factory()->create(['id_users' => 'POS-USER1', 'nama' => 'User Pertama']);
        $user2 = User::factory()->create(['id_users' => 'POS-USER2', 'nama' => 'User Kedua']);

        // Produk milik user 1
        $prod1 = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user1->id,
            'name' => 'Produk User 1 Template CV',
            'slug' => 'template-cv-user-1',
            'product_type' => 'digital',
            'price' => 35000,
            'is_active' => true,
            'value' => ['file_url' => 'https://example.com/cv.zip'],
        ]);

        // Produk milik user 2
        $prod2 = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user2->id,
            'name' => 'Produk User 2 E-Book AI',
            'slug' => 'ebook-ai-user-2',
            'product_type' => 'digital',
            'price' => 95000,
            'is_active' => true,
            'value' => ['file_url' => 'https://example.com/ai.pdf'],
        ]);

        // 1. Ambil data via query parameter ?id_users= (TANPA MEMASUKKAN KEY)
        $responseUser1 = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/products?id_users=' . $user1->id_users);

        $responseUser1->assertStatus(200);
        $responseUser1->assertJsonPath('status', 'success');
        $responseUser1->assertJsonPath('user.id_users', $user1->id_users);
        $responseUser1->assertJsonPath('meta.total', 1);
        $responseUser1->assertJsonPath('data.0.name', 'Produk User 1 Template CV');
        // Pastikan produk user 2 TIDAK bocor ke respon user 1
        $responseUser1->assertJsonMissing(['name' => 'Produk User 2 E-Book AI']);

        // 2. Ambil data via route /v1/products/user/{id_users} (TANPA MEMASUKKAN KEY)
        $responseUser2 = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/products/user/' . $user2->id_users);

        $responseUser2->assertStatus(200);
        $responseUser2->assertJsonPath('status', 'success');
        $responseUser2->assertJsonPath('user.id_users', $user2->id_users);
        $responseUser2->assertJsonPath('meta.total', 1);
        $responseUser2->assertJsonPath('data.0.name', 'Produk User 2 E-Book AI');
        // Pastikan produk user 1 TIDAK bocor ke respon user 2
        $responseUser2->assertJsonMissing(['name' => 'Produk User 1 Template CV']);
    }

    public function test_product_api_can_show_single_product(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-SHOW01']);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'E-Book Python AI',
            'slug' => 'e-book-python-ai-123456',
            'product_type' => 'digital',
            'price' => 89000,
            'is_active' => true,
            'value' => ['file_url' => 'https://example.com/python-ai.pdf'],
        ]);

        // Search by ID
        $responseId = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson("/v1/products/{$product->id}");
        $responseId->assertStatus(200);
        $responseId->assertJsonPath('data.name', 'E-Book Python AI');

        // Search by UUID
        $responseUuid = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson("/v1/products/{$product->uuid}");
        $responseUuid->assertStatus(200);
        $responseUuid->assertJsonPath('data.slug', 'e-book-python-ai-123456');
    }

    public function test_product_api_show_not_found_returns_404(): void
    {
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/products/non-existent-product-id');

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'code' => 404,
            'error' => 'PRODUCT_NOT_FOUND',
        ]);
    }

    public function test_product_api_can_update_product(): void
    {
        $userKey = 'user_secret_key_update_99';
        $user = User::factory()->create([
            'id_users' => 'POS-UP01',
            'key' => $userKey,
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Harga Lama',
            'slug' => 'harga-lama-123',
            'product_type' => 'digital',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
            'value' => ['deskripsi' => 'Deskripsi lama'],
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->putJson("/v1/products/{$product->id}", [
                'key' => $userKey,
                'name' => 'Harga Promo',
                'price' => 39000,
                'stock' => 25,
                'deskripsi' => 'Deskripsi diperbarui promo.',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', 'Harga Promo');
        $response->assertJsonPath('data.stock', 25);
        $response->assertJsonPath('data.user_key', $userKey);
        $response->assertJsonPath('data.value.deskripsi', 'Deskripsi diperbarui promo.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Harga Promo',
            'price' => '39000.00',
            'stock' => 25,
        ]);
    }

    public function test_product_api_update_fails_without_user_key(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-UPNOKEY', 'key' => 'key123']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk A',
            'slug' => 'produk-a-123',
            'product_type' => 'digital',
            'price' => 50000,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->putJson("/v1/products/{$product->id}", [
                'name' => 'Produk Ganti Nama',
            ]);

        $response->assertStatus(401);
        $response->assertJsonPath('error', 'MISSING_USER_KEY');
    }

    public function test_product_api_update_fails_with_invalid_user_key(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-UPINVKEY', 'key' => 'valid_key_123']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk B',
            'slug' => 'produk-b-123',
            'product_type' => 'digital',
            'price' => 50000,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->putJson("/v1/products/{$product->id}", [
                'key' => 'totally_wrong_key',
                'name' => 'Produk Ganti Nama',
            ]);

        $response->assertStatus(401);
        $response->assertJsonPath('error', 'INVALID_USER_KEY');
    }

    public function test_product_api_update_fails_when_user_is_not_product_owner(): void
    {
        $owner = User::factory()->create(['id_users' => 'POS-OWNER', 'key' => 'owner_key_123']);
        $otherUser = User::factory()->create(['id_users' => 'POS-OTHER', 'key' => 'other_user_key_456']);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $owner->id,
            'name' => 'Produk Milik Owner',
            'slug' => 'produk-milik-owner-123',
            'product_type' => 'digital',
            'price' => 50000,
        ]);

        // User lain mencoba mengedit produk owner dengan key milik user lain
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->putJson("/v1/products/{$product->id}", [
                'key' => 'other_user_key_456',
                'name' => 'Diretas Orang Lain',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'FORBIDDEN_NOT_OWNER');
    }

    public function test_product_api_can_soft_delete_product(): void
    {
        $userKey = 'secret_delete_key_777';
        $user = User::factory()->create([
            'id_users' => 'POS-DEL01',
            'key' => $userKey,
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk Akan Dihapus',
            'slug' => 'produk-akan-dihapus-123',
            'product_type' => 'digital',
            'price' => 10000,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->deleteJson("/v1/products/{$product->id}", [
                'key' => $userKey,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('code', 200);
        $response->assertJsonPath('data.user_key', $userKey);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_api_delete_fails_without_user_key(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-DELNOKEY', 'key' => 'del_key_123']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk C',
            'slug' => 'produk-c-123',
            'product_type' => 'digital',
            'price' => 10000,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->deleteJson("/v1/products/{$product->id}");

        $response->assertStatus(401);
        $response->assertJsonPath('error', 'MISSING_USER_KEY');
    }

    public function test_product_api_delete_fails_when_user_is_not_product_owner(): void
    {
        $owner = User::factory()->create(['id_users' => 'POS-DELOWNER', 'key' => 'owner_del_key']);
        $attacker = User::factory()->create(['id_users' => 'POS-ATTACKER', 'key' => 'attacker_key']);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $owner->id,
            'name' => 'Produk Aman',
            'slug' => 'produk-aman-123',
            'product_type' => 'digital',
            'price' => 10000,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->deleteJson("/v1/products/{$product->id}", [
                'key' => 'attacker_key',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'FORBIDDEN_NOT_OWNER');
    }

    public function test_product_api_can_delete_using_slug_and_uuid_safely_for_postgresql(): void
    {
        $userKey = 'del_pg_key_999';
        $user = User::factory()->create([
            'id_users' => 'POS-DELPG',
            'key' => $userKey,
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk Test Postgres',
            'slug' => 'produk-test-postgres-slug',
            'product_type' => 'digital',
            'price' => 15000,
            'is_active' => true,
        ]);

        // Test delete via slug safely without invalid uuid type error
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->deleteJson("/v1/products/{$product->slug}", [
                'key' => $userKey,
            ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
