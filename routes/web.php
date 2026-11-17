<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
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
});

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
