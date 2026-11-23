<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
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

        $vendor = $workspace->vendor();
        if (! $vendor) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'An active seller session is required.'], Auth::guard('web')->check() ? 403 : 401);
            }

            return Auth::guard('web')->check()
                ? redirect()->route('vendor.register')
                : self::redirectToAccountSignIn($request);
        }
        if ($vendor->status !== 'approved') {
            Auth::guard('vendor_web')->logout();
            return redirect()->route(Auth::guard('web')->check() ? 'vendor.register' : 'vendor.login')
                ->with('error', 'Your seller account is not currently active.');
        }
        return $next($request);
    }

    public static function redirectToAccountSignIn(Request $request): RedirectResponse
    {
        if ($request->isMethodSafe()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('login')->header('Cache-Control', 'no-store, private');
    }
}
