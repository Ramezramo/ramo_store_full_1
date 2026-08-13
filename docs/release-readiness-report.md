# Ramo Store Release-Readiness Report

**Assessment date:** 13 August 2026  
**Application:** Ramo Store storefront, Laravel 10.50.2, PHP 8.3, PostgreSQL  
**Scope:** Customer-facing storefront only. Admin and seller pages were not changed.

## Executive conclusion

**The storefront is functionally improved but is not ready for a public production launch serving approximately 10,000 users yet.** The key customer-facing work is working: Arabic-first behaviour is now selected for first visits from Egypt and the other Arab League countries, English is selected for other countries, and an explicit language choice in the session always wins. The full test suite passes with **12 tests and 35 assertions**.

The production decision is nevertheless a **no-go** until the security, dependency, infrastructure, and load-validation gates in this report are completed. The strongest blockers are the enabled debug mode observed in the running configuration, the vulnerable dependency audit, Laravel 10’s expired security support, local file-based state, and synchronous OTP delivery. These are production-system concerns rather than storefront-design defects.

| Area | Current result | Launch decision |
|---|---|---|
| Arabic/English first-visit locale | Implemented and covered by three feature tests. Manual selection is preserved. | Ready, provided the CDN sends a trusted country header. |
| Local visual QA cache policy | `no-store` remains active while debug is enabled; the policy automatically stops when `APP_DEBUG=false`. | Ready for development; production must set debug off. |
| Basic public navigation | Home, shop, search, cart, login, and tracking paths returned successful responses. The empty guest checkout redirected safely. | Basic smoke check passed; not a replacement for end-to-end checkout tests. |
| Automated regression | `php artisan test`: 12 passed, 35 assertions. | Passed, but coverage remains too narrow for launch. |
| Framework and package security | `composer audit` found 32 advisories affecting 10 packages, including high-severity advisories. Laravel 10 is out of security support. | **Blocker.** |
| Session, cache, and queue topology | Current runtime configuration uses file sessions, file cache, and a synchronous queue. | **Blocker for multi-instance operation and sustained peak traffic.** |
| Production configuration | The running configuration reported `Environment=production` with debug still enabled. | **Security blocker.** |

## Changes completed and pushed

The following implementation is now on GitHub branch `main` in commit **`02d6122`** (`Add locale detection and production readiness safeguards`).

> The locale middleware checks `CF-IPCountry`, `CloudFront-Viewer-Country`, `X-Vercel-IP-Country`, and `X-AppEngine-Country`. It writes a locale only when the session has no existing `locale` value. Egypt and every other Arab League country resolve to `ar`; every other valid, absent, or unrecognized code resolves to `en`.

A deployment must place the site behind a properly configured CDN or edge layer. Cloudflare can add `CF-IPCountry` to origin requests when IP Geolocation is enabled, and CloudFront can provide `CloudFront-Viewer-Country` through its origin-request policy. [1] [2] The current temporary development URL did not expose any such header, so first-time visitors there default to English; this is expected and does not affect a user who selects Arabic manually.

The production-safety changes also move the remaining application-level environment reads into Laravel configuration files. This makes `config:cache` safe for the image-host override, SMS gateways, Mailtrap notification delivery, and the password-reset development branch. Laravel explicitly states that, after configuration is cached, `env()` calls outside configuration files will return `null`. [3] The new `.env.production.example` documents the required production environment variables without containing any credentials.

Finally, the temporary cache-prevention middleware now applies its `no-store` policy only while application debug mode is enabled. This preserves immediate reloads on the current development URL, while allowing normal caching in production once `APP_DEBUG=false` is enforced.

## Evidence from this assessment

The audit was deliberately non-destructive. It used source inspection, dependency audit output, configuration-cache verification, the automated test suite, and single-request smoke checks against the public development URL. It did **not** place synthetic orders, send real OTP messages, submit payments, or run an aggressive load test against the shared temporary URL.

