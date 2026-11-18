<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use App\Http\Controllers\Api\V1\WalletApiController;
use App\Http\Controllers\Api\V1\WebsiteApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Protected by: api.key middleware (Requires X-API-KEY header / env API_KEY)
*/

Route::prefix('v1/auth')->middleware('api.key')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
    Route::post('/update-value', [AuthController::class, 'updateValue'])->name('api.v1.auth.update-value');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    Route::get('/status-demo/{code}', [AuthController::class, 'statusDemo'])->name('api.v1.auth.status-demo');
});

Route::prefix('auth')->middleware('api.key')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-value', [AuthController::class, 'updateValue']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/status-demo/{code}', [AuthController::class, 'statusDemo']);
});

Route::prefix('v1/products')->middleware('api.key')->group(function () {
    Route::get('/', [ProductApiController::class, 'index'])->name('api.v1.products.index');
    Route::get('/user/{id_users}', [ProductApiController::class, 'index'])->name('api.v1.products.user');
    Route::post('/', [ProductApiController::class, 'store'])->name('api.v1.products.store');
    Route::post('/upload-image', [ProductApiController::class, 'uploadImage'])->name('api.v1.products.upload-image');
    Route::get('/{id}', [ProductApiController::class, 'show'])->name('api.v1.products.show');
    Route::match(['put', 'post'], '/{id}', [ProductApiController::class, 'update'])->name('api.v1.products.update');
    Route::delete('/{id}', [ProductApiController::class, 'destroy'])->name('api.v1.products.destroy');
});

Route::prefix('v1/upload')->middleware('api.key')->group(function () {
    Route::post('/image', [ProductApiController::class, 'uploadImage'])->name('api.v1.upload.image');
});

Route::post('v1/upload-image', [ProductApiController::class, 'uploadImage'])->middleware('api.key');
Route::post('upload-image', [ProductApiController::class, 'uploadImage'])->middleware('api.key');

Route::prefix('v1/wallet')->middleware('api.key')->group(function () {
    Route::get('/', [WalletApiController::class, 'index'])->name('api.v1.wallet.index');
    Route::post('/bank-account', [WalletApiController::class, 'updateBankAccount'])->name('api.v1.wallet.bank');
    Route::post('/withdraw', [WalletApiController::class, 'withdraw'])->name('api.v1.wallet.withdraw');
});

Route::prefix('v1/transactions')->middleware('api.key')->group(function () {
    Route::get('/', [TransactionApiController::class, 'index'])->name('api.v1.transactions.index');
    Route::post('/checkout', [TransactionApiController::class, 'checkout'])->name('api.v1.transactions.checkout');
    Route::get('/{invoice}/status', [TransactionApiController::class, 'status'])->name('api.v1.transactions.status');
    Route::get('/{invoice}', [TransactionApiController::class, 'show'])->name('api.v1.transactions.show');
    Route::post('/{invoice}/simulate-payment', [TransactionApiController::class, 'simulatePayment'])->name('api.v1.transactions.simulate');
});

// Midtrans Webhook Notification Callback (dapat diakses langsung oleh server Midtrans)
Route::post('v1/payments/midtrans-notification', [TransactionApiController::class, 'notification'])->name('api.v1.payments.notification');
Route::post('api/v1/payments/midtrans-notification', [TransactionApiController::class, 'notification']);

// Website Toko API (Storefront Catalog for Next.js domain/namatoko)
Route::prefix('v1/website')->group(function () {
    Route::get('/', [WebsiteApiController::class, 'index'])->name('api.v1.website.index');
    Route::get('/{namatoko}', [WebsiteApiController::class, 'show'])->name('api.v1.website.show');
});

Route::prefix('website')->group(function () {
    Route::get('/', [WebsiteApiController::class, 'index']);
    Route::get('/{namatoko}', [WebsiteApiController::class, 'show']);
});

