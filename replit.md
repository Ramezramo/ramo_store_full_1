# Ramo Store — Laravel 10 Multi-Vendor E-Commerce Platform

## Overview
Ramo Store is a full-stack multi-vendor e-commerce platform built with Laravel 10, designed to host multiple sellers and provide a comprehensive online shopping experience. It includes a Blade-powered storefront for customers, a robust REST API for integrations, an administrative dashboard for platform management, and a dedicated vendor seller portal. The platform was initially developed with MySQL and has been migrated to PostgreSQL for optimized performance and hosting on Replit.

**Key Capabilities:**
- Multi-vendor support with separate vendor portals for product and order management.
- Comprehensive product catalog with variations (color, size, price, stock).
- Dynamic pricing architecture supporting product-level and variation-level discounts.
- Multi-vendor order splitting during checkout.
- Customer account management, order history, wishlists, and reviews.
- Admin dashboard for full platform control, including user, vendor, product, order, and refund management.
- Dynamic homepage layout configuration.

**Business Vision:** To provide a scalable and feature-rich e-commerce solution that empowers multiple vendors to sell their products through a unified platform, offering a seamless shopping experience for customers.

## User Preferences
No specific user preferences were provided.

## System Architecture

**Core Technologies:**
- **Framework:** Laravel 10 (PHP 8.2)
- **Database:** PostgreSQL (Replit Helium DB)
- **Frontend:** Blade templates, Vite 5 for asset compilation.
- **Authentication:** Laravel Sanctum for API token authentication and session-based authentication for web, admin, and vendor portals.

**Architectural Patterns:**
- **Modular Design:** Separation of concerns with distinct portals for customers, vendors, and administrators, each with dedicated routes, controllers, and views.
- **API-First Approach:** A comprehensive REST API (`/api` prefix) supports various functionalities for potential mobile app or external integrations.
- **Dynamic Content Management:** The homepage layout is configurable via a JSON structure stored in the `app_configs` table, allowing for flexible arrangement of sections.

**UI/UX Decisions:**
- **Storefront:** Designed for intuitive customer navigation, product discovery, and a streamlined checkout process.
- **Admin/Vendor Portals:** Focus on clear data presentation and efficient workflows for managing products, orders, and platform settings.
- **Responsive Design:** Assumed for modern web applications.

**Feature Specifications:**

1.  **Product Management:**
    *   Support for simple and variable products (color, size variations).
    *   Inline editing for product details in vendor and admin portals.
    *   Bulk price update tool for vendors.
    *   Image handling with support for both relative paths and external URLs.

2.  **Pricing Architecture:**
    *   `regular_price`, `sale_price`, and `price` columns in `product_variations` to manage pricing.
    *   `discount_percentage` at the product level (`products_data`) for uniform discounts across variations.
    *   Fallback logic to compute effective prices using `discount_percentage` if `sale_price` is not explicitly set or is higher than `regular_price`.
    *   Display logic (`parseProduct()`) calculates minimum effective prices for product listings and detail pages, including discount badges.

3.  **Order Management:**
    *   Automatic splitting of orders into `order_sub_orders` for each vendor during checkout.
    *   Separate order views for customers (parent orders with sub-order cards), vendors (their sub-orders), and admins (all orders).
    *   Parent order status synchronization with sub-order statuses.

4.  **Refund System:**
    *   `refund_requests` table to track return and refund processes.
    *   Customer interface for initiating requests, vendor interface for viewing, and admin interface for managing.

5.  **User Roles and Permissions:**
    *   Distinct roles for `admin`, `vendor_user`, and `customer`.
    *   Middleware (`AdminAuth`) to secure admin routes based on email or role.

## Database Import

The app uses PostgreSQL (Replit Helium DB). On first run, `start.sh` automatically detects if the database is empty (fewer than 5 tables) and imports a SQL dump file.

**How it works:**
- `start.sh` counts existing tables in the public schema.
- If fewer than 5 tables exist, it looks for the SQL dump file:
  - `project_last_database_updated_use_it_for_first_time_conigration.sql`
- If a file is found, it pipes it through `psql` (stripping `\restrict` lines that are non-standard).
- If no SQL file is found, it falls back to running `php artisan migrate`.

