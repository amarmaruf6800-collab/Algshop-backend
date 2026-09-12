<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // 1. Menampilkan isi keranjang
    public function index()
    {
        $carts = Cart::with('product.shop')->where('user_id', Auth::id())->get();
        return view('keranjang', compact('carts'));
    }

    // 2. Menambah barang ke keranjang
    public function add($product_id)
    {
        $product = Product::findOrFail($product_id);

        // Keamanan: Tolak jika beli barang sendiri
        if (Auth::user()->shop && Auth::user()->shop->id == $product->shop_id) {
            abort(403, 'Anda tidak bisa memasukkan barang sendiri ke keranjang.');
        }

        // Cek apakah barang sudah ada di keranjang?
        $existingCart = Cart::where('user_id', Auth::id())->where('product_id', $product_id)->first();

        if ($existingCart) {
            // Jika ada, tambah jumlahnya
            $existingCart->increment('quantity');
        } else {
            // Jika belum, buat baru
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product_id,
                'quantity' => 1
            ]);
        }

        return redirect('/keranjang');
    }

    // 3. Menghapus dari keranjang
    public function remove($id)
    {
        Cart::where('id', $id)->where('user_id', Auth::id())->delete();
        return back();
    }

    // 4. Proses Checkout Massal (Logika Multi-Vendor)
    public function checkout()
    {
        $carts = Cart::with('product')->where('user_id', Auth::id())->get();

        if ($carts->isEmpty()) {
            return back();
        }

        DB::transaction(function () use ($carts) {
            // A. Kelompokkan barang di keranjang berdasarkan ID Toko (shop_id)
            $cartsByShop = $carts->groupBy('product.shop_id');

            // B. Looping per toko untuk membuat Nota (Order) yang terpisah
            foreach ($cartsByShop as $shop_id => $items) {
                
                // Hitung total harga khusus untuk barang-barang di toko ini
                $totalPrice = $items->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                });

                // Buat 1 Nota untuk toko ini
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'shop_id' => $shop_id,
                    'total_price' => $totalPrice,
                    'status' => 'paid'
                ]);

                // Masukkan rincian barangnya dan potong stok
                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price
                    ]);
                    
                    $item->product->decrement('stock', $item->quantity);
                    $item->delete(); // Bersihkan dari keranjang setelah berhasil di-checkout
                }
            }
        });

        return redirect('/riwayat-belanja');
    }
}
