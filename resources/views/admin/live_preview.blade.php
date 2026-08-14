<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Preview Editor — Ramo Store</title>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
:root {
  --accent:#e85d26; --accent-dim:rgba(232,93,38,.15);
  --muted:rgba(255,255,255,.45); --text:#fff;
  --bg:#0f0f1a; --bg-panel:#13131f; --bg-card:#1a1a2e;
  --border:rgba(255,255,255,.08);
  --green:#22c55e; --red:#ef4444;
}
*{box-sizing:border-box;margin:0;padding:0}
body{display:flex;height:100vh;overflow:hidden;background:var(--bg);font-family:system-ui,-apple-system,sans-serif;color:var(--text)}

/* ── LEFT PANEL ── */
#lp-left{width:360px;flex-shrink:0;display:flex;flex-direction:column;background:var(--bg-panel);border-right:1px solid var(--border);overflow:hidden}
#lp-head{padding:12px 14px;border-bottom:1px solid var(--border);flex-shrink:0}
#lp-title{font-size:13px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px;margin-bottom:8px}
#lp-btns{display:flex;gap:6px;margin-bottom:6px}
#lp-status{font-size:11px;color:var(--green);min-height:16px;padding:1px 0}
#lp-search-wrap{padding:6px 8px 0;flex-shrink:0}
#lp-search{width:100%;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;padding:6px 10px 6px 28px;font-size:12px;font-family:inherit;outline:none;transition:.12s}
#lp-search:focus{border-color:var(--accent)}
#lp-search::placeholder{color:rgba(255,255,255,.3)}
#lp-search-icon{position:absolute;left:20px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);font-size:12px;pointer-events:none}
#lp-search-wrap{position:relative}
#lp-filter-count{font-size:10px;color:var(--muted);padding:4px 8px 0;display:none}
#lp-body{flex:1;overflow-y:auto;padding:8px}
#lp-foot{padding:10px 14px;border-top:1px solid var(--border);flex-shrink:0;font-size:11px;color:var(--muted)}

/* ── RIGHT PANEL ── */
#lp-right{flex:1;display:flex;flex-direction:column;overflow:hidden}
#lp-bar{height:38px;background:var(--bg);display:flex;align-items:center;gap:8px;padding:0 12px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0}
#lp-bar .lp-dot{width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0}
#lp-url{font-size:11px;color:rgba(255,255,255,.3);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#lpIframe{flex:1;border:none;width:100%;height:100%;background:#f8f8f8}

