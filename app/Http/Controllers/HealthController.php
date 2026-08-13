<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Public liveness/readiness probe for an external uptime service.
     * Return no infrastructure details: the status code is sufficient for
     * monitoring, while the structured server log identifies the failed dependency.
     */
    public function __invoke(): JsonResponse
    {
        try {
            DB::selectOne('SELECT 1 AS available');
        } catch (\Throwable $exception) {
            Log::error('health_check_failed', ['dependency' => 'database']);

            return $this->unavailable();
        }

        try {
            Cache::get('__ramo_health_probe__');
        } catch (\Throwable $exception) {
            Log::error('health_check_failed', ['dependency' => 'cache']);

            return $this->unavailable();
        }

        return response()
            ->json(['status' => 'ok'])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function unavailable(): JsonResponse
    {
        return response()
            ->json(['status' => 'unavailable'], 503)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
