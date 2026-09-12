<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    // Mengambil semua katalog produk beserta toko, gambar, dan ulasan
    public function index()
    {
        $products = Product::with(['shop', 'reviews', 'images'])
            ->where('stock', '>', 0)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data katalog produk Algshop',
            'data'    => $products
        ], 200);
    }

    public function storeReview(Request $request, $id)
    {
        $user = $request->user();

        $hasBought = OrderItem::whereHas('order', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('product_id', $id)->exists();

        if (!$hasBought) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya bisa mengulas produk yang sudah dibeli.'
            ], 403);
        }

        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah memberikan ulasan.'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string'
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil disimpan.',
            'data' => $review
        ], 200);
    }

    // Ambil detail produk beserta semua gambar dan ulasan
    public function show($id)
    {
        $product = Product::with(['shop', 'reviews.user', 'images'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product
        ], 200);
    }

    // Ambil produk milik toko sendiri
    public function myProducts(Request $request)
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Belum punya toko'
            ], 403);
        }

        $products = Product::with('images')
            ->where('shop_id', $shop->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    // Tambah produk baru dengan satu atau beberapa gambar
    public function storeMyProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $product = $request->user()->shop->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'stock' => $validated['stock'],
        ]);

        $this->saveProductImages($product, $request->file('images', []));

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product->load('images')
        ]);
    }

    // Update produk. Jika mengirim gambar baru, gambar lama diganti.
    public function updateMyProduct(Request $request, $id)
    {
        $product = $request->user()->shop->products()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'stock' => $validated['stock'],
        ]);

        if ($request->hasFile('images')) {
            $this->deleteProductImages($product);
            $this->saveProductImages($product, $request->file('images', []));
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diupdate',
            'data' => $product->load('images')
        ]);
    }

    // Hapus produk dan seluruh gambarnya
    public function destroyMyProduct(Request $request, $id)
    {
        $product = $request->user()->shop->products()->findOrFail($id);

        $this->deleteProductImages($product);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }

    private function saveProductImages(Product $product, array $files): void
    {
        foreach ($files as $index => $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }
    }

    private function deleteProductImages(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        // Bersihkan gambar legacy yang mungkin masih dimiliki produk lama.
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            $product->update(['image' => null]);
        }
    }
}
