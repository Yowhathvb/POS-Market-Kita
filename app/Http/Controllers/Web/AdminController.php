<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StoreWebsite;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AuthHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Tampilkan halaman utama admin panel (Hanya untuk role 'admin').
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'users');

        // Statistik Keseluruhan Sistem
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_sellers' => User::where('role', '!=', 'admin')->count(),
            'total_products' => Product::count(),
            'total_active_products' => Product::where('is_active', true)->count(),
            'total_transactions' => Transaction::count(),
            'total_paid_transactions' => Transaction::where('status', 'paid')->count(),
            'total_revenue' => Transaction::where('status', 'paid')->sum('total_amount'),
            'total_wallet_balance' => Wallet::sum('balance'),
            'total_stores' => StoreWebsite::count(),
        ];

        // 1. Data Tab Users (Management User)
        $usersQuery = User::withCount(['products', 'sellerTransactions'])
            ->with('wallet')
            ->latest();

        if ($searchUser = $request->input('search_user')) {
            $usersQuery->where(function ($q) use ($searchUser) {
                $q->where('nama', 'like', "%{$searchUser}%")
                  ->orWhere('username', 'like', "%{$searchUser}%")
                  ->orWhere('email', 'like', "%{$searchUser}%")
                  ->orWhere('id_users', 'like', "%{$searchUser}%")
                  ->orWhere('id_toko', 'like', "%{$searchUser}%")
                  ->orWhere('nama_toko', 'like', "%{$searchUser}%");
            });
        }

        if ($roleFilter = $request->input('role_filter')) {
            $usersQuery->where('role', $roleFilter);
        }

        $users = $usersQuery->paginate(15, ['*'], 'users_page')->withQueryString();

        // 2. Data Tab Products (Semua Produk)
        $productsQuery = Product::with(['user', 'storeWebsite'])->latest();

        if ($searchProduct = $request->input('search_product')) {
            $productsQuery->where(function ($q) use ($searchProduct) {
                $q->where('name', 'like', "%{$searchProduct}%")
                  ->orWhere('slug', 'like', "%{$searchProduct}%");
            });
        }

        if ($request->has('product_status') && $request->input('product_status') !== '') {
            $productsQuery->where('is_active', filter_var($request->input('product_status'), FILTER_VALIDATE_BOOLEAN));
        }

        $products = $productsQuery->paginate(15, ['*'], 'products_page')->withQueryString();

        // 3. Data Tab Transactions (Semua Transaksi)
        $transactionsQuery = Transaction::with(['seller', 'items.product', 'latestPayment'])->latest();

        if ($searchTrx = $request->input('search_trx')) {
            $transactionsQuery->where(function ($q) use ($searchTrx) {
                $q->where('invoice_number', 'like', "%{$searchTrx}%")
                  ->orWhere('buyer_email', 'like', "%{$searchTrx}%")
                  ->orWhere('value->customer_name', 'like', "%{$searchTrx}%");
            });
        }

        if ($statusFilter = $request->input('trx_status')) {
            $transactionsQuery->where('status', $statusFilter);
        }

        $transactions = $transactionsQuery->paginate(15, ['*'], 'trx_page')->withQueryString();

        // 4. Data Tab Wallets (Semua Wallet & Saldo)
        $wallets = Wallet::with('user')->orderByDesc('balance')->paginate(15, ['*'], 'wallets_page')->withQueryString();

        // 5. Data Tab Stores (Semua Toko Website)
        $stores = StoreWebsite::with('user')->latest()->paginate(15, ['*'], 'stores_page')->withQueryString();

        return view('admin.index', compact(
            'activeTab',
            'stats',
            'users',
            'products',
            'transactions',
            'wallets',
            'stores'
        ));
    }

    /**
     * Update data user oleh Admin.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['user', 'admin'])],
            'nama_toko' => ['nullable', 'string', 'max:150'],
            'id_toko' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'username' => strtolower($validated['username']),
            'role' => $validated['role'],
            'nama_toko' => $validated['nama_toko'] ?: null,
            'id_toko' => $validated['id_toko'] ?: null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('success', "Data pengguna '{$user->nama}' ({$user->id_users}) berhasil diperbarui.");
    }

    /**
     * Regenerate security key user oleh Admin.
     */
    public function regenerateKey(User $user): RedirectResponse
    {
        $keyData = AuthHelper::generateLoginKey($user->username);
        $user->key = $keyData['hashed_key'];
        $user->save();

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('success', "Security key untuk user '{$user->username}' berhasil digenerate ulang.");
    }

    /**
     * Hapus user oleh Admin.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.index', ['tab' => 'users'])
                ->with('error', 'Anda tidak dapat menghapus akun administrator yang sedang Anda gunakan.');
        }

        $nama = $user->nama;
        $user->delete();

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('success', "User '{$nama}' berhasil dihapus dari sistem.");
    }

    /**
     * Toggle status aktif/nonaktif produk oleh Admin.
     */
    public function toggleProduct(Product $product): RedirectResponse
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.index', ['tab' => 'products'])
            ->with('success', "Produk '{$product->name}' berhasil {$statusStr}.");
    }

    /**
     * Hapus produk oleh Admin.
     */
    public function destroyProduct(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.index', ['tab' => 'products'])
            ->with('success', "Produk '{$name}' berhasil dihapus oleh admin.");
    }
}
