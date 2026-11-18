<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StoreWebsite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => 'pos_secret_token_1234567890']);
    }

    public function test_can_get_store_and_products_by_exact_nama_toko(): void
    {
        $seller = User::factory()->create([
            'nama' => 'Ahmad Penjual',
            'username' => 'ahmadshop',
            'email' => 'ahmad@example.com',
            'id_users' => 'POS-AHMAD99',
            'id_toko' => 'POSM-AHMAD01',
            'nama_toko' => 'Toko Digital Ahmad',
        ]);

        $prodActive1 = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Source Code Aplikasi Kasir POS Pro',
            'slug' => 'source-code-pos-pro',
            'product_type' => 'digital',
            'price' => 150000,
            'stock' => 20,
            'is_active' => true,
            'value' => [
                'gambar' => 'https://ik.imagekit.io/p0v1tjsucu/pos/cover1.jpg',
                'deskripsi' => 'Full source code Laravel & Next.js',
                'file_url' => 'https://example.com/download.zip',
            ],
        ]);

        $prodActive2 = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Panduan REST API Laravel',
            'slug' => 'ebook-rest-api-laravel',
            'product_type' => 'digital',
            'price' => 50000,
            'stock' => null, // unlimited
            'is_active' => true,
            'value' => [
                'gambar' => 'https://ik.imagekit.io/p0v1tjsucu/pos/cover2.jpg',
                'deskripsi' => 'Panduan lengkap API modern',
            ],
        ]);

        // Inactive product should NOT be shown on public storefront
        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Produk Nonaktif Draf',
            'slug' => 'produk-nonaktif-draf',
            'product_type' => 'digital',
            'price' => 25000,
            'is_active' => false,
        ]);

        $response = $this->getJson('/v1/website/Toko Digital Ahmad');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.store.id_users', 'POS-AHMAD99');
        $response->assertJsonPath('data.store.id_toko', 'POSM-AHMAD01');
        $response->assertJsonPath('data.store.nama_toko', 'Toko Digital Ahmad');
        $response->assertJsonPath('data.store.owner.nama', 'Ahmad Penjual');
        $response->assertJsonPath('data.store.total_products', 2);

        $response->assertJsonCount(2, 'data.products');
        $response->assertJsonFragment([
            'name' => 'Source Code Aplikasi Kasir POS Pro',
            'price' => 150000,
            'image' => 'https://ik.imagekit.io/p0v1tjsucu/pos/cover1.jpg',
        ]);
        $response->assertJsonFragment([
            'name' => 'E-Book Panduan REST API Laravel',
            'price' => 50000,
            'is_unlimited_stock' => true,
        ]);
    }

    public function test_can_get_store_by_slugified_nama_toko(): void
    {
        $seller = User::factory()->create([
            'id_users' => 'POS-SLUG01',
            'id_toko' => 'POSM-SLUG01',
            'nama_toko' => 'Berkah Digital Store',
        ]);

        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Template Dashboard Tailwind',
            'slug' => 'template-dashboard-tailwind',
            'product_type' => 'digital',
            'price' => 75000,
            'is_active' => true,
        ]);

        // Call using kebab-case slug: berkah-digital-store
        $response = $this->getJson('/v1/website/berkah-digital-store');

        $response->assertStatus(200);
        $response->assertJsonPath('data.store.id_users', 'POS-SLUG01');
        $response->assertJsonPath('data.store.nama_toko', 'Berkah Digital Store');
        $response->assertJsonCount(1, 'data.products');
    }

    public function test_can_get_store_by_id_toko(): void
    {
        $seller = User::factory()->create([
            'id_users' => 'POS-IDTOKO9',
            'id_toko' => 'POSM-KITA88',
            'nama_toko' => 'Toko Jaya Mandiri',
        ]);

        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'UI Kit Figma',
            'slug' => 'ui-kit-figma-pro',
            'product_type' => 'digital',
            'price' => 99000,
            'is_active' => true,
        ]);

        $response = $this->getJson('/v1/website/POSM-KITA88');

        $response->assertStatus(200);
        $response->assertJsonPath('data.store.id_toko', 'POSM-KITA88');
        $response->assertJsonPath('data.store.id_users', 'POS-IDTOKO9');
    }

    public function test_can_get_store_by_store_website_slug(): void
    {
        $seller = User::factory()->create([
            'id_users' => 'POS-STOREWEB1',
            'nama_toko' => 'Mitra Solusi IT',
        ]);

        StoreWebsite::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'store_name' => 'Mitra Solusi IT',
            'slug' => 'mitra-solusi',
            'is_published' => true,
        ]);

        $response = $this->getJson('/v1/website/mitra-solusi');

        $response->assertStatus(200);
        $response->assertJsonPath('data.store.id_users', 'POS-STOREWEB1');
        $response->assertJsonPath('data.store.website_settings.slug', 'mitra-solusi');
    }

    public function test_can_filter_products_inside_store_by_search_keyword(): void
    {
        $seller = User::factory()->create([
            'id_users' => 'POS-SEARCH01',
            'nama_toko' => 'Mega Software Shop',
        ]);

        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Aplikasi Akuntansi Excel Pro',
            'slug' => 'aplikasi-akuntansi-excel',
            'product_type' => 'digital',
            'price' => 120000,
            'is_active' => true,
        ]);

        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Source Code Flutter E-Commerce',
            'slug' => 'source-code-flutter-ecommerce',
            'product_type' => 'digital',
            'price' => 350000,
            'is_active' => true,
        ]);

        // Search for "Akuntansi"
        $response = $this->getJson('/v1/website/mega-software-shop?search=Akuntansi');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.products');
        $response->assertJsonPath('data.products.0.name', 'Aplikasi Akuntansi Excel Pro');
    }

    public function test_can_get_store_via_query_parameter(): void
    {
        $seller = User::factory()->create([
            'id_users' => 'POS-QUERY01',
            'nama_toko' => 'Studio Kreatif ID',
        ]);

        Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Template Banner Sosmed',
            'slug' => 'template-banner-sosmed',
            'product_type' => 'digital',
            'price' => 45000,
            'is_active' => true,
        ]);

        $response = $this->getJson('/v1/website?nama_toko=studio-kreatif-id');

        $response->assertStatus(200);
        $response->assertJsonPath('data.store.id_users', 'POS-QUERY01');
        $response->assertJsonPath('data.store.nama_toko', 'Studio Kreatif ID');
    }

    public function test_returns_404_when_store_not_found(): void
    {
        $response = $this->getJson('/v1/website/toko-fiktif-yang-pasti-tidak-ada-999');

        $response->assertStatus(404);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 404);
        $response->assertJsonPath('error', 'STORE_NOT_FOUND');
    }
}
