# Timeline / Live Section Editor — Complete Reference

Everything about how the homepage layout system works in Ramo Store: data structure, database storage, admin UI, rendering, and how to add new widget types.

---

## 1. What It Is

The **Timeline Editor** (also called the **Live Section Editor** or **Horizon Layout**) is a drag-and-drop admin tool that controls every section rendered on the homepage (`/`).

- **Admin URL:** `/admin/live-preview` — split-screen editor with iframe live preview
- **Legacy URL:** `/admin/timeline` — older list-based editor (same data)
- **Save endpoint:** `POST /admin/timeline/save`

---

## 2. Database Storage

| Table        | Key               | Column  | Notes |
|-------------|-------------------|---------|-------|
| `app_configs` | `horizon_layout` | `value` | JSON array of section objects |
| `app_configs` | `horizon_layout` | `lang`  | Language code (`en`, `ar`, …). Default row is `en`. |

The layout is a **flat JSON array**. Each element is one section object. The array **order = render order** on the homepage.

### Read / Write via PHP

```php
// Read
$row = DB::table('app_configs')
    ->where('config_key', 'horizon_layout')
    ->where('lang', 'en')
    ->first();
$sections = json_decode($row->value, true);

// Write
DB::table('app_configs')
    ->where('config_key', 'horizon_layout')
    ->where('lang', 'en')
    ->update(['value' => json_encode($sections), 'updated_at' => now()]);
```

### Read / Write via psql (Replit shell)

```bash
# Read pretty-printed
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE \
  -t -c "SELECT value FROM app_configs WHERE config_key='horizon_layout';" \
  | python3 -c "import sys,json; [print(json.dumps(s,indent=2,ensure_ascii=False)) for s in json.load(sys.stdin)]"

# Overwrite the whole layout with a JSON file
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -p $PGPORT -U $PGUSER -d $PGDATABASE \
  -c "UPDATE app_configs SET value=\$\$(cat layout.json)\$\$ WHERE config_key='horizon_layout' AND lang='en';"
```

---

## 3. Section Object Schema

Every element in the JSON array must have a `layout` key. All other keys are layout-specific.

### 3a. `logo` — Top Navigation Bar

```json
{
  "layout": "logo",
  "showMenu": true,
  "showSearch": true,
  "showLogo": true,
  "showliked": true
}
```

| Field        | Type    | Default | Notes |
|-------------|---------|---------|-------|
| `showMenu`  | boolean | true    | Show nav menu links |
| `showSearch`| boolean | true    | Show search bar |
| `showLogo`  | boolean | true    | Show store logo |
| `showliked` | boolean | true    | Show wishlist icon |

---

### 3b. `category` — Category Icon Strip

```json
{
  "layout": "category",
  "name": "Men's Collection",
  "type": "icon",
  "wrap": false,
  "size": 1,
  "radius": 50,
  "items": [
    {
      "category": 18,
      "label": "Men",
      "image": "https://example.com/men.jpg",
      "colors": ["#3E6AB5", "#3E6AB5"]
    }
  ]
}
```

| Field    | Type    | Default | Notes |
|---------|---------|---------|-------|
| `name`  | string  | —       | Display name in editor only |
| `type`  | string  | `icon`  | Always `icon` currently |
| `wrap`  | boolean | false   | Allow items to wrap to next line |
| `size`  | number  | 1       | Scale multiplier for icons |
| `radius`| number  | 50      | Border-radius of icon circles (0–50) |

**Item fields:**

| Field      | Type     | Notes |
|-----------|----------|-------|
| `category` | integer  | FK → `categories2.id` |
| `label`    | string   | Text below icon |
| `image`    | string   | URL of icon image |
| `colors`   | string[] | Gradient pair `[from, to]` for icon background |

---

### 3c. `bannerImage` — Hero Banner Slider or Static Image

```json
{
  "layout": "bannerImage",
  "design": "default",
  "autoPlay": true,
  "radius": 2,
  "bannerHeight": 420,
  "items": [
    {
      "category": 29,
      "image": "https://example.com/banner.webp",
      "padding": 7
    }
  ]
}
```

| Field          | Type    | Default | Notes |
|---------------|---------|---------|-------|
| `design`       | string  | `default` | `default` = slider (if >1 item), `static` = stacked images |
| `autoPlay`     | boolean | true    | Auto-advance slides |
| `radius`       | number  | 2       | Border-radius of the slider wrapper in px |
| `bannerHeight` | number  | 420     | Image height in px (applied inline). Range: 80–900 |

**Item fields:**

| Field      | Type    | Notes |
|-----------|---------|-------|
| `category` | integer | FK → `categories2.id` — clicking banner goes to this category |
| `image`    | string  | URL of banner image |
| `padding`  | number  | Unused by renderer currently; kept for legacy compat |

> **Rendering rule:** Slider mode is used when `design !== 'static'` AND `count(items) > 1`. Otherwise falls back to stacked static images.

---

### 3d. `saleImages` — Horizontal Product Scroll

```json
{
  "layout": "saleImages",
  "category": 23,
  "headerText": "تسوق بالمظهر",
  "maxItemsToShow": 8,
  "productWidth": 130,
  "productConfig": {
    "imageRatio": 1.4,
    "borderRadius": 10
  }
}
```

