<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\GameController;
use App\Http\Controllers\LevelingController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\GameController as AdminGameController;
use App\Http\Controllers\Admin\LevelingController as AdminLevelingController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;

// ------------------------
// Public Routes
// ------------------------

/* GAME */
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/slug/{slug}', [GameController::class, 'showBySlug']);
Route::post('/games', [GameController::class, 'store']);
Route::put('/games/{id}', [GameController::class, 'update']);
Route::delete('/games/{id}', [GameController::class, 'destroy']);
Route::post('/games/{id}', [GameController::class, 'update']); // for method spoofing (FormData)

/* LEVELING */
Route::get('levelings', [LevelingController::class, 'index']);
Route::get('/levelings/slug/{slug}', [LevelingController::class, 'showBySlug']);
Route::post('/levelings', [LevelingController::class, 'store']);
Route::put('/levelings/{id}', [LevelingController::class, 'update']);
Route::delete('/levelings/{id}', [LevelingController::class, 'destroy']);
Route::post('/levelings/{id}', [LevelingController::class, 'update']); // for method spoofing

/* BANNER */
Route::get('/banners', [BannerController::class, 'index']);
Route::post('/banners', [BannerController::class, 'store']);
Route::post('/banners/{id}', [BannerController::class, 'update']);
Route::delete('/banners/{id}', [BannerController::class, 'destroy']);

/* PAYMENT */
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

/* ORDER */
Route::post('/order', [OrderController::class, 'store']);
Route::get('/order/{id}', [OrderController::class, 'show']);

/* ADMIN AUTH */
Route::post('/admin/login', [AdminAuthController::class, 'login']);

/* SANCTUM PROTECTED */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* ADMIN PANEL (CRUD) */
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('/games', AdminGameController::class);
    Route::apiResource('/levelings', AdminLevelingController::class);
    Route::apiResource('/banners', AdminBannerController::class);
});

// DIGIFLAZZ
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/transactions/{ref_id}/status', [TransactionController::class, 'check']);
Route::post('/admin/sync-products', function (Request $request) {
    $percent = $request->input('markup_percent', 0);
    $fixed = $request->input('markup_fixed', 0);

    \Artisan::call('sync:digiflazz-products', [
        '--markup_percent' => $percent,
        '--markup_fixed' => $fixed,
    ]);

    return response()->json(['message' => 'Sync berhasil'], 200);
});

// MIDTRANS
Route::post('/payments', [PaymentController::class, 'create']);
Route::post('/midtrans/callback', [PaymentController::class, 'callback']);
Route::get('/transactions/invoice/{invoice}', [PaymentController::class, 'showByInvoice']);
Route::post('/transactions/draft', [TransactionController::class, 'draft']);

// ADMIN
Route::prefix('admin')->group(function () {
    Route::get('/transactions/recent', [StatsController::class, 'recent']);
});

// VALIDATION
Route::post('/validate/{game}', [ValidationController::class, 'check']);

// SEARCH
Route::get('/search', [SearchController::class, 'index']);
Route::get('/search/suggest', [SearchController::class, 'suggest']);

// KIRIMI
Route::post('/test/kirimi', function (\App\Services\KirimiService $wa) {
    return $wa->sendMessage('628884206137', 'Test Kirimi dari Syncpedia');
});