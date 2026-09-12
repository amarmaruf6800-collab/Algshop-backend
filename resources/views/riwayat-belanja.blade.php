<!DOCTYPE html>
<html>
<head><title>Riwayat Belanja</title></head>
<body>
    <h1>Riwayat Belanja Anda</h1>
    <hr>

    @foreach ($orders as $order)
        <div style="border: 1px solid black; padding: 10px; margin-bottom: 10px;">
            <h3>Order ID: #{{ $order->id }} - Toko: {{ $order->shop->name }}</h3>
            <p>Status: <strong>{{ strtoupper($order->status) }}</strong></p>
            <p>Total Bayar: Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
            
            <h4>Rincian Barang:</h4>
            <ul>
                @foreach ($order->items as $item)
                    <li>{{ $item->product->name }} ({{ $item->quantity }}x) - Rp{{ number_format($item->price, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</body>
</html>