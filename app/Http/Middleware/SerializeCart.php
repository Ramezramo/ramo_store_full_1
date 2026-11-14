<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SerializeCart
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Vendor and customer IDs live in separate tables and can overlap.
        abort_if($user && ! $user instanceof User, 403, 'A customer account is required.');
        if (! $user) return $next($request);

        // Lock before reading the cart, through its mutation and order commit.
        return DB::transaction(function () use ($request, $next, $user) {
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
            return $next($request);
        });
    }
}
