<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageKitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    /**
     * GET /v1/products
     * GET /v1/products/user/{id_users}
     * Mengambil daftar produk digital milik user tertentu (Isolasi Data Produk per Akun).
     * Tidak memerlukan 'key' dari tabel users, namun WAJIB menyertakan 'id_users'
     * (atau header 'X-USER-ID') agar produk satu user tidak terlihat oleh user lain.
     */
    public function index(Request $request, ?string $id_users = null): JsonResponse
    {
        // 1. Ambil identifier user dari route path, query param, header, atau username/user_id
        $rawIdentifier = $id_users
            ?? $request->input('id_users')
            ?? $request->header('X-USER-ID')
            ?? $request->input('username')
            ?? $request->input('user_id');

        // Jika user sedang aktif via web session dan identifier kosong, fallback ke session
        if (empty($rawIdentifier) && Auth::check()) {
            $rawIdentifier = Auth::user()->id_users;
        }

        if (empty($rawIdentifier)) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Identifier user (id_users) wajib disertakan untuk mengambil data produk agar data tidak terlihat oleh pengguna lain. Gunakan parameter "?id_users=POS-XXXX", header "X-USER-ID", atau endpoint "/v1/products/user/{id_users}".',
                'error' => 'MISSING_USER_IDENTIFIER',
            ], 400);
        }

        // 2. Cari pengguna berdasarkan id_users (atau username / user_id)
        $targetUser = User::where('id_users', trim($rawIdentifier))
            ->orWhere('username', strtolower(trim($rawIdentifier)))
            ->first();

        if (!$targetUser && is_numeric($rawIdentifier)) {
            $targetUser = User::find($rawIdentifier);
        }

        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Pengguna dengan identifier '{$rawIdentifier}' tidak ditemukan di database.",
                'error' => 'USER_NOT_FOUND',
            ], 404);
        }

        // 3. Query produk yang HANYA milik user ini (Data terisolasi total)
        $query = Product::where('user_id', $targetUser->id)
            ->with(['user:id,id_users,nama,username', 'storeWebsite:id,store_name,slug']);

        // Filter search nama / slug
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter status aktif
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => "Data produk digital milik user '{$targetUser->nama}' ({$targetUser->id_users}) berhasil diambil.",
            'user' => [
                'id' => $targetUser->id,
                'id_users' => $targetUser->id_users,
                'username' => $targetUser->username,
                'nama' => $targetUser->nama,
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'data' => $products->items(),
        ], 200);
    }

    /**
     * POST /v1/products
     * Input / membuat produk digital baru sesuai skema ERD.
     * Wajib menyertakan key dari tabel users untuk otentikasi pemilik produk.
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:200'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'key' => ['nullable', 'string'],
            'user_key' => ['nullable', 'string'],
            'store_website_id' => ['nullable', 'exists:store_websites,id'],
            'file_url' => ['nullable', 'string', 'max:1000'],
            'gambar' => ['nullable'],
            'gambar_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'file' => ['nullable'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable'],
            'id_users' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
            'value' => ['nullable'],
        ];

        if ($request->hasFile('gambar')) {
            $rules['gambar'] = ['file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'];
        } elseif ($request->filled('gambar')) {
            $rules['gambar'] = ['string', 'max:1000'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.numeric' => 'Harga produk harus berupa angka numerik.',
            'price.min' => 'Harga produk tidak boleh bernilai negatif.',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stock.min' => 'Stok produk tidak boleh bernilai negatif.',
            'store_website_id.exists' => 'ID Store Website yang dipilih tidak valid atau tidak ditemukan.',
            'gambar.image' => 'File gambar yang diunggah harus berupa file gambar valid (jpeg, png, jpg, webp, gif, svg).',
            'gambar_file.image' => 'File gambar yang diunggah harus berupa file gambar valid (jpeg, png, jpg, webp, gif, svg).',
            'image.image' => 'File gambar yang diunggah harus berupa file gambar valid (jpeg, png, jpg, webp, gif, svg).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi input produk gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 1. Ambil key dari request (body, header, atau query)
        $userKey = $request->input('key')
            ?? $request->input('user_key')
            ?? $request->header('X-USER-KEY')
            ?? $request->header('X-KEY')
            ?? $request->query('key');

        // Jika user sedang login via web session dan key tidak dikirim, fallback ke Auth::user()->key
        if (empty($userKey) && Auth::check() && Auth::user()?->key) {
            $userKey = Auth::user()->key;
        }

        if (empty($userKey)) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Key dari tabel user wajib disertakan untuk input produk. Sertakan field "key" pada body atau header "X-USER-KEY".',
                'error' => 'MISSING_USER_KEY',
            ], 401);
        }

        // 2. Cari dan verifikasi user pemilik key dari tabel user
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
            // Verifikasi key milik target user
            if (empty($targetUser->key)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => "User '{$targetUser->username}' belum memiliki key di tabel users. Silakan login terlebih dahulu untuk membangkitkan key.",
                    'error' => 'USER_KEY_NOT_SET',
                ], 401);
            }

            if (!$this->verifyKeyMatch($userKey, $targetUser->key)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Key pengguna tidak valid atau tidak cocok dengan data di tabel users.',
                    'error' => 'INVALID_USER_KEY',
                ], 401);
            }
        } else {
            // Jika identifier tidak disertakan, cari user pemilik key secara langsung
            $targetUser = User::where('key', $userKey)->first();

            // Jika belum ketemu dan input adalah raw key (12 acak + username), verifikasi via verifyKeyMatch
            if (!$targetUser) {
                $candidates = User::whereNotNull('key')->get();
                foreach ($candidates as $candidate) {
                    if ($this->verifyKeyMatch($userKey, $candidate->key)) {
                        $targetUser = $candidate;
                        break;
                    }
                }
            }

            if (!$targetUser) {
                return response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Key dari tabel user tidak ditemukan atau tidak valid.',
                    'error' => 'INVALID_USER_KEY',
                ], 401);
            }
        }

        // Pastikan store_website_id (jika diisi) milik target user
        if ($request->filled('store_website_id')) {
            $store = $targetUser->storeWebsites()->where('id', $request->input('store_website_id'))->first();
            if (!$store) {
                return response()->json([
                    'status' => 'error',
                    'code' => 403,
                    'message' => 'Store website yang dipilih bukan milik user pemilik key.',
                    'error' => 'STORE_NOT_OWNED',
                ], 403);
            }
        }

        // Resolve gambar: Uploaded file ke ImageKit (fallback local storage) atau direct URL string
        $uploadedFile = $request->file('gambar_file')
            ?? $request->file('gambar')
            ?? $request->file('image')
            ?? ($request->file('file') instanceof UploadedFile ? $request->file('file') : null);

        $gambarPath = null;
        if ($uploadedFile instanceof UploadedFile) {
            $imageKitUrl = ImageKitService::upload($uploadedFile);
            if ($imageKitUrl) {
                $gambarPath = $imageKitUrl;
            } else {
                $path = $uploadedFile->store('products', 'public');
                $gambarPath = url(Storage::disk('public')->url($path));
            }
        } elseif (is_string($request->input('gambar'))) {
            $gambarPath = $request->input('gambar');
        } elseif (is_string($request->input('gambar_url'))) {
            $gambarPath = $request->input('gambar_url');
        }

        // Siapkan struktur value JSON (file_url, gambar, deskripsi, user_key)
        $customValue = is_array($request->input('value')) ? $request->input('value') : [];
        $valueData = array_merge([
            'file_url' => $request->input('file_url'),
            'gambar' => $gambarPath,
            'deskripsi' => $request->input('deskripsi', ''),
            'user_key' => $targetUser->key,
        ], $customValue);

        if (!empty($gambarPath)) {
            $valueData['gambar'] = $gambarPath;
        }

        $uuid = (string) Str::uuid();
        $slug = Str::slug($request->input('name')) . '-' . strtolower(Str::random(6));

        $product = Product::create([
            'uuid' => $uuid,
            'user_id' => $targetUser->id,
            'store_website_id' => $request->input('store_website_id'),
            'name' => $request->input('name'),
            'slug' => $slug,
            'product_type' => 'digital',
            'price' => $request->input('price'),
            'stock' => $request->filled('stock') ? (int) $request->input('stock') : null,
            'is_active' => $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : true,
            'value' => $valueData,
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'Produk digital baru berhasil disimpan ke database.',
            'data' => [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'user_id' => $product->user_id,
                'user_key' => $targetUser->key,
                'user' => [
                    'id' => $targetUser->id,
                    'id_users' => $targetUser->id_users,
                    'username' => $targetUser->username,
                    'nama' => $targetUser->nama,
                    'key' => $targetUser->key,
                ],
                'store_website_id' => $product->store_website_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_type' => $product->product_type,
                'price' => $product->price,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'value' => $product->value,
                'created_at' => $product->created_at?->toIso8601String(),
                'updated_at' => $product->updated_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * GET /v1/products/{id}
     * Detail produk digital berdasarkan ID, UUID, atau Slug.
     * Jika id_users disertakan, pencarian dibatasi pada produk milik user tersebut.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $query = Product::whereIdentifier($id)
            ->with(['user:id,id_users,nama,username', 'storeWebsite:id,store_name,slug']);

        // Jika id_users atau header X-USER-ID dikirim, pastikan milik user tersebut
        $rawIdentifier = $request->input('id_users') ?? $request->header('X-USER-ID');
        if ($rawIdentifier) {
            $targetUser = User::where('id_users', trim($rawIdentifier))
                ->orWhere('username', strtolower(trim($rawIdentifier)))
                ->first();
            if ($targetUser) {
                $query->where('user_id', $targetUser->id);
            }
        }

        $product = $query->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Produk digital dengan identifier '{$id}' tidak ditemukan.",
                'error' => 'PRODUCT_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Detail produk digital berhasil diambil.',
            'data' => $product,
        ], 200);
    }

    /**
     * PUT /v1/products/{id}
     * Memperbarui data produk digital.
     * Wajib menyertakan key dari tabel users untuk otentikasi pemilik produk.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::whereIdentifier($id)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Produk digital dengan identifier '{$id}' tidak ditemukan.",
                'error' => 'PRODUCT_NOT_FOUND',
            ], 404);
        }

        // 1. Verifikasi kepemilikan produk dan validitas key user
        [$owner, $authError] = $this->authorizeOwnerKey($request, $product, 'memperbarui');
        if ($authError) {
            return $authError;
        }

        // 2. Validasi field yang ingin diperbarui
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'file_url' => ['nullable', 'string', 'max:1000'],
            'gambar' => ['nullable'],
            'gambar_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'file' => ['nullable'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable'],
            'value' => ['nullable'],
        ];

        if ($request->hasFile('gambar')) {
            $rules['gambar'] = ['file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'];
        } elseif ($request->filled('gambar')) {
            $rules['gambar'] = ['string', 'max:1000'];
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama produk tidak boleh kosong.',
            'price.numeric' => 'Harga produk harus berupa angka numerik.',
            'price.min' => 'Harga produk tidak boleh bernilai negatif.',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stock.min' => 'Stok produk tidak boleh bernilai negatif.',
            'gambar.image' => 'File gambar yang diunggah harus berupa file gambar valid.',
            'gambar_file.image' => 'File gambar yang diunggah harus berupa file gambar valid.',
            'image.image' => 'File gambar yang diunggah harus berupa file gambar valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi pembaruan produk gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingValue = $product->value ?? [];
        if ($request->has('value') && is_array($request->input('value'))) {
            $existingValue = array_merge($existingValue, $request->input('value'));
        }

        // Upload file baru ke ImageKit (jika dikirim)
        $uploadedFile = $request->file('gambar_file')
            ?? $request->file('gambar')
            ?? $request->file('image')
            ?? ($request->file('file') instanceof UploadedFile ? $request->file('file') : null);

        if ($uploadedFile instanceof UploadedFile) {
            $imageKitUrl = ImageKitService::upload($uploadedFile);
            if ($imageKitUrl) {
                $existingValue['gambar'] = $imageKitUrl;
            } else {
                $path = $uploadedFile->store('products', 'public');
                $existingValue['gambar'] = url(Storage::disk('public')->url($path));
            }
        } elseif ($request->has('gambar') && is_string($request->input('gambar'))) {
            $existingValue['gambar'] = $request->input('gambar');
        } elseif ($request->has('gambar_url') && is_string($request->input('gambar_url'))) {
            $existingValue['gambar'] = $request->input('gambar_url');
        }

        if ($request->has('file_url')) $existingValue['file_url'] = $request->input('file_url');
        if ($request->has('deskripsi')) $existingValue['deskripsi'] = $request->input('deskripsi');
        $existingValue['user_key'] = $owner->key;

        $updateData = [
            'value' => $existingValue,
        ];

        if ($request->filled('name')) {
            $updateData['name'] = $request->input('name');
        }
        if ($request->filled('price')) {
            $updateData['price'] = $request->input('price');
        }
        if ($request->has('stock')) {
            $updateData['stock'] = $request->filled('stock') ? (int) $request->input('stock') : null;
        }
        if ($request->has('is_active')) {
            $updateData['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $product->update($updateData);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Data produk digital berhasil diperbarui.',
            'data' => [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'user_id' => $product->user_id,
                'user_key' => $owner->key,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_type' => $product->product_type,
                'price' => $product->price,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'value' => $product->value,
                'created_at' => $product->created_at?->toIso8601String(),
                'updated_at' => $product->fresh()->updated_at?->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * DELETE /v1/products/{id}
     * Menghapus produk digital (Soft Delete sesuai ERD).
     * Wajib menyertakan key dari tabel users untuk otentikasi pemilik produk.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $product = Product::whereIdentifier($id)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => "Produk digital dengan identifier '{$id}' tidak ditemukan.",
                'error' => 'PRODUCT_NOT_FOUND',
            ], 404);
        }

        // 1. Verifikasi kepemilikan produk dan validitas key user
        [$owner, $authError] = $this->authorizeOwnerKey($request, $product, 'menghapus');
        if ($authError) {
            return $authError;
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Produk digital berhasil dihapus (Soft Delete).',
            'data' => [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'user_id' => $product->user_id,
                'user_key' => $owner->key,
                'deleted_at' => $product->fresh()->deleted_at?->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Memverifikasi user key terhadap pemilik produk.
     * Mengembalikan array [$owner, null] jika sukses, atau [null, JsonResponse] jika gagal.
     *
     * @return array{0: ?User, 1: ?JsonResponse}
     */
    private function authorizeOwnerKey(Request $request, Product $product, string $actionVerb = 'mengubah'): array
    {
        $userKey = $request->input('key')
            ?? $request->input('user_key')
            ?? $request->header('X-USER-KEY')
            ?? $request->header('X-KEY')
            ?? $request->query('key');

        if (empty($userKey) && Auth::check() && Auth::user()?->key) {
            $userKey = Auth::user()->key;
        }

        if (empty($userKey)) {
            return [
                null,
                response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => "Key dari tabel user wajib disertakan untuk {$actionVerb} produk. Sertakan field \"key\" pada body atau header \"X-USER-KEY\".",
                    'error' => 'MISSING_USER_KEY',
                ], 401)
            ];
        }

        $owner = User::find($product->user_id);
        if (!$owner || empty($owner->key)) {
            return [
                null,
                response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'User pemilik produk belum memiliki key di tabel users.',
                    'error' => 'USER_KEY_NOT_SET',
                ], 401)
            ];
        }

        if (!$this->verifyKeyMatch($userKey, $owner->key)) {
            // Cek apakah key cocok dengan user lain di tabel users
            $candidates = User::whereNotNull('key')->where('id', '!=', $owner->id)->get();
            $matchesOtherUser = false;
            foreach ($candidates as $candidate) {
                if ($this->verifyKeyMatch($userKey, $candidate->key)) {
                    $matchesOtherUser = true;
                    break;
                }
            }

            if ($matchesOtherUser) {
                return [
                    null,
                    response()->json([
                        'status' => 'error',
                        'code' => 403,
                        'message' => "Akses ditolak. Anda tidak memiliki hak akses untuk {$actionVerb} produk milik pengguna lain.",
                        'error' => 'FORBIDDEN_NOT_OWNER',
                    ], 403)
                ];
            }

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

        return [$owner, null];
    }

    /**
     * POST /v1/products/upload-image
     * POST /v1/upload/image
     * Mengunggah file gambar langsung ke ImageKit.io via API multipart/form-data.
     * Mengembalikan URL CDN publik ImageKit dan metadata gambar.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'file' => ['nullable'],
            'gambar' => ['nullable'],
            'gambar_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg', 'max:10240'],
            'folder' => ['nullable', 'string', 'max:100'],
            'file_name' => ['nullable', 'string', 'max:200'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validasi upload gambar gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image')
            ?? $request->file('file')
            ?? $request->file('gambar')
            ?? $request->file('gambar_file');

        if (!$file instanceof UploadedFile) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'File gambar tidak ditemukan. Kirimkan file gambar (multipart/form-data) dengan key "image", "file", atau "gambar".',
                'error' => 'MISSING_IMAGE_FILE',
            ], 400);
        }

        $folder = $request->input('folder');
        $fileName = $request->input('file_name');

        $result = ImageKitService::uploadDetailed($file, $fileName, $folder);

        if ($result['success'] && !empty($result['url'])) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Gambar berhasil diupload ke ImageKit.',
                'data' => [
                    'url' => $result['url'],
                    'provider' => 'imagekit',
                    'file_id' => $result['file_id'] ?? null,
                    'file_name' => $result['file_name'] ?? $file->getClientOriginalName(),
                    'file_path' => $result['file_path'] ?? null,
                    'thumbnail_url' => $result['thumbnail_url'] ?? null,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ],
            ], 200);
        }

        $errorMessage = $result['error'] ?? 'Upload ke ImageKit gagal atau tidak merespons.';

        \Illuminate\Support\Facades\Log::warning('ImageKit Upload Failed: ' . $errorMessage, [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'error_detail' => $result,
        ]);

        return response()->json([
            'status' => 'error',
            'code' => 502,
            'message' => 'Gagal mengunggah file ke ImageKit: ' . $errorMessage,
            'error' => 'IMAGEKIT_UPLOAD_FAILED',
            'detail' => $errorMessage,
        ], 502);
    }

    /**
     * Memverifikasi kecocokan antara key yang dikirim dengan key di tabel users.
     * Mendukung perbandingan string langsung (hash_equals) dan hash Bcrypt ($2y$).
     */
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
