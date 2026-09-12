<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    // A. Untuk Pembeli: Riwayat Belanja
    public function history(Request $request)
    {
        $orders = Order::with(['shop', 'items.product'])
            ->where('user_id', $request->user()->id)
            ->latest()->get();

        return response()->json(['success' => true, 'data' => $orders], 200);
    }

    // B. Untuk Penjual: Pesanan Masuk
    public function incomingOrders(Request $request)
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Anda belum memiliki toko.'], 403);
        }

        $orders = Order::with(['user', 'items.product'])
            ->where('shop_id', $shop->id)
            ->latest()->get();

        return response()->json(['success' => true, 'data' => $orders], 200);
    }

    // C. Untuk Penjual: Update Status Resi
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->shop_id !== $request->user()->shop->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $order->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status pesanan berhasil diperbarui.']);
    }
}
