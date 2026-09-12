<!DOCTYPE html>
<html>
<head><title>Buka Toko di Algshop</title></head>
<body>
    <h1>Mulai Berjualan di Algshop!</h1>
    <hr>
    
    <form action="/buka-toko" method="POST">
        @csrf
        <label>Nama Toko:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Deskripsi Toko:</label><br>
        <textarea name="description" required></textarea><br><br>

        <button type="submit">Buka Toko Sekarang</button>
    </form>
</body>
</html>