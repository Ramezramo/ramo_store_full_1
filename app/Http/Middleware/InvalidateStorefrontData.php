<?php

namespace App\Http\Middleware;

use App\Services\StorefrontDataCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvalidateStorefrontData
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe() && ($response->isSuccessful() || $response->isRedirection())) {
            StorefrontDataCache::invalidate();
        }

        return $response;
    }
}
