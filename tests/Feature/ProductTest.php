<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_products(): void
    {
        $response = $this->get('/products');
        $response->assertRedirect('/login');

        $responseCreate = $this->get('/products/create');
        $responseCreate->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_product_create_page(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-TEST1234',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/products/create');
        $response->assertStatus(200);
        $response->assertSee('Input Produk Baru');
        $response->assertSee('Produk Digital');
    }

    public function test_user_can_create_digital_product_according_to_erd(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-SELLER01',
            'role' => 'user',
        ]);

        $postData = [
            'name' => 'E-Book Mahir POS Laravel 2026',
            'product_type' => 'digital',
            'price' => '75000',
            'file_url' => 'https://example.com/downloads/ebook-pos.pdf',
            'deskripsi' => 'Panduan komprehensif implementasi POS modern.',
            'is_active' => '1',
        ];

        $response = $this->actingAs($user)->post('/products', $postData);
        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $followResponse = $this->actingAs($user)->get('/products');
        $followResponse->assertStatus(200);
        $followResponse->assertSee('E-Book Mahir POS Laravel 2026');
        $followResponse->assertSee('Halaman Data Produk Digital');

        $this->assertDatabaseHas('products', [
            'name' => 'E-Book Mahir POS Laravel 2026',
            'user_id' => $user->id,
            'product_type' => 'digital',
            'price' => '75000.00',
            'is_active' => true,
        ]);

        $product = Product::where('name', 'E-Book Mahir POS Laravel 2026')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->uuid);
        $this->assertStringContainsString('e-book-mahir-pos-laravel-2026', $product->slug);
        $this->assertEquals('https://example.com/downloads/ebook-pos.pdf', $product->value['file_url']);
        $this->assertEquals('Panduan komprehensif implementasi POS modern.', $product->value['deskripsi']);
    }

    public function test_user_can_create_another_digital_product(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-SELLER02',
            'role' => 'user',
        ]);

        $postData = [
            'name' => 'Template UI Tailwind POS Market',
            'product_type' => 'digital',
            'price' => '120000',
            'file_url' => 'https://example.com/template-ui.zip',
            'deskripsi' => 'Template dashboard modern siap pakai.',
            'is_active' => '1',
        ];

        $response = $this->actingAs($user)->post('/products', $postData);
        $response->assertRedirect('/products');

        $this->assertDatabaseHas('products', [
            'name' => 'Template UI Tailwind POS Market',
            'user_id' => $user->id,
            'product_type' => 'digital',
            'price' => '120000.00',
        ]);
    }

    public function test_user_can_update_product(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-USERUPDATE',
            'role' => 'user',
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk Awal',
            'slug' => 'produk-awal-123456',
            'product_type' => 'digital',
            'price' => 10000,
            'is_active' => true,
            'value' => ['deskripsi' => 'Deskripsi lama'],
        ]);

        $response = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => 'Produk Telah Diperbarui',
            'product_type' => 'digital',
            'price' => 20000,
            'deskripsi' => 'Deskripsi baru',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produk Telah Diperbarui',
            'price' => '20000.00',
        ]);
    }

    public function test_user_can_soft_delete_product(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-USERDELETE',
            'role' => 'user',
        ]);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Produk Dihapus',
            'slug' => 'produk-dihapus-123',
            'product_type' => 'physical',
            'price' => 15000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->delete("/products/{$product->id}");
        $response->assertRedirect('/products');

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_user_cannot_update_another_users_product(): void
    {
        $user1 = User::factory()->create(['id_users' => 'POS-USER01']);
        $user2 = User::factory()->create(['id_users' => 'POS-USER02', 'role' => 'user']);

        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user1->id,
            'name' => 'Milik User 1',
            'slug' => 'milik-user-1',
            'product_type' => 'digital',
            'price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user2)->put("/products/{$product->id}", [
            'name' => 'Coba bajak produk',
            'product_type' => 'digital',
            'price' => 1000,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_set_and_update_stock_via_web(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-STOCKUSER', 'role' => 'user']);

        // Create with stock
        $response = $this->actingAs($user)->post('/products', [
            'name' => 'E-Book Terbatas',
            'product_type' => 'digital',
            'price' => '99000',
            'stock' => 75,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'name' => 'E-Book Terbatas',
            'user_id' => $user->id,
            'stock' => 75,
        ]);

        $product = Product::where('name', 'E-Book Terbatas')->first();

        // Update stock
        $updateResponse = $this->actingAs($user)->put("/products/{$product->id}", [
            'name' => 'E-Book Terbatas Edisi 2',
            'product_type' => 'digital',
            'price' => '120000',
            'stock' => 50,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'E-Book Terbatas Edisi 2',
            'stock' => 50,
        ]);
    }
}
