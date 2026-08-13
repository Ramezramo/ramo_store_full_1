<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Exposes a plain (non-HttpOnly, non-encrypted) cookie reflecting whether the
 * current visitor is authenticated. It carries no sensitive data — only a
 * 0/1 flag — and exists so the client-side Service Worker page cache
 * (see public/sw.js) can decide, purely from request headers, whether it is
 * safe to serve a cached copy of the Home/Shop pages (guests only) or must
 * always hit the network (authenticated users, to avoid ever showing one
 * user's session inside another's cached page).
 */
class SetAuthFlagCookie
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $flag = Auth::check() ? '1' : '0';
        $secure = (bool) config('session.secure', false) || $request->isSecure();

        // Plain, readable-by-JS, short-path cookie — must NOT be added to
        // EncryptCookies' except-list requirement since we build it raw here.
        // It is still marked Secure whenever the session policy or request
        // requires HTTPS, preventing a production downgrade through this
        // customer-facing cache-state flag.
        $response->headers->setCookie(
            new Cookie('ramo_auth_flag', $flag, 0, '/', null, $secure, false, false, 'Lax')
        );

        return $response;
    }
}
