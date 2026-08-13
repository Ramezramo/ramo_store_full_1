# Third Retest Intake — 13 August 2026

## Source

The user supplied `RamoStore_Third_Retest_Comparison_EN.md`, dated 13 August 2026, for the temporary public deployment at `https://5000-iyqms9tcbk0ie59dd61nq-a577ee3b.sg1.manus.computer/`.

## Reported resolved findings

The report observed a permanent HTTP-to-HTTPS redirect, HSTS, secure session/CSRF/auth-state cookie attributes, HTTPS internal and sitemap URLs, no public Debugbar markers, and a route-aware split between short public catalog caching and private shopper-page no-store caching.

## Reported unresolved findings to reproduce

| Priority | Finding |
|---|---|
| P0 | Three local gallery assets remain referenced and return 404. |
| P0 | Product 5 remains semantically jeans content paired with a sneaker image. |
| P1 | A `jeans` search reportedly includes an unrelated men's sneaker. |
| P1 | Arabic customer filters/categories/colour labels remain partially English. |
| P1 | A Jeans category image fails to render; external media dependencies remain. |
| P1 | CSP and externally preserved frame protection are not observed. |
| Release gate | No controlled load test demonstrates 10,000-user capacity. |

## Reconciliation note

The immediately preceding live check did not return the sneaker for `/search?q=jeans`, whereas the supplied third retest reports the opposite. The next verification must test the exact request, locale, cache variant, and response body before treating it as a new regression.

## Reference

[1]: ../../upload/RamoStore_Third_Retest_Comparison_EN.md "User-supplied third retest comparison"
