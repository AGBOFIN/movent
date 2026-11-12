<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

/** Vérifie localement le JWT émis par le User Service (secret partagé). */
class JwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }
        try {
            $p = JWT::decode($token, new Key((string) env('JWT_SECRET'), 'HS256'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token invalide ou expiré.'], 401);
        }
        $request->attributes->set('auth', [
            'id' => $p->sub, 'role' => $p->role, 'email' => $p->email, 'name' => $p->name,
        ]);
        return $next($request);
    }
}
