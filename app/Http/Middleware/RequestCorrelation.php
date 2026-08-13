<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestCorrelation
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);
        $startedAt = microtime(true);

        $request->attributes->set('request_id', $requestId);
        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'route' => (string) optional($request->route())->getName(),
        ]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        if (! config('app.debug')) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            Log::channel(config('logging.default'))->info('http_request_completed', [
                'status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
            ]);
        }

        return $response;
    }

    private function requestId(Request $request): string
    {
        $candidate = trim((string) $request->headers->get('X-Request-ID', ''));

        // Only accept a bounded token-safe identifier from a trusted proxy/client.
        // This prevents header newline/control-character injection into structured logs.
        if ($candidate !== '' && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $candidate)) {
            return $candidate;
        }

        return (string) Str::uuid();
    }
}