| Check | Result | Interpretation |
|---|---|---|
| `php artisan test` | 12 passed; 35 assertions | The new locale and cache-policy checks pass with all pre-existing tests. |
| `php artisan config:cache` | Completed successfully after the configuration refactor | The application can use Laravel configuration caching, but real production values still need to be supplied by the deployment environment. |
| Running configuration check | Environment reported as production; debug reported as enabled | Debug must be disabled in the process/environment actually used by the web and worker services, not only in a local `.env` file. |
| Public route smoke check | `/`, `/shop`, `/search`, `/cart`, `/login`, and `/track` returned 200; a cartless guest checkout returned 302 | Navigation is reachable and empty-cart checkout does not create an order. |
| Single-request timing | Approximately 56–95 ms time-to-first-byte on the temporary URL | A useful point-in-time observation only; it does not establish throughput, peak capacity, or availability. |
| Response headers | Current development GET response includes `no-store` while debug is enabled | The requested no-cache development behaviour still works. Security headers were not observed on the temporary URL. |

## Mandatory no-go gates before production

### 1. Eliminate the dependency and framework-security risk

The audit found **32 Composer advisories across 10 packages**, including high-severity findings. No package update was performed in this task because a framework upgrade must be tested as a change set, not applied blindly to a commerce application. The installed Laravel 10.50.2 line is also no longer within security support. Laravel’s official policy provides security fixes for two years; its published schedule shows Laravel 10 security fixes ended on **4 February 2025**, whereas Laravel 12 security fixes run until **24 February 2027**. [4]

Upgrade the application and all audited dependencies to a currently supported Laravel release, resolve every applicable `composer audit` advisory, run the complete test suite, and repeat the customer-journey tests before release. This is a hard gate, not an optional improvement.

### 2. Enforce safe runtime configuration in the deployment platform

Set `APP_ENV=production` and **`APP_DEBUG=false`** in the secrets or environment configuration that starts PHP-FPM, the queue workers, and deployment commands. Then verify the effective values with `php artisan about`. Laravel warns that enabling debug in production can expose sensitive configuration values to users. [3]

The following production settings are included as a non-secret template in `.env.production.example`: HTTPS-only session cookies, Redis-backed sessions/cache/queues, S3-compatible file storage, logging level, mail delivery, and SMS settings. Replace all placeholders with managed-service credentials held in the deployment secret store. Do not copy a development `.env` file to a public server.

### 3. Make application state shared and asynchronous

`SESSION_DRIVER=file` and `CACHE_DRIVER=file` prevent safe horizontal scaling because each application instance keeps its own local state. `QUEUE_CONNECTION=sync` means slow email or OTP work remains on the HTTP request path. In the current OTP flow, the SMS call is still synchronous in code; configuring Redis alone will not make it asynchronous.

Before launch, use managed Redis for sessions, cache, and queues. Refactor OTP and notification delivery into queued jobs with retry, timeout, failure handling, and idempotency controls. Run at least two supervised queue workers and monitor their queue depth, failure count, and oldest-job age. Laravel’s queue documentation recommends persistent worker processes; the deployment guide documents the supporting production optimization commands. [3] [5]

### 4. Protect the edge and establish security headers

Terminate HTTPS at a CDN or load balancer, force HTTP-to-HTTPS redirects, and restrict direct origin access so that only the edge may reach the application. This also makes country-header data meaningful: a direct client should not be able to inject a location header straight into the origin. The locale header affects presentation only, not authorization, but the origin should nevertheless trust it only from the selected edge.

Configure and test HSTS, `X-Content-Type-Options: nosniff`, an appropriate `X-Frame-Options` or CSP `frame-ancestors` policy, `Referrer-Policy`, and a carefully tested Content Security Policy. The current temporary URL did not return these headers. Laravel’s Nginx production example includes `X-Frame-Options` and `X-Content-Type-Options`; the final policy must be checked against payment, maps, image, and third-party script behaviour. [3]

### 5. Prove the business-critical journeys

Current feature tests do not cover cart persistence, guest-to-account cart migration, OTP request/verify throttling, checkout validation, payment confirmation, inventory/quantity limits, receipt upload, refunds, or simultaneous purchases of limited stock. Add isolated test fixtures and feature tests for these flows. A separate staging environment with sandbox SMS and payment credentials should be used for the complete end-to-end suite.

## Capacity model for approximately 10,000 users

A count of 10,000 registered users does not define peak capacity. Capacity depends on concurrent active users, browsing cadence, product/catalogue size, SMS and payment latency, bot traffic, and a target response-time and availability objective. Therefore, the values below are **planning assumptions**, not measured production capacity.

