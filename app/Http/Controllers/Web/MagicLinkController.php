<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MagicLinkController extends Controller
{
    /**
     * Tampilkan Halaman Gateway Magic Link Produk Digital
     * Endpoint: GET /{id_users}/produk/{magic_token}
     */
    public function show(string $id_users, string $magic_token): View|RedirectResponse
    {
        $seller = User::where('id_users', $id_users)->first();
        if (!$seller) {
            abort(404, "Penjual dengan ID '{$id_users}' tidak ditemukan.");
        }

        $transaction = Transaction::where('magic_token', $magic_token)
            ->where('seller_user_id', $seller->id)
            ->with(['items.product', 'seller', 'latestPayment'])
            ->first();

        if (!$transaction) {
            abort(404, "Magic link produk tidak valid atau transaksi tidak ditemukan.");
        }

        // Cek apakah transaksi sudah lunas
        $isPaid = $transaction->status === 'paid';

        // Cek status verifikasi Google pada session
        $isVerified = session("magic_auth_{$transaction->id}") === true;

        $firstItem = $transaction->items->first();
        $product = $firstItem?->product;
        $productName = $product?->name ?? ($firstItem?->value['product_name'] ?? 'Produk Digital');
        $productImage = $product?->image ?? ($firstItem?->value['gambar'] ?? null);
        $fileUrl = $this->resolveFileUrl($firstItem, $product);

        // Mask email buyer untuk privasi (contoh: budi***@gmail.com)
        $maskedEmail = $this->maskEmail($transaction->buyer_email);

        $firebaseConfig = [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
            'measurementId' => config('services.firebase.measurement_id'),
        ];

        return view('magic_link.show', [
            'seller' => $seller,
            'transaction' => $transaction,
            'product' => $product,
            'productName' => $productName,
            'productImage' => $productImage,
            'fileUrl' => $fileUrl,
            'isPaid' => $isPaid,
            'isVerified' => $isVerified,
            'maskedEmail' => $maskedEmail,
            'firebaseConfig' => $firebaseConfig,
        ]);
    }

    /**
     * Verifikasi Email Google Pembeli via Firebase
     * Endpoint: POST /{id_users}/produk/{magic_token}/verify-google
     */
    public function verifyGoogle(Request $request, string $id_users, string $magic_token): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'google_uid' => ['nullable', 'string', 'max:255'],
            'id_token' => ['nullable', 'string'],
        ]);

        $seller = User::where('id_users', $id_users)->first();
        if (!$seller) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => "Penjual dengan ID '{$id_users}' tidak ditemukan.",
            ], 404);
        }

        $transaction = Transaction::where('magic_token', $magic_token)
            ->where('seller_user_id', $seller->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => "Transaksi tidak ditemukan untuk tautan ini.",
            ], 404);
        }

        if ($transaction->status !== 'paid') {
            return response()->json([
                'success' => false,
                'code' => 400,
                'message' => "Transaksi ini belum lunas. Pembayaran QRIS harus diselesaikan terlebih dahulu.",
            ], 400);
        }

        $googleEmail = strtolower(trim($request->input('email')));
        $checkoutEmail = strtolower(trim($transaction->buyer_email));

        // Validasi kesamaan email Google dengan email saat checkout
        if ($googleEmail !== $checkoutEmail) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => "Akses Ditolak: Email Google yang Anda gunakan ({$googleEmail}) tidak cocok dengan email pembeli saat checkout ({$checkoutEmail}). Silakan login menggunakan akun Google yang sesuai.",
                'details' => [
                    'provided_email' => $googleEmail,
                    'required_email' => $this->maskEmail($checkoutEmail),
                ],
            ], 403);
        }

        // Email cocok! Simpan Google UID jika belum ada
        if ($request->filled('google_uid') && empty($transaction->buyer_google_sub)) {
            $transaction->update([
                'buyer_google_sub' => $request->input('google_uid'),
            ]);
        }

        // Simpan tanda verifikasi ke session
        session(["magic_auth_{$transaction->id}" => true]);
        session(["magic_auth_email_{$transaction->id}" => $googleEmail]);

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => "Verifikasi Akun Google berhasil! Email cocok ({$googleEmail}). Mengalihkan ke tautan produk digital...",
            'redirect_url' => route('magic.link.download', [
                'id_users' => $id_users,
                'magic_token' => $magic_token,
            ]),
            'download_url' => route('magic.link.download', [
                'id_users' => $id_users,
                'magic_token' => $magic_token,
            ]),
        ], 200);
    }

    /**
     * Download File Produk Digital yang Sudah Terverifikasi
     * Endpoint: GET /{id_users}/produk/{magic_token}/download
     */
    public function download(string $id_users, string $magic_token): RedirectResponse|View
    {
        $seller = User::where('id_users', $id_users)->firstOrFail();
        $transaction = Transaction::where('magic_token', $magic_token)
            ->where('seller_user_id', $seller->id)
            ->with(['items.product'])
            ->firstOrFail();

        // Pastikan status transaksi paid
        if ($transaction->status !== 'paid') {
            return redirect()->route('magic.link.show', [$id_users, $magic_token])
                ->with('error', 'Transaksi ini belum berstatus lunas.');
        }

        // Pastikan sudah terverifikasi lewat Google di session
        if (!session("magic_auth_{$transaction->id}")) {
            return redirect()->route('magic.link.show', [$id_users, $magic_token])
                ->with('error', 'Silakan login dengan akun Google terdaftar terlebih dahulu untuk mengunduh produk ini.');
        }

        $item = $transaction->items->first();
        $fileUrl = $this->resolveFileUrl($item, $item?->product);

        if (empty($fileUrl)) {
            return redirect()->route('magic.link.show', [$id_users, $magic_token])
                ->with('error', 'Tautan file produk belum diunggah oleh penjual. Silakan hubungi penjual.');
        }

        // Simpan ke item transaksi jika sebelumnya kosong agar konsisten
        if ($item && empty($item->value['file_url'])) {
            $val = $item->value ?? [];
            $val['file_url'] = $fileUrl;
            $item->value = $val;
            $item->save();
        }

        // Teruskan (redirect) ke tautan produk digital asli
        return redirect()->away($fileUrl);
    }

    /**
     * Resolusi URL file unduhan produk digital dari berbagai alternatif key dan storage.
     */
    protected function resolveFileUrl($item, $product): ?string
    {
        $candidates = [
            $item?->value['file_url'] ?? null,
            $item?->value['download_url'] ?? null,
            $item?->value['file'] ?? null,
            $item?->value['url'] ?? null,
            $product?->value['file_url'] ?? null,
            $product?->value['download_url'] ?? null,
            $product?->value['file'] ?? null,
            $product?->value['url'] ?? null,
        ];

        foreach ($candidates as $cand) {
            if (!empty($cand) && is_string($cand)) {
                $trimmed = trim($cand);
                if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
                    return $trimmed;
                }
                if (str_starts_with($trimmed, 'storage/') || str_starts_with($trimmed, '/storage/')) {
                    return url($trimmed);
                }
            }
        }

        return null;
    }

    /**
     * Helper untuk sensor/mask email agar privasi terjaga
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1) . '***';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 3)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }
}
