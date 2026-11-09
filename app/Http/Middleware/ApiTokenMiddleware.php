<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['success' => false, 'code' => 'UNAUTHENTICATED', 'message' => 'Bearer token diperlukan.', 'details' => []], 401);
        }

        try {
            $payload = JWT::decode($token, new Key((string) config('app.key'), 'HS256'));
            $user = User::find($payload->sub ?? null);
        } catch (ExpiredException) {
            return response()->json(['success' => false, 'code' => 'TOKEN_EXPIRED', 'message' => 'Token sudah kedaluwarsa.', 'details' => []], 401);
        } catch (\Throwable) {
            return response()->json(['success' => false, 'code' => 'INVALID_TOKEN', 'message' => 'Token tidak valid.', 'details' => []], 401);
        }

        if (! $user) {
            return response()->json(['success' => false, 'code' => 'UNAUTHENTICATED', 'message' => 'Pengguna tidak ditemukan.', 'details' => []], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
