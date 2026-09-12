@extends('template')

@section('content')
<h1 style="text-align: center; margin-bottom: 20px;">Belanja Apa Saja di Algshop!</h1>

{{-- MODUL BARU: Form Pencarian --}}
<form action="/katalog" method="GET" style="text-align: center; margin-bottom: 30px;">
    {{-- request('cari') digunakan agar teks yang diketik tidak hilang setelah ditekan enter --}}
    <input type="text" name="cari" placeholder="Cari nama produk..." value="{{ request('cari') }}" style="padding: 12px; width: 40%; border-radius: 5px; border: 1px solid #ccc; font-size: 1em;">
    <button type="submit" style="padding: 12px 25px; background: #2980b9; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em;">Cari</button>

    @if(request('cari'))
    <a href="/katalog" style="margin-left: 10px; color: #e74c3c; text-decoration: none; font-weight: bold;">✖ Reset</a>
    @endif
</form>

{{-- Area Katalog --}}
<div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-bottom: 30px;">
    @forelse ($products as $produk)
    <div style="border: 1px solid #ddd; padding: 15px; width: 220px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); background: white;">
        @if ($produk->image)
        <img src="{{ asset('storage/' . $produk->image) }}" alt="{{ $produk->name }}" style="width: 100%; height: 180px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
        @else
        <div style="width: 100%; height: 180px; background: #eee; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; border-radius: 5px;">Tanpa Gambar</div>
        @endif

        <h3 style="margin: 0 0 5px 0; font-size: 1.2em;">
            <a href="/produk/{{ $produk->id }}" style="text-decoration: none; color: #333;">
                {{ $produk->name }}
            </a>
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 0.9em; color: gray;">🏪 {{ $produk->shop->name }}</p>
        <h2 style="margin: 0 0 10px 0; color: #e67e22;">Rp{{ number_format($produk->price, 0, ',', '.') }}</h2>

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
    @empty
    {{-- Tampilan jika produk yang dicari tidak ditemukan --}}
    <h3 style="color: gray; width: 100%; text-align: center;">Maaf, produk tidak ditemukan.</h3>
    @endforelse
</div>

{{-- MODUL BARU: Tombol Pagination (Halaman 1, 2, 3...) --}}
<div style="display: flex; justify-content: center; margin-top: 20px;">
    {{ $products->links() }}
</div>
@endsection