<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['success' => false, 'code' => 'INVALID_CREDENTIALS', 'message' => 'Email atau password salah.', 'details' => []], 422);
        }

        $now = now()->timestamp;
        $token = JWT::encode(['iss' => config('app.url'), 'sub' => $user->id, 'role' => $user->role, 'iat' => $now, 'exp' => $now + 3600], (string) config('app.key'), 'HS256');

        return response()->json(['success' => true, 'message' => 'Login berhasil.', 'data' => ['token' => $token, 'expires_in' => 3600, 'user' => $user->only('id', 'name', 'email', 'role')], 'meta' => []]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Data pengguna.', 'data' => $request->user()->only('id', 'name', 'email', 'role'), 'meta' => []]);
    }
}