/* ── SECTION CARDS ── */
#lpSectionList{display:flex;flex-direction:column;gap:6px}
.tl-card{background:var(--bg-card);border:1px solid var(--border);border-radius:8px;overflow:hidden;transition:.15s}
.tl-card:hover{border-color:rgba(255,255,255,.15)}
.tl-card-header{display:flex;align-items:center;gap:8px;padding:10px 12px;cursor:pointer;user-select:none}
.tl-drag-handle{color:var(--muted);font-size:16px;cursor:grab;flex-shrink:0}
.tl-drag-handle:active{cursor:grabbing}
.tl-section-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tl-section-desc{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:1px}
.tl-card-actions{display:flex;gap:5px;flex-shrink:0;margin-left:auto}
.tl-body{display:none;padding:12px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.15)}
.tl-body.open{display:block}
.sortable-ghost{opacity:.35}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:7px;border:1px solid var(--border);background:rgba(255,255,255,.07);color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:.12s;text-decoration:none;white-space:nowrap}
.btn:hover{background:rgba(255,255,255,.14)}
.btn-sm{padding:4px 9px;font-size:11px;border-radius:5px}
.btn-primary{background:var(--accent);border-color:var(--accent)}
.btn-primary:hover{background:#c94d1a}
.btn-danger{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:#ef4444}
.btn-danger:hover{background:rgba(239,68,68,.22)}
.btn-ghost{background:transparent;border-color:var(--border);color:var(--muted)}
.btn-ghost:hover{background:rgba(255,255,255,.06);color:#fff}

/* ── FORMS ── */
.form-group{margin-bottom:10px}
.form-group label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;padding:7px 10px;font-size:12px;font-family:inherit;outline:none;transition:.12s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--accent)}
.form-group select option{background:#1a1a2e;color:#fff}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
.form-grid .form-group{margin:0}
.items-list{display:flex;flex-direction:column;gap:5px;margin-top:4px}
.item-row{display:flex;align-items:center;gap:5px;padding:7px 8px;background:rgba(255,255,255,.03);border-radius:6px;border:1px solid rgba(255,255,255,.06);flex-wrap:wrap}
.item-row input,.item-row select{flex:1;min-width:50px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:5px;color:#fff;padding:5px 8px;font-size:12px;font-family:inherit;outline:none}
.add-item-btn{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.18);border-radius:6px;color:var(--muted);font-size:11px;cursor:pointer;margin-top:5px}
.add-item-btn:hover{background:rgba(255,255,255,.08);color:#fff}

/* ── WIDGET PICKER ── */
#typePicker{position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.72);display:none;align-items:center;justify-content:center}
#typePicker.open{display:flex}
#wp-panel{width:680px;max-width:96vw;max-height:88vh;background:var(--bg-panel);border-radius:14px;border:1px solid rgba(255,255,255,.1);display:flex;flex-direction:column;overflow:hidden}
#wp-head{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
#wp-body{display:flex;flex:1;overflow:hidden;min-height:400px}
#wp-left{width:190px;flex-shrink:0;border-right:1px solid var(--border);overflow-y:auto;padding:8px}
.wp-group-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:8px 6px 4px;margin-top:4px}
.wp-btn{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;cursor:pointer;color:#fff;border:1px solid transparent}
.wp-btn:hover,.wp-btn.wp-active{background:rgba(232,93,38,.12);border-color:rgba(232,93,38,.28)}
.wp-ico{font-size:16px;width:22px;text-align:center}
.wp-name{font-size:12px;font-weight:600;color:var(--muted)}
.wp-btn:hover .wp-name,.wp-btn.wp-active .wp-name{color:#fff}
#wp-right{flex:1;display:flex;flex-direction:column;overflow:hidden}
#wp-preview{flex:1;padding:16px;overflow-y:auto;background:rgba(0,0,0,.08)}
#wp-preview-bottom{padding:12px 16px;border-top:1px solid var(--border);display:none;gap:8px}
.wp-preview-title{font-size:15px;font-weight:800;color:#fff;margin-bottom:4px}
.wp-preview-desc{font-size:12px;color:var(--muted);line-height:1.6;margin-bottom:10px}
.wp-preview-tags{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px}
.wp-preview-tag{font-size:10px;font-weight:600;padding:2px 8px;border-radius:12px;background:rgba(255,255,255,.07);color:var(--muted)}
.wp-preview-mockup{background:#f8f8f8;border-radius:8px;padding:14px;min-height:80px}
.wp-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;color:var(--muted);text-align:center}

/* ── LANG SWITCH ── */
#langSwitch{background:rgba(255,255,255,.06);border:1px solid var(--border);color:#fff;padding:4px 8px;border-radius:6px;font-size:11px;font-family:inherit;outline:none;cursor:pointer}

/* ── VIEW BUTTON ── */
.btn-view{background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.35);color:#818cf8}
.btn-view:hover{background:rgba(99,102,241,.22);color:#a5b4fc}
/* ── SOLO MODE BAR ── */
#lp-solo-bar{display:none;align-items:center;gap:8px;padding:6px 12px;background:rgba(99,102,241,.18);border-bottom:1px solid rgba(99,102,241,.3);flex-shrink:0;font-size:11px;color:#a5b4fc}
#lp-solo-bar.active{display:flex}

/* ── RESPONSIVE DIMENSION TABS ── */
.dim-section{margin-top:12px;border-top:1px solid rgba(255,255,255,.07);padding-top:12px}
.dim-section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.dim-tab-bar{display:flex;gap:4px;margin-bottom:10px;background:rgba(0,0,0,.25);border-radius:8px;padding:3px}
.dim-tab{flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding:5px 8px;border-radius:6px;border:none;background:transparent;color:rgba(255,255,255,.4);font-size:11px;font-weight:700;cursor:pointer;transition:.12s;font-family:inherit}
.dim-tab:hover{color:rgba(255,255,255,.7)}
.dim-tab.active{background:rgba(255,255,255,.1);color:#fff}
.dim-tab.dim-tab-win.active{background:rgba(59,130,246,.25);color:#93c5fd;border:1px solid rgba(59,130,246,.3)}
.dim-tab.dim-tab-and.active{background:rgba(34,197,94,.2);color:#86efac;border:1px solid rgba(34,197,94,.25)}
.dim-panel{display:none}
.dim-panel.active{display:block}
</style>
</head>
<body>

{{-- ────────────────────────────── LEFT PANEL ────────────────────────────── --}}
<div id="lp-left">
  <div id="lp-head">
    <div id="lp-title">
      <span style="font-size:18px">✏️</span>
      <span>Live Section Editor</span>
      @if($langs->count() > 1)
      <select id="langSwitch" style="margin-left:auto" onchange="switchLang(this.value)">
        @foreach($langs as $l)
          <option value="{{ $l }}" {{ $lang === $l ? 'selected' : '' }}>{{ strtoupper($l) }}</option>
        @endforeach
      </select>
      @endif
    </div>
    <div id="lp-btns">
      <button class="btn btn-sm" style="flex:1" onclick="openPicker()">＋ Add Widget</button>
      <button class="btn btn-primary btn-sm" style="flex:1" id="lpSaveBtn" onclick="lpSave()">💾 Save &amp; Reload</button>
    </div>
    <div id="lp-status"></div>
  </div>

  <div id="lp-search-wrap">
    <span id="lp-search-icon">🔍</span>
    <input id="lp-search" type="text" placeholder="Filter widgets by name or type…" oninput="filterWidgets(this.value)">
  </div>
  <div id="lp-filter-count"></div>

  <div id="lp-body">
    <div id="lpSectionList"></div>
  </div>

  <div id="lp-foot">
    <a href="{{ route('admin.timeline') }}" style="color:var(--accent);text-decoration:none;font-weight:700">← Full Timeline Editor</a>
    <span style="margin:0 8px;opacity:.4">·</span>
    <span>Click any section in the preview to open its editor</span>
  </div>
</div>

{{-- ────────────────────────────── RIGHT PANEL ────────────────────────────── --}}
<div id="lp-right">
  <div id="lp-bar">
    <span class="lp-dot"></span>
    <span id="lp-url" class="lp-url">{{ url('/') }}?tl_preview=1</span>
    <button class="btn btn-sm btn-ghost" onclick="reloadIframe()" title="Reload preview">↺ Reload</button>
    <a id="lp-open-btn" href="{{ url('/') }}?tl_preview=1" target="_blank" class="btn btn-sm btn-ghost" title="Open in new tab">⬡ Open</a>
  </div>
  <div id="lp-solo-bar">
    <span style="font-size:13px">👁</span>
    <span id="lp-solo-label" style="flex:1;font-weight:600">Viewing widget</span>
    <button class="btn btn-sm" onclick="viewFull()" style="background:rgba(99,102,241,.25);border-color:rgba(99,102,241,.5);color:#c7d2fe;font-size:11px">← Full Page View</button>
  </div>
  <iframe id="lpIframe" src="{{ url('/') }}?tl_preview=1" sandbox="allow-same-origin allow-scripts allow-forms allow-popups"></iframe>
</div>

{{-- ────────────────────────────── WIDGET PICKER ────────────────────────────── --}}
<div id="typePicker">
  <div id="wp-panel">
    <div id="wp-head">
      <span style="font-size:14px;font-weight:800">Add a Widget</span>
      <button onclick="closePicker()" style="background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer;line-height:1">×</button>
    </div>
    <div id="wp-body">
      <div id="wp-left">
        <div class="wp-group-label">Content</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('bannerImage')" onclick="lpAddSection('bannerImage')"><span class="wp-ico">🖼️</span><span class="wp-name">Banner / Slider</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('flexBannerGrid')" onclick="lpAddSection('flexBannerGrid')"><span class="wp-ico">🧩</span><span class="wp-name">Flexible Banner Grid</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('category')" onclick="lpAddSection('category')"><span class="wp-ico">📂</span><span class="wp-name">Categories Strip</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('categoryCards')" onclick="lpAddSection('categoryCards')"><span class="wp-ico">🗂️</span><span class="wp-name">Category Cards</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('twoColumn')" onclick="lpAddSection('twoColumn')"><span class="wp-ico">🛍️</span><span class="wp-name">Products Grid</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('saleImages')" onclick="lpAddSection('saleImages')"><span class="wp-ico">🏷️</span><span class="wp-name">Products Scroll</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('seupermarketstars')" onclick="lpAddSection('seupermarketstars')"><span class="wp-ico">⭐</span><span class="wp-name">Featured Items</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('topVendors')" onclick="lpAddSection('topVendors')"><span class="wp-ico">🏪</span><span class="wp-name">Top Vendors</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('brands')" onclick="lpAddSection('brands')"><span class="wp-ico">🏷️</span><span class="wp-name">Brands Strip</span></div>
        <div class="wp-group-label">Widgets</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('coupons')" onclick="lpAddSection('coupons')"><span class="wp-ico">🎟️</span><span class="wp-name">Coupons Strip</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('statsBar')" onclick="lpAddSection('statsBar')"><span class="wp-ico">📊</span><span class="wp-name">Stats Bar</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('promoBlock')" onclick="lpAddSection('promoBlock')"><span class="wp-ico">📣</span><span class="wp-name">Promo Block</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('testimonials')" onclick="lpAddSection('testimonials')"><span class="wp-ico">💬</span><span class="wp-name">Testimonials</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('reviewsCarousel')" onclick="lpAddSection('reviewsCarousel')"><span class="wp-ico">🌟</span><span class="wp-name">Reviews Carousel</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('newsletter')" onclick="lpAddSection('newsletter')"><span class="wp-ico">📧</span><span class="wp-name">Newsletter</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('bundle')" onclick="lpAddSection('bundle')"><span class="wp-ico">🎁</span><span class="wp-name">Bundle Deal</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('loyalty')" onclick="lpAddSection('loyalty')"><span class="wp-ico">⭐</span><span class="wp-name">Loyalty Points</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('activity')" onclick="lpAddSection('activity')"><span class="wp-ico">🔴</span><span class="wp-name">Live Activity</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('referral')" onclick="lpAddSection('referral')"><span class="wp-ico">🤝</span><span class="wp-name">Referral Widget</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('recent')" onclick="lpAddSection('recent')"><span class="wp-ico">🕐</span><span class="wp-name">Recently Viewed</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('recommended')" onclick="lpAddSection('recommended')"><span class="wp-ico">💡</span><span class="wp-name">Recommended</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('complete')" onclick="lpAddSection('complete')"><span class="wp-ico">👗</span><span class="wp-name">Complete the Look</span></div>
        <div class="wp-group-label">Products</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('trending')" onclick="lpAddSection('trending')"><span class="wp-ico">🔥</span><span class="wp-name">Trending Now</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('arrivals')" onclick="lpAddSection('arrivals')"><span class="wp-ico">✨</span><span class="wp-name">New Arrivals</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('brandLogos')" onclick="lpAddSection('brandLogos')"><span class="wp-ico">🏷️</span><span class="wp-name">Brand Logos Row</span></div>
        <div class="wp-group-label">Full-width</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('announcement')" onclick="lpAddSection('announcement')"><span class="wp-ico">📢</span><span class="wp-name">Announcement Bar</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('flash')" onclick="lpAddSection('flash')"><span class="wp-ico">⚡</span><span class="wp-name">Flash Sale Timer</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('seasonal')" onclick="lpAddSection('seasonal')"><span class="wp-ico">🎄</span><span class="wp-name">Seasonal Banner</span></div>
        <div class="wp-group-label">Featured</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('productCustomizer')" onclick="lpAddSection('productCustomizer')"><span class="wp-ico">🎯</span><span class="wp-name">Product Customizer</span></div>
        <div class="wp-group-label">Layout</div>
        <div class="wp-btn" onmouseenter="lpShowPreview('spacer')" onclick="lpAddSection('spacer')"><span class="wp-ico">↕️</span><span class="wp-name">Spacer</span></div>
        <div class="wp-btn" onmouseenter="lpShowPreview('divider')" onclick="lpAddSection('divider')"><span class="wp-ico">➖</span><span class="wp-name">Divider</span></div>
      </div>
      <div id="wp-right">
        <div id="wp-preview">
          <div class="wp-empty"><div style="font-size:36px;opacity:.3">👆</div><div style="font-size:12px">Hover a widget to preview it, then click to add</div></div>
        </div>
        <div id="wp-preview-bottom">
          <button id="wp-add-btn" class="btn btn-primary" style="flex:1" onclick="">Add Widget</button>
          <button class="btn btn-ghost" onclick="closePicker()">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── DATA ──────────────────────────────────────────────────────────
const CATEGORIES = @json($categories);
const LANG       = '{{ $lang }}';
const SAVE_URL   = '{{ route('admin.timeline.save') }}';
const CSRF       = '{{ csrf_token() }}';
let sections     = @json($sections ?? []);
let _lpCurrentPreview = null;

// ── TYPE META ──────────────────────────────────────────────────────
const TYPE_META = {
  logo:             { icon:'🏪', label:'Logo / Header Bar', color:'#3b82f6' },
  category:         { icon:'📂', label:'Categories Strip',   color:'#8b5cf6' },
  categoryCards:    { icon:'🗂️', label:'Category Cards',     color:'#8b5cf6' },
  bannerImage:      { icon:'🖼️', label:'Banner / Slider',    color:'#e85d26' },
  flexBannerGrid:   { icon:'🧩', label:'Flexible Banner Grid', color:'#7c3aed' },
  twoColumn:        { icon:'🛍️', label:'Products Grid',      color:'#22c55e' },
  saleImages:       { icon:'🏷️', label:'Products Scroll',    color:'#f59e0b' },
  seupermarketstars:{ icon:'⭐', label:'Featured Items',      color:'#ec4899' },
  topVendors:       { icon:'🏪', label:'Top Vendors',         color:'#f97316' },
  brands:           { icon:'🏷️', label:'Brands',             color:'#06b6d4' },
  coupons:          { icon:'🎟️', label:'Coupons Strip',       color:'#f59e0b' },
  trending:         { icon:'🔥', label:'Trending Now',        color:'#ef4444' },
  arrivals:         { icon:'✨', label:'New Arrivals',        color:'#8b5cf6' },
  brandLogos:       { icon:'🏷️', label:'Brand Logos Row',    color:'#0ea5e9' },
  reviewsCarousel:  { icon:'🌟', label:'Reviews Carousel',   color:'#f59e0b' },
  activity:         { icon:'🔴', label:'Live Activity',       color:'#ef4444' },
  recent:           { icon:'🕐', label:'Recently Viewed',    color:'#6366f1' },
  bundle:           { icon:'🎁', label:'Bundle Deal',         color:'#22c55e' },
  loyalty:          { icon:'⭐', label:'Loyalty Points',     color:'#f59e0b' },
  seasonal:         { icon:'🎄', label:'Seasonal Banner',    color:'#22c55e' },
  referral:         { icon:'🤝', label:'Referral Widget',    color:'#0ea5e9' },
  complete:         { icon:'👗', label:'Complete the Look',  color:'#ec4899' },
  recommended:      { icon:'💡', label:'Recommended',        color:'#8b5cf6' },
  announcement:     { icon:'📢', label:'Announcement Bar',   color:'#334155' },
  flash:            { icon:'⚡', label:'Flash Sale Timer',   color:'#ef4444' },
  statsBar:         { icon:'📊', label:'Stats Bar',          color:'#6366f1' },
  promoBlock:       { icon:'📣', label:'Promo Block',        color:'#e85d26' },
  testimonials:     { icon:'💬', label:'Testimonials',       color:'#22c55e' },
  newsletter:       { icon:'📧', label:'Newsletter Signup',  color:'#06b6d4' },
  spacer:           { icon:'↕️', label:'Spacer',            color:'#6b7280' },
  divider:          { icon:'➖', label:'Divider',           color:'#6b7280' },
  productCustomizer:{ icon:'🎯', label:'Product Customizer', color:'#e85d26' },
};

// ── HELPERS ────────────────────────────────────────────────────────
function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escAttr(s){ return String(s||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function catOptions(selected){
  return CATEGORIES.map(c=>`<option value="${c.id}" ${c.id==selected?'selected':''}>${escHtml(c.name)}</option>`).join('');
}

// ── BUILD CARD ────────────────────────────────────────────────────
function buildCard(sec, idx) {
  const type = sec.layout || 'unknown';
  const meta = TYPE_META[type] || { icon:'❓', label: type, color:'#6b7280' };
  const name = sec.name || sec.headerText || sec.title || meta.label;
  const hidden = sec.hidden === true;
  return `
  <div class="tl-card" data-idx="${idx}" style="${hidden?'opacity:.45;border-style:dashed':''}">
    <div class="tl-card-header" onclick="toggleBody(${idx})">
      <span class="tl-drag-handle">⠿</span>
      <span style="font-size:18px">${hidden?'🚫':meta.icon}</span>
      <div style="flex:1;min-width:0">
        <div class="tl-section-name" style="${hidden?'text-decoration:line-through;color:var(--muted)':''}">${escHtml(name)}</div>
        <div class="tl-section-desc" style="color:${hidden?'var(--muted)':meta.color}">${hidden?'Hidden':meta.label}</div>
      </div>
      <div class="tl-card-actions" onclick="event.stopPropagation()">
        <button class="btn btn-view btn-sm" onclick="viewWidget(${idx})" title="Preview only this widget">👁 View</button>
        <button class="btn btn-sm" style="background:${hidden?'rgba(34,197,94,.15)':'rgba(255,255,255,.06)'};border:1px solid ${hidden?'rgba(34,197,94,.3)':'rgba(255,255,255,.1)'};color:${hidden?'#22c55e':'var(--muted)'}" onclick="toggleHidden(${idx})" title="${hidden?'Show':'Hide'}">${hidden?'Show':'Hide'}</button>
        <button class="btn btn-danger btn-sm" onclick="removeSection(${idx})">✕</button>
      </div>
      <span style="color:var(--muted);font-size:12px;margin-left:4px">▼</span>
    </div>
    <div class="tl-body" id="body-${idx}">
      ${buildEditor(sec, idx)}
    </div>
  </div>`;
}

// ── BUILD EDITOR ───────────────────────────────────────────────────
function buildEditor(sec, idx) {
  const type = sec.layout;
  let html = '';

  if (type === 'logo') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Show Logo</label><select onchange="updateField(${idx},'showLogo',this.value==='true')"><option value="true" ${sec.showLogo!==false?'selected':''}>Yes</option><option value="false" ${sec.showLogo===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Show Search</label><select onchange="updateField(${idx},'showSearch',this.value==='true')"><option value="true" ${sec.showSearch!==false?'selected':''}>Yes</option><option value="false" ${sec.showSearch===false?'selected':''}>No</option></select></div>
    </div>`;
  }
  else if (type === 'category') {
    html = `<div style="font-size:12px;color:var(--muted);margin-bottom:8px">Category items shown as icon strip on homepage.</div>`;
    html += `<div class="items-list" id="catItems-${idx}">` + (sec.items||[]).map((item,ii)=>buildCatItem(idx,ii,item)).join('') + `</div>`;
    html += `<button class="add-item-btn" onclick="addCatItem(${idx})">+ Add Category Item</button>`;
  }
  else if (type === 'categoryCards') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Shop by Category')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Columns</label><select onchange="updateField(${idx},'columns',parseInt(this.value))"><option value="2" ${(sec.columns||3)==2?'selected':''}>2 columns</option><option value="3" ${(sec.columns||3)==3?'selected':''}>3 columns</option><option value="4" ${(sec.columns||3)==4?'selected':''}>4 columns</option></select></div>
      <div class="form-group"><label>Card Height (px)</label><input type="number" value="${sec.cardHeight||220}" min="100" max="500" step="10" onchange="updateField(${idx},'cardHeight',parseInt(this.value)||220)"></div>
      <div class="form-group"><label>Corner Radius (px)</label><input type="number" value="${sec.cardBorderRadius!=null?sec.cardBorderRadius:14}" min="0" max="40" step="1" onchange="updateField(${idx},'cardBorderRadius',parseInt(this.value))"></div>
      <div class="form-group"><label>Max Categories</label><input type="number" value="${sec.maxItemsToShow||12}" min="2" max="24" step="1" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value)||12)"></div>
      <div class="form-group"><label>Show Item Count</label><select onchange="updateField(${idx},'showCount',this.value==='true')"><option value="true" ${sec.showCount!==false?'selected':''}>Yes</option><option value="false" ${sec.showCount===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Top-level Only</label><select onchange="updateField(${idx},'parentOnly',this.value==='true')"><option value="true" ${sec.parentOnly!==false?'selected':''}>Yes</option><option value="false" ${sec.parentOnly===false?'selected':''}>No</option></select></div>
    </div>`;
  }
  else if (type === 'bannerImage') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Style</label><select onchange="updateField(${idx},'design',this.value)"><option value="default" ${(sec.design||'default')==='default'?'selected':''}>Slider</option><option value="static" ${sec.design==='static'?'selected':''}>Static</option></select></div>
      <div class="form-group"><label>Auto Play</label><select onchange="updateField(${idx},'autoPlay',this.value==='true')"><option value="true" ${sec.autoPlay!==false?'selected':''}>Yes</option><option value="false" ${sec.autoPlay===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Border Radius</label><input type="number" value="${sec.radius||2}" min="0" max="30" onchange="updateField(${idx},'radius',parseFloat(this.value)||0)"></div>
      <div class="form-group"><label>Image Height (px)</label><input type="number" value="${sec.bannerHeight||420}" min="80" max="900" step="10" onchange="updateField(${idx},'bannerHeight',parseInt(this.value)||420)" placeholder="420"></div>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Banner images:</div>
    <div class="items-list" id="bannerItems-${idx}">` + (sec.items||[]).map((item,ii)=>buildBannerItem(idx,ii,item)).join('') + `</div>
    <button class="add-item-btn" onclick="addBannerItem(${idx})">+ Add Banner Image</button>`;
  }
  else if (type === 'flexBannerGrid') {
    const items = Array.isArray(sec.items) ? sec.items : [];
    html = `<div class="form-grid">
      <div class="form-group"><label>Text Above Banners <span style="font-weight:400;color:var(--muted)">(optional)</span></label><input type="text" value="${escAttr(sec.headerText||'')}" maxlength="120" onchange="updateField(${idx},'headerText',this.value)" placeholder="e.g. عروض النهاردة و بس"></div>
      <div class="form-group"><label>Gap Between Banners (px)</label><input type="number" value="${sec.gap??12}" min="0" max="40" onchange="updateField(${idx},'gap',Math.max(0,Math.min(40,parseInt(this.value)||0)))"></div>
      <div class="form-group"><label>Corner Radius (px)</label><input type="number" value="${sec.radius??10}" min="0" max="40" onchange="updateField(${idx},'radius',Math.max(0,Math.min(40,parseInt(this.value)||0)))"></div>
      <div class="form-group"><label>Phone Layout</label><select onchange="updateField(${idx},'mobileColumns',parseInt(this.value))"><option value="1" ${Number(sec.mobileColumns)===1?'selected':''}>Stack one per row</option><option value="2" ${Number(sec.mobileColumns)!==1?'selected':''}>Compact two columns</option></select></div>
    </div>
    <div style="font-size:12px;color:var(--muted);margin:2px 0 8px">Add as many banners as you need. Choose <strong>Full</strong>, <strong>Half</strong>, or <strong>Quarter</strong> width for each card. Use the arrows to arrange banners next to or below one another.</div>
    <div class="items-list" id="flexBannerItems-${idx}">${items.map((item,ii)=>buildFlexBannerItem(idx,ii,item)).join('')}</div>
    <button class="add-item-btn" onclick="addFlexBannerItem(${idx})">+ Add Banner${items.length ? ` (${items.length} added)` : ''}</button>`;
  }
  else if (type === 'twoColumn' || type === 'saleImages' || type === 'seupermarketstars') {
    const defWidth  = type === 'saleImages' ? 140 : (type === 'seupermarketstars' ? 200 : 230);
    const defHeight = type === 'saleImages' ? 196 : (type === 'seupermarketstars' ? 200 : 230);
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||sec.name||'')}" onchange="updateField(${idx},'headerText',this.value)" placeholder="e.g. On Sale Today"></div>
      <div class="form-group"><label>Category</label><select onchange="updateField(${idx},'category',parseInt(this.value))"><option value="">All Products</option>${catOptions(sec.category)}</select></div>
      <div class="form-group"><label>Max Items</label><input type="number" value="${sec.maxItemsToShow||8}" min="1" max="20" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Card Width (px)</label><input type="number" value="${sec.productWidth||defWidth}" min="80" max="500" step="10" onchange="updateField(${idx},'productWidth',parseInt(this.value)||defWidth)"></div>
      <div class="form-group"><label>Image Height (px)</label><input type="number" value="${sec.imageHeight||defHeight}" min="60" max="800" step="1" onchange="updateField(${idx},'imageHeight',parseInt(this.value)||defHeight)"></div>
      <div class="form-group"><label>Card Height (px)</label><input type="number" value="${sec.cardHeight||0}" min="0" max="1000" step="1" onchange="updateField(${idx},'cardHeight',parseInt(this.value)||0)" placeholder="Auto"></div>
      <div class="form-group"><label>Image Width (px) <span style="font-size:10px;opacity:.62;font-weight:400">0 = full card width</span></label><div style="display:flex;gap:6px;align-items:center"><input type="number" value="${sec.imageWidth||0}" min="0" max="500" step="1" onchange="updateField(${idx},'imageWidth',parseInt(this.value)||0)" placeholder="Full width"><button type="button" class="btn btn-sm" style="white-space:nowrap" onclick="updateField(${idx},'imageWidth',0);this.previousElementSibling.value=0">Full width</button></div></div>
      <div class="form-group"><label>Element Spacing (px)</label><input type="number" value="${sec.elementSpacing!=null?sec.elementSpacing:0}" min="0" max="40" step="1" onchange="updateField(${idx},'elementSpacing',Math.max(0,Math.min(40,parseInt(this.value)||0)))"></div>
      <div class="form-group"><label>Corner Radius (px)</label><input type="number" value="${sec.cardBorderRadius!=null?sec.cardBorderRadius:10}" min="0" max="40" step="1" onchange="updateField(${idx},'cardBorderRadius',parseInt(this.value))"></div>
    </div>
    <div style="font-size:12px;font-weight:700;color:var(--muted);margin:12px 0 8px;text-transform:uppercase;letter-spacing:.5px;border-top:1px solid rgba(255,255,255,.07);padding-top:12px">Card Elements</div>
    <div style="display:flex;flex-wrap:wrap;gap:10px 18px">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showBadge!==false?'checked':''} onchange="updateField(${idx},'showBadge',this.checked)" style="width:14px;height:14px"> Sale Badge</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showWishlist!==false?'checked':''} onchange="updateField(${idx},'showWishlist',this.checked)" style="width:14px;height:14px"> Wishlist ♡</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showSwatches!==false?'checked':''} onchange="updateField(${idx},'showSwatches',this.checked)" style="width:14px;height:14px"> Color Swatches</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showSizes!==false?'checked':''} onchange="updateField(${idx},'showSizes',this.checked)" style="width:14px;height:14px"> Size Chips</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showOldPrice!==false?'checked':''} onchange="updateField(${idx},'showOldPrice',this.checked)" style="width:14px;height:14px"> Original Price</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showAddToCart!==false?'checked':''} onchange="updateField(${idx},'showAddToCart',this.checked)" style="width:14px;height:14px"> Add to Cart</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showDetails!==false?'checked':''} onchange="updateField(${idx},'showDetails',this.checked)" style="width:14px;height:14px"> See Details Button</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showCoupon!==false?'checked':''} onchange="updateField(${idx},'showCoupon',this.checked)" style="width:14px;height:14px"> Coupon Bar</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showRating?'checked':''} onchange="updateField(${idx},'showRating',this.checked)" style="width:14px;height:14px"> Star Rating</label>
    </div>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.07)">
      <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer;user-select:none">
        <input type="checkbox" ${sec.uniformHeight?'checked':''} onchange="updateField(${idx},'uniformHeight',this.checked)" style="width:14px;height:14px">
        <span><strong>Uniform card height</strong> — align all "Add to Cart" buttons in the same row</span>
      </label>
    </div>`;
  }
  else if (type === 'topVendors') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Top Sellers')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Max Vendors</label><input type="number" value="${sec.maxItemsToShow||6}" min="1" max="20" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Sort By</label><select onchange="updateField(${idx},'sortBy',this.value)"><option value="products" ${(sec.sortBy||'products')==='products'?'selected':''}>Most Products</option><option value="rating" ${sec.sortBy==='rating'?'selected':''}>Highest Rated</option><option value="newest" ${sec.sortBy==='newest'?'selected':''}>Newest</option></select></div>
    </div>`;
  }
  else if (type === 'brands') {
    html = `<div style="font-size:12px;color:var(--muted)">Displays all brands automatically. No extra configuration needed.</div>`;
  }
  else if (type === 'spacer') {
    html = `<div class="form-grid"><div class="form-group"><label>Height (px)</label><input type="number" value="${sec.height||24}" min="4" max="200" onchange="updateField(${idx},'height',parseInt(this.value))"></div></div>`;
  }
  else if (type === 'divider') {
    html = `<div style="font-size:12px;color:var(--muted)">Shows a horizontal divider line. No configuration needed.</div>`;
  }
  else if (type === 'coupons') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||"This Week's Deals")}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Sub-label</label><input type="text" value="${escAttr(sec.subLabel||'Use code at checkout')}" onchange="updateField(${idx},'subLabel',this.value)"></div>
      <div class="form-group"><label>Max Coupons</label><input type="number" value="${sec.maxItemsToShow||6}" min="1" max="20" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Sort By</label><select onchange="updateField(${idx},'sortBy',this.value)"><option value="amount" ${(sec.sortBy||'amount')==='amount'?'selected':''}>Discount Amount</option><option value="newest" ${sec.sortBy==='newest'?'selected':''}>Newest</option></select></div>
    </div>
    <label style="display:flex;align-items:center;gap:7px;font-size:12px;cursor:pointer;margin-bottom:6px"><input type="checkbox" ${sec.hideWhenEmpty!==false?'checked':''} onchange="updateField(${idx},'hideWhenEmpty',this.checked)" style="width:14px;height:14px"> Hide when no coupons available</label>`;
  }
  else if (type === 'statsBar') {
    const statKeys=['products','vendors','categories','brands','orders','reviews'];
    const items=sec.items||[{key:'products',label:'Products'},{key:'vendors',label:'Vendors'},{key:'categories',label:'Categories'},{key:'brands',label:'Brands'}];
    html = `<div class="form-grid">
      <div class="form-group"><label>Background</label><div style="display:flex;gap:5px"><input type="color" value="${sec.bgColor||'#111111'}" style="width:38px;height:34px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'bgColor',this.value)"><input type="text" value="${escAttr(sec.bgColor||'#111111')}" onchange="updateField(${idx},'bgColor',this.value)"></div></div>
      <div class="form-group"><label>Text Color</label><div style="display:flex;gap:5px"><input type="color" value="${sec.textColor||'#ffffff'}" style="width:38px;height:34px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'textColor',this.value)"><input type="text" value="${escAttr(sec.textColor||'#ffffff')}" onchange="updateField(${idx},'textColor',this.value)"></div></div>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Stats to display:</div>
    <div class="items-list" id="statsItems-${idx}">` +
      items.map((item,ii)=>`<div class="item-row" id="statsItem-${idx}-${ii}">
        <select style="width:130px" onchange="updateStatsItem(${idx},${ii},'key',this.value)">${statKeys.map(k=>`<option value="${k}" ${item.key===k?'selected':''}>${k.charAt(0).toUpperCase()+k.slice(1)}</option>`).join('')}</select>
        <input type="text" value="${escAttr(item.label||'')}" placeholder="Label" style="flex:1" onchange="updateStatsItem(${idx},${ii},'label',this.value)">
        <button class="btn btn-danger btn-sm" onclick="removeStatsItem(${idx},${ii})">×</button>
      </div>`).join('') +
    `</div><button class="add-item-btn" onclick="addStatsItem(${idx})">+ Add Stat</button>`;
  }
  else if (type === 'promoBlock') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Headline</label><input type="text" value="${escAttr(sec.headline||'Special Offer')}" onchange="updateField(${idx},'headline',this.value)"></div>
      <div class="form-group"><label>Subtext</label><input type="text" value="${escAttr(sec.subtext||'')}" onchange="updateField(${idx},'subtext',this.value)"></div>
      <div class="form-group"><label>Button Text</label><input type="text" value="${escAttr(sec.btnText||'Shop Now')}" onchange="updateField(${idx},'btnText',this.value)"></div>
      <div class="form-group"><label>Button Link</label><input type="text" value="${escAttr(sec.btnLink||'/shop')}" onchange="updateField(${idx},'btnLink',this.value)"></div>
      <div class="form-group"><label>Side Image URL</label><input type="text" value="${escAttr(sec.image||'')}" onchange="updateField(${idx},'image',this.value)" placeholder="https://…"></div>
      <div class="form-group"><label>Alignment</label><select onchange="updateField(${idx},'align',this.value)"><option value="center" ${(sec.align||'center')==='center'?'selected':''}>Center</option><option value="left" ${sec.align==='left'?'selected':''}>Left</option><option value="right" ${sec.align==='right'?'selected':''}>Right</option></select></div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label>Background</label><div style="display:flex;gap:5px"><input type="color" value="${sec.bgColor||'#111111'}" style="width:38px;height:34px;border:none;background:none" onchange="updateField(${idx},'bgColor',this.value)"><input type="text" value="${escAttr(sec.bgColor||'#111111')}" onchange="updateField(${idx},'bgColor',this.value)"></div></div>
      <div class="form-group"><label>Text Color</label><div style="display:flex;gap:5px"><input type="color" value="${sec.textColor||'#ffffff'}" style="width:38px;height:34px;border:none;background:none" onchange="updateField(${idx},'textColor',this.value)"><input type="text" value="${escAttr(sec.textColor||'#ffffff')}" onchange="updateField(${idx},'textColor',this.value)"></div></div>
      <div class="form-group"><label>Button Color</label><div style="display:flex;gap:5px"><input type="color" value="${sec.btnColor||'#e85d26'}" style="width:38px;height:34px;border:none;background:none" onchange="updateField(${idx},'btnColor',this.value)"><input type="text" value="${escAttr(sec.btnColor||'#e85d26')}" onchange="updateField(${idx},'btnColor',this.value)"></div></div>
    </div>`;
  }
  else if (type === 'testimonials') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'What Our Customers Say')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Max Reviews</label><input type="number" value="${sec.maxItemsToShow||4}" min="1" max="12" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Star Rating</label><select onchange="updateField(${idx},'minRating',parseInt(this.value))"><option value="3" ${(sec.minRating||4)===3?'selected':''}>3+ Stars</option><option value="4" ${(sec.minRating||4)>=4?'selected':''}>4+ Stars</option><option value="5" ${sec.minRating===5?'selected':''}>5 Stars Only</option></select></div>
    </div>`;
  }
  else if (type === 'newsletter') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Headline</label><input type="text" value="${escAttr(sec.headline||'Stay in the Loop')}" onchange="updateField(${idx},'headline',this.value)"></div>
      <div class="form-group"><label>Subtext</label><input type="text" value="${escAttr(sec.subtext||'')}" onchange="updateField(${idx},'subtext',this.value)"></div>
      <div class="form-group"><label>Button Text</label><input type="text" value="${escAttr(sec.btnText||'Subscribe')}" onchange="updateField(${idx},'btnText',this.value)"></div>
      <div class="form-group"><label>Background</label><div style="display:flex;gap:5px"><input type="color" value="${sec.bgColor||'#f0ede8'}" style="width:38px;height:34px;border:none;background:none" onchange="updateField(${idx},'bgColor',this.value)"><input type="text" value="${escAttr(sec.bgColor||'#f0ede8')}" onchange="updateField(${idx},'bgColor',this.value)"></div></div>
    </div>`;
  }
  else if (type === 'flash') {
    const endTimeVal = sec.endTime ? new Date(sec.endTime).toISOString().slice(0,16) : '';
    const endTimeStatus = sec.endTime
      ? (sec.endTime > Date.now() ? `<span style="color:#22c55e">⏱ Active</span>` : `<span style="color:#ef4444">⏰ Expired</span>`)
      : `<span style="color:var(--muted)">No end time set</span>`;
    html = `<div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Flash Sale')}" onchange="updateField(${idx},'title',this.value)"></div>
      <div class="form-group"><label>Discount (%)</label><input type="number" value="${sec.discount||20}" min="1" max="99" onchange="updateField(${idx},'discount',parseInt(this.value))"></div>
      <div class="form-group"><label>Duration (hours)</label><input type="number" value="${sec.duration||4}" min="1" max="720" onchange="updateField(${idx},'duration',parseInt(this.value))" id="flash-dur-${idx}"></div>
      <div class="form-group"><label>Min Order (EGP)</label><input type="number" value="${sec.minOrder||0}" min="0" onchange="updateField(${idx},'minOrder',parseInt(this.value))"></div>
    </div>
    <div class="form-group" style="margin-top:6px">
      <label>End Date & Time ${endTimeStatus}</label>
      <div style="display:flex;gap:7px;margin-top:4px;flex-wrap:wrap">
        <input type="datetime-local" value="${endTimeVal}" style="flex:1;min-width:160px" onchange="updateField(${idx},'endTime',this.value?new Date(this.value).getTime():0)" id="flash-endtime-${idx}">
        <button class="btn btn-sm" style="background:var(--accent-dim);border:1px solid rgba(232,93,38,.3);color:var(--accent)" onclick="setFlashEndFromNow(${idx})">⚡ Start now</button>
      </div>
    </div>
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showCountdownSeconds!==false?'checked':''} onchange="updateField(${idx},'showCountdownSeconds',this.checked)" style="width:14px;height:14px"> Show Seconds</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.autoDismissWhenExpired?'checked':''} onchange="updateField(${idx},'autoDismissWhenExpired',this.checked)" style="width:14px;height:14px"> Auto-Dismiss</label>
    </div>
    <div style="margin-top:12px;border-top:1px solid rgba(255,255,255,.08);padding-top:12px">
      <label style="font-size:12px;font-weight:700;display:block;margin-bottom:8px">⚡ Product Targeting</label>
      <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:10px">
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="radio" name="flash-apply-${idx}" value="all" ${(sec.applyTo||'all')==='all'?'checked':''} onchange="updateField(${idx},'applyTo','all');renderFlashTargeting(${idx})"> All Products</label>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="radio" name="flash-apply-${idx}" value="categories" ${sec.applyTo==='categories'?'checked':''} onchange="updateField(${idx},'applyTo','categories');renderFlashTargeting(${idx})"> By Category</label>
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="radio" name="flash-apply-${idx}" value="products" ${sec.applyTo==='products'?'checked':''} onchange="updateField(${idx},'applyTo','products');renderFlashTargeting(${idx})"> Specific Products</label>
      </div>
      <div id="flash-targeting-${idx}"></div>
    </div>`;
    setTimeout(() => renderFlashTargeting(idx), 0);
  }
  else if (type === 'bundle') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Bundle Deal')}" onchange="updateField(${idx},'title',this.value)"></div>
      <div class="form-group"><label>Category Label</label><input type="text" value="${escAttr(sec.category||'')}" onchange="updateField(${idx},'category',this.value)" placeholder="e.g. Phones"></div>
      <div class="form-group"><label>Min Qty to Buy</label><input type="number" value="${sec.minQty||2}" min="1" onchange="updateField(${idx},'minQty',parseInt(this.value))"></div>
      <div class="form-group"><label>Free Items</label><input type="number" value="${sec.freeItems||1}" min="1" onchange="updateField(${idx},'freeItems',parseInt(this.value))"></div>
    </div>`;
  }
  else if (type === 'loyalty') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Points per EGP</label><input type="number" value="${sec.rate||10}" min="1" onchange="updateField(${idx},'rate',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Points to Redeem</label><input type="number" value="${sec.minRedeem||100}" min="1" onchange="updateField(${idx},'minRedeem',parseInt(this.value))"></div>
      <div class="form-group"><label>Conversion Rate</label><input type="text" value="${escAttr(sec.convRate||'100 pts = 5 EGP')}" onchange="updateField(${idx},'convRate',this.value)"></div>
    </div>`;
  }
  else if (type === 'trending') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Trending Now')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||10}" min="1" max="20" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Algorithm</label><select onchange="updateField(${idx},'algo',this.value)"><option value="sold7d" ${(sec.algo||'sold7d')==='sold7d'?'selected':''}>Most Sold</option><option value="views7d" ${sec.algo==='views7d'?'selected':''}>Most Viewed</option><option value="rated" ${sec.algo==='rated'?'selected':''}>Highest Rated</option></select></div>
    </div>`;
  }
  else if (type === 'arrivals') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'New Arrivals')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||8}" min="1" max="20" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Speed</label><select onchange="updateField(${idx},'speed',this.value)"><option value="slow" ${sec.speed==='slow'?'selected':''}>Slow</option><option value="normal" ${(sec.speed||'normal')==='normal'?'selected':''}>Normal</option><option value="fast" ${sec.speed==='fast'?'selected':''}>Fast</option></select></div>
      <div class="form-group"><label>Chip Tag</label><input type="text" value="${escAttr(sec.tag||'Just Arrived')}" onchange="updateField(${idx},'tag',this.value)"></div>
    </div>`;
  }
  else if (type === 'brandLogos') {
    html = `<div class="form-group" style="margin-bottom:10px"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Shop by Brand')}" onchange="updateField(${idx},'headerText',this.value)"></div>
    <div class="form-group" style="margin-bottom:10px"><label>Brands (comma-separated, blank = all)</label><input type="text" value="${escAttr(sec.brands||'')}" onchange="updateField(${idx},'brands',this.value)" placeholder="Nike, Adidas, Zara…"></div>
    <div class="form-grid">
      <div class="form-group"><label>Logo Size</label><select onchange="updateField(${idx},'size',this.value)"><option value="small" ${sec.size==='small'?'selected':''}>Small</option><option value="medium" ${(sec.size||'medium')==='medium'?'selected':''}>Medium</option><option value="large" ${sec.size==='large'?'selected':''}>Large</option></select></div>
    </div>`;
  }
  else if (type === 'reviewsCarousel') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Customer Reviews')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Reviews to Show</label><input type="number" value="${sec.count||6}" min="1" max="20" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Stars</label><select onchange="updateField(${idx},'minStars',parseInt(this.value))"><option value="3" ${(sec.minStars||4)===3?'selected':''}>3+</option><option value="4" ${(sec.minStars||4)===4?'selected':''}>4+</option><option value="5" ${sec.minStars===5?'selected':''}>5 only</option></select></div>
      <div class="form-group"><label>Rotate Every (secs)</label><input type="number" value="${sec.interval||4}" min="2" max="30" onchange="updateField(${idx},'interval',parseInt(this.value))"></div>
    </div>`;
  }
  else if (type === 'activity') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Message ({n} = count)</label><input type="text" value="${escAttr(sec.messageTemplate||'{n} people shopped with us recently')}" onchange="updateField(${idx},'messageTemplate',this.value)"></div>
      <div class="form-group"><label>Time Window</label><select onchange="updateField(${idx},'window',this.value)"><option value="24h" ${(sec.window||'24h')==='24h'?'selected':''}>24 Hours</option><option value="7d" ${sec.window==='7d'?'selected':''}>7 Days</option><option value="month" ${sec.window==='month'?'selected':''}>This Month</option></select></div>
    </div>`;
  }
  else if (type === 'recent') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Max Products</label><input type="number" value="${sec.maxProducts||8}" min="1" max="20" onchange="updateField(${idx},'maxProducts',parseInt(this.value))"></div>
      <div class="form-group"><label>Remember for (days)</label><input type="number" value="${sec.persistDays||30}" min="1" max="90" onchange="updateField(${idx},'persistDays',parseInt(this.value))"></div>
    </div>`;
  }
  else if (type === 'complete') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Suggestions per Item</label><input type="number" value="${sec.suggestionsPerItem||3}" min="1" max="10" onchange="updateField(${idx},'suggestionsPerItem',parseInt(this.value))"></div>
      <div class="form-group"><label>Strategy</label><select onchange="updateField(${idx},'strategy',this.value)"><option value="Same category" ${(sec.strategy||'Same category')==='Same category'?'selected':''}>Same Category</option><option value="Complementary" ${sec.strategy==='Complementary'?'selected':''}>Complementary</option><option value="AI picks" ${sec.strategy==='AI picks'?'selected':''}>AI Picks</option></select></div>
    </div>`;
  }
  else if (type === 'productCustomizer') {
    html = `<div style="font-size:12px;color:var(--muted);margin-bottom:10px">Pick a product to feature. All its details (title, price, rating, variations, Add to Cart, coupon) will be shown as a beautiful two-column card on the homepage.</div>
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1">
        <label>Product ID <span style="font-weight:400;color:var(--muted)">(find it in the product list URL: /admin/products/{id}/edit)</span></label>
        <input type="number" value="${sec.productId||''}" min="1" placeholder="e.g. 12" style="max-width:180px" onchange="updateField(${idx},'productId',parseInt(this.value)||'')">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label>Section Heading <span style="font-weight:400;color:var(--muted)">(optional, shown above the card)</span></label>
        <input type="text" value="${escAttr(sec.sectionTitle||'')}" placeholder="e.g. Featured Product" onchange="updateField(${idx},'sectionTitle',this.value)">
      </div>
    </div>
    <div style="font-size:12px;font-weight:700;color:var(--muted);margin:10px 0 6px;text-transform:uppercase;letter-spacing:.5px">Show / Hide elements</div>
    <div style="display:flex;flex-wrap:wrap;gap:10px">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showWishlist!==false?'checked':''} onchange="updateField(${idx},'showWishlist',this.checked)" style="width:14px;height:14px"> Wishlist button</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showRating!==false?'checked':''} onchange="updateField(${idx},'showRating',this.checked)" style="width:14px;height:14px"> Star rating</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showVariations!==false?'checked':''} onchange="updateField(${idx},'showVariations',this.checked)" style="width:14px;height:14px"> Variations</label>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer"><input type="checkbox" ${sec.showCoupon!==false?'checked':''} onchange="updateField(${idx},'showCoupon',this.checked)" style="width:14px;height:14px"> Coupon input</label>
    </div>`;
  }
  else if (type === 'recommended') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Recommended For You')}" onchange="updateField(${idx},'headerText',this.value)"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||8}" min="1" max="20" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Fallback</label><select onchange="updateField(${idx},'fallback',this.value)"><option value="trending" ${(sec.fallback||'trending')==='trending'?'selected':''}>Trending</option><option value="arrivals" ${sec.fallback==='arrivals'?'selected':''}>New Arrivals</option><option value="bestsellers" ${sec.fallback==='bestsellers'?'selected':''}>Best Sellers</option></select></div>
    </div>`;
  }
  else if (type === 'seasonal') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Special Season')}" onchange="updateField(${idx},'title',this.value)"></div>
      <div class="form-group"><label>Subtitle</label><input type="text" value="${escAttr(sec.subtitle||'')}" onchange="updateField(${idx},'subtitle',this.value)"></div>
      <div class="form-group"><label>Start Date</label><input type="date" value="${sec.startDate||''}" onchange="updateField(${idx},'startDate',this.value)"></div>
      <div class="form-group"><label>End Date</label><input type="date" value="${sec.endDate||''}" onchange="updateField(${idx},'endDate',this.value)"></div>
      <div class="form-group"><label>Color Theme</label><select onchange="updateField(${idx},'theme',this.value)"><option value="Gold & Purple" ${(sec.theme||'Gold & Purple')==='Gold & Purple'?'selected':''}>Gold & Purple</option><option value="Green & White" ${sec.theme==='Green & White'?'selected':''}>Green & White</option><option value="Red & Gold" ${sec.theme==='Red & Gold'?'selected':''}>Red & Gold</option></select></div>
    </div>`;
  }
  else if (type === 'referral') {
    html = `<div class="form-grid">
      <div class="form-group"><label>Reward for Referrer (EGP)</label><input type="number" value="${sec.rewardReferrer||50}" min="0" onchange="updateField(${idx},'rewardReferrer',parseInt(this.value))"></div>
      <div class="form-group"><label>Reward for New User (EGP)</label><input type="number" value="${sec.rewardNewUser||30}" min="0" onchange="updateField(${idx},'rewardNewUser',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Order to Qualify</label><input type="number" value="${sec.minOrder||200}" min="0" onchange="updateField(${idx},'minOrder',parseInt(this.value))"></div>
      <div class="form-group"><label>CTA Text</label><input type="text" value="${escAttr(sec.ctaText||'Invite Friends & Earn!')}" onchange="updateField(${idx},'ctaText',this.value)"></div>
    </div>`;
  }
  else if (type === 'announcement') {
    html = `<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:8px">📢 Full-width — appears above the page header</div>
    <div class="form-group" style="margin-bottom:10px"><label>Message</label><textarea style="width:100%;min-height:55px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:6px;font-family:inherit;font-size:12px;outline:none" onchange="updateField(${idx},'message',this.value)">${escHtml(sec.message||'Welcome to Ramo Store! Free shipping on orders over 500 EGP.')}</textarea></div>
    <div class="form-grid">
      <div class="form-group"><label>Scroll Speed</label><select onchange="updateField(${idx},'speed',this.value)"><option value="slow" ${sec.speed==='slow'?'selected':''}>Slow</option><option value="normal" ${(sec.speed||'normal')==='normal'?'selected':''}>Normal</option><option value="fast" ${sec.speed==='fast'?'selected':''}>Fast</option><option value="static" ${sec.speed==='static'?'selected':''}>Static</option></select></div>
      <div class="form-group"><label>Bar Color</label><select onchange="updateField(${idx},'barColor',this.value)"><option value="dark" ${(sec.barColor||'dark')==='dark'?'selected':''}>Dark (black)</option><option value="orange" ${sec.barColor==='orange'?'selected':''}>Brand Orange</option><option value="navy" ${sec.barColor==='navy'?'selected':''}>Navy</option><option value="white" ${sec.barColor==='white'?'selected':''}>White</option></select></div>
    </div>`;
  }
  else {
    html = `<div style="font-size:12px;color:var(--muted);margin-bottom:6px">Raw JSON:</div>
    <textarea style="width:100%;height:100px;font-family:monospace;font-size:11px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:6px;outline:none" onchange="updateRaw(${idx},this.value)">${escHtml(JSON.stringify(sec,null,2))}</textarea>`;
  }
  return html + buildDimEditor(sec, idx);
}

// ── RESPONSIVE DIMENSION EDITOR ───────────────────────────────────
function getDim(sec, platform, key, fallback) {
  return (sec.responsive && sec.responsive[platform] && sec.responsive[platform][key] !== undefined)
    ? sec.responsive[platform][key]
    : fallback;
}

function switchDimTab(idx, platform) {
  const bodyEl = document.getElementById('body-' + idx);
  if (!bodyEl) return;
  bodyEl.querySelectorAll('.dim-tab').forEach(t => t.classList.remove('active'));
  bodyEl.querySelectorAll('.dim-panel').forEach(p => p.classList.remove('active'));
  const tab = bodyEl.querySelector('.dim-tab[data-plat="' + platform + '"]');
  const panel = bodyEl.querySelector('.dim-panel[data-plat="' + platform + '"]');
  if (tab) tab.classList.add('active');
  if (panel) panel.classList.add('active');
}

function updateDimField(idx, platform, key, value) {
  if (!sections[idx].responsive) sections[idx].responsive = {};
  if (!sections[idx].responsive[platform]) sections[idx].responsive[platform] = {};
  sections[idx].responsive[platform][key] = value;
}

function resetDimField(idx, platform, key, input) {
  updateDimField(idx, platform, key, 0);
  if (input) input.value = 0;
}

function buildDimEditor(sec, idx) {
  const type = sec.layout;

  // Per-widget dimension field definitions
  // Each entry: { key, label, type, min, max, step, options (for select) }
  let fields = [];

  if (type === 'bannerImage') {
    fields = [
      { key:'bannerHeight', label:'Banner Height (px)', type:'number', min:80, max:900, step:10, def:420 },
      { key:'radius',       label:'Corner Radius (px)', type:'number', min:0,  max:40,  step:1,  def:2 },
    ];
  } else if (type === 'category') {
    fields = [
      { key:'size',   label:'Icon Scale',       type:'number', min:0.5, max:3,  step:0.1, def:1 },
      { key:'radius', label:'Icon Radius (0–50)',type:'number', min:0,   max:50, step:1,   def:50 },
    ];
  } else if (type === 'categoryCards') {
    fields = [
      { key:'columns',          label:'Columns',           type:'select', options:[{v:1,l:'1 col'},{v:2,l:'2 cols'},{v:3,l:'3 cols'},{v:4,l:'4 cols'}], def:3 },
      { key:'cardHeight',       label:'Card Height (px)',  type:'number', min:80,  max:500, step:10, def:220 },
      { key:'cardBorderRadius', label:'Corner Radius (px)',type:'number', min:0,   max:40,  step:1,  def:14 },
      { key:'maxItemsToShow',   label:'Max Items',         type:'number', min:2,   max:24,  step:1,  def:12 },
    ];
  } else if (type === 'twoColumn' || type === 'saleImages' || type === 'seupermarketstars') {
    const dW = type === 'saleImages' ? 140 : (type === 'seupermarketstars' ? 200 : 230);
    const dH = type === 'saleImages' ? 196 : (type === 'seupermarketstars' ? 200 : 230);
    fields = [
      { key:'productWidth',     label:'Card Width (px)',   type:'number', min:80,  max:500, step:1,  def:dW },
      { key:'cardHeight',       label:'Card Height (px)',  type:'number', min:0,   max:1000, step:1,  def:0 },
      { key:'imageWidth',       label:'Image Width (px)',  type:'number', min:0,   max:500, step:1,  def:0 },
      { key:'imageHeight',      label:'Image Height (px)', type:'number', min:60,  max:800, step:1,  def:dH },
      { key:'elementSpacing',   label:'Element Spacing (px)', type:'number', min:0, max:40, step:1, def:0 },
      { key:'cardBorderRadius', label:'Corner Radius (px)',type:'number', min:0,   max:40,  step:1,  def:10 },
      { key:'maxItemsToShow',   label:'Max Items',         type:'number', min:1,   max:30,  step:1,  def:8  },
    ];
  } else if (type === 'spacer') {
    fields = [
      { key:'height', label:'Height (px)', type:'number', min:4, max:300, step:4, def:24 },
    ];
  } else if (type === 'topVendors') {
    fields = [
      { key:'maxItemsToShow', label:'Max Vendors', type:'number', min:1, max:20, step:1, def:6 },
    ];
  } else if (type === 'trending' || type === 'arrivals' || type === 'recent' || type === 'recommended') {
    fields = [
      { key:'count',        label:'Items to Show', type:'number', min:1, max:20, step:1, def:8 },
      { key:'productWidth', label:'Card Width (px)',type:'number', min:60, max:400, step:10, def:130 },
    ];
  } else if (type === 'logo' || type === 'announcement' || type === 'flash' || type === 'divider') {
    // these widgets use generic padding only
  } else {
    // generic padding fallback for all others
  }

  // Always add padding fields for every widget except a few structural ones
  const noPad = ['logo','announcement','flash','spacer','divider'];
  if (!noPad.includes(type)) {
    fields.push({ key:'paddingTop',    label:'Padding Top (px)',   type:'number', min:0, max:100, step:4, def:0 });
    fields.push({ key:'paddingBottom', label:'Padding Bottom (px)',type:'number', min:0, max:100, step:4, def:0 });
  }

  if (fields.length === 0) return '';

  function buildPanelFields(platform) {
    return fields.map(f => {
      const val = getDim(sec, platform, f.key, f.def);
      if (f.type === 'select') {
        const opts = f.options.map(o => `<option value="${o.v}" ${val==o.v?'selected':''}>${o.l}</option>`).join('');
        return `<div class="form-group"><label>${f.label}</label><select onchange="updateDimField(${idx},'${platform}','${f.key}',${f.type==='number'?'parseInt(this.value)':'this.value'})">${opts}</select></div>`;
      } else if (f.key === 'imageWidth') {
        return `<div class="form-group"><label>${f.label} <span style="font-size:10px;opacity:.62;font-weight:400">0 = full card width</span></label><div style="display:flex;gap:6px;align-items:center"><input type="number" value="${val}" min="${f.min}" max="${f.max}" step="${f.step||1}" onchange="updateDimField(${idx},'${platform}','${f.key}',parseFloat(this.value)||0)"><button type="button" class="btn btn-sm" style="white-space:nowrap" onclick="resetDimField(${idx},'${platform}','${f.key}',this.previousElementSibling)">Full width</button></div></div>`;
      } else {
        return `<div class="form-group"><label>${f.label}</label><input type="number" value="${val}" min="${f.min}" max="${f.max}" step="${f.step||1}" onchange="updateDimField(${idx},'${platform}','${f.key}',parseFloat(this.value)||${f.def})"></div>`;
      }
    }).join('');
  }

  const bp = (sec.responsive && sec.responsive.breakpoint) ? sec.responsive.breakpoint : 768;

  return `
