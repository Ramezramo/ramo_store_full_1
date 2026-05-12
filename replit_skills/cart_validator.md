# Cart Validator — Architecture, Flow & Security

## Overview

The cart system was refactored across multiple sessions to:
1. Centralise shared logic into reusable PHP traits
2. Enforce server-side price verification at checkout
3. Prevent client-supplied line items from reaching the order-creation API

---

## Files Involved

| File | Role |
|---|---|
| `app/Http/Traits/CartTrait.php` | Shared cart read/write/merge logic |
| `app/Http/Traits/WishlistTrait.php` | Shared wishlist read/write logic |
| `app/Http/Controllers/Web/CartController.php` | Storefront cart management |
| `app/Http/Controllers/Web/CheckoutController.php` | Web checkout flow (index + place) |
| `app/Http/Controllers/Web/AuthWebController.php` | Login/register — merges guest session into DB |
| `app/Http/Controllers/Web/WishlistController.php` | Storefront wishlist toggle/remove |
| `app/Http/Controllers/OrdersController.php` | API order creation (`createOrder()`) |

---

## Cart Storage — Two Modes

### Guest user
- Cart lives in `session('ramo_cart', [])`.
- Each entry is a keyed array (key = `md5(product_id . '_' . variation_id)`).
- Prices are written to session when the item is added (via `CartController`).
- **Risk**: session data is server-side but was originally set from product page values — must be re-verified at checkout.

### Authenticated user
- Cart lives in the `cart_items` DB table.
- Schema: `user_id`, `product_id`, `variation_id` (nullable), `qty`, `created_at`, `updated_at`.
- Prices are **never stored** in `cart_items` — they are re-fetched from `products_data` + `product_variations` every time the cart is loaded.

---

## CartTrait (`app/Http/Traits/CartTrait.php`)

Used by: `CartController`, `CheckoutController`, `AuthWebController`

### Methods

#### `getCart(): array`
Returns the full cart as an associative array keyed by `rowId`.
- Logged-in → calls `loadCartFromDb()`
- Guest → returns `session('ramo_cart', [])`

#### `saveCart(array $cart): void`
Persists the cart.
- Logged-in → calls `syncCartToDb($cart)`
- Guest → `session(['ramo_cart' => $cart])`

#### `loadCartFromDb(): array`
Builds the cart array from the DB for the current authenticated user.

**Steps:**
1. Load all `cart_items` rows for `Auth::id()`
2. Bulk-load matching `products_data` and `product_variations` records
3. For items with no `variation_id`, fall back to the product's `main_variation`
4. Compute the live price:
   ```
   regularPrice = variation.regular_price
   price        = variation.price ?? regularPrice
   if discount_percentage > 0 && price >= regularPrice:
       price = regularPrice * (1 - discount_percentage / 100)
   ```
5. Decode variation `attributes` JSON (with `stripslashes` fallback)
6. Return array of cart items:

```php
[
  'rowId'        => md5(product_id . '_' . variation_id),
  'product_id'   => int,
  'variation_id' => int|null,
  'name'         => string,
  'price'        => float,   // live DB price
  'qty'          => int,
  'image'        => string,  // via AppConstants::productThumbnailUrl()
  'stock'        => int,
  'attrs'        => array,   // decoded variation attributes
]
```

#### `syncCartToDb(array $cart): void`
Full replace: deletes all existing `cart_items` for `Auth::id()`, then inserts one row per cart entry.

#### `mergeGuestCartToDb(int $userId, array $guestCart): void`
Merges a guest session cart into the DB cart on login/register.
- If the product+variation already exists → increments qty (capped by `$item['stock']`)
- Otherwise → inserts a new row

#### `mergeGuestWishlistToDb(int $userId, array $guestWishlist): void`
Merges guest wishlist product IDs into the `wishlists` table, skipping duplicates.

#### `mergeGuestSessionOnLogin(int $userId): void`
Convenience wrapper called on both login and register:
```php
$this->mergeGuestCartToDb($userId, session('ramo_cart', []));
$this->mergeGuestWishlistToDb($userId, session('ramo_wishlist', []));
session()->forget(['ramo_cart', 'ramo_wishlist', 'ramo_coupon']);
```

---

## WishlistTrait (`app/Http/Traits/WishlistTrait.php`)

Used by: `WishlistController`

### Methods

#### `getWishlistIds(): array`
- Logged-in → reads `product_id` values from `wishlists` table for `Auth::id()`
- Guest → returns `session('ramo_wishlist', [])` (array of product IDs)

#### `saveWishlistIds(array $ids): void`
- Logged-in → full replace of the user's `wishlists` rows
- Guest → `session(['ramo_wishlist' => $ids])`

---

## Guest-to-Authenticated Cart Merge Flow

