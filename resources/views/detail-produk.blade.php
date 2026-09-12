@extends('template')

@section('content')
<div style="display: flex; gap: 30px; margin-bottom: 40px;">
    {{-- Sisi Kiri: Gambar Produk --}}
    <div style="flex: 1;">
        @if ($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" style="width: 100%; border-radius: 8px; border: 1px solid #ddd;">
        @else
        <div style="width: 100%; height: 300px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 8px;">Tanpa Gambar</div>
        @endif
    </div>

    {{-- Sisi Kanan: Info & Tombol Beli --}}
    <div style="flex: 1;">
        <h1 style="margin: 0 0 10px 0;">{{ $product->name }}</h1>

        {{-- Kalkulasi Rata-rata Bintang --}}
        @php $avgRating = $product->reviews->avg('rating') ?: 0; @endphp
        <p style="color: #f39c12; font-size: 1.2em; font-weight: bold; margin: 0 0 10px 0;">
            ⭐ {{ number_format($avgRating, 1) }} / 5.0
            <span style="color: gray; font-size: 0.8em; font-weight: normal;">({{ $product->reviews->count() }} Ulasan)</span>
        </p>

        <p style="font-size: 1.1em; color: gray;">🏪 Toko: <strong><a href="/toko/{{ $product->shop_id }}" style="color: #3498db; text-decoration: none;">{{ $product->shop->name }}</a></strong></p>
        <h1 style="color: #e67e22; margin: 15px 0;">Rp{{ number_format($product->price, 0, ',', '.') }}</h1>
        <p><strong>Sisa Stok:</strong> {{ $product->stock }}</p>
        <hr>
        <p style="line-height: 1.6;">{{ $product->description }}</p>

        {{-- UI Tombol Ganda Anda --}}
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <form action="/keranjang/{{ $product->id }}" method="POST" style="flex: 1;">
                @csrf
                <button type="submit" style="width: 100%; background: #2980b9; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; font-weight: bold;">
                    🛒 + Keranjang
                </button>
            </form>
            <a href="/produk/{{ $product->id }}/beli" style="flex: 1; text-align: center; background: #27ae60; color: white; padding: 12px; text-decoration: none; border-radius: 5px; font-size: 1em; font-weight: bold;">
                ⚡ Beli Cepat
            </a>
        </div>
    </div>
</div>

{{-- Area Ulasan Pembeli --}}
<hr>
<h2>Ulasan Pembeli</h2>

@auth
<div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd;">
    <h4 style="margin-top: 0;">Pernah membeli barang ini? Tulis ulasan Anda!</h4>
    <form action="/produk/{{ $product->id }}/ulasan" method="POST">
        @csrf
        Beri Bintang:
        <select name="rating" required style="padding: 5px; margin-bottom: 10px;">
            <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
            <option value="4">⭐⭐⭐⭐ Bagus</option>
            <option value="3">⭐⭐⭐ Lumayan</option>
            <option value="2">⭐⭐ Kurang</option>
            <option value="1">⭐ Buruk</option>
        </select><br>
        <textarea name="comment" rows="3" placeholder="Bagaimana kualitas produk ini?" required style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc;"></textarea><br>
        <button type="submit" style="background: #f39c12; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Kirim Ulasan</button>
    </form>
</div>
@endauth

{{-- Daftar Komentar --}}
@forelse ($product->reviews as $ulasan)
<div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
    <strong>{{ $ulasan->user->name }}</strong>
    <span style="color: #f39c12;"> - ⭐ {{ $ulasan->rating }}</span><br>
    <span style="color: gray; font-size: 0.8em;">{{ $ulasan->created_at->diffForHumans() }}</span>
    <p style="margin-top: 5px;">"{{ $ulasan->comment }}"</p>
</div>
@empty
<p style="color: gray;">Belum ada ulasan untuk produk ini.</p>
@endforelse

@endsection