<div class="dim-section">
  <div class="dim-section-label">📐 Responsive Dimensions <span style="font-weight:400;opacity:.55;font-size:10px">(adapts to browser width)</span></div>
  <div class="form-group" style="margin-bottom:8px">
    <label style="font-size:10px;opacity:.7">Mobile breakpoint — switch below this width (px)</label>
    <input type="number" value="${bp}" min="320" max="1920" step:10 style="max-width:100px"
      onchange="if(!sections[${idx}].responsive) sections[${idx}].responsive={};sections[${idx}].responsive.breakpoint=parseInt(this.value)||768;">
  </div>
  <div class="dim-tab-bar">
    <button class="dim-tab dim-tab-win active" data-plat="desktop" onclick="switchDimTab(${idx},'desktop')">🖥️ Wide screen</button>
    <button class="dim-tab dim-tab-and" data-plat="mobile" onclick="switchDimTab(${idx},'mobile')">📱 Narrow screen</button>
  </div>
  <div class="dim-panel active" data-plat="desktop">
    <div class="form-grid">${buildPanelFields('desktop')}</div>
  </div>
  <div class="dim-panel" data-plat="mobile">
    <div class="form-grid">${buildPanelFields('mobile')}</div>
  </div>
</div>`;
}

// ── ITEM BUILDERS ─────────────────────────────────────────────────
function buildCatItem(idx,ii,item){
  return `<div class="item-row" id="catItem-${idx}-${ii}">
    <select style="width:130px" onchange="updateCatItem(${idx},${ii},'category',parseInt(this.value))"><option value="">Select category</option>${catOptions(item.category)}</select>
    <input type="text" value="${escAttr(item.label||'')}" placeholder="Label" style="width:80px" onchange="updateCatItem(${idx},${ii},'label',this.value)">
    <input type="text" value="${escAttr(item.image||'')}" placeholder="Image URL" style="flex:1" onchange="updateCatItem(${idx},${ii},'image',this.value)">
    <button class="btn btn-danger btn-sm" onclick="removeCatItem(${idx},${ii})">×</button>
  </div>`;
}
function buildBannerItem(idx,ii,item){
  return `<div class="item-row" id="bannerItem-${idx}-${ii}">
    <input type="text" value="${escAttr(item.image||'')}" placeholder="Image URL" style="flex:2" onchange="updateBannerItem(${idx},${ii},'image',this.value)">
    <select style="width:140px" onchange="updateBannerItem(${idx},${ii},'category',this.value?parseInt(this.value):undefined)"><option value="">No link</option>${catOptions(item.category)}</select>
    <button class="btn btn-danger btn-sm" onclick="removeBannerItem(${idx},${ii})">×</button>
  </div>`;
}

// ── RENDER / SORTABLE ─────────────────────────────────────────────
function renderAll(){
  const list = document.getElementById('lpSectionList');
  list.innerHTML = sections.map((s,i)=>buildCard(s,i)).join('');
  initSortable();
}
function initSortable(){
  Sortable.create(document.getElementById('lpSectionList'),{
    handle:'.tl-drag-handle', animation:150, ghostClass:'sortable-ghost',
    onEnd(evt){
      const moved = sections.splice(evt.oldIndex,1)[0];
      sections.splice(evt.newIndex,0,moved);
      renderAll();
    }
  });
}

// ── ACTIONS ──────────────────────────────────────────────────────
function toggleBody(idx){ const b=document.getElementById('body-'+idx); if(b) b.classList.toggle('open'); }

function removeSection(idx){
  if(!confirm('Remove this section?')) return;
  sections.splice(idx,1);
  renderAll();
}
function updateField(idx,key,value){ sections[idx][key]=value; }
function updateRaw(idx,raw){ try{ sections[idx]=JSON.parse(raw); }catch(e){} }

function toggleHidden(idx){
  sections[idx].hidden=!sections[idx].hidden;
  renderAll();
  setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50);
}
function setFlashEndFromNow(idx){
  const durEl=document.getElementById('flash-dur-'+idx);
  const hours=durEl?(parseInt(durEl.value)||4):(sections[idx].duration||4);
  const endMs=Date.now()+hours*3600*1000;
  sections[idx].endTime=endMs;
  const dtEl=document.getElementById('flash-endtime-'+idx);
  if(dtEl){ dtEl.value=new Date(endMs-new Date().getTimezoneOffset()*60000).toISOString().slice(0,16); }
}

// Cat item helpers
function addCatItem(idx){
  if(!sections[idx].items) sections[idx].items=[];
  sections[idx].items.push({category:'',label:'',image:'',colors:['#3CC2BF','#3CC2BF']});
  const ii=sections[idx].items.length-1;
  document.getElementById('catItems-'+idx).insertAdjacentHTML('beforeend',buildCatItem(idx,ii,sections[idx].items[ii]));
}
function removeCatItem(idx,ii){ sections[idx].items.splice(ii,1); renderAll(); setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50); }
function updateCatItem(idx,ii,key,value){ if(!sections[idx].items) sections[idx].items=[]; if(!sections[idx].items[ii]) sections[idx].items[ii]={}; sections[idx].items[ii][key]=value; }

// Stats item helpers
function addStatsItem(idx){
  if(!sections[idx].items) sections[idx].items=[];
  const statKeys=['products','vendors','categories','brands','orders','reviews'];
  const usedKeys=sections[idx].items.map(i=>i.key);
  const nextKey=statKeys.find(k=>!usedKeys.includes(k))||'products';
  sections[idx].items.push({key:nextKey,label:nextKey.charAt(0).toUpperCase()+nextKey.slice(1)});
  const ii=sections[idx].items.length-1;
  const item=sections[idx].items[ii];
  const row=document.createElement('div');
  row.className='item-row'; row.id=`statsItem-${idx}-${ii}`;
  row.innerHTML=`<select style="width:130px" onchange="updateStatsItem(${idx},${ii},'key',this.value)">${statKeys.map(k=>`<option value="${k}" ${item.key===k?'selected':''}>${k.charAt(0).toUpperCase()+k.slice(1)}</option>`).join('')}</select><input type="text" value="${escAttr(item.label||'')}" placeholder="Label" style="flex:1" onchange="updateStatsItem(${idx},${ii},'label',this.value)"><button class="btn btn-danger btn-sm" onclick="removeStatsItem(${idx},${ii})">×</button>`;
  document.getElementById('statsItems-'+idx).appendChild(row);
}
function removeStatsItem(idx,ii){ sections[idx].items.splice(ii,1); renderAll(); setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50); }
function updateStatsItem(idx,ii,key,value){ if(!sections[idx].items) sections[idx].items=[]; if(!sections[idx].items[ii]) sections[idx].items[ii]={}; sections[idx].items[ii][key]=value; }

// Banner item helpers
function addBannerItem(idx){
  if(!sections[idx].items) sections[idx].items=[];
  sections[idx].items.push({image:'',padding:7});
  const ii=sections[idx].items.length-1;
  document.getElementById('bannerItems-'+idx).insertAdjacentHTML('beforeend',buildBannerItem(idx,ii,sections[idx].items[ii]));
}
function removeBannerItem(idx,ii){ sections[idx].items.splice(ii,1); renderAll(); setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50); }
function updateBannerItem(idx,ii,key,value){ if(!sections[idx].items) sections[idx].items=[]; if(!sections[idx].items[ii]) sections[idx].items[ii]={}; if(value===undefined) delete sections[idx].items[ii][key]; else sections[idx].items[ii][key]=value; }

function buildFlexBannerItem(idx,ii,item){
  const total=(sections[idx].items||[]).length;
  return `<div class="item-row" id="flexBannerItem-${idx}-${ii}" style="align-items:flex-start;flex-wrap:wrap">
    <div style="display:flex;gap:8px;align-items:center;width:100%;font-size:12px;color:var(--muted);font-weight:700">
      <span style="background:rgba(124,58,237,.15);color:#a78bfa;border-radius:5px;padding:3px 7px">Banner ${ii+1}</span>
      <span style="margin-left:auto;display:flex;gap:4px"><button class="btn btn-sm" onclick="moveFlexBannerItem(${idx},${ii},-1)" ${ii===0?'disabled':''} title="Move up">↑</button><button class="btn btn-sm" onclick="moveFlexBannerItem(${idx},${ii},1)" ${ii===total-1?'disabled':''} title="Move down">↓</button></span>
    </div>
    <div style="display:flex;gap:5px;flex:2;min-width:200px"><input type="text" value="${escAttr(item.image||'')}" placeholder="Image URL (required)" style="flex:1;min-width:0" onchange="updateFlexBannerItem(${idx},${ii},'image',this.value)"><a class="btn btn-sm" href="{{ route('admin.image-gallery') }}" target="_blank" rel="noopener" title="Open Image Gallery and copy a reusable image URL">Gallery</a></div>
    <input type="text" value="${escAttr(item.link||'')}" placeholder="Destination URL e.g. /shop?category=1" style="flex:2;min-width:200px" onchange="updateFlexBannerItem(${idx},${ii},'link',this.value)">
    <select style="width:120px" onchange="updateFlexBannerItem(${idx},${ii},'width',this.value)">
      <option value="full" ${(item.width||'half')==='full'?'selected':''}>Full width</option>
      <option value="half" ${(item.width||'half')==='half'?'selected':''}>Half width</option>
      <option value="quarter" ${item.width==='quarter'?'selected':''}>Quarter width</option>
    </select>
    <input type="text" value="${escAttr(item.alt||'')}" placeholder="Image description" style="flex:1;min-width:160px" onchange="updateFlexBannerItem(${idx},${ii},'alt',this.value)">
    <button class="btn btn-danger btn-sm" onclick="removeFlexBannerItem(${idx},${ii})" title="Remove banner">×</button>
  </div>`;
}
function addFlexBannerItem(idx){
  if(!sections[idx].items) sections[idx].items=[];
  sections[idx].items.push({image:'',link:'',width:'half',alt:''});
  renderAll();
  setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50);
}
function updateFlexBannerItem(idx,ii,key,value){
  if(!sections[idx].items) sections[idx].items=[];
  if(!sections[idx].items[ii]) sections[idx].items[ii]={};
  sections[idx].items[ii][key]=value;
}
function removeFlexBannerItem(idx,ii){
  sections[idx].items.splice(ii,1);
  renderAll();
  setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50);
}
function moveFlexBannerItem(idx,ii,direction){
  const target=ii+direction;
  const items=sections[idx].items||[];
  if(target<0||target>=items.length) return;
  [items[ii],items[target]]=[items[target],items[ii]];
  renderAll();
  setTimeout(()=>{ const b=document.getElementById('body-'+idx); if(b) b.classList.add('open'); },50);
}

// ── DEFAULTS ─────────────────────────────────────────────────────
const DEFAULTS = {
  bannerImage:       { layout:'bannerImage', design:'default', isSlider:true, autoPlay:true, radius:2, items:[] },
  flexBannerGrid:    { layout:'flexBannerGrid', name:'Flexible Banner Grid', headerText:'', gap:12, radius:10, mobileColumns:2, items:[] },
  category:          { layout:'category', type:'icon', wrap:false, size:1, radius:50, items:[] },
  categoryCards:     { layout:'categoryCards', headerText:'Shop by Category', columns:3, cardHeight:220, cardBorderRadius:14, maxItemsToShow:12, showCount:true, parentOnly:true },
  twoColumn:         { layout:'twoColumn', headerText:'New Section', maxItemsToShow:8, category:'' },
  saleImages:        { layout:'saleImages', headerText:'Featured Products', maxItemsToShow:8, category:'' },
  seupermarketstars: { layout:'seupermarketstars', name:'Featured', category:'' },
  topVendors:        { layout:'topVendors', headerText:'Top Sellers', maxItemsToShow:6, sortBy:'products' },
  brands:            { layout:'brands' },
  coupons:           { layout:'coupons', headerText:"This Week's Deals", subLabel:'Use code at checkout', maxItemsToShow:6, sortBy:'amount', showExpiredFallback:true, hideWhenEmpty:true },
  statsBar:          { layout:'statsBar', bgColor:'#111111', textColor:'#ffffff', items:[{key:'products',label:'Products'},{key:'vendors',label:'Vendors'},{key:'categories',label:'Categories'},{key:'brands',label:'Brands'}] },
  promoBlock:        { layout:'promoBlock', headline:'Special Offer', subtext:'Discover exclusive deals.', btnText:'Shop Now', btnLink:'/shop', bgColor:'#1a1a2e', textColor:'#ffffff', btnColor:'#e85d26', align:'left' },
  testimonials:      { layout:'testimonials', headerText:'What Our Customers Say', maxItemsToShow:4, minRating:4 },
  newsletter:        { layout:'newsletter', headline:'Stay in the Loop', subtext:'Get the latest deals delivered to your inbox.', btnText:'Subscribe', placeholder:'Your email address', bgColor:'#f0ede8' },
  trending:          { layout:'trending', headerText:'Trending Now', count:10, algo:'sold7d', refreshInterval:24, showRankBadge:true },
  arrivals:          { layout:'arrivals', headerText:'New Arrivals', count:8, speed:'normal', tag:'Just Arrived', pauseOnHover:true, loopInfinitely:true },
  brandLogos:        { layout:'brandLogos', headerText:'Shop by Brand', brands:'', size:'medium', clickableFilter:true, showNameBelowLogo:true },
  reviewsCarousel:   { layout:'reviewsCarousel', headerText:'Customer Reviews', count:6, minStars:4, interval:4, showProductReviewed:true, allowManualNavigation:true },
  activity:          { layout:'activity', messageTemplate:'{n} people shopped with us recently', minCount:1, window:'24h', randomizeSlightly:true },
  recent:            { layout:'recent', maxProducts:8, persistDays:30, showForGuests:true },
  bundle:            { layout:'bundle', title:'Bundle Deal', category:'', minQty:2, freeItems:1, showSavingsBadge:true },
  loyalty:           { layout:'loyalty', rate:10, minRedeem:100, convRate:'100 pts = 5 EGP' },
  seasonal:          { layout:'seasonal', title:'Special Season', subtitle:'Limited-time offers', startDate:'', endDate:'', theme:'Gold & Purple', fullWidthBanner:true, animateEntrance:true },
  referral:          { layout:'referral', rewardReferrer:50, rewardNewUser:30, minOrder:200, ctaText:'Invite Friends & Earn!', shareViaWhatsApp:true, shareViaLink:true },
  complete:          { layout:'complete', suggestionsPerItem:3, strategy:'Same category' },
  recommended:       { layout:'recommended', headerText:'Recommended For You', count:8, fallback:'trending' },
  announcement:      { layout:'announcement', message:'Welcome to Ramo Store! Free shipping on orders over 500 EGP.', speed:'normal', barColor:'dark', dismissableByUser:true, showOnAllPages:true },
  flash:             { layout:'flash', title:'Flash Sale', discount:20, duration:4, minOrder:0, showCountdownSeconds:true, applyTo:'all', targetCategories:[], targetProductIds:[] },
  spacer:            { layout:'spacer', height:24 },
  divider:           { layout:'divider' },
  productCustomizer: { layout:'productCustomizer', productId:'', sectionTitle:'', showWishlist:true, showRating:true, showVariations:true, showCoupon:true },
};

// ── WIDGET INFO ───────────────────────────────────────────────────
const WIDGET_INFO = {
  bannerImage:     { title:'Banner / Slider', desc:'Full-width hero image or auto-playing slideshow. Each slide can link to a category.', tags:['Full-width','Auto-play','Multiple slides'] },
  flexBannerGrid:  { title:'Flexible Banner Grid', desc:'A responsive mosaic of linked banners. Add as many as you need, set every banner to full, half, or quarter width, and reorder them visually.', tags:['Unlimited images','Full / half / quarter','Phone layout','Custom spacing'] },
  category:        { title:'Categories Strip', desc:'Horizontal row of category icons. Great for quick navigation.', tags:['Icon strip','Scrollable'] },
  categoryCards:   { title:'Category Cards', desc:'Beautiful full-image grid cards for each category. Auto-loads categories with product counts and hover effects.', tags:['Auto-loaded','Grid layout','Hover zoom','Gradient overlay'] },
  twoColumn:       { title:'Products Grid', desc:'2–4 column product grid for a category or all products.', tags:['Product cards','Category filter'] },
  saleImages:      { title:'Products Scroll', desc:'Horizontal scrollable strip of product cards.', tags:['Horizontal scroll','Category filter'] },
  seupermarketstars:{ title:'Featured Items', desc:'Wide product grid showcasing featured products.', tags:['Product cards','4 columns'] },
  topVendors:      { title:'Top Vendors', desc:'Horizontal scroll of vendor cards sorted by products, rating, or newest.', tags:['Vendor cards','Sort options'] },
  brands:          { title:'Brands Strip', desc:'All brands from the database as clickable chips.', tags:['Auto-loaded','No config'] },
  coupons:         { title:'Coupons Strip', desc:'Active discount coupons with copy-to-clipboard buttons.', tags:['Auto-loaded','Copy button'] },
  statsBar:        { title:'Stats Bar', desc:'Full-width dark bar with live store statistics.', tags:['Live stats','Dark bar'] },
  promoBlock:      { title:'Promo Block', desc:'Bold promotional banner with headline, subtext, and a CTA button.', tags:['Custom colors','CTA button'] },
  testimonials:    { title:'Testimonials Grid', desc:'Grid of star-rated customer review cards from the database.', tags:['Auto-loaded','Min stars filter'] },
  reviewsCarousel: { title:'Reviews Carousel', desc:'Auto-rotating single review spotlight.', tags:['Auto-rotate','Navigation dots'] },
  newsletter:      { title:'Newsletter Signup', desc:'Email subscription block with custom background.', tags:['Email capture','Custom background'] },
  bundle:          { title:'Bundle Deal Card', desc:'Buy-X-get-Y-free promotional card.', tags:['Promotional card'] },
  loyalty:         { title:'Loyalty Points Banner', desc:'Promotes your loyalty program with points rate info.', tags:['Points rate','CTA button'] },
  activity:        { title:'Live Activity Banner', desc:'Shows how many people shopped recently — social proof.', tags:['Live count','Social proof'] },
  referral:        { title:'Referral Widget', desc:'Share-and-earn card with referral link and WhatsApp sharing.', tags:['Referral link','WhatsApp'] },
  recent:          { title:'Recently Viewed', desc:'Products the visitor has viewed. Uses localStorage.', tags:['Browser history','Auto-hides'] },
  recommended:     { title:'Recommended For You', desc:'Personalised product strip with trending fallback.', tags:['Personalized','Trending fallback'] },
  complete:        { title:'Complete the Look', desc:'Encourages customers to explore complementary products.', tags:['Cross-sell','Strategy options'] },
  trending:        { title:'Trending Now Strip', desc:'Best-selling products sorted by total sales with rank badges.', tags:['DB-powered','Rank badges'] },
  arrivals:        { title:'New Arrivals Ticker', desc:'Auto-scrolling ticker of your newest products.', tags:['Auto-scroll','DB-powered'] },
  brandLogos:      { title:'Brand Logos Row', desc:'Grid of branded logo chips.', tags:['Clickable filter','Custom brands'] },
  announcement:    { title:'Announcement Bar', desc:'Full-width scrolling ticker above the page header.', tags:['Full-width','Scrolling text','Dismissable'] },
  flash:           { title:'Flash Sale Timer', desc:'Urgency countdown bar in fire-red at the top of the page.', tags:['Countdown timer','Full-width'] },
  seasonal:        { title:'Seasonal Banner', desc:'Full-width gradient banner that auto-shows/hides based on dates.', tags:['Date range','Color themes'] },
  spacer:            { title:'Spacer', desc:'Adds vertical empty space between sections.', tags:['Layout','Height control'] },
  divider:           { title:'Divider Line', desc:'Adds a subtle horizontal rule between sections.', tags:['Layout'] },
  productCustomizer: { title:'Product Customizer', desc:'Showcases a single product with full detail: title, wishlist, rating, price, variations, Add to Cart, and coupon input.', tags:['Featured product','Add to cart','Wishlist','Coupon'] },
};

// ── WIDGET MOCKUPS ────────────────────────────────────────────────
const WIDGET_MOCKUPS = {
  bannerImage:`<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;height:90px;display:flex;align-items:center;justify-content:center"><div style="text-align:center"><div style="font-size:11px;opacity:.5;margin-bottom:4px;color:#fff;letter-spacing:1px">HERO BANNER</div><div style="font-size:16px;font-weight:800;color:#fff">🖼️ Image Slider</div></div></div>`,
  flexBannerGrid:`<div style="display:grid;grid-template-columns:repeat(4,1fr);grid-auto-rows:42px;gap:6px"><div style="grid-column:span 4;background:linear-gradient(135deg,#7c3aed,#c084fc);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:800">FULL WIDTH</div><div style="grid-column:span 2;background:linear-gradient(135deg,#e85d26,#fb923c);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:800">HALF</div><div style="grid-column:span 1;background:#0ea5e9;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:800">¼</div><div style="grid-column:span 1;background:#22c55e;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:800">¼</div></div>`,
  category:`<div style="display:flex;gap:10px;flex-wrap:wrap">${['👗 Clothes','👟 Shoes','👜 Bags','📱 Phones','💄 Beauty'].map(c=>`<div style="display:flex;flex-direction:column;align-items:center;gap:4px"><div style="width:42px;height:42px;border-radius:50%;background:#f0ede8;border:2px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:18px">${c.split(' ')[0]}</div><span style="font-size:10px;color:#333;font-weight:600">${c.split(' ')[1]}</span></div>`).join('')}</div>`,
  categoryCards:`<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:7px">${[['#e85d26','Bags'],['#1a1a2e','Shoes'],['#22c55e','Clothes'],['#8b5cf6','Phones'],['#f59e0b','Beauty'],['#ec4899','Outerwear']].map(([bg,label])=>`<div style="height:68px;background:${bg};border-radius:9px;overflow:hidden;position:relative"><div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75),transparent)"></div><div style="position:absolute;bottom:7px;left:9px;color:#fff;font-size:11px;font-weight:800">${label}</div></div>`).join('')}</div>`,
  twoColumn:`<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px">${Array(4).fill(0).map((_,i)=>`<div style="border-radius:6px;overflow:hidden;border:1px solid #eee"><div style="height:60px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8fd'][i]};display:flex;align-items:center;justify-content:center;font-size:20px">🛍️</div><div style="padding:5px"><div style="height:6px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="height:8px;background:#e85d26;border-radius:3px;width:60%"></div></div></div>`).join('')}</div>`,
  saleImages:`<div style="display:flex;gap:7px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:90px;border-radius:7px;overflow:hidden;border:1px solid #eee"><div style="height:70px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:22px">🛍️</div><div style="padding:4px"><div style="height:5px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="height:7px;background:#e85d26;border-radius:3px;width:55%"></div></div></div>`).join('')}</div>`,
  seupermarketstars:`<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px">${Array(4).fill(0).map((_,i)=>`<div style="border-radius:6px;overflow:hidden;border:1px solid #eee"><div style="height:70px;background:${['#fff8f0','#f0f8ff','#f0fff4','#fff0f8'][i]};display:flex;align-items:center;justify-content:center;font-size:24px">⭐</div><div style="padding:5px"><div style="height:6px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="height:8px;background:#e85d26;border-radius:3px;width:65%"></div></div></div>`).join('')}</div>`,
  topVendors:`<div style="display:flex;gap:8px;overflow:hidden">${['🏪','🛒','🏬','🏪'].map((ic)=>`<div style="flex-shrink:0;width:80px;border-radius:8px;border:1px solid #eee;padding:8px;text-align:center"><div style="width:36px;height:36px;border-radius:50%;background:#f5f5f5;margin:0 auto 5px;font-size:18px;display:flex;align-items:center;justify-content:center">${ic}</div><div style="height:6px;background:#ddd;border-radius:3px;margin-bottom:3px"></div><div style="font-size:9px;color:#999">12 items</div></div>`).join('')}</div>`,
  brands:`<div style="display:flex;flex-wrap:wrap;gap:6px">${['Nike','Adidas','Zara','H&M','Gucci','Puma'].map(b=>`<span style="padding:4px 10px;border:1px solid #ddd;border-radius:20px;font-size:11px;font-weight:600;color:#555">${b}</span>`).join('')}</div>`,
  coupons:`<div><div style="display:flex;justify-content:space-between;margin-bottom:8px"><span style="font-size:12px;font-weight:700;color:#333">This Week's Deals</span><span style="font-size:10px;color:#999">Use code at checkout</span></div><div style="display:flex;gap:7px">${[['#e85d26','SAVE20','20% off'],['#1a1a2e','FREESHIP','Free shipping']].map(([bg,code,label])=>`<div style="background:${bg};border-radius:7px;padding:9px 12px;color:#fff;flex:1"><div style="font-size:13px;font-weight:800">${label}</div><div style="display:flex;align-items:center;gap:5px;margin-top:4px"><span style="background:rgba(255,255,255,.25);padding:2px 7px;border-radius:3px;font-size:9px;font-weight:700">${code}</span></div></div>`).join('')}</div></div>`,
  statsBar:`<div style="background:#111;border-radius:8px;padding:14px;display:flex;justify-content:space-around">${[['1,240','Products'],['48','Vendors'],['32','Categories'],['120','Brands']].map(([n,l])=>`<div style="text-align:center"><div style="font-size:20px;font-weight:800;color:#fff">${n}</div><div style="font-size:9px;color:rgba(255,255,255,.5);text-transform:uppercase;margin-top:2px">${l}</div></div>`).join('')}</div>`,
  promoBlock:`<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;padding:14px 18px;display:flex;align-items:center;gap:14px"><div style="width:60px;height:50px;border-radius:7px;background:rgba(255,255,255,.1);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px">📣</div><div style="flex:1"><div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:3px">Special Offer</div><div style="font-size:11px;color:rgba(255,255,255,.6);margin-bottom:8px">Exclusive deals and limited-time offers.</div><span style="background:#e85d26;color:#fff;padding:4px 12px;border-radius:16px;font-size:11px;font-weight:700">Shop Now</span></div></div>`,
  testimonials:`<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px">${[['Sarah K.','Amazing quality!',5],['Omar M.','Fast shipping.',4]].map(([n,c,r])=>`<div style="border:1px solid #eee;border-radius:7px;padding:9px"><div style="color:#f5a623;font-size:11px;margin-bottom:3px">${'★'.repeat(r)}${'☆'.repeat(5-r)}</div><div style="font-size:11px;color:#555;margin-bottom:5px">"${c}"</div><div style="display:flex;align-items:center;gap:5px"><div style="width:18px;height:18px;border-radius:50%;background:#e85d26;color:#fff;font-size:9px;display:flex;align-items:center;justify-content:center">${n[0]}</div><span style="font-size:10px;font-weight:600">${n}</span></div></div>`).join('')}</div>`,
  reviewsCarousel:`<div style="border:1px solid #eee;border-radius:8px;padding:16px;text-align:center"><div style="color:#f5a623;margin-bottom:6px">★★★★★</div><div style="font-size:12px;color:#444;font-style:italic;margin-bottom:10px">"Absolutely love this product!"</div><div style="display:flex;align-items:center;justify-content:center;gap:7px"><div style="width:24px;height:24px;border-radius:50%;background:#e85d26;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center">S</div><span style="font-size:11px;font-weight:600">Sarah K.</span></div></div>`,
  newsletter:`<div style="background:#f0ede8;border-radius:8px;padding:16px;text-align:center"><div style="font-size:14px;font-weight:800;color:#333;margin-bottom:3px">Stay in the Loop</div><div style="font-size:11px;color:#888;margin-bottom:10px">Get the latest deals delivered to your inbox.</div><div style="display:flex;gap:5px;max-width:260px;margin:auto"><input type="text" placeholder="Your email address" style="flex:1;padding:6px 9px;border:1px solid #ddd;border-radius:16px;font-size:11px;background:#fff" readonly><button style="padding:6px 12px;background:#333;color:#fff;border:none;border-radius:16px;font-size:11px;font-weight:700">Subscribe</button></div></div>`,
  bundle:`<div style="background:linear-gradient(135deg,#fff8f4,#fde8d8);border:1.5px solid #fbd5bd;border-radius:8px;padding:12px;position:relative"><div style="display:flex;align-items:center;gap:10px"><span style="font-size:30px">🎁</span><div><div style="font-size:13px;font-weight:800;color:#333;margin-bottom:2px">Bundle Deal</div><div style="font-size:11px;color:#888">Buy 2, get 1 FREE!</div></div><span style="background:#e85d26;color:#fff;padding:5px 12px;border-radius:16px;font-size:11px;font-weight:700;margin-left:auto">Shop Now</span></div></div>`,
  loyalty:`<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;padding:12px 16px"><div style="display:flex;align-items:center;gap:10px"><span style="font-size:26px">⭐</span><div style="flex:1"><div style="font-size:12px;font-weight:700;color:#fff;margin-bottom:1px">Earn 10 points per EGP spent!</div><div style="font-size:10px;color:rgba(255,255,255,.6)">100 pts = 5 EGP · Redeem from 100 pts</div></div><span style="background:#e85d26;color:#fff;padding:5px 12px;border-radius:16px;font-size:11px;font-weight:700">Earn</span></div></div>`,
  activity:`<div style="display:flex;justify-content:center"><div style="display:inline-flex;align-items:center;gap:7px;background:#fff8f4;border:1.5px solid #fde8d8;border-radius:26px;padding:6px 14px"><span style="width:8px;height:8px;border-radius:50%;background:#e85d26;display:inline-block"></span><span style="font-size:12px;font-weight:500;color:#7c3826">142 people shopped with us recently</span></div></div>`,
  referral:`<div style="background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1.5px solid #bae6fd;border-radius:8px;padding:12px"><div style="display:flex;gap:10px;align-items:flex-start"><span style="font-size:26px">🎁</span><div><div style="font-size:12px;font-weight:800;color:#0c4a6e;margin-bottom:2px">Invite Friends & Earn!</div><div style="font-size:11px;color:#0369a1;margin-bottom:7px">You earn <strong>50 EGP</strong> and your friend gets <strong>30 EGP</strong> off!</div><div style="display:flex;gap:5px"><input type="text" value="ramostore.com/ref/USER" style="flex:1;padding:4px 7px;border:1px solid #bae6fd;border-radius:5px;font-size:10px;background:#fff" readonly><span style="padding:4px 9px;background:#25d366;color:#fff;border-radius:5px;font-size:10px;font-weight:700">WhatsApp</span></div></div></div></div>`,
  recent:`<div><div style="font-size:11px;font-weight:700;color:#333;margin-bottom:7px">Recently Viewed</div><div style="display:flex;gap:7px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:80px;text-align:center"><div style="width:80px;height:70px;border-radius:7px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8fd'][i]};display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:3px">🛍️</div><div style="font-size:9px;color:#e85d26;font-weight:700">199 EGP</div></div>`).join('')}</div></div>`,
  recommended:`<div><div style="font-size:11px;font-weight:700;color:#333;margin-bottom:7px">💡 Recommended For You</div><div style="display:flex;gap:7px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:80px;border-radius:7px;overflow:hidden;border:1px solid #eee"><div style="height:65px;background:${['#fff0e8','#e8f0ff','#e8fff0','#ffe8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:20px">🛍️</div><div style="padding:4px"><div style="height:5px;background:#eee;border-radius:3px;margin-bottom:2px"></div><div style="height:7px;background:#e85d26;border-radius:3px;width:55%"></div></div></div>`).join('')}</div></div>`,
  complete:`<div style="background:#fff;border:1.5px solid #eee;border-radius:8px;padding:14px"><div style="display:flex;align-items:center;gap:10px"><span style="font-size:26px">👗</span><div><div style="font-size:13px;font-weight:700;color:#333;margin-bottom:2px">Complete the Look</div><div style="font-size:11px;color:#888">Find items that go perfectly together.</div></div><span style="background:#333;color:#fff;padding:5px 12px;border-radius:16px;font-size:11px;font-weight:700;margin-left:auto">Browse</span></div></div>`,
  trending:`<div><div style="display:flex;justify-content:space-between;margin-bottom:7px"><span style="font-size:11px;font-weight:700;color:#333">🔥 Trending Now</span><span style="font-size:10px;color:#e85d26">View all →</span></div><div style="display:flex;gap:7px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:80px;border-radius:7px;overflow:hidden;border:1px solid #eee;position:relative"><span style="position:absolute;top:4px;left:4px;background:#e85d26;color:#fff;font-size:8px;font-weight:800;padding:1px 4px;border-radius:8px">#${i+1}</span><div style="height:65px;background:${['#fff0e8','#e8f0ff','#e8fff0','#ffe8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:20px">🛍️</div><div style="padding:4px"><div style="font-size:9px;color:#e85d26;font-weight:600">${[240,185,130,98][i]}+ sold</div></div></div>`).join('')}</div></div>`,
  arrivals:`<div><div style="display:flex;justify-content:space-between;margin-bottom:7px"><span style="font-size:11px;font-weight:700;color:#333">✨ New Arrivals</span><span style="font-size:10px;color:#e85d26">See all →</span></div><div style="display:flex;gap:7px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:80px;border-radius:7px;overflow:hidden;border:1px solid #eee"><div style="height:65px;background:${['#f0e8ff','#e8fff0','#fff0e8','#e8f0ff'][i]};display:flex;align-items:center;justify-content:center;font-size:20px">✨</div><div style="padding:4px"><span style="background:#e85d2615;color:#e85d26;font-size:8px;font-weight:700;padding:1px 5px;border-radius:7px">New</span></div></div>`).join('')}</div></div>`,
  brandLogos:`<div><div style="font-size:11px;font-weight:700;color:#333;margin-bottom:7px">Shop by Brand</div><div style="display:flex;flex-wrap:wrap;gap:6px">${['Nike','Adidas','Zara','H&M','Puma','Levi\'s'].map(b=>`<div style="width:54px;height:40px;border:1px solid #eee;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#555">${b}</div>`).join('')}</div></div>`,
  announcement:`<div style="background:#111;color:#fff;padding:10px 14px;border-radius:6px;display:flex;align-items:center;gap:10px"><span style="font-size:14px">📢</span><div style="flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:12px">Welcome to Ramo Store! Free shipping on orders over 500 EGP.</div><span style="font-size:14px;opacity:.6">×</span></div>`,
  flash:`<div style="background:#ef4444;color:#fff;padding:10px 14px;border-radius:6px;display:flex;align-items:center;gap:10px"><span style="font-size:14px">⚡</span><div style="flex:1"><div style="font-size:12px;font-weight:700">Flash Sale — 20% OFF</div></div><div style="display:flex;gap:5px;font-size:11px;font-weight:800"><span style="background:rgba(0,0,0,.25);padding:3px 7px;border-radius:4px">02</span><span>:</span><span style="background:rgba(0,0,0,.25);padding:3px 7px;border-radius:4px">45</span><span>:</span><span style="background:rgba(0,0,0,.25);padding:3px 7px;border-radius:4px">30</span></div></div>`,
  seasonal:`<div style="background:linear-gradient(135deg,#7c3aed,#c2410c);color:#fff;padding:14px;border-radius:8px;text-align:center"><div style="font-size:20px;margin-bottom:4px">🎄</div><div style="font-size:14px;font-weight:800;margin-bottom:2px">Special Season</div><div style="font-size:11px;opacity:.8">Limited-time offers for this season</div></div>`,
  spacer:`<div style="border:2px dashed #ddd;border-radius:6px;height:30px;display:flex;align-items:center;justify-content:center"><span style="font-size:11px;color:#bbb">↕ Spacer (24px)</span></div>`,
  divider:`<div style="display:flex;align-items:center;gap:8px"><div style="flex:1;height:1px;background:#ddd"></div><span style="font-size:10px;color:#ccc">DIVIDER</span><div style="flex:1;height:1px;background:#ddd"></div></div>`,
  productCustomizer:`<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;border:1.5px solid #eee;border-radius:8px;overflow:hidden"><div style="background:linear-gradient(135deg,#f0f0f0,#e0e0e0);min-height:130px;display:flex;align-items:center;justify-content:center;font-size:32px;position:relative"><span>🛍️</span><span style="position:absolute;top:7px;left:7px;background:#e85d26;color:#fff;font-size:8px;font-weight:700;padding:2px 6px;border-radius:10px">15% OFF</span></div><div style="padding:12px;display:flex;flex-direction:column;gap:7px"><div style="display:flex;justify-content:space-between;align-items:center"><div style="height:9px;background:#222;border-radius:3px;width:75%"></div><div style="width:20px;height:20px;border-radius:50%;border:1.5px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:11px;color:#bbb">♡</div></div><div style="display:flex;gap:2px;font-size:11px;color:#f5a623">★★★★☆</div><div style="display:flex;align-items:baseline;gap:6px"><div style="height:14px;background:#e85d26;border-radius:3px;width:50%"></div><div style="height:8px;background:#ddd;border-radius:3px;width:28%;text-decoration:line-through"></div></div><div style="background:#f0fdf4;border-radius:4px;height:7px;width:80%"></div><div style="display:flex;gap:4px">${['#222','#c0392b','#f59e0b'].map(c=>`<div style="width:16px;height:16px;border-radius:50%;background:${c};border:1.5px solid rgba(0,0,0,.15)"></div>`).join('')}</div><div style="height:26px;background:#111;border-radius:5px;display:flex;align-items:center;justify-content:center"><span style="font-size:9px;color:#fff;font-weight:700">🛒 Add to Cart</span></div><div style="border:1px dashed #ddd;border-radius:4px;height:18px;display:flex;align-items:center;padding:0 6px;gap:4px"><div style="flex:1;height:5px;background:#eee;border-radius:2px"></div><div style="width:28px;height:12px;background:#111;border-radius:3px"></div></div></div></div>`,
};

