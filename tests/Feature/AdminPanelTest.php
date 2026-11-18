<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_is_forbidden_from_admin_panel(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_panel_and_see_tabs(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertSee('Pusat Kendali Administrator');
        $response->assertSee('Manajemen User');
    }

    public function test_admin_can_update_user_information_and_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'nama' => 'Budi Lama',
            'username' => 'budilama',
            'email' => 'budilama@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
            'nama' => 'Budi Baru',
            'username' => 'budibaru',
            'email' => 'budibaru@example.com',
            'role' => 'admin',
            'nama_toko' => 'Toko Budi Sukses',
            'id_toko' => 'POSM-BUDI01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'nama' => 'Budi Baru',
            'username' => 'budibaru',
            'email' => 'budibaru@example.com',
            'role' => 'admin',
            'nama_toko' => 'Toko Budi Sukses',
            'id_toko' => 'POSM-BUDI01',
        ]);
    }

    public function test_admin_can_regenerate_user_security_key(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'username' => 'targetuser',
            'key' => 'old_hashed_key_123',
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/regenerate-key");

        $response->assertRedirect();
        $freshUser = $targetUser->fresh();
        $this->assertNotEmpty($freshUser->key);
        $this->assertNotEquals('old_hashed_key_123', $freshUser->key);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->delete("/admin/users/{$targetUser->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $admin->id,
            'name' => 'Produk Admin Toggle',
            'slug' => 'produk-admin-toggle-123',
            'product_type' => 'digital',
            'price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post("/admin/products/{$product->id}/toggle");

        $response->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($admin)->post("/admin/products/{$product->id}/toggle");
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_admin_can_destroy_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $admin->id,
            'name' => 'Produk Akan Dihapus Admin',
            'slug' => 'produk-hapus-admin-999',
            'product_type' => 'digital',
            'price' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
