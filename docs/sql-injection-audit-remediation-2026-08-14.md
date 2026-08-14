# SQL Injection Audit Remediation — 14 August 2026

## Scope and source

This record reconciles the revised, user-supplied `SQL_INJECTION_AUDIT.md` against the Laravel application at the time of remediation. The revised review identified **no exploitable SQL-injection vulnerability**. It reported four defense-in-depth and code-hygiene actions affecting raw-SQL scanning, a legacy native-PHP endpoint, duplicated native API code, and continuous integration.

The assessment and remediation cover Laravel code in `app/`, project scripts in `scripts/`, and native-PHP compatibility code in `routes/ramo-native-php/`. No customer data, credentials, session material, or one-time passcodes are included in this record.

## Reconciliation and completed actions

| Revised audit item | Status | Completed remediation and evidence |
|---|---|---|
| Extend the raw-SQL guardrail to route code and native database sinks | **Implemented** | `scripts/check_raw_sql_interpolation.php` now scans `app/`, `routes/`, and `scripts/`. It detects PHP-variable interpolation passed directly to Laravel raw-SQL helpers, PDO `query`/`exec`, `pg_query`, and `mysqli_query`. |
| Confirm the guardrail runs in continuous integration | **Verified** | `.github/workflows/raw-sql-safety.yml` already runs the scanner on pushes and pull requests targeting `main`. No workflow change was required for this revised audit item. |
| Remove `routes/ramo-native-php/products/exec.php` | **Removed** | The endpoint was dead code: it assigned an empty SQL string and exposed an unauthenticated `$con->query($sql)` execution path. It has been deleted rather than retained behind a route gate. |
| Consolidate duplicated native API implementations | **Implemented** | The 13 byte-identical legacy files below are now small backward-compatible wrappers that require their canonical counterpart in `routes/ramo-native-php/v1/`. This preserves the legacy URLs while ensuring that future security updates have one implementation to maintain. |

The consolidated legacy paths are `brands.php`, `categories.php`, `constants/product_arrays.php`, `countries.php`, `currency-rates.php`, `new-api-rules.php`, `prodduct-current-price.php`, `products-get.php`, `serveraouth/server-confirmation.php`, `serveraouth/token-operations.php`, `serveraouth/token-validation.php`, `serveraouth/update-usage-times.php`, and `tags.php` under `routes/ramo-native-php/products/get-products/v4/products/`.

## Earlier audit findings retained as remediated

| Earlier audit item | Status | Evidence |
|---|---|---|
| Interpolated PostgreSQL interval in storefront timeline activity | **Remediated** | `WebController` uses `whereRaw('date_created > NOW() - (?::interval)', [$interval])`; the three-value interval allowlist remains in place. |
| Review product identifier validation must occur before query construction | **Verified and regression-tested** | `ReviewProductIdValidationTest` submits malformed non-integer identifiers to authenticated web and API review endpoints. Both fail validation and create no review. |
| Prevent unsafe raw-SQL interpolation from returning | **Implemented and expanded** | The scanner is now broader than the original control and is executed by the GitHub Actions workflow described above. |

## Verification performed

| Check | Result |
|---|---|
| PHP syntax check of the expanded scanner | Passed. |
| Controlled scanner regression exercise | Passed. A temporary, uncommitted route fixture with direct interpolation in Laravel, PDO `query`, PDO `exec`, `pg_query`, and `mysqli_query` calls was rejected at all five expected source lines. The fixture was removed before the clean scan. |
| Clean repository raw-SQL scan | Passed with no direct PHP-variable interpolation found in the scanned code. |
| Syntax check of all legacy wrapper files | Passed. |
| Laravel automated test suite | Passed: **68 tests, 282 assertions**. |

## Residual risk and operating guidance

The scanner is a preventive static control, not a complete substitute for security review. It rejects direct PHP-variable interpolation where a SQL literal is passed to supported raw-query sinks. Developers must use prepared statements and bound parameters for values. Security review is still required when code composes SQL identifiers, table names, sort columns, or other query structure that database drivers cannot parameterize.

The native-PHP layer consistently uses PDO prepared statements with emulated prepares disabled in the reviewed routes. Consolidating the duplicate files reduces configuration drift and lowers the chance that a future security change is applied to only one copy.

## Release impact

This revised audit does **not** identify an exploitable SQL-injection issue and its hardening actions are complete. The SQL-injection audit is therefore no longer a blocker. The overall launch decision remains **NO-GO** until the separate production-readiness gates documented in `release-readiness-report.md` are completed; this change does not publish the application or accept real orders.

## Follow-up audit: `SQL_INJECTION_AUDIT_REPORT.md`

A further user-supplied audit was reconciled against commit `1137a8c`. It confirmed that no exploitable classic SQL injection exists in the Laravel or native-PHP code. Its actionable findings were defense-in-depth measures, which are now complete as follows.

