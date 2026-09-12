@extends('template')

@section('content')
<div style="background: #ecf0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
    <h1 style="margin-top: 0;">{{ $shop->name }}</h1>
    <p style="color: #555; font-size: 1.1em;">{{ $shop->description }}</p>
    <p style="color: gray;">Pemilik: {{ $shop->user->name }}</p>
</div>

<h3>Katalog Produk {{ $shop->name }}</h3>
<hr style="margin-bottom: 20px;">

<div style="display: flex; flex-wrap: wrap; gap: 20px;">
    @foreach ($shop->products as $produk)
    <div style="border: 1px solid #ddd; padding: 15px; width: 220px; border-radius: 8px; background: white;">
        @if ($produk->image)
        <img src="{{ asset('storage/' . $produk->image) }}" alt="{{ $produk->name }}" style="width: 100%; height: 180px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
        @else
        <div style="width: 100%; height: 180px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border-radius: 5px;">Tanpa Gambar</div>
        @endif

        <h3 style="margin: 0 0 5px 0;">{{ $produk->name }}</h3>
        <h2 style="margin: 0 0 5px 0; color: #e67e22;">Rp{{ number_format($produk->price, 0, ',', '.') }}</h2>
        <p style="margin: 0 0 15px 0; font-size: 0.9em; color: gray;">Sisa Stok: {{ $produk->stock }}</p>

        <div style="display: flex; gap: 5px; margin-top: 10px;">
            {{-- Tombol 1: Tambah ke Keranjang --}}
            <form action="/keranjang/{{ $produk->id }}" method="POST" style="flex: 1;">
                @csrf
                <button type="submit" style="width: 100%; background: #2980b9; color: white; padding: 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                    + Keranjang
                </button>
            </form>

            {{-- Tombol 2: Beli Langsung (Bisa diarahkan ke rute checkout kilat yang sudah dibuat sebelumnya) --}}
            <a href="/produk/{{ $produk->id }}/beli" style="flex: 1; text-align: center; background: #27ae60; color: white; padding: 8px; text-decoration: none; border-radius: 4px; font-size: 0.9em; font-weight: bold;">
                Beli Cepat
            </a>
        </div>
    </div>
    @endforeach
</div>
@endsection