// ── WIDGET PICKER LOGIC ───────────────────────────────────────────
let _lpCurrentType = null;

function openPicker(){
  document.getElementById('typePicker').classList.add('open');
}
function closePicker(){
  document.getElementById('typePicker').classList.remove('open');
  _lpCurrentType = null;
  document.querySelectorAll('.wp-btn').forEach(b=>b.classList.remove('wp-active'));
}
function lpShowPreview(type){
  if(_lpCurrentType===type) return;
  _lpCurrentType = type;
  document.querySelectorAll('.wp-btn').forEach(b=>b.classList.toggle('wp-active', b.onmouseenter&&b.getAttribute('onmouseenter')==='lpShowPreview(\''+type+'\')'));

  const info = WIDGET_INFO[type] || { title:type, desc:'No description available.', tags:[] };
  const mockup = WIDGET_MOCKUPS[type] || `<div style="text-align:center;padding:20px;color:#999;font-size:12px">No visual preview available.</div>`;

  document.getElementById('wp-preview').innerHTML = `
    <div class="wp-preview-title">${info.title}</div>
    <div class="wp-preview-desc">${info.desc}</div>
    <div class="wp-preview-tags">${info.tags.map(t=>`<span class="wp-preview-tag">${t}</span>`).join('')}</div>
    <div class="wp-preview-mockup">${mockup}</div>`;

  const bottom = document.getElementById('wp-preview-bottom');
  bottom.style.display = 'flex';
  document.getElementById('wp-add-btn').onclick = () => lpAddSection(type);
}
function lpAddSection(type){
  sections.push({ ...(DEFAULTS[type] || { layout: type }) });
  closePicker();
  renderAll();
  setTimeout(()=>{
    const bodies = document.querySelectorAll('.tl-body');
    if(bodies.length) bodies[bodies.length-1].classList.add('open');
    const cards = document.getElementById('lpSectionList').querySelectorAll('.tl-card');
    if(cards.length) cards[cards.length-1].scrollIntoView({behavior:'smooth',block:'nearest'});
  },50);
}

