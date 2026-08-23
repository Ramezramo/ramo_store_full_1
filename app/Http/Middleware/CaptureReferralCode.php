<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferralCode
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = strtoupper(trim((string) $request->query('ref', '')));
        $hasExistingReferral = (string) $request->cookie('ref_code', '') !== '';

        if (! $hasExistingReferral && $code !== '' && preg_match('/^[A-Z0-9]{4,20}$/', $code)) {
            Cookie::queue(cookie(
                'ref_code',
                $code,
                60 * 24 * 30,
                '/',
                null,
                (bool) config('session.secure', false),
                true,
                false,
                'lax'
            ));
        }

        return $next($request);
    }
}