| Field            | Type   | Default | Notes |
|-----------------|--------|---------|-------|
| `category`       | integer| —       | Products are pulled from this category |
| `headerText`     | string | —       | Section heading |
| `maxItemsToShow` | integer| 8       | Max products to render |
| `productWidth`   | number | 130     | Width of each product card in px |
| `productConfig.imageRatio` | number | 1.4 | Height/width ratio of product image |
| `productConfig.borderRadius` | number | 10 | Card border-radius |

---

### 3e. `twoColumn` — Product Grid

```json
{
  "layout": "twoColumn",
  "name": "مجموعات الرجال",
  "headerText": "تخفيضات اليوم ⚡️",
  "category": 23,
  "maxItemsToShow": 7,
  "productWidth": 200,
  "productConfig": {
    "borderRadius": 12.5,
    "showHeart": true,
    "imageRatio": 1.5,
    "layout": "grid"
  }
}
```

| Field            | Type    | Default | Notes |
|-----------------|---------|---------|-------|
| `name`           | string  | —       | Editor-only label |
| `headerText`     | string  | —       | Section heading shown on page |
| `category`       | integer | —       | Source category for products |
| `maxItemsToShow` | integer | 7       | Max products |
| `productWidth`   | number  | 200     | Card width in px |
| `productConfig.showHeart` | boolean | true | Show wishlist heart |
| `productConfig.imageRatio` | number | 1.5 | Image aspect ratio |
| `productConfig.borderRadius` | number | 12 | Card radius |
| `productConfig.layout` | string | `grid` | `grid` or `list` |

---

## 4. Controller: `AdminTimelineController`

**File:** `app/Http/Controllers/Admin/AdminTimelineController.php`

| Method          | Route                     | Description |
|----------------|---------------------------|-------------|
| `index()`       | `GET /admin/timeline`     | Legacy editor view |
| `livePreview()` | `GET /admin/live-preview` | Split-screen live editor |
| `save()`        | `POST /admin/timeline/save` | Validate & persist JSON |
| `searchProducts()` | `GET /admin/timeline/search` | AJAX product search |

### Save payload

```
POST /admin/timeline/save
Content-Type: application/x-www-form-urlencoded

lang=en&payload=[...JSON array...]
```

The controller validates `payload` is a valid JSON array, then does an upsert into `app_configs` for the given `lang`.

---

## 5. Rendering: `home.blade.php`

**File:** `resources/views/web/home.blade.php`

The homepage controller loads the JSON array and passes it as `$sections` to the Blade view. The view loops over sections:

```php
@foreach($sections as $si => $sec)
  @php $layout = $sec['layout'] ?? ''; @endphp

  @if($layout === 'logo') ... @endif
  @elseif($layout === 'category') ... @endif
  @elseif($layout === 'bannerImage') ... @endif
  @elseif($layout === 'saleImages') ... @endif
  @elseif($layout === 'twoColumn') ... @endif
@endforeach
```

### Solo preview mode (`?tl_solo=N`)

Append `?tl_solo=2` to the homepage URL to render **only** section index 2.  
Used by the Live Section Editor's "👁 View" button to isolate one widget in the iframe.

```php
// In home.blade.php
@if($tlSolo !== null && $si !== $tlSolo) @continue @endif
```

### Preview mode (`?tl_preview=1`)

Adds a top toolbar, hover overlays on each section with type label, and click-to-edit `postMessage` events back to the parent editor iframe.

---

## 6. Live Section Editor UI

**File:** `resources/views/admin/live_preview.blade.php`

### Layout
- **Left panel (360px):** Draggable widget cards + search filter + Save/Refresh buttons
- **Right panel (flex):** iframe showing `/?tl_preview=1`

### Key JS functions

| Function | Description |
|---------|-------------|
| `buildCard(sec, idx)` | Renders one widget card in the left panel |
| `buildEditor(sec, idx)` | Renders the expanded editor form for a widget type, appends responsive dim editor |
| `buildDimEditor(sec, idx)` | Appended to every widget — renders 🖥️ Windows / 📱 Android dimension tabs |
| `switchDimTab(idx, platform)` | Switches the active dimension tab for a widget (`'desktop'` or `'mobile'`) |
| `updateDimField(idx, platform, key, val)` | Writes to `sections[idx].responsive[platform][key]` |
| `getDim(sec, platform, key, fallback)` | Reads a responsive override or returns fallback |
| `updateField(idx, key, val)` | Updates `sections[idx][key]` in memory |
| `saveLayout()` | POSTs current `sections` JSON to `/admin/timeline/save` |
| `reloadIframe()` | Reloads the preview iframe |
| `viewWidget(idx)` | Reloads iframe with `?tl_solo=IDX` to show only that section |
| `filterWidgets()` | Filters left-panel cards by search input text |

### SortableJS drag-and-drop

Initialized on `#lpSectionList`. On `onEnd`, it reads the new card order from `data-idx` attributes and reorders the `sections` array in memory. A save is required to persist the new order.