// ── SAVE ──────────────────────────────────────────────────────────
async function lpSave(){
  const btn = document.getElementById('lpSaveBtn');
  const status = document.getElementById('lp-status');
  btn.disabled = true; btn.textContent = 'Saving…';
  status.textContent = ''; status.style.color = 'var(--muted)';
  try {
    const payload = sections.map(s=>{ const c=Object.assign({},s); delete c._productNames; return c; });
    const res = await fetch(SAVE_URL, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
      body:JSON.stringify({ lang:LANG, payload:JSON.stringify(payload) })
    });
    const data = await res.json();
    if(data.success){
      status.textContent = '✓ Saved! Reloading preview…';
      status.style.color = 'var(--green)';
      setTimeout(reloadIframe, 600);
    } else {
      status.textContent = data.error || 'Error saving.';
      status.style.color = 'var(--red)';
    }
  } catch(e) {
    status.textContent = 'Network error: '+e.message;
    status.style.color = 'var(--red)';
  }
  btn.disabled = false; btn.textContent = '💾 Save & Reload';
  setTimeout(()=>{ status.textContent=''; },6000);
}

// ── IFRAME CONTROL ────────────────────────────────────────────────
let _soloIdx = null;
const BASE_PREVIEW_URL = '{{ url('/') }}?tl_preview=1';

