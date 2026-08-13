<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetInitialLocaleFromCountry
{
    /**
     * Arabic-speaking members of the Arab League, represented by ISO 3166-1 alpha-2 country codes.
     *
     * @var array<int, string>
     */
    private const ARAB_COUNTRIES = [
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
            $request->session()->put(
                'locale',
                in_array($this->countryCode($request), self::ARAB_COUNTRIES, true) ? 'ar' : 'en'
            );
        }

        return $next($request);
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
