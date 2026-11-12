<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Protège les routes internes entre microservices. */
class InternalKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = (string) env('INTERNAL_API_KEY');
        if ($key === '' || ! hash_equals($key, (string) $request->header('X-Internal-Key', ''))) {
            return response()->json(['message' => 'Route interne.'], 403);
        }
        return $next($request);
    }
}
