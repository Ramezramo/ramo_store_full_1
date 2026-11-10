<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\VendorWorkspace;

class VendorWebAuth
{
    public function handle(Request $request, Closure $next)
    {
        $workspace = app(VendorWorkspace::class);

        if ($workspace->isAdminManaging()) {
            if (! $workspace->vendor()) {
                $request->session()->forget(VendorWorkspace::SESSION_KEY);
                abort(404, 'Vendor account not found.');
            }

            return $next($request);
        }

        if (! Auth::guard('vendor_web')->check()) {
            return redirect()->route('vendor.login')
                ->with('error', 'Please log in to access your vendor dashboard.');
        }
        return $next($request);
    }
}
