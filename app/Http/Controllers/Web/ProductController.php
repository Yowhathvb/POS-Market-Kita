<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StoreWebsite;
use App\Services\AuthHelper;
use App\Services\ImageKitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of user products.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $user->role === 'admin' ? Product::query() : $user->products();

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Product type filter
        if ($type = $request->input('type')) {
            if (in_array($type, ['digital', 'physical'])) {
                $query->where('product_type', $type);
            }
        }

        // Metrics for summary cards
        $baseQuery = $user->role === 'admin' ? Product::query() : $user->products();
        $totalProducts = (clone $baseQuery)->count();
        $digitalCount = (clone $baseQuery)->where('product_type', 'digital')->count();
        $physicalCount = (clone $baseQuery)->where('product_type', 'physical')->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();

        $products = $query->with('storeWebsite')->latest()->paginate(10)->withQueryString();

        return view('products.index', compact(
            'products',
            'totalProducts',
            'digitalCount',
            'physicalCount',
            'activeCount'
        ));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'product_type' => ['nullable', 'in:digital,physical'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'file_url' => ['nullable', 'string', 'max:1000'],
            'gambar_url' => ['nullable', 'string', 'max:1000'],
            'gambar_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'is_active' => ['nullable'],
        ]);

        $productType = $request->input('product_type', 'digital');

        // Handle Image upload: ImageKit.io with local storage fallback
        $gambarPath = $request->input('gambar_url');
        if ($request->hasFile('gambar_file')) {
            $imageKitUrl = ImageKitService::upload($request->file('gambar_file'));
            if ($imageKitUrl) {
                $gambarPath = $imageKitUrl;
            } else {
                $path = $request->file('gambar_file')->store('products', 'public');
                $gambarPath = Storage::url($path);
            }
        }

        // Pastikan user memiliki key login aktif
        $user = Auth::user();
        if (empty($user->key)) {
            $keyData = AuthHelper::generateLoginKey($user->username);
            $user->key = $keyData['hashed_key'];
            $user->save();
        }

        // Prepare JSON value column (file_url, gambar, deskripsi, user_key) as per ERD
        $valueData = [
            'deskripsi' => $request->input('deskripsi', ''),
            'gambar' => $gambarPath ?: null,
            'file_url' => $request->input('file_url'),
            'user_key' => $user->key,
        ];

        Product::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . strtolower(Str::random(6)),
            'product_type' => $productType,
            'price' => $validated['price'],
            'stock' => $request->filled('stock') ? (int) $request->input('stock') : null,
            'is_active' => $request->boolean('is_active', true),
            'value' => $valueData,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk digital baru berhasil disimpan ke dalam katalog!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'product_type' => ['nullable', 'in:digital,physical'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'file_url' => ['nullable', 'string', 'max:1000'],
            'gambar_url' => ['nullable', 'string', 'max:1000'],
            'gambar_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'is_active' => ['nullable'],
        ]);

        $existingValue = $product->value ?? [];
        $gambarPath = $request->input('gambar_url') ?: ($existingValue['gambar'] ?? null);

        // Upload to ImageKit.io or fallback to local
        if ($request->hasFile('gambar_file')) {
            $imageKitUrl = ImageKitService::upload($request->file('gambar_file'));
            if ($imageKitUrl) {
                $gambarPath = $imageKitUrl;
            } else {
                $path = $request->file('gambar_file')->store('products', 'public');
                $gambarPath = Storage::url($path);
            }
        }

        $user = Auth::user();
        $userKey = $user->key;
        if (empty($userKey)) {
            $keyData = AuthHelper::generateLoginKey($user->username);
            $user->key = $keyData['hashed_key'];
            $user->save();
            $userKey = $user->key;
        }

        $valueData = [
            'deskripsi' => $request->input('deskripsi', $existingValue['deskripsi'] ?? ''),
            'gambar' => $gambarPath,
            'file_url' => $request->input('file_url', $existingValue['file_url'] ?? null),
            'user_key' => $userKey,
        ];

        $product->update([
            'name' => $validated['name'],
            'product_type' => $request->input('product_type', 'digital'),
            'price' => $validated['price'],
            'stock' => $request->filled('stock') ? (int) $request->input('stock') : null,
            'is_active' => $request->boolean('is_active', true),
            'value' => $valueData,
        ]);

        return redirect()->route('products.index')->with('success', 'Data produk digital berhasil diperbarui!');
    }

    /**
     * Remove the specified product from storage (Soft Delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus dari katalog.');
    }

    /**
     * Ensure the authenticated user can access the product.
     */
    protected function authorizeProduct(Product $product): void
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $product->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah produk ini.');
        }
    }
}
