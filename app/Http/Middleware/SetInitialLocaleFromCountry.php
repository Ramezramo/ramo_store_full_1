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
        $source = (string) $request->session()->get('locale_source', '');
        $hasLocale = $request->session()->has('locale');
        $legacyOrManual = $source === '' || in_array($source, ['manual', 'legacy_session'], true);
        $oldClientSource = in_array($source, ['fallback_pending', 'browser_language', 'client_ip', 'client_language'], true);

        // Automatic locale results are resolved on the server. This repairs old
        // sessions created by the removed browser splash flow. If a server lookup
        // has no usable IP result, preserve an already-selected session locale
        // rather than unexpectedly forcing it back to English.
        if (! $hasLocale || $oldClientSource) {
            $this->storeServerLocale($request);
        } elseif ($source === 'server_default') {
            // Retry a previously failed server lookup, but only replace the value
            // when a real country is found.
            $country = $this->countryCode($request) ?? $this->countryCodeFromServerIp($request->ip());
            if ($country !== null) {
                $request->session()->put('locale', self::localeForCountry($country));
                $request->session()->put('locale_source', $this->countryCode($request) !== null ? 'trusted_edge' : 'server_ip');
            } elseif (($language = $this->localeFromAcceptLanguage($request)) !== null) {
                $request->session()->put('locale', $language);
                $request->session()->put('locale_source', 'accept_language');
            }
        } elseif ($legacyOrManual && ! $request->session()->has('locale_source')) {
            $request->session()->put('locale_source', 'legacy_session');
        }

        return $next($request);
    }

    private function storeServerLocale(Request $request): void
    {
        $country = $this->countryCode($request);
        $source = $country !== null ? 'trusted_edge' : null;

        if ($country === null) {
            $country = $this->countryCodeFromServerIp($request->ip());
            $source = $country !== null ? 'server_ip' : 'server_default';
        }

        if ($country !== null) {
            $request->session()->put('locale', self::localeForCountry($country));
            $request->session()->put('locale_source', $source);
            return;
        }

        $language = $this->localeFromAcceptLanguage($request);
        $request->session()->put('locale', $language ?? 'en');
        $request->session()->put('locale_source', $language !== null ? 'accept_language' : 'server_default');
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

        $country = Cache::remember($cacheKey, now()->addDay(), function () use ($ip): string {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout(1)
                    ->timeout(2)
                    ->get('https://ipwho.is/'.rawurlencode($ip));

                if (! $response->successful() || $response->json('success') === false) {
                    return '__none__';
                }

                $country = strtoupper(trim((string) $response->json('country_code')));
                return preg_match('/^[A-Z]{2}$/', $country) ? $country : '__none__';
            } catch (\Throwable) {
                return '__none__';
            }
        });

        return $country === '__none__' ? null : $country;
    }

    private function localeFromAcceptLanguage(Request $request): ?string
    {
        foreach (explode(',', strtolower((string) $request->header('Accept-Language'))) as $candidate) {
            $language = trim((string) explode(';', $candidate, 2)[0]);

            if (str_starts_with($language, 'ar')) {
                return 'ar';
            }
            if (str_starts_with($language, 'en')) {
                return 'en';
            }
        }

        return null;
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
