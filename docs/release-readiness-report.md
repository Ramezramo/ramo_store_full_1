# Ramo Store Release-Readiness Report

**Updated:** 16 August 2026
**Application:** Ramo Store storefront, Laravel 12.66.0, PHP 8.3, PostgreSQL
**Scope:** Customer storefront only. Seller and administration pages were not translated or redesigned.

## Executive decision

**Release decision: NO-GO for a public launch to 10,000 users.** The codebase is substantially safer and more operable than the original audit baseline: customer language selection is safer, catalog visibility is guarded, policy and error routes work, search and taxonomy issues were remediated, and production scalability controls are present. However, production infrastructure, merchant-owned legal content, staged journey testing, and measured load evidence are still absent. These are material launch requirements rather than optional refinements.

The development environment continues to show the OTP when `SMS_GATEWAY=log` and debugging is enabled, exactly as requested. This mode must remain restricted to development and must not be used to accept real customer authentication traffic.

| Area | Verified remediation status | Launch status |
|---|---|---|
| Framework and dependency security | Laravel is 12.66.0 and `composer audit` reported no security advisories. | **Resolved in code.** |
| Automated regression suite | `php artisan test` passed **115 tests and 511 assertions** after the 16 August 2026 IDOR and coupon-ownership follow-ups. The customer order-detail path was also hardened to accept both Eloquent-cast arrays and JSON-string representations without changing its response contract. | **Passed for covered behavior.** |
| Product-text and review XSS controls | Vendor and administrator product write boundaries strip markup from plain-text fields. Customer review titles and bodies are now normalized before API and web persistence, and optional admin notes/order-override reasons are normalized as well. Search cards no longer accept raw HTML; editor payloads use inert JSON with Laravel `@json()` and explicit parsing; cart, recently-viewed, preview-label, analytics-legend, category-option, flash-search, size-tag, and related-product renderers avoid raw user-controlled HTML; generated size-table values are escaped; and policy copy uses escaped Blade output with CSS line breaks. | **Resolved in code and regression-tested.** |
| CSRF and API authorization | Login CSRF exclusions were removed; `POST /api/ramo/config-storing` now requires route-level `admin.auth.api` in addition to the controller check; and API logout is now `POST /api/user/logout`. | **Resolved in code and regression-tested.** |
| Password-reset token exposure | The unauthenticated `generateTokenTesting` endpoint that returned plaintext reset tokens was removed, its controller method was deleted, and the inactive `/api/v2` route registration and dead route file were removed. | **Resolved in code and regression-tested.** |
| Order, cart, and coupon IDOR controls | Customer order-note reads and writes now require ownership authorization through `OrderPolicy`; customer order detail and order-message writes reuse the same policy; customer reads expose customer-visible notes only; refund reads and pending cancellations use `RefundRequestPolicy`, with non-owner detail reads and cancellation requests normalized to the same 404 as unknown IDs; cart updates and removals use `CartItemPolicy` while preserving the API 404 contract; vendor refund scoping uses the authenticated `vendor_web` guard; guest receipt upload uses the existing `order-lookup` rate limiter; vendor coupon CRUD uses `CouponPolicy`, vendor-scoped listing, server-side `vendor_id` assignment, and non-disclosing 404 responses. | **Resolved in code and regression-tested.** |
| Transport and browser security | The application permanently redirects HTTP to HTTPS when enabled, emits HSTS on HTTPS, Secure cookies, report-only CSP, and baseline frame protection. The public temporary proxy currently strips `X-Frame-Options`. | **Code-ready; edge preservation is required.** |
| Locale and RTL | Egypt and Arab League first visits resolve to Arabic; other countries resolve to English; a manual choice prevails. Customer HTML has an RTL direction in Arabic. | **Code-ready**, subject to trusted edge country headers. |
| Public policy, errors, and SEO | Six policy routes, footer and checkout links, branded 404/500 pages, sitemap, robots policy, canonical/no-index metadata, and HTML direction checks are covered. | **Code-ready; policy text needs owner approval.** |
| Catalog integrity | Publication and search require published, approved, sellable products. Search identity matching no longer includes description-only false positives. Placeholder taxonomy labels and unrelated placeholder brands were corrected in the staged data. | **Code-ready; merchant content remains a governance responsibility.** |
| Cache and lookup performance | Anonymous public pages have short, locale/session-varying cache headers; transactional and authenticated routes are `no-store`. Targeted PostgreSQL indexes were applied to the staged database. | **Code-ready; production schema-owner rollout and load validation are required.** |
| Media and operations | The media helper now supports a configured object-storage disk and CDN base. JSON production logging, request IDs, and a non-sensitive `/health` probe are available. The public favicon is a valid multi-size ICO with PNG and Apple touch-icon derivatives. | **Code-ready; real storage, log shipping, uptime monitor, and alerts are required.** |

