<?php

namespace Tests\Feature;

use App\Mail\CheckoutInvoiceMail;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BrevoMailTransportTest extends TestCase
{
    use RefreshDatabase;

    public function test_brevo_transport_sends_email_via_https_api(): void
    {
        config([
            'services.brevo.key' => 'xkeysib-test-api-key-12345',
            'mail.from.address' => 'noreply@posmarket.id',
            'mail.from.name' => 'POS Market',
        ]);

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response([
                'messageId' => '<202611251430.12345@smtp-relay.mailin.fr>',
            ], 201),
        ]);

        $seller = User::factory()->create(['id_users' => 'POS-BREVO1']);
        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Template SaaS Laravel',
            'slug' => 'template-saas-laravel',
            'product_type' => 'digital',
            'price' => 99000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-20261125-0001-BRV',
            'seller_user_id' => $seller->id,
            'buyer_email' => 'pembeli@gmail.com',
            'total_amount' => 99000,
            'status' => 'unpaid',
            'value' => [
                'customer_name' => 'Budi Santoso',
            ],
        ]);

        TransactionItem::create([
            'uuid' => (string) Str::uuid(),
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 99000,
            'subtotal' => 99000,
            'value' => [
                'product_name' => $product->name,
            ],
        ]);

        Mail::mailer('brevo')->to('pembeli@gmail.com')->send(new CheckoutInvoiceMail($transaction));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'xkeysib-test-api-key-12345')
                && $request['sender']['email'] === 'noreply@posmarket.id'
                && $request['to'][0]['email'] === 'pembeli@gmail.com'
                && str_contains($request['subject'], 'INV-20261125-0001-BRV')
                && !empty($request['htmlContent']);
        });
    }

    public function test_brevo_transport_throws_clear_exception_when_api_key_missing(): void
    {
        config(['services.brevo.key' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BREVO_API_KEY belum diisi');

        $seller = User::factory()->create(['id_users' => 'POS-BREVO2']);
        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'invoice_number' => 'INV-20261125-0002-BRV',
            'seller_user_id' => $seller->id,
            'buyer_email' => 'pembeli@gmail.com',
            'total_amount' => 50000,
            'status' => 'unpaid',
            'value' => ['customer_name' => 'Siti'],
        ]);

        Mail::mailer('brevo')->to('pembeli@gmail.com')->send(new CheckoutInvoiceMail($transaction));
    }
}
