# Ramo Store Release-Readiness Report

**Updated:** 13 August 2026

**Application:** Ramo Store storefront, Laravel 12.66.0, PHP 8.3, PostgreSQL

**Scope:** Customer-facing storefront only. Admin and seller pages were not changed.

## Executive conclusion

**The application is materially safer than at the original audit, but it is not ready to serve a 10,000-user public launch until the production infrastructure is provisioned and validated.** The framework has been upgraded to Laravel 12.66.0, the Composer audit is clean, configuration safety is now testable, Redis support is bundled, and real-provider OTP sends no longer need to block the login request. The development `log` SMS fallback deliberately continues to expose the OTP in the UI while no provider has been selected.

| Area | Current result | Launch status |
|---|---|---|
| Arabic/English first-visit locale | Egypt and all Arab League countries resolve to Arabic; other countries resolve to English. A manual selection always wins. | **Ready**, when a trusted CDN header is supplied. |
| Framework and dependency security | Laravel was upgraded from 10.50.2 to **12.66.0**; `composer audit` reports no advisories. | **Resolved.** |
| Automated regression | `php artisan test`: **18 passed, 60 assertions**. | Passed; wider checkout/payment test coverage remains required. |
| Production configuration gate | `php artisan production:check` fails a release if debug, HTTPS, secure cookies, shared state, queues, or shared storage are unsafe. | **Implemented**, but current development runtime correctly fails the gate. |
| Redis/queue preparation | Predis 2.4.1 is bundled; the Redis queue has bounded blocking and after-commit defaults. Real-provider OTP delivery is queued. | **Code-ready; external Redis and workers are still required.** |
| OTP development experience | `SMS_GATEWAY=log` remains synchronous and returns `dev_otp` only when debug is enabled. | Preserved by request; **not production-ready** until a provider is selected. |
| Edge and headers | The storefront now returns baseline `nosniff`, frame, referrer, permissions, and HTTPS HSTS headers. A trusted CDN/origin restriction and tested CSP remain outstanding. | **Partially resolved; deployment gate remains.** |

## Completed fixes

The following commits are published on GitHub branch `main`:

| Commit | Completed work |
|---|---|
| `02d6122` and `9ee083a` | Country-based locale selection, safe config caching, development-only no-cache policy, production template, and audit documentation. |
| `de9aff9` and `c385f23` | Laravel 12 dependency upgrade, regenerated package manifests, and a clean Composer audit. |
| `c5ab727` | `production:check` command and test coverage for effective production configuration. |
| `df4eeb9` | Bundled Predis Redis client, queue defaults, asynchronous real-provider OTP job, and OTP dispatch tests. |
| Current incremental change | Baseline storefront security headers with regression coverage; public header verification completed. |

> The development OTP fallback is intentionally unchanged: with `SMS_GATEWAY=log` and `APP_DEBUG=true`, the OTP is still shown to the developer. When `SMS_GATEWAY` is changed to a real provider, the request queues `SendOtpSms` instead and returns immediately. The job skips expired or already-verified OTP records, uses a short timeout, and has bounded retries.

Laravel's configuration cache requires all `env()` access to remain in configuration files; the application was refactored accordingly before enabling the documented production cache commands. [1] Laravel 12 supports Redis through either the recommended PhpRedis extension or the bundled `predis/predis` package, which allows the planned shared session, cache, and queue topology without requiring an extension on the first deployment. [2]

## Current production gate result

Running `php artisan production:check` against the present development server **must fail**. It reports `APP_DEBUG=true`, an HTTP localhost URL, insecure cookies, file sessions/cache, a synchronous queue, and local storage. This is correct for the temporary development environment and is no longer a hidden risk: the command is designed to stop a deployment if those values leak into production.

| Required production setting | Target | Reason |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Prevents debug data from being exposed to visitors. [1] |
| `APP_URL` / session cookie | `https://…` / `SESSION_SECURE_COOKIE=true` | Ensures authenticated cookies are sent only on HTTPS. |
| Sessions and cache | `SESSION_DRIVER=redis`, `CACHE_DRIVER=redis` | Allows more than one application instance to share state. |
| Queue | `QUEUE_CONNECTION=redis`, supervised workers | Removes real-provider SMS and other slow jobs from the request path. [3] |
| Files | `FILESYSTEM_DISK=s3` or equivalent shared persistent storage | Prevents receipt and media files from being tied to a single application host. |

The non-secret `.env.production.example` documents these exact settings. A deployment must populate its placeholders through the hosting platform's secret store, run `php artisan config:cache`, and then run `php artisan production:check` on every release before traffic is routed to it.

