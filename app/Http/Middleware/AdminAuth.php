<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please sign in to access the admin panel.');
        }

        $user = Auth::user();
        $adminEmail = DB::table('app_configs')
            ->where('config_key', 'admin_email')
            ->value('value');
        $adminEmail = $adminEmail ? trim(json_decode($adminEmail) ?? $adminEmail, '"') : null;

        $isAdmin = $user->email === $adminEmail
            || $user->email === 'adminramoui@gmail.com'
            || $user->role === 'admin'
            || str_contains((string)$user->role, 'admin');

        if (!$isAdmin) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
