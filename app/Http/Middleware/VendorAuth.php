<?php

namespace App\Http\Middleware;

use App\Models\VendorUser;
use Closure;
use Illuminate\Http\Request;

class VendorAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!($user instanceof VendorUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only vendors can access this resource.',
            ], 401);
        }

        return $next($request);
    }
}