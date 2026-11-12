<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleCheck
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $auth = $request->attributes->get('auth');
        if (! $auth || ! in_array($auth['role'], $roles, true)) {
            return response()->json(['message' => 'Accès refusé pour votre rôle.'], 403);
        }
        return $next($request);
    }
}