function reloadIframe(){
  const fr = document.getElementById('lpIframe');
  fr.src = _soloIdx !== null
    ? BASE_PREVIEW_URL + '&tl_solo=' + _soloIdx
    : BASE_PREVIEW_URL;
}

function viewWidget(idx){
  _soloIdx = idx;
  const meta = TYPE_META[sections[idx].layout] || { icon:'❓', label: sections[idx].layout };
  const name = sections[idx].name || sections[idx].headerText || sections[idx].title || meta.label;
  const url = BASE_PREVIEW_URL + '&tl_solo=' + idx;

  // update iframe
  document.getElementById('lpIframe').src = url;

  // update open button
  document.getElementById('lp-open-btn').href = url;

  // show solo bar
  document.getElementById('lp-solo-label').textContent =
    meta.icon + '  Viewing: ' + name + ' (' + meta.label + ')';
  document.getElementById('lp-solo-bar').classList.add('active');

  // highlight the active card
  document.querySelectorAll('.tl-card').forEach((c, i) => {
    c.style.outline = i === idx ? '2px solid #818cf8' : 'none';
  });

  // open editor for that card
  document.querySelectorAll('.tl-body').forEach(b => b.classList.remove('open'));
  const body = document.getElementById('body-' + idx);
  if(body){
    body.classList.add('open');
    body.closest('.tl-card').scrollIntoView({behavior:'smooth', block:'nearest'});
  }
}