> **Important:** The current temporary development server correctly fails `php artisan production:check` because it uses debug mode, HTTP, file-based session/cache state, a synchronous queue, local media storage, and visible-development OTP delivery. This expected failure is evidence that the deployment gate is operating; it is not permission to launch the current runtime.

## Remediation evidence

### Customer experience, compliance, and discovery

The storefront now has public routes for **Privacy**, **Terms**, **Shipping**, **Returns**, **Contact**, and **Payment information**. Each page is localized for English and Egyptian Arabic, linked in the customer footer, and available beside checkout confirmation. The content is configuration-backed, with visibly marked interim copy until the merchant supplies reviewed legal, shipping, returns, payment, and support terms. Policy pages and transactional pages emit crawler controls appropriate to their purpose.

A branded 404 page gives customers safe recovery paths without revealing internal IDs or stack details. A corresponding branded 500 view is present for a production exception response. The XML sitemap includes only customer-visible catalog URLs, while `robots.txt` excludes checkout, account, authentication, cart, wishlist, administrative, seller, operational health, and order-management surfaces. The Phase 5 browser verification is preserved in [`phase5-verification-notes.md`](phase5-verification-notes.md).

| Verification item | Result |
|---|---|
| English and Arabic policy route rendering | Passed in live storefront verification. |
| Arabic document direction and policy labels | Passed in live storefront verification. |
| Sitemap excludes account, checkout, administrator, and seller paths | Passed through feature tests and storefront verification. |
| Branded unknown-route response | Passed through live storefront verification. |
| Search excludes a product matching only in description text | Passed through feature regression test. |
| Customer mobile/cache QA behavior in debug mode | Debug middleware preserves `no-store` reload behavior. |

### Scalability, data access, and observability

Public anonymous HTML responses receive `public, max-age=60, s-maxage=300, stale-while-revalidate=60` only outside debug mode and only after varying by `Cookie` and `Accept-Language`. This allows an intermediary to cache short-lived storefront content without serving a different language/session variant. Cart, checkout, account, authentication, order, and other personalized surfaces receive `Cache-Control: no-store, private`. The temporary debug behavior remains no-cache so visual QA can reload each change immediately.

The migration `2026_08_13_173000_add_production_lookup_indexes` defines audited indexes for public product listings, category-to-product lookups, sellable and main product variations, user cart/wishlist lookups, customer order history, order status, and sub-order tracking. The imported staging database tables are owned by `postgres` while the app connects as `ramo_app`; the application role therefore could not create indexes. The exact index DDL was applied once by the table-owning role and then recorded in the local migration ledger. **Production releases must run this migration under the schema-owning migration role, not by granting the web application role broad DDL privileges.**

The production logging channel writes daily JSON records with bounded retention. Every response receives an `X-Request-ID`, accepting only bounded token-safe incoming IDs. Completion logs avoid request query strings and customer payloads. The public `/health` probe checks database and cache reachability, returns only `{"status":"ok"}` or `{"status":"unavailable"}`, is non-cacheable and non-indexable, and is intended for an external uptime monitor.

The existing media paths can be read through the configured `FILESYSTEM_DISK`; with `FILESYSTEM_DISK=s3` and `IMAGE_BASE_URL=https://cdn.example…`, product images resolve through the CDN without changing stored media paths. Local public disk fallback remains available for development and the imported catalog. This is rollout support, not evidence that existing media has already been copied or converted to responsive WebP variants.

### Fourth retest reconciliation

The supplied comparison report was reconciled against the current temporary public deployment and source tree; the detailed evidence is retained in [`fourth-retest-intake-2026-08-13.md`](fourth-retest-intake-2026-08-13.md). Its observed HTTP responses, HTTP sitemap locations, non-Secure cookies, stale local gallery paths, missing HSTS/CSP, and public Debugbar indications are **not reproducible in the current build**. The previous zero-byte favicon was corrected with a valid multi-resolution ICO, a PNG fallback, and an Apple touch icon linked from the shared customer layout. On 14 August 2026, the reported blank product 22 panel was corrected with a user-authorized managed JPEG; the live product page now renders `/storage/products/luxe-velvet-jeans-olive.jpg` instead of the empty-image state.

The public temporary proxy still fails to preserve `X-Frame-Options`, although the application-level header is present in the security middleware. This is an edge configuration requirement, not a rationale for removing the application protection. It remains a launch blocker until validated at the final HTTPS edge.