## Remaining launch gates

### 1. Provision the production services

Create a staging environment that matches the intended production topology: at least two stateless PHP application instances behind a load balancer, managed PostgreSQL with backups and point-in-time recovery, managed Redis, object storage, and supervised queue workers. Start with two workers that process `otp,default` in that priority order and monitor queue depth, failure count, and oldest-job age. Laravel documents persistent queue workers and their Redis configuration requirements. [2] [3]

### 2. Select and configure an SMS provider

No provider selection has been made, so `SMS_GATEWAY=log` remains by design. Before public launch, select either the existing Msegat or Vonage integration, place credentials in the production secret store, test delivery only in staging, and change `SMS_GATEWAY` there. Do not set `APP_DEBUG=true` merely to retrieve a code: production must use the actual provider and no `dev_otp` value is returned when debug is disabled.

### 3. Add edge protection and headers

Place the site behind Cloudflare, CloudFront, or an equivalent trusted edge; terminate HTTPS there; redirect HTTP to HTTPS; and reject direct public origin access. Cloudflare can provide `CF-IPCountry` when IP Geolocation is enabled, while CloudFront can pass `CloudFront-Viewer-Country` via an origin-request policy. [4] [5] Restrict those country headers to traffic originating at the chosen edge.

The storefront now sends HSTS for HTTPS requests, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, and a restrictive camera/microphone permissions policy. The remaining browser-control gate is a Content Security Policy compatible with payment, maps, images, and storefront scripts. Do not deploy a CSP without confirming checkout, payment, and map operation in staging.

### 4. Prove business-critical journeys

The automated coverage has improved but still does not exercise all order-sensitive paths. Add isolated tests for cart persistence and guest migration, OTP verification and throttling, checkout validation, payment callbacks, inventory limits under concurrency, receipt upload, refunds, and simultaneous purchases of limited stock. Execute end-to-end checkout tests in staging with sandbox payment and SMS credentials.

### 5. Establish capacity evidence

Ten thousand accounts is not the same as ten thousand concurrent users. The prior planning model assumes five percent peak activity, or about 500 active sessions, and approximately 17 dynamic requests/second for one dynamic request every 30 seconds. This is only a sizing hypothesis. Validate it with staged baseline, peak, and failure-mode tests at 30 then 50 mixed requests/second, while measuring p50/p95/p99 latency, error rate, PostgreSQL utilisation, Redis saturation, and OTP queue lag.

## Deployment sequence

| Order | Release action | Required evidence |
|---:|---|---|
| 1 | Provision staging Redis, object storage, PostgreSQL, queue workers, sandbox SMS, and sandbox payments. | No production customer data or credentials are used. |
| 2 | Set all production secrets based on `.env.production.example`; run caches and the production gate. | `php artisan production:check` exits 0; cookies show `Secure`. |
| 3 | Configure the CDN country header and origin access restriction. | Egypt/Arab and non-Arab first-visit tests pass; manual language selection remains unchanged. |
| 4 | Configure one real SMS provider in staging. | OTP is delivered, request time is not provider-bound, failures are visible in queue monitoring. |
| 5 | Complete journey and load tests. | Agreed latency/error/queue objectives pass with no order-integrity failures. |
| 6 | Deploy through rolling or blue/green release with health checks, alerts, backups, and a tested rollback plan. | A monitored canary release is healthy before wider traffic is enabled. |

## Required production commands

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan production:check
php artisan queue:restart
```

The worker supervisor must then run persistent workers, for example `php artisan queue:work redis --queue=otp,default --tries=2 --timeout=15`, using deployment-specific process supervision and restart policies. The command is a starting point; worker counts and limits must be tuned from staging load results. [3]

## Final decision

**Do not launch to the full 10,000-user audience yet.** The dependency-security blocker, request-path OTP bottleneck, and lack of a reproducible production configuration check have been resolved in code. The remaining no-go gates are external but material: provision shared Redis/object storage/workers, choose an SMS provider, enable a trusted HTTPS edge with headers and origin protection, and pass end-to-end plus staged load tests.

## References

[1]: https://laravel.com/docs/12.x/deployment "Laravel 12 — Deployment"
[2]: https://laravel.com/docs/12.x/redis "Laravel 12 — Redis"
[3]: https://laravel.com/docs/12.x/queues "Laravel 12 — Queues"
[4]: https://developers.cloudflare.com/network/ip-geolocation/ "Cloudflare — IP geolocation"
[5]: https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/adding-cloudfront-headers.html "AWS — Add CloudFront request headers"
