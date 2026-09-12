@extends('template')

@section('content')
    <h1>Edit Produk: {{ $product->name }}</h1>
    <hr>

    <form action="/dasbor-toko/produk/{{ $product->id }}" method="POST" enctype="multipart/form-data" style="background: #ecf0f1; padding: 20px; border-radius: 5px;">
        @csrf
        @method('PUT') {{-- Mengakali HTML agar mengirim sebagai PUT Request --}}
        
        <div style="margin-bottom: 15px;">
            <label>Nama Produk:</label><br>
            <input type="text" name="name" value="{{ $product->name }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Harga (Rp):</label><br>
            <input type="number" name="price" value="{{ $product->price }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Stok:</label><br>
            <input type="number" name="stock" value="{{ $product->stock }}" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Deskripsi:</label><br>
            <textarea name="description" style="width: 100%; padding: 8px;">{{ $product->description }}</textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Gambar Saat Ini:</label><br>
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="Gambar lama" style="height: 100px; margin-bottom: 10px;"><br>
            @else
                <p style="color: gray; font-size: 0.9em;">Belum ada gambar</p>
            @endif
            <label>Ganti Gambar (Kosongkan jika tidak ingin ganti):</label><br>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit" style="background: #f39c12; color: white; padding: 10px 20px; border: none; cursor: pointer;">Update Produk</button>
        <a href="/dasbor-toko" style="margin-left: 15px; color: #7f8c8d; text-decoration: none;">Batal</a>
    </form>
@endsection