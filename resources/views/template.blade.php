<!DOCTYPE html>
<html>
<head>
    <title>Algshop Marketplace</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px;">
    
    {{-- Menu Navigasi Global --}}
    {{-- Menu Navigasi Global --}}
    <nav style="background: #2c3e50; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <a href="/katalog" style="color: white; text-decoration: none; margin-right: 20px; font-weight: bold;">🏠 Beranda Algshop</a>
        <a href="/toko" style="color: #bdc3c7; text-decoration: none; margin-right: 20px;">🏢 Daftar Toko</a>
        
        @auth
            {{-- Menu untuk User yang sudah Login --}}
            <a href="/dasbor-toko" style="color: white; text-decoration: none; margin-right: 20px;">🏪 Toko Saya</a>
            <a href="/riwayat-belanja" style="color: white; text-decoration: none; margin-right: 20px;">🛒 Riwayat Belanja</a>
            <a href="/keranjang" style="color: white; text-decoration: none; margin-right: 20px;">🛒 Keranjang</a>
            
            <div style="float: right;">
                <span style="color: #f1c40f; margin-right: 15px;">Halo, {{ Auth::user()->name }}!</span>
                {{-- Tombol Logout bawaan Breeze --}}
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; cursor: pointer;">Logout</button>
                </form>
            </div>
        @else
            {{-- Menu untuk Tamu yang belum Login --}}
            <div style="float: right;">
                <a href="/login" style="color: white; text-decoration: none; margin-right: 15px;">Login</a>
                <a href="/register" style="color: white; text-decoration: none;">Register</a>
            </div>
        @endauth
    </nav>

    {{-- Di sinilah konten dari file-file lain akan disuntikkan --}}
    @yield('content')

</body>
</html>