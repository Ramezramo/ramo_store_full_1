<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Add baseline browser protections without overriding application-specific
     * headers set later by a payment, map, or authentication integration.
     *
     * A narrowly scoped Content Security Policy begins in report-only mode.
     * It must be observed against every third-party script and payment flow in
     * staging before enforcement is enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Checkout uses browser geolocation, so geolocation is intentionally not disabled here.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=()');
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; connect-src 'self' https://api.country.is https://ipwho.is; upgrade-insecure-requests"
        );
        $response->headers->remove('X-Powered-By');

        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
