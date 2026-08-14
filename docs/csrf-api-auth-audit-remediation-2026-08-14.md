# CSRF & API Auth-Guard Audit Remediation

**Date:** 2026-08-14
**Repository:** `ramo_store_full_1`
**Scope:** CSRF login protection, admin API authorization, and API logout method safety.

## Findings addressed

### 1. Login CSRF exclusions removed

The `login`, `vendor-login`, and `admin/login` paths were removed from `VerifyCsrfToken::$except`. The corresponding customer, vendor, and administrator forms already included `@csrf`, so the existing forms now receive effective CSRF verification.

### 2. Route-level API admin authorization added

A JSON-safe `AdminAuthApi` middleware was added and registered as `admin.auth.api`. It returns `401` for unauthenticated requests and `403` for authenticated non-admin users without redirecting to a web login page. The middleware was applied to `POST /api/ramo/config-storing`. The existing controller-level admin check remains in place as defense in depth.

### 3. API logout changed from GET to POST

`POST /api/user/logout` is now the only registered Laravel API logout route. The checked-in API guide and interactive request example were updated from GET to POST. No application client call site using the old API route was present in the repository.

## Regression coverage

`tests/Feature/CsrfAndApiAuthTest.php` covers:

- Customer login rejects a missing CSRF token.
- Vendor login rejects a missing CSRF token.
- Administrator login rejects a missing CSRF token.
- Configuration upload requires authentication.
- Configuration upload requires the admin role at the route boundary.
- An admin request reaches controller validation after passing the route middleware.
- API logout rejects GET with HTTP 405.

## Validation

- Focused suite: **7 passed, 8 assertions**.
- Full Laravel suite: pending final run after documentation and staged-diff validation.
- Raw-SQL guardrail: pending final run.
- No secrets, tokens, OTP values, or production customer data are included in this record.
