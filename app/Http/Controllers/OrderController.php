<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function checkout($id)
    {
        $product = Product::findOrFail($id);
        
        // VALIDASI KEAMANAN: Cek apakah user punya toko, dan apakah ID tokonya sama dengan toko asal produk ini
        if (Auth::user()->shop && Auth::user()->shop->id == $product->shop_id) {
            abort(403, 'Akses Ditolak: Anda tidak boleh membeli barang dari toko Anda sendiri!');
        }

        return view('checkout', compact('product'));
    }

    public function process(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $quantity = $request->quantity;

        // VALIDASI KEAMANAN (Lapis 2 saat dieksekusi)
        if (Auth::user()->shop && Auth::user()->shop->id == $product->shop_id) {
            abort(403, 'Akses Ditolak: Anda tidak boleh membeli barang dari toko Anda sendiri!');
        }

        if ($product->stock < $quantity) {
            return "Error: Stok tidak cukup! Sisa stok hanya " . $product->stock;
        }

        DB::transaction(function () use ($product, $quantity) {
            $buyer_id = Auth::id(); 
            
            $order = Order::create([
                'user_id' => $buyer_id,
                'shop_id' => $product->shop_id,
                'total_price' => $product->price * $quantity,
                'status' => 'paid'
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price
            ]);

            $product->decrement('stock', $quantity);
        });

        // KOREKSI 1: Mengembalikan fungsi alihkan halaman yang terpotong
        return redirect('/toko/' . $product->shop_id);
    }

    // KOREKSI 2: Menghapus parameter $id di dalam kurung
    public function history()
    {
        $orders = Order::with(['shop', 'items.product'])->where('user_id', Auth::id())->latest()->get();
        return view('riwayat-belanja', compact('orders'));
    }

    // KOREKSI 3: Menghapus parameter $id di dalam kurung
    public function incomingOrders()
    {
        $shop = Auth::user()->shop;
        
        if (!$shop) {
            abort(403, 'Akses Ditolak: Anda belum memiliki toko untuk melihat pesanan masuk.');
        }

        $orders = Order::with(['user', 'items.product'])->where('shop_id', $shop->id)->latest()->get();
        return view('pesanan-masuk', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        // KOREKSI 4: Memanggil data Order sebelum mengecek kepemilikan tokonya
        $order = Order::findOrFail($id);

        if ($order->shop_id !== Auth::user()->shop->id) {
            abort(403, 'Akses Ditolak: Ini bukan pesanan toko Anda.');
        }

        $order->update(['status' => $request->status]);
        return back();
    }

    
}