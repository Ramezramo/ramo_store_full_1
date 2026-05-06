# Categories — Complete Reference

Everything about how categories work in Ramo Store: database, hierarchy rules, controllers, views, routes, and edge cases.

---

## 1. Database Table: `categories2`

> Note: the table is named `categories2` (not `categories`). There is no `categories` table.

### Schema

| Column        | Type           | Nullable | Notes |
|---------------|----------------|----------|-------|
| `id`          | bigint         | NO       | Primary key, auto-increment |
| `name`        | varchar(255)   | NO       | Human-readable name |
| `slug`        | varchar(255)   | YES      | URL-safe version of name, generated with `Str::slug($name)` |
| `parent`      | integer        | YES      | `0` or `NULL` = top-level parent; positive integer = child pointing to parent's `id` |
| `description` | varchar(255)   | YES      | Optional short description |
| `display`     | varchar(255)   | YES      | `'visible'` when created via admin; may be NULL for old data |
| `image`       | text           | YES      | Relative storage path e.g. `categories/abc123.jpg`; NULL if no image |
| `menu_order`  | integer        | YES      | Lower number = appears higher in lists; sort always by `menu_order ASC, name ASC` |
| `count`       | integer        | YES      | Legacy field; NOT used for product counts (use `product_category` join instead) |
| `has_children`| double precision| YES     | `1` if category has at least one child; `0` or NULL otherwise; must be kept in sync manually |
| `_links`      | text           | YES      | Legacy field from WooCommerce import; not used |

### Hierarchy Rules

- **Top-level (parent) category:** `parent = 0` OR `parent IS NULL`
- **Child (sub) category:** `parent = <id of parent>` (positive integer)
- Maximum supported depth in the UI: **2 levels** (parent → child). Deeper nesting is not rendered anywhere.
- `has_children` must be kept in sync:
  - Set to `1` on the parent whenever a child is inserted or moved under it.
  - Set to `0` on the parent when its last child is removed or re-parented.

### Live Data Snapshot (as of migration)

```
id=315  Games           parent=NULL   menu_order=0   has_children=1
id=316  Shooter         parent=315    menu_order=0   has_children=0
id=314  Uncategorized   parent=0      menu_order=0
id=311  mobile-phones   parent=2      menu_order=2
id=208  Clothing        parent=0      menu_order=3
id=23   Bags-ramo       parent=0      menu_order=4
id=18   Men             parent=0      menu_order=6
id=24   Bag-ramo        parent=18     menu_order=7
id=30   Jeans Man       parent=18     menu_order=8
id=28   Jackets         parent=30     menu_order=9
id=20   Shoes           parent=28     menu_order=10
id=19   Shirts          parent=18     menu_order=11
id=21   T-Shirts        parent=18     menu_order=12
id=22   Women           parent=24     menu_order=13
...
```

---

## 2. Product–Category Pivot: `product_category`

| Column        | Type   | Notes |
|---------------|--------|-------|
| `product_id`  | bigint | FK to `products_data.id` |
| `category_id` | bigint | FK to `categories2.id` |

Primary key: `(product_id, category_id)` — a product can belong to multiple categories.

**To count products per category (correct method):**
```php
$catCounts = DB::table('product_category as pc')
    ->join('products_data as p', 'p.id', '=', 'pc.product_id')
    ->select('pc.category_id', DB::raw('count(*) as cnt'))
    ->groupBy('pc.category_id')
    ->pluck('cnt', 'category_id');
// Access: $catCounts[$categoryId] ?? 0
```

Do **not** use `categories2.count` — it is stale legacy data from WooCommerce.

---

## 3. How to Query the Hierarchy

### Standard pattern used everywhere

```php
$allCats    = DB::table('categories2')->orderBy('menu_order')->orderBy('name')->get();
$parentCats = $allCats->filter(fn($c) => $c->parent == 0 || $c->parent === null)->values();
$childCats  = $allCats->filter(fn($c) => $c->parent > 0)->groupBy('parent');
// $childCats is a Collection keyed by parent id
// $childCats[18] → collection of children belonging to category id=18
```