function viewFull(){
  _soloIdx = null;
  const url = BASE_PREVIEW_URL;
  document.getElementById('lpIframe').src = url;
  document.getElementById('lp-open-btn').href = url;
  document.getElementById('lp-solo-bar').classList.remove('active');
  document.querySelectorAll('.tl-card').forEach(c => { c.style.outline = 'none'; });
}

function switchLang(lang){
  window.location.href = '/admin/live-preview?lang='+lang;
}

// ── POSTMESSAGE: when iframe section is clicked → open that card ──
window.addEventListener('message', function(e){
  if(!e.data) return;
  if(e.data.type === 'tlSectionClick'){
    const si = e.data.si;
    // close all bodies, open the clicked one
    document.querySelectorAll('.tl-body').forEach(b=>b.classList.remove('open'));
    const body = document.getElementById('body-'+si);
    if(body){
      body.classList.add('open');
      const card = body.closest('.tl-card');
      if(card) card.scrollIntoView({behavior:'smooth',block:'nearest'});
    }
    // highlight in iframe
    document.getElementById('lpIframe').contentWindow.postMessage({type:'tlHighlight',si:si},'*');
  }
});

// ── FLASH TARGETING ───────────────────────────────────────────────
function renderFlashTargeting(idx){
  const sec=sections[idx];
  const el=document.getElementById('flash-targeting-'+idx);
  if(!el) return;
  const applyTo=sec.applyTo||'all';
  if(applyTo==='all'){ el.innerHTML='<p style="font-size:12px;color:var(--muted);margin:0">Discount applies to every product in the store.</p>'; return; }
  if(applyTo==='categories'){
    const selected=Array.isArray(sec.targetCategories)?sec.targetCategories.map(Number):[];
    let html='<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:7px">';
    CATEGORIES.forEach(cat=>{
      const checked=selected.includes(Number(cat.id))?'checked':'';
      html+=`<label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:5px;padding:4px 8px"><input type="checkbox" value="${cat.id}" ${checked} onchange="flashToggleCategory(${idx},${cat.id},this.checked)" style="width:13px;height:13px"> ${escHtml(cat.name)}</label>`;
    });
    html+='</div>';
    if(selected.length===0) html+='<p style="font-size:11px;color:#f59e0b;margin:0">⚠ No categories selected.</p>';
    el.innerHTML=html; return;
  }
  if(applyTo==='products'){
    const selected=Array.isArray(sec.targetProductIds)?sec.targetProductIds.map(Number):[];
    const chips=selected.map(id=>{
      const name=(sec._productNames||{})[id]||('Product #'+id);
      return `<span style="display:inline-flex;align-items:center;gap:3px;background:var(--accent-dim);border:1px solid rgba(232,93,38,.3);color:var(--accent);border-radius:16px;padding:2px 9px;font-size:11px">${escHtml(name)}<button onclick="flashRemoveProduct(${idx},${id})" style="background:none;border:none;color:var(--accent);cursor:pointer;font-size:13px;line-height:1;padding:0">×</button></span>`;
    }).join('');
    el.innerHTML=`<div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px">${chips||'<span style="font-size:11px;color:var(--muted)">No products selected yet.</span>'}</div>
    <input type="text" id="flash-psearch-${idx}" placeholder="Search products by name…" style="width:100%;margin-bottom:5px;font-size:12px" oninput="flashSearchProducts(${idx},this.value)">
    <div id="flash-presults-${idx}" style="max-height:160px;overflow-y:auto;border:1px solid rgba(255,255,255,.1);border-radius:7px;display:none"></div>
    ${selected.length===0?'<p style="font-size:11px;color:#f59e0b;margin-top:6px">⚠ No products selected.</p>':''}`;
  }
}
function flashToggleCategory(idx,catId,checked){
  if(!sections[idx].targetCategories) sections[idx].targetCategories=[];
  catId=Number(catId);
  if(checked){ if(!sections[idx].targetCategories.includes(catId)) sections[idx].targetCategories.push(catId); }
  else{ sections[idx].targetCategories=sections[idx].targetCategories.filter(c=>c!==catId); }
  renderFlashTargeting(idx);
}
function flashRemoveProduct(idx,prodId){
  prodId=Number(prodId);
  if(!sections[idx].targetProductIds) sections[idx].targetProductIds=[];
  sections[idx].targetProductIds=sections[idx].targetProductIds.filter(p=>p!==prodId);
  if(sections[idx]._productNames) delete sections[idx]._productNames[prodId];
  renderFlashTargeting(idx);
}
let _flashTimer=null;
function flashSearchProducts(idx,q){
  clearTimeout(_flashTimer);
  const el=document.getElementById('flash-presults-'+idx);
  if(!el) return;
  if(q.trim().length<1){ el.style.display='none'; return; }
  _flashTimer=setTimeout(async()=>{
    el.style.display='block';
    el.innerHTML='<div style="padding:8px;font-size:11px;color:var(--muted)">Searching…</div>';
    try{
      const resp=await fetch(`/admin/products/search?q=${encodeURIComponent(q.trim())}`);
      const data=await resp.json();
      if(!data.length){ el.innerHTML='<div style="padding:8px;font-size:11px;color:var(--muted)">No products found.</div>'; return; }
      const selected=Array.isArray(sections[idx].targetProductIds)?sections[idx].targetProductIds.map(Number):[];
      el.innerHTML=data.map(p=>{
        const isSel=selected.includes(Number(p.id));
        return `<div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-bottom:1px solid rgba(255,255,255,.06);font-size:12px">
          <span>${escHtml(p.name)}</span>
          <button onclick="flashAddProduct(${idx},${p.id},'${escAttr(p.name)}')" style="background:${isSel?'rgba(34,197,94,.15)':'var(--accent-dim)'};border:1px solid ${isSel?'rgba(34,197,94,.3)':'rgba(232,93,38,.3)'};color:${isSel?'#22c55e':'var(--accent)'};border-radius:5px;padding:2px 9px;font-size:11px;cursor:pointer">${isSel?'✓ Added':'+ Add'}</button>
        </div>`;
      }).join('');
    }catch(e){ el.innerHTML='<div style="padding:8px;font-size:11px;color:#ef4444">Search failed.</div>'; }
  },300);
}
function flashAddProduct(idx,prodId,prodName){
  prodId=Number(prodId);
  if(!sections[idx].targetProductIds) sections[idx].targetProductIds=[];
  if(!sections[idx]._productNames) sections[idx]._productNames={};
  if(!sections[idx].targetProductIds.includes(prodId)){
    sections[idx].targetProductIds.push(prodId);
    sections[idx]._productNames[prodId]=prodName;
  }
  renderFlashTargeting(idx);
  const inp=document.getElementById('flash-psearch-'+idx);
  if(inp) flashSearchProducts(idx,inp.value);
}

