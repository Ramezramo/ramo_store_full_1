# Ramo Store — Release-readiness audit notes

## Authoritative references consulted

| Topic | Key point used in this audit | Source |
|---|---|---|
| Laravel deployment | Production deployments should use `composer install --optimize-autoloader --no-dev`, configuration caching, and route/view caching where suitable. `APP_DEBUG` must be `false` in production. | [Laravel 10 deployment](https://laravel.com/docs/10.x/deployment) |
| Laravel configuration | Laravel advises using `env()` only within configuration files when `config:cache` is used, because `.env` is not loaded during requests after configuration caching. | [Laravel 10 configuration](https://laravel.com/docs/10.x/configuration) |
| Laravel queues | The `sync` queue driver executes work immediately and is intended for local development; Redis, database, SQS, and Beanstalkd are asynchronous queue backends. | [Laravel 10 queues](https://laravel.com/docs/10.x/queues) |
| Laravel support policy | Laravel major releases receive security fixes for two years. Laravel 10 security support ended on 4 February 2025; Laravel 12 security support ends on 24 February 2027. | [Laravel release notes](https://laravel.com/docs/13.x/releases) |

## Local audit evidence — 13 August 2026

| Area | Observed result | Initial assessment |
|---|---|---|
| Runtime environment | فحص Laravel في عملية التشغيل الحالية أظهر `Environment=production` مع `Debug Mode=enabled`. هذا يعني أن متغير بيئة العملية قد يغلّب قيمة ملف `.env`. | Production blocker: يجب فرض `APP_ENV=production` و`APP_DEBUG=false` في سرّيات/بيئة منصة النشر نفسها، ثم التحقق بـ`php artisan about`. |
| State and background work | `SESSION_DRIVER=file`, `CACHE_DRIVER=file`, `QUEUE_CONNECTION=sync` | Not horizontally scalable; external message work runs on the request path. |
| Cookies | `SESSION_SECURE_COOKIE=false`, `SESSION_SAME_SITE=lax` | `secure` must be true behind HTTPS. `lax` is normally suitable for a commerce site with external redirects. |
| Response headers | Public response returns `Cache-Control: no-cache, private`; no HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, or Permissions-Policy header was observed. | The development cache middleware now applies only while `APP_DEBUG=true`; a security-header policy must be set by the deployment edge/web server after compatibility testing. |
| Dependency scan | `composer audit` reported 32 advisories affecting 10 packages, including high-severity advisories. Installed packages include Laravel 10.50.2, Guzzle 7.10.0, Guzzle PSR-7 2.9.0, League CommonMark 2.8.2, and Symfony 6.4 components. | Release blocker. Upgrade and re-run the audit before production. |
| First-visit language | New middleware supports `CF-IPCountry`, `CloudFront-Viewer-Country`, `X-Vercel-IP-Country`, and `X-AppEngine-Country`. Arab League country codes set Arabic; others set English; existing session choice wins. | Implemented and tested. A production CDN/edge must inject and sanitize one of these headers; none was visible on the current development URL. |
| Customer journey smoke test | `/`, `/shop`, `/search`, `/cart`, `/login`, and `/track` returned 200. Guest `/checkout` returned 302 to `/cart` because the cart was empty; the controller requires login once a valid cart exists. Single-request TTFB was about 56–95 ms on the current temporary URL. | Basic public navigation is healthy. This is not a concurrent load test and must not be interpreted as a production capacity guarantee. |
| Test suite | Five test files exist after adding locale coverage. The complete user checkout, OTP, payment, cart persistence, and database-concurrency paths do not have feature-test coverage. | Significant release risk. |
| Data-access review | Home page bulk-loads product variations, avoiding a per-card variation query. However it performs multiple per-widget queries and uses expensive patterns such as `ORDER BY RANDOM()`. Product catalogue filters/search rely on columns that have no visible supporting indexes in the supplied migrations. | Needs profiling and targeted PostgreSQL indexes before marketing-scale traffic. |

## Current conclusion

The storefront changes are suitable for continued development and demonstration, but the application is **not production-ready yet** for a real user launch or a 10,000-user target. The minimum go-live gates are: fix all dependency advisories, upgrade from unsupported Laravel 10 to a supported release, move sessions/cache/queues to managed shared services, set production-only environment and HTTPS cookie settings, disable development no-cache behavior outside local development, add observability and critical-flow tests, and complete a representative authenticated load test.

This file records evidence and sources. The final report will provide a prioritized implementation checklist and a capacity model.

## Supplementary source notes

- Cloudflare documents that enabling IP Geolocation adds `CF-IPCountry` to requests sent to the origin: <https://developers.cloudflare.com/network/ip-geolocation/>.
- AWS documents that `CloudFront-Viewer-Country` contains the viewer's two-letter country code and can be sent to the origin through the appropriate policy: <https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/adding-cloudfront-headers.html>.
- Laravel's production guide states that `config:cache` requires `env()` calls to remain inside configuration files and that `APP_DEBUG` must always be `false` in production: <https://laravel.com/docs/10.x/deployment>.
- Laravel's official release schedule shows security fixes for Laravel 10 ended on 4 February 2025; Laravel 12 security support runs until 24 February 2027: <https://laravel.com/docs/13.x/releases>.
