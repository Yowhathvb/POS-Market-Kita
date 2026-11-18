<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use App\Http\Controllers\Api\V1\WalletApiController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\CheckoutController;
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
});

// Public Checkout Routes (QRIS Midtrans)
Route::get('/checkout/invoice/{invoice}', [CheckoutController::class, 'invoice'])->name('checkout.invoice');
Route::get('/checkout/{product:slug}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{product:slug}', [CheckoutController::class, 'process'])->name('checkout.process');

// Documentation Route
Route::get('/docs', [WebAuthController::class, 'docs'])->name('docs');

/*
|--------------------------------------------------------------------------
| Direct API Endpoints: /v1/auth/...
|--------------------------------------------------------------------------
| Matches user's exact specification (/v1/auth) protected by API Key.
*/
Route::prefix('v1/auth')->middleware('api.key')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::post('/update-value', [ApiAuthController::class, 'updateValue']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/status-demo/{code}', [ApiAuthController::class, 'statusDemo']);
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
    Route::get('/{id}', [ProductApiController::class, 'show']);
    Route::put('/{id}', [ProductApiController::class, 'update']);
    Route::delete('/{id}', [ProductApiController::class, 'destroy']);
});

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