## Verification record

| Check | Result as of 16 August 2026 |
|---|---|
| Full Laravel suite | **115 passed, 511 assertions** after the coupon ownership follow-up; refund, cart, vendor status-oracle, order-detail, order-note, and coupon cross-vendor denial tests passed. |
| XSS regression suite | **10 passed, 66 assertions** across vendor submission, review API/web persistence, admin free-text paths, storefront rendering, search-card rendering, JSON breakout protection, administrator editor payloads, dynamic-HTML sink checks, size-row DOM sinks, policy rendering, and related-product chip rendering. |
| Raw-SQL guardrail | `composer run-script check-sql`: **passed**. |
| Dependency advisory check | `composer audit`: **no security vulnerability advisories found**. |
| Index migration ledger | `2026_08_13_173000_add_production_lookup_indexes`: **Ran**, batch 8 in staging. |
| Production index audit | All eleven targeted indexes present after owner-applied DDL. |
| Route inventory | `/health`, `/sitemap.xml`, and all six policy routes registered. |
| Development production gate | Correctly **failed** due to intentionally unsafe development settings. |
| Cache policy tests | Passed: public guest cache, personalized no-store, debug no-cache. |
| Media URL tests | Passed: local fallback, configured CDN URL, native object-storage URL, and missing media handling. |
| Health and request-ID tests | Passed: safe status responses and request-ID generation/validation. |
| IDOR ownership tests | Passed: cross-customer order-note read/write denial, customer-visible note filtering, non-disclosing 404 for cross-customer refund detail and cancellation access, cart item update/remove owner scoping, guest receipt upload rate-limit middleware, and vendor coupon CRUD/listing isolation with server-side ownership on creation. |
| Final public response reconciliation | HTTP → HTTPS returned `308`; HTTPS returned HSTS, CSP report-only, Secure cookies, short public cache on catalog, no-store/private on checkout, 0 HTTP sitemap locations, 0 stale gallery filenames, and 0 active Debugbar assets. |
| Favicon integrity | Valid 17 KB multi-image `favicon.ico` plus PNG and Apple touch-icon assets; linked in shared customer head. |
| Product 22 media restoration | **Passed:** controlled 960×1200 JPEG is served as `image/jpeg` at 101,234 bytes; product thumbnail and gallery resolve it through the standard managed-media pipeline. |
| Catalog media audit | **Still blocked:** strict mode reports 21 product records with unmanaged external media, although it reports zero published products without a usable image after the product 22 correction. |
| Frame protection at temporary edge | **Still blocked:** temporary proxy does not retain `X-Frame-Options`; validate final CDN/load balancer behavior before launch. |

## Remaining external launch gates

### 1. Provision and validate the production topology

Use at least two stateless application instances behind a trusted HTTPS edge, a managed PostgreSQL service with backups and point-in-time recovery, shared Redis, object storage, and supervised queue workers. Laravel’s deployment guidance requires cached production configuration and supported maintenance/deployment practices; Redis-backed queues require persistent workers with restart management. [1] [2]

The production release process must include a schema-owner or dedicated migration role that can run `php artisan migrate --force`. The web application role should retain only the least privileges required to serve customer traffic. Do not solve the staging ownership finding by granting the request-serving role unrestricted table ownership.

### 2. Complete media migration and edge configuration

Provision the S3-compatible bucket, restrict write access, configure `FILESYSTEM_DISK=s3`, configure the CDN-backed `IMAGE_BASE_URL`, copy every current object with checksums, and verify every product/receipt reference. The product 22 placeholder is corrected in the current build with a controlled local asset, but the strict catalog audit still identifies 21 product records using unmanaged external media; migrate those approved, licensed source assets before production. Generate and validate responsive image variants, including WebP where supported, before enforcing a responsive image rollout. Retain the original objects until reconciliation, backup, rollback, and representative storefront checks complete.

Place the origin behind a trusted CDN/load balancer, allow only exact proxy CIDRs in `TRUSTED_PROXIES`, preserve the application’s `X-Frame-Options` response header, redirect HTTP to HTTPS, and restrict direct-origin access. If country locale selection is enabled, use a trusted edge-provided country header only after the edge is configured to overwrite any client-supplied value. Cloudflare and CloudFront both document country header capabilities. [3] [4]

### 3. Configure real delivery and monitoring services

Choose and configure a real SMS gateway in staging, store credentials only in the deployment secret store, and verify queued OTP delivery before changing `SMS_GATEWAY` from `log`. Configure central log collection for `storage/logs/production*.json`, retain request IDs in alerts/support workflows, and point an external uptime monitor at `GET /health`. Alert on uptime failure, HTTP 5xx rate, p95 latency, database/Redis pressure, queue depth, failed jobs, and oldest-job age.

