# Catalog Remediation Register

**Updated:** 14 August 2026 UTC
**Scope:** Catalog-media audit of the project database and local public media storage, including the product 22 remediation.

## Current evidence

The catalog-media audit inspected **22 products** and **4 gallery records**. All 22 products are currently marked `publish` and `approved`. The application prevents newly submitted or approved products from becoming storefront-visible when mandatory catalog fields, usable media, category membership, or sellable variation pricing are missing. Customer pages skip missing local asset paths and fall back to the next usable product image when one exists. On 14 August 2026, product 22 was corrected from three missing imported paths to one user-authorized, managed JPEG; the live product page now renders that image instead of an empty gallery panel.

| Finding | Count | Automated remediation applied | Remaining action |
|---|---:|---|---|
| Published products without a merchant SKU | 21 | Publication is now blocked for future incomplete products. | The merchant must assign verified SKUs. The system must not fabricate commercial stock identifiers. |
| Product 22 missing imported media references | 3 historical paths | Replaced with the user-authorized managed JPEG `products/luxe-velvet-jeans-olive.jpg`; the public product page, cart thumbnail path, and category fallback now resolve it through the existing media helper. | Replace the interim catalog image only if the merchant later supplies a more accurate licensed product photograph. |
| Products using external media | 21 | Detected and reported. | Migrate approved, licensed source assets to managed object storage before production. |
| Gallery records pointing to missing local files | 4 | Detected and reported. | Re-upload or remove the stale gallery records after verifying that their original assets are no longer needed. |

> The catalog-media audit now records the user-authorized product 22 image correction. It does **not** fabricate SKUs, pricing, stock, or additional commercial imagery. In `--strict` mode, it exits non-zero while external-media or missing-media defects remain, so it can serve as a release gate.

## Affected local media references

| Record | Stored path |
|---|---|
| Product 22 managed thumbnail and gallery image | `products/luxe-velvet-jeans-olive.jpg` |
| Product 22 former imported paths | Replaced by migration `2026_08_14_010000_repair_luxe_velvet_jeans_media`; not emitted to customer HTML. |
| Gallery 18 | `image-gallery/TS3jg0Uwd2cOWGo7107QhMXOpGk0bkE8roB7qFsY.png` |
| Gallery 19 | `image-gallery/wplKQURnGABGUo1uRrqykvc48AM5IQ7fO7fozO5y.png` |
| Gallery 20 | `image-gallery/Oz5lvZeeXPHipbtH3n4tCJ20Lv5GxrFUBFn0lNst.png` |
| Gallery 21 | `image-gallery/JRsRpX1ME2SEp6UNZXwFb5JJeapo51tOT1QLncH3.jpg` |

## Recommended merchant workflow

The administrator should export or inspect the affected published products, add the verified merchant SKU for each product, then upload or confirm the intended product assets. The gallery entries should be re-uploaded only where the source files are known and licensed. After the data corrections, run:

```bash
php scripts/audit_catalog_media.php --strict
```

A launch candidate should have zero missing required fields, zero missing local media references, and zero unmanaged external-media references. The strict audit currently reports **21 external product-media records**, so the catalog is not yet production-ready despite the completed product 22 correction.
