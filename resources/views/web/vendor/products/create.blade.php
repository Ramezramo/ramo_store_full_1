@extends('web.vendor.layout')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Add Product')

@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────── */
.section-card{background:#fff;border:1px solid var(--light);border-radius:12px;padding:24px;margin-bottom:20px}
.section-card h2{font-size:13px;font-weight:700;color:var(--dark);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--light);display:flex;align-items:center;gap:8px}
.vs-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 20px}
.vs-form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 20px}
.col-span-2{grid-column:span 2}

/* ── Categories ──────────────────────────────────────── */
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:6px}
.cat-label{display:flex;align-items:center;gap:7px;padding:7px 10px;border-radius:7px;border:1px solid var(--light);cursor:pointer;font-size:13px;transition:.12s}
.cat-label:hover{border-color:var(--orange);background:#fff8f5}
.cat-label input[type=checkbox]{accent-color:var(--orange);width:14px;height:14px;flex-shrink:0}
.cat-label input[type=checkbox]:checked + span{color:var(--orange);font-weight:600}
.cat-section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin:12px 0 6px;grid-column:1/-1}

/* ── Images ──────────────────────────────────────────── */
.img-drop{border:2px dashed var(--light);border-radius:10px;background:#fafaf8;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;padding:20px;cursor:pointer;transition:.15s;min-height:120px;text-align:center}
.img-drop:hover{border-color:var(--orange);background:#fff8f5}
.img-drop.has-file{border-style:solid;border-color:var(--orange);background:#fff8f5}
.img-drop svg{opacity:.4}
.img-drop span{font-size:12px;color:var(--mid)}
.img-preview-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.img-preview-item{position:relative;width:80px;height:80px;border-radius:8px;overflow:hidden;border:1px solid var(--light);background:#f3f4f6}
.img-preview-item img{width:100%;height:100%;object-fit:cover}
.img-size-badge{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);color:#fff;font-size:9px;font-weight:700;text-align:center;padding:2px 3px;line-height:1.3;backdrop-filter:blur(2px)}
.thumb-info{display:flex;align-items:center;gap:10px;margin-top:8px;padding:8px 12px;background:#f9fafb;border:1px solid var(--light);border-radius:8px}
.thumb-info img{width:52px;height:52px;object-fit:cover;border-radius:6px;border:1px solid var(--light);flex-shrink:0}
.thumb-info-text{font-size:11px;color:var(--mid);line-height:1.6}
.thumb-info-text strong{color:var(--dark);font-size:12px}

/* ── Related products ────────────────────────────────── */
.rp-search-wrap{position:relative}
.rp-dropdown{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--light);border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.08);z-index:50;max-height:200px;overflow-y:auto;display:none}
.rp-dropdown.open{display:block}
.rp-option{padding:8px 12px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px}
.rp-option:hover{background:#f5f5f2}
.rp-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.rp-tag{display:inline-flex;align-items:center;gap:6px;background:#f3f4f6;border-radius:20px;padding:4px 10px;font-size:12px;font-weight:600}
.rp-tag button{background:none;border:none;cursor:pointer;color:var(--mid);font-size:14px;line-height:1;padding:0}
.rp-tag button:hover{color:var(--red)}
textarea.vs-input{resize:vertical;min-height:100px}

/* ── Variation builder (Color+Size) ─────────────────── */
.var-toggle-row{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f9fafb;border:1px solid var(--light);border-radius:8px;margin-bottom:18px;cursor:pointer}
.var-toggle-row input[type=checkbox]{accent-color:var(--orange);width:16px;height:16px;cursor:pointer;flex-shrink:0}
.var-toggle-row .vt-label{font-size:13px;font-weight:600;color:var(--dark)}
.var-toggle-row .vt-sub{font-size:12px;color:var(--mid)}

.color-row{border:1px solid var(--light);border-radius:10px;padding:16px;margin-bottom:12px;background:#fafaf8;transition:.15s}
.color-row.is-main{border-color:var(--orange);background:#fff8f5}
.color-row-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px}
.color-row-badge{font-size:11px;font-weight:700;color:var(--orange);background:#fff0e6;border:1px solid #f5c9a8;border-radius:20px;padding:2px 10px}
.color-row-remove{background:none;border:1px solid #fee2e2;border-radius:6px;color:#dc2626;font-size:12px;font-weight:600;padding:4px 10px;cursor:pointer;transition:.12s}
.color-row-remove:hover{background:#fee2e2}

.size-tags-wrap{display:flex;flex-wrap:wrap;gap:6px;align-items:center;padding:8px;background:#f3f4f6;border-radius:8px;min-height:38px}
.size-tag{display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid var(--light);border-radius:16px;padding:3px 10px;font-size:12px;font-weight:600}
.size-tag-remove{background:none;border:none;cursor:pointer;color:var(--mid);font-size:13px;padding:0;line-height:1}
.size-tag-remove:hover{color:var(--red)}
.size-add-input{border:none;background:transparent;font-size:12px;outline:none;min-width:60px;flex:1}

.price-map-table{width:100%;border-collapse:collapse;margin-top:8px;font-size:12px}
.price-map-table th{background:#f3f4f6;padding:6px 10px;text-align:left;font-weight:600;color:var(--mid);border:1px solid var(--light)}
.price-map-table td{padding:5px 8px;border:1px solid var(--light)}
.price-map-table input{width:100%;border:none;background:transparent;font-size:12px;outline:none;padding:2px 4px}
.price-map-table input:focus{background:#fff8f5;border-radius:4px}

.add-color-btn{display:inline-flex;align-items:center;gap:6px;background:none;border:1px dashed var(--orange);border-radius:8px;color:var(--orange);font-size:13px;font-weight:600;padding:8px 16px;cursor:pointer;transition:.12s;margin-top:4px}
.add-color-btn:hover{background:#fff8f5}

/* ── Translations ────────────────────────────────────── */
.lang-tab-bar{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:12px}
.lang-tab{padding:5px 14px;border:1px solid var(--light);border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;background:#fff;transition:.12s;display:flex;align-items:center;gap:5px}
.lang-tab.active{border-color:var(--orange);color:var(--orange);background:#fff8f5}
.lang-tab .lt-remove{background:none;border:none;cursor:pointer;color:var(--mid);font-size:13px;padding:0;line-height:1;margin-left:2px}
.lang-tab .lt-remove:hover{color:var(--red)}
.lang-add-wrap{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
.translation-panel{display:none}
.translation-panel.active{display:block}

/* ── Attributes ──────────────────────────────────────── */
.attr-row{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid var(--light);border-radius:8px;margin-bottom:8px;background:#fafaf8}
.attr-row-del{background:none;border:1px solid #fee2e2;border-radius:6px;color:#dc2626;font-size:12px;padding:3px 8px;cursor:pointer;white-space:nowrap;flex-shrink:0}
.attr-row-del:hover{background:#fee2e2}

/* ── Tags ────────────────────────────────────────────── */
.tag-pill{display:inline-flex;align-items:center;gap:4px;background:#f3f4f6;border:1px solid var(--light);border-radius:16px;padding:3px 10px;font-size:12px;font-weight:600;margin:3px}
.tag-pill-remove{background:none;border:none;cursor:pointer;color:var(--mid);font-size:13px;padding:0;line-height:1}
.tag-pill-remove:hover{color:var(--red)}

/* ── Toggle switch ───────────────────────────────────── */
.toggle-switch{display:flex;align-items:center;gap:8px;cursor:pointer}
.toggle-switch input{display:none}
.toggle-knob{width:38px;height:22px;background:#d1d5db;border-radius:11px;position:relative;transition:.2s}
.toggle-knob::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked + .toggle-knob{background:var(--orange)}
.toggle-switch input:checked + .toggle-knob::after{left:19px}
.toggle-label{font-size:13px;font-weight:600;color:var(--dark)}
.toggle-sub{font-size:12px;color:var(--mid)}

.var-section-sep{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin:16px 0 8px}

/* ── Section error state ─────────────────────────────────── */
.section-has-error{border-left:3px solid var(--red)!important}
.section-err-badge{
  display:inline-flex;align-items:center;justify-content:center;
  width:18px;height:18px;border-radius:50%;
  background:var(--red);color:#fff;font-size:11px;font-weight:700;
  margin-left:8px;vertical-align:middle;flex-shrink:0
}
/* ── Color-row variation error ───────────────────────────── */
.color-row.has-error{border-color:var(--red)!important;background:#fff8f8}
.color-row.has-error .color-row-header{border-color:var(--red)}
</style>
@endpush

@section('content')

@php
  $isEdit        = isset($product);
  $formAction    = $isEdit ? route('vendor.products.update', $product->id) : route('vendor.products.store');
  $images        = $images ?? [];
  $selectedCats  = $selectedCats ?? [];
  $relatedIds    = $relatedIds ?? [];
  $relatedProds  = $relatedProds ?? collect();
  $variation     = $variation ?? null;
  $dbVariations  = $dbVariations ?? collect();
  $hasVariations = $hasVariations ?? false;
  $imgBase       = \Illuminate\Support\Facades\Storage::url('');
  $translations  = $translations ?? [];
  $tags          = $tags ?? [];
  $attributes    = $attributes ?? [];
  $whatsappData  = $whatsappData ?? [];
  $unitType      = $unitType ?? 'piece';
  $unitAmount    = $unitAmount ?? 1;
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
  <a href="{{ route('vendor.products') }}" style="color:var(--mid);font-size:13px">← Back to Products</a>
  <span style="color:var(--light)">|</span>
  <div style="font-size:22px;font-weight:800">{{ $isEdit ? 'Edit: '.$product->name : 'Add New Product' }}</div>
  @if(!empty($isDebug) && !$isEdit)
  <button type="button" onclick="fillFakeData()"
    style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#f3f4f6;border:1.5px dashed #9ca3af;border-radius:8px;font-size:12px;font-weight:700;color:#6b7280;cursor:pointer;transition:.15s"
    onmouseover="this.style.borderColor='#e85d26';this.style.color='#e85d26'"
    onmouseout="this.style.borderColor='#9ca3af';this.style.color='#6b7280'"
    title="Debug mode only — fills the form with random test data">
    🧪 Fill with Test Data
  </button>
  @endif
</div>

@if($errors->any())
@php
  // Map raw Laravel field keys → readable labels
  $fieldLabels = [
    'name'                  => 'Product name (English)',
    'status'                => 'Status',
    'regular_price'         => 'Regular price',
    'sale_price'            => 'Sale price',
    'stock_quantity'        => 'Stock quantity',
    'discount_percentage'   => 'Discount percentage',
    'minimum_order_qty'     => 'Minimum order quantity',
    'max_orders_per_person' => 'Max orders per person',
    'unit'                  => 'Unit type',
    'unit_amount'           => 'Unit amount',
    'sku'                   => 'SKU',
    'brand_id'              => 'Brand',
    'product_type'          => 'Product type',
    'short_description'     => 'Short description',
    'thumbnail'             => 'Main thumbnail image',
    'whatsapp_number'       => 'WhatsApp number',
    'tags_input'            => 'Tags',
    'related_ids'           => 'Related products',
  ];
  $friendlyErrors = [];
  foreach ($errors->messages() as $field => $msgs) {
    // colors.N.name → "Color #N+1 — name is required"
    if (preg_match('/^colors\.(\d+)\.(\w+)/', $field, $m)) {
      $n = (int)$m[1] + 1;
      $sub = ['name'=>'name','sizes'=>'sizes (at least one required)','price_map'=>'pricing','stock'=>'stock','sale_price'=>'sale price override'][$m[2]] ?? $m[2];
      $friendlyErrors[] = "Color #$n — $sub: " . $msgs[0];
      continue;
    }
    // translations.N.name etc
    if (preg_match('/^translations\.(\d+)\.(\w+)/', $field, $m)) {
      $n = (int)$m[1] + 1;
      $sub = ['name'=>'name','locale'=>'language','description'=>'description'][$m[2]] ?? $m[2];
      $friendlyErrors[] = "Translation #$n — $sub: " . $msgs[0];
      continue;
    }
    // prod_attributes.N.name etc
    if (preg_match('/^prod_attributes\.(\d+)\.(\w+)/', $field, $m)) {
      $n = (int)$m[1] + 1;
      $sub = ['name'=>'name','values'=>'values'][$m[2]] ?? $m[2];
      $friendlyErrors[] = "Attribute #$n — $sub: " . $msgs[0];
      continue;
    }
    $label = $fieldLabels[$field] ?? ucfirst(str_replace(['_','.'], [' ',' '], $field));
    $friendlyErrors[] = $label . ': ' . $msgs[0];
  }

  // Detect which sections have errors
  $sectionErrors = [];
  foreach ($errors->keys() as $k) {
    if (in_array($k, ['name','status','short_description','description','sku','brand_id','unit','unit_amount','product_type'])) $sectionErrors['basic'] = true;
    if (str_starts_with($k,'translations')) $sectionErrors['translations'] = true;
    if (in_array($k, ['regular_price','sale_price','stock_quantity','discount_percentage','minimum_order_qty','max_orders_per_person']) || str_starts_with($k,'colors')) $sectionErrors['pricing'] = true;
    if (str_starts_with($k,'prod_attributes')) $sectionErrors['attributes'] = true;
    if (in_array($k, ['thumbnail','other_images','natural_images'])) $sectionErrors['images'] = true;
  }
@endphp
  <div class="vs-alert vs-alert-error" id="error-summary" style="margin-bottom:16px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div style="flex:1">
      <strong>{{ count($friendlyErrors) }} problem{{ count($friendlyErrors) !== 1 ? 's' : '' }} need{{ count($friendlyErrors) === 1 ? 's' : '' }} fixing before you can save:</strong>
      <ul style="margin:6px 0 0 16px;font-size:12px;line-height:1.7">
        @foreach($friendlyErrors as $fe)<li>{{ $fe }}</li>@endforeach
      </ul>
    </div>
  </div>
@endif
@php $sectionErrors = $sectionErrors ?? []; @endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="product-form">
  @csrf

{{-- ── BASIC INFO ──────────────────────────────────────────────────── --}}
<div class="section-card {{ !empty($sectionErrors['basic']) ? 'section-has-error' : '' }}" id="section-basic">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    Basic Information
    @if(!empty($sectionErrors['basic']))<span class="section-err-badge">!</span>@endif
  </h2>

  <div class="vs-form-group">
    <label class="vs-label">Product Name (English) <span style="color:var(--red)">*</span></label>
    <input type="text" name="name"
           value="{{ old('name', $product->name ?? '') }}"
           class="vs-input {{ $errors->has('name') ? 'err' : '' }}"
           placeholder="Enter product name in English" required maxlength="500">
    @error('name')<div class="vs-err">{{ $message }}</div>@enderror
  </div>

  <div class="vs-form-grid">
    <div class="vs-form-group">
      <label class="vs-label">Status</label>
      <select name="status" class="vs-input">
        <option value="draft"   {{ old('status', $product->status ?? 'draft') === 'draft'   ? 'selected' : '' }}>Draft (hidden)</option>
        <option value="publish" {{ old('status', $product->status ?? 'draft') === 'publish' ? 'selected' : '' }}>Published</option>
      </select>
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Product Type</label>
      <select name="product_type" class="vs-input" id="product-type-select" onchange="toggleProductType(this.value)">
        <option value="physical" {{ old('product_type', $product->product_type ?? 'physical') === 'physical' ? 'selected' : '' }}>Physical (shipped)</option>
        <option value="digital"  {{ old('product_type', $product->product_type ?? 'physical') === 'digital'  ? 'selected' : '' }}>Digital (download)</option>
      </select>
    </div>
  </div>

  <div class="vs-form-grid">
    <div class="vs-form-group" style="grid-column:span 2">
      <label class="vs-label">Product Card Buttons</label>
      <select name="button_mode" class="vs-input">
        <option value="both"         {{ old('button_mode', $product->button_mode ?? 'both') === 'both'         ? 'selected' : '' }}>Show both "Add to Cart" and "See Details"</option>
        <option value="cart_only"    {{ old('button_mode', $product->button_mode ?? 'both') === 'cart_only'    ? 'selected' : '' }}>Show "Add to Cart" only</option>
        <option value="details_only" {{ old('button_mode', $product->button_mode ?? 'both') === 'details_only' ? 'selected' : '' }}>Show "See Details" only</option>
      </select>
      <div style="font-size:11px;color:var(--mid);margin-top:4px">Controls which action buttons appear on the product listing card.</div>
    </div>
  </div>

  <div class="vs-form-grid">
    <div class="vs-form-group">
      <label class="vs-label">Unit Type <span style="color:var(--mid);font-weight:400;text-transform:none">e.g. piece, kg, box</span></label>
      <select name="unit" class="vs-input">
        @foreach(['piece','kg','g','liter','ml','meter','cm','box','pack','set'] as $u)
          <option value="{{ $u }}" {{ old('unit', $unitType) === $u ? 'selected' : '' }}>{{ $u }}</option>
        @endforeach
      </select>
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Unit Amount</label>
      <input type="number" name="unit_amount" step="0.01" min="0.01"
             value="{{ old('unit_amount', $unitAmount) }}"
             class="vs-input" placeholder="e.g. 1, 0.5, 500">
    </div>
  </div>

  <div class="vs-form-group">
    <label class="vs-label">Short Description (English)</label>
    <textarea name="short_description" class="vs-input" style="min-height:72px" maxlength="1000"
              placeholder="Brief summary shown on listing cards">{{ old('short_description', $product->short_description ?? '') }}</textarea>
    @error('short_description')<div class="vs-err">{{ $message }}</div>@enderror
  </div>

  <div class="vs-form-group">
    <label class="vs-label">Full Description (English)</label>
    <textarea name="description" class="vs-input" style="min-height:140px"
              placeholder="Detailed product description">{{ old('description', $product->description ?? '') }}</textarea>
  </div>
</div>

{{-- ── TRANSLATIONS ────────────────────────────────────────────────── --}}
<div class="section-card {{ !empty($sectionErrors['translations']) ? 'section-has-error' : '' }}" id="section-translations">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8l6 6"/><path d="M4 14l6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="M22 22l-5-10-5 10"/><path d="M14 18h6"/></svg>
    Translations
    @if(!empty($sectionErrors['translations']))<span class="section-err-badge">!</span>@endif
    <span style="font-size:11px;font-weight:400;color:var(--mid);margin-left:4px">— add product info in other languages</span>
  </h2>

  {{-- Language tabs --}}
  <div class="lang-tab-bar" id="lang-tab-bar">
    <div class="lang-tab active" data-lang="en" onclick="switchLangTab('en')">🇬🇧 English (main)</div>
    @foreach($translations as $i => $tr)
      <div class="lang-tab" data-lang="{{ $tr['locale'] }}" onclick="switchLangTab('{{ $tr['locale'] }}')">
        <span>{{ strtoupper($tr['locale']) }}</span>
        <button type="button" class="lt-remove" onclick="removeLangTab(event,'{{ $tr['locale'] }}')">×</button>
      </div>
    @endforeach
  </div>

  <div class="lang-add-wrap">
    <select id="lang-add-select" class="vs-input" style="width:auto;min-width:180px">
      <option value="">— Add language —</option>
      <option value="ar">🇦🇪 Arabic (ar)</option>
      <option value="fr">🇫🇷 French (fr)</option>
      <option value="de">🇩🇪 German (de)</option>
      <option value="es">🇪🇸 Spanish (es)</option>
      <option value="it">🇮🇹 Italian (it)</option>
    </select>
    <button type="button" class="vs-btn vs-btn-ghost" onclick="addLangTab()">+ Add</button>
  </div>

  {{-- English panel (main, not stored in translations array) --}}
  <div class="translation-panel active" id="tr-panel-en">
    <div style="font-size:12px;color:var(--mid);padding:10px 14px;background:#f9fafb;border-radius:8px">
      English content is entered in the Basic Information section above.
    </div>
  </div>

  {{-- Dynamic translation panels --}}
  <div id="tr-panels-container">
    @foreach($translations as $i => $tr)
      <div class="translation-panel" id="tr-panel-{{ $tr['locale'] }}">
        <input type="hidden" name="translations[{{ $i }}][locale]" value="{{ $tr['locale'] }}">
        <div class="vs-form-group">
          <label class="vs-label">Product Name ({{ strtoupper($tr['locale']) }}) <span style="color:var(--red)">*</span></label>
          <input type="text" name="translations[{{ $i }}][name]"
                 value="{{ old("translations.$i.name", $tr['name']) }}"
                 class="vs-input" placeholder="Product name in {{ strtoupper($tr['locale']) }}" maxlength="500">
        </div>
        <div class="vs-form-group">
          <label class="vs-label">Short Description ({{ strtoupper($tr['locale']) }})</label>
          <textarea name="translations[{{ $i }}][short_description]"
                    class="vs-input" style="min-height:72px"
                    placeholder="Short description in {{ strtoupper($tr['locale']) }}">{{ old("translations.$i.short_description", $tr['short_description'] ?? '') }}</textarea>
        </div>
        <div class="vs-form-group">
          <label class="vs-label">Full Description ({{ strtoupper($tr['locale']) }})</label>
          <textarea name="translations[{{ $i }}][description]"
                    class="vs-input" style="min-height:120px"
                    placeholder="Full description in {{ strtoupper($tr['locale']) }}">{{ old("translations.$i.description", $tr['description']) }}</textarea>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- ── PRICING & VARIATIONS ────────────────────────────────────────── --}}
<div class="section-card {{ !empty($sectionErrors['pricing']) ? 'section-has-error' : '' }}" id="section-pricing">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
    Pricing, Stock & Variations
    @if(!empty($sectionErrors['pricing']))<span class="section-err-badge">!</span>@endif
  </h2>

  {{-- SKU & Brand --}}
  <div class="vs-form-grid" style="margin-bottom:4px">
    <div class="vs-form-group">
      <label class="vs-label">SKU</label>
      @error('sku')<div class="vs-err">{{ $message }}</div>@enderror
      <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
             class="vs-input" placeholder="e.g. SHIRT-RED-XL-001" maxlength="100">
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Brand</label>
      <select name="brand_id" class="vs-input">
        <option value="">— No brand —</option>
        @foreach($brands as $b)
          <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
      </select>
    </div>
  </div>

  {{-- Discount --}}
  <div class="vs-form-grid" style="margin-bottom:4px">
    <div class="vs-form-group">
      <label class="vs-label">Discount Percentage <span style="color:var(--mid);font-weight:400;text-transform:none">0 = no discount, max 80</span></label>
      <input type="number" name="discount_percentage" step="0.01" min="0" max="80.99"
             value="{{ old('discount_percentage', $product->discount_percentage ?? 0) }}"
             class="vs-input {{ $errors->has('discount_percentage') ? 'err' : '' }}"
             placeholder="0" oninput="updateDiscountHint(this.value)">
      <div id="discount-hint" style="font-size:11px;color:var(--orange);margin-top:4px"></div>
      @error('discount_percentage')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Min Order Qty</label>
      <input type="number" name="minimum_order_qty" min="1" max="100"
             value="{{ old('minimum_order_qty', $product->minimum_order_qty ?? 1) }}"
             class="vs-input {{ $errors->has('minimum_order_qty') ? 'err' : '' }}"
             placeholder="1">
      @error('minimum_order_qty')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="vs-form-grid" style="margin-bottom:16px">
    <div class="vs-form-group">
      <label class="vs-label">Max Orders Per Person <span style="color:var(--mid);font-weight:400;text-transform:none">0 = unlimited</span></label>
      <input type="number" name="max_orders_per_person" min="0" max="100"
             value="{{ old('max_orders_per_person', $product->max_orders_per_person ?? 0) }}"
             class="vs-input {{ $errors->has('max_orders_per_person') ? 'err' : '' }}"
             placeholder="0 (unlimited)">
      @error('max_orders_per_person')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
    <div></div>
  </div>

  {{-- Has-variations toggle --}}
  <label class="var-toggle-row">
    <input type="checkbox" name="has_variations" id="has-variations-toggle" value="1"
           {{ old('has_variations', $hasVariations) ? 'checked' : '' }}
           onchange="toggleVariations(this.checked)">
    <div>
      <div class="vt-label">This product has multiple variations (Color + Sizes)</div>
      <div class="vt-sub">Define colors, sizes, per-size pricing and stock levels</div>
    </div>
  </label>

  {{-- ── SIMPLE PRODUCT ──────────────────────────────────────────────── --}}
  <div id="simple-panel">
    <div class="vs-form-grid-3">
      <div class="vs-form-group">
        <label class="vs-label">Regular Price (EGP) <span style="color:var(--red)">*</span></label>
        <input type="number" name="regular_price" id="simple-regular-price" step="0.01" min="0"
               value="{{ old('regular_price', $variation->regular_price ?? '') }}"
               class="vs-input {{ $errors->has('regular_price') ? 'err' : '' }}"
               placeholder="0.00" oninput="updateSimpleEffectivePrice()">
        @error('regular_price')<div class="vs-err">{{ $message }}</div>@enderror
      </div>
      <div class="vs-form-group">
        <label class="vs-label">Sale Price (EGP) <span style="color:var(--mid);font-weight:400;text-transform:none">optional — overridden by % discount</span></label>
        <input type="number" name="sale_price" id="simple-sale-price" step="0.01" min="0"
               value="{{ old('sale_price', $variation->sale_price ?? '') }}"
               class="vs-input" placeholder="Leave blank if no discount"
               oninput="updateSimpleEffectivePrice()">
      </div>
      <div class="vs-form-group">
        <label class="vs-label">Stock Quantity <span style="color:var(--red)">*</span></label>
        <input type="number" name="stock_quantity" id="simple-stock" min="0"
               value="{{ old('stock_quantity', $variation->stock_quantity ?? 0) }}"
               class="vs-input {{ $errors->has('stock_quantity') ? 'err' : '' }}"
               placeholder="0">
        @error('stock_quantity')<div class="vs-err">{{ $message }}</div>@enderror
      </div>
    </div>
    <div id="simple-eff-preview" style="display:none;font-size:12px;color:var(--orange);margin-top:-4px;margin-bottom:8px;font-weight:600"></div>
  </div>

  {{-- ── VARIABLE PRODUCT (Color + Sizes) ───────────────────────────── --}}
  <div id="var-panel" style="display:none">
    <div class="var-section-sep">Color Variations</div>
    <p style="font-size:12px;color:var(--mid);margin-bottom:14px">
      Each color can have multiple sizes with individual pricing and stock. The first color is the default displayed variation.
    </p>

    <div id="color-rows"></div>

    <button type="button" class="add-color-btn" onclick="addColorRow()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Color
    </button>
  </div>
</div>

{{-- ── PRODUCT ATTRIBUTES ──────────────────────────────────────────── --}}
<div class="section-card {{ !empty($sectionErrors['attributes']) ? 'section-has-error' : '' }}" id="section-attributes">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
    Product Attributes
    @if(!empty($sectionErrors['attributes']))<span class="section-err-badge">!</span>@endif
    <span style="font-size:11px;font-weight:400;color:var(--mid);margin-left:4px">— filterable specs (e.g. Material: Cotton, Wool)</span>
  </h2>

  <div id="attr-rows">
    @foreach($attributes as $ai => $attr)
      <div class="attr-row" id="attr-row-{{ $ai }}">
        <div style="flex:1">
          <input type="text" name="prod_attributes[{{ $ai }}][name]"
                 value="{{ $attr['name'] }}"
                 class="vs-input" placeholder="Attribute name (e.g. Material)" style="margin-bottom:6px">
          <input type="text" name="prod_attributes[{{ $ai }}][values]"
                 value="{{ implode(', ', (array)($attr['values'] ?? [])) }}"
                 class="vs-input" placeholder="Values separated by commas (e.g. Cotton, Polyester, Wool)">
        </div>
        <button type="button" class="attr-row-del" onclick="this.closest('.attr-row').remove()">× Remove</button>
      </div>
    @endforeach
  </div>

  <button type="button" class="add-color-btn" style="margin-top:4px" onclick="addAttrRow()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Attribute
  </button>
</div>

{{-- ── TAGS ────────────────────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
    Tags
    <span style="font-size:11px;font-weight:400;color:var(--mid);margin-left:4px">— for search & discovery</span>
  </h2>

  <input type="hidden" name="tags_input" id="tags-hidden" value="{{ old('tags_input', implode(', ', $tags)) }}">
  <div style="display:flex;flex-wrap:wrap;align-items:center;gap:4px;padding:8px;border:1px solid var(--light);border-radius:8px;min-height:44px" id="tags-visual">
    @foreach($tags as $tag)
      <span class="tag-pill" data-tag="{{ $tag }}">
        {{ $tag }}
        <button type="button" class="tag-pill-remove" onclick="removeTag('{{ $tag }}')">×</button>
      </span>
    @endforeach
    <input type="text" id="tag-input" placeholder="Type and press Enter or comma" style="border:none;outline:none;font-size:13px;flex:1;min-width:140px;padding:4px"
           onkeydown="handleTagInput(event)">
  </div>
  <div style="font-size:11px;color:var(--mid);margin-top:5px">Press Enter or comma to add a tag. Examples: summer, trending, gift</div>
</div>

{{-- ── CATEGORIES ─────────────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
    Categories
  </h2>

  @php
    $topLevel = $categories->where('parent', 0)->values();
    $children = $categories->where('parent', '!=', 0)->groupBy('parent');
  @endphp

  <div class="cat-grid">
    @foreach($topLevel as $parent)
      <div class="cat-section-title">{{ $parent->name }}</div>
      <label class="cat-label">
        <input type="checkbox" name="categories[]" value="{{ $parent->id }}"
               {{ in_array($parent->id, old('categories', $selectedCats)) ? 'checked' : '' }}>
        <span>{{ $parent->name }}</span>
      </label>
      @foreach($children->get($parent->id, collect()) as $child)
        <label class="cat-label">
          <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                 {{ in_array($child->id, old('categories', $selectedCats)) ? 'checked' : '' }}>
          <span>{{ $child->name }}</span>
        </label>
      @endforeach
    @endforeach
    @php
      $listedParentIds = $topLevel->pluck('id')->toArray();
      $orphaned = $categories->where('parent', '!=', 0)->filter(fn($c) => !in_array($c->parent, $listedParentIds));
    @endphp
    @if($orphaned->count())
      <div class="cat-section-title">Other</div>
      @foreach($orphaned as $cat)
        <label class="cat-label">
          <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                 {{ in_array($cat->id, old('categories', $selectedCats)) ? 'checked' : '' }}>
          <span>{{ $cat->name }}</span>
        </label>
      @endforeach
    @endif
  </div>
</div>

{{-- ── IMAGES ─────────────────────────────────────────────────────── --}}
<div class="section-card {{ !empty($sectionErrors['images']) ? 'section-has-error' : '' }}" id="section-images">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Product Images
    @if(!empty($sectionErrors['images']))<span class="section-err-badge">!</span>@endif
  </h2>

  <div class="vs-form-group">
    <label class="vs-label">Main Thumbnail <span style="color:var(--mid);font-weight:400;text-transform:none">— shown on listing cards</span></label>
    @if(!empty($images['thumbnail']))
      <div style="margin-bottom:8px">
        <img src="{{ $imgBase . ltrim($images['thumbnail'],'/') }}" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--light)">
        <div style="font-size:11px;color:var(--mid);margin-top:4px">Upload a new image to replace</div>
      </div>
    @endif
    <label for="thumbnail-input" class="img-drop" id="thumb-drop">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <span id="thumb-label">Click to upload thumbnail · JPG / PNG / WebP · max 5 MB</span>
    </label>
    <input type="file" id="thumbnail-input" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display:none"
           onchange="previewSingle(this,'thumb-drop','thumb-label')">
    @error('thumbnail')<div class="vs-err">{{ $message }}</div>@enderror
  </div>

  <div class="vs-form-group">
    <label class="vs-label">Gallery Images <span style="color:var(--mid);font-weight:400;text-transform:none">— multiple allowed</span></label>
    @if(!empty($images['other_images']))
      <div class="img-preview-row" style="margin-bottom:8px">
        @foreach($images['other_images'] as $oi)
          <div class="img-preview-item"><img src="{{ $imgBase . ltrim($oi,'/') }}" alt=""></div>
        @endforeach
      </div>
      <div style="font-size:11px;color:var(--mid);margin-bottom:6px">Upload new images to replace existing gallery</div>
    @endif
    <label for="other-input" class="img-drop" id="other-drop">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      <span id="other-label">Click to upload gallery images (select multiple)</span>
    </label>
    <input type="file" id="other-input" name="other_images[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none"
           onchange="previewMulti(this,'other-drop','other-label','other-previews')">
    <div class="img-preview-row" id="other-previews"></div>
  </div>

  <div class="vs-form-group">
    <label class="vs-label">Lifestyle / Natural Images</label>
    @if(!empty($images['natural_images']))
      <div class="img-preview-row" style="margin-bottom:8px">
        @foreach($images['natural_images'] as $ni)
          <div class="img-preview-item"><img src="{{ $imgBase . ltrim($ni,'/') }}" alt=""></div>
        @endforeach
      </div>
    @endif
    <label for="natural-input" class="img-drop" id="natural-drop">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      <span id="natural-label">Click to upload lifestyle images</span>
    </label>
    <input type="file" id="natural-input" name="natural_images[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none"
           onchange="previewMulti(this,'natural-drop','natural-label','natural-previews')">
    <div class="img-preview-row" id="natural-previews"></div>
  </div>
</div>

{{-- ── WHATSAPP ─────────────────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
    WhatsApp Contact
    <span style="font-size:11px;font-weight:400;color:var(--mid);margin-left:4px">— allow customers to contact you directly</span>
  </h2>

  <label class="toggle-switch" style="margin-bottom:14px">
    <input type="checkbox" name="whatsapp_available" id="wa-toggle" value="1"
           {{ old('whatsapp_available', !empty($whatsappData['available'])) ? 'checked' : '' }}
           onchange="document.getElementById('wa-number-wrap').style.display=this.checked?'':'none'">
    <span class="toggle-knob"></span>
    <div>
      <div class="toggle-label">Enable WhatsApp contact for this product</div>
      <div class="toggle-sub">Customers can message you directly about this product</div>
    </div>
  </label>

  <div id="wa-number-wrap" style="display:{{ old('whatsapp_available', !empty($whatsappData['available'])) ? '' : 'none' }}">
    <div class="vs-form-group">
      <label class="vs-label">WhatsApp Number <span style="color:var(--mid);font-weight:400;text-transform:none">e.g. 01012345678</span></label>
      <input type="text" name="whatsapp_number"
             value="{{ old('whatsapp_number', $whatsappData['number'] ?? '') }}"
             class="vs-input" placeholder="01012345678" maxlength="20">
    </div>
  </div>
</div>

{{-- ── RELATED PRODUCTS ────────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
    Related Products
  </h2>
  <div style="font-size:12px;color:var(--mid);margin-bottom:12px">Products shown as recommendations on this product's page.</div>

  <input type="hidden" name="related_ids" id="related-ids-input"
         value="{{ old('related_ids', implode(',', $relatedIds)) }}">

  <div class="rp-search-wrap">
    <input type="text" id="rp-search" class="vs-input" placeholder="Search your products to add…" autocomplete="off">
    <div class="rp-dropdown" id="rp-dropdown"></div>
  </div>

  <div class="rp-tags" id="rp-tags">
    @foreach($relatedProds as $rp)
      <span class="rp-tag" data-id="{{ $rp->id }}">
        {{ Str::limit($rp->name, 35) }}
        <button type="button" onclick="removeRelated({{ $rp->id }})">×</button>
      </span>
    @endforeach
  </div>
</div>

{{-- ── SAVE ─────────────────────────────────────────────────────────── --}}
<div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:40px">
  <a href="{{ route('vendor.products') }}" class="vs-btn vs-btn-ghost">Cancel</a>
  <button type="submit" id="product-submit-btn" class="vs-btn vs-btn-primary">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    {{ $isEdit ? 'Save Changes' : 'Add Product' }}
  </button>
</div>

</form>

{{-- ── DATA FOR EDIT MODE ───────────────────────────────────────────── --}}
@php
  $editProductId = isset($product) ? (int)$product->id : 0;

  // Build color-row data from dbVariations (grouped by Color attribute).
  // Handles both {"Color":"X","Size":"Y"} and legacy free-form attributes
  // like {"Color":"X","RAM":"8GB","Storage":"256GB"} by combining non-Color
  // keys into a composite size label ("8GB / 256GB").
  $editColorRows = [];
  if ($hasVariations && $dbVariations->count()) {
      foreach ($dbVariations as $dv) {
          $attrs = $dv->attributes ?? [];

          // Resolve color — fall back to "Default" so the row is always valid
          $color = isset($attrs['Color']) && $attrs['Color'] !== ''
              ? $attrs['Color']
              : 'Default';

          // Resolve size — use Size if present, otherwise combine remaining attrs
          if (isset($attrs['Size']) && $attrs['Size'] !== '') {
              $size = $attrs['Size'];
          } else {
              $otherAttrs = array_filter(
                  $attrs,
                  fn($k) => strtolower($k) !== 'color',
                  ARRAY_FILTER_USE_KEY
              );
              $size = count($otherAttrs)
                  ? implode(' / ', array_values($otherAttrs))
                  : 'Default';
          }

          if (! isset($editColorRows[$color])) {
              $editColorRows[$color] = [
                  'name'             => $color,
                  'sizes'            => [],
                  'price_map'        => [],
                  'sale_price_map'   => [],
                  'stock'            => [],
                  'stock_status_map' => [],
                  'status_map'       => [],
                  'sale_price'       => '',
                  'images'           => $dv->images ?? [],
              ];
          }

          $editColorRows[$color]['sizes'][]                      = $size;
          $editColorRows[$color]['price_map'][$size]             = $dv->regular_price ?? 0;
          $editColorRows[$color]['stock'][$size]                 = $dv->stock_quantity ?? 0;
          $editColorRows[$color]['stock_status_map'][$size]      = $dv->stock_status ?? 'instock';
          $editColorRows[$color]['status_map'][$size]            = $dv->status ?? 'publish';
          // Restore per-size sale price only when it's genuinely discounted
          $dvSale = (float)($dv->sale_price ?? 0);
          $dvReg  = (float)($dv->regular_price ?? 0);
          if ($dvSale > 0 && $dvReg > 0 && $dvSale < $dvReg) {
              $editColorRows[$color]['sale_price_map'][$size] = $dvSale;
          }
      }
  }
  $editColorRows = array_values($editColorRows);
@endphp

<script>
const EDIT_HAS_VARIATIONS = {{ $hasVariations ? 'true' : 'false' }};
const EDIT_COLOR_ROWS     = {!! json_encode($editColorRows) !!};
const EDIT_PRODUCT_ID     = {{ $editProductId }};
const EXISTING_TRANSLATIONS = {!! json_encode($translations) !!};

// ─── Discount hint + live effective price recalc ──────────────────
function updateDiscountHint(val) {
  const pct  = parseFloat(val) || 0;
  const hint = document.getElementById('discount-hint');
  if (pct > 0) {
    hint.textContent = `${pct}% off — overrides any per-size or per-color sale prices`;
  } else {
    hint.textContent = 'No global discount — you can set per-size sale prices in the table below';
  }
  recalcAllEffectives();
  updateSimpleEffectivePrice();
}

function recalcAllEffectives() {
  Object.keys(colorSizes).forEach(idx => {
    (colorSizes[idx] || []).forEach(({ size }) => recalcEffective(Number(idx), size));
  });
}

function recalcEffective(idx, size) {
  const container = document.getElementById(`size-pricing-${idx}`);
  if (!container) return;
  const effCell = container.querySelector(`.eff-price-cell[data-size-key="${CSS.escape(size)}"]`);
  const regInput  = container.querySelector(`input[data-role="regular"][data-size="${size}"]`);
  const saleInput = container.querySelector(`input[data-role="sale"][data-size="${size}"]`);
  if (!effCell || !regInput) return;

  const reg     = parseFloat(regInput.value) || 0;
  const sale    = parseFloat(saleInput?.value || '') || 0;
  const discPct = parseFloat(document.querySelector('[name="discount_percentage"]')?.value || '0') || 0;

  if (reg <= 0) { effCell.textContent = '—'; effCell.style.color = 'var(--mid)'; return; }

  let eff;
  if (discPct > 0) {
    eff = reg * (1 - discPct / 100);
  } else if (sale > 0 && sale < reg) {
    eff = sale;
  } else {
    eff = reg;
  }

  const isSale = eff < reg;
  effCell.style.color = isSale ? 'var(--red)' : 'var(--dark)';
  effCell.innerHTML   = isSale
    ? `<strong>${eff.toFixed(2)}</strong> <span style="text-decoration:line-through;color:var(--mid);font-size:10px">${reg.toFixed(2)}</span>`
    : `<strong>${eff.toFixed(2)}</strong>`;
}

function updateSimpleEffectivePrice() {
  const preview  = document.getElementById('simple-eff-preview');
  const regEl    = document.getElementById('simple-regular-price');
  const saleEl   = document.getElementById('simple-sale-price');
  if (!preview || !regEl) return;
  const reg     = parseFloat(regEl.value) || 0;
  const sale    = parseFloat(saleEl?.value || '') || 0;
  const discPct = parseFloat(document.querySelector('[name="discount_percentage"]')?.value || '0') || 0;
  if (reg <= 0) { preview.style.display = 'none'; return; }
  let eff;
  if (discPct > 0) {
    eff = reg * (1 - discPct / 100);
  } else if (sale > 0 && sale < reg) {
    eff = sale;
  } else {
    eff = reg;
  }
  const isSale = eff < reg;
  preview.style.display = '';
  preview.innerHTML = isSale
    ? `Effective price: <strong style="color:var(--red)">${eff.toFixed(2)} EGP</strong> <span style="text-decoration:line-through;color:var(--mid);font-size:11px">${reg.toFixed(2)} EGP</span>`
    : `Effective price: <strong>${eff.toFixed(2)} EGP</strong>`;
}

// ─── Product type ─────────────────────────────────────────────────
function toggleProductType(val) {
  // could hide/show shipping fields in future
}

// ─── Image previews ───────────────────────────────────────────────
// ─── Image size helpers ────────────────────────────────────────────
function fmtBytes(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}
function imgDimensions(src) {
  return new Promise(resolve => {
    const img = new Image();
    img.onload = () => resolve({ w: img.naturalWidth, h: img.naturalHeight });
    img.onerror = () => resolve(null);
    img.src = src;
  });
}
function makePreviewItem(dataUrl, file, container) {
  const reader = new FileReader();
  const div = document.createElement('div');
  div.className = 'img-preview-item';
  const imgEl = document.createElement('img');
  imgEl.src = dataUrl;
  imgEl.alt = '';
  div.appendChild(imgEl);
  const badge = document.createElement('div');
  badge.className = 'img-size-badge';
  badge.textContent = '…';
  div.appendChild(badge);
  container.appendChild(div);
  imgDimensions(dataUrl).then(dim => {
    badge.textContent = dim ? `${dim.w}×${dim.h}\n${fmtBytes(file.size)}` : fmtBytes(file.size);
  });
}

function previewSingle(input, dropId, labelId) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const drop = document.getElementById(dropId);
  drop.classList.add('has-file');
  document.getElementById(labelId).textContent = '✓ ' + file.name;

  // Remove any existing thumb-info
  const existingInfo = drop.parentElement.querySelector('.thumb-info');
  if (existingInfo) existingInfo.remove();

  const reader = new FileReader();
  reader.onload = e => {
    const dataUrl = e.target.result;
    imgDimensions(dataUrl).then(dim => {
      const info = document.createElement('div');
      info.className = 'thumb-info';
      info.innerHTML = `
        <img src="${dataUrl}" alt="preview">
        <div class="thumb-info-text">
          <strong>${file.name}</strong><br>
          ${dim ? `${dim.w} × ${dim.h} px &nbsp;·&nbsp; ` : ''}${fmtBytes(file.size)}
        </div>`;
      drop.parentElement.appendChild(info);
    });
  };
  reader.readAsDataURL(file);
  checkUploadSize();
}

function previewMulti(input, dropId, labelId, previewsId) {
  if (!input.files || !input.files.length) return;
  const container = document.getElementById(previewsId);
  container.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => makePreviewItem(e.target.result, file, container);
    reader.readAsDataURL(file);
  });
  document.getElementById(dropId).classList.add('has-file');
  document.getElementById(labelId).textContent = `✓ ${input.files.length} image(s) selected`;
  checkUploadSize();
}

// ─── Upload size guard ──────────────────────────────────────────────
// The server (PHP) rejects the whole request outside Laravel's own
// validation when the total POST body exceeds post_max_size, producing an
// ugly generic error page instead of a friendly one. We warn ahead of time
// and block the submit button so the seller never hits that page.
const MAX_SINGLE_FILE_MB = 5;
const MAX_TOTAL_UPLOAD_MB = 7; // stays safely under the server's 8MB POST limit
function _collectUploadFiles() {
  const files = [];
  document.querySelectorAll('#product-form input[type="file"]').forEach(inp => {
    if (inp.files) files.push(...Array.from(inp.files));
  });
  return files;
}
function checkUploadSize() {
  const files = _collectUploadFiles();
  const totalBytes = files.reduce((sum, f) => sum + f.size, 0);
  const totalMB = totalBytes / (1024 * 1024);
  const tooBigFiles = files.filter(f => f.size / (1024 * 1024) > MAX_SINGLE_FILE_MB);
  const overLimit = totalMB > MAX_TOTAL_UPLOAD_MB || tooBigFiles.length > 0;

  let box = document.getElementById('upload-size-warning');
  const submitBtn = document.getElementById('product-submit-btn');

  if (!overLimit) {
    if (box) box.remove();
    if (submitBtn) { submitBtn.disabled = false; submitBtn.title = ''; }
    return false;
  }

  if (!box) {
    box = document.createElement('div');
    box.id = 'upload-size-warning';
    box.className = 'vs-alert vs-alert-error';
    box.style.marginBottom = '12px';
    document.getElementById('section-images').prepend(box);
  }
  const parts = [];
  if (tooBigFiles.length) {
    parts.push(`<li>${tooBigFiles.map(f => `"${f.name}" is ${(f.size/(1024*1024)).toFixed(1)} MB`).join(', ')} — each image must be under ${MAX_SINGLE_FILE_MB} MB.</li>`);
  }
  if (totalMB > MAX_TOTAL_UPLOAD_MB) {
    parts.push(`<li>Total selected images are ${totalMB.toFixed(1)} MB — please keep the combined size under ${MAX_TOTAL_UPLOAD_MB} MB.</li>`);
  }
  box.innerHTML =
    `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
     <div style="flex:1">
       <strong>These images are too large to upload:</strong>
       <ul style="margin:6px 0 0 16px;font-size:12px;line-height:1.7">${parts.join('')}</ul>
       <div style="font-size:12px;margin-top:4px">Try compressing the image or choosing a smaller file, then reselect it above.</div>
     </div>`;

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.title = 'Reduce the image size(s) before saving';
  }
  return true;
}

// ─── Related products search ──────────────────────────────────────
const rpSearch   = document.getElementById('rp-search');
const rpDropdown = document.getElementById('rp-dropdown');
const rpTags     = document.getElementById('rp-tags');
const rpInput    = document.getElementById('related-ids-input');
let selectedIds  = rpInput.value ? rpInput.value.split(',').map(Number).filter(Boolean) : [];
let searchTimeout;

rpSearch.addEventListener('input', () => {
  clearTimeout(searchTimeout);
  const q = rpSearch.value.trim();
  if (q.length < 1) { rpDropdown.classList.remove('open'); return; }
  searchTimeout = setTimeout(() => {
    fetch(`{{ route('vendor.products.search') }}?q=${encodeURIComponent(q)}&exclude=${EDIT_PRODUCT_ID||''}`)
      .then(r => r.json())
      .then(data => {
        rpDropdown.innerHTML = '';
        if (!data.length) {
          rpDropdown.innerHTML = '<div class="rp-option" style="color:var(--mid)">No products found</div>';
        } else {
          data.forEach(p => {
            if (selectedIds.includes(p.id)) return;
            const div = document.createElement('div');
            div.className = 'rp-option';
            div.textContent = p.name;
            div.addEventListener('click', () => addRelated(p.id, p.name));
            rpDropdown.appendChild(div);
          });
          if (!rpDropdown.children.length)
            rpDropdown.innerHTML = '<div class="rp-option" style="color:var(--mid)">All matching products already added</div>';
        }
        rpDropdown.classList.add('open');
      }).catch(() => {});
  }, 300);
});
document.addEventListener('click', e => {
  if (!rpSearch.contains(e.target) && !rpDropdown.contains(e.target))
    rpDropdown.classList.remove('open');
});
function addRelated(id, name) {
  if (selectedIds.includes(id)) return;
  selectedIds.push(id);
  rpInput.value = selectedIds.join(',');
  const tag = document.createElement('span');
  tag.className = 'rp-tag';
  tag.setAttribute('data-id', id);
  tag.innerHTML = `${name.length > 35 ? name.substring(0,35)+'…' : name}<button type="button" onclick="removeRelated(${id})">×</button>`;
  rpTags.appendChild(tag);
  rpSearch.value = '';
  rpDropdown.classList.remove('open');
}
function removeRelated(id) {
  selectedIds = selectedIds.filter(i => i !== id);
  rpInput.value = selectedIds.join(',');
  const tag = rpTags.querySelector(`[data-id="${id}"]`);
  if (tag) tag.remove();
}

// ─── Variations: Color + Size ─────────────────────────────────────
let colorSeq = 0;

function toggleVariations(on) {
  document.getElementById('simple-panel').style.display = on ? 'none' : '';
  document.getElementById('var-panel').style.display    = on ? ''     : 'none';
  document.getElementById('simple-regular-price').required = !on;
  document.getElementById('simple-stock').required         = !on;

  if (on && document.getElementById('color-rows').children.length === 0) {
    addColorRow();
  }
}

function addColorRow(colorData) {
  colorData = colorData || {};
  const idx     = colorSeq++;
  const isFirst = document.getElementById('color-rows').children.length === 0;
  const sizes        = colorData.sizes || [];
  const priceMap        = colorData.price_map || {};
  const salePriceMap    = colorData.sale_price_map || {};
  const stockMap        = colorData.stock || {};
  const stockStatusMap  = colorData.stock_status_map || {};
  const statusMap       = colorData.status_map || {};
  const colorName       = colorData.name || '';
  const salePriceVal    = colorData.sale_price || '';

  const row = document.createElement('div');
  row.className = 'color-row' + (isFirst ? ' is-main' : '');
  row.id = `color-row-${idx}`;
  row.dataset.colorIdx = idx;

  row.innerHTML = `
    <div class="color-row-header">
      <span class="color-row-badge">${isFirst ? '★ Main Color' : 'Color #' + (document.getElementById('color-rows').children.length + 1)}</span>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <input type="text" name="colors[${idx}][name]" class="vs-input" placeholder="Color name (e.g. Red, Navy Blue)"
               value="${escHtml(colorName)}" style="width:220px"
               onblur="refreshSizePricingTable(${idx})">
        ${isFirst ? '' : `<button type="button" class="color-row-remove" onclick="removeColorRow(${idx})">× Remove</button>`}
      </div>
    </div>

    <div class="vs-form-grid" style="margin-bottom:12px">
      <div class="vs-form-group">
        <label class="vs-label">Sale Price Override (EGP) <span style="color:var(--mid);font-weight:400;text-transform:none">optional — overrides % discount for this color</span></label>
        <input type="number" name="colors[${idx}][sale_price]" step="0.01" min="0"
               class="vs-input" placeholder="Leave blank — use % discount instead" value="${escHtml(String(salePriceVal))}">
      </div>
    </div>

    <div class="vs-form-group">
      <label class="vs-label">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Color Images
        <span style="color:var(--mid);font-weight:400;text-transform:none">— photos shown when this color is selected (multiple allowed)</span>
      </label>
      <label for="color-img-${idx}" class="img-drop" id="color-img-drop-${idx}" style="min-height:80px;padding:14px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <span id="color-img-label-${idx}">Click to upload images for this color · JPG / PNG / WebP</span>
      </label>
      <input type="file" id="color-img-${idx}" name="variation_images[${idx}][]"
             accept="image/jpeg,image/png,image/webp" multiple style="display:none"
             onchange="previewColorImages(this, ${idx})">
      <div class="img-preview-row" id="color-img-previews-${idx}" style="margin-top:8px"></div>
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Sizes <span style="color:var(--red)">*</span> <span style="color:var(--mid);font-weight:400;text-transform:none">— press Enter or comma to add</span></label>
      <div class="size-tags-wrap" id="size-tags-${idx}" onclick="document.getElementById('size-input-${idx}').focus()">
        <input type="text" id="size-input-${idx}" class="size-add-input" placeholder="Add size…"
               onkeydown="handleSizeInput(event, ${idx})">
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px">
        ${['XS','S','M','L','XL','XXL','XXXL'].map(s => `<button type="button" class="preset-btn" onclick="addSize(${idx}, '${s}')" style="font-size:11px;padding:2px 8px;border:1px solid var(--light);border-radius:12px;background:#f3f4f6;cursor:pointer">${s}</button>`).join('')}
        ${['36','37','38','39','40','41','42','43','44','45'].map(s => `<button type="button" class="preset-btn" onclick="addSize(${idx}, '${s}')" style="font-size:11px;padding:2px 8px;border:1px solid var(--light);border-radius:12px;background:#f3f4f6;cursor:pointer">${s}</button>`).join('')}
        ${['OS','Free Size','One Size'].map(s => `<button type="button" class="preset-btn" onclick="addSize(${idx}, '${s}')" style="font-size:11px;padding:2px 8px;border:1px solid var(--light);border-radius:12px;background:#f3f4f6;cursor:pointer">${s}</button>`).join('')}
      </div>
    </div>

    <div id="size-pricing-${idx}" style="margin-top:8px"></div>`;

  document.getElementById('color-rows').appendChild(row);

  // Restore sizes from edit data (including per-size sale prices)
  sizes.forEach(s => addSize(idx, s, priceMap[s] || '', salePriceMap[s] || '', stockMap[s] || 0, stockStatusMap[s] || 'instock', statusMap[s] || 'publish'));
}

function removeColorRow(idx) {
  const row = document.getElementById(`color-row-${idx}`);
  if (row) row.remove();
  // Re-label
  document.querySelectorAll('[data-color-idx]').forEach((r, i) => {
    const badge = r.querySelector('.color-row-badge');
    if (badge) badge.textContent = i === 0 ? '★ Main Color' : `Color #${i + 1}`;
    r.classList.toggle('is-main', i === 0);
  });
}

// ─── Size management per color ────────────────────────────────────
const colorSizes = {}; // colorIdx -> [{ size, price, salePrice, stock }]

function handleSizeInput(e, idx) {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault();
    const input = document.getElementById(`size-input-${idx}`);
    const val   = input.value.trim().replace(/,/g, '');
    if (val) { addSize(idx, val); input.value = ''; }
  }
}

function addSize(idx, size, price, salePrice, stock, stockStatus, status) {
  size = size.trim();
  if (!size) return;
  if (!colorSizes[idx]) colorSizes[idx] = [];
  if (colorSizes[idx].find(s => s.size === size)) return; // no duplicates

  colorSizes[idx].push({ size, price: price || '', salePrice: salePrice || '', stock: stock || 0, stockStatus: stockStatus || 'instock', status: status || 'publish' });

  // Add visual tag
  const wrap = document.getElementById(`size-tags-${idx}`);
  const tag  = document.createElement('span');
  tag.className = 'size-tag';
  tag.dataset.size = size;
  tag.innerHTML = `${escHtml(size)}<button type="button" class="size-tag-remove" onclick="removeSize(${idx}, '${escJs(size)}')">×</button>`;
  wrap.insertBefore(tag, document.getElementById(`size-input-${idx}`));

  refreshSizePricingTable(idx);
}

function removeSize(idx, size) {
  if (!colorSizes[idx]) return;
  colorSizes[idx] = colorSizes[idx].filter(s => s.size !== size);

  const wrap = document.getElementById(`size-tags-${idx}`);
  const tag  = wrap.querySelector(`[data-size="${size}"]`);
  if (tag) tag.remove();

  refreshSizePricingTable(idx);
}

function refreshSizePricingTable(idx) {
  const sizes     = colorSizes[idx] || [];
  const container = document.getElementById(`size-pricing-${idx}`);
  if (!container) return;

  if (!sizes.length) {
    container.innerHTML = '';
    return;
  }

  let html = `<label class="vs-label" style="margin-bottom:6px">Per-Size Pricing & Stock</label>
    <table class="price-map-table">
      <thead><tr>
        <th>Size</th>
        <th>Regular Price (EGP) <span style="color:var(--red)">*</span></th>
        <th>Sale Price (EGP) <span style="font-weight:400;font-size:10px;color:var(--mid)">optional — overrides % discount</span></th>
        <th>Stock Qty</th>
        <th>Stock Status</th>
        <th>Availability</th>
        <th style="background:#fff8f5;color:var(--orange);min-width:100px">Effective Price</th>
      </tr></thead>
      <tbody>`;

  sizes.forEach(({ size, price, salePrice, stock, stockStatus, status }) => {
    const ss = stockStatus || 'instock';
    const st = status || 'publish';
    html += `<tr>
      <td><strong>${escHtml(size)}</strong></td>
      <td><input type="number" name="colors[${idx}][price_map][${escHtml(size)}]"
                 value="${escHtml(String(price || ''))}" step="0.01" min="0" placeholder="0.00"
                 data-role="regular" data-size="${escHtml(size)}"
                 oninput="recalcEffective(${idx}, '${escJs(size)}')"
                 class="vs-input" style="border:none;padding:2px 4px;width:100px"></td>
      <td><input type="number" name="colors[${idx}][sale_price_map][${escHtml(size)}]"
                 value="${salePrice ? escHtml(String(salePrice)) : ''}" step="0.01" min="0" placeholder="leave blank"
                 data-role="sale" data-size="${escHtml(size)}"
                 oninput="recalcEffective(${idx}, '${escJs(size)}')"
                 class="vs-input" style="border:none;padding:2px 4px;width:100px"></td>
      <td><input type="number" name="colors[${idx}][stock][${escHtml(size)}]"
                 value="${escHtml(String(stock || 0))}" min="0" placeholder="0"
                 class="vs-input" style="border:none;padding:2px 4px;width:80px"></td>
      <td>
        <select name="colors[${idx}][stock_status_map][${escHtml(size)}]" class="vs-input" style="border:none;padding:2px 4px;font-size:12px">
          <option value="instock"     ${ss==='instock'     ? 'selected' : ''}>In Stock</option>
          <option value="outofstock"  ${ss==='outofstock'  ? 'selected' : ''}>Out of Stock</option>
          <option value="onbackorder" ${ss==='onbackorder' ? 'selected' : ''}>On Backorder</option>
        </select>
      </td>
      <td>
        <select name="colors[${idx}][status_map][${escHtml(size)}]" class="vs-input" style="border:none;padding:2px 4px;font-size:12px">
          <option value="publish" ${st==='publish' ? 'selected' : ''}>Active</option>
          <option value="draft"   ${st==='draft'   ? 'selected' : ''}>Disabled</option>
        </select>
      </td>
      <td class="eff-price-cell" data-size-key="${escHtml(size)}" style="font-weight:600;font-size:13px">—</td>
    </tr>`;
  });

  html += '</tbody></table>';
  container.innerHTML = html;

  // Compute initial effective prices after rendering
  sizes.forEach(({ size }) => recalcEffective(idx, size));
}

function previewColorImages(input, idx) {
  const container = document.getElementById(`color-img-previews-${idx}`);
  container.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => makePreviewItem(e.target.result, file, container);
    reader.readAsDataURL(file);
  });
  const label = document.getElementById(`color-img-label-${idx}`);
  if (label) label.textContent = `✓ ${input.files.length} image(s)`;
  document.getElementById(`color-img-drop-${idx}`).classList.add('has-file');
  checkUploadSize();
}

// ─── Product Attributes ───────────────────────────────────────────
let attrSeq = {{ count($attributes) }};
function addAttrRow() {
  const idx = attrSeq++;
  const container = document.getElementById('attr-rows');
  const row = document.createElement('div');
  row.className = 'attr-row';
  row.id = `attr-row-${idx}`;
  row.innerHTML = `
    <div style="flex:1">
      <input type="text" name="prod_attributes[${idx}][name]"
             class="vs-input" placeholder="Attribute name (e.g. Material, Occasion)" style="margin-bottom:6px">
      <input type="text" name="prod_attributes[${idx}][values]"
             class="vs-input" placeholder="Values separated by commas (e.g. Cotton, Polyester, Wool)">
    </div>
    <button type="button" class="attr-row-del" onclick="this.closest('.attr-row').remove()">× Remove</button>`;
  container.appendChild(row);
}

// ─── Tags ──────────────────────────────────────────────────────────
const tagsHidden  = document.getElementById('tags-hidden');
const tagsVisual  = document.getElementById('tags-visual');
let currentTags   = tagsHidden.value ? tagsHidden.value.split(',').map(t => t.trim()).filter(Boolean) : [];

function syncTagsHidden() {
  tagsHidden.value = currentTags.join(', ');
}
function handleTagInput(e) {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault();
    const val = e.target.value.trim().replace(/,/g, '');
    if (val) { addTagPill(val); e.target.value = ''; }
  }
}
function addTagPill(tag) {
  tag = tag.trim();
  if (!tag || currentTags.includes(tag)) return;
  currentTags.push(tag);
  syncTagsHidden();
  const pill = document.createElement('span');
  pill.className = 'tag-pill';
  pill.dataset.tag = tag;
  pill.innerHTML = `${escHtml(tag)}<button type="button" class="tag-pill-remove" onclick="removeTag('${escJs(tag)}')">×</button>`;
  tagsVisual.insertBefore(pill, document.getElementById('tag-input'));
}
function removeTag(tag) {
  currentTags = currentTags.filter(t => t !== tag);
  syncTagsHidden();
  const pill = tagsVisual.querySelector(`[data-tag="${tag}"]`);
  if (pill) pill.remove();
}

// ─── Translations ──────────────────────────────────────────────────
let trIndex = {{ count($translations) }};
const existingLangs = new Set(['en', ...EXISTING_TRANSLATIONS.map(t => t.locale)]);

const langNames = { ar:'🇦🇪 Arabic', fr:'🇫🇷 French', de:'🇩🇪 German', es:'🇪🇸 Spanish', it:'🇮🇹 Italian' };

function switchLangTab(lang) {
  document.querySelectorAll('.lang-tab').forEach(t => t.classList.toggle('active', t.dataset.lang === lang));
  document.querySelectorAll('.translation-panel').forEach(p => p.classList.toggle('active', p.id === `tr-panel-${lang}`));
}

function addLangTab() {
  const select = document.getElementById('lang-add-select');
  const lang   = select.value;
  if (!lang) return;
  if (existingLangs.has(lang)) {
    switchLangTab(lang);
    select.value = '';
    return;
  }
  existingLangs.add(lang);

  // Add tab button
  const tab = document.createElement('div');
  tab.className = 'lang-tab';
  tab.dataset.lang = lang;
  tab.onclick = () => switchLangTab(lang);
  tab.innerHTML = `<span>${langNames[lang] || lang.toUpperCase()}</span>
    <button type="button" class="lt-remove" onclick="removeLangTab(event,'${lang}')">×</button>`;
  document.getElementById('lang-tab-bar').appendChild(tab);

  // Add panel
  const idx   = trIndex++;
  const panel = document.createElement('div');
  panel.className = 'translation-panel';
  panel.id = `tr-panel-${lang}`;
  panel.innerHTML = `
    <input type="hidden" name="translations[${idx}][locale]" value="${lang}">
    <div class="vs-form-group">
      <label class="vs-label">Product Name (${lang.toUpperCase()}) <span style="color:var(--red)">*</span></label>
      <input type="text" name="translations[${idx}][name]" class="vs-input"
             placeholder="Product name in ${langNames[lang] || lang}" maxlength="500">
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Short Description (${lang.toUpperCase()})</label>
      <textarea name="translations[${idx}][short_description]" class="vs-input" style="min-height:72px"
                placeholder="Short description in ${langNames[lang] || lang}"></textarea>
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Full Description (${lang.toUpperCase()})</label>
      <textarea name="translations[${idx}][description]" class="vs-input" style="min-height:120px"
                placeholder="Full description in ${langNames[lang] || lang}"></textarea>
    </div>`;
  document.getElementById('tr-panels-container').appendChild(panel);

  switchLangTab(lang);
  select.value = '';
}

function removeLangTab(event, lang) {
  event.stopPropagation();
  existingLangs.delete(lang);
  const tab   = document.querySelector(`.lang-tab[data-lang="${lang}"]`);
  const panel = document.getElementById(`tr-panel-${lang}`);
  if (tab)   tab.remove();
  if (panel) panel.remove();
  switchLangTab('en');
}

// ─── Helpers ───────────────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) {
  return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

// ─── Init (page load) ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  if (EDIT_HAS_VARIATIONS) {
    document.getElementById('has-variations-toggle').checked = true;
    toggleVariations(true);
    EDIT_COLOR_ROWS.forEach(c => addColorRow(c));
  } else {
    toggleVariations(false);
  }

  // Init discount hint + effective price previews
  const discPct = document.querySelector('[name="discount_percentage"]');
  if (discPct) updateDiscountHint(discPct.value);
  updateSimpleEffectivePrice();

  // ── Auto-scroll to error summary + highlight bad color rows ──────
  const summary = document.getElementById('error-summary');
  if (summary) {
    setTimeout(() => summary.scrollIntoView({ behavior: 'smooth', block: 'center' }), 120);
    // Mark color rows missing name or sizes
    document.querySelectorAll('.color-row').forEach(row => {
      const nm  = row.querySelector('[name$="[name]"]')?.value?.trim() || '';
      const sz  = row.querySelectorAll('.size-tag').length;
      if (!nm || sz === 0) row.classList.add('has-error');
    });
  }

  // ── Client-side pre-validation on submit ─────────────────────────
  document.getElementById('product-form').addEventListener('submit', function (e) {
    const issues = [];

    // Clear previous has-error on color rows
    document.querySelectorAll('.color-row').forEach(r => r.classList.remove('has-error'));

    // Product name
    if (!(document.querySelector('[name="name"]')?.value?.trim())) {
      issues.push('Product name (English) is required.');
    }

    const hasVars = document.getElementById('has-variations-toggle')?.checked;
    if (hasVars) {
      const colorRows = document.querySelectorAll('.color-row');
      if (colorRows.length === 0) {
        issues.push('Add at least one color variation.');
      } else {
        colorRows.forEach((row, i) => {
          const n   = i + 1;
          const nm  = row.querySelector('[name$="[name]"]')?.value?.trim() || '';
          const sz  = row.querySelectorAll('.size-tag').length;
          if (!nm)   { issues.push(`Color #${n}: name is required.`);               row.classList.add('has-error'); }
          if (sz===0){ issues.push(`Color #${n} "${nm||'?'}": add at least one size.`); row.classList.add('has-error'); }
        });
      }
    } else {
      const rp = parseFloat(document.querySelector('[name="regular_price"]')?.value || '0');
      if (!rp || rp <= 0) issues.push('Regular price is required and must be greater than 0.');
      const sq = document.querySelector('[name="stock_quantity"]')?.value;
      if (sq === '' || sq === null || sq === undefined) issues.push('Stock quantity is required.');
    }

    if (checkUploadSize()) {
      issues.push(`Some images are too large — reduce them to under ${MAX_SINGLE_FILE_MB} MB each and ${MAX_TOTAL_UPLOAD_MB} MB total before saving.`);
    }

    if (issues.length === 0) {
      // Inject hidden sizes[] inputs from the colorSizes object so the server receives them
      document.querySelectorAll('[data-color-idx]').forEach(row => {
        const idx = row.dataset.colorIdx;
        // Remove any previously injected hidden size inputs for this color
        row.querySelectorAll('input[data-injected-size]').forEach(el => el.remove());
        const sizes = colorSizes[idx] || [];
        sizes.forEach(({ size }) => {
          const hidden = document.createElement('input');
          hidden.type  = 'hidden';
          hidden.name  = `colors[${idx}][sizes][]`;
          hidden.value = size;
          hidden.setAttribute('data-injected-size', '1');
          row.appendChild(hidden);
        });
      });
      return; // all good — let the form submit
    }
    e.preventDefault();

    // Build or update the error banner
    let box = document.getElementById('error-summary');
    if (!box) {
      box = document.createElement('div');
      box.id = 'error-summary';
      box.className = 'vs-alert vs-alert-error';
      box.style.marginBottom = '16px';
      document.getElementById('product-form').prepend(box);
    }
    box.innerHTML =
      `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
       <div style="flex:1">
         <strong>${issues.length} problem${issues.length!==1?'s':''} need${issues.length===1?'s':''} fixing before you can save:</strong>
         <ul style="margin:6px 0 0 16px;font-size:12px;line-height:1.7">${issues.map(i=>`<li>${i}</li>`).join('')}</ul>
       </div>`;
    setTimeout(() => box.scrollIntoView({ behavior: 'smooth', block: 'center' }), 60);
  });
});

// ─── 🧪 Debug: Fill with fake data ───────────────────────────────
function fillFakeData() {
  const pick = arr => arr[Math.floor(Math.random() * arr.length)];
  const rand = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

  const adjectives = ['Classic','Premium','Modern','Slim-Fit','Vintage','Essential','Luxe','Casual','Tailored','Bold'];
  const materials  = ['Cotton','Linen','Polyester','Wool','Denim','Leather','Silk','Velvet'];
  const nouns      = ['T-Shirt','Hoodie','Jacket','Blazer','Trousers','Dress','Skirt','Coat','Jeans','Vest'];
  const colors     = ['Black','White','Navy','Beige','Olive','Grey','Burgundy','Teal'];

  const name    = `${pick(adjectives)} ${pick(materials)} ${pick(nouns)} — ${pick(colors)}`;
  const sku     = `SKU-${Math.random().toString(36).substring(2,8).toUpperCase()}`;
  const price   = (rand(199, 2999)).toFixed(2);
  const stock   = rand(5, 150);
  const disc    = pick([0, 0, 5, 10, 15, 20]);
  const shortDesc = `High-quality ${pick(materials).toLowerCase()} ${nouns[rand(0,nouns.length-1)].toLowerCase()} perfect for everyday wear. Comfortable fit with a modern look.`;
  const fullDesc  = `${shortDesc}\n\nFeatures:\n• Premium ${pick(materials).toLowerCase()} fabric\n• Available in multiple sizes\n• Machine washable\n• True to size fit\n\nCare instructions: Wash at 30°C, do not tumble dry.`;
  const tagList   = [pick(['summer','winter','casual','formal']), pick(['trending','new','sale','bestseller']), pick(['gift','everyday','premium'])];

  // Basic fields
  document.querySelector('[name="name"]').value              = name;
  document.querySelector('[name="status"]').value            = 'publish';
  document.querySelector('[name="short_description"]').value = shortDesc;
  document.querySelector('[name="description"]').value       = fullDesc;
  document.querySelector('[name="sku"]').value               = sku;
  document.querySelector('[name="discount_percentage"]').value = disc;
  document.querySelector('[name="minimum_order_qty"]').value = 1;
  document.querySelector('[name="max_orders_per_person"]').value = 0;
  document.querySelector('[name="unit"]').value              = 'piece';
  document.querySelector('[name="unit_amount"]').value       = 1;

  // Pick first brand if available
  const brandSel = document.querySelector('[name="brand_id"]');
  if (brandSel && brandSel.options.length > 1) brandSel.selectedIndex = 1;

  // Simple pricing & stock
  const hasVarChk = document.getElementById('has-variations-toggle');
  if (hasVarChk && hasVarChk.checked) {
    hasVarChk.checked = false;
    toggleVariations(false);
  }
  document.getElementById('simple-regular-price').value = price;
  document.getElementById('simple-stock').value         = stock;
  updateSimpleEffectivePrice();
  updateDiscountHint(disc);

  // Tags
  tagList.forEach(t => {
    const existing = document.getElementById('tags-hidden');
    const cur = existing.value ? existing.value.split(', ').filter(Boolean) : [];
    if (!cur.includes(t)) {
      cur.push(t);
      existing.value = cur.join(', ');
      const pill = document.createElement('span');
      pill.className = 'tag-pill';
      pill.dataset.tag = t;
      pill.innerHTML = `${t}<button type="button" class="tag-pill-remove" onclick="removeTag('${t}')">×</button>`;
      document.getElementById('tags-visual').insertBefore(pill, document.getElementById('tag-input'));
    }
  });

  // Pick a random category checkbox
  const catBoxes = document.querySelectorAll('[name="categories[]"]');
  if (catBoxes.length) catBoxes[rand(0, catBoxes.length-1)].checked = true;

  // Translations — add Arabic with fake data
  const arNames  = ['قميص قطني كلاسيكي','بنطلون جينز أنيق','جاكيت عصري فاخر','فستان صيفي خفيف','بلوزة كاجوال مريحة'];
  const arShorts = ['ملابس عالية الجودة مناسبة للاستخدام اليومي بتصميم عصري ومريح.','قطعة أنيقة من أجود الخامات تناسب جميع المناسبات.'];
  const arFulls  = ['منتج مصنوع من أجود الخامات، مريح وعملي للاستخدام اليومي.\n\nالمميزات:\n• خامة ممتازة\n• مقاسات متعددة\n• سهل العناية\n\nتعليمات العناية: اغسل على 30 درجة مئوية.'];

  // Only add if Arabic tab doesn't already exist
  if (!document.querySelector('[data-lang="ar"]')) {
    const langSelect = document.getElementById('lang-add-select');
    if (langSelect) {
      langSelect.value = 'ar';
      addLangTab(); // fires the existing addLangTab() function which creates the panel
    }
  }

  // Fill the AR panel fields (may take a tick to render)
  setTimeout(() => {
    const arPanel = document.getElementById('tr-panel-ar');
    if (arPanel) {
      const nameInput  = arPanel.querySelector('input[name$="[name]"]');
      const shortArea  = arPanel.querySelector('textarea[name$="[short_description]"]');
      const fullArea   = arPanel.querySelector('textarea[name$="[description]"]');
      if (nameInput) nameInput.value = pick(arNames);
      if (shortArea) shortArea.value = pick(arShorts);
      if (fullArea)  fullArea.value  = pick(arFulls);
    }
  }, 80);

  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

@endsection
