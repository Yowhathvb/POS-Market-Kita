<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use App\Http\Controllers\Api\V1\WalletApiController;
use App\Http\Controllers\Api\V1\WebsiteApiController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\MagicLinkController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\TransactionController as WebTransactionController;
use App\Http\Controllers\Web\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Login & Register Pages + Dashboard)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Products Management (UC-5, UC-6, UC-7)
    Route::get('/data-produk', [ProductController::class, 'index'])->name('products.data');
    Route::resource('products', ProductController::class);

    // Wallet Management (UC-17, UC-18)
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/bank', [WalletController::class, 'updateBank'])->name('wallet.bank');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

    // Transactions Management (UC-14)
    Route::get('/transactions', [WebTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction:invoice_number}', [WebTransactionController::class, 'show'])->name('transactions.show');

    // Admin Panel (Endpoint: /admin - Tidak ditampilkan di menu sidebar)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/regenerate-key', [AdminController::class, 'regenerateKey'])->name('users.regenerate-key');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/products/{product}/toggle', [AdminController::class, 'toggleProduct'])->name('products.toggle');
        Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    });
});

// Public Checkout Routes (QRIS Midtrans)
Route::get('/checkout/invoice/{invoice}', [CheckoutController::class, 'invoice'])->name('checkout.invoice');
Route::get('/checkout/{product:slug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{product:slug}', [CheckoutController::class, 'process'])->name('checkout.process');

// Documentation Route
Route::get('/docs', [WebAuthController::class, 'docs'])->name('docs');

/*
|--------------------------------------------------------------------------
| Direct API Endpoints: /v1/auth/... and /auth/... alias
|--------------------------------------------------------------------------
| Matches user's exact specification (/v1/auth and /auth) protected by API Key.
*/
Route::prefix('v1/auth')->middleware('api.key')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::post('/update-value', [ApiAuthController::class, 'updateValue']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/status-demo/{code}', [ApiAuthController::class, 'statusDemo']);
});

Route::prefix('auth')->middleware('api.key')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [ApiAuthController::class, 'login'])->name('api.auth.login');
    Route::get('/me', [ApiAuthController::class, 'me'])->name('api.auth.me');
    Route::post('/update-value', [ApiAuthController::class, 'updateValue'])->name('api.auth.update-value');
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout');
    Route::get('/status-demo/{code}', [ApiAuthController::class, 'statusDemo'])->name('api.auth.status-demo');
});

/*
|--------------------------------------------------------------------------
| Product Digital API Endpoints: /v1/products
|--------------------------------------------------------------------------
| Protected by API Key middleware.
*/
Route::prefix('v1/products')->middleware('api.key')->group(function () {
    Route::get('/', [ProductApiController::class, 'index']);
    Route::get('/user/{id_users}', [ProductApiController::class, 'index']);
    Route::post('/', [ProductApiController::class, 'store']);
    Route::post('/upload-image', [ProductApiController::class, 'uploadImage']);
    Route::get('/{id}', [ProductApiController::class, 'show']);
    Route::match(['put', 'post'], '/{id}', [ProductApiController::class, 'update']);
    Route::delete('/{id}', [ProductApiController::class, 'destroy']);
});

Route::prefix('v1/upload')->middleware('api.key')->group(function () {
    Route::post('/image', [ProductApiController::class, 'uploadImage']);
});

Route::post('v1/upload-image', [ProductApiController::class, 'uploadImage'])->middleware('api.key');
Route::post('upload-image', [ProductApiController::class, 'uploadImage'])->middleware('api.key');

/*
|--------------------------------------------------------------------------
| Wallet API Endpoints: /v1/wallet
|--------------------------------------------------------------------------
*/
Route::prefix('v1/wallet')->middleware('api.key')->group(function () {
    Route::get('/', [WalletApiController::class, 'index']);
    Route::post('/bank-account', [WalletApiController::class, 'updateBankAccount']);
    Route::post('/withdraw', [WalletApiController::class, 'withdraw']);
});

/*
|--------------------------------------------------------------------------
| Transactions & Midtrans QRIS API Endpoints: /v1/transactions
|--------------------------------------------------------------------------
*/
Route::prefix('v1/transactions')->middleware('api.key')->group(function () {
    Route::get('/', [TransactionApiController::class, 'index']);
    Route::post('/checkout', [TransactionApiController::class, 'checkout']);
    Route::get('/{invoice}/status', [TransactionApiController::class, 'status']);
    Route::get('/{invoice}', [TransactionApiController::class, 'show']);
    Route::post('/{invoice}/simulate-payment', [TransactionApiController::class, 'simulatePayment']);
});

// Midtrans Webhook Notification
Route::post('v1/payments/midtrans-notification', [TransactionApiController::class, 'notification']);

/*
|--------------------------------------------------------------------------
| Magic Link Digital Product Gateway: /{id_users}/produk/{magic_token}
|--------------------------------------------------------------------------
| Memverifikasi login Google pembeli via Firebase sebelum memberikan akses unduh produk.
*/
Route::get('/{id_users}/produk/{magic_token}', [MagicLinkController::class, 'show'])->name('magic.link.show');
Route::post('/{id_users}/produk/{magic_token}/verify-google', [MagicLinkController::class, 'verifyGoogle'])->name('magic.link.verify');
Route::get('/{id_users}/produk/{magic_token}/download', [MagicLinkController::class, 'download'])->name('magic.link.download');

/*
|--------------------------------------------------------------------------
| Website Toko API Endpoints: /v1/website & /website
|--------------------------------------------------------------------------
| Public Storefront for Next.js storefront pages: domain/[namatoko]
*/
Route::prefix('v1/website')->group(function () {
    Route::get('/', [WebsiteApiController::class, 'index']);
    Route::post('/template', [WebsiteApiController::class, 'updateTemplate']);
    Route::get('/{namatoko}', [WebsiteApiController::class, 'show']);
});

Route::prefix('website')->group(function () {
    Route::get('/', [WebsiteApiController::class, 'index']);
    Route::post('/template', [WebsiteApiController::class, 'updateTemplate']);
    Route::get('/{namatoko}', [WebsiteApiController::class, 'show']);
});


