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