| Planning input | Conservative working assumption | Resulting planning implication |
|---|---:|---|
| Registered users | 10,000 | This is the total audience, not simultaneous traffic. |
| Peak active share | 5% | About 500 concurrent active sessions at peak. Validate this assumption using analytics after launch. |
| Dynamic browsing cadence | One dynamic request per active session every 30 seconds | Approximately 17 dynamic requests per second before checkout bursts and automated traffic. |
| Validation headroom | Test to at least 30 read requests/second, then 50 requests/second, with realistic product pages and session behaviour | Gives headroom above the planning baseline; record p50/p95/p99 latency, error rate, database load, Redis saturation, and queue lag. |
| Write-path exercise | Run a separate staged profile for cart changes, OTP requests, and checkout/order creation | These requests are more expensive and must be rate-limited and idempotent. |

A practical initial architecture is two stateless PHP application instances behind a health-checked load balancer, managed PostgreSQL with automated backups and point-in-time recovery, managed Redis for shared sessions/cache/queues, object storage for receipts and media, a CDN for static assets and country headers, and at least two supervised queue workers. This is an **availability-oriented starting point**, not a capacity certification. Autoscaling bounds and instance sizes should be selected from a staging load test rather than guessed.

The acceptance threshold should be agreed before testing. A reasonable first release target is a sustained mixed workload at the agreed peak rate with an application error rate below 1%, no checkout/order-integrity failures, stable database and Redis utilization, queue lag below the agreed operational limit, and a p95 response time acceptable to the business. The exact latency objective should be chosen with the owner; it was not supplied for this assessment.

## Recommended release sequence

| Order | Required action | Exit evidence |
|---:|---|---|
| 1 | Create a production-like staging environment with separate PostgreSQL, Redis, object storage, sandbox SMS, and sandbox payments. | Secrets are isolated; staging has no production customer data. |
| 2 | Upgrade from Laravel 10 and update all vulnerable Composer packages. | `composer audit` has no unresolved applicable advisories; migration tests and regression suite pass. |
| 3 | Configure the production variables from `.env.production.example` in the deployment secret store. Set debug off and secure cookies on. | `php artisan about` confirms production and disabled debug; cookie inspection confirms `Secure`. |
| 4 | Enable CDN country-header delivery and lock down direct origin access. | Egypt/Arab and non-Arab synthetic first visits select the correct locale; manual choice remains unchanged. |
| 5 | Move OTP/email work to queued jobs and start monitored workers. Add rate limits for OTP and sensitive endpoints. | Failed-job handling and retries are demonstrated in staging; no external send blocks the request path. |
| 6 | Add the missing customer-journey feature tests and execute end-to-end staging tests. | Cart, login, OTP, payment, order, stock-limit, and receipt paths pass. |
| 7 | Run baseline, peak, and failure-mode load tests in staging. | Observability dashboard and agreed latency/error/queue thresholds pass. |
| 8 | Deploy by rolling or blue/green release, warm caches, run migrations safely, monitor, and retain a tested rollback plan. | Health checks, logs, error alerts, order metrics, and rollback procedure are verified. |

## Deployment commands after the required gates pass

Laravel recommends optimized Composer installation and cached configuration, routes, and views for production. [3] Run these only in the production deployment pipeline after correct secrets are present and after the dependency upgrade is complete:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
php artisan about
```

The deployment system should start or reload PHP-FPM and the queue-worker supervisor only after the release is healthy. Always retain the prior release and database rollback decision process; database migrations are not automatically reversible without review.

## Final decision

**Do not launch to the full 10,000-user audience today.** The core storefront and new locale behaviour are in a good functional state, but the security and infrastructure gates above are material. The site becomes a reasonable candidate for a controlled staging-to-production rollout only after the framework/dependency audit is clean, debug is disabled in the actual runtime, Redis and worker infrastructure are in place, direct origin exposure is addressed, and the listed end-to-end plus load tests pass.

## References

[1]: https://developers.cloudflare.com/network/ip-geolocation/ "Cloudflare — IP geolocation"
[2]: https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/adding-cloudfront-headers.html "AWS — Add CloudFront request headers"
[3]: https://laravel.com/docs/10.x/deployment "Laravel 10 — Deployment"
[4]: https://laravel.com/docs/13.x/releases "Laravel — Release notes and support policy"
[5]: https://laravel.com/docs/10.x/queues "Laravel 10 — Queues"
