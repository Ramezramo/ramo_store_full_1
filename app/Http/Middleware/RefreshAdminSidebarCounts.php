<?php

namespace App\Http\Middleware;

use App\Http\View\Composers\AdminSidebarComposer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sidebar counts are cached, so any admin action that mutates data must drop
 * the cached values. Without this, approving a vendor or deleting a coupon
 * would leave a stale badge until the cache expired on its own.
 */
class RefreshAdminSidebarCounts
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe()) {
            Cache::forget(AdminSidebarComposer::CACHE_KEY);
        }

        return $response;
    }
}
