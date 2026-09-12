<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;

Route::get('/', function () {
    return redirect('/toko');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/produk/{id}/ulasan', [ShopController::class, 'storeReview']);
});

// Rute Publik (Bisa diakses tanpa login)
// Rute Utama: Langsung mengarah ke Etalase Produk
Route::get('/', function () {
    return redirect('/katalog');
});

// Rute Publik (Bisa diakses tanpa login)
Route::get('/katalog', [ShopController::class, 'globalCatalog']);
Route::get('/toko', [ShopController::class, 'index']);
Route::get('/toko/{id}', [ShopController::class, 'show']);
Route::get('/produk/{id}', [ShopController::class, 'detailProduct']);

// Rute Privat (WAJIB LOGIN)
Route::middleware('auth')->group(function () {
    // Checkout
    Route::get('/produk/{id}/beli', [OrderController::class, 'checkout']);
    Route::post('/produk/{id}/beli', [OrderController::class, 'process']);

    // Dasbor Dinamis berdasarkan User yang Login
    Route::get('/riwayat-belanja', [OrderController::class, 'history']);
    Route::get('/pesanan-masuk', [OrderController::class, 'incomingOrders']);
    Route::post('/pesanan/{id}/update-status', [OrderController::class, 'updateStatus']);

    // Fitur Buka Toko
    Route::get('/buka-toko', [ShopController::class, 'createShop']);
    Route::post('/buka-toko', [ShopController::class, 'storeShop']);

    // Dasbor Khusus Penjual (Manajemen Toko & Produk)
    Route::get('/dasbor-toko', [ShopController::class, 'myShop']);
    Route::post('/dasbor-toko/produk', [ShopController::class, 'storeMyProduct']);

    // Fitur Edit & Hapus Produk
    Route::get('/dasbor-toko/produk/{id}/edit', [ShopController::class, 'editProduct']);
    Route::put('/dasbor-toko/produk/{id}', [ShopController::class, 'updateProduct']);
    Route::delete('/dasbor-toko/produk/{id}', [ShopController::class, 'destroyProduct']);

    // Fitur Keranjang Belanja
    Route::get('/keranjang', [CartController::class, 'index']);
    Route::post('/keranjang/{product_id}', [CartController::class, 'add']);
    Route::delete('/keranjang/{id}', [CartController::class, 'remove']);
    Route::post('/checkout-keranjang', [CartController::class, 'checkout']);
});
require __DIR__ . '/auth.php';