| Follow-up audit item | Status | Remediation and evidence |
|---|---|---|
| Do not return raw database exception text from the native app-configuration endpoint | **Remediated** | `routes/ramo-native-php/config/app-config.php` now records the PDO exception through `error_log()` and returns only the existing generic JSON error message to the client. |
| Exercise injection-sensitive endpoints with automated tests | **Implemented** | `SqlInjectionRegressionTest` submits four representative SQLi payloads through the public search and shop query surfaces (`q`, `category`, sort, price, brand, and search inputs). Responses remain successful and do not contain common SQL/PDO error markers. The test also guards the native error-redaction and raw-SQL scanner coverage. |
| Remove remaining duplicate native database bootstrap code | **Remediated** | The final duplicate `v4` `serveraouth/connectfile.php` is now a compatibility wrapper requiring the canonical `v1/serveraouth/connectfile.php`. The remaining `get-app-startup-config.php` is a distinct legacy endpoint with a different table and response contract, so it was intentionally not replaced by an incompatible wrapper. |
| Preserve safe sort whitelists in future code review | **Documented** | `docs/security-code-review-checklist.md` specifies the allowlist/mapping pattern required for dynamic sort columns and directions, and prohibits raw request values in query identifiers. |

During the payload exercise, the search page exposed a separate robustness issue: a non-numeric `category` value was correctly bound, but PostgreSQL rejected it when comparing against the numeric category column and returned a 500 response. `SearchController` now parses that filter as a positive integer before applying it. Invalid values are ignored safely, preserving the normal category filter for valid identifiers and avoiding an unnecessary database error.

### Follow-up verification

| Check | Result |
|---|---|
| Changed PHP syntax checks | Passed. |
| Raw SQL interpolation guardrail | Passed. |
| Focused SQL-injection and search regression tests | Passed: **7 tests, 57 assertions**. |
| Complete Laravel test suite | Passed: **72 tests, 329 assertions**. |

The follow-up audit does not change the overall release decision. It confirms the SQL-injection review is complete, while the separate production-readiness gates in `release-readiness-report.md` continue to govern whether the storefront can accept real orders.

## Validation of additional audit report

A subsequent user-supplied `SQL_INJECTION_AUDIT_REPORT.md` was tested against the current main branch without deploying the application. The report again found **no exploitable SQL injection**. Its requested preventive-control work was reconciled as follows.

| Audit request | Status | Evidence |
|---|---|---|
| Execute the raw-SQL guard for real | **Passed** | `composer run-script check-sql` runs `php scripts/check_raw_sql_interpolation.php` and returned `Raw-SQL interpolation check passed.` |
| Ensure the guard runs in CI | **Already implemented** | `.github/workflows/raw-sql-safety.yml` runs the same guard on pull requests and pushes to `main`. |
| Scan public native-PHP mirror files | **Implemented** | The scanner now covers `app/`, `public/`, `routes/`, and `scripts/`. No PHP files currently exist under `lib/`, so no additional PHP scan root was required. |
| Provide a stable developer command | **Implemented** | `composer.json` now exposes `composer run-script check-sql`. |
| Preserve safe dynamic-sort construction | **Already documented** | `docs/security-code-review-checklist.md` requires fixed allowlists or mappings for dynamic identifiers. |

The audit’s LIKE-wildcard observation remains a **low-priority search-semantics hardening opportunity**, not an SQL-injection vulnerability. It was intentionally not changed during this test-only request. The audit’s database-export observation is out of SQL-injection scope and should be handled as a separate data-hygiene review without inspecting or exposing any possible customer data.

### Additional validation

| Check | Result |
|---|---|
| `composer validate --no-check-publish` | Passed. |
| `composer run-script check-sql` | Passed. |
| SQL-injection regression feature tests | Passed: **4 tests, 50 assertions**. |
| Complete Laravel test suite | Passed: **72 tests, 332 assertions**. |

No permanent deployment, production publication, or real-order activation was performed.

## Reconciliation — latest static SQL audit

### Conclusion

The latest user-supplied static audit reviewed dynamic `orderBy()` calls, bound `whereRaw()` values, CLI maintenance scripts, and remaining raw SQL helpers. It found **no confirmed exploitable SQL injection**. The coupon listing’s dynamic sort column and direction are allowlisted before they reach the query builder. The review purchase query continues to use a bound placeholder; its `product_id` value is now explicitly cast to an integer for clarity.

### Completed preventive controls

| Finding | Verified status and completed action |
|---|---|
| Coupon dynamic sorting | `sort_by` remains restricted to `Coupon::getFillable()` and `sort_dir` to `asc` or `desc`. A route-level regression test now proves SQL-like payloads in both parameters return HTTP 422 before query construction. The endpoint now handles its validation exception explicitly, so rejected input cannot be misreported as a 500 response. |
| Other dynamic ordering | The remaining `orderBy($orderCol, ...)` cases map application configuration values through closed `match` expressions. The only commented legacy occurrence is not executable. |
| Bound review lookup | The `whereRaw()` SQL text remains static and the search term is bound through `?`; `product_id` is now cast to `(int)` before being incorporated into the bound value. |
| Maintenance scripts | `sync_postgres_sequences.php`, `audit_database_ownership.php`, and `audit_production_indexes.php` have no references outside `scripts/`. They are CLI maintenance utilities and are not registered in application routes. |
| Raw SQL CI guard | The existing guardrail now also detects a variable on the left side of a concatenated `Raw()` SQL argument. A controlled, non-executed fixture was detected at the expected path and the clean repository subsequently passed. |

### Validation evidence

PHP syntax checks passed for the changed controllers and guard script. The focused coupon sorting and SQL-injection suites passed with **6 tests and 54 assertions**. The complete Laravel suite passed with **79 tests and 357 assertions**, and `composer run-script check-sql` passed in the final clean state.

No deployment or production-order activation was performed as part of this audit work.
