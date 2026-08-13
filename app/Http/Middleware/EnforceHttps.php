<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    /**
     * Redirect plain HTTP requests only when the deployment has explicitly
     * enabled HTTPS. TrustProxies runs before this middleware, so a configured
     * load balancer's X-Forwarded-Proto header prevents redirect loops.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.force_https') && ! $request->isSecure()) {
            return redirect()->to(url($request->getRequestUri()), 308);
        }

        return $next($request);
    }
}
