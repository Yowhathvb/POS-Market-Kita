<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\TransactionApiController;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
        $_ENV['API_KEY'] = $this->apiKey;
    }

    private function createSeller(): User
    {
        return User::create([
            'id_users' => 'POS-' . strtoupper(Str::random(8)),
            'nama' => 'Penjual Concurrency',
            'username' => 'seller_conc_' . Str::random(4),
            'email' => 'seller_conc_' . Str::random(4) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'key' => Hash::make('conc_key_123456'),
        ]);
    }

    public function test_invoice_numbers_follow_n_plus_one_sequential_pattern(): void
    {
        $seller = $this->createSeller();

        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Produk N+1 Sequence',
            'slug' => 'produk-n-plus-one',
            'product_type' => 'digital',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Generate 3 sequential invoice numbers
        $inv1 = Transaction::generateNextInvoiceNumber();
        Transaction::create([
            'invoice_number' => $inv1,
            'seller_user_id' => $seller->id,
            'buyer_email' => 'buyer1@example.com',
            'total_amount' => 50000,
            'status' => 'unpaid',
        ]);

        $inv2 = Transaction::generateNextInvoiceNumber();
        Transaction::create([
            'invoice_number' => $inv2,
            'seller_user_id' => $seller->id,
            'buyer_email' => 'buyer2@example.com',
            'total_amount' => 50000,
            'status' => 'unpaid',
        ]);

        $inv3 = Transaction::generateNextInvoiceNumber();

        $datePrefix = 'INV-' . date('Ymd') . '-';
        $this->assertStringStartsWith($datePrefix . '0001-', $inv1);
        $this->assertStringStartsWith($datePrefix . '0002-', $inv2);
        $this->assertStringStartsWith($datePrefix . '0003-', $inv3);
    }

    public function test_concurrent_payment_settlement_is_idempotent_and_prevents_double_crediting(): void
    {
        $seller = $this->createSeller();
        $sellerWallet = $seller->getOrCreateWallet();

        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Concurrency Test',
            'slug' => 'ebook-concurrency-test',
            'product_type' => 'digital',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $trx = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => Transaction::generateNextInvoiceNumber(),
            'seller_user_id' => $seller->id,
            'buyer_email' => 'buyer@example.com',
            'total_amount' => 100000,
            'status' => 'unpaid',
            'value' => ['customer_name' => 'Pembeli Satu'],
        ]);

        TransactionItem::create([
            'uuid' => (string) Str::uuid(),
            'transaction_id' => $trx->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'value' => ['product_name' => $product->name],
        ]);

        Payment::create([
            'uuid' => (string) Str::uuid(),
            'transaction_id' => $trx->id,
            'payment_gateway' => 'midtrans',
            'payment_method' => 'qris',
            'amount' => 100000,
            'status' => 'pending',
        ]);

        // First payment settlement
        $controller = new class extends TransactionApiController {
            public function testMarkPaid(Transaction $trx): void {
                $this->markTransactionAsPaid($trx, ['test' => true]);
            }
        };

        $controller->testMarkPaid($trx);

        $product->refresh();
        $sellerWallet->refresh();
        $trx->refresh();

        $this->assertEquals(4, $product->stock, 'Stok harus berkurang 1 dari 5 menjadi 4');
        $this->assertEquals(100000, (float) $sellerWallet->balance, 'Saldo wallet harus bertambah 100.000');
        $this->assertEquals('paid', $trx->status);

        // Concurrent/second payment settlement (simulasi callback bersamaan)
        $controller->testMarkPaid($trx);

        $product->refresh();
        $sellerWallet->refresh();

        // Must remain exactly 4 and 100.000 (no double deduction or crediting!)
        $this->assertEquals(4, $product->stock, 'Stok tidak boleh berkurang ganda');
        $this->assertEquals(100000, (float) $sellerWallet->balance, 'Saldo wallet tidak boleh bertambah ganda');
    }

    public function test_rapid_double_submit_checkout_from_same_user_is_prevented_by_atomic_lock(): void
    {
        $seller = $this->createSeller();

        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Atomic Lock',
            'slug' => 'ebook-atomic-lock',
            'product_type' => 'digital',
            'price' => 75000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $customerEmail = 'rapid.buyer@example.com';
        $concurrencyKey = 'checkout_lock_' . md5(strtolower(trim($customerEmail)) . '_' . $product->id);

        // Acquire lock intentionally to simulate concurrent active checkout
        $lock = Cache::lock($concurrencyKey, 10);
        $this->assertTrue($lock->get());

        // Attempt second concurrent checkout
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/v1/transactions/checkout', [
            'product_id' => $product->id,
            'buyer_name' => 'Rapid Buyer',
            'buyer_email' => $customerEmail,
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
                'code' => 429,
                'error' => 'CONCURRENT_CHECKOUT_PREVENTED',
            ]);

        $lock->release();
    }

    public function test_checkout_fails_when_product_stock_is_exhausted_by_concurrent_checkouts(): void
    {
        $seller = $this->createSeller();

        // Product with 0 stock (exhausted)
        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Limited Stock Zero',
            'slug' => 'ebook-limited-zero',
            'product_type' => 'digital',
            'price' => 50000,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/v1/transactions/checkout', [
            'product_id' => $product->id,
            'buyer_name' => 'Buyer Two',
            'buyer_email' => 'buyer2@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'code' => 400,
                'error' => 'PRODUCT_OUT_OF_STOCK',
            ]);
    }
}
