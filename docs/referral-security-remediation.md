# Referral security fixes — 13 November 2026

All nine findings from the referral review have been addressed. The changes are installed on the local application, including the four database migrations.

| Finding | Result |
|---|---|
| Exposed legacy OTP / account takeover | Old API phone endpoints return 410 without creating/reading a code or issuing a token. Supported website OTP enforces configuration, attempt limits, expiry, single use and blocked-account checks. |
| Vendor-created commissions on unpaid parent orders | Vendors update only their owned shipment through validated transitions. Parent state is computed from all shipments. Rewards require platform-confirmed payment and verified customer identity. |
| Rewards surviving refunds/returns | Completed refunds and commission reversal commit together. Returns/partial cancellations revoke the original reward. Open refunds block approval; rejected refunds can resume legitimate qualification. |
| Award creation racing cancellation | The processor loads and locks the order inside its transaction before checking eligibility, then locks the referral. Reversal uses the same ordering. |
| Stale administrator approval restoring a reversed reward | Admin actions reload locked order/referral/commission records and use conditional transitions. Approval rechecks current payment, delivery, refund, identity and referral facts. |
| Same phone in different formats / unverified identities | Egyptian phone values are canonical for signup/uniqueness/fraud comparison. Unverified identities cannot earn. Verification automatically retries pending legitimate completed orders after commit. |
| Referral registration rate-limit key bypass | Attribution and limiter keys share canonical codes; independent IP limits cannot be reset with raw cookie whitespace. |
| Google account pre-hijacking | Local same-email accounts are not silently linked. Existing Google accounts use stable provider identity. Recovery revokes previous API credentials and browser sessions validate credential fingerprints. |
| Missing/replayed Google state | State is random, session-bound, expiring, constant-time checked, consumed once and claimed atomically across concurrent callbacks. |

Financial approvals, rejections, amount changes, creations and reversals now produce immutable audit records with actor/before/after/reason data. The database refuses audit updates/deletes. Generic referral settings edits share validation, numeric bounds, privacy and cache invalidation with the dedicated settings page.

Queue processing selects the real first completion deterministically. Late jobs do not expire an on-time order, and only automatic expiry can recover from delayed processing. The expiry command reconciles on-time completions before expiring a referral.

## Payment collection in the admin interface

“Confirmed” at COD placement means the order may be fulfilled; it does not prove cash collection. After delivery, open the administrator's order details and use **Confirm collected payment**, enter the collection/transfer reference, and confirm receipt of funds. Eligible rewards are processed afterward.

Administrator approval of a manual transfer receipt records the platform payment proof automatically. A seller's receipt approval does not certify platform receipt of funds for rewards.

Because refund requests do not contain reliable line-level monetary amounts, any completed refund/return voids the original order commission rather than guessing a proportional adjustment. Already-paid reversals are recorded as requiring manual recovery; this update does not introduce automatic bank transfers or withdrawals.

## Session behavior

Old browser sessions without a credential binding must sign in once after the update. Password recovery invalidates existing API tokens, remember cookies and old authenticated browser sessions. Successful current password, OTP, Google and remembered sign-ins establish the new binding.

## Validation

- **77** focused security/status tests passed: **560 assertions**.
- **13** existing referral core compatibility tests passed: **42 assertions**.
- **16** earlier cart-security compatibility tests passed: **103 assertions**. One additional existing PostgreSQL-only cart-lock test was skipped by the SQLite compatibility runner.
- Total: **106 passed**, one environment-specific skip, no failures/errors in the final runs.
- A separate real PostgreSQL check used a private, disposable schema and two connections: cancellation was blocked while the award held its order lock; cancellation after commit reversed the reward. The private schema was removed afterward.
- All tests used isolated fixtures. Live application accounts, orders, inventory and settings were not used as test data.
- Before deployment, live metadata checks found no old User `auth_token` credentials, outstanding reset/verification links, unsafe legacy Google links, duplicate provider identities or existing commissions requiring remediation.
- Four migrations applied successfully. Home, Shop, Cart and Login returned HTTP 200; retired phone endpoints returned 410. No database migrations remain pending.

The original audit-only reproduction scripts intentionally assert vulnerable behavior and are retained as evidence. The new project regression suites assert the corrected behavior.
