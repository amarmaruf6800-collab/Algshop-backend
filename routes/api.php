<?php

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\AddressApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/katalog', [ProductApiController::class, 'index']);

// Rute Publik (Tanpa Token)
Route::get('/katalog', [ProductApiController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/produk/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'show']);

// Rute Privat (WAJIB bawa Token)
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint untuk mengecek profil user yang sedang login menggunakan tokennya
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profil-saya', function (Request $request) {
            return response()->json(['success' => true, 'data' => $request->user()]);
        });

        // Endpoint Keranjang Belanja API
        Route::get('/keranjang', [CartApiController::class, 'index']);
        Route::post('/keranjang/{product_id}', [CartApiController::class, 'add']);

        // Endpoint Checkout API
        Route::post('/checkout-keranjang', [CartApiController::class, 'checkout']);
	// API Alamat Pengiriman
	Route::get('/alamat', [AddressApiController::class, 'index']);
	Route::post('/alamat', [AddressApiController::class, 'store']);
	Route::put('/alamat/{id}', [AddressApiController::class, 'update']);
	Route::delete('/alamat/{id}', [AddressApiController::class, 'destroy']);
	Route::put('/alamat/{id}/default', [AddressApiController::class, 'setDefault']);
        // Endpoint Simulasi Pembayaran
        Route::post('/bayar-simulasi', [CartApiController::class, 'paySimulation']);
        // Riwayat & Pesanan
        Route::get('/riwayat-belanja', [OrderApiController::class, 'history']);
        Route::get('/pesanan-masuk', [OrderApiController::class, 'incomingOrders']);
        Route::put('/pesanan/{id}/status', [OrderApiController::class, 'updateStatus']);

        // Ulasan
        Route::post('/produk/{id}/ulasan', [ProductApiController::class, 'storeReview']);

        Route::get('/toko-saya/produk', [App\Http\Controllers\Api\ProductApiController::class, 'myProducts']);
        Route::post('/toko-saya/produk', [App\Http\Controllers\Api\ProductApiController::class, 'storeMyProduct']);
        Route::post('/toko-saya/produk/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'updateMyProduct']); // Menggunakan POST untuk mengakali FormData HTML
        Route::delete('/toko-saya/produk/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'destroyMyProduct']);

        Route::post('/buka-toko', function (Request $request) {
            $request->validate(['name' => 'required|string|max:255']);
            $user = $request->user();

            // Cek apakah pengguna sudah memiliki toko
            $existingShop = Shop::where('user_id', $user->id)->first();
            if ($existingShop) {
                return response()->json(['message' => 'Anda sudah memiliki toko.'], 400);
            }

            // Buat toko baru
            Shop::create([
                'user_id' => $user->id,
                'name' => $request->name
            ]);

            return response()->json(['message' => 'Toko berhasil dibuat!']);
        });
    });
});
