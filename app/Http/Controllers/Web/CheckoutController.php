<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\CheckoutInvoiceMail;
use App\Models\EmailNotification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman checkout produk digital untuk pembeli.
     */
    public function show(Product $product): View
    {
        return view('checkout.show', compact('product'));
    }

    /**
     * Proses checkout dan buat tagihan QRIS Midtrans.
     */
    public function process(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_email.required' => 'Alamat email wajib diisi untuk menerima file produk digital.',
            'customer_email.email' => 'Format email tidak valid.',
        ]);

        $customerName = trim($validated['customer_name']);
        $customerEmail = strtolower(trim($validated['customer_email']));

        // 1. Antisipasi klik checkout ganda / bersamaan via Atomic Cache Lock
        $concurrencyKey = 'web_checkout_lock_' . md5($customerEmail . '_' . $product->id);
        $lock = Cache::lock($concurrencyKey, 6);

        if (!$lock->get()) {
            return back()->with('error', 'Permintaan checkout sedang diproses. Mohon tunggu sejenak untuk menghindari duplikasi tagihan.');
        }

        try {
            // 2. Gunakan DB Transaction dengan Pessimistic Locking (lockForUpdate)
            // untuk mengantisipasi klik checkout bersamaan antar pengguna berbeda
            $transaction = DB::transaction(function () use ($product, $validated, $customerName, $customerEmail, $request) {
                // Lock row produk secara eksklusif (Pessimistic Lock)
                $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();

                if (!$lockedProduct || !$lockedProduct->is_active) {
                    throw new \RuntimeException('Produk ini sedang tidak tersedia untuk dibeli.');
                }

                // Cek stok secara atomik dan kurangi stok produk
                if (!is_null($lockedProduct->stock)) {
                    if ($lockedProduct->stock <= 0) {
                        throw new \RuntimeException('Maaf, persediaan stok produk digital ini telah habis.');
                    }
                    $lockedProduct->decrement('stock', 1);
                }

                // N+1 Sequential Auto-Increment Invoice Numbering
                $invoiceNumber = Transaction::generateNextInvoiceNumber();

                // Simpan Transaksi
                $trx = Transaction::create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_number' => $invoiceNumber,
                    'store_website_id' => $lockedProduct->store_website_id,
                    'seller_user_id' => $lockedProduct->user_id,
                    'buyer_email' => $customerEmail,
                    'total_amount' => $lockedProduct->price,
                    'status' => 'unpaid',
                    'value' => [
                        'customer_name' => $customerName,
                        'customer_phone' => $validated['customer_phone'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                        'ip_address' => $request->ip(),
                        'stock_reduced' => true,
                    ],
                ]);

                // Simpan Item Transaksi
                TransactionItem::create([
                    'uuid' => (string) Str::uuid(),
                    'transaction_id' => $trx->id,
                    'product_id' => $lockedProduct->id,
                    'qty' => 1,
                    'unit_price' => $lockedProduct->price,
                    'subtotal' => $lockedProduct->price,
                    'value' => [
                        'product_name' => $lockedProduct->name,
                        'file_url' => $lockedProduct->value['file_url'] ?? null,
                        'gambar' => $lockedProduct->value['gambar'] ?? null,
                    ],
                ]);

                $itemDetails = [[
                    'id' => (string) $lockedProduct->id,
                    'price' => (int) round($lockedProduct->price),
                    'quantity' => 1,
                    'name' => mb_substr($lockedProduct->name, 0, 50),
                ]];

                // Request tagihan QRIS & Payment Link ke Midtrans
                $midtrans = MidtransService::chargeQris(
                    $invoiceNumber,
                    $lockedProduct->price,
                    $customerName,
                    $customerEmail,
                    $itemDetails
                );

                $paymentLinkData = MidtransService::createPaymentLink(
                    $invoiceNumber,
                    $lockedProduct->price,
                    $customerName,
                    $customerEmail,
                    $itemDetails
                );

                $paymentValue = array_merge($midtrans, [
                    'payment_url' => $paymentLinkData['payment_url'] ?? null,
                ]);

                // Catat Payment
                Payment::create([
                    'uuid' => (string) Str::uuid(),
                    'transaction_id' => $trx->id,
                    'payment_gateway' => 'midtrans',
                    'payment_method' => 'qris',
                    'external_ref_id' => $midtrans['transaction_id'] ?? null,
                    'amount' => $lockedProduct->price,
                    'status' => 'pending',
                    'value' => $paymentValue,
                ]);

                return $trx;
            });

            // Kirim email tagihan/invoice otomatis ke pembeli
            try {
                Mail::to($customerEmail)->send(new CheckoutInvoiceMail($transaction));

                EmailNotification::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $transaction->seller_user_id,
                    'transaction_id' => $transaction->id,
                    'recipient_email' => $customerEmail,
                    'subject' => "Tagihan Pembayaran: " . ($product->name ?? 'Produk Digital') . " (Invoice #{$transaction->invoice_number})",
                    'notif_type' => 'invoice',
                    'status' => 'sent',
                    'value' => [
                        'invoice_number' => $transaction->invoice_number,
                        'total_amount' => (float) $transaction->total_amount,
                        'type' => 'checkout_invoice',
                    ],
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $mailEx) {
                Log::warning("Gagal mengirim email invoice checkout ke {$customerEmail}: " . $mailEx->getMessage());
            }

            return redirect()->route('checkout.invoice', $transaction->invoice_number);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kendala saat memproses tagihan QRIS: ' . $e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Tampilkan halaman pembayaran QRIS Midtrans & status invoice.
     */
    public function invoice(string $invoiceNumber): View
    {
        $transaction = Transaction::whereIdentifier($invoiceNumber)
            ->with(['items.product', 'latestPayment'])
            ->firstOrFail();

        $item = $transaction->items->first();
        $payment = $transaction->latestPayment;

        return view('checkout.qris', compact('transaction', 'item', 'payment'));
    }
}