```
User logs in / registers
        │
        ▼
AuthWebController captures guest session BEFORE session regeneration:
  $guestCart     = session('ramo_cart', [])
  $guestWishlist = session('ramo_wishlist', [])
        │
        ▼
Auth::attempt() / Auth::login() + session()->regenerate()
        │
        ▼
mergeGuestSessionOnLogin($user->id)
  ├─ mergeGuestCartToDb($userId, $guestCart)
  │     For each guest item:
  │       - Already in DB? → qty += guest_qty (capped at stock)
  │       - Not in DB?     → INSERT new row
  └─ mergeGuestWishlistToDb($userId, $guestWishlist)
        For each guest product_id:
          - Already in wishlists? → skip
          - Not there?            → INSERT
        │
        ▼
session()->forget(['ramo_cart', 'ramo_wishlist', 'ramo_coupon'])
```

---

## Web Checkout Flow (`CheckoutController`)

### `index()`
1. `$cart = $this->getCart()` — redirect to cart if empty
2. Guard: require login unless guest checkout is enabled
3. `$subtotal = collect($cart)->sum(price * qty)` — server-side
4. `$discount = calcDiscount($subtotal, $coupon)` — from session coupon
5. `$total = max(0, $subtotal - $discount)`
6. Render `web.checkout` view

### `place(Request $r)`

**Validation** — only accepts: billing/shipping fields, payment_method, notes, coupon. No price or total fields accepted from the client.

**Price re-verification block** (the key security step):
```
1. Collect all product_ids and variation_ids from the loaded cart
2. Bulk-load products_data  (keyed by id)
3. Bulk-load product_variations for those products (keyed by id)
4. For each cart item:
     a. Look up live product from DB → 404 redirect if gone
     b. Look up live variation from DB → redirect if gone
        (falls back to main_variation if no variation_id)
     c. Recompute live price using the same formula as loadCartFromDb()
     d. Replace item['price'] with the live DB price
5. $cart = $verifiedCart  (session/DB prices are now overwritten)
```

**Totals computed from verified cart:**
```php
$subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
$discount = $this->calcDiscount($subtotal, $coupon);
$total    = max(0, $subtotal - $discount);
```

**Order creation:**
- Inserts one row into `orders` with server-computed `original_total`, `final_total`, `discount_total`
- Splits into vendor sub-orders in `order_sub_orders` with proportional discount
- Clears session: `session()->forget(['ramo_cart', 'ramo_coupon'])`

---

## API Order Creation (`OrdersController::createOrder()`)

### What the client sends
```json
{
  "currency": "EGP",
  "payment_method": "cod",
  "payment_method_title": "Cash on Delivery",
  "billing": { ... },
  "shipping": { ... },
  "shipping_lines": [ { "method_id": "...", "method_title": "...", "total": 0 } ],
  "coupon": "OPTIONAL20"
}
```

`line_items` is **not accepted from the client** — it was removed from the validator entirely.

### Server-side line item construction
```php
$cartItems = DB::table('cart_items')->where('user_id', $userId)->get();
// Fails with 422 if empty

$validatedData['line_items'] = $cartItems->map(fn($item) => [
    'product_id'           => $item->product_id,
    'variation_id'         => $item->variation_id,
    'quantity'             => $item->qty,
    'main_variation_order' => 0,
])->all();
```

### Price and stock resolution (STEP 4)
For each DB-sourced line item:
- Resolves variation (by `variation_id`, or falls back to `main_variation`)
- Price priority: `sale_price` → `regular_price` → `price`
- Stock check: if `manage_stock = 1` and `stock_quantity < qty` → 422 error
- Stock deduction: `variation->decrement('stock_quantity', $quantity)`

### After successful order
```php
DB::table('cart_items')->where('user_id', $userId)->delete();
```

---

## Database Tables Referenced

| Table | Purpose |
|---|---|
| `cart_items` | Authenticated user cart rows |
| `products_data` | Product master (name, images, vendor_id, discount_percentage, stock_quantity) |
| `product_variations` | SKU-level data (price, regular_price, sale_price, attributes, stock_quantity, main_variation flag) |
| `wishlists` | Authenticated user saved products |
| `orders` | Parent order record |
| `order_sub_orders` | Per-vendor split of the parent order |
| `coupons` | Coupon codes validated by `CouponController::applyCouponLocally()` |

---

## Security Properties Enforced

| Attack | How it's blocked |
|---|---|
| Client sends arbitrary `line_items` in API | Removed from validator; loaded from `cart_items` table instead |
| Client sends tampered price in session cart | `place()` re-fetches all prices from DB before computing totals |
| Client sends a variation not in their cart | API builds items from `cart_items` — variation must exist in that table |
| Double-order (submit twice) | Cart cleared from DB after successful order creation (both API and web) |
| Guest merging wrong items on login | Guest cart captured before `session()->regenerate()`, merged item-by-item with stock cap |
