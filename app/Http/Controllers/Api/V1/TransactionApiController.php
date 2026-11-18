<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\DigitalProductDeliveryMail;
use App\Models\EmailNotification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransactionApiController extends Controller
{
    /**
     * GET /v1/transactions
     * Mengambil daftar transaksi pada produk milik user penjual (isolasi data per akun).
     * Otentikasi: parameter 'key' / header 'X-USER-KEY' dari tabel users, atau parameter 'id_users'.
     */
    public function index(Request $request): JsonResponse
    {
        [$user, $authError] = $this->authenticateUserKey($request);
        if ($authError) {
            return $authError;
        }

        $query = Transaction::where('seller_user_id', $user->id)
            ->with(['items.product', 'latestPayment']);

        // Filter status: paid, unpaid, pending, expired, cancelled, all
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = strtolower(trim($request->input('status')));
            $query->where('status', $status);
        }

        // Filter product_id (ID, UUID, atau slug)
        if ($request->filled('product_id')) {
            $productId = $request->input('product_id');
            $query->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId)
                    ->orWhereHas('product', function ($p) use ($productId) {
                        $p->where('uuid', $productId)->orWhere('slug', $productId);
                    });
            });
        }

        // Filter search: nomor invoice, buyer_email, atau nama pembeli
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('buyer_email', 'like', "%{$search}%")
                    ->orWhere('value->customer_name', 'like', "%{$search}%");
            });
        }

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Ringkasan metrik statistik untuk seluruh produk milik penjual ini
        $statsBase = Transaction::where('seller_user_id', $user->id);
        $totalCount = (clone $statsBase)->count();
        $paidCount = (clone $statsBase)->where('status', 'paid')->count();
        $unpaidCount = (clone $statsBase)->where('status', 'unpaid')->count();
        $totalRevenue = (float) (clone $statsBase)->where('status', 'paid')->sum('total_amount');

        $perPage = min(max((int) $request->input('per_page', $request->input('limit', 15)), 1), 100);
        $paginated = $query->latest('id')->paginate($perPage);

        $items = collect($paginated->items())->map(function (Transaction $trx) {
            return $this->formatTransactionResource($trx);
        });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Daftar transaksi produk milik '{$user->nama}' berhasil diambil.",
            'data' => [
                'seller' => [
                    'id' => $user->id,
                    'id_users' => $user->id_users,
                    'nama' => $user->nama,
                    'username' => $user->username,
                ],
                'summary' => [
                    'total_transactions' => $totalCount,
                    'paid_transactions' => $paidCount,
                    'unpaid_transactions' => $unpaidCount,
                    'total_revenue' => $totalRevenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                ],
                'transactions' => $items,
                'pagination' => [
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'has_more_pages' => $paginated->hasMorePages(),
                ],
            ],
        ], 200);
    }

    /**
     * GET /v1/transactions/{identifier}
     * Mengambil detail transaksi tertentu berdasarkan invoice_number, uuid, atau id.
     * Jika diakses oleh penjual (dengan key/id_users), memastikan verifikasi kepemilikan.
     */
    public function show(Request $request, string $identifier): JsonResponse
    {
        $transaction = Transaction::where('invoice_number', $identifier)
            ->orWhere('uuid', $identifier)
            ->when(is_numeric($identifier), fn($q) => $q->orWhere('id', (int) $identifier))
            ->with(['items.product', 'latestPayment', 'seller', 'emailNotifications'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Transaksi dengan identifier '{$identifier}' tidak ditemukan.",
                'error' => 'TRANSACTION_NOT_FOUND',
            ], 404);
        }

        // Jika request membawa key / id_users / session auth, verifikasi otorisasi penjual
        $hasKey = $request->filled('key') || $request->filled('user_key') || $request->hasHeader('X-USER-KEY') || $request->filled('id_users');
        if ($hasKey) {
            [$seller, $authError] = $this->authenticateUserKey($request);
            if ($authError) {
                return $authError;
            }
            if ($transaction->seller_user_id !== $seller->id) {
                return response()->json([
                    'status' => 'error',
                    'code' => 403,
                    'message' => 'Anda tidak memiliki hak akses melihat transaksi produk milik penjual lain.',
                    'error' => 'FORBIDDEN_TRANSACTION_ACCESS',
                ], 403);
            }
        }

        $formatted = $this->formatTransactionResource($transaction);
        $formatted['email_notifications'] = $transaction->emailNotifications->map(function ($notif) {
            return [
                'recipient_email' => $notif->recipient_email,
                'subject' => $notif->subject,
                'status' => $notif->status,
                'sent_at' => $notif->sent_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Detail transaksi '{$transaction->invoice_number}' berhasil diambil.",
            'data' => [
                'transaction' => $formatted,
            ],
        ], 200);
    }

    /**
     * POST /v1/transactions/checkout
     * Melakukan checkout produk digital:
     * 1. Pembeli memasukkan nama & email
     * 2. Membuat invoice di Laravel dan menagih QRIS ke Midtrans Core API
     * 3. Mengembalikan payload QRIS (qr_string & qr_code_url) untuk di-scan pembeli
     */
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'customer_name' => ['required_without:buyer_name', 'nullable', 'string', 'max:150'],
            'buyer_name' => ['required_without:customer_name', 'nullable', 'string', 'max:150'],
            'customer_email' => ['required_without:buyer_email', 'nullable', 'email', 'max:150'],
            'buyer_email' => ['required_without:customer_email', 'nullable', 'email', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'buyer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'product_id.required' => 'Identifier produk (ID, UUID, atau Slug) wajib disertakan.',
            'customer_name.required_without' => 'Nama lengkap pembeli wajib diisi (customer_name atau buyer_name).',
            'buyer_name.required_without' => 'Nama lengkap pembeli wajib diisi (buyer_name atau customer_name).',
            'customer_email.required_without' => 'Alamat email pembeli wajib diisi untuk pengiriman file produk digital.',
            'buyer_email.required_without' => 'Alamat email pembeli wajib diisi untuk pengiriman file produk digital.',
            'customer_email.email' => 'Format email pembeli tidak valid.',
            'buyer_email.email' => 'Format email pembeli tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi checkout produk gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $productId = $request->input('product_id');
        $customerName = trim($request->input('customer_name') ?? $request->input('buyer_name') ?? '');
        $customerEmail = strtolower(trim($request->input('customer_email') ?? $request->input('buyer_email') ?? ''));
        $customerPhone = $request->input('customer_phone') ?? $request->input('buyer_phone');
        $notes = $request->input('notes');

        // Antisipasi klik checkout bersamaan / double submit via Atomic Cache Lock
        $concurrencyKey = 'checkout_lock_' . md5($customerEmail . '_' . $productId);
        $lock = Cache::lock($concurrencyKey, 6);

        if (!$lock->get()) {
            return response()->json([
                'status' => 'error',
                'code' => 429,
                'message' => 'Permintaan checkout sedang diproses. Mohon tunggu sejenak untuk menghindari duplikasi transaksi.',
                'error' => 'CONCURRENT_CHECKOUT_PREVENTED',
            ], 429);
        }

        try {
            // Gunakan Database Transaction dengan Pessimistic Locking (lockForUpdate)
            // untuk mengantisipasi klik checkout bersamaan antar pengguna yang berbeda
            return DB::transaction(function () use ($productId, $customerName, $customerEmail, $customerPhone, $notes, $request) {
                // Cari produk dengan lock row eksklusif (Pessimistic Lock)
                $product = Product::where('id', $productId)
                    ->orWhere('uuid', $productId)
                    ->orWhere('slug', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 404,
                        'message' => "Produk digital dengan identifier '{$productId}' tidak ditemukan.",
                        'error' => 'PRODUCT_NOT_FOUND',
                    ], 404);
                }

                if (!$product->is_active) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Produk ini sedang tidak aktif atau tidak tersedia untuk dibeli.',
                        'error' => 'PRODUCT_INACTIVE',
                    ], 400);
                }

                // Cek stok produk secara atomik jika terbatas
                if (!is_null($product->stock) && $product->stock <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Persediaan stok produk digital ini telah habis.',
                        'error' => 'PRODUCT_OUT_OF_STOCK',
                    ], 400);
                }

                // N+1 Sequential Auto-Increment Invoice Numbering
                $invoiceNumber = Transaction::generateNextInvoiceNumber();

                // 1. Simpan Transaksi ke database
                $transaction = Transaction::create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_number' => $invoiceNumber,
                    'store_website_id' => $product->store_website_id,
                    'seller_user_id' => $product->user_id,
                    'buyer_email' => $customerEmail,
                    'total_amount' => $product->price,
                    'status' => 'unpaid',
                    'value' => [
                        'customer_name' => $customerName,
                        'customer_phone' => $customerPhone,
                        'notes' => $notes,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                ]);

                // 2. Simpan Item Transaksi
                $item = TransactionItem::create([
                    'uuid' => (string) Str::uuid(),
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => 1,
                    'unit_price' => $product->price,
                    'subtotal' => $product->price,
                    'value' => [
                        'product_name' => $product->name,
                        'product_slug' => $product->slug,
                        'file_url' => $product->value['file_url'] ?? null,
                        'gambar' => $product->value['gambar'] ?? null,
                    ],
                ]);

                // 3. Request QRIS ke Midtrans Core API
                $midtransItem = [
                    'id' => (string) $product->id,
                    'price' => (int) round($product->price),
                    'quantity' => 1,
                    'name' => mb_substr($product->name, 0, 50),
                ];

                $midtrans = MidtransService::chargeQris(
                    $invoiceNumber,
                    $product->price,
                    $customerName,
                    $customerEmail,
                    [$midtransItem]
                );

                // 4. Catat Payment record
                $payment = Payment::create([
                    'uuid' => (string) Str::uuid(),
                    'transaction_id' => $transaction->id,
                    'payment_gateway' => 'midtrans',
                    'payment_method' => 'qris',
                    'external_ref_id' => $midtrans['transaction_id'] ?? null,
                    'amount' => $product->price,
                    'status' => 'pending',
                    'value' => $midtrans,
                ]);

                return response()->json([
                    'status' => 'success',
                    'code' => 201,
                    'message' => 'Invoice checkout berhasil dibuat. Silakan lakukan pembayaran dengan memindai kode QRIS berikut.',
                    'data' => [
                        'invoice_number' => $transaction->invoice_number,
                        'uuid' => $transaction->uuid,
                        'status' => $transaction->status,
                        'total_amount' => (float) $transaction->total_amount,
                        'formatted_amount' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                        'customer' => [
                            'name' => $customerName,
                            'email' => $customerEmail,
                            'phone' => $customerPhone,
                        ],
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'price' => (float) $product->price,
                        ],
                        'payment' => [
                            'gateway' => 'midtrans',
                            'method' => 'qris',
                            'qr_string' => $midtrans['qr_string'] ?? null,
                            'qr_code_url' => $midtrans['qr_code_url'] ?? null,
                            'expiry_time' => $midtrans['expiry_time'] ?? null,
                            'is_simulated' => $midtrans['is_simulated'] ?? false,
                        ],
                        'check_status_url' => url("/v1/transactions/{$transaction->invoice_number}/status"),
                        'instructions' => [
                            '1. Buka aplikasi pembayaran atau mobile banking (GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, BRI, dll).',
                            '2. Pilih menu Bayar / Scan QRIS.',
                            '3. Pindai kode QRIS yang tersedia atau gunakan link gambar qr_code_url.',
                            '4. Pastikan nominal pembayaran sesuai dengan total tagihan, lalu konfirmasi pembayaran.',
                            '5. Setelah pembayaran berhasil, produk digital akan otomatis dikirimkan ke email Anda dan stok berkurang.',
                        ],
                    ],
                ], 201);
            });
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * GET /v1/transactions/{invoice_number}/status
     * Memeriksa status pembayaran invoice.
     * Jika sudah LUNAS (paid), respon menyertakan link unduhan produk digital.
     */
    public function status(string $invoiceNumber): JsonResponse
    {
        $transaction = Transaction::where('invoice_number', $invoiceNumber)
            ->orWhere('uuid', $invoiceNumber)
            ->with(['items.product', 'latestPayment'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Transaksi dengan invoice '{$invoiceNumber}' tidak ditemukan.",
                'error' => 'TRANSACTION_NOT_FOUND',
            ], 404);
        }

        $item = $transaction->items->first();
        $isPaid = $transaction->status === 'paid';
        $downloadUrl = $isPaid 
            ? ($item?->value['file_url'] ?? $item?->product?->value['file_url'] ?? null)
            : null;

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Status invoice '{$transaction->invoice_number}' adalah " . strtoupper($transaction->status) . '.',
            'data' => [
                'invoice_number' => $transaction->invoice_number,
                'status' => $transaction->status,
                'is_paid' => $isPaid,
                'total_amount' => (float) $transaction->total_amount,
                'formatted_amount' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                'customer_email' => $transaction->buyer_email,
                'customer_name' => $transaction->value['customer_name'] ?? null,
                'paid_at' => $transaction->paid_at?->toIso8601String(),
                'product' => [
                    'name' => $item?->product?->name ?? ($item?->value['product_name'] ?? '-'),
                    'download_url' => $downloadUrl,
                ],
                'payment' => [
                    'gateway' => 'midtrans',
                    'method' => 'qris',
                    'status' => $transaction->latestPayment?->status ?? 'pending',
                    'qr_code_url' => $transaction->latestPayment?->value['qr_code_url'] ?? null,
                ],
            ],
        ], 200);
    }

    /**
     * POST /v1/payments/midtrans-notification
     * Webhook HTTP notification dari Midtrans saat pembayaran QRIS selesai.
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (empty($orderId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Field order_id tidak ditemukan pada payload notification.',
            ], 400);
        }

        // Cari transaksi berdasarkan invoice_number
        $transaction = Transaction::where('invoice_number', $orderId)->first();
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => "Transaksi '{$orderId}' tidak ditemukan di database.",
            ], 404);
        }

        // Verifikasi signature jika dikirimkan oleh Midtrans
        if ($signatureKey && $statusCode && $grossAmount) {
            $isValid = MidtransService::verifySignature($orderId, $statusCode, (string) $grossAmount, $signatureKey);
            if (!$isValid) {
                Log::warning("Midtrans Notification signature verification failed for Order ID: {$orderId}");
            }
        }

        // Evaluasi status transaksi dari Midtrans
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            $this->markTransactionAsPaid($transaction, $payload);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $transaction->update(['status' => 'expired']);
            $transaction->latestPayment?->update(['status' => 'failed', 'value' => $payload]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Notifikasi pembayaran untuk invoice {$orderId} berhasil diproses.",
        ], 200);
    }

    /**
     * POST /v1/transactions/{invoice_number}/simulate-payment
     * Endpoint pembantu simulasi pembayaran sukses di lingkungan Sandbox/Testing.
     */
    public function simulatePayment(string $invoiceNumber): JsonResponse
    {
        $transaction = Transaction::where('invoice_number', $invoiceNumber)
            ->orWhere('uuid', $invoiceNumber)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Transaksi '{$invoiceNumber}' tidak ditemukan.",
                'error' => 'TRANSACTION_NOT_FOUND',
            ], 404);
        }

        if ($transaction->status === 'paid') {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Transaksi ini sudah lunas sebelumnya.',
                'data' => [
                    'invoice_number' => $transaction->invoice_number,
                    'status' => 'paid',
                ],
            ], 200);
        }

        $simulatedPayload = [
            'transaction_time' => now()->toDateTimeString(),
            'transaction_status' => 'settlement',
            'transaction_id' => 'sim_' . (string) Str::uuid(),
            'status_message' => 'Midtrans simulation payment settlement success',
            'status_code' => '200',
            'signature_key' => 'simulated_signature',
            'payment_type' => 'qris',
            'order_id' => $transaction->invoice_number,
            'gross_amount' => (string) (int) round($transaction->total_amount),
            'currency' => 'IDR',
        ];

        $this->markTransactionAsPaid($transaction, $simulatedPayload);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Simulasi pembayaran QRIS berhasil! Transaksi dinyatakan LUNAS, stok telah dikurangi 1, saldo seller bertambah, dan file produk dikirim via email.",
            'data' => [
                'invoice_number' => $transaction->invoice_number,
                'status' => 'paid',
                'paid_at' => $transaction->fresh()->paid_at?->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Logika eksekusi ketika transaksi berhasil dibayar:
     * 1. Tandai transaksi paid & simpan waktu pembayaran
     * 2. Tandai payment settlement
     * 3. Kurangi stok produk (-1 jika stok bukan unlimited)
     * 4. Tambahkan saldo ke wallet penjual (seller) & catat mutasi credit
     * 5. Kirim email berisi link unduhan produk digital ke pembeli & catat di tabel email_notifications
     */
    protected function markTransactionAsPaid(Transaction $transaction, array $paymentPayload = []): void
    {
        $now = now();
        $emailData = null;

        // Gunakan DB Transaction dengan Pessimistic Row Lock (lockForUpdate)
        // untuk mengantisipasi concurrent webhook callbacks atau simulasi paralel
        DB::transaction(function () use ($transaction, $paymentPayload, $now, &$emailData) {
            $lockedTrx = Transaction::where('id', $transaction->id)->lockForUpdate()->first();
            if (!$lockedTrx || $lockedTrx->status === 'paid') {
                return; // Idempotent: sudah lunas, hindari dobel potong stok atau dobel kredit saldo
            }

            // 1. Update status transaksi
            $lockedTrx->update([
                'status' => 'paid',
                'paid_at' => $now,
            ]);

            // 2. Update payment
            $payment = $lockedTrx->latestPayment;
            if ($payment) {
                $payment->update([
                    'status' => 'settlement',
                    'paid_at' => $now,
                    'value' => array_merge($payment->value ?? [], $paymentPayload),
                ]);
            }

            // 3. Kurangi stok produk secara atomik dengan lockForUpdate
            foreach ($lockedTrx->items as $item) {
                $lockedProduct = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($lockedProduct && !is_null($lockedProduct->stock) && $lockedProduct->stock > 0) {
                    $lockedProduct->decrement('stock', $item->qty ?: 1);
                }
            }

            // 4. Tambahkan saldo ke wallet seller secara aman (Pessimistic Lock)
            $seller = $lockedTrx->seller;
            if ($seller) {
                $sellerWallet = Wallet::where('user_id', $seller->id)->lockForUpdate()->first();
                if (!$sellerWallet) {
                    $sellerWallet = $seller->getOrCreateWallet();
                }
                $sellerWallet->credit(
                    (float) $lockedTrx->total_amount,
                    "Penjualan produk digital (Invoice #{$lockedTrx->invoice_number})",
                    $lockedTrx->invoice_number
                );
            }

            $firstItem = $lockedTrx->items->first();
            $emailData = [
                'seller_user_id' => $lockedTrx->seller_user_id,
                'transaction_id' => $lockedTrx->id,
                'buyer_email' => $lockedTrx->buyer_email,
                'invoice_number' => $lockedTrx->invoice_number,
                'product_name' => $firstItem?->product?->name ?? ($firstItem?->value['product_name'] ?? 'Produk Digital'),
                'download_url' => $firstItem?->value['file_url'] ?? $firstItem?->product?->value['file_url'] ?? null,
                'gross_amount' => (float) $lockedTrx->total_amount,
            ];
        });

        // 5. Kirim file produk digital via Email ke pembeli di luar lock DB
        if ($emailData) {
            $emailStatus = 'sent';
            try {
                Mail::to($emailData['buyer_email'])->send(new DigitalProductDeliveryMail($transaction));
            } catch (\Throwable $e) {
                Log::error("Gagal mengirim email produk digital ke {$emailData['buyer_email']}: " . $e->getMessage());
                $emailStatus = 'failed';
            }

            // Catat di tabel email_notifications sesuai ERD
            EmailNotification::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $emailData['seller_user_id'],
                'transaction_id' => $emailData['transaction_id'],
                'recipient_email' => $emailData['buyer_email'],
                'subject' => "Pengiriman Produk Digital: {$emailData['product_name']} (Invoice #{$emailData['invoice_number']})",
                'notif_type' => 'invoice',
                'status' => $emailStatus,
                'value' => [
                    'invoice_number' => $emailData['invoice_number'],
                    'product_name' => $emailData['product_name'],
                    'download_url' => $emailData['download_url'],
                    'gross_amount' => $emailData['gross_amount'],
                ],
                'sent_at' => $emailStatus === 'sent' ? $now : null,
            ]);
        }
    }

    /**
     * Memformat objek transaksi menjadi array resource JSON yang konsisten.
     */
    private function formatTransactionResource(Transaction $trx): array
    {
        $isPaid = $trx->status === 'paid';

        return [
            'id' => $trx->id,
            'uuid' => $trx->uuid,
            'invoice_number' => $trx->invoice_number,
            'status' => $trx->status,
            'is_paid' => $isPaid,
            'total_amount' => (float) $trx->total_amount,
            'formatted_amount' => 'Rp ' . number_format($trx->total_amount, 0, ',', '.'),
            'buyer' => [
                'email' => $trx->buyer_email,
                'name' => $trx->value['customer_name'] ?? null,
                'phone' => $trx->value['customer_phone'] ?? null,
                'notes' => $trx->value['notes'] ?? null,
            ],
            'items' => $trx->items->map(function ($item) use ($isPaid) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? ($item->value['product_name'] ?? '-'),
                    'product_slug' => $item->product?->slug ?? ($item->value['product_slug'] ?? null),
                    'qty' => $item->qty,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'download_url' => $isPaid ? ($item->value['file_url'] ?? $item->product?->value['file_url'] ?? null) : null,
                    'gambar' => $item->value['gambar'] ?? $item->product?->value['gambar'] ?? null,
                ];
            }),
            'payment' => [
                'gateway' => $trx->latestPayment?->payment_gateway ?? 'midtrans',
                'method' => $trx->latestPayment?->payment_method ?? 'qris',
                'status' => $trx->latestPayment?->status ?? 'pending',
                'qr_code_url' => $trx->latestPayment?->value['qr_code_url'] ?? null,
                'external_ref_id' => $trx->latestPayment?->external_ref_id,
            ],
            'paid_at' => $trx->paid_at?->toIso8601String(),
            'created_at' => $trx->created_at?->toIso8601String(),
        ];
    }

    /**
     * Otentikasi key user dari request (header X-USER-KEY, body 'key', query 'key', atau parameter 'id_users').
     *
     * @return array{0: ?User, 1: ?JsonResponse}
     */
    private function authenticateUserKey(Request $request): array
    {
        $userKey = $request->input('key')
            ?? $request->input('user_key')
            ?? $request->header('X-USER-KEY')
            ?? $request->header('X-KEY')
            ?? $request->query('key');

        if (empty($userKey) && Auth::check() && Auth::user()?->key) {
            $userKey = Auth::user()->key;
        }

        // Jika tidak ada key, tapi ada id_users yang valid
        if (empty($userKey) && $request->filled('id_users')) {
            $userById = User::where('id_users', trim($request->input('id_users')))->first();
            if ($userById) {
                return [$userById, null];
            }
            return [
                null,
                response()->json([
                    'status' => 'error',
                    'code' => 404,
                    'message' => "Pengguna dengan id_users '{$request->input('id_users')}' tidak ditemukan.",
                    'error' => 'USER_NOT_FOUND',
                ], 404)
            ];
        }

        if (empty($userKey)) {
            return [
                null,
                response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Key dari tabel users (atau parameter id_users) wajib disertakan untuk melihat transaksi produk Anda. Kirimkan via parameter "key", header "X-USER-KEY", atau "id_users".',
                    'error' => 'MISSING_USER_KEY',
                ], 401)
            ];
        }

        // Cari berdasarkan id_users / username / user_id spesifik jika diberikan
        $targetUser = null;
        if ($request->filled('id_users')) {
            $targetUser = User::where('id_users', trim($request->input('id_users')))->first();
        } elseif ($request->filled('username')) {
            $targetUser = User::where('username', strtolower(trim($request->input('username'))))->first();
        } elseif ($request->filled('user_id')) {
            $targetUser = User::find($request->input('user_id'));
        } elseif (Auth::check()) {
            $targetUser = Auth::user();
        }

        if ($targetUser) {
            if (empty($targetUser->key) || !$this->verifyKeyMatch($userKey, $targetUser->key)) {
                return [
                    null,
                    response()->json([
                        'status' => 'error',
                        'code' => 401,
                        'message' => 'Key pengguna tidak valid atau tidak cocok dengan data di tabel users.',
                        'error' => 'INVALID_USER_KEY',
                    ], 401)
                ];
            }
            return [$targetUser, null];
        }

        // Cari di seluruh kandidat user yang memiliki key
        $candidates = User::whereNotNull('key')->get();
        foreach ($candidates as $candidate) {
            if ($this->verifyKeyMatch($userKey, $candidate->key)) {
                return [$candidate, null];
            }
        }

        return [
            null,
            response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Key pengguna tidak ditemukan atau tidak valid di tabel users.',
                'error' => 'INVALID_USER_KEY',
            ], 401)
        ];
    }

    private function verifyKeyMatch(string $inputKey, string $storedKey): bool
    {
        if (hash_equals($storedKey, $inputKey)) {
            return true;
        }

        if (str_starts_with($storedKey, '$2y$') || str_starts_with($storedKey, '$2a$') || str_starts_with($storedKey, '$2b$')) {
            try {
                return Hash::check($inputKey, $storedKey);
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }
}
