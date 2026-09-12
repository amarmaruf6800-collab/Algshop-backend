<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('user')->get();
        return view('toko', compact('shops'));
    }

    public function show($id)
    {
        $shop = Shop::with('products')->findOrFail($id);
        return view('detail-toko', compact('shop'));
    }

    public function createProduct($id)
    {
        $shop = Shop::findOrFail($id);
        return view('tambah-produk', compact('shop'));
    }

    public function storeProduct(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $shop->products()->create($request->only('name', 'description', 'price', 'stock'));
        return redirect('/toko/' . $id);
    }

    // 1. Menampilkan form buka toko (jika belum punya)
    public function createShop()
    {
        // Jika sudah punya toko, langsung usir ke dasbor
        if (Auth::user()->shop) {
            return redirect('/dasbor-toko');
        }
        return view('buka-toko');
    }

    // 2. Memproses pembuatan toko
    public function storeShop(Request $request)
    {
        Auth::user()->shop()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return redirect('/dasbor-toko');
    }

    // 3. Halaman Dasbor Penjual
    public function myShop()
    {
        $shop = Auth::user()->shop;

        // Jika belum punya toko, arahkan ke halaman buka toko
        if (!$shop) {
            return redirect('/buka-toko');
        }

        // Ambil data toko beserta produk-produknya
        $shop->load('products');
        return view('dasbor-toko', compact('shop'));
    }

    // 4. Tambah produk dari Dasbor
    public function storeMyProduct(Request $request)
    {
        // 1. Siapkan variabel untuk path gambar
        $imagePath = null;

        // 2. Cek apakah penjual mengunggah file gambar
        if ($request->hasFile('image')) {
            // Simpan file ke dalam folder 'storage/app/public/products'
            // Nilai yang dikembalikan adalah teks path filenya (misal: products/nama-acak.jpg)
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 3. Simpan data ke database beserta path gambarnya
        Auth::user()->shop->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        return back();
    }

    public function editProduct($id)
    {
        // Keamanan: Pastikan hanya mencari di dalam produk milik toko user ini
        $product = Auth::user()->shop->products()->findOrFail($id);
        return view('edit-produk', compact('product'));
    }

    // Memproses perubahan data produk
    public function updateProduct(Request $request, $id)
    {
        $product = Auth::user()->shop->products()->findOrFail($id);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
        ];

        // Jika ada file gambar baru yang diunggah
        if ($request->hasFile('image')) {
            // Hapus gambar lama secara fisik dari folder storage jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            // Simpan gambar baru
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect('/dasbor-toko');
    }

    public function destroyProduct($id)
    {
        $product = Auth::user()->shop->products()->findOrFail($id);

        // Hapus file gambar dari server sebelum menghapus data di database
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return back();
    }

    // Menampilkan etalase global (semua produk dari semua toko)
    public function globalCatalog(Request $request)
    {
        // 1. Mulai query dasar: Ambil produk yang ada stoknya
        $query = Product::with('shop')->where('stock', '>', 0);

        // 2. Fitur Pencarian: Jika pembeli mengetik sesuatu di kotak pencarian
        if ($request->has('cari') && $request->cari != '') {
            // Cari nama produk yang mengandung kata kunci tersebut
            $query->where('name', 'like', '%' . $request->cari . '%');
        }

        // 3. Pagination: Tampilkan maksimal 12 produk per halaman (bukan get() yang mengambil semua)
        $products = $query->latest()->paginate(12);

        return view('katalog-global', compact('products'));
    }

    // Menampilkan Halaman Detail Produk beserta daftar ulasannya
    public function detailProduct($id)
    {
        // Tarik data produk beserta info toko dan kumpulan ulasan (beserta nama pengulas)
        $product = Product::with(['shop', 'reviews.user'])->findOrFail($id);

        return view('detail-produk', compact('product'));
    }

    // Memproses form ulasan dari Pembeli
    public function storeReview(Request $request, $id)
    {
        // 1. Keamanan Lapis 1: Pastikan user benar-benar sudah pernah membeli barang ini
        // Kita mencarinya di tabel order_items yang berelasi dengan tabel orders miliknya
        $hasBought = OrderItem::whereHas('order', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('product_id', $id)->exists();

        if (!$hasBought) {
            abort(403, 'Anda hanya bisa mengulas produk yang sudah dibeli.');
        }

        // 2. Keamanan Lapis 2: Cegah spam ulasan ganda
        $existingReview = Review::where('user_id', Auth::id())->where('product_id', $id)->first();
        if ($existingReview) {
            abort(403, 'Anda sudah pernah memberikan ulasan untuk produk ini.');
        }

        // 3. Simpan Ulasan
        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return back();
    }
}
