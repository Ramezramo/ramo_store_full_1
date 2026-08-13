# Ramo Store Retest Validation — 13 August 2026

## Scope and verdict

This validation repeated the public checks in the supplied comparison report after the current remediation changes and temporary HTTPS-safe runtime configuration were applied. The reported transport-security, generated-link, cookie, sitemap, debug-marker, and English search-relevance findings are now corrected on the public temporary storefront. The site is **still NO-GO for a production launch or real orders**, because the catalog has verified missing/mismatched media, Arabic customer content remains incomplete, and the required production infrastructure and staging load validation are not yet complete.[1]

## Retest comparison

| Finding from supplied retest | Post-fix evidence | Current disposition |
|---|---|---|
| HTTP served content directly | `http://…/health` returned **308 Permanent Redirect** to the canonical HTTPS endpoint. | **Resolved in the temporary public environment.** Production must retain an edge-level redirect as defense in depth. |
| Secure session and CSRF cookies missing | HTTPS response cookies for `rms_session`, `XSRF-TOKEN`, and `ramo_auth_flag` each included the `Secure` attribute. The session cookie remained `HttpOnly; SameSite=Lax`. | **Resolved in the temporary public environment.** |
| Homepage and sitemap used internal HTTP URLs | Homepage internal references to the public hostname over HTTP: **0**. Sitemap `<loc>http://` entries: **0**. | **Resolved.** |
| HSTS absent | HTTPS response included `Strict-Transport-Security: max-age=31536000; includeSubDomains`. | **Resolved for the temporary public HTTPS route.** |
| Debugbar indicators remained in HTML | Active Debugbar asset or payload markers: **0**. The obsolete inert selectors were removed from the shared customer layout. | **Resolved.** |
| `jeans` search returned a sneaker | Public `?q=jeans` response no longer contained **Men's Classic Sneakers**. | **Resolved.** |
| Public-catalog cache was `no-store, private` | Anonymous homepage now sends short shared-cache directives: `public, max-age=60, s-maxage=300, stale-while-revalidate=60`. Personalized pages retain no-store handling in automated coverage. | **Resolved in application policy; CDN configuration remains a deployment task.** |
| `X-Frame-Options` absent from public sample | The application adds `X-Frame-Options: SAMEORIGIN`, but the temporary public proxy did not expose it in its final response. | **External proxy/header-preservation gate.** Verify at the production edge; a tested CSP `frame-ancestors` policy is still pending. |
| Three gallery image paths returned 404 | Homepage has **3** local `image-gallery` references and all **3** returned failure status. | **Open P0 catalog/media gate.** Requires approved source assets or corrected merchant paths. |
| Blue jeans product shows a sneaker image | Existing catalog audit still identifies product 5 as an image-to-product mismatch. | **Open P0 catalog-data gate.** Do not infer or substitute a product image without merchant approval. |
| Arabic customer experience exposed English labels and colour values | The live RTL document is structurally correct, but some category labels and variation colours remain English. | **Open P1 localization/content gate.** |
| CSP not present | No Content-Security-Policy was added, by design, because it requires a staged allow-list for all verified scripts, fonts, payment resources, and media hosts. | **Open P1 staging-security gate.** |
| Heavy third-party media usage and failed external images | The current homepage continues to rely on externally hosted media and has unresolved broken assets. | **Open P1 media-migration gate.** |

## Application changes validated in this retest

The application now has an opt-in `FORCE_HTTPS` policy. When enabled after a proxy is explicitly trusted, it forces absolute URLs—such as canonical URLs, redirects, and sitemap entries—to HTTPS and returns a permanent redirect for plain HTTP requests. This remains opt-in so ordinary local HTTP development is not broken. The production environment template documents the required configuration.

The readable `ramo_auth_flag` cookie is still intentionally non-HttpOnly because the service worker uses it only to decide whether a public page cache is safe. It now follows the secure-session policy or request scheme and therefore receives the `Secure` attribute on HTTPS responses. The session cookie remains encrypted and HttpOnly.

The temporary public configuration has diagnostics disabled and uses only the immediate proxy for forwarded-scheme recognition. This configuration is intentionally untracked. A real deployment must use the exact CDN/load-balancer CIDR ranges rather than a broad proxy setting.

## Automated verification

The full Laravel suite passed after the change:

| Check | Result |
|---|---|
| Full application regression suite | **56 tests passed; 227 assertions** |
| Transport-security regression coverage | HTTPS URL generation, HTTP 308 redirect, and secure auth-state cookie all passed. |
| Security-header regression coverage | Direct HTTP, trusted HTTPS, and untrusted forwarded-header cases all passed. |
| Public live transport recheck | HTTP redirect, secure cookies, HSTS, canonical scheme, clean diagnostics, sitemap scheme, and jeans search all passed. |

## Remaining launch gates

The following items cannot be represented as fixed merely by a code change and keep the release decision at **NO-GO**:

| Gate | Required evidence before real traffic or orders |
|---|---|
| Catalog accuracy | Merchant-approved repair of the three missing gallery files, product 5 media mismatch, remaining broken customer images, and a repeat media-health audit with zero critical errors. |
| Legal/commercial readiness | Merchant or legal approval and publication of complete Privacy, Terms, Shipping, Returns, Payment, and Contact content. |
| Arabic completion | Egyptian-Arabic translations for remaining customer category labels, variation colours, validation messages, and customer-flow copy. Personal names must remain unmodified. |
| Production edge and headers | Exact trusted-proxy CIDRs, permanent edge HTTPS redirect, header preservation, and a tested CSP/frame-ancestors policy. |
| Production platform | Managed PostgreSQL and Redis, queue workers, real SMS provider, object storage/CDN migration, centralized logs, error tracking, and uptime alerting. |
| Capacity proof | Staging environment that mirrors production, monitoring, and a controlled progressive load test covering catalog, session, OTP, cart, search, checkout, and queue workloads. |

## References

[1]: ../../upload/RamoStore_Retest_Comparison_EN.md "Supplied RamoStore retest comparison"