**To re-import or reset the database manually:**
```bash
# Drop all tables first (run in psql or via the Replit DB shell)
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE \
  -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"

# Then import from the SQL dump
grep -v '\\restrict' project_last_database_updated_use_it_for_first_time_conigration.sql | \
  PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE
```

**To export the current database:**
```bash
PGPASSWORD=$PGPASSWORD pg_dump -h $PGHOST -p $PGPORT -U $PGUSER $PGDATABASE > database_export.sql
```

The SQL dump file should be placed in the project root. The startup script will pick it up automatically on the next fresh environment.

## Known Fixes & Critical Configuration

### Vendor Login — Session / Cookie Fix
**Problem:** After login, the browser was redirected back to `/vendor-login` with empty fields. Root cause: Replit's preview pane is an iframe (top-level origin `replit.com`). Cookies with `SameSite=lax` are blocked in cross-site iframe contexts, so the session cookie was never sent with the POST or follow-up GET requests.

**Fix applied (do not revert):**
- `SESSION_SAME_SITE=none` — allows cookies in cross-site/iframe contexts.
- `SESSION_SECURE_COOKIE=true` — required by browsers when SameSite=None.
- `SESSION_COOKIE=rms_session` — renamed from the default to avoid conflicts with old cookies stored in users' browsers.
- Both settings are written into `.env` by `start.sh` on every boot (lines ~55–57).
- `config/session.php` reads `same_site` from env: `env('SESSION_SAME_SITE', 'lax')`.
- After `Auth::guard('vendor_web')->login(...)`, the controller calls `$request->session()->save()` before issuing the 302 redirect, ensuring session data is flushed to disk before the browser follows the redirect.

**Key files:**
- `start.sh` — writes `SESSION_COOKIE`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE` into `.env`
- `config/session.php` — `same_site` uses `env()` (not hardcoded)
- `app/Http/Controllers/Web/VendorWebController.php` — `login()` method calls `session()->save()` before redirect
- `app/Http/Middleware/VerifyCsrfToken.php` — `/vendor-login`, `/login`, `/admin/login` are in the `$except` array to prevent 419 errors

### Image Upload / Storage Symlink Fix
**Problem:** After uploading a product image, it displayed as broken (404). Root cause: `public/storage` was a **real directory** instead of a symlink to `storage/app/public`. Laravel's `Storage::disk('public')` writes to `storage/app/public/`, but the web server serves files from `public/storage/`. Since they were different directories, uploaded images were never reachable via HTTP.

**Fix applied (do not revert):**
- `start.sh` now detects if `public/storage` is a real directory, copies its contents into `storage/app/public/`, removes the directory, then creates a proper symlink: `ln -sfn ../storage/app/public public/storage`
- `php artisan storage:link` was removed — it silently skips when a directory already exists; the manual `ln -sfn` is used instead and is idempotent
- All product image subdirectories are pre-created on boot: `thumbnails/`, `other_images/`, `natural_images/`, `variations/`

**Key file:** `start.sh` lines ~101–107 — the symlink fix block runs on every boot.

### APP_KEY Persistence
The APP_KEY is generated once and stored in `.app_key` in the project root. `start.sh` loads it from there on every restart so session cookies survive container restarts.

### CSRF Exemptions
Login routes are exempt from CSRF verification in `VerifyCsrfToken.php`:
```php
protected $except = [
    'login',
    'vendor-login',
    'admin/login',
];
```

### Test Accounts

**Admin:**
- **URL:** `/admin/login`
- **Email:** `adminramoui@gmail.com`
- **Password:** `admin123456`
- **Role:** `admin` in the `users` table (no separate admins table)
- Guard: standard `web` (Auth) + `AdminAuth` middleware checks `role === 'admin'` or email match
- Routes under `/admin/*`

**Vendor:**
- **URL:** `/vendor-login`
- **Email:** `cairo.fashion@ramostore.com`
- **Password:** `vendor123456`
- **Status:** `approved` in `vendor_users` table
- Guard: `vendor_web` (session-based), routes under `/seller/*`

## External Dependencies

-   **PostgreSQL:** Primary database hosted on Replit Helium DB.
-   **Vite 5:** Used for compiling and bundling frontend assets (JavaScript, CSS).
-   **NPM:** Manages JavaScript dependencies for Vite.
-   **Replit Secrets:** Environment variables are managed securely via Replit's secrets feature, dynamically generating the `.env` file at runtime.