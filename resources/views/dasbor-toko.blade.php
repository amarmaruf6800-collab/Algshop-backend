<!DOCTYPE html>
@extends('template')

@section('content')
    <h1>Dasbor Toko: {{ $shop->name }}</h1>
    <p>{{ $shop->description }}</p>
    
    <a href="/toko/{{ $shop->id }}/pesanan">
        <button style="background: #27ae60; color: white; padding: 10px; border: none; cursor: pointer;">Cek Pesanan Masuk</button>
    </a>
    <hr>

    <h3>Tambah Produk Baru</h3>
    {{-- WAJIB tambahkan enctype="multipart/form-data" --}}
    <form action="/dasbor-toko/produk" method="POST" enctype="multipart/form-data" style="background: #ecf0f1; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        @csrf
        <div style="margin-bottom: 10px;">
            Nama Produk: <input type="text" name="name" required style="margin-right: 10px;">
            Harga (Rp): <input type="number" name="price" required style="margin-right: 10px;">
            Stok: <input type="number" name="stock" required style="margin-right: 10px;">
        </div>
        <div style="margin-bottom: 10px;">
            Deskripsi: <input type="text" name="description" style="width: 300px; margin-right: 10px;">
            {{-- Input untuk File Gambar --}}
            Gambar Produk: <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" style="background: #2980b9; color: white; padding: 5px 15px; border: none; cursor: pointer;">Simpan Produk</button>
    </form>

    <h3>Katalog Produk Saya</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        @foreach ($shop->products as $produk)
            <div style="border: 1px solid #ccc; padding: 15px; width: 200px; border-radius: 5px;">
                {{-- Logika untuk menampilkan gambar jika ada, atau gambar default jika kosong --}}
                @if ($produk->image)
                    <img src="{{ asset('storage/' . $produk->image) }}" alt="{{ $produk->name }}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
                @else
                    <div style="width: 100%; height: 150px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">Tanpa Gambar</div>
                @endif
                
                <strong>{{ $produk->name }}</strong><br>
                <span style="color: #e67e22; font-weight: bold;">Rp{{ number_format($produk->price, 0, ',', '.') }}</span><br>
                Sisa Stok: {{ $produk->stock }}
            </div>
            <div style="margin-top: 15px; display: flex; gap: 5px;">
                {{-- Tombol Edit --}}
                <a href="/dasbor-toko/produk/{{ $produk->id }}/edit">
                    <button style="background: #f39c12; color: white; border: none; padding: 5px 15px; cursor: pointer; border-radius: 3px;">Edit</button>
                </a>

                {{-- Tombol Hapus (Menggunakan form dengan metode DELETE) --}}
                <form action="/dasbor-toko/produk/{{ $produk->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 15px; cursor: pointer; border-radius: 3px;">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection