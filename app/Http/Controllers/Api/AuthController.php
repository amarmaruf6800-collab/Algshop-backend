<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasi data dari React
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Buat user baru
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Buat token Sanctum
        $token = $user->createToken('AlgshopToken')->plainTextToken;

        // 4. Kirim response ke React
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. Tangkap email dan password yang dikirim dari React/Frontend
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Cek ke database apakah cocok?
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 3. Jika cocok, buatkan Token rahasia khusus untuk user ini
            $token = $user->createToken('AlgshopToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'user'    => $user,
                'token'   => $token
            ], 200);
        }

        // Jika salah password/email
        return response()->json([
            'success' => false,
            'message' => 'Email atau Password salah'
        ], 401);
    }
}
