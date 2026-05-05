<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('vendor')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only vendors can add products.',
            ], 401);
        }

        return $next($request);
    }
}