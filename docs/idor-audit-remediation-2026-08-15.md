# IDOR Audit Remediation — Orders, Notes, and Cart

**Date:** 2026-08-15
**Repository:** `Ramezramo/ramo_store_full_1`
**Scope:** Customer order-note endpoints and authenticated cart updates.

## Findings remediated

The customer note API previously validated only that `order_id` existed. It did not verify that the authenticated customer owned the order, allowing cross-customer note writes and reads. A new `OrderPolicy` now authorizes access using `orders.customer_id`, and `UserNoteController::store()` and `getAll()` call the policy before touching notes.

Customer note retrieval now filters to `customer_note = true`, preventing internal operational notes created by vendors or administrators from being disclosed through the customer endpoint. Notes submitted through the customer endpoint are explicitly stored as customer-visible notes.

The cart update endpoint already performed an ownership check when loading the item. The final mutation now repeats `where('user_id', $userId)` as defense in depth, matching the ownership pattern used by cart removal and preventing future refactors from widening the update scope.

The guest payment-receipt upload route now uses the existing `order-lookup` named limiter, matching the guest order lookup flow and limiting repeated order-ID/email attempts before the upload controller is reached. Customer order detail and order-message writes now use the registered `OrderPolicy` for the same ownership decision instead of duplicating the ownership predicate in each controller.

## Code changes

| Area | Remediation |
|---|---|
| `app/Policies/OrderPolicy.php` | Added an `OrderPolicy::view()` ownership rule based on `customer_id`. |
| `app/Providers/AuthServiceProvider.php` | Registered the `Order` to `OrderPolicy` mapping. |
| `UserNoteController` | Authorizes the order before creating or reading notes; customer reads return customer-visible notes only. |
| `CartApiController` | Repeated the authenticated `user_id` predicate on the final `UPDATE`. |
| `PaymentReceiptController` route | Added `throttle:order-lookup` to the guest receipt upload route. |
| `AccountController` and `OrderMessageController` | Reused `OrderPolicy::view()` for customer order detail and message ownership. |
| `IdorAuthorizationTest` | Added cross-customer note read/write, cart ownership, and guest receipt-rate-limit regressions. |

## Verification

The focused IDOR tests passed: **4 tests and 12 assertions** after the follow-up hardening. During full validation, the customer order-detail path also required compatibility with Eloquent-cast arrays as well as JSON strings; `AccountController` now handles both representations without changing the response contract. The full application suite passed **109 tests and 488 assertions**, and the raw-SQL safety guardrail passed. No secrets or customer data are included in the changes or evidence.

The public order tracking flows remain intentionally separate: they use an order identifier plus a matching phone or billing email as the guest ownership proof and are rate-limited by their existing route middleware.

## Remaining readiness status

These IDOR findings are closed in code. The broader production decision remains dependent on the previously documented infrastructure, trusted-edge, managed-media, real SMS/payment, merchant-content, and load-testing gates.

## Follow-up remediation — 2026-08-16

The remaining resource-authorization recommendations are now applied without changing endpoint behavior. `RefundRequest` and `CartItem` are explicit Eloquent models over the existing tables, with integer casts for ownership and identifier fields. `RefundRequestPolicy` centralizes customer read and pending-cancellation ownership decisions, while `CartItemPolicy` centralizes cart-item management ownership.

`RefundRequestController::show()` and `cancel()` now load the Eloquent resource before authorization. The show action authorizes before loading presentation-only order details and converts a non-owner denial to the same 404 used for an unknown ID, avoiding an existence oracle. The cancel action authorizes ownership first, preserves the existing localized message for non-pending requests, and then applies the policy cancellation ability before mutation. `CartApiController::update()` and `remove()` now load the item, authorize it through `CartItemPolicy`, and translate both missing and non-owned items into the existing JSON 404 response so the API does not disclose ownership.

`VendorRefundController::vendorId()` now reads the authenticated `vendor_web` guard ID. This matches `VendorWebController`, which logs vendors into that guard; no application path sets `session('vendor_web_id')`, so the previous session-key lookup was stale and could not reliably scope vendor refunds.

| Area | Follow-up remediation |
|---|---|
| `app/Models/RefundRequest.php` | Added an explicit Eloquent model for `refund_requests` with safe fillable fields and identifier casts. |
| `app/Models/CartItem.php` | Added an explicit Eloquent model for `cart_items` with ownership and quantity casts. |
| `app/Policies/RefundRequestPolicy.php` | Added `view()` and pending-only `cancel()` ownership rules. |
| `app/Policies/CartItemPolicy.php` | Added the `manage()` owner rule. |
| `AuthServiceProvider` | Registered both new policy mappings. |
| Refund and cart controllers | Replaced inline resource ownership decisions with model-load-then-authorize flows while preserving 404/API and non-pending response contracts. |
| `VendorRefundController` | Switched vendor scoping to `auth('vendor_web')->id()`. |
| `IdorAuthorizationTest` | Added cross-customer refund read/cancel denial and cross-customer cart update/remove denial regressions. |

The focused IDOR coverage remains **8 tests and 20 assertions** when combined with the prior checks, now including the non-disclosing 404 refund-detail regression. The full suite baseline before this follow-up was **109 tests and 488 assertions**; the final post-change run passed 113 tests and 496 assertions after this follow-up. No secrets, tokens, OTP values, or customer data are included.
