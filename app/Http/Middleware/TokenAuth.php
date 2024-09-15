<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class TokenAuth
{
    public function handle($request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['message' => 'Authorization token is required.'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        // Validate the token
        if ($token !== Cache::get('auth_token')) {
            return response()->json(['message' => 'Invalid or expired authorization token.'], 403);
        }

        return $next($request);
    }
}
