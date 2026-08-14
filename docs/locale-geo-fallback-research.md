# Locale geolocation fallback research

## Verified deployment constraint

The current preview URL does not forward any of the supported trusted edge country headers (`CF-IPCountry`, `CloudFront-Viewer-Country`, `X-Vercel-IP-Country`, or `X-AppEngine-Country`) to Laravel. In that environment, the server-side middleware cannot infer a visitor’s country and therefore correctly uses its English fallback. This is a hosting-header limitation, rather than an application-level IP parsing failure.

## Privacy-preserving fallback

For a **first visit only** that lacks a trusted edge header, the customer layout requests `https://api.country.is/` from the visitor’s browser. The provider’s root endpoint returns the country of the caller’s IP address as an ISO two-letter country code; the browser submits only that two-letter code to the same-origin Laravel endpoint. The IP address returned by the provider is neither read nor sent to Ramo Store. Country accepts commercial use without a key, reports no quotas, and documents a 10-request-per-second infrastructure limit per IP. [1]

The endpoint accepts only exactly two alphabetic characters and applies the result only when the session is marked `fallback_pending`. Requests with a trusted country header are never replaced, and every language choice made with the existing language selector is marked `manual` and remains authoritative. Sessions created before locale-source tracking are allowed one migration lookup because they may have received the former English fallback solely due to the missing preview-host header.

## Production recommendation

A permanent production deployment should provide a trusted edge country header. In that case, the browser fallback is not called. If a critical production deployment cannot guarantee this header, self-hosting the documented open-source country service or using a contracted geolocation provider should be evaluated as part of the remaining production-readiness work. [1]

## References

[1]: https://country.is/ "Country — free IP-to-country geolocation API"