### postMessage communication (iframe ↔ editor)

| Direction | Type | Payload | Purpose |
|----------|------|---------|---------|
| iframe → parent | `tlSectionClick` | `{ si: number }` | User clicked a section in preview |
| parent → iframe | `tlHighlight` | `{ si: number }` | Scroll + highlight a section |
| parent → iframe | `tlReload` | — | Trigger full iframe reload |

---

## 7. Adding a New Widget Type

1. **Add the section object** to the `horizon_layout` JSON in the DB (via psql or the editor's raw JSON).

2. **Add a renderer** in `home.blade.php` inside the `@foreach`:
   ```php
   @elseif($layout === 'myNewWidget')
     @php $title = $sec['title'] ?? 'My Widget'; @endphp
     <div class="my-widget">{{ $title }}</div>
   ```

3. **Add an editor form** in `live_preview.blade.php` inside `buildEditor()`:
   ```js
   else if (type === 'myNewWidget') {
     html = `<div class="form-grid">
       <div class="form-group"><label>Title</label>
         <input type="text" value="${sec.title||''}" onchange="updateField(${idx},'title',this.value)">
       </div>
     </div>`;
   }
   ```

4. **Add a card color** in the `COLORS` map (bottom of `home.blade.php` preview JS):
   ```js
   const COLORS = { ..., myNewWidget: '#06b6d4' };
   ```

5. **Add a label** in the `TYPE_LABEL` map:
   ```js
   const TYPE_LABEL = { ..., myNewWidget: 'My New Widget' };
   ```

---

## 8. Current Live Layout (8 Sections)

| Index | Layout       | Name / Purpose |
|-------|-------------|----------------|
| 0     | `logo`       | Top nav bar |
| 1     | `category`   | Main category icon strip (Arabic labels) |
| 2     | `bannerImage`| Hero banner slider |
| 3     | `saleImages` | "Shop by Look" horizontal product scroll |
| 4     | `twoColumn`  | "Today's Sales" product grid |
| 5     | `category`   | Men's Collection strip |
| 6     | `category`   | Women's Collection strip |
| 7     | `category`   | All Categories strip |

---

## 9. Responsive Dimensions (Windows 🖥️ / Android 📱)

Every widget editor now has a **Responsive Dimensions** section at the bottom, with two tabs:
- **🖥️ Windows** — dimension overrides applied to desktop/laptop browsers
- **📱 Android** — dimension overrides applied when a mobile/Android device is detected

### Data structure

Responsive overrides live in `section.responsive`:

```json
{
  "layout": "bannerImage",
  "bannerHeight": 420,
  "responsive": {
    "desktop": { "bannerHeight": 480, "radius": 4 },
    "mobile":  { "bannerHeight": 220, "radius": 0 }
  }
}
```

If a `responsive` override exists for the visitor's platform, it is **merged over** the base section values before rendering. The base values (without `responsive`) act as the fallback.

### Dimension fields per widget type

| Widget | Desktop / Mobile fields |
|--------|------------------------|
| `bannerImage` | Banner Height (px), Corner Radius |
| `category` | Icon Scale, Icon Radius |
| `categoryCards` | Columns, Card Height (px), Corner Radius, Max Items |
| `twoColumn`, `saleImages`, `seupermarketstars` | Card Width (px), Image Height (px), Corner Radius, Max Items |
| `spacer` | Height (px) |
| `topVendors` | Max Vendors |
| `trending`, `arrivals`, `recent`, `recommended` | Items to Show, Card Width (px) |
| All others | Padding Top (px), Padding Bottom (px) |

Structural widgets (`logo`, `announcement`, `flash`, `spacer`, `divider`) skip the generic padding fields.

### How rendering works

In `home.blade.php`, before rendering each section:

```php
$ua = request()->userAgent() ?? '';
$isMobile = preg_match('/Mobile|Android|iPhone|iPad.../i', $ua);
$tlPlatform = $isMobile ? 'mobile' : 'desktop';

// merge overrides
if (!empty($sec['responsive'][$tlPlatform])) {
    $sec = array_merge($sec, $sec['responsive'][$tlPlatform]);
}
```

This means any field set in `responsive.desktop` or `responsive.mobile` transparently overrides the section's base value for that device type.

### Adding responsive fields to a new widget

In `buildDimEditor()`, add a new `else if (type === 'myNewWidget')` branch with the fields array:

```js
} else if (type === 'myNewWidget') {
  fields = [
    { key:'myHeight', label:'Height (px)', type:'number', min:50, max:500, step:10, def:200 },
  ];
}
```

---

## 10. Gotchas

- The JSON array index is the render order. Reordering in the editor reshuffles indexes — always reference sections by `layout` + `name`, not by index in documentation.
- `bannerHeight` defaults to `420` if absent — existing rows without this key render at 420px.
- `design: "static"` forces stacked images even if there are multiple items. `design: "default"` uses slider only when `count(items) > 1`.
- The `lang` column in `app_configs` must match exactly when saving — a mismatch creates a duplicate row instead of updating.
- Saving from the editor always sends `lang=en` unless a lang switcher is used. If you add multi-language support, always filter by both `config_key` and `lang`.
