<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_wallet(): void
    {
        $response = $this->get('/wallet');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_wallet_page(): void
    {
        $user = User::factory()->create([
            'id_users' => 'POS-WALLETWEB',
            'role' => 'user',
        ]);

        $wallet = $user->getOrCreateWallet();
        $wallet->balance = 350000;
        $wallet->save();

        $response = $this->actingAs($user)->get('/wallet');
        $response->assertStatus(200);
        $response->assertSee('Wallet dan Saldo Penjual');
        $response->assertSee('350.000');
        $response->assertSee('Rekening Pencairan');
    }

    public function test_user_can_update_bank_account_via_web(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-BANKWEB']);

        $response = $this->actingAs($user)->post('/wallet/bank', [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Budi Santoso',
        ]);

        $response->assertRedirect('/wallet');
        $response->assertSessionHas('success');

        $wallet = $user->getOrCreateWallet();
        $this->assertEquals('BCA', $wallet->value['bank_account']['bank_name']);
        $this->assertEquals('1234567890', $wallet->value['bank_account']['account_number']);
    }

    public function test_user_can_withdraw_balance_via_web(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-WDWEB']);
        $wallet = $user->getOrCreateWallet();
        $wallet->balance = 100000;
        $wallet->value = [
            'bank_account' => [
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Budi Santoso',
            ]
        ];
        $wallet->save();

        $response = $this->actingAs($user)->post('/wallet/withdraw', [
            'amount' => 50000,
        ]);

        $response->assertRedirect('/wallet');
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals(50000, (float) $wallet->balance);
        $this->assertEquals(50000, (float) $wallet->pending_balance);
    }

    public function test_public_user_can_view_checkout_page(): void
    {
        $seller = User::factory()->create(['id_users' => 'POS-SELLERWEB']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Laravel Master',
            'slug' => 'ebook-laravel-master',
            'product_type' => 'digital',
            'price' => 75000,
            'is_active' => true,
        ]);

        $response = $this->get("/checkout/{$product->slug}");
        $response->assertStatus(200);
        $response->assertSee('Checkout Produk Digital');
        $response->assertSee('E-Book Laravel Master');
        $response->assertSee('75.000');
    }
}