### Checking if a category has children

```php
$hasKids  = isset($childCats[$cat->id]) && $childCats[$cat->id]->count() > 0;
// or in DB:
$hasKids  = DB::table('categories2')->where('parent', $cat->id)->exists();
```

---

## 4. Shop Page Filtering (`WebController::shop()`)

**File:** `app/Http/Controllers/WebController.php`

### Query parameter
`?category=<id>` — selects a category to filter by. Works for both parent and child IDs.

### Filtering logic
When a category ID is passed:
1. Start with that category's ID in the filter list.
2. If it is a parent, **also add all its children** to the filter list (inclusive filter).
3. Filter products using a `JOIN` on `product_category` with `whereIn(category_id, $ids)` + `distinct()`.

```php
$activeCategoryId  = $request->filled('category') ? (int) $request->category : null;
$filterCategoryIds = [];

if ($activeCategoryId) {
    $filterCategoryIds[] = $activeCategoryId;
    if (isset($childCats[$activeCategoryId])) {
        foreach ($childCats[$activeCategoryId] as $child) {
            $filterCategoryIds[] = $child->id;
        }
    }
    $query->join('product_category as pc', function ($j) use ($ids) {
        $j->on('pc.product_id', '=', 'p.id')->whereIn('pc.category_id', $ids);
    })->distinct();
}
```

### Active parent tracking
Used by the sidebar to know which parent section to expand:

```php
$activeParentId = null;
if ($activeCategoryId) {
    $activeCat = $allCats->firstWhere('id', $activeCategoryId);
    if ($activeCat && $activeCat->parent > 0) {
        $activeParentId = $activeCat->parent; // child selected → open its parent
    } else {
        $activeParentId = $activeCategoryId;  // parent selected → open itself
    }
}
```

### Variables passed to `shop.blade.php`

| Variable           | Type                 | Description |
|--------------------|----------------------|-------------|
| `$parentCats`      | Collection           | All top-level categories |
| `$childCats`       | Collection (grouped) | Children keyed by parent id |
| `$activeCategoryId`| int\|null            | Currently selected category id |
| `$activeParentId`  | int\|null            | Parent to expand in sidebar |
| `$catCounts`       | Collection           | Product counts keyed by category id |

---

## 5. Shop Sidebar Widget (Blade)

**File:** `resources/views/web/shop.blade.php`

### Rendering order
1. "All Categories" link (clears the `?category=` param, keeps other filters).
2. Loop `$parentCats` → for each parent, render its row then a collapsible sub-list of `$childCats[$parent->id]`.

### Active state
- Parent row: highlighted (orange pill) when `$activeCategoryId == $parent->id || $activeParentId == $parent->id`.
- Child row: highlighted when `$activeCategoryId == $child->id`.
- Sub-list auto-expands if `$activeParentId == $parent->id`.

### Product count badges
`{{ $catCounts[$parent->id] ?? 0 }}` — displayed next to each row. Counts are inclusive of the parent's direct products only (children have their own separate counts).

### localStorage persistence
The open/closed state of each widget (categories, sort) is stored in `localStorage` under the key `widget_<widgetId>`.

### URL generation for category links
Always merge with existing query params to preserve active search/sort:
```blade
route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]))
```

---

## 6. Admin CRUD

**Controller:** `app/Http/Controllers/Admin/AdminCategoryBrandController.php`  
**View:** `resources/views/admin/category-brand-requests/index.blade.php`  
**URL:** `/admin/category-brand-requests?tab=categories`

### Routes (all inside `auth:admin` middleware group)

| Method | URI | Route name | Action |
|--------|-----|-----------|--------|
| GET    | `/admin/category-brand-requests` | `admin.cbr` | Index (all 3 tabs) |
| POST   | `/admin/categories` | `admin.categories.store` | Create category |
| PATCH  | `/admin/categories/{id}` | `admin.categories.update` | Update category |
| DELETE | `/admin/categories/{id}` | `admin.categories.destroy` | Delete category |