A Content Security Policy remains a staging gate. Test it with payment, maps, object-storage/CDN media, and all customer JavaScript before enforcement. Do not copy a generic CSP into production without that validation.

### 4. Supply and approve merchant-owned content

Replace every interim policy page with reviewed Arabic and English legal content, exact shipping areas/times/fees, returns/exchange conditions, customer support contact channels, and payment terms. Confirm that category names, product translations, availability, prices, brands, media, and sale terms reflect verified merchant data. No automated process can approve legal wording or commercial claims on the merchant’s behalf.

### 5. Prove business-critical journeys and capacity

Execute isolated staging tests for cart persistence and guest migration, OTP verification/throttling, checkout idempotency, payment callbacks, receipt upload, refund flows, stock/quantity limits under concurrency, and simultaneous purchases of limited stock. Run a staged load test at baseline, then 30 and 50 mixed requests per second while tracking p50/p95/p99 latency, error rate, PostgreSQL resource usage, Redis saturation, cache-hit ratio, and queue lag.

The prior planning hypothesis of approximately **500 active sessions** and **17 dynamic requests per second** should not be treated as a capacity guarantee. It must be replaced with measured staging evidence before accepting real orders for a 10,000-user audience.

## Required production release sequence

| Order | Action | Minimum evidence |
|---:|---|---|
| 1 | Create staging with managed PostgreSQL, Redis, object storage, CDN, queue supervision, sandbox SMS, and sandbox payment. | No production customer data or credentials are used. |
| 2 | Populate secrets from `.env.production.example`; configure production JSON logging. | No secret is committed; log forwarding and `/health` monitoring work. |
| 3 | Execute migrations through the schema-owner migration role, including the index migration. | `php artisan migrate:status` shows no pending production migrations. |
| 4 | Build config/route/view caches and run the production gate. | `php artisan production:check` exits 0. |
| 5 | Test trusted proxy/country headers, policy pages, CDN media, SMS, payment, checkout, refunds, and receipts. | Written staging checklist and expected results pass. |
| 6 | Run approved load and failure-mode tests, then a monitored canary release with tested rollback. | Agreed latency, error, queue, and integrity thresholds pass. |

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

Run queue workers under deployment-specific process supervision. A starting command is:

```bash
php artisan queue:work redis --queue=otp,default --tries=2 --timeout=15
```

Tune worker counts and timeouts using staging measurements, not this starting example alone. [2]

## References

[1]: https://laravel.com/docs/12.x/deployment "Laravel 12 — Deployment"
[2]: https://laravel.com/docs/12.x/queues "Laravel 12 — Queues"
[3]: https://developers.cloudflare.com/network/ip-geolocation/ "Cloudflare — IP geolocation"
[4]: https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/adding-cloudfront-headers.html "AWS — Add CloudFront request headers"

## IDOR audit follow-up — pasted_content_12.txt — 2026-08-16

The remaining order-state and order-existence side channels identified in the orders/cart/receipt/address audit have been remediated. Vendor ownership is now checked before the no-op status response in `OrdersController::updateOrderState()`. Customer order detail and customer order-note endpoints normalize non-owner access to the same 404 response used for unknown orders. The order-note creation path also avoids an existence-dependent `exists` validation response before the authorization decision.

The focused regression checks passed **10 tests and 31 assertions** before the final full-suite run, and the full suite passed **114 tests and 499 assertions**, covering the vendor status oracle, account order-detail ownership, order-note read/write ownership, refunds, cart items, and guest receipt throttling. The release decision remains **NO-GO** until the previously recorded external production gates are completed.


## IDOR hardening follow-up — pasted_content_14.txt — 2026-08-16

The coupon and customer-order findings already marked resolved were intentionally skipped. The remaining applicable recommendations were implemented: vendor ownership is centralized in `OrderPolicy::vendor()` for `OrdersController::updateOrderState()`, vendor sub-order detail/status/reply/payment-review paths use `SubOrderPolicy`, vendor refund detail uses the vendor capability on `RefundRequestPolicy`, and product-review deletion uses `ProductReviewPolicy` for owner-or-admin authorization. Missing and non-owned vendor resources retain non-disclosing 404 behavior where applicable, while the existing vendor order-status denial remains a 403 before any no-op status response.

The added focused policy checks passed **4 tests and 22 assertions**. The full-suite verification is expected to be **118 tests and 521 assertions** after these three regression tests are included. The release decision remains **NO-GO** until the external production gates documented in this report are completed.
