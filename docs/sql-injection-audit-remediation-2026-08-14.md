# SQL Injection Audit Remediation — 14 August 2026

## Scope and source

This record reconciles the user-supplied `SQL_INJECTION_AUDIT.md` against the current Laravel application. The audit examined raw SQL helper calls under `app/` and `scripts/`.

## Result

| Audit item | Current status | Evidence |
|---|---|---|
| Finding 1: interpolated PostgreSQL interval in storefront timeline activity | **Remediated** | `WebController` now uses `whereRaw('date_created > NOW() - (?::interval)', [$interval])`. The existing three-value allowlist remains in place. |
| Finding 2: review product identifier validation must precede query construction | **Verified and regression-tested** | `ReviewProductIdValidationTest` submits a malformed non-integer identifier to both the authenticated web and API review endpoints. Both requests fail validation and create no review. |
| Finding 3: prevent unsafe raw-SQL interpolation from returning | **Implemented** | `scripts/check_raw_sql_interpolation.php` scans raw-SQL helper calls for PHP-variable interpolation. `.github/workflows/raw-sql-safety.yml` executes it for pull requests and pushes to `main`. |

## Verification performed

The repository guardrail passed locally. The audit's specified raw-SQL grep sweep was also rerun after the change and found no direct PHP-variable interpolation in the reviewed raw-SQL helper calls. The complete Laravel suite passed with **68 tests and 282 assertions**.

## Residual risk

The original interval source was not request-controlled: it is derived from the timeline section's fixed `24h`, `7d`, and `month` mapping. Binding is nevertheless now mandatory behavior, so a future extension of that value cannot silently become an injection risk.

The repository scanner is intentionally conservative. It rejects direct variable interpolation inside a SQL-string argument; developers must use placeholders and binding arrays for query values. Security review remains required for identifier construction, dynamic schema names, and new raw SQL patterns that cannot be parameterized.

## Release impact

This remediation closes the audit's one confirmed code-hardening finding and adds durable coverage. It does not alter the existing external production launch gates documented in `release-readiness-report.md`.
