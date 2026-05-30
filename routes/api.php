<?php

use App\Http\Controllers\API\AdminLaporanController;
use App\Http\Controllers\API\AdminUserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint Public (Tidak butuh token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Protected (Butuh token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Auth Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mendapatkan data user yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json([
            'statusCode' => 200,
            'data'       => $request->user(),
            'message'    => 'Berhasil mengambil data user',
        ]);
    });

    // Endpoint Laporan (Role: User)
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);
    Route::get('/laporan/{id}', [LaporanController::class, 'show']);
    Route::put('/laporan/{id}', [LaporanController::class, 'update']);
    Route::delete('/laporan/{id}', [LaporanController::class, 'destroy']);

    // Endpoint (Role: Admin)
    Route::prefix('admin')->group(function () {
        // Laporan
        Route::get('/laporan', [AdminLaporanController::class, 'index']);
        Route::put('/laporan/{id}/status', [AdminLaporanController::class, 'updateStatus']);

        // Manajemen User (CRUD)
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    });
});
