<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorWebAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('vendor_web')->check()) {
            return redirect()->route('vendor.login')
                ->with('error', 'Please log in to access your vendor dashboard.');
        }
        return $next($request);
    }
}