// ── WIDGET FILTER / SEARCH ────────────────────────────────────────
function filterWidgets(q){
  const term = q.trim().toLowerCase();
  const cards = document.querySelectorAll('#lpSectionList .tl-card');
  let visible = 0;
  cards.forEach((card, i) => {
    const sec = sections[i];
    if(!sec){ card.style.display=''; return; }
    const meta = TYPE_META[sec.layout] || { label: sec.layout };
    const name = (sec.name || sec.headerText || sec.title || meta.label || '').toLowerCase();
    const type = (meta.label || sec.layout || '').toLowerCase();
    const match = !term || name.includes(term) || type.includes(term);
    card.style.display = match ? '' : 'none';
    if(match) visible++;
  });
  const countEl = document.getElementById('lp-filter-count');
  if(term){
    countEl.style.display = 'block';
    countEl.textContent = visible + ' of ' + cards.length + ' widgets match';
  } else {
    countEl.style.display = 'none';
  }
}

// ── CLOSE PICKER ON BACKDROP ──────────────────────────────────────
document.getElementById('typePicker').addEventListener('click',function(e){
  if(e.target===this) closePicker();
});

// ── KEYBOARD SHORTCUT: Ctrl+S / Cmd+S saves ───────────────────────
document.addEventListener('keydown',function(e){
  if((e.ctrlKey||e.metaKey)&&e.key==='s'){ e.preventDefault(); lpSave(); }
});

// ── INIT ─────────────────────────────────────────────────────────
renderAll();
</script>
</body>
</html>
