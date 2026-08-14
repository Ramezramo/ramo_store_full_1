<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthApi
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $roles = is_array($user->role)
            ? $user->role
            : (json_decode((string) $user->role, true) ?: [(string) $user->role]);

        if (!in_array('admin', $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
