<!DOCTYPE html>
<html>
<head><title>Pesanan Masuk</title></head>
<body>
    <h1>Dasbor Pesanan Toko</h1>
    <hr>

    @foreach ($orders as $order)
        <div style="background-color: #f9f9f9; padding: 10px; margin-bottom: 15px;">
            <h3>Order ID: #{{ $order->id }}</h3>
            <p>Pembeli: {{ $order->user->name }} | Tanggal: {{ $order->created_at }}</p>
            
            <ul>
                @foreach ($order->items as $item)
                    <li>{{ $item->product->name }} (Qty: {{ $item->quantity }})</li>
                @endforeach
            </ul>
            <p>Total Pendapatan: <strong>Rp{{ number_format($order->total_price, 0, ',', '.') }}</strong></p>

            {{-- Form Update Status --}}
            <form action="/pesanan/{{ $order->id }}/update-status" method="POST">
                @csrf
                <label>Update Status:</label>
                <select name="status">
                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Dibayar</option>
                    <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Dikirim</option>
                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>
                <button type="submit">Simpan</button>
            </form>
        </div>
    @endforeach
</body>
</html>