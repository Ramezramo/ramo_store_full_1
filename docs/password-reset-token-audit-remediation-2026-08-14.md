# Password-reset Token Endpoint Audit Remediation

**Date:** 2026-08-14
**Repository:** `ramo_store_full_1`
**Scope:** Unauthenticated password-reset token generation and inactive API v2 route registration.

## Finding

The audit identified the unauthenticated `POST /api/user/generateTokenTesting` endpoint as an account-takeover risk. It accepted any existing user email, generated a valid password-reset token, and returned the plaintext token and reset URL in the JSON response. The endpoint was a testing helper and was not required by the supported password-recovery flow.

## Remediation

The `generateTokenTesting` route was removed from `routes/api/auth.php`, and the corresponding controller method was removed from `AuthController`. The supported `forgot-password` flow remains available and continues to use Laravel's password broker to deliver reset instructions without returning a reset token in the API response.

The inactive `routes/api2.php` file contained only commented-out legacy routes and was registered under `/api/v2`. Its registration was removed from `RouteServiceProvider`, and the dead file was deleted so future edits cannot accidentally activate an unreviewed route set.

## Regression coverage

`tests/Feature/CsrfAndApiAuthTest.php` now verifies that the password-reset token testing endpoint returns no route and that the inactive API v2 route file is not present. Existing tests continue to verify the supported forgot-password and reset-password behavior through their established routes.

## Validation

The focused security test class passed after the remediation:

```text
php artisan test --filter=CsrfAndApiAuthTest
PASS  9 tests (10 assertions)
```

PHP syntax checks for the modified controller, route provider, and authentication route file passed. Route inspection confirmed that neither `/api/user/generateTokenTesting` nor `/api/v2` has a registered route.

No customer data, credentials, reset tokens, session values, or other secrets are included in this record.

## Status

**Closed in code and regression-tested.** The supported email password-recovery flow remains available; the unsafe testing endpoint is no longer exposed.
