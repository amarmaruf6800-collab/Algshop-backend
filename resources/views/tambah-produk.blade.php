<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk Baru</title>
</head>
<body>
    <h1>Tambah Produk untuk {{ $shop->name }}</h1>
    <hr>

    {{-- Form ini diatur untuk metode POST --}}
    <form action="/toko/{{ $shop->id }}/produk" method="POST">
        @csrf {{-- Ini adalah kunci gembok keamanan Laravel --}}

        <label>Nama Produk:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Deskripsi:</label><br>
        <textarea name="description"></textarea><br><br>

        <label>Harga (Rp):</label><br>
        <input type="number" name="price" required><br><br>

        <label>Stok Awal:</label><br>
        <input type="number" name="stock" required><br><br>

        <button type="submit">Simpan Produk</button>
    </form>
</body>
</html>