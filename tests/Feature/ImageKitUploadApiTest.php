<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageKitUploadApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        config(['services.imagekit.private_key' => 'private_test_key_123']);
        config(['services.imagekit.folder' => 'pos']);
        putenv("API_KEY={$this->apiKey}");
        $_ENV['API_KEY'] = $this->apiKey;
    }

    public function test_can_upload_image_to_dedicated_upload_endpoint(): void
    {
        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'file_xyz123',
                'name' => 'prod_test_img.png',
                'url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/prod_test_img.png',
                'filePath' => '/pos/prod_test_img.png',
                'thumbnailUrl' => 'https://ik.imagekit.io/p0v1tjsucu/tr:n-media_library_thumbnail/pos/prod_test_img.png',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('test_product.png', 400, 400);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->post('/v1/upload/image', [
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.provider', 'imagekit');
        $response->assertJsonPath('data.url', 'https://ik.imagekit.io/p0v1tjsucu/pos/prod_test_img.png');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'upload.imagekit.io');
        });
    }

    public function test_can_upload_image_to_products_upload_image_alias(): void
    {
        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'file_alias456',
                'name' => 'alias_img.jpg',
                'url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/alias_img.jpg',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('alias.jpg', 300, 300);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->post('/v1/products/upload-image', [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.url', 'https://ik.imagekit.io/p0v1tjsucu/pos/alias_img.jpg');
    }

    public function test_can_create_product_with_multipart_image_file_uploaded_to_imagekit(): void
    {
        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'file_prod789',
                'name' => 'product_cover.webp',
                'url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/product_cover.webp',
            ], 200),
        ]);

        $userKey = 'pos_imagekit_user_key_999';
        $user = User::factory()->create([
            'id_users' => 'POS-IMG01',
            'role' => 'user',
            'key' => $userKey,
        ]);

        $file = UploadedFile::fake()->image('cover.webp', 800, 600);

        $payload = [
            'name' => 'Template Dashboard POS Tailwind',
            'price' => 150000,
            'key' => $userKey,
            'id_users' => $user->id_users,
            'gambar_file' => $file,
            'deskripsi' => 'Dashboard modern siap pakai.',
        ];

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->post('/v1/products', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', 'Template Dashboard POS Tailwind');
        $response->assertJsonPath('data.value.gambar', 'https://ik.imagekit.io/p0v1tjsucu/pos/product_cover.webp');

        $this->assertDatabaseHas('products', [
            'name' => 'Template Dashboard POS Tailwind',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_update_product_image_using_multipart_file(): void
    {
        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'file_updated999',
                'name' => 'product_updated.jpg',
                'url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/product_updated.jpg',
            ], 200),
        ]);

        $userKey = 'pos_update_user_key_777';
        $user = User::factory()->create([
            'id_users' => 'POS-UP01',
            'role' => 'user',
            'key' => $userKey,
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk Awal',
            'slug' => 'produk-awal-test',
            'product_type' => 'digital',
            'price' => 50000,
            'is_active' => true,
            'value' => [
                'gambar' => 'https://ik.imagekit.io/p0v1tjsucu/pos/old_image.jpg',
                'user_key' => $userKey,
            ],
        ]);

        $newFile = UploadedFile::fake()->image('new_banner.jpg', 600, 400);

        // Test POST to /{id} with multipart file
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->post("/v1/products/{$product->id}", [
                'key' => $userKey,
                'name' => 'Produk Banner Baru',
                'gambar_file' => $newFile,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.name', 'Produk Banner Baru');
        $response->assertJsonPath('data.value.gambar', 'https://ik.imagekit.io/p0v1tjsucu/pos/product_updated.jpg');
    }

    public function test_upload_image_fails_if_file_is_not_an_image(): void
    {
        $nonImageFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->post('/v1/upload/image', [
                'image' => $nonImageFile,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'error');
    }
}
