<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class CartApiController extends Controller
{
    // Menampilkan isi keranjang
    public function index(Request $request)
    {
        $carts = Cart::with(['product.shop', 'product.images'])
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data keranjang berhasil diambil',
            'data'    => $carts
        ], 200);
    }

    // Menambah barang ke keranjang
    public function add(Request $request, $product_id)
    {
        $product = Product::findOrFail($product_id);
        $user = $request->user();

        // Keamanan: Tolak jika beli barang sendiri
        if ($user->shop && $user->shop->id == $product->shop_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak bisa membeli barang dari toko sendiri.'
            ], 403);
        }

        // Cari atau buat baru
        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product_id
            ],
            [
                'quantity' => 0
            ]
        );

        $cart->increment('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Berhasil ditambahkan ke keranjang',
            'data'    => $cart
        ], 200);
    }

    // Proses Checkout Massal via API
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Validasi alamat yang dipilih
        $validated = $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        // Pastikan alamat benar-benar milik user yang sedang login
        $address = Address::where('id', $validated['address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat pengiriman tidak valid atau bukan milik Anda.'
            ], 403);
        }

        // Ambil isi keranjang user
        $carts = Cart::with(['product.images'])
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.'
            ], 400);
        }

        // Buat nomor invoice induk
        $invoiceNumber = 'INV-' . time() . '-' . $user->id;
        $grandTotal = 0;

        DB::transaction(function () use (
            $carts,
            $user,
            $invoiceNumber,
            $address,
            &$grandTotal
        ) {
            // Pisahkan pesanan berdasarkan toko
            $cartsByShop = $carts->groupBy('product.shop_id');

            foreach ($cartsByShop as $shop_id => $items) {

                // Hitung total per toko
                $totalPrice = $items->sum(function ($item) {
                    return $item->product->final_price * $item->quantity;
                });

                $grandTotal += $totalPrice;

                // Buat order dengan snapshot alamat pengiriman
                $order = Order::create([
                    'user_id' => $user->id,
                    'shop_id' => $shop_id,
                    'total_price' => $totalPrice,
                    'invoice_number' => $invoiceNumber,
                    'status' => 'pending',

                    // Data alamat pengiriman
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'shipping_address' => $address->address,
                    'province' => $address->province,
                    'city' => $address->city,
                    'district' => $address->district,
                    'postal_code' => $address->postal_code,
                ]);

                // Buat item order
                foreach ($items as $item) {

                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->final_price
                    ]);

                    // Kurangi stok
                    $item->product->decrement(
                        'stock',
                        $item->quantity
                    );

                    // Hapus dari keranjang
                    $item->delete();
                }
            }
        });

        // Kembalikan data tagihan ke React
        return response()->json([
            'success' => true,
            'message' => 'Checkout berhasil! Silakan lakukan pembayaran.',
            'data' => [
                'invoice_number' => $invoiceNumber,
                'grand_total' => $grandTotal,

                // Kirim kembali alamat untuk kebutuhan frontend
                'address' => [
                    'id' => $address->id,
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'shipping_address' => $address->address,
                    'province' => $address->province,
                    'city' => $address->city,
                    'district' => $address->district,
                    'postal_code' => $address->postal_code,
                ]
            ]
        ], 200);
    }

    // Endpoint Simulasi Pembayaran
    public function paySimulation(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string'
        ]);

        $invoice = $request->invoice_number;

        // Cari apakah tagihan dengan nomor tersebut ada di database
        $orders = Order::where('invoice_number', $invoice)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan.'
            ], 404);
        }

        // Ubah semua pesanan yang terikat dengan invoice menjadi paid
        Order::where('invoice_number', $invoice)
            ->update([
                'status' => 'paid'
            ]);

        return response()->json([
            'success' => true,
            'message' => "Pembayaran untuk tagihan $invoice berhasil disimulasikan!",
            'invoice_number' => $invoice
        ], 200);
    }
}
