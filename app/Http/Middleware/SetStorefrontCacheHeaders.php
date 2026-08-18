<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontCacheHeaders
{
    /**
     * Apply cache policy by response sensitivity. This middleware deliberately
     * does nothing while APP_DEBUG is enabled, leaving visual QA reloads under
     * PreventPageCaching. Production catalog pages are cacheable only for guests
     * and vary by locale/session cookies, preventing cross-customer markup leaks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (config('app.debug') || ! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html') && ! str_contains($contentType, 'application/xml')) {
            return $response;
        }

        // Controllers may specify a more precise cache policy (for example the XML sitemap).
        // Laravel sessions add `no-cache, private` by default; that framework default
        // must not prevent the production catalog policy from taking effect.
        $existingCacheControl = (string) $response->headers->get('Cache-Control', '');
        if ($existingCacheControl !== '' && ! in_array($existingCacheControl, ['no-cache, private', 'private, no-cache'], true)) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($this->isPersonalized($request, $routeName)) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        // Locale is selected through a session-backed customer preference. Varying
        // by Cookie keeps a CDN or intermediary from serving Arabic markup to an
        // English request (or vice versa) while still allowing short-lived caching.
        $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=60');
        $response->headers->set('Vary', 'Cookie, Accept-Language');

        return $response;
    }

    private function isPersonalized(Request $request, string $routeName): bool
    {
        if (Auth::check()) {
            return true;
        }

        if ($request->is([
            'cart', 'cart/*', 'checkout', 'checkout/*', 'account', 'account/*',
            'wishlist', 'wishlist/*', 'shop', 'login', 'register', 'forgot-password',
            'reset-password', 'order-success/*', 'my-order', 'track', 'auth/*',
        ])) {
            return true;
        }

        return str_starts_with($routeName, 'cart.')
            || str_starts_with($routeName, 'checkout.')
            || str_starts_with($routeName, 'account.')
            || str_starts_with($routeName, 'order.')
            || str_starts_with($routeName, 'guest-order.')
            || str_starts_with($routeName, 'auth.');
    }
}