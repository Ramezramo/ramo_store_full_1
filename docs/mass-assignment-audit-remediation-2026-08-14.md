# Mass-Assignment Audit Remediation — 2026-08-14

## Scope and method

This review traced the reported Eloquent models and every direct application write path identified for `AttributesModel`, `User`, `VendorUser`, `Order`, `Product`, `ProductData`, and `VideosModel`. The route table was also inspected to determine whether the reported `AttributesController` write method is currently web-reachable.

## Findings and disposition

| Area | Assessment | Disposition |
|---|---|---|
| `AttributesController::store()` and `AttributesModel` | The request rules accepted `id`, and `AttributesModel::$fillable` permitted it. A caller could supply a primary key to `AttributesModel::create()`. The controller is not currently registered in the route table, but the code was unsafe if connected later. | **Confirmed defect; remediated.** |
| `Product::$fillable` | `id` was allowlisted even though the sole current product creation map does not supply a primary key. | **Defense-in-depth issue; remediated.** |
| `ProductData` and `VideosModel` | Both relied on Laravel's implicit deny-all default and have no approved direct mass-assignment write paths. | **Hardening/documentation gap; remediated.** |
| `User::$fillable` (`role`, `capabilities`) | Sensitive fields are allowlisted. Current customer registration paths construct server-controlled arrays and hard-code customer-only values; no request-wide fill path or confirmed privilege-escalation route was found. | **No confirmed live exploit.** Retain pending a separately scoped refactor of all trusted account-provisioning paths to explicit privileged assignment. |
| `VendorUser::$fillable` | System fields are broadly allowlisted. Current vendor registration whitelists fields and assigns system values server-side; no request-wide fill path was found. | **No confirmed live exploit.** |
| `Order::$fillable` | Financial and status fields are allowlisted. Current order construction is server-computed and manually assembled; no request-wide fill path was found. | **No confirmed live exploit.** |
| `Product::$fillable` (`vendor_id`, `acceptance_status`, `status`) | These fields are sensitive, but the product creation map derives them from the authenticated seller and server constants. | **No confirmed live exploit.** |

## Implemented changes

The attributes write path no longer validates or accepts `id`, and the model no longer permits primary-key mass assignment. The controller also returns immediately on validation failure, preventing invalid inputs from reaching persistence.

The product model no longer permits primary-key mass assignment. `ProductData` and `VideosModel` now explicitly declare `protected $guarded = ['*'];`, preserving their existing secure behavior while making the policy durable and reviewable.

## Regression evidence

`tests/Feature/MassAssignmentProtectionTest.php` now verifies that caller-supplied IDs are discarded for the attributes and product models and that the two legacy models reject unapproved mass assignment. The focused test and the complete Laravel suite passed after the changes.

| Validation | Result |
|---|---|
| PHP syntax checks for changed application files | Passed |
| Focused mass-assignment regression test | Passed: 3 tests, 10 assertions |
| Complete Laravel suite | Passed: 82 tests, 365 assertions |

## Follow-up boundary

No customer, vendor, administrator, order, payment, or product workflow was changed beyond primary-key protection. The sensitive fillable fields retained in `User`, `VendorUser`, `Order`, and `Product` should be reduced only in a dedicated refactor that converts their trusted server-side writers to explicit assignment. Doing so independently avoids silent omissions of required, server-generated fields in existing account, checkout, and seller flows.


## Follow-up report reconciliation

A follow-up review of `mass_assignment_report.md` was completed against the hardened branch. Its remaining model-level recommendations were implemented where the protected attributes are controlled by server logic.

| Area | Follow-up action | Compatibility measure |
|---|---|---|
| `User` | Removed `role` and `capabilities` from `$fillable`. The obsolete API registration `role` validation rule was removed. | Web, Google, and OTP registration now set the fixed customer role and capabilities explicitly before save. Test-only admin setup likewise assigns its trusted role explicitly. |
| `VendorUser` | Removed approval status, token, commission, metrics, ratings, and payout/banking fields from `$fillable`. | Seller onboarding already assigns platform-controlled values explicitly; seller-editable profile fields remain fillable. |
| `Product` | Removed `vendor_id`, `status`, and `acceptance_status` from `$fillable` (the primary key had already been removed). | Product creation assigns vendor ownership and moderation/publication defaults explicitly before saving. |
| `Order` | Confirmed `set_paid`, `payment_status`, and `payment_reviewed_by` are not mass assignable; customer ownership is now removed from the create map and assigned explicitly. | Checkout retains the authenticated user as `customer_id` through trusted server assignment before save. |

### Follow-up validation

- Focused model-policy tests: **6 tests, 28 assertions**.
- Complete Laravel suite: **85 tests, 385 assertions**.
- PHP syntax checks passed for all changed models and controllers.

No customer, vendor, admin, payment, or production data was used in validation.

**Residual design note:** ordinary seller profile and checkout fields intentionally remain assignable only through controllers that validate and construct narrow server-side payloads. Any future direct request-to-model `create`, `update`, or `fill` call must use the protected-model tests and the approved-field allowlists as a gate.

---

*Follow-up reconciliation completed on 2026-08-14.*


## Order financial and lifecycle allowlist hardening

A subsequent report confirmed that no active mass-assignment path exists, but identified `Order::$fillable` as a defense-in-depth concern because it still permitted calculated totals and lifecycle fields. The following preventive controls were applied.

| Control | Implementation |
|---|---|
| Financial totals | Removed `original_total`, `discount_total`, `discount_tax`, `shipping_total`, `shipping_tax`, `cart_tax`, `total_tax`, and `final_total` from `$fillable`. Checkout now assigns each calculated value explicitly before saving the order. |
| Lifecycle and administration | Removed `status`, general-order override fields, and paid/completed timestamps from `$fillable`. The validated vendor status transition now uses direct property assignments and `save()`. |
| Regression gate | Expanded the model-policy test to verify generic `fill()` cannot populate ownership, paid state, lifecycle, administrative override, or financial-total fields, while explicit trusted assignments remain functional. |

### Validation

- Focused mass-assignment suite: **6 tests, 40 assertions**.
- Complete Laravel suite: **85 tests, 397 assertions**.
- PHP syntax checks passed for `Order` and `OrdersController`.

This is a preventative control; the audit found no existing request-to-model mass-assignment vulnerability. No deployment, production order, payment, or customer data action was performed.

---

*Order hardening reconciliation completed on 2026-08-14.*