### Create (`storeCategory`)
- Validates: `name` required, `image` optional image ≤ 2 MB.
- Duplicate check: case-insensitive `LOWER(name) = ?`.
- Slug: auto-generated with `Str::slug($name)`.
- Image: stored to `storage/app/public/categories/` → relative path saved in DB.
- Sets parent's `has_children = 1` if a parent is specified.

### Update (`updateCategory`)
- Accepts same fields as create plus `remove_image` (hidden input = `"1"` to delete current image).
- Old image is deleted from disk before storing the new one.
- If parent changes, the old parent's `has_children` is recomputed and set to `0` if it now has no remaining children.

### Delete (`destroyCategory`)
- Blocks deletion if children exist **unless** `force=1` is posted (UI sends it automatically when children exist).
- On force delete: moves all children to `parent = 0` (top-level).
- Removes the row from `product_category` (unlinks all products).
- Deletes the image file from storage.
- Resets old parent's `has_children` if it becomes childless.

---

## 7. Image Handling

### Storage path
- Files are stored to the **public disk**: `Storage::disk('public')->put('categories/', ...)`.
- The `store()` helper: `$request->file('image')->store('categories', 'public')`.
- This writes to: `storage/app/public/categories/<filename>`.
- Served via the symlink: `public/storage` → `storage/app/public`.
- Full URL in Blade: `Storage::disk('public')->url($category->image)`.

### Symlink setup (critical)
`start.sh` creates the symlink on every boot:
```bash
ln -sfn ../storage/app/public public/storage
```
If `public/storage` is a real directory, `start.sh` copies its contents to `storage/app/public/` first, then replaces it with the symlink.

### Directory pre-creation
`start.sh` also pre-creates the subdirectories on boot:
```bash
mkdir -p storage/app/public/categories
mkdir -p storage/app/public/brands
```

---

## 8. Vendor Requests (`category_brand_requests`)

Vendors can request new categories be added. These are reviewed by admin in the "Vendor Requests" tab of the same page.

### Admin approval flow
1. Admin sees the request with `status = 'pending'`.
2. Admin can override the requested parent category before approving.
3. On approval: checks for duplicates, inserts into `categories2`, sets `has_children` on parent.
4. On rejection: sets `status = 'rejected'` and stores admin note.

---

## 9. Common Gotchas & Rules

| Rule | Detail |
|------|--------|
| Always sort by `menu_order ASC, name ASC` | Any query that lists categories for display must use this order |
| Top-level = `parent == 0` OR `parent IS NULL` | Both values exist in live data; always check both with `$c->parent == 0 \|\| $c->parent === null` |
| `has_children` must be synced manually | Laravel has no auto trigger; update it in the same query as insert/update/delete |
| Product counts come from `product_category` JOIN | Never use `categories2.count`; it's stale WooCommerce data |
| Duplicate check is case-insensitive | Use `whereRaw('LOWER(name) = ?', [strtolower($name)])` |
| Image path stored as relative | Store the relative path (e.g. `categories/abc.jpg`), not the full URL |
| Blade: `@continue` must be on its own line | `@if(...)@continue@endif` on one line causes a Blade parse error |
| Max 2 levels deep | The shop sidebar and admin UI only render parent → child; no grandchildren |
| Table name is `categories2` | Never reference a table called `categories` — it does not exist |

---

## 10. Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/WebController.php` | `shop()` method: hierarchy query, filter logic, variable packing |
| `app/Http/Controllers/Admin/AdminCategoryBrandController.php` | Full CRUD + image upload + vendor request approval |
| `resources/views/web/shop.blade.php` | Storefront sidebar: collapsible tree, active states, counts |
| `resources/views/admin/category-brand-requests/index.blade.php` | Admin tabbed UI: Categories / Brands / Vendor Requests |
| `routes/web.php` (lines ~180–186) | All admin category routes |
| `start.sh` | Creates storage symlink and `categories/` directory on every boot |
