@extends('web.vendor.layout')
@section('title', $product->name)
@section('page-title', 'Product Detail')

@push('styles')
<style>
:root{--orange:#f97316;--red:#ef4444;--green:#22c55e;--mid:#6b7280;--light:#e5e7eb;--dark:#111827;--yellow:#f59e0b}

/* ── Page header ──────────────────────────────────────── */
.ph-wrap{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap}
.ph-thumb{width:80px;height:80px;border-radius:12px;object-fit:cover;border:1px solid var(--light);flex-shrink:0;background:#f3f4f6}
.ph-thumb-ph{width:80px;height:80px;border-radius:12px;background:#f3f4f6;border:1px solid var(--light);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ph-meta{flex:1;min-width:0}
.ph-name{font-size:22px;font-weight:800;color:var(--dark);line-height:1.2;margin-bottom:6px}
.ph-badges{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.ph-actions{display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap;align-self:flex-start}

/* ── Section card ─────────────────────────────────────── */
.dc{background:#fff;border:1px solid var(--light);border-radius:14px;margin-bottom:16px;overflow:hidden}
.dc-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--light);background:#fafafa}
.dc-title{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--dark);text-transform:uppercase;letter-spacing:.05em}
.dc-title svg{opacity:.6}
.dc-body{padding:18px 20px}
.dc-edit-btn{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--orange);border:1px solid var(--light);border-radius:8px;padding:5px 12px;background:#fff;cursor:pointer;transition:.15s}
.dc-edit-btn:hover{background:#fff7ed;border-color:var(--orange)}
.dc-cancel-btn{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--mid);border:1px solid var(--light);border-radius:8px;padding:5px 12px;background:#fff;cursor:pointer;transition:.15s}
.dc-cancel-btn:hover{background:#f9fafb}
.dc-save-btn{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#fff;background:var(--orange);border:none;border-radius:8px;padding:5px 14px;cursor:pointer;transition:.15s}
.dc-save-btn:hover{background:#ea6d0e}

/* ── Detail rows ──────────────────────────────────────── */
.dr{display:grid;grid-template-columns:160px 1fr;gap:8px 16px;font-size:13px;margin-bottom:6px}
.dr-label{color:var(--mid);font-weight:500;padding-top:1px}
.dr-value{color:var(--dark);word-break:break-word}
.dr-value.mono{font-family:monospace;font-size:12px;background:#f3f4f6;padding:1px 6px;border-radius:4px;display:inline}

/* ── Badge ────────────────────────────────────────────── */
.badge{padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-block}
.badge-publish,.badge-instock,.badge-approved{background:#dcfce7;color:#166534}
.badge-draft,.badge-outofstock{background:#f3f4f6;color:var(--mid)}
.badge-pending{background:#fef9c3;color:#92400e}
.badge-rejected{background:#fee2e2;color:#991b1b}
.badge-physical{background:#eff6ff;color:#1d4ed8}
.badge-digital{background:#f5f3ff;color:#6d28d9}
.badge-sale{background:#fff7ed;color:#c2410c}

/* ── Tag pills ────────────────────────────────────────── */
.tag-pill{background:#f3f4f6;color:var(--dark);border-radius:20px;padding:2px 10px;font-size:12px;font-weight:500}

/* ── Variations table ─────────────────────────────────── */
.var-table{width:100%;border-collapse:collapse;font-size:12px}
.var-table th{background:#f9fafb;padding:7px 10px;text-align:left;font-weight:600;color:var(--mid);border-bottom:1px solid var(--light)}
.var-table td{padding:7px 10px;border-bottom:1px solid #f3f4f6;color:var(--dark)}
.var-table tr:last-child td{border-bottom:none}
.var-table .main-chip{background:#fff7ed;color:#c2410c;border-radius:10px;padding:1px 7px;font-size:10px;font-weight:700}

/* ── Image grid ───────────────────────────────────────── */
.img-grid{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.img-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;border:1px solid var(--light)}

/* ── Inline edit form fields ──────────────────────────── */
.if-input{width:100%;padding:8px 12px;border:1px solid var(--light);border-radius:8px;font-size:13px;outline:none;transition:.15s}
.if-input:focus{border-color:var(--orange);box-shadow:0 0 0 3px #fff7ed}
.if-label{font-size:11px;font-weight:600;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;display:block}
.if-group{margin-bottom:12px}
.if-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.if-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.if-actions{display:flex;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid var(--light)}
.if-err{color:var(--red);font-size:11px;margin-top:3px}

/* ── Translations view ────────────────────────────────── */
.tr-block{border:1px solid var(--light);border-radius:10px;padding:12px;margin-bottom:10px}
.tr-lang{font-size:11px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}

/* ── Category chips ───────────────────────────────────── */
.cat-chip{background:#f3f4f6;border-radius:8px;padding:4px 10px;font-size:12px;display:inline-block;margin:2px}
.cat-grid-check{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:6px}
.cat-check-label{display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:4px 6px;border-radius:6px}
.cat-check-label:hover{background:#f9fafb}

/* ── Alert ────────────────────────────────────────────── */
.vs-alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:16px}

/* ── Color+size builder (reused from create) ──────────── */
.color-row{border:1px solid var(--light);border-radius:10px;padding:14px;margin-bottom:12px}
.color-row-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--light)}
.color-row-badge{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;background:#fff7ed;color:var(--orange);padding:2px 8px;border-radius:10px}
.color-row-remove{font-size:12px;color:var(--red);background:none;border:1px solid #fee2e2;border-radius:6px;padding:2px 8px;cursor:pointer}
.size-tags-wrap{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-height:40px;border:1px solid var(--light);border-radius:8px;padding:6px 10px;cursor:text}
.size-tag{display:inline-flex;align-items:center;gap:4px;background:#fff7ed;color:var(--orange);border-radius:16px;padding:2px 10px;font-size:12px;font-weight:600}
.size-tag button{background:none;border:none;color:inherit;cursor:pointer;font-size:13px;padding:0;line-height:1}
.size-add-input{border:none;outline:none;font-size:13px;min-width:100px;flex:1}
.preset-btn{font-size:11px;padding:2px 8px;border:1px solid var(--light);border-radius:12px;background:#f3f4f6;cursor:pointer}
.preset-btn:hover{background:#fff7ed;border-color:var(--orange);color:var(--orange)}
.price-table{width:100%;border-collapse:collapse;font-size:12px;margin-top:10px}
.price-table th{background:#f9fafb;padding:6px 10px;text-align:left;font-weight:600;color:var(--mid);border-bottom:1px solid var(--light)}
.price-table td{padding:6px 10px;border-bottom:1px solid #f3f4f6}
.price-table input{width:80px;padding:3px 7px;border:1px solid var(--light);border-radius:6px;font-size:12px}
.add-color-btn{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--orange);background:#fff7ed;border:1px dashed var(--orange);border-radius:8px;padding:6px 14px;cursor:pointer;margin-top:8px}
.img-drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:2px dashed var(--light);border-radius:10px;padding:18px;cursor:pointer;text-align:center;font-size:12px;color:var(--mid);transition:.15s}
.img-drop:hover{border-color:var(--orange);color:var(--orange)}
.img-drop.has-file{border-color:var(--green);color:var(--green)}
</style>
@endpush

@section('content')

@php
  $imgBase   = \Illuminate\Support\Facades\Storage::url('');
  $sectionUrl = route('vendor.products.update-section', $product->id);
  $thumb      = $images['thumbnail'] ?? null;
  $thumbUrl   = $thumb ? $imgBase . ltrim($thumb, '/') : null;
  $mainVar    = $dbVariations->first();
  $priceRange = $dbVariations->count()
    ? ['min' => $dbVariations->min('price'), 'max' => $dbVariations->max('price')]
    : null;
  $totalStock = $dbVariations->sum('stock_quantity');
  $discountPct = (float)($product->discount_percentage ?? 0);
@endphp

{{-- ── SUCCESS FLASH ─────────────────────────────────── --}}
@if(session('success'))
  <div class="vs-alert-ok">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
  </div>
@endif

{{-- ── PAGE HEADER ───────────────────────────────────── --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
  <a href="{{ route('vendor.products') }}" style="color:var(--mid);font-size:13px">← Products</a>
</div>

<div class="ph-wrap">
  @if($thumbUrl)
    <img src="{{ $thumbUrl }}" class="ph-thumb" alt="">
  @else
    <div class="ph-thumb-ph">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    </div>
  @endif
  <div class="ph-meta">
    <div class="ph-name">{{ $product->name }}</div>
    <div class="ph-badges">
      <span class="badge badge-{{ $product->status }}">{{ ucfirst($product->status) }}</span>
      <span class="badge badge-{{ $product->acceptance_status ?? 'pending' }}">{{ ucfirst($product->acceptance_status ?? 'Pending review') }}</span>
      @if($product->product_type)
        <span class="badge badge-{{ $product->product_type }}">{{ ucfirst($product->product_type) }}</span>
      @endif
      @if($discountPct > 0)
        <span class="badge badge-sale">{{ $discountPct }}% OFF</span>
      @endif
      @if($product->sku)
        <span style="font-size:12px;color:var(--mid);font-family:monospace">SKU: {{ $product->sku }}</span>
      @endif
    </div>
  </div>
  <div class="ph-actions">
    <a href="{{ route('vendor.products.edit', $product->id) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--mid);border:1px solid var(--light);border-radius:8px;padding:7px 16px;background:#fff;text-decoration:none">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit All
    </a>
    <form method="POST" action="{{ route('vendor.products.destroy', $product->id) }}" onsubmit="return confirm('Delete this product? This cannot be undone.')">
      @csrf @method('DELETE')
      <button type="submit"
        style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--red);border:1px solid #fee2e2;border-radius:8px;padding:7px 16px;background:#fff;cursor:pointer">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        Delete
      </button>
    </form>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 1 — BASIC INFO
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-basic">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Basic Information
    </div>
    <button class="dc-edit-btn" onclick="openSection('basic')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    {{-- ── VIEW MODE ──────────────────────────────────── --}}
    <div id="view-basic">
      <div class="dr"><span class="dr-label">Name</span><span class="dr-value">{{ $product->name }}</span></div>
      <div class="dr"><span class="dr-label">Status</span><span class="dr-value"><span class="badge badge-{{ $product->status }}">{{ ucfirst($product->status) }}</span></span></div>
      <div class="dr"><span class="dr-label">Product Type</span><span class="dr-value">{{ ucfirst($product->product_type ?? 'physical') }}</span></div>
      <div class="dr"><span class="dr-label">SKU</span><span class="dr-value">@if($product->sku)<span class="dr-value mono">{{ $product->sku }}</span>@else<span style="color:var(--mid)">—</span>@endif</span></div>
      <div class="dr"><span class="dr-label">Brand</span>
        @php $brandName = $brands->firstWhere('id',$product->brand_id)?->name ?? null; @endphp
        <span class="dr-value">{{ $brandName ?? '—' }}</span>
      </div>
      <div class="dr"><span class="dr-label">Unit</span><span class="dr-value">{{ $unitAmount }} {{ $unitType }}</span></div>
      <div class="dr"><span class="dr-label">Short Description</span><span class="dr-value">{{ $product->short_description ?: '—' }}</span></div>
      @if($product->description)
        <div class="dr" style="align-items:flex-start">
          <span class="dr-label">Description</span>
          <span class="dr-value" style="max-height:80px;overflow:hidden;text-overflow:ellipsis">{{ Str::limit(strip_tags($product->description), 200) }}</span>
        </div>
      @endif
    </div>

    {{-- ── EDIT MODE ──────────────────────────────────── --}}
    <div id="edit-basic" style="display:none">
      @if($errors->any() && old('_section') === 'basic')
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;font-size:12px;color:#991b1b;margin-bottom:12px">
          <ul style="margin:0 0 0 16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="basic">
        <div class="if-grid" style="margin-bottom:12px">
          <div class="if-group">
            <label class="if-label">Product Name <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" class="if-input" value="{{ old('name', $product->name) }}" required maxlength="500">
          </div>
          <div class="if-group">
            <label class="if-label">SKU</label>
            <input type="text" name="sku" class="if-input" value="{{ old('sku', $product->sku) }}" maxlength="100" placeholder="e.g. SHIRT-RED-M">
          </div>
        </div>
        <div class="if-grid" style="margin-bottom:12px">
          <div class="if-group">
            <label class="if-label">Status</label>
            <select name="status" class="if-input">
              <option value="draft"   {{ old('status',$product->status)==='draft'   ? 'selected' : '' }}>Draft (hidden)</option>
              <option value="publish" {{ old('status',$product->status)==='publish' ? 'selected' : '' }}>Published</option>
            </select>
          </div>
          <div class="if-group">
            <label class="if-label">Product Type</label>
            <select name="product_type" class="if-input">
              <option value="physical" {{ old('product_type',$product->product_type)==='physical' ? 'selected' : '' }}>Physical (shipped)</option>
              <option value="digital"  {{ old('product_type',$product->product_type)==='digital'  ? 'selected' : '' }}>Digital (download)</option>
            </select>
          </div>
        </div>
        <div class="if-grid" style="margin-bottom:12px">
          <div class="if-group">
            <label class="if-label">Brand</label>
            <select name="brand_id" class="if-input">
              <option value="">— No brand —</option>
              @foreach($brands as $b)
                <option value="{{ $b->id }}" {{ old('brand_id',$product->brand_id)==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="if-grid" style="gap:8px">
            <div class="if-group">
              <label class="if-label">Unit</label>
              <select name="unit" class="if-input">
                @foreach(['piece','kg','g','liter','ml','meter','cm','box','pack','set'] as $u)
                  <option value="{{ $u }}" {{ old('unit',$unitType)===$u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
              </select>
            </div>
            <div class="if-group">
              <label class="if-label">Amount</label>
              <input type="number" name="unit_amount" class="if-input" step="0.01" min="0.01" value="{{ old('unit_amount',$unitAmount) }}">
            </div>
          </div>
        </div>
        <div class="if-group">
          <label class="if-label">Short Description</label>
          <textarea name="short_description" class="if-input" style="min-height:60px;resize:vertical" maxlength="1000">{{ old('short_description',$product->short_description) }}</textarea>
        </div>
        <div class="if-group">
          <label class="if-label">Full Description</label>
          <textarea name="description" class="if-input" style="min-height:100px;resize:vertical">{{ old('description',$product->description) }}</textarea>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Basic Info
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('basic')">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 2 — TRANSLATIONS
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-translations">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8l6 6"/><path d="M4 14l6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="M22 22l-5-10-5 10"/><path d="M14 18h6"/></svg>
      Translations
      <span style="font-size:11px;font-weight:400;color:var(--mid);text-transform:none;letter-spacing:0">{{ count($translations) + 1 }} language{{ count($translations) + 1 !== 1 ? 's' : '' }}</span>
    </div>
    <button class="dc-edit-btn" onclick="openSection('translations')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    <div id="view-translations">
      <div class="tr-block">
        <div class="tr-lang">🇬🇧 English (main)</div>
        <div style="font-weight:600;font-size:13px">{{ $product->name }}</div>
        @if($product->short_description)
          <div style="font-size:12px;color:var(--mid);margin-top:4px">{{ Str::limit($product->short_description, 120) }}</div>
        @endif
      </div>
      @foreach($translations as $tr)
        @php $flags = ['ar'=>'🇦🇪','fr'=>'🇫🇷','de'=>'🇩🇪','es'=>'🇪🇸','it'=>'🇮🇹']; @endphp
        <div class="tr-block">
          <div class="tr-lang">{{ $flags[$tr['locale']] ?? '🌐' }} {{ strtoupper($tr['locale']) }}</div>
          <div style="font-weight:600;font-size:13px">{{ $tr['name'] }}</div>
          @if(!empty($tr['description']))
            <div style="font-size:12px;color:var(--mid);margin-top:4px">{{ Str::limit($tr['description'], 120) }}</div>
          @endif
        </div>
      @endforeach
      @if(empty($translations))
        <div style="color:var(--mid);font-size:13px">No additional translations — click Edit to add Arabic, French, etc.</div>
      @endif
    </div>

    <div id="edit-translations" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}" id="form-translations">
        @csrf
        <input type="hidden" name="_section" value="translations">
        <div id="tr-edit-rows">
          @foreach($translations as $i => $tr)
            <div class="tr-block" id="tredit-{{ $i }}" style="position:relative">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span class="tr-lang">{{ strtoupper($tr['locale']) }}</span>
                <button type="button" onclick="this.closest('[id^=tredit]').remove()" style="font-size:11px;color:var(--red);background:none;border:1px solid #fee2e2;border-radius:5px;padding:1px 7px;cursor:pointer">Remove</button>
              </div>
              <input type="hidden" name="translations[{{ $i }}][locale]" value="{{ $tr['locale'] }}">
              <div class="if-group">
                <label class="if-label">Name</label>
                <input type="text" name="translations[{{ $i }}][name]" class="if-input" value="{{ $tr['name'] }}" required maxlength="500">
              </div>
              <div class="if-group">
                <label class="if-label">Description</label>
                <textarea name="translations[{{ $i }}][description]" class="if-input" style="min-height:60px">{{ $tr['description'] ?? '' }}</textarea>
              </div>
            </div>
          @endforeach
        </div>
        <div style="margin-top:10px;display:flex;align-items:center;gap:8px">
          <select id="tr-add-locale" class="if-input" style="width:auto">
            <option value="">— Add language —</option>
            <option value="ar">🇦🇪 Arabic (ar)</option>
            <option value="fr">🇫🇷 French (fr)</option>
            <option value="de">🇩🇪 German (de)</option>
            <option value="es">🇪🇸 Spanish (es)</option>
            <option value="it">🇮🇹 Italian (it)</option>
          </select>
          <button type="button" class="dc-edit-btn" onclick="addTrRow()">+ Add</button>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Translations
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('translations')">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 3 — PRICING & INVENTORY
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-pricing">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      Pricing & Inventory
    </div>
    <button class="dc-edit-btn" onclick="openSection('pricing')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    <div id="view-pricing">
      @if($priceRange)
        <div class="dr">
          <span class="dr-label">Price Range</span>
          <span class="dr-value" style="font-weight:700;font-size:15px">
            @if($priceRange['min'] == $priceRange['max'])
              {{ number_format($priceRange['min'],2) }} EGP
            @else
              {{ number_format($priceRange['min'],2) }} – {{ number_format($priceRange['max'],2) }} EGP
            @endif
          </span>
        </div>
      @elseif($mainVar)
        <div class="dr"><span class="dr-label">Regular Price</span><span class="dr-value" style="font-weight:700">{{ number_format($mainVar->regular_price,2) }} EGP</span></div>
        @if($mainVar->sale_price && $mainVar->sale_price < $mainVar->regular_price)
          <div class="dr"><span class="dr-label">Sale Price</span><span class="dr-value" style="color:var(--red);font-weight:700">{{ number_format($mainVar->sale_price,2) }} EGP</span></div>
        @endif
      @endif
      <div class="dr"><span class="dr-label">Discount</span><span class="dr-value">{{ $discountPct > 0 ? $discountPct.'%' : 'No discount' }}</span></div>
      <div class="dr"><span class="dr-label">Total Stock</span>
        <span class="dr-value" style="font-weight:600;color:{{ $totalStock > 10 ? 'var(--green)' : ($totalStock > 0 ? 'var(--yellow)' : 'var(--red)') }}">
          {{ $totalStock }} units
        </span>
      </div>
      <div class="dr"><span class="dr-label">Min Order Qty</span><span class="dr-value">{{ $product->minimum_order_qty ?? 1 }}</span></div>
      <div class="dr"><span class="dr-label">Max Per Person</span><span class="dr-value">{{ $product->max_orders_per_person ? $product->max_orders_per_person : 'Unlimited' }}</span></div>
    </div>

    <div id="edit-pricing" style="display:none">
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;margin-bottom:12px">
        To update individual variation prices and stock, use the <strong>Variations</strong> section below.
        Here you can update the global discount and order limits.
      </div>
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="pricing">
        <div class="if-grid">
          <div class="if-group">
            <label class="if-label">Discount % <span style="color:var(--mid);font-weight:400;text-transform:none">0 = no discount, max 80</span></label>
            <input type="number" name="discount_percentage" class="if-input" step="0.01" min="0" max="80.99"
                   value="{{ old('discount_percentage',$product->discount_percentage ?? 0) }}" placeholder="0">
          </div>
          <div class="if-group">
            <label class="if-label">Min Order Qty</label>
            <input type="number" name="minimum_order_qty" class="if-input" min="1" max="100"
                   value="{{ old('minimum_order_qty',$product->minimum_order_qty ?? 1) }}">
          </div>
        </div>
        <div class="if-group" style="max-width:200px">
          <label class="if-label">Max Per Person <span style="color:var(--mid);font-weight:400;text-transform:none">0 = unlimited</span></label>
          <input type="number" name="max_orders_per_person" class="if-input" min="0" max="100"
                 value="{{ old('max_orders_per_person',$product->max_orders_per_person ?? 0) }}">
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Pricing
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('pricing')">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 4 — VARIATIONS
════════════════════════════════════════════════════════════════ --}}
@php
  // Group variations by color for display
  $colorGroups = [];
  foreach ($dbVariations as $dv) {
    $attrs = $dv->attributes ?? [];
    $color = $attrs['Color'] ?? 'Default';
    $size  = $attrs['Size'] ?? (implode(' / ', array_filter($attrs, fn($k) => strtolower($k) !== 'color', ARRAY_FILTER_USE_KEY)) ?: 'Default');
    if (!isset($colorGroups[$color])) $colorGroups[$color] = [];
    $colorGroups[$color][] = ['size' => $size, 'price' => $dv->price, 'reg' => $dv->regular_price, 'sale' => $dv->sale_price, 'stock' => $dv->stock_quantity, 'main' => $dv->main_variation];
  }
  $editColorRows4 = [];
  foreach ($colorGroups as $colorName => $rows) {
    $sizes = array_column($rows, 'size');
    $priceMap = []; $stockMap = []; $salePriceMap4 = [];
    foreach ($rows as $r) {
      $priceMap[$r['size']] = $r['reg'];
      $stockMap[$r['size']] = $r['stock'];
      $dvSale4 = (float)($r['sale'] ?? 0);
      $dvReg4  = (float)($r['reg']  ?? 0);
      if ($dvSale4 > 0 && $dvReg4 > 0 && $dvSale4 < $dvReg4) {
        $salePriceMap4[$r['size']] = $dvSale4;
      }
    }
    $editColorRows4[] = ['name' => $colorName, 'sizes' => $sizes, 'price_map' => $priceMap, 'sale_price_map' => $salePriceMap4, 'stock' => $stockMap, 'sale_price' => '', 'images' => []];
  }
@endphp
<div class="dc" id="dc-variations">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
      Variations
      <span style="font-size:11px;font-weight:400;color:var(--mid);text-transform:none;letter-spacing:0">
        {{ $dbVariations->count() }} variation{{ $dbVariations->count() !== 1 ? 's' : '' }}
        @if(count($colorGroups) > 0) · {{ count($colorGroups) }} color{{ count($colorGroups) !== 1 ? 's' : '' }} @endif
      </span>
    </div>
    <button class="dc-edit-btn" onclick="openSection('variations')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    <div id="view-variations">
      @if($dbVariations->isEmpty())
        <span style="color:var(--mid);font-size:13px">No variations defined.</span>
      @else
        <table class="var-table">
          <thead>
            <tr>
              <th>Color</th><th>Size / Option</th>
              <th>Regular</th><th>Sale / Price</th><th>Stock</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dbVariations as $dv)
              @php
                $dvAttrs = $dv->attributes ?? [];
                $dvColor = $dvAttrs['Color'] ?? '—';
                $dvSize  = $dvAttrs['Size']  ?? implode(' / ', array_filter($dvAttrs, fn($k) => strtolower($k) !== 'color', ARRAY_FILTER_USE_KEY)) ?: '—';
              @endphp
              <tr>
                <td>
                  {{ $dvColor }}
                  @if($dv->main_variation)<span class="main-chip">main</span>@endif
                </td>
                <td>{{ $dvSize }}</td>
                <td>{{ number_format($dv->regular_price,2) }}</td>
                <td style="{{ $dv->sale_price && $dv->sale_price < $dv->regular_price ? 'color:var(--red);font-weight:600' : '' }}">
                  {{ $dv->sale_price ? number_format($dv->sale_price,2) : number_format($dv->price,2) }}
                </td>
                <td style="color:{{ $dv->stock_quantity > 10 ? 'var(--green)' : ($dv->stock_quantity > 0 ? 'var(--yellow)' : 'var(--red)') }};font-weight:600">
                  {{ $dv->stock_quantity }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div id="edit-variations" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}" enctype="multipart/form-data" id="form-variations">
        @csrf
        <input type="hidden" name="_section" value="variations">
        <div style="margin-bottom:12px">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer">
            <input type="checkbox" name="has_variations" id="var-toggle-show" value="1"
                   {{ $hasVariations ? 'checked' : '' }} onchange="toggleVarPanel(this.checked)">
            This product has Color + Size variations
          </label>
        </div>
        <div id="var-panel-show" style="{{ $hasVariations ? '' : 'display:none' }}">
          <div id="color-rows-show"></div>
          <button type="button" class="add-color-btn" onclick="addColorRowShow()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Color
          </button>
        </div>
        {{-- Simple product fields when no variations --}}
        <div id="simple-panel-show" style="{{ $hasVariations ? 'display:none' : '' }}">
          <div class="if-grid">
            <div class="if-group">
              <label class="if-label">Regular Price (EGP) <span style="color:var(--red)">*</span></label>
              <input type="number" name="regular_price" class="if-input" step="0.01" min="0"
                     value="{{ $mainVar->regular_price ?? '' }}" placeholder="0.00">
            </div>
            <div class="if-group">
              <label class="if-label">Sale Price (EGP)</label>
              <input type="number" name="sale_price" class="if-input" step="0.01" min="0"
                     value="{{ $mainVar->sale_price ?? '' }}" placeholder="Leave blank if none">
            </div>
            <div class="if-group">
              <label class="if-label">Stock Quantity <span style="color:var(--red)">*</span></label>
              <input type="number" name="stock_quantity" class="if-input" min="0"
                     value="{{ $mainVar->stock_quantity ?? 0 }}" placeholder="0">
            </div>
          </div>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Variations
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('variations')">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 5 — ATTRIBUTES
════════════════════════════════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════════
     SECTION 4b — VARIATION IMAGES
════════════════════════════════════════════════════════════════ --}}
@if($dbVariations->isNotEmpty())
@php $varImgCount = $dbVariations->sum(fn($v) => count($v->images ?? [])); @endphp
<div class="dc" id="dc-varimages">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      Variation Images
      <span style="font-size:11px;font-weight:400;color:var(--mid);text-transform:none;letter-spacing:0">{{ $varImgCount }} image{{ $varImgCount !== 1 ? 's' : '' }}</span>
    </div>
    <button class="dc-edit-btn" onclick="openSection('varimages')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    <div id="view-varimages">
      @php $hasAnyVarImg = false; $seenVarColors = []; @endphp
      @foreach($dbVariations as $dv)
        @php
          $dvA = $dv->attributes ?? [];
          $dvColor = $dvA['Color'] ?? 'Default';
          $dvSize  = $dvA['Size']  ?? '';
          $dvImgs  = $dv->images ?? [];
          if (empty($dvImgs)) continue;
          $hasAnyVarImg = true;
        @endphp
        <div class="dr" style="align-items:flex-start;margin-bottom:8px">
          <span class="dr-label" style="font-weight:600">{{ $dvColor }}{{ $dvSize ? ' / '.$dvSize : '' }}</span>
          <div class="img-grid" style="margin-top:0">
            @foreach($dvImgs as $vImg)
              <img src="{{ $imgBase . ltrim($vImg, '/') }}" class="img-thumb" alt="" onerror="this.style.display='none'">
            @endforeach
          </div>
        </div>
      @endforeach
      @if(!$hasAnyVarImg)
        <span style="color:var(--mid);font-size:13px">No variation images yet — click Edit to upload per color.</span>
      @endif
    </div>

    <div id="edit-varimages" style="display:none">
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:9px 14px;font-size:12px;color:#92400e;margin-bottom:14px">
        Each variation row saves independently. Check existing images to delete them, then click <strong>Save</strong>.
      </div>
      @foreach($dbVariations as $dv)
        @php
          $dvA     = $dv->attributes ?? [];
          $dvColor = $dvA['Color'] ?? 'Default';
          $dvSize  = $dvA['Size']  ?? '';
          $dvLabel = $dvColor . ($dvSize ? ' / '.$dvSize : '');
          $dvImgs  = $dv->images ?? [];
        @endphp
        <div class="color-row" style="margin-bottom:14px">
          <div class="color-row-header">
            <span class="color-row-badge">{{ $dvLabel }}</span>
            @if($dv->main_variation)<span style="font-size:10px;color:var(--mid);margin-left:6px">★ main</span>@endif
          </div>
          @if(!empty($dvImgs))
            <div style="margin-bottom:10px">
              <label class="if-label">Current — check to remove on save</label>
              <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px">
                @foreach($dvImgs as $vImg)
                  <label style="position:relative;cursor:pointer" title="Check to delete">
                    <img src="{{ $imgBase . ltrim($vImg, '/') }}"
                         style="width:72px;height:72px;border-radius:8px;object-fit:cover;border:2px solid var(--light);display:block"
                         onerror="this.style.opacity='.25'">
                    <input type="checkbox" name="delete_images[]" value="{{ $vImg }}"
                           form="vi-form-{{ $dv->id }}"
                           style="position:absolute;top:3px;right:3px;accent-color:#ef4444;width:16px;height:16px">
                  </label>
                @endforeach
              </div>
            </div>
          @endif
          <form id="vi-form-{{ $dv->id }}"
                method="POST"
                action="{{ $sectionUrl }}"
                enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_section"     value="var_images">
            <input type="hidden" name="variation_id" value="{{ $dv->id }}">
            <div class="if-group">
              <label class="if-label">Add New Images</label>
              <label for="vi-file-{{ $dv->id }}" class="img-drop" style="min-height:54px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Click to choose images (jpg / png / webp)</span>
              </label>
              <input type="file" id="vi-file-{{ $dv->id }}" name="new_images[]"
                     accept="image/jpeg,image/png,image/webp" multiple style="display:none"
                     onchange="this.previousElementSibling.classList.add('has-file');this.previousElementSibling.querySelector('span').textContent='✓ '+this.files.length+' file(s) selected'">
            </div>
            <div style="display:flex;gap:8px">
              <button type="submit" class="dc-save-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Save
              </button>
            </div>
          </form>
        </div>
      @endforeach
      <div style="margin-top:2px">
        <button type="button" class="dc-cancel-btn" onclick="closeSection('varimages')">Close</button>
      </div>
    </div>

  </div>
</div>
@endif

<div class="dc" id="dc-attributes">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
      Product Attributes
      <span style="font-size:11px;font-weight:400;color:var(--mid);text-transform:none;letter-spacing:0">{{ count($attributes) }} attribute{{ count($attributes) !== 1 ? 's' : '' }}</span>
    </div>
    <button class="dc-edit-btn" onclick="openSection('attributes')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">
    <div id="view-attributes">
      @if(empty($attributes))
        <span style="color:var(--mid);font-size:13px">No attributes defined.</span>
      @else
        @foreach($attributes as $attr)
          <div class="dr" style="margin-bottom:8px">
            <span class="dr-label" style="font-weight:600">{{ $attr['name'] }}</span>
            <span class="dr-value">
              @foreach((array)($attr['values'] ?? []) as $v)
                <span class="tag-pill">{{ $v }}</span>
              @endforeach
            </span>
          </div>
        @endforeach
      @endif
    </div>
    <div id="edit-attributes" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="attributes">
        <div id="attr-edit-rows">
          @foreach($attributes as $ai => $attr)
            <div class="tr-block" id="attredit-{{ $ai }}" style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px">
              <div style="flex:1">
                <div class="if-group">
                  <label class="if-label">Attribute Name</label>
                  <input type="text" name="prod_attributes[{{ $ai }}][name]" class="if-input"
                         value="{{ $attr['name'] }}" placeholder="e.g. Material" maxlength="100">
                </div>
                <div class="if-group">
                  <label class="if-label">Values (comma-separated)</label>
                  <input type="text" name="prod_attributes[{{ $ai }}][values]" class="if-input"
                         value="{{ implode(', ', (array)($attr['values'] ?? [])) }}"
                         placeholder="e.g. Cotton, Polyester">
                </div>
              </div>
              <button type="button" onclick="this.closest('[id^=attredit]').remove()"
                style="margin-top:20px;font-size:11px;color:var(--red);background:none;border:1px solid #fee2e2;border-radius:6px;padding:4px 8px;cursor:pointer;flex-shrink:0">
                Remove
              </button>
            </div>
          @endforeach
        </div>
        <button type="button" class="add-color-btn" onclick="addAttrRowShow()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Attribute
        </button>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Attributes
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('attributes')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 6 — TAGS
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-tags">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      Tags
    </div>
    <button class="dc-edit-btn" onclick="openSection('tags')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">
    <div id="view-tags">
      @if(empty($tags))
        <span style="color:var(--mid);font-size:13px">No tags — click Edit to add.</span>
      @else
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          @foreach($tags as $tag)<span class="tag-pill">{{ $tag }}</span>@endforeach
        </div>
      @endif
    </div>
    <div id="edit-tags" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="tags">
        <div class="if-group">
          <label class="if-label">Tags <span style="color:var(--mid);font-weight:400;text-transform:none">— comma-separated</span></label>
          <input type="text" name="tags_input" class="if-input"
                 value="{{ implode(', ', $tags) }}"
                 placeholder="e.g. polo, cotton, summer">
          <div style="font-size:11px;color:var(--mid);margin-top:4px">Separate each tag with a comma</div>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Tags
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('tags')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 7 — CATEGORIES
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-categories">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
      Categories
    </div>
    <button class="dc-edit-btn" onclick="openSection('categories')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">
    <div id="view-categories">
      @php $selCatNames = $categories->whereIn('id', $selectedCats)->pluck('name'); @endphp
      @if($selCatNames->isEmpty())
        <span style="color:var(--mid);font-size:13px">No categories selected.</span>
      @else
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          @foreach($selCatNames as $cn)<span class="cat-chip">{{ $cn }}</span>@endforeach
        </div>
      @endif
    </div>
    <div id="edit-categories" style="display:none">
      @php
        $topLevel = $categories->where('parent', 0)->values();
        $children = $categories->where('parent', '!=', 0)->groupBy('parent');
      @endphp
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="categories">
        <div class="cat-grid-check">
          @foreach($topLevel as $parent)
            <div>
              <div style="font-size:11px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px">{{ $parent->name }}</div>
              <label class="cat-check-label">
                <input type="checkbox" name="categories[]" value="{{ $parent->id }}"
                       {{ in_array($parent->id, $selectedCats) ? 'checked' : '' }}>
                {{ $parent->name }}
              </label>
              @foreach($children->get($parent->id, collect()) as $child)
                <label class="cat-check-label" style="padding-left:14px">
                  <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                         {{ in_array($child->id, $selectedCats) ? 'checked' : '' }}>
                  {{ $child->name }}
                </label>
              @endforeach
            </div>
          @endforeach
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Categories
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('categories')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 8 — IMAGES
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-images">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      Product Images
    </div>
    <button class="dc-edit-btn" onclick="openSection('images')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">

    {{-- ── View mode ── --}}
    <div id="view-images">
      @php
        $allOthers = array_merge($images['others'] ?? [], $images['other_images'] ?? []);
        $naturals  = $images['natural'] ?? $images['natural_images'] ?? [];
      @endphp

      @if(!empty($images['thumbnail']))
        <div class="dr" style="align-items:flex-start;margin-bottom:12px">
          <span class="dr-label">Thumbnail</span>
          <img src="{{ $imgBase . ltrim($images['thumbnail'],'/') }}" class="img-thumb"
               style="width:80px;height:80px" alt="" onerror="this.style.opacity='.25'">
        </div>
      @endif

      @if(!empty($allOthers))
        <div style="margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
            📷 Product Images ({{ count($allOthers) }})
          </div>
          <div class="img-grid">
            @foreach($allOthers as $oi)
              <img src="{{ $imgBase . ltrim($oi,'/') }}" class="img-thumb" alt=""
                   onerror="this.style.opacity='.2'">
            @endforeach
          </div>
        </div>
      @endif

      @if(!empty($naturals))
        <div style="margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
            🌿 Natural / Lifestyle Images ({{ count($naturals) }})
          </div>
          <div class="img-grid">
            @foreach($naturals as $ni)
              <img src="{{ $imgBase . ltrim($ni,'/') }}" class="img-thumb" alt=""
                   onerror="this.style.opacity='.2'">
            @endforeach
          </div>
        </div>
      @endif

      @if(empty($images['thumbnail']) && empty($allOthers) && empty($naturals))
        <span style="color:var(--mid);font-size:13px">No images uploaded yet — click Edit to add.</span>
      @endif
    </div>

    {{-- ── Edit mode ── --}}
    <div id="edit-images" style="display:none">
      <style>
        .img-del-grid { display:flex; flex-wrap:wrap; gap:10px; margin:8px 0 4px; }
        .img-del-item { position:relative; width:72px; height:72px; border-radius:8px; overflow:hidden; border:1.5px solid var(--light); flex-shrink:0; }
        .img-del-item img { width:100%; height:100%; object-fit:cover; display:block; }
        .img-del-item input[type=checkbox] { position:absolute; top:4px; left:4px; width:16px; height:16px; cursor:pointer; accent-color:var(--red); }
        .img-del-item:has(input:checked)::after {
          content:''; position:absolute; inset:0;
          background:rgba(239,68,68,.35); border-radius:6px;
          pointer-events:none;
        }
        .img-del-item:has(input:checked) { border-color:var(--red); }
        .del-hint { font-size:11px; color:var(--mid); margin-bottom:10px; }
        .img-section-divider { border:none; border-top:1px solid var(--light); margin:18px 0; }
        .img-section-title { font-size:11px; font-weight:700; color:var(--mid); text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
      </style>

      <form method="POST" action="{{ $sectionUrl }}" enctype="multipart/form-data" id="form-images">
        @csrf
        <input type="hidden" name="_section" value="images">

        {{-- ── Thumbnail ── --}}
        <div class="if-group">
          <div class="img-section-title">🖼️ Main Thumbnail</div>
          @if(!empty($images['thumbnail']))
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <div style="position:relative;width:72px;height:72px;border-radius:8px;overflow:hidden;border:1.5px solid var(--light)">
                <img src="{{ $imgBase . ltrim($images['thumbnail'],'/') }}"
                     style="width:100%;height:100%;object-fit:cover" alt=""
                     onerror="this.style.opacity='.2'">
              </div>
              <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--red);cursor:pointer;font-weight:600">
                <input type="checkbox" name="delete_thumbnail" value="1"> Remove thumbnail
              </label>
            </div>
            <div style="font-size:11px;color:var(--mid);margin-bottom:8px">Or upload a new image to replace it:</div>
          @endif
          <label for="si-thumb" class="img-drop">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <span id="si-thumb-label">{{ empty($images['thumbnail']) ? 'Click to upload thumbnail' : 'Click to replace thumbnail' }}</span>
          </label>
          <input type="file" id="si-thumb" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display:none"
                 onchange="siPreview(this,'si-thumb-label','si-thumb-preview')">
          <div id="si-thumb-preview" style="display:none;margin-top:8px"></div>
        </div>

        <hr class="img-section-divider">

        {{-- ── Product Images (other_images) ── --}}
        <div class="if-group">
          <div class="img-section-title">📷 Product Images</div>
          @php $allOthersEdit = array_merge($images['others'] ?? [], $images['other_images'] ?? []); @endphp
          @if(!empty($allOthersEdit))
            <div class="del-hint">Check images to delete them on save:</div>
            <div class="img-del-grid">
              @foreach($allOthersEdit as $oi)
                <div class="img-del-item">
                  <img src="{{ $imgBase . ltrim($oi,'/') }}" alt="" onerror="this.style.opacity='.2'">
                  <input type="checkbox" name="delete_other_images[]" value="{{ $oi }}" title="Delete this image">
                </div>
              @endforeach
            </div>
          @endif
          <div style="margin-top:10px">
            <label for="si-gallery" class="img-drop">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span id="si-gallery-label">Add product images</span>
            </label>
            <input type="file" id="si-gallery" name="other_images[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none"
                   onchange="siPreviewMulti(this,'si-gallery-label','si-gallery-preview')">
            <div id="si-gallery-preview" style="display:none;margin-top:8px"></div>
          </div>
        </div>

        <hr class="img-section-divider">

        {{-- ── Natural / Lifestyle Images ── --}}
        <div class="if-group">
          <div class="img-section-title">🌿 Natural / Lifestyle Images</div>
          @php $naturalsEdit = $images['natural'] ?? $images['natural_images'] ?? []; @endphp
          @if(!empty($naturalsEdit))
            <div class="del-hint">Check images to delete them on save:</div>
            <div class="img-del-grid">
              @foreach($naturalsEdit as $ni)
                <div class="img-del-item">
                  <img src="{{ $imgBase . ltrim($ni,'/') }}" alt="" onerror="this.style.opacity='.2'">
                  <input type="checkbox" name="delete_natural_images[]" value="{{ $ni }}" title="Delete this image">
                </div>
              @endforeach
            </div>
          @endif
          <div style="margin-top:10px">
            <label for="si-natural" class="img-drop">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span id="si-natural-label">Add natural / lifestyle images</span>
            </label>
            <input type="file" id="si-natural" name="natural_images[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none"
                   onchange="siPreviewMulti(this,'si-natural-label','si-natural-preview')">
            <div id="si-natural-preview" style="display:none;margin-top:8px"></div>
          </div>
        </div>

        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save Images
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('images')">Cancel</button>
        </div>
      </form>
    </div>

  </div>
</div>
<script>
function siPreview(input, labelId, previewId) {
  const label = document.getElementById(labelId);
  const preview = document.getElementById(previewId);
  if (!input.files.length) return;
  if (label) label.textContent = '✓ ' + input.files[0].name;
  if (preview) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.innerHTML = `<img src="${e.target.result}" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1.5px solid var(--light)">`;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
  input.closest('label')?.classList.add('has-file');
}
function siPreviewMulti(input, labelId, previewId) {
  const label = document.getElementById(labelId);
  const preview = document.getElementById(previewId);
  if (!input.files.length) return;
  if (label) label.textContent = '✓ ' + input.files.length + ' file(s) selected';
  if (preview) {
    preview.innerHTML = '';
    preview.style.display = 'flex';
    preview.style.flexWrap = 'wrap';
    preview.style.gap = '8px';
    Array.from(input.files).forEach(file => {
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:8px;border:1.5px solid var(--light)';
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  }
}
</script>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 9 — WHATSAPP
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-whatsapp">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
      WhatsApp Contact
    </div>
    <button class="dc-edit-btn" onclick="openSection('whatsapp')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">
    <div id="view-whatsapp">
      <div class="dr"><span class="dr-label">Enabled</span>
        <span class="dr-value">{{ !empty($whatsappData['available']) ? '✓ Yes' : 'No' }}</span>
      </div>
      @if(!empty($whatsappData['number']))
        <div class="dr"><span class="dr-label">Number</span>
          <span class="dr-value mono">{{ $whatsappData['number'] }}</span>
        </div>
      @endif
    </div>
    <div id="edit-whatsapp" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="whatsapp">
        <div class="if-group">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer">
            <input type="checkbox" name="whatsapp_available" value="1"
                   {{ !empty($whatsappData['available']) ? 'checked' : '' }}
                   id="wa-available-show"
                   onchange="document.getElementById('wa-num-wrap').style.display=this.checked?'':'none'">
            Enable WhatsApp contact for this product
          </label>
        </div>
        <div id="wa-num-wrap" style="{{ !empty($whatsappData['available']) ? '' : 'display:none' }}">
          <div class="if-group">
            <label class="if-label">WhatsApp Number</label>
            <input type="text" name="whatsapp_number" class="if-input"
                   value="{{ $whatsappData['number'] ?? '' }}" placeholder="e.g. 01012345678" maxlength="20">
          </div>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save WhatsApp
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('whatsapp')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SECTION 10 — COUPON ATTACHMENT
════════════════════════════════════════════════════════════════ --}}
<div class="dc" id="dc-coupon">
  <div class="dc-head">
    <div class="dc-title">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      Coupon Attachment
    </div>
    <button class="dc-edit-btn" onclick="openSection('coupon')">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </button>
  </div>
  <div class="dc-body">
    <div id="view-coupon">
      @if($attachedCoupon)
        @php
          $acp = $variation ? (float)$variation->price : 0;
          $acprice = $attachedCoupon->discount_type === 'percent'
              ? $acp * (1 - (float)$attachedCoupon->amount / 100)
              : max(0, $acp - (float)$attachedCoupon->amount);
        @endphp
        <div style="display:inline-flex;border-radius:8px;overflow:hidden;font-size:12px;font-weight:700">
          <span style="background:#7c3aed;color:#fff;padding:7px 14px;display:flex;align-items:center;gap:6px">
            🏷️ WITH CODE <span style="background:rgba(255,255,255,.2);border-radius:4px;padding:1px 7px">{{ strtoupper($attachedCoupon->code) }}</span>
            ({{ $attachedCoupon->discount_type === 'percent' ? $attachedCoupon->amount.'%' : number_format((float)$attachedCoupon->amount,2).' EGP' }} off)
          </span>
          @if($acp > 0)<span style="background:#5b21b6;color:#fff;padding:7px 14px">↓ {{ number_format($acprice,2) }} EGP</span>@endif
        </div>
        <p style="font-size:11px;color:var(--mid);margin-top:8px">This coupon banner will appear on the product card in the storefront.</p>
      @else
        <span style="color:var(--mid);font-size:13px">No coupon attached — click Edit to attach one.</span>
      @endif
    </div>
    <div id="edit-coupon" style="display:none">
      <form method="POST" action="{{ $sectionUrl }}">
        @csrf
        <input type="hidden" name="_section" value="coupon">
        <div class="if-group">
          <label class="if-label">Attach Coupon</label>
          <select name="coupon_id" class="if-input" style="background:#fff">
            <option value="0">— None (remove coupon banner) —</option>
            @foreach($allCoupons as $cp)
              <option value="{{ $cp->id }}" {{ $attachedCoupon && $attachedCoupon->id === $cp->id ? 'selected' : '' }}>
                {{ strtoupper($cp->code) }}
                — {{ $cp->discount_type === 'percent' ? $cp->amount.'% off' : number_format((float)$cp->amount,2).' EGP off' }}
              </option>
            @endforeach
          </select>
          <p style="font-size:11px;color:var(--mid);margin-top:6px">The banner will show on this product's card in the storefront. One coupon per product at a time.</p>
        </div>
        <div class="if-actions">
          <button type="submit" class="dc-save-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Save
          </button>
          <button type="button" class="dc-cancel-btn" onclick="closeSection('coupon')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ─── DATA FOR JS ─── --}}
<script>
const SHOW_EDIT_COLOR_ROWS = {!! json_encode($editColorRows4) !!};
let showColorIdx = 0;

// ─── Section open/close ─────────────────────────────────────────
function openSection(name) {
  document.getElementById('view-' + name).style.display = 'none';
  document.getElementById('edit-' + name).style.display = '';
  document.getElementById('dc-' + name).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function closeSection(name) {
  document.getElementById('edit-' + name).style.display = 'none';
  document.getElementById('view-' + name).style.display = '';
}

// ─── Auto-open if section param in URL ──────────────────────────
(function() {
  const p = new URLSearchParams(window.location.search);
  const s = p.get('section');
  if (s) openSection(s);
})();

// ─── Translation add row ────────────────────────────────────────
let trIdx = {{ count($translations) }};
const flags = {ar:'🇦🇪',fr:'🇫🇷',de:'🇩🇪',es:'🇪🇸',it:'🇮🇹'};
function addTrRow() {
  const sel = document.getElementById('tr-add-locale');
  const loc = sel.value;
  if (!loc) return;
  const container = document.getElementById('tr-edit-rows');
  const div = document.createElement('div');
  div.className = 'tr-block';
  div.id = 'tredit-' + trIdx;
  div.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
      <span class="tr-lang">${flags[loc]||'🌐'} ${loc.toUpperCase()}</span>
      <button type="button" onclick="this.closest('[id^=tredit]').remove()"
        style="font-size:11px;color:var(--red);background:none;border:1px solid #fee2e2;border-radius:5px;padding:1px 7px;cursor:pointer">Remove</button>
    </div>
    <input type="hidden" name="translations[${trIdx}][locale]" value="${loc}">
    <div class="if-group">
      <label class="if-label">Name</label>
      <input type="text" name="translations[${trIdx}][name]" class="if-input" required maxlength="500">
    </div>
    <div class="if-group">
      <label class="if-label">Description</label>
      <textarea name="translations[${trIdx}][description]" class="if-input" style="min-height:60px"></textarea>
    </div>`;
  container.appendChild(div);
  trIdx++;
  sel.value = '';
}

// ─── Attribute add row ──────────────────────────────────────────
let attrIdx = {{ count($attributes) }};
function addAttrRowShow() {
  const container = document.getElementById('attr-edit-rows');
  const div = document.createElement('div');
  div.className = 'tr-block';
  div.id = 'attredit-' + attrIdx;
  div.style.cssText = 'display:flex;align-items:flex-start;gap:10px;margin-bottom:8px';
  div.innerHTML = `
    <div style="flex:1">
      <div class="if-group">
        <label class="if-label">Attribute Name</label>
        <input type="text" name="prod_attributes[${attrIdx}][name]" class="if-input" placeholder="e.g. Material" maxlength="100">
      </div>
      <div class="if-group">
        <label class="if-label">Values (comma-separated)</label>
        <input type="text" name="prod_attributes[${attrIdx}][values]" class="if-input" placeholder="e.g. Cotton, Polyester">
      </div>
    </div>
    <button type="button" onclick="this.closest('[id^=attredit]').remove()"
      style="margin-top:20px;font-size:11px;color:var(--red);background:none;border:1px solid #fee2e2;border-radius:6px;padding:4px 8px;cursor:pointer;flex-shrink:0">
      Remove
    </button>`;
  container.appendChild(div);
  attrIdx++;
}

// ─── Variation panel toggle ─────────────────────────────────────
function toggleVarPanel(on) {
  document.getElementById('var-panel-show').style.display   = on ? '' : 'none';
  document.getElementById('simple-panel-show').style.display = on ? 'none' : '';
}

// ─── Effective price recalc (show page) ─────────────────────────
function recalcEffectiveShow(idx, size) {
  const container = document.getElementById('csprice-' + idx);
  if (!container) return;
  const effCell   = container.querySelector(`.eff-price-cell[data-size-key="${CSS.escape(size)}"]`);
  const regInput  = container.querySelector(`input[data-role="regular"][data-size="${size}"]`);
  const saleInput = container.querySelector(`input[data-role="sale"][data-size="${size}"]`);
  if (!effCell || !regInput) return;
  const reg  = parseFloat(regInput.value) || 0;
  const sale = parseFloat(saleInput?.value || '') || 0;
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

// ─── Color+Size builder (show page version) ─────────────────────
function addColorRowShow(colorData) {
  const idx          = showColorIdx++;
  const colorName    = colorData?.name || '';
  const sizes        = colorData?.sizes || [];
  const priceMap     = colorData?.price_map || {};
  const salePriceMap = colorData?.sale_price_map || {};
  const stockMap     = colorData?.stock || {};
  const salePriceVal = colorData?.sale_price || '';
  const isFirst      = document.getElementById('color-rows-show').children.length === 0;

  const row = document.createElement('div');
  row.className = 'color-row' + (isFirst ? ' is-main' : '');
  row.id = 'csrow-' + idx;
  row.dataset.colorIdx = idx;

  row.innerHTML = `
    <div class="color-row-header">
      <span class="color-row-badge">${isFirst ? '★ Main Color' : 'Color #'+(document.getElementById('color-rows-show').children.length+1)}</span>
      <div style="display:flex;gap:8px;align-items:center">
        <input type="text" name="colors[${idx}][name]" class="if-input" placeholder="Color name"
               value="${colorName}" style="width:200px" onblur="refreshPriceTableShow(${idx})">
        ${isFirst ? '' : `<button type="button" class="color-row-remove" onclick="document.getElementById('csrow-${idx}').remove()">× Remove</button>`}
      </div>
    </div>
    <div class="if-group">
      <label class="if-label">Color Images <span style="color:var(--mid);font-weight:400;text-transform:none">— photos for this color</span></label>
      <label for="csimg-${idx}" class="img-drop" style="min-height:60px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <span>Upload color images</span>
      </label>
      <input type="file" id="csimg-${idx}" name="variation_images[${idx}][]"
             accept="image/jpeg,image/png,image/webp" multiple style="display:none"
             onchange="this.previousElementSibling.classList.add('has-file');this.previousElementSibling.querySelector('span').textContent='✓ '+this.files.length+' image(s)'">
    </div>
    <div class="if-group">
      <label class="if-label">Sizes <span style="color:var(--red)">*</span> — press Enter or comma to add</label>
      <div class="size-tags-wrap" id="csstags-${idx}" onclick="document.getElementById('cssize-${idx}').focus()">
        <input type="text" id="cssize-${idx}" class="size-add-input" placeholder="Add size…"
               onkeydown="handleSizeInputShow(event,${idx})">
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px">
        ${['XS','S','M','L','XL','XXL','XXXL'].map(s=>`<button type="button" class="preset-btn" onclick="addSizeShow(${idx},'${s}')">${s}</button>`).join('')}
        ${['36','37','38','39','40','41','42','43','44','45'].map(s=>`<button type="button" class="preset-btn" onclick="addSizeShow(${idx},'${s}')">${s}</button>`).join('')}
      </div>
    </div>
    <div id="csprice-${idx}"></div>`;

  document.getElementById('color-rows-show').appendChild(row);
  sizes.forEach(s => addSizeShow(idx, s, priceMap[s] || '', salePriceMap[s] || '', stockMap[s] || 0));
}

function handleSizeInputShow(e, idx) {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault();
    const v = e.target.value.trim().replace(/,/g,'');
    if (v) { addSizeShow(idx, v); e.target.value=''; }
  }
}
const showSizeData = {};
function addSizeShow(idx, size, price, salePrice, stock) {
  if (!size) return;
  if (!showSizeData[idx]) showSizeData[idx] = {};
  if (showSizeData[idx][size]) return;
  showSizeData[idx][size] = { price: price||'', salePrice: salePrice||'', stock: stock||0 };

  const wrap  = document.getElementById('csstags-' + idx);
  const input = document.getElementById('cssize-' + idx);
  const tag   = document.createElement('span');
  tag.className  = 'size-tag';
  tag.dataset.size = size;
  tag.innerHTML  = `${size}<button type="button" onclick="removeSizeShow(${idx},'${size.replace(/'/g,"\\'")}')">×</button>`;
  wrap.insertBefore(tag, input);
  refreshPriceTableShow(idx);
}
function removeSizeShow(idx, size) {
  const tag = document.querySelector(`#csstags-${idx} [data-size="${size}"]`);
  if (tag) tag.remove();
  if (showSizeData[idx]) delete showSizeData[idx][size];
  refreshPriceTableShow(idx);
}
function refreshPriceTableShow(idx) {
  const container = document.getElementById('csprice-' + idx);
  if (!container) return;
  const tags = document.querySelectorAll('#csstags-' + idx + ' .size-tag');
  if (tags.length === 0) { container.innerHTML = ''; return; }
  let html = `<label class="if-label" style="margin-top:10px">Per-Size Pricing & Stock</label>
    <table class="price-table">
      <thead><tr>
        <th>Size</th>
        <th>Regular Price (EGP)</th>
        <th>Sale Price (EGP) <span style="font-weight:400;font-size:10px;color:var(--mid)">optional</span></th>
        <th>Stock Qty</th>
        <th style="background:#fff8f5;color:var(--orange)">Effective Price</th>
      </tr></thead>
      <tbody>`;
  tags.forEach(tag => {
    const s = tag.dataset.size;
    const d = (showSizeData[idx]||{})[s] || {};
    html += `<tr>
      <td><strong>${s}</strong><input type="hidden" name="colors[${idx}][sizes][]" value="${s}"></td>
      <td><input type="number" name="colors[${idx}][price_map][${s}]"
                 value="${d.price||''}" step="0.01" min="0" placeholder="0.00"
                 data-role="regular" data-size="${s}"
                 oninput="recalcEffectiveShow(${idx},'${s.replace(/'/g,"\\'")}')"
                 class="price-table" style="width:95px"></td>
      <td><input type="number" name="colors[${idx}][sale_price_map][${s}]"
                 value="${d.salePrice||''}" step="0.01" min="0" placeholder="leave blank"
                 data-role="sale" data-size="${s}"
                 oninput="recalcEffectiveShow(${idx},'${s.replace(/'/g,"\\'")}')"
                 class="price-table" style="width:95px"></td>
      <td><input type="number" name="colors[${idx}][stock][${s}]"
                 value="${d.stock||0}" min="0" placeholder="0"
                 class="price-table" style="width:75px"></td>
      <td class="eff-price-cell" data-size-key="${s}" style="font-weight:600;font-size:13px">—</td>
    </tr>`;
  });
  html += '</tbody></table>';
  container.innerHTML = html;
  // Compute initial effective prices after rendering
  tags.forEach(tag => recalcEffectiveShow(idx, tag.dataset.size));
}

// ─── Init ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  if ({{ $hasVariations ? 'true' : 'false' }}) {
    SHOW_EDIT_COLOR_ROWS.forEach(c => addColorRowShow(c));
  }

  // Auto-open section from URL ?section=
  const sp = new URLSearchParams(window.location.search).get('section');
  if (sp) { openSection(sp); }
});
</script>

@endsection
