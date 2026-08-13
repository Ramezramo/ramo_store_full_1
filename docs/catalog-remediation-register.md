# Catalog Remediation Register

**Generated:** 13 August 2026 UTC
**Scope:** Read-only audit of the project database and local public media storage.

## Current evidence

The catalog-health audit inspected **22 products** and **4 gallery records**. All 22 products are currently marked `publish` and `approved`. The application now prevents newly submitted or approved products from becoming storefront-visible when mandatory catalog fields, usable media, category membership, or sellable variation pricing are missing. Customer pages also skip missing local asset paths and fall back to the next usable product image when one exists.

| Finding | Count | Automated remediation applied | Remaining action |
|---|---:|---|---|
| Published products without a merchant SKU | 21 | Publication is now blocked for future incomplete products. | The merchant must assign verified SKUs. The system must not fabricate commercial stock identifiers. |
| Product 22 local media references missing from storage | 3 | Customer rendering no longer emits broken URLs; a usable secondary image is selected when present. | Re-upload or intentionally replace the three original media assets after confirming the intended product imagery. |
| Products using external media | 21 | Detected and reported. | Migrate approved, licensed source assets to managed object storage before production. |
| Gallery records pointing to missing local files | 4 | Detected and reported. | Re-upload or remove the stale gallery records after verifying that their original assets are no longer needed. |

> The audit intentionally does **not** modify SKUs, pricing, imagery, stock, or categories. Those values are commercial content and require verified merchant input. The audit exits with a non-zero status while unresolved content defects remain so it can be used as a release gate.

## Affected local media references

| Record | Stored path |
|---|---|
| Product 22 thumbnail | `products/thumbnails/jWxe2g5AHxyoQJgVxo8FknSBZq8ohIJy3W1G29QP.jpg` |
| Product 22 additional image | `products/other_images/nVBVb7y51SbUZuaEfKPlJVsYmzUAbbUdQrGlOQRF.jpg` |
| Product 22 natural image | `products/natural_images/ILzxE3ijlarqJIbl6BJGyWZXcgTun45P5ydqzWMh.jpg` |
| Gallery 18 | `image-gallery/TS3jg0Uwd2cOWGo7107QhMXOpGk0bkE8roB7qFsY.png` |
| Gallery 19 | `image-gallery/wplKQURnGABGUo1uRrqykvc48AM5IQ7fO7fozO5y.png` |
| Gallery 20 | `image-gallery/Oz5lvZeeXPHipbtH3n4tCJ20Lv5GxrFUBFn0lNst.png` |
| Gallery 21 | `image-gallery/JRsRpX1ME2SEp6UNZXwFb5JJeapo51tOT1QLncH3.jpg` |

## Recommended merchant workflow

The administrator should export or inspect the affected published products, add the verified merchant SKU for each product, then upload or confirm the intended product assets. The gallery entries should be re-uploaded only where the source files are known and licensed. After the data corrections, run:

```bash
php scripts/audit_catalog_health.php
```

A launch candidate should have zero missing required fields and zero missing local media references. External-media references should be migrated to the approved production media store before launch.
