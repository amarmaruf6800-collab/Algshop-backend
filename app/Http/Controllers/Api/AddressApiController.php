<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressApiController extends Controller
{
    // 1. Menampilkan semua alamat milik user
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ], 200);
    }

    // 2. Menambahkan alamat baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'address' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        $user = $request->user();

        // Jika ini alamat pertama, otomatis menjadi default
        $hasAddress = Address::where('user_id', $user->id)->exists();

        if (!$hasAddress) {
            $validated['is_default'] = true;
        }

        // Jika user memilih alamat ini sebagai default,
        // alamat lama harus dibuat tidak default
        if (!empty($validated['is_default'])) {
            Address::where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $validated['user_id'] = $user->id;
        $validated['label'] = $validated['label'] ?? 'Rumah';

        $address = Address::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $address
        ], 201);
    }

    // 3. Mengedit alamat
    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'address' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $request->user()->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $validated['label'] = $validated['label'] ?? 'Rumah';

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui.',
            'data' => $address->fresh()
        ], 200);
    }

    // 4. Menghapus alamat
    public function destroy(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $wasDefault = $address->is_default;

        $address->delete();

        // Kalau alamat yang dihapus adalah default,
        // jadikan alamat terbaru sebagai default
        if ($wasDefault) {
            $newDefault = Address::where('user_id', $request->user()->id)
                ->latest()
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus.'
        ], 200);
    }

    // 5. Menjadikan alamat sebagai default
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $address = Address::where('user_id', $user->id)
            ->findOrFail($id);

        // Semua alamat user dibuat tidak default
        Address::where('user_id', $user->id)
            ->update(['is_default' => false]);

        // Alamat yang dipilih menjadi default
        $address->update([
            'is_default' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diubah.',
            'data' => $address->fresh()
        ], 200);
    }
}
