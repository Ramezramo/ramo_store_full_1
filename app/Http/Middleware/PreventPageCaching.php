<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Temporary visual-QA cache policy for dynamic storefront pages.
 *
 * Static files remain served directly by the web server. Only HTML responses
 * and redirects produced by Laravel are marked no-store so a normal phone
 * reload always receives the latest storefront markup, inline styles, and
 * inline scripts.
 */
class PreventPageCaching
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // This middleware exists for visual QA only. Keep normal caching available
        // whenever production-safe debug mode is disabled, while preserving instant
        // reloads during local visual QA.
        if (! config('app.debug')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($request->isMethod('GET') && ($response->isRedirection() || str_contains($contentType, 'text/html'))) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
