<?php

namespace Tests\Feature;

use App\Mail\CheckoutInvoiceMail;
use App\Mail\DigitalProductDeliveryMail;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionMidtransApiTest extends TestCase
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

    public function test_checkout_requires_api_key(): void
    {
        $response = $this->postJson('/v1/transactions/checkout', [
            'product_id' => 1,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
        ]);

        $response->assertStatus(401);
    }

    public function test_checkout_creates_invoice_and_returns_qris_data(): void
    {
        Mail::fake();

        $seller = User::factory()->create(['id_users' => 'POS-SELLER10']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Master API 2026',
            'slug' => 'ebook-master-api-2026',
            'product_type' => 'digital',
            'price' => 125000,
            'stock' => 15,
            'is_active' => true,
            'value' => [
                'file_url' => 'https://example.com/ebook-api.pdf',
                'gambar' => 'https://ik.imagekit.io/p0v1tjsucu/pos/cover.jpg',
            ],
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/transactions/checkout', [
                'product_id' => $product->id,
                'customer_name' => 'Ahmad Pembeli',
                'customer_email' => 'ahmad@example.com',
                'customer_phone' => '081234567890',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.total_amount', 125000);
        $response->assertJsonPath('data.payment.gateway', 'midtrans');
        $response->assertJsonPath('data.payment.method', 'qris');
        $this->assertNotEmpty($response->json('data.payment.payment_url'));
        $this->assertNotEmpty($response->json('data.payment.qr_string'));
        $this->assertNotEmpty($response->json('data.payment.qr_code_url'));

        $invoice = $response->json('data.invoice_number');
        $this->assertDatabaseHas('transactions', [
            'invoice_number' => $invoice,
            'seller_user_id' => $seller->id,
            'buyer_email' => 'ahmad@example.com',
            'status' => 'unpaid',
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => '125000.00',
        ]);

        // Verifikasi stok produk langsung berkurang 1 saat checkout (15 -> 14)
        $product->refresh();
        $this->assertEquals(14, $product->stock, 'Stok produk harus langsung berkurang 1 saat checkout.');

        // Verifikasi email tagihan checkout dikirimkan otomatis ke pembeli
        Mail::assertSent(CheckoutInvoiceMail::class, function ($mail) {
            return $mail->hasTo('ahmad@example.com');
        });
    }

    public function test_checkout_fails_if_product_out_of_stock(): void
    {
        $seller = User::factory()->create(['id_users' => 'POS-SELLER11']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Produk Habis',
            'slug' => 'produk-habis',
            'product_type' => 'digital',
            'price' => 50000,
            'stock' => 0, // Habis
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/transactions/checkout', [
                'product_id' => $product->id,
                'customer_name' => 'Calon Pembeli',
                'customer_email' => 'calon@example.com',
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'PRODUCT_OUT_OF_STOCK');
    }

    public function test_check_transaction_status(): void
    {
        $seller = User::factory()->create(['id_users' => 'POS-SELLER12']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'E-Book Status Test',
            'slug' => 'ebook-status-test',
            'product_type' => 'digital',
            'price' => 75000,
            'is_active' => true,
            'value' => ['file_url' => 'https://example.com/asset.zip'],
        ]);

        $checkoutResponse = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/transactions/checkout', [
                'product_id' => $product->id,
                'customer_name' => 'Rina',
                'customer_email' => 'rina@example.com',
            ]);

        $invoice = $checkoutResponse->json('data.invoice_number');

        // Status check dengan API Key
        $statusResponse = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson("/v1/transactions/{$invoice}/status");

        $statusResponse->assertStatus(200);
        $statusResponse->assertJsonPath('status', 'success');
        $statusResponse->assertJsonPath('data.invoice_number', $invoice);
        $statusResponse->assertJsonPath('data.status', 'unpaid');
        $statusResponse->assertJsonPath('data.is_paid', false);

        // Status check publik TANPA API Key (harus tetap sukses untuk frontend polling)
        $publicStatusResponse = $this->getJson("/v1/transactions/{$invoice}/status");
        $publicStatusResponse->assertStatus(200);
        $publicStatusResponse->assertJsonPath('status', 'success');
        $publicStatusResponse->assertJsonPath('data.invoice_number', $invoice);
        $publicStatusResponse->assertJsonPath('data.is_paid', false);
    }

    public function test_successful_midtrans_payment_decrements_stock_credits_wallet_and_sends_email(): void
    {
        Mail::fake();

        $seller = User::factory()->create(['id_users' => 'POS-SELLER13']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Source Code Aplikasi Kasir',
            'slug' => 'source-code-aplikasi-kasir',
            'product_type' => 'digital',
            'price' => 200000,
            'stock' => 10, // Awal 10
            'is_active' => true,
            'value' => ['file_url' => 'https://example.com/pos-pro.zip'],
        ]);

        $checkoutResponse = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/transactions/checkout', [
                'product_id' => $product->id,
                'customer_name' => 'Doni Darmawan',
                'customer_email' => 'doni@example.com',
            ]);

        $invoice = $checkoutResponse->json('data.invoice_number');

        // Simulasi Midtrans Webhook Notification settlement
        $notificationPayload = [
            'order_id' => $invoice,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '200000.00',
            'payment_type' => 'qris',
        ];

        $webhookResponse = $this->postJson('/v1/payments/midtrans-notification', $notificationPayload);
        $webhookResponse->assertStatus(200);

        // 1. Verifikasi status transaksi menjadi paid
        $transaction = Transaction::where('invoice_number', $invoice)->first();
        $this->assertEquals('paid', $transaction->status);
        $this->assertNotNull($transaction->paid_at);

        // 2. Verifikasi stok produk berkurang 1 (10 -> 9)
        $product->refresh();
        $this->assertEquals(9, $product->stock);

        // 3. Verifikasi saldo wallet seller bertambah 200.000
        $sellerWallet = $seller->getOrCreateWallet();
        $this->assertEquals(200000, (float) $sellerWallet->balance);

        // 4. Verifikasi email dikirimkan ke pembeli
        Mail::assertSent(DigitalProductDeliveryMail::class, function ($mail) {
            return $mail->hasTo('doni@example.com');
        });

        // 5. Verifikasi record email_notifications tercatat
        $this->assertDatabaseHas('email_notifications', [
            'recipient_email' => 'doni@example.com',
            'transaction_id' => $transaction->id,
            'status' => 'sent',
        ]);
    }

    public function test_simulation_payment_decrements_stock_credits_wallet_and_sends_email(): void
    {
        Mail::fake();

        $seller = User::factory()->create(['id_users' => 'POS-SELLER14']);
        $product = Product::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Template Notion Pro',
            'slug' => 'template-notion-pro',
            'product_type' => 'digital',
            'price' => 100000,
            'stock' => 5, // Awal 5
            'is_active' => true,
            'value' => ['file_url' => 'https://notion.so/template-pro'],
        ]);

        $checkoutResponse = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/transactions/checkout', [
                'product_id' => $product->id,
                'customer_name' => 'Siti Nurhaliza',
                'customer_email' => 'siti@example.com',
            ]);

        $invoice = $checkoutResponse->json('data.invoice_number');

        // Panggil endpoint simulate-payment
        $simResponse = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson("/v1/transactions/{$invoice}/simulate-payment");

        $simResponse->assertStatus(200);
        $simResponse->assertJsonPath('status', 'success');
        $simResponse->assertJsonPath('data.status', 'paid');

        // Stok berkurang 1 (5 -> 4)
        $product->refresh();
        $this->assertEquals(4, $product->stock);

        // Wallet seller bertambah
        $sellerWallet = $seller->getOrCreateWallet();
        $this->assertEquals(100000, (float) $sellerWallet->balance);

        // Email terkirim
        Mail::assertSent(DigitalProductDeliveryMail::class);
    }
}
