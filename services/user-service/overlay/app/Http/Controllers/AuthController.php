<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function token(User $u): string
    {
        $now = time();
        return JWT::encode([
            'sub' => $u->id, 'role' => $u->role, 'email' => $u->email,
            'name' => $u->first_name.' '.$u->last_name,
            'iat' => $now, 'exp' => $now + 8 * 3600,
        ], (string) env('JWT_SECRET'), 'HS256');
    }

    public function register(Request $r)
    {
        $d = $r->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:8|max:100',
            'phone' => 'nullable|string|max:30',
            'role' => 'nullable|in:PARTICIPANT,ORGANIZER', // ADMIN uniquement via seed / administrateur
        ]);
        $u = User::create([...$d, 'role' => $d['role'] ?? 'PARTICIPANT', 'is_active' => true]);
        return response()->json(['token' => $this->token($u), 'user' => $u], 201);
    }

    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $u = User::where('email', $d['email'])->first();
        if (! $u || ! Hash::check($d['password'], $u->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }
        if (! $u->is_active) {
            return response()->json(['message' => 'Ce compte est désactivé.'], 403);
        }
        return response()->json(['token' => $this->token($u), 'user' => $u]);
    }

    public function logout()
    {
        // JWT sans état : le client supprime son token.
        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $r)
    {
        return User::findOrFail($r->attributes->get('auth')['id']);
    }

    public function updateMe(Request $r)
    {
        $u = User::findOrFail($r->attributes->get('auth')['id']);
        $d = $r->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8|max:100',
        ]);
        if (empty($d['password'])) {
            unset($d['password']);
        }
        $u->update($d);
        return $u;
    }
}
