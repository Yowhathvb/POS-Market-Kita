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

class TransactionSellerApiTest extends TestCase
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

    private function createSellerWithKey(string $username, string $rawKey): User
    {
        return User::create([
            'id_users' => 'POS-' . strtoupper(Str::random(8)),
            'nama' => 'Penjual ' . ucfirst($username),
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'key' => Hash::make($rawKey),
        ]);
    }

    private function createTransactionForSeller(User $seller, string $status = 'paid', float $amount = 125000): Transaction
    {
        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Laravel Mastery ' . Str::random(4),
            'slug' => 'ebook-laravel-' . Str::random(6),
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
                'customer_name' => 'Pembeli Satu',
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

    public function test_unauthenticated_request_cannot_access_transactions_api(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/v1/transactions');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 401,
                'error' => 'MISSING_USER_KEY',
            ]);
    }

    public function test_invalid_user_key_cannot_access_transactions_api(): void
    {
        $this->createSellerWithKey('seller1', 'validkey123456');

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => 'wrongkey_999999',
        ])->getJson('/v1/transactions');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'code' => 401,
                'error' => 'INVALID_USER_KEY',
            ]);
    }

    public function test_seller_can_list_transactions_for_their_products_using_user_key(): void
    {
        $rawKey = 'validkey123456';
        $seller = $this->createSellerWithKey('seller1', $rawKey);

        $trx1 = $this->createTransactionForSeller($seller, 'paid', 100000);
        $trx2 = $this->createTransactionForSeller($seller, 'unpaid', 50000);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $rawKey,
        ])->getJson('/v1/transactions');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'code' => 200,
            ])
            ->assertJsonPath('data.seller.id', $seller->id)
            ->assertJsonPath('data.summary.total_transactions', 2)
            ->assertJsonPath('data.summary.paid_transactions', 1)
            ->assertJsonPath('data.summary.unpaid_transactions', 1)
            ->assertJsonPath('data.summary.total_revenue', 100000)
            ->assertJsonCount(2, 'data.transactions');
    }

    public function test_seller_can_list_transactions_using_id_users(): void
    {
        $rawKey = 'validkey123456';
        $seller = $this->createSellerWithKey('seller2', $rawKey);
        $this->createTransactionForSeller($seller, 'paid', 75000);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/v1/transactions?id_users=' . $seller->id_users);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'code' => 200,
            ])
            ->assertJsonPath('data.seller.id_users', $seller->id_users)
            ->assertJsonCount(1, 'data.transactions');
    }

    public function test_seller_only_sees_transactions_for_their_own_products_not_other_sellers(): void
    {
        $keyA = 'sellerA_key1234';
        $sellerA = $this->createSellerWithKey('sellerA', $keyA);
        $trxA = $this->createTransactionForSeller($sellerA, 'paid', 200000);

        $keyB = 'sellerB_key5678';
        $sellerB = $this->createSellerWithKey('sellerB', $keyB);
        $trxB = $this->createTransactionForSeller($sellerB, 'paid', 150000);

        // Seller A requests
        $responseA = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $keyA,
        ])->getJson('/v1/transactions');

        $responseA->assertStatus(200);
        $responseA->assertJsonCount(1, 'data.transactions');
        $this->assertEquals($trxA->invoice_number, $responseA->json('data.transactions.0.invoice_number'));

        // Seller B requests
        $responseB = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $keyB,
        ])->getJson('/v1/transactions');

        $responseB->assertStatus(200);
        $responseB->assertJsonCount(1, 'data.transactions');
        $this->assertEquals($trxB->invoice_number, $responseB->json('data.transactions.0.invoice_number'));
    }

    public function test_seller_can_filter_transactions_by_status(): void
    {
        $rawKey = 'filterkey123456';
        $seller = $this->createSellerWithKey('seller_filter', $rawKey);

        $this->createTransactionForSeller($seller, 'paid', 50000);
        $this->createTransactionForSeller($seller, 'unpaid', 50000);

        // Filter paid
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $rawKey,
        ])->getJson('/v1/transactions?status=paid');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.status', 'paid');
    }

    public function test_seller_can_view_transaction_detail_using_key(): void
    {
        $rawKey = 'detailkey123456';
        $seller = $this->createSellerWithKey('seller_detail', $rawKey);
        $trx = $this->createTransactionForSeller($seller, 'paid', 99000);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $rawKey,
        ])->getJson('/v1/transactions/' . $trx->invoice_number);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'code' => 200,
            ])
            ->assertJsonPath('data.transaction.invoice_number', $trx->invoice_number)
            ->assertJsonPath('data.transaction.total_amount', 99000)
            ->assertJsonPath('data.transaction.buyer.email', 'buyer@example.com');
    }

    public function test_seller_cannot_view_other_sellers_transaction_detail(): void
    {
        $keyA = 'sellerA_detailkey';
        $sellerA = $this->createSellerWithKey('sellerA_det', $keyA);
        $trxA = $this->createTransactionForSeller($sellerA, 'paid', 99000);

        $keyB = 'sellerB_detailkey';
        $sellerB = $this->createSellerWithKey('sellerB_det', $keyB);

        // Seller B attempts to view Seller A's transaction
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
            'X-USER-KEY' => $keyB,
        ])->getJson('/v1/transactions/' . $trxA->invoice_number);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'code' => 403,
                'error' => 'FORBIDDEN_TRANSACTION_ACCESS',
            ]);
    }
}
