<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Prefix: /api/v1/auth
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
