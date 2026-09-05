<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please sign in to access the admin panel.');
        }

        $user = Auth::user();
        $decodedRoles = is_array($user->role)
            ? $user->role
            : json_decode((string) $user->role, true);
        $roles = is_array($decodedRoles)
            ? $decodedRoles
            : [(string) $user->role];

        if (!in_array('admin', $roles, true)) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
