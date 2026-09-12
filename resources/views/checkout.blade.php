<!DOCTYPE html>
<html>
<head>
    <title>Checkout {{ $product->name }}</title>
</head>
<body>
    <h1>Checkout Produk</h1>
    <hr>
    <h3>Anda akan membeli: <strong>{{ $product->name }}</strong></h3>
    <p>Harga Satuan: Rp{{ number_format($product->price, 0, ',', '.') }}</p>
    <p>Sisa Stok Tersedia: {{ $product->stock }}</p>

    <form action="/produk/{{ $product->id }}/beli" method="POST">
        @csrf
        <label>Jumlah Beli:</label><br>
        <input type="number" name="quantity" value="1" min="1" required><br><br>
        
        <button type="submit">Bayar Sekarang</button>
    </form>
</body>
</html>