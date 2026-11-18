<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StoreWebsite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebsiteApiController extends Controller
{
    /**
     * GET /v1/website
     * GET /website
     * Menampilkan direktori seluruh website toko aktif di platform
     * ATAU menampilkan toko spesifik jika parameter ?nama_toko=... disertakan.
     */
    public function index(Request $request): JsonResponse
    {
        $identifier = $request->input('nama_toko')
            ?? $request->input('namatoko')
            ?? $request->input('store')
            ?? $request->input('slug')
            ?? $request->input('id_toko')
            ?? $request->input('id_users');

        if (!empty($identifier)) {
            return $this->show($request, (string) $identifier);
        }

        // Tampilkan daftar seluruh toko yang memiliki nama_toko / store_website
        $usersWithStore = User::whereNotNull('nama_toko')
            ->where('nama_toko', '!=', '')
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->with(['storeWebsites' => function ($q) {
                $q->where('is_published', true)->latest();
            }])
            ->latest()
            ->paginate(20);

        $baseDomain = rtrim(config('app.frontend_url') ?: config('app.url') ?: env('APP_URL') ?: 'https://pos.cloudsn.id', '/');

        $storesData = collect($usersWithStore->items())->map(function (User $u) use ($baseDomain) {
            $storeWeb = $u->storeWebsites->first();
            return [
                'id_users' => $u->id_users,
                'id_toko' => $u->id_toko,
                'nama_toko' => $u->nama_toko,
                'slug' => Str::slug($u->nama_toko ?: $u->username),
                'template' => $u->template,
                'template_code' => $u->template,
                'owner' => [
                    'nama' => $u->nama,
                    'username' => $u->username,
                ],
                'total_products' => $u->products_count,
                'website_url' => $baseDomain . '/v1/website/' . Str::slug($u->nama_toko ?: $u->username),
                'custom_domain' => $storeWeb?->custom_domain,
            ];
        });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Direktori website toko berhasil diambil.',
            'meta' => [
                'current_page' => $usersWithStore->currentPage(),
                'total' => $usersWithStore->total(),
                'last_page' => $usersWithStore->lastPage(),
            ],
            'data' => $storesData,
        ], 200);
    }

    /**
     * GET /v1/website/{namatoko}
     * GET /website/{namatoko}
     * Mencari penjual berdasarkan nama toko (atau slug / id_toko / id_users)
     * dan menampilkan profil toko, id_users, serta seluruh katalog produk aktifnya.
     */
    public function show(Request $request, ?string $namatoko = null): JsonResponse
    {
        $rawIdentifier = $namatoko
            ?? $request->input('nama_toko')
            ?? $request->input('namatoko')
            ?? $request->input('store')
            ?? $request->input('slug')
            ?? $request->input('id_toko')
            ?? $request->input('id_users');

        if (empty($rawIdentifier)) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Nama toko atau identifier toko wajib disertakan. Contoh pemanggilan: GET /v1/website/toko-berkah atau GET /v1/website?nama_toko=toko-berkah',
                'error' => 'MISSING_STORE_IDENTIFIER',
            ], 400);
        }

        // 1. Cari pemilik toko berdasarkan nama_toko / slug / id_toko / id_users / store_websites
        $targetUser = $this->findUserByStore((string) $rawIdentifier);

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Toko website dengan identifier '{$rawIdentifier}' tidak ditemukan.",
                'error' => 'STORE_NOT_FOUND',
            ], 404);
        }

        // 2. Query seluruh produk aktif milik penjual ini
        $productQuery = Product::where('user_id', $targetUser->id)
            ->where('is_active', true);

        // Filter pencarian nama/slug produk (fitur search di dalam toko Next.js)
        if ($search = $request->input('search')) {
            $productQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter rentang harga
        if ($request->filled('min_price')) {
            $productQuery->where('price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $productQuery->where('price', '<=', (float) $request->input('max_price'));
        }

        // Sorting
        $sort = strtolower((string) $request->input('sort', 'latest'));
        match ($sort) {
            'cheapest', 'murah' => $productQuery->orderBy('price', 'asc'),
            'expensive', 'mahal' => $productQuery->orderBy('price', 'desc'),
            'name_asc' => $productQuery->orderBy('name', 'asc'),
            default => $productQuery->latest(),
        };

        $products = $productQuery->get();

        // 3. Format data produk untuk kebutuhan Frontend Next.js
        $baseDomain = rtrim(config('app.frontend_url') ?: config('app.url') ?: env('APP_URL') ?: 'https://pos.cloudsn.id', '/');

        $formattedProducts = $products->map(function (Product $p) use ($baseDomain) {
            $val = $p->value ?? [];
            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'formatted_price' => 'Rp ' . number_format($p->price, 0, ',', '.'),
                'stock' => $p->stock,
                'is_unlimited_stock' => is_null($p->stock),
                'product_type' => $p->product_type,
                'image' => $val['gambar'] ?? null,
                'deskripsi' => $val['deskripsi'] ?? '',
                'has_digital_file' => !empty($val['file_url']),
                'checkout_url' => $baseDomain . '/checkout/' . $p->slug,
                'created_at' => $p->created_at?->toIso8601String(),
            ];
        });

        // 4. Ambil informasi toko website penjual jika ada
        $storeWebsite = StoreWebsite::where('user_id', $targetUser->id)->latest()->first();

        $storeInfo = [
            'id_users' => $targetUser->id_users,
            'id_toko' => $targetUser->id_toko,
            'nama_toko' => $targetUser->nama_toko ?: $targetUser->nama,
            'slug' => Str::slug($targetUser->nama_toko ?: $targetUser->username),
            'template' => $targetUser->template,
            'template_code' => $targetUser->template,
            'owner' => [
                'nama' => $targetUser->nama,
                'username' => $targetUser->username,
                'email' => $targetUser->email,
            ],
            'total_products' => $products->count(),
            'website_settings' => $storeWebsite ? [
                'id' => $storeWebsite->id,
                'store_name' => $storeWebsite->store_name,
                'slug' => $storeWebsite->slug,
                'template_id' => $storeWebsite->template_id,
                'custom_domain' => $storeWebsite->custom_domain,
                'is_published' => $storeWebsite->is_published,
                'settings' => $storeWebsite->value,
            ] : null,
        ];

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Data website toko '{$storeInfo['nama_toko']}' berhasil ditemukan.",
            'data' => [
                'store' => $storeInfo,
                'products' => $formattedProducts,
            ],
        ], 200);
    }

    /**
     * POST /v1/website/template
     * POST /website/template
     * Input atau ubah kode template toko penjual secara manual via API.
     * Payload:
     * - template (atau template_code): string kode template (contoh: 'TPL-01', 'modern', 'minimalist')
     * - id_users / username / key: identifier toko atau user
     */
    public function updateTemplate(Request $request): JsonResponse
    {
        $templateCode = $request->input('template')
            ?? $request->input('template_code')
            ?? $request->input('code');

        if (empty($templateCode) || !is_string($templateCode)) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Parameter template atau template_code wajib diisi berupa kode string template (contoh: "TPL-01", "modern", "minimalist").',
                'error' => 'MISSING_TEMPLATE_CODE',
            ], 422);
        }

        // Cari user pemilik toko
        $identifier = $request->header('X-USER-ID')
            ?? $request->header('X-USER-KEY')
            ?? $request->input('id_users')
            ?? $request->input('username')
            ?? $request->input('key')
            ?? $request->input('id_toko')
            ?? $request->input('nama_toko')
            ?? (auth()->check() ? auth()->user()->id_users : null);

        if (empty($identifier)) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Identifier user/toko wajib disertakan via parameter (id_users, username, key, id_toko) atau header X-USER-ID.',
                'error' => 'MISSING_USER_IDENTIFIER',
            ], 400);
        }

        $targetUser = $this->findUserByStore((string) $identifier);

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Pengguna atau toko dengan identifier '{$identifier}' tidak ditemukan.",
                'error' => 'USER_NOT_FOUND',
            ], 404);
        }

        $cleanCode = trim($templateCode);
        $targetUser->setTemplate($cleanCode);

        // Jika user memiliki StoreWebsite record, sinkronkan juga template_id nya
        $storeWeb = StoreWebsite::where('user_id', $targetUser->id)->first();
        if ($storeWeb) {
            $storeWeb->template_id = $cleanCode;
            $storeWeb->save();
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Template toko untuk '{$targetUser->nama_toko}' berhasil diperbarui menjadi '{$cleanCode}'.",
            'data' => [
                'id_users' => $targetUser->id_users,
                'id_toko' => $targetUser->id_toko,
                'nama_toko' => $targetUser->nama_toko ?: $targetUser->nama,
                'template' => $cleanCode,
                'template_code' => $cleanCode,
                'value' => $targetUser->value,
            ],
        ], 200);
    }

    /**
     * Mencari pengguna (User) pemilik toko secara fleksibel:
     * - Berdasarkan id_toko (POSM-XXXXX)
     * - Berdasarkan id_users (POS-XXXXX)
     * - Berdasarkan StoreWebsite (slug / store_name)
     * - Berdasarkan User.nama_toko (exact, slugified, compact)
     * - Berdasarkan User.username
     */
    private function findUserByStore(string $rawIdentifier): ?User
    {
        $identifier = trim($rawIdentifier);
        $clean = strtolower($identifier);
        $slugified = Str::slug($clean);
        $compact = str_replace(['-', '_', ' '], '', $clean);

        // 1. Cari berdasarkan id_toko (format POSM-XXXXX)
        if (str_starts_with(strtoupper($identifier), 'POSM-')) {
            $user = User::where('id_toko', strtoupper($identifier))->first();
            if ($user) return $user;
        }

        // 2. Cari berdasarkan id_users (format POS-XXXXX)
        if (str_starts_with(strtoupper($identifier), 'POS-')) {
            $user = User::where('id_users', strtoupper($identifier))->first();
            if ($user) return $user;
        }

        // 3. Cari dari StoreWebsite (slug atau store_name)
        $storeWeb = StoreWebsite::where('slug', $clean)
            ->orWhere('slug', $slugified)
            ->orWhereRaw('LOWER(store_name) = ?', [$clean])
            ->first();
        if ($storeWeb && $storeWeb->user) {
            return $storeWeb->user;
        }

        // 4. Cari dari User.nama_toko (case-insensitive & slug comparison)
        $user = User::whereNotNull('nama_toko')
            ->where(function ($q) use ($clean, $slugified, $compact) {
                $q->whereRaw('LOWER(nama_toko) = ?', [$clean])
                  ->orWhereRaw('LOWER(REPLACE(REPLACE(nama_toko, \' \', \'-\'), \'_\', \'-\')) = ?', [$clean])
                  ->orWhereRaw('LOWER(REPLACE(REPLACE(nama_toko, \' \', \'-\'), \'_\', \'-\')) = ?', [$slugified])
                  ->orWhereRaw('LOWER(REPLACE(REPLACE(REPLACE(nama_toko, \' \', \'\'), \'-\', \'\'), \'_\', \'\')) = ?', [$compact]);
            })
            ->first();

        if ($user) return $user;

        // 4b. Cari dari JSON column 'value' (store_name / storeName) jika ada
        $jsonUser = User::whereNotNull('value')
            ->where(function ($q) use ($clean, $slugified) {
                $q->whereRaw("LOWER(value->>'store_name') = ?", [$clean])
                  ->orWhereRaw("LOWER(value->>'storeName') = ?", [$clean])
                  ->orWhereRaw("LOWER(REPLACE(REPLACE(value->>'store_name', ' ', '-'), '_', '-')) = ?", [$slugified])
                  ->orWhereRaw("LOWER(REPLACE(REPLACE(value->>'storeName', ' ', '-'), '_', '-')) = ?", [$slugified]);
            })
            ->first();

        if ($jsonUser) return $jsonUser;

        // 4c. Cari berdasarkan exact id_users atau key
        $exactUser = User::where('id_users', $identifier)
            ->orWhere('key', $identifier)
            ->first();
        if ($exactUser) return $exactUser;

        // 5. Fallback cari berdasarkan username
        return User::where('username', $clean)->first();
    }
}
