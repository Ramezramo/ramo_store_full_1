<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SetInitialLocaleFromCountry
{
    /**
     * Arabic-speaking members of the Arab League, represented by ISO 3166-1 alpha-2 country codes.
     *
     * @var array<int, string>
     */
    public const ARAB_COUNTRIES = [
        'AE', 'BH', 'DJ', 'DZ', 'EG', 'IQ', 'JO', 'KM', 'KW', 'LB', 'LY',
        'MA', 'MR', 'OM', 'PS', 'QA', 'SA', 'SD', 'SO', 'SY', 'TN', 'YE',
    ];

    /**
     * Set a first-visit storefront locale from a trusted edge country header.
     *
     * A previously selected locale always takes precedence, including after logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('locale')) {
            $country = $this->countryCode($request);
            $source = $country !== null ? 'trusted_edge' : null;

            if ($country === null) {
                $country = $this->countryCodeFromServerIp($request->ip());
                $source = $country !== null ? 'server_ip' : 'server_default';
            }

            $request->session()->put('locale', self::localeForCountry($country));
            $request->session()->put('locale_source', $source);
        } elseif (! $request->session()->has('locale_source')) {
            // Legacy sessions already have a locale. Preserve it and mark it as
            // server-resolved instead of reopening a client-side locale flow.
            $request->session()->put('locale_source', 'legacy_session');
        }

        return $next($request);
    }

    public static function localeForCountry(?string $country): string
    {
        return in_array(strtoupper((string) $country), self::ARAB_COUNTRIES, true) ? 'ar' : 'en';
    }

    private function countryCodeFromServerIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $cacheKey = 'server_locale_country.'.hash('sha256', $ip);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($ip): ?string {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout(1)
                    ->timeout(2)
                    ->get('https://ipwho.is/'.rawurlencode($ip));

                if (! $response->successful() || $response->json('success') === false) {
                    return null;
                }

                $country = strtoupper(trim((string) $response->json('country_code')));
                return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
            } catch (\Throwable) {
                return null;
            }
        });
    }

    private function countryCode(Request $request): ?string
    {
        foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Vercel-IP-Country', 'X-AppEngine-Country'] as $header) {
            $country = strtoupper(trim((string) $request->header($header)));

            if (preg_match('/^[A-Z]{2}$/', $country)) {
                return $country;
            }
        }

        return null;
    }
}
