<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MagicLinkGoogleTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected Product $product;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'id_users' => 'POS-SELLER1',
            'id_toko' => 'POSM-SHOP001',
            'nama_toko' => 'Karya Digital Toko',
            'email' => 'seller@example.com',
        ]);

        $this->product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->seller->id,
            'name' => 'E-Book Panduan SaaS Laravel',
            'slug' => 'e-book-panduan-saas-laravel',
            'product_type' => 'digital',
            'price' => 75000,
            'stock' => 50,
            'is_active' => true,
            'value' => [
                'deskripsi' => 'Panduan lengkap membangun aplikasi SaaS.',
                'file_url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/ebook-saas.pdf',
            ],
        ]);

        $this->transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-20261118-0099-XYZ',
            'magic_token' => 'magictesttoken1234567890abcdef',
            'seller_user_id' => $this->seller->id,
            'buyer_email' => 'buyer.google@gmail.com',
            'total_amount' => 75000,
            'status' => 'paid',
            'paid_at' => now(),
            'value' => [
                'customer_name' => 'Budi Pembeli',
            ],
        ]);

        TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
            'value' => [
                'product_name' => $this->product->name,
                'file_url' => 'https://ik.imagekit.io/p0v1tjsucu/pos/ebook-saas.pdf',
            ],
        ]);
    }

    public function test_magic_link_page_renders_with_seller_id_and_masked_email(): void
    {
        $url = "/{$this->seller->id_users}/produk/{$this->transaction->magic_token}";
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Magic Link Gateway Produk Digital');
        $response->assertSee('Karya Digital Toko');
        $response->assertSee('POS-SELLER1');
        $response->assertSee('POSM-SHOP001');
        $response->assertSee('E-Book Panduan SaaS Laravel');
        $response->assertSee('INV-20261118-0099-XYZ');
        // Masked email should show bu...e@gmail.com
        $response->assertSee('bu');
        $response->assertSee('@gmail.com');
    }

    public function test_magic_link_verification_succeeds_when_google_email_matches(): void
    {
        $verifyUrl = "/{$this->seller->id_users}/produk/{$this->transaction->magic_token}/verify-google";

        $response = $this->postJson($verifyUrl, [
            'email' => 'buyer.google@gmail.com',
            'google_uid' => 'google_sub_uid_987654321',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'code' => 200,
        ]);
        $response->assertJsonPath('download_url', route('magic.link.download', [
            'id_users' => $this->seller->id_users,
            'magic_token' => $this->transaction->magic_token,
        ]));

        // Check Google UID was saved
        $this->transaction->refresh();
        $this->assertEquals('google_sub_uid_987654321', $this->transaction->buyer_google_sub);

        // Session is authenticated
        $this->assertTrue(session("magic_auth_{$this->transaction->id}"));
    }

    public function test_magic_link_verification_fails_with_403_when_google_email_does_not_match(): void
    {
        $verifyUrl = "/{$this->seller->id_users}/produk/{$this->transaction->magic_token}/verify-google";

        $response = $this->postJson($verifyUrl, [
            'email' => 'attacker.different@gmail.com',
            'google_uid' => 'attacker_uid_999',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'code' => 403,
        ]);
        $response->assertSeeText('tidak cocok dengan email pembeli saat checkout');
    }

    public function test_download_endpoint_requires_google_verification(): void
    {
        $downloadUrl = "/{$this->seller->id_users}/produk/{$this->transaction->magic_token}/download";

        // Without session verification -> redirected back
        $response = $this->get($downloadUrl);
        $response->assertRedirect("/{$this->seller->id_users}/produk/{$this->transaction->magic_token}");
        $response->assertSessionHas('error');

        // With session verification -> redirected to actual asset file_url
        $verifiedResponse = $this->withSession(["magic_auth_{$this->transaction->id}" => true])
            ->get($downloadUrl);

        $verifiedResponse->assertRedirect('https://ik.imagekit.io/p0v1tjsucu/pos/ebook-saas.pdf');
    }

    public function test_magic_link_shows_unpaid_notice_if_transaction_is_not_paid(): void
    {
        $this->transaction->update(['status' => 'unpaid']);

        $url = "/{$this->seller->id_users}/produk/{$this->transaction->magic_token}";
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSeeText('Menunggu Pembayaran');
        $response->assertSeeText('Selesaikan pembayaran QRIS');
    }
}
