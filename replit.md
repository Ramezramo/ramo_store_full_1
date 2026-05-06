# Ramo Store — Laravel 10 multi-vendor e-commerce platform

## Run & Operate
- **Start:** `bash start.sh` (writes `.env`, runs migrations, builds assets, serves on port 5000)
- **Build assets:** `npm run build`
- **Migrate:** `php artisan migrate --force`
- **Clear cache:** `php artisan config:clear && php artisan cache:clear`
- **Required env vars:** `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`, `REPLIT_DEV_DOMAIN` (all auto-provided by Replit)

## Stack
- PHP 8.2, Laravel 10, Laravel Sanctum
- PostgreSQL (Replit Helium DB)
- Blade templates + Vite 5 (CSS/JS bundling)
- Node 20 / npm for frontend deps

## Where things live
- `start.sh` — bootstrap script (env, DB import, asset build, serve)
- `routes/web.php` — web/Blade routes; `routes/api.php` loads `routes/api/*.php`
- `app/Http/Controllers/Web/` — web portal controllers
- `app/Http/Controllers/Api/` — API controllers
- `app/Http/Controllers/Admin/` — admin portal controllers
- `resources/views/` — Blade templates
- `database/migrations/` — schema source of truth
- `vite.config.js` — frontend build config

## Architecture decisions
- Custom auth via Laravel Sanctum (API tokens) + session guards (`vendor_web`, `web`) — no external auth provider
- `SESSION_SAME_SITE=none` + `SESSION_SECURE_COOKIE=true` required for Replit iframe/preview compatibility
- `APP_KEY` persisted in `.app_key` file so sessions survive container restarts
- Storage symlink (`public/storage → storage/app/public`) managed manually in `start.sh` (not via `artisan storage:link`)
- CSRF exempt for login routes: `login`, `vendor-login`, `admin/login`

## Product
- Multi-vendor storefront with product catalog, cart, checkout, wishlists, reviews
- Vendor portal for product/order management; Admin dashboard for full platform control
- Order splitting per vendor, refund requests, OTP-based registration

## User preferences
_None specified yet._

## Gotchas
- Do not revert `SESSION_SAME_SITE=none` — required for cookies in Replit iframe preview
- Stripe/Twilio references in code are labels/comments only — no real SDK integrations exist
- SQL dump import in `start.sh` strips `\restrict` lines for PostgreSQL compatibility
- Re-import DB: `DROP SCHEMA public CASCADE; CREATE SCHEMA public;` then re-run `start.sh`

## Test accounts
- **Admin:** `/admin/login` — `adminramoui@gmail.com` / `admin123456`
- **Vendor:** `/vendor-login` — `cairo.fashion@ramostore.com` / `vendor123456`
