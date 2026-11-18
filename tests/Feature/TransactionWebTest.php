<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionWebTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(string $username): User
    {
        return User::create([
            'id_users' => 'POS-' . strtoupper(Str::random(8)),
            'nama' => 'Penjual ' . ucfirst($username),
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }

    private function createTransactionForSeller(User $seller, string $status = 'paid', float $amount = 125000): Transaction
    {
        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Laravel Mastery',
            'slug' => 'ebook-laravel-mastery-' . Str::random(6),
            'product_type' => 'digital',
            'price' => $amount,
            'stock' => 10,
            'is_active' => true,
            'value' => [
                'file_url' => 'https://example.com/ebook.pdf',
            ],
        ]);

        $trx = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'seller_user_id' => $seller->id,
            'buyer_email' => 'buyer@example.com',
            'total_amount' => $amount,
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
            'value' => [
                'customer_name' => 'Budi Pembeli',
                'customer_phone' => '081234567890',
            ],
        ]);

        TransactionItem::create([
            'uuid' => (string) Str::uuid(),
            'transaction_id' => $trx->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => $amount,
            'subtotal' => $amount,
            'value' => [
                'product_name' => $product->name,
                'file_url' => 'https://example.com/ebook.pdf',
            ],
        ]);

        Payment::create([
            'uuid' => (string) Str::uuid(),
            'transaction_id' => $trx->id,
            'payment_gateway' => 'midtrans',
            'payment_method' => 'qris',
            'amount' => $amount,
            'status' => $status === 'paid' ? 'settlement' : 'pending',
            'value' => [
                'qr_code_url' => 'https://example.com/qr.png',
            ],
        ]);

        return $trx;
    }

    public function test_guest_is_redirected_to_login_when_accessing_transactions(): void
    {
        $response = $this->get('/transactions');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_seller_can_view_transactions_index_page(): void
    {
        $seller = $this->createSeller('seller_web_1');

        $response = $this->actingAs($seller)->get('/transactions');

        $response->assertStatus(200)
            ->assertSee('Data Transaksi &amp; Checkout Produk', false)
            ->assertSee('Belum Ada Transaksi Produk');
    }

    public function test_transactions_page_displays_seller_transactions_and_stats(): void
    {
        $seller = $this->createSeller('seller_web_2');
        $trx = $this->createTransactionForSeller($seller, 'paid', 125000);

        $response = $this->actingAs($seller)->get('/transactions');

        $response->assertStatus(200)
            ->assertSee($trx->invoice_number)
            ->assertSee('E-Book Laravel Mastery')
            ->assertSee('Budi Pembeli')
            ->assertSee('Lunas (Paid)', false)
            ->assertSee('125.000');
    }

    public function test_seller_can_view_their_transaction_detail_page(): void
    {
        $seller = $this->createSeller('seller_web_3');
        $trx = $this->createTransactionForSeller($seller, 'paid', 150000);

        $response = $this->actingAs($seller)->get('/transactions/' . $trx->invoice_number);

        $response->assertStatus(200)
            ->assertSee($trx->invoice_number)
            ->assertSee('Detail Transaksi Invoice')
            ->assertSee('150.000')
            ->assertSee('Budi Pembeli');
    }

    public function test_seller_cannot_view_other_sellers_transaction_detail_page(): void
    {
        $sellerA = $this->createSeller('seller_web_a');
        $trxA = $this->createTransactionForSeller($sellerA, 'paid', 100000);

        $sellerB = $this->createSeller('seller_web_b');

        // Seller B attempts to view Seller A's transaction
        $response = $this->actingAs($sellerB)->get('/transactions/' . $trxA->invoice_number);

        $response->assertStatus(403);
    }
}
