@extends('template')

@section('content')
    <h1>Keranjang Belanja Saya</h1>
    <hr style="margin-bottom: 20px;">

    @if ($carts->isEmpty())
        <p style="color: gray; font-style: italic;">Keranjang Anda masih kosong. Yuk belanja!</p>
        <a href="/katalog"><button style="padding: 10px; background: #3498db; color: white; border: none; border-radius: 5px;">Kembali ke Katalog</button></a>
    @else
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <tr style="background: #ecf0f1; text-align: left;">
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Produk</th>
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Toko</th>
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Harga Satuan</th>
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Jumlah</th>
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Subtotal</th>
                <th style="padding: 10px; border: 1px solid #bdc3c7;">Aksi</th>
            </tr>
            
            @php $grandTotal = 0; @endphp
            
            @foreach ($carts as $item)
                @php 
                    $subtotal = $item->product->price * $item->quantity; 
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td style="padding: 10px; border: 1px solid #bdc3c7;">
                        <strong>{{ $item->product->name }}</strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #bdc3c7;">{{ $item->product->shop->name }}</td>
                    <td style="padding: 10px; border: 1px solid #bdc3c7;">Rp{{ number_format($item->product->price, 0, ',', '.') }}</td>
                    <td style="padding: 10px; border: 1px solid #bdc3c7;">{{ $item->quantity }}</td>
                    <td style="padding: 10px; border: 1px solid #bdc3c7; color: #e67e22; font-weight: bold;">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td style="padding: 10px; border: 1px solid #bdc3c7;">
                        <form action="/keranjang/{{ $item->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>

        <div style="background: #f9f9f9; padding: 20px; text-align: right; border-radius: 5px; border: 1px solid #ddd;">
            <h2 style="margin-top: 0;">Total Pembayaran: Rp{{ number_format($grandTotal, 0, ',', '.') }}</h2>
            <form action="/checkout-keranjang" method="POST">
                @csrf
                <button type="submit" style="background: #27ae60; color: white; border: none; padding: 15px 30px; font-size: 1.1em; font-weight: bold; border-radius: 5px; cursor: pointer;">
                    Checkout Semua Barang
                </button>
            </form>
        </div>
    @endif
@endsection