@extends('admin.layout')
@section('title', 'Homepage Timeline')
@section('page-title', 'Homepage Timeline Builder')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
.tl-lang-tabs { display:flex;gap:8px;margin-bottom:24px }
.tl-lang-tab { padding:6px 18px;border-radius:6px;border:1px solid rgba(255,255,255,.12);background:transparent;color:var(--muted);cursor:pointer;font-size:13px;font-weight:600;transition:.15s }
.tl-lang-tab.active { background:var(--accent);border-color:var(--accent);color:#fff }
.tl-section-list { display:flex;flex-direction:column;gap:10px;min-height:40px }
.tl-card { background:var(--bg-card);border:1px solid rgba(255,255,255,.08);border-radius:10px;overflow:hidden;transition:box-shadow .15s }
.tl-card.sortable-ghost { opacity:.4 }
.tl-card-header { display:flex;align-items:center;gap:10px;padding:12px 16px;cursor:pointer;user-select:none }
.tl-drag-handle { color:var(--muted);cursor:grab;font-size:18px;flex-shrink:0;padding:0 4px }
.tl-drag-handle:active { cursor:grabbing }
.tl-type-badge { font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(232,93,38,.15);color:var(--accent);flex-shrink:0;letter-spacing:.5px;text-transform:uppercase }
.tl-section-name { font-weight:600;font-size:14px;flex:1 }
.tl-section-desc { font-size:12px;color:var(--muted);flex:1;margin-top:2px }
.tl-card-actions { display:flex;gap:6px;flex-shrink:0 }
.tl-body { display:none;padding:16px;border-top:1px solid rgba(255,255,255,.07);background:rgba(0,0,0,.15) }
.tl-body.open { display:block }
.form-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:12px }
.form-grid .form-group { margin:0 }
.items-list { display:flex;flex-direction:column;gap:8px;margin-top:8px }
.item-row { display:flex;align-items:center;gap:8px;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;border:1px solid rgba(255,255,255,.06) }
.item-row input, .item-row select { flex:1;min-width:80px }
.add-item-btn { display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:rgba(255,255,255,.06);border:1px dashed rgba(255,255,255,.2);border-radius:6px;color:var(--muted);font-size:12px;cursor:pointer;margin-top:8px;transition:.15s }
.add-item-btn:hover { background:rgba(255,255,255,.1);color:#fff }
.tl-add-section { display:flex;align-items:center;gap:10px;padding:14px 18px;border:2px dashed rgba(255,255,255,.12);border-radius:10px;cursor:pointer;transition:.15s;background:transparent;width:100% }
.tl-add-section:hover { border-color:var(--accent);background:rgba(232,93,38,.05) }
.tl-type-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-top:12px }
.tl-type-btn { padding:12px 8px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);text-align:center;cursor:pointer;transition:.15s;color:#fff }
.tl-type-btn:hover,.tl-type-btn.wp-active { border-color:var(--accent);background:rgba(232,93,38,.12) }
.tl-type-btn .icon { font-size:22px;margin-bottom:6px }
.tl-type-btn .lbl { font-size:12px;font-weight:600;color:var(--muted) }

/* ── WIDGET PICKER PANEL ── */
.wp-panel { display:flex;gap:0;margin-top:8px;background:var(--bg-card);border:1px solid rgba(255,255,255,.1);border-radius:12px;overflow:hidden;min-height:480px }
.wp-left { width:210px;flex-shrink:0;border-right:1px solid rgba(255,255,255,.08);overflow-y:auto;max-height:520px;padding:10px 8px }
.wp-left::-webkit-scrollbar { width:4px }
.wp-left::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1);border-radius:4px }
.wp-group-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:8px 6px 4px;margin-top:4px }
.wp-group-label:first-child { margin-top:0 }
.wp-btn { display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:7px;cursor:pointer;transition:.12s;color:#fff;border:1px solid transparent }
.wp-btn:hover,.wp-btn.active { background:rgba(232,93,38,.12);border-color:rgba(232,93,38,.3);color:#fff }
.wp-btn .wp-ico { font-size:18px;flex-shrink:0;width:24px;text-align:center }
.wp-btn .wp-name { font-size:12px;font-weight:600;color:var(--muted) }
.wp-btn:hover .wp-name,.wp-btn.active .wp-name { color:#fff }
.wp-right { flex:1;display:flex;flex-direction:column;overflow:hidden }
.wp-preview-top { flex:1;padding:20px 22px;overflow-y:auto;background:rgba(0,0,0,.1) }
.wp-preview-top::-webkit-scrollbar { width:4px }
.wp-preview-top::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1);border-radius:4px }
.wp-preview-mockup { background:#f8f8f8;border-radius:10px;padding:16px;margin-bottom:16px;min-height:100px;overflow:hidden }
.wp-preview-title { font-size:16px;font-weight:800;color:#fff;margin-bottom:5px }
.wp-preview-desc { font-size:13px;color:var(--muted);line-height:1.55;margin-bottom:12px }
.wp-preview-tags { display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px }
.wp-preview-tag { font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.07);color:var(--muted) }
.wp-preview-bottom { padding:14px 20px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px;background:rgba(0,0,0,.15) }
.wp-add-btn { flex:1;padding:11px;border-radius:8px;background:var(--accent);color:#fff;border:none;font-size:14px;font-weight:700;cursor:pointer;transition:.15s }
.wp-add-btn:hover { background:#c94d1a }
.wp-empty { display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:12px;color:var(--muted) }
.wp-empty-icon { font-size:40px;opacity:.4 }
.wp-empty-txt { font-size:13px }
.save-bar { position:sticky;bottom:0;background:var(--bg-sidebar);border-top:1px solid rgba(255,255,255,.08);padding:14px 0;margin-top:24px;display:flex;align-items:center;gap:12px;z-index:10 }
.save-status { font-size:13px;color:var(--green) }
</style>
@endpush

@section('content')

{{-- Language Tabs --}}
<div class="tl-lang-tabs">
  @foreach($langs as $l)
  <a href="{{ route('admin.timeline', ['lang' => $l]) }}" class="tl-lang-tab {{ $lang === $l ? 'active' : '' }}">
    {{ strtoupper($l) }}
  </a>
  @endforeach
  <span style="margin-left:4px;font-size:12px;color:var(--muted);align-self:center">Editing <strong>{{ strtoupper($lang) }}</strong> layout</span>
</div>

<div class="card" style="margin-bottom:16px">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <div>
      <div style="font-weight:700;font-size:15px">Homepage Sections</div>
      <div style="font-size:12px;color:var(--muted);margin-top:3px">Drag to reorder · Click a section to edit · Changes only saved when you press Save</div>
    </div>
    <div style="display:flex;gap:8px">
      <a href="{{ url('/') }}?tl_preview=1" target="_blank" class="btn btn-ghost btn-sm" style="opacity:.7">👁 Quick Preview</a>
      <a href="{{ route('admin.live.preview') }}" target="_blank" class="btn btn-ghost btn-sm" style="background:rgba(232,93,38,.15);border-color:rgba(232,93,38,.35);color:#e85d26">✏️ Live Editor</a>
    </div>
  </div>
</div>

<div id="sectionList" class="tl-section-list">
  {{-- Populated by JS --}}
</div>

<div style="margin-top:10px">
  <button type="button" class="tl-add-section" id="addSectionBtn">
    <span style="font-size:20px">＋</span>
    <span style="font-weight:600;font-size:14px">Add Section</span>
    <span style="font-size:12px;color:var(--muted);margin-left:4px">Choose from available section types</span>
  </button>
</div>

{{-- Add Section Picker (hidden) --}}
<div id="typePicker" style="display:none;margin-top:8px">
  <div class="wp-panel">

    {{-- LEFT: scrollable widget list --}}
    <div class="wp-left">

      <div class="wp-group-label">Content</div>
      <div class="wp-btn" onmouseenter="showPreview('bannerImage')" onclick="addSection('bannerImage')"><span class="wp-ico">🖼️</span><span class="wp-name">Banner / Slider</span></div>
      <div class="wp-btn" onmouseenter="showPreview('category')" onclick="addSection('category')"><span class="wp-ico">📂</span><span class="wp-name">Categories Strip</span></div>
      <div class="wp-btn" onmouseenter="showPreview('twoColumn')" onclick="addSection('twoColumn')"><span class="wp-ico">🛍️</span><span class="wp-name">Products Grid</span></div>
      <div class="wp-btn" onmouseenter="showPreview('saleImages')" onclick="addSection('saleImages')"><span class="wp-ico">🏷️</span><span class="wp-name">Products Scroll</span></div>
      <div class="wp-btn" onmouseenter="showPreview('seupermarketstars')" onclick="addSection('seupermarketstars')"><span class="wp-ico">⭐</span><span class="wp-name">Featured Items</span></div>
      <div class="wp-btn" onmouseenter="showPreview('topVendors')" onclick="addSection('topVendors')"><span class="wp-ico">🏪</span><span class="wp-name">Top Vendors</span></div>
      <div class="wp-btn" onmouseenter="showPreview('brands')" onclick="addSection('brands')"><span class="wp-ico">🏷️</span><span class="wp-name">Brands</span></div>

      <div class="wp-group-label">Widgets</div>
      <div class="wp-btn" onmouseenter="showPreview('coupons')" onclick="addSection('coupons')"><span class="wp-ico">🎟️</span><span class="wp-name">Coupons Strip</span></div>
      <div class="wp-btn" onmouseenter="showPreview('statsBar')" onclick="addSection('statsBar')"><span class="wp-ico">📊</span><span class="wp-name">Stats Bar</span></div>
      <div class="wp-btn" onmouseenter="showPreview('promoBlock')" onclick="addSection('promoBlock')"><span class="wp-ico">📣</span><span class="wp-name">Promo Block</span></div>
      <div class="wp-btn" onmouseenter="showPreview('testimonials')" onclick="addSection('testimonials')"><span class="wp-ico">💬</span><span class="wp-name">Testimonials</span></div>
      <div class="wp-btn" onmouseenter="showPreview('reviewsCarousel')" onclick="addSection('reviewsCarousel')"><span class="wp-ico">🌟</span><span class="wp-name">Reviews Carousel</span></div>
      <div class="wp-btn" onmouseenter="showPreview('newsletter')" onclick="addSection('newsletter')"><span class="wp-ico">📧</span><span class="wp-name">Newsletter</span></div>
      <div class="wp-btn" onmouseenter="showPreview('bundle')" onclick="addSection('bundle')"><span class="wp-ico">🎁</span><span class="wp-name">Bundle Deal</span></div>
      <div class="wp-btn" onmouseenter="showPreview('loyalty')" onclick="addSection('loyalty')"><span class="wp-ico">⭐</span><span class="wp-name">Loyalty Points</span></div>
      <div class="wp-btn" onmouseenter="showPreview('activity')" onclick="addSection('activity')"><span class="wp-ico">🔴</span><span class="wp-name">Live Activity</span></div>
      <div class="wp-btn" onmouseenter="showPreview('referral')" onclick="addSection('referral')"><span class="wp-ico">🤝</span><span class="wp-name">Referral Widget</span></div>
      <div class="wp-btn" onmouseenter="showPreview('recent')" onclick="addSection('recent')"><span class="wp-ico">🕐</span><span class="wp-name">Recently Viewed</span></div>
      <div class="wp-btn" onmouseenter="showPreview('recommended')" onclick="addSection('recommended')"><span class="wp-ico">💡</span><span class="wp-name">Recommended</span></div>
      <div class="wp-btn" onmouseenter="showPreview('complete')" onclick="addSection('complete')"><span class="wp-ico">👗</span><span class="wp-name">Complete the Look</span></div>

      <div class="wp-group-label">Products</div>
      <div class="wp-btn" onmouseenter="showPreview('trending')" onclick="addSection('trending')"><span class="wp-ico">🔥</span><span class="wp-name">Trending Now</span></div>
      <div class="wp-btn" onmouseenter="showPreview('arrivals')" onclick="addSection('arrivals')"><span class="wp-ico">✨</span><span class="wp-name">New Arrivals</span></div>
      <div class="wp-btn" onmouseenter="showPreview('brandLogos')" onclick="addSection('brandLogos')"><span class="wp-ico">🏷️</span><span class="wp-name">Brand Logos Row</span></div>

      <div class="wp-group-label">Full-width</div>
      <div class="wp-btn" onmouseenter="showPreview('announcement')" onclick="addSection('announcement')"><span class="wp-ico">📢</span><span class="wp-name">Announcement Bar</span></div>
      <div class="wp-btn" onmouseenter="showPreview('flash')" onclick="addSection('flash')"><span class="wp-ico">⚡</span><span class="wp-name">Flash Sale Timer</span></div>
      <div class="wp-btn" onmouseenter="showPreview('seasonal')" onclick="addSection('seasonal')"><span class="wp-ico">🎄</span><span class="wp-name">Seasonal Banner</span></div>

      <div class="wp-group-label">Layout</div>
      <div class="wp-btn" onmouseenter="showPreview('spacer')" onclick="addSection('spacer')"><span class="wp-ico">↕️</span><span class="wp-name">Spacer</span></div>
      <div class="wp-btn" onmouseenter="showPreview('divider')" onclick="addSection('divider')"><span class="wp-ico">➖</span><span class="wp-name">Divider</span></div>

    </div>{{-- /wp-left --}}

    {{-- RIGHT: preview panel --}}
    <div class="wp-right">
      <div class="wp-preview-top" id="wpPreviewTop">
        <div class="wp-empty">
          <div class="wp-empty-icon">👆</div>
          <div class="wp-empty-txt">Hover a widget on the left to preview it</div>
        </div>
      </div>
      <div class="wp-preview-bottom" id="wpPreviewBottom" style="display:none">
        <button class="wp-add-btn" id="wpAddBtn" onclick="">Add This Widget</button>
        <button style="padding:11px 16px;border-radius:8px;background:transparent;border:1px solid rgba(255,255,255,.15);color:var(--muted);font-size:13px;cursor:pointer" onclick="document.getElementById('typePicker').style.display='none'">Cancel</button>
      </div>
    </div>{{-- /wp-right --}}

  </div>{{-- /wp-panel --}}
</div>

<div class="save-bar">
  <button class="btn btn-primary" onclick="saveTimeline()" id="saveBtn">Save Timeline</button>
  <span class="save-status" id="saveStatus"></span>
</div>

{{-- Data passed to JS --}}
<script>
const CATEGORIES = @json($categories);
const LANG = '{{ $lang }}';
const SAVE_URL = '{{ route('admin.timeline.save') }}';
const CSRF = '{{ csrf_token() }}';

// Initial sections from PHP
let sections = @json($sections);

// ── TYPE META ──────────────────────────────────────────────────────
const TYPE_META = {
  logo:             { icon:'🏪', label:'Logo / Header Bar', color:'#3b82f6' },
  category:         { icon:'📂', label:'Categories Strip',   color:'#8b5cf6' },
  bannerImage:      { icon:'🖼️', label:'Banner / Slider',    color:'#e85d26' },
  twoColumn:        { icon:'🛍️', label:'Products Grid',      color:'#22c55e' },
  saleImages:       { icon:'🏷️', label:'Products Scroll',    color:'#f59e0b' },
  seupermarketstars:{ icon:'⭐', label:'Featured Items',      color:'#ec4899' },
  topVendors:       { icon:'🏪', label:'Top Vendors',         color:'#f97316' },
  brands:           { icon:'🏷️', label:'Brands',             color:'#06b6d4' },
  coupons:          { icon:'🎟️', label:'Coupons Strip',        color:'#f59e0b' },
  trending:         { icon:'🔥', label:'Trending Now',         color:'#ef4444' },
  arrivals:         { icon:'✨', label:'New Arrivals Ticker',  color:'#8b5cf6' },
  brandLogos:       { icon:'🏷️', label:'Brand Logos Row',      color:'#0ea5e9' },
  reviewsCarousel:  { icon:'🌟', label:'Reviews Carousel',     color:'#f59e0b' },
  activity:         { icon:'🔴', label:'Live Activity Banner', color:'#ef4444' },
  recent:           { icon:'🕐', label:'Recently Viewed',      color:'#6366f1' },
  bundle:           { icon:'🎁', label:'Bundle Deal',          color:'#22c55e' },
  loyalty:          { icon:'⭐', label:'Loyalty Points',       color:'#f59e0b' },
  seasonal:         { icon:'🎄', label:'Seasonal Banner',      color:'#22c55e' },
  referral:         { icon:'🤝', label:'Referral Widget',      color:'#0ea5e9' },
  complete:         { icon:'👗', label:'Complete the Look',    color:'#ec4899' },
  recommended:      { icon:'💡', label:'Recommended For You',  color:'#8b5cf6' },
  announcement:     { icon:'📢', label:'Announcement Bar',     color:'#111111' },
  flash:            { icon:'⚡', label:'Flash Sale Timer',     color:'#ef4444' },
  statsBar:         { icon:'📊', label:'Stats Bar',           color:'#6366f1' },
  promoBlock:       { icon:'📣', label:'Promo Block',         color:'#e85d26' },
  testimonials:     { icon:'💬', label:'Testimonials',        color:'#22c55e' },
  newsletter:       { icon:'📧', label:'Newsletter Signup',   color:'#06b6d4' },
  spacer:           { icon:'↕️', label:'Spacer',             color:'#6b7280' },
  divider:          { icon:'➖', label:'Divider',            color:'#6b7280' },
};

// ── CATEGORY OPTIONS HTML ──────────────────────────────────────────
function catOptions(selected) {
  return CATEGORIES.map(c =>
    `<option value="${c.id}" ${c.id == selected ? 'selected' : ''}>${c.name}</option>`
  ).join('');
}

// ── BUILD SECTION CARD HTML ────────────────────────────────────────
function buildCard(sec, idx) {
  const type = sec.layout || 'unknown';
  const meta = TYPE_META[type] || { icon:'❓', label: type, color:'#6b7280' };
  const name = sec.name || sec.headerText || meta.label;
  const hidden = sec.hidden === true;

  return `
  <div class="tl-card" data-idx="${idx}" style="${hidden ? 'opacity:.45;border-style:dashed' : ''}">
    <div class="tl-card-header" onclick="toggleBody(${idx})">
      <span class="tl-drag-handle">⠿</span>
      <span style="font-size:18px">${hidden ? '🚫' : meta.icon}</span>
      <div style="flex:1;min-width:0">
        <div class="tl-section-name" style="${hidden ? 'text-decoration:line-through;color:var(--muted)' : ''}">${escHtml(name)}</div>
        <div class="tl-section-desc" style="color:${hidden ? 'var(--muted)' : meta.color}">${hidden ? 'Hidden — not shown on homepage' : meta.label}</div>
      </div>
      <div class="tl-card-actions" onclick="event.stopPropagation()">
        <button class="btn btn-sm" style="background:${hidden ? 'rgba(34,197,94,.15)' : 'rgba(255,255,255,.07)'};border:1px solid ${hidden ? 'rgba(34,197,94,.3)' : 'rgba(255,255,255,.12)'};color:${hidden ? '#22c55e' : 'var(--muted)'}" onclick="toggleHidden(${idx})" title="${hidden ? 'Show widget' : 'Hide widget'}">${hidden ? '👁 Show' : '👁 Hide'}</button>
        <button class="btn btn-danger btn-sm" onclick="removeSection(${idx})">Remove</button>
      </div>
      <span style="color:var(--muted);font-size:12px;margin-left:4px">▼</span>
    </div>
    <div class="tl-body" id="body-${idx}">
      ${buildEditor(sec, idx)}
    </div>
  </div>`;
}

// ── BUILD EDITOR FOR A SECTION ─────────────────────────────────────
function buildEditor(sec, idx) {
  const type = sec.layout;
  let html = '';

  if (type === 'logo') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Show Logo</label><select onchange="updateField(${idx},'showLogo',this.value==='true')"><option value="true" ${sec.showLogo!==false?'selected':''}>Yes</option><option value="false" ${sec.showLogo===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Show Search</label><select onchange="updateField(${idx},'showSearch',this.value==='true')"><option value="true" ${sec.showSearch!==false?'selected':''}>Yes</option><option value="false" ${sec.showSearch===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Show Menu</label><select onchange="updateField(${idx},'showMenu',this.value==='true')"><option value="true" ${sec.showMenu!==false?'selected':''}>Yes</option><option value="false" ${sec.showMenu===false?'selected':''}>No</option></select></div>
    </div>`;
  }

  else if (type === 'category') {
    html = `<div style="font-size:12px;color:var(--muted);margin-bottom:8px">Category items shown as icon strip on homepage.</div>`;
    html += `<div class="items-list" id="catItems-${idx}">` + (sec.items||[]).map((item, ii) => buildCatItem(idx, ii, item)).join('') + `</div>`;
    html += `<button class="add-item-btn" onclick="addCatItem(${idx})">+ Add Category Item</button>`;
  }

  else if (type === 'bannerImage') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Style</label><select onchange="updateField(${idx},'design',this.value)">
        <option value="default" ${(sec.design||'default')==='default'?'selected':''}>Slider</option>
        <option value="static" ${sec.design==='static'?'selected':''}>Static Image</option>
      </select></div>
      <div class="form-group"><label>Auto Play</label><select onchange="updateField(${idx},'autoPlay',this.value==='true')"><option value="true" ${sec.autoPlay!==false?'selected':''}>Yes</option><option value="false" ${sec.autoPlay===false?'selected':''}>No</option></select></div>
      <div class="form-group"><label>Border Radius</label><input type="number" value="${sec.radius||2}" min="0" max="30" onchange="updateField(${idx},'radius',parseFloat(this.value)||0)" style="width:100%"></div>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Banner images (URL + optional category or product link):</div>
    <div class="items-list" id="bannerItems-${idx}">` + (sec.items||[]).map((item, ii) => buildBannerItem(idx, ii, item)).join('') + `</div>
    <button class="add-item-btn" onclick="addBannerItem(${idx})">+ Add Banner Image</button>`;
  }

  else if (type === 'twoColumn' || type === 'saleImages' || type === 'seupermarketstars') {
    const label = type === 'twoColumn' ? 'Products Grid (2 columns)' : type === 'saleImages' ? 'Horizontal Product Scroll' : 'Featured Items';
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||sec.name||'')}" onchange="updateField(${idx},'headerText',this.value)" placeholder="e.g. On Sale Today ⚡" style="width:100%"></div>
      <div class="form-group"><label>Category</label><select onchange="updateField(${idx},'category',parseInt(this.value))"><option value="">All Products</option>${catOptions(sec.category)}</select></div>
      <div class="form-group"><label>Max Items</label><input type="number" value="${sec.maxItemsToShow||8}" min="1" max="20" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))" style="width:100%"></div>
    </div>`;
  }

  else if (type === 'topVendors') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Top Sellers')}" onchange="updateField(${idx},'headerText',this.value)" placeholder="e.g. Top Sellers" style="width:100%"></div>
      <div class="form-group"><label>Max Vendors</label><input type="number" value="${sec.maxItemsToShow||6}" min="1" max="20" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))" style="width:100%"></div>
      <div class="form-group"><label>Sort By</label><select onchange="updateField(${idx},'sortBy',this.value)">
        <option value="products" ${(sec.sortBy||'products')==='products'?'selected':''}>Most Products</option>
        <option value="rating" ${sec.sortBy==='rating'?'selected':''}>Highest Rated</option>
        <option value="newest" ${sec.sortBy==='newest'?'selected':''}>Newest</option>
      </select></div>
    </div>
    <div style="font-size:12px;color:var(--muted)">Shows approved vendors as a horizontal scroll strip. Vendor logos and names are pulled automatically from the database.</div>`;
  }

  else if (type === 'brands') {
    html = `<div style="font-size:12px;color:var(--muted)">Displays all brands from the database. No extra configuration needed.</div>`;
  }

  else if (type === 'spacer') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Height (px)</label><input type="number" value="${sec.height||24}" min="4" max="200" onchange="updateField(${idx},'height',parseInt(this.value))" style="width:120px"></div>
    </div>`;
  }

  else if (type === 'divider') {
    html = `<div style="font-size:12px;color:var(--muted)">Shows a horizontal divider line. No configuration needed.</div>`;
  }

  else if (type === 'coupons') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'This Week\'s Deals')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="This Week's Deals"></div>
      <div class="form-group"><label>Sub-label (right side)</label><input type="text" value="${escAttr(sec.subLabel||'Use code at checkout')}" style="width:100%" onchange="updateField(${idx},'subLabel',this.value)" placeholder="Use code at checkout"></div>
      <div class="form-group"><label>Max Coupons to Show</label><input type="number" value="${sec.maxItemsToShow||6}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Sort By</label><select onchange="updateField(${idx},'sortBy',this.value)">
        <option value="amount" ${(sec.sortBy||'amount')==='amount'?'selected':''}>Discount Amount (highest first)</option>
        <option value="newest" ${sec.sortBy==='newest'?'selected':''}>Newest First</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer">
        <input type="checkbox" ${(sec.showExpiredFallback!==false)?'checked':''} onchange="updateField(${idx},'showExpiredFallback',this.checked)" style="width:16px;height:16px">
        Show expired coupons as fallback (when no active ones exist)
      </label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer">
        <input type="checkbox" ${(sec.hideWhenEmpty!==false)?'checked':''} onchange="updateField(${idx},'hideWhenEmpty',this.checked)" style="width:16px;height:16px">
        Hide section completely when no coupons are available
      </label>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:10px">Displays active published coupons from your store. Coupon cards are managed in the Coupons section of the admin panel.</div>`;
  }

  else if (type === 'statsBar') {
    const statKeys = ['products','vendors','categories','brands','orders','reviews'];
    const items = sec.items || [
      {key:'products',label:'Products'},
      {key:'vendors',label:'Vendors'},
      {key:'categories',label:'Categories'},
      {key:'brands',label:'Brands'},
    ];
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Background Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.bgColor||'#111111'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'bgColor',this.value)">
        <input type="text" value="${escAttr(sec.bgColor||'#111111')}" style="flex:1" onchange="updateField(${idx},'bgColor',this.value)" placeholder="#111111">
      </div></div>
      <div class="form-group"><label>Text Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.textColor||'#ffffff'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'textColor',this.value)">
        <input type="text" value="${escAttr(sec.textColor||'#ffffff')}" style="flex:1" onchange="updateField(${idx},'textColor',this.value)" placeholder="#ffffff">
      </div></div>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Stats to display (live counts from database):</div>
    <div class="items-list" id="statsItems-${idx}">` +
      items.map((item, ii) => `<div class="item-row" id="statsItem-${idx}-${ii}">
        <select style="width:140px" onchange="updateStatsItem(${idx},${ii},'key',this.value)">
          ${statKeys.map(k => `<option value="${k}" ${item.key===k?'selected':''}>${k.charAt(0).toUpperCase()+k.slice(1)}</option>`).join('')}
        </select>
        <input type="text" value="${escAttr(item.label||'')}" placeholder="Label (e.g. Products)" style="flex:1" onchange="updateStatsItem(${idx},${ii},'label',this.value)">
        <button class="btn btn-danger btn-sm" onclick="removeStatsItem(${idx},${ii})">×</button>
      </div>`).join('') +
    `</div>
    <button class="add-item-btn" onclick="addStatsItem(${idx})">+ Add Stat</button>`;
  }

  else if (type === 'promoBlock') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Headline</label><input type="text" value="${escAttr(sec.headline||'Special Offer')}" style="width:100%" onchange="updateField(${idx},'headline',this.value)" placeholder="e.g. Summer Sale"></div>
      <div class="form-group"><label>Subtext</label><input type="text" value="${escAttr(sec.subtext||'')}" style="width:100%" onchange="updateField(${idx},'subtext',this.value)" placeholder="e.g. Up to 40% off"></div>
      <div class="form-group"><label>Button Text</label><input type="text" value="${escAttr(sec.btnText||'Shop Now')}" style="width:100%" onchange="updateField(${idx},'btnText',this.value)" placeholder="Shop Now"></div>
      <div class="form-group"><label>Button Link (URL)</label><input type="text" value="${escAttr(sec.btnLink||'/shop')}" style="width:100%" onchange="updateField(${idx},'btnLink',this.value)" placeholder="/shop or /shop?category=1"></div>
      <div class="form-group"><label>Side Image URL</label><input type="text" value="${escAttr(sec.image||'')}" style="width:100%" onchange="updateField(${idx},'image',this.value)" placeholder="https://... (optional)"></div>
      <div class="form-group"><label>Text Alignment</label><select onchange="updateField(${idx},'align',this.value)">
        <option value="center" ${(sec.align||'center')==='center'?'selected':''}>Center</option>
        <option value="left" ${sec.align==='left'?'selected':''}>Left</option>
        <option value="right" ${sec.align==='right'?'selected':''}>Right</option>
      </select></div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label>Background Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.bgColor||'#111111'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'bgColor',this.value)">
        <input type="text" value="${escAttr(sec.bgColor||'#111111')}" style="flex:1" onchange="updateField(${idx},'bgColor',this.value)">
      </div></div>
      <div class="form-group"><label>Text Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.textColor||'#ffffff'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'textColor',this.value)">
        <input type="text" value="${escAttr(sec.textColor||'#ffffff')}" style="flex:1" onchange="updateField(${idx},'textColor',this.value)">
      </div></div>
      <div class="form-group"><label>Button Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.btnColor||'#e85d26'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'btnColor',this.value)">
        <input type="text" value="${escAttr(sec.btnColor||'#e85d26')}" style="flex:1" onchange="updateField(${idx},'btnColor',this.value)">
      </div></div>
    </div>`;
  }

  else if (type === 'testimonials') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'What Our Customers Say')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="What Our Customers Say"></div>
      <div class="form-group"><label>Max Reviews</label><input type="number" value="${sec.maxItemsToShow||4}" min="1" max="12" style="width:100%" onchange="updateField(${idx},'maxItemsToShow',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Star Rating</label><select onchange="updateField(${idx},'minRating',parseInt(this.value))">
        <option value="3" ${(sec.minRating||4)==3?'selected':''}>3+ Stars</option>
        <option value="4" ${(sec.minRating||4)>=4?'selected':''}>4+ Stars</option>
        <option value="5" ${sec.minRating===5?'selected':''}>5 Stars Only</option>
      </select></div>
    </div>
    <div style="font-size:12px;color:var(--muted)">Reviews are randomly selected from approved customer reviews in the database that meet the minimum star rating.</div>`;
  }

  else if (type === 'newsletter') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Headline</label><input type="text" value="${escAttr(sec.headline||'Stay in the Loop')}" style="width:100%" onchange="updateField(${idx},'headline',this.value)" placeholder="Stay in the Loop"></div>
      <div class="form-group"><label>Subtext</label><input type="text" value="${escAttr(sec.subtext||'Get the latest deals and new arrivals delivered to your inbox.')}" style="width:100%" onchange="updateField(${idx},'subtext',this.value)" placeholder="Get the latest deals..."></div>
      <div class="form-group"><label>Button Text</label><input type="text" value="${escAttr(sec.btnText||'Subscribe')}" style="width:100%" onchange="updateField(${idx},'btnText',this.value)" placeholder="Subscribe"></div>
      <div class="form-group"><label>Input Placeholder</label><input type="text" value="${escAttr(sec.placeholder||'Your email address')}" style="width:100%" onchange="updateField(${idx},'placeholder',this.value)" placeholder="Your email address"></div>
      <div class="form-group"><label>Background Color</label><div style="display:flex;gap:6px;align-items:center">
        <input type="color" value="${sec.bgColor||'#f0ede8'}" style="width:40px;height:36px;border:none;background:none;cursor:pointer" onchange="updateField(${idx},'bgColor',this.value)">
        <input type="text" value="${escAttr(sec.bgColor||'#f0ede8')}" style="flex:1" onchange="updateField(${idx},'bgColor',this.value)">
      </div></div>
    </div>
    <div style="font-size:12px;color:var(--muted)">Shows a subscription form. Note: backend email collection requires additional setup.</div>`;
  }

  else if (type === 'flash') {
    const endTimeVal = sec.endTime ? new Date(sec.endTime).toISOString().slice(0,16) : '';
    const endTimeStatus = sec.endTime
      ? (sec.endTime > Date.now()
          ? `<span style="color:#22c55e">⏱ Active — ends ${new Date(sec.endTime).toLocaleString()}</span>`
          : `<span style="color:#ef4444">⏰ Expired — set a new end time</span>`)
      : `<span style="color:var(--muted)">No end time set — timer resets on every page load</span>`;
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Flash Sale')}" style="width:100%" onchange="updateField(${idx},'title',this.value)" placeholder="Flash Sale"></div>
      <div class="form-group"><label>Discount (%)</label><input type="number" value="${sec.discount||20}" min="1" max="99" style="width:100%" onchange="updateField(${idx},'discount',parseInt(this.value))"></div>
      <div class="form-group"><label>Duration (hours)</label><input type="number" value="${sec.duration||4}" min="1" max="720" style="width:100%" onchange="updateField(${idx},'duration',parseInt(this.value))" id="flash-dur-${idx}"></div>
      <div class="form-group"><label>Min Order (EGP)</label><input type="number" value="${sec.minOrder||0}" min="0" style="width:100%" onchange="updateField(${idx},'minOrder',parseInt(this.value))"></div>
    </div>
    <div class="form-group" style="margin-top:8px">
      <label>End Date & Time <span style="font-size:11px;font-weight:400;color:var(--muted)">(sets a fixed countdown target — required for the timer to work consistently)</span></label>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px">
        <input type="datetime-local" value="${endTimeVal}" style="flex:1;min-width:180px" onchange="updateField(${idx},'endTime', this.value ? new Date(this.value).getTime() : 0)" id="flash-endtime-${idx}">
        <button class="btn btn-sm" style="background:rgba(232,93,38,.15);border:1px solid rgba(232,93,38,.3);color:#e85d26;white-space:nowrap" onclick="setFlashEndFromNow(${idx})">⚡ Start from now</button>
        <button class="btn btn-sm" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:var(--muted)" onclick="updateField(${idx},'endTime',0);document.getElementById('flash-endtime-${idx}').value=''" title="Clear end time">Clear</button>
      </div>
      <div style="font-size:12px;margin-top:6px">${endTimeStatus}</div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showCountdownSeconds!==false?'checked':''} onchange="updateField(${idx},'showCountdownSeconds',this.checked)" style="width:16px;height:16px"> Show Seconds in Countdown</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.autoDismissWhenExpired?'checked':''} onchange="updateField(${idx},'autoDismissWhenExpired',this.checked)" style="width:16px;height:16px"> Auto-Dismiss When Expired</label>
    </div>
    <div style="margin-top:16px;border-top:1px solid rgba(255,255,255,.1);padding-top:14px">
      <label style="font-size:13px;font-weight:600;color:var(--text);display:block;margin-bottom:10px">⚡ Product Targeting</label>
      <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:12px">
        <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer">
          <input type="radio" name="flash-apply-${idx}" value="all" ${(sec.applyTo||'all')==='all'?'checked':''} onchange="updateField(${idx},'applyTo','all');renderFlashTargeting(${idx})"> All Products
        </label>
        <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer">
          <input type="radio" name="flash-apply-${idx}" value="categories" ${sec.applyTo==='categories'?'checked':''} onchange="updateField(${idx},'applyTo','categories');renderFlashTargeting(${idx})"> Specific Categories
        </label>
        <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer">
          <input type="radio" name="flash-apply-${idx}" value="products" ${sec.applyTo==='products'?'checked':''} onchange="updateField(${idx},'applyTo','products');renderFlashTargeting(${idx})"> Specific Products
        </label>
      </div>
      <div id="flash-targeting-${idx}"></div>
    </div>`;
    setTimeout(() => renderFlashTargeting(idx), 0);
  }

  else if (type === 'bundle') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Bundle Deal')}" style="width:100%" onchange="updateField(${idx},'title',this.value)" placeholder="Bundle Deal"></div>
      <div class="form-group"><label>Category (label)</label><input type="text" value="${escAttr(sec.category||'')}" style="width:100%" onchange="updateField(${idx},'category',this.value)" placeholder="e.g. Phones, Bags…"></div>
      <div class="form-group"><label>Min Qty to Buy</label><input type="number" value="${sec.minQty||2}" min="1" style="width:100%" onchange="updateField(${idx},'minQty',parseInt(this.value))"></div>
      <div class="form-group"><label>Free Items Given</label><input type="number" value="${sec.freeItems||1}" min="1" style="width:100%" onchange="updateField(${idx},'freeItems',parseInt(this.value))"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.autoAddFreeItem?'checked':''} onchange="updateField(${idx},'autoAddFreeItem',this.checked)" style="width:16px;height:16px"> Auto-Add Free Item to Cart</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showSavingsBadge!==false?'checked':''} onchange="updateField(${idx},'showSavingsBadge',this.checked)" style="width:16px;height:16px"> Show "Special Deal" Badge</label>
    </div>`;
  }

  else if (type === 'loyalty') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Points per EGP</label><input type="number" value="${sec.rate||10}" min="1" style="width:100%" onchange="updateField(${idx},'rate',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Points to Redeem</label><input type="number" value="${sec.minRedeem||100}" min="1" style="width:100%" onchange="updateField(${idx},'minRedeem',parseInt(this.value))"></div>
      <div class="form-group"><label>Conversion Rate (text)</label><input type="text" value="${escAttr(sec.convRate||'100 pts = 5 EGP')}" style="width:100%" onchange="updateField(${idx},'convRate',this.value)" placeholder="100 pts = 5 EGP"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showInNav?'checked':''} onchange="updateField(${idx},'showInNav',this.checked)" style="width:16px;height:16px"> Show in Navigation</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.notifyOnMilestone?'checked':''} onchange="updateField(${idx},'notifyOnMilestone',this.checked)" style="width:16px;height:16px"> Notify on Milestone</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.doublePointsWeekends?'checked':''} onchange="updateField(${idx},'doublePointsWeekends',this.checked)" style="width:16px;height:16px"> 2× Points on Weekends</label>
    </div>`;
  }

  else if (type === 'trending') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Trending Now')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="Trending Now"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||10}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Refresh Every (hours)</label><input type="number" value="${sec.refreshInterval||24}" min="1" style="width:100%" onchange="updateField(${idx},'refreshInterval',parseInt(this.value))"></div>
      <div class="form-group"><label>Algorithm</label><select onchange="updateField(${idx},'algo',this.value)">
        <option value="sold7d" ${(sec.algo||'sold7d')==='sold7d'?'selected':''}>Most Sold (All Time)</option>
        <option value="views7d" ${sec.algo==='views7d'?'selected':''}>Most Viewed 7d</option>
        <option value="rated" ${sec.algo==='rated'?'selected':''}>Highest Rated</option>
        <option value="manual" ${sec.algo==='manual'?'selected':''}>Manual</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showRankBadge!==false?'checked':''} onchange="updateField(${idx},'showRankBadge',this.checked)" style="width:16px;height:16px"> Show Rank Badge (#1, #2…)</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showSoldToday?'checked':''} onchange="updateField(${idx},'showSoldToday',this.checked)" style="width:16px;height:16px"> Show "X+ sold" count</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.autoScroll?'checked':''} onchange="updateField(${idx},'autoScroll',this.checked)" style="width:16px;height:16px"> Auto-Scroll</label>
    </div>`;
  }

  else if (type === 'arrivals') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'New Arrivals')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="New Arrivals"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||8}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Scroll Speed</label><select onchange="updateField(${idx},'speed',this.value)">
        <option value="slow" ${sec.speed==='slow'?'selected':''}>Slow</option>
        <option value="normal" ${(sec.speed||'normal')==='normal'?'selected':''}>Normal</option>
        <option value="fast" ${sec.speed==='fast'?'selected':''}>Fast</option>
      </select></div>
      <div class="form-group"><label>Chip Tag Text</label><input type="text" value="${escAttr(sec.tag||'Just Arrived')}" style="width:100%" onchange="updateField(${idx},'tag',this.value)" placeholder="Just Arrived"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.pauseOnHover!==false?'checked':''} onchange="updateField(${idx},'pauseOnHover',this.checked)" style="width:16px;height:16px"> Pause on Hover</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showCategoryChip?'checked':''} onchange="updateField(${idx},'showCategoryChip',this.checked)" style="width:16px;height:16px"> Show Category Chip</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.loopInfinitely!==false?'checked':''} onchange="updateField(${idx},'loopInfinitely',this.checked)" style="width:16px;height:16px"> Loop Infinitely</label>
    </div>`;
  }

  else if (type === 'brandLogos') {
    html = `
    <div class="form-group" style="margin-bottom:12px"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Shop by Brand')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="Shop by Brand"></div>
    <div class="form-group" style="margin-bottom:12px"><label>Brands (comma-separated, leave blank for all DB brands)</label><input type="text" value="${escAttr(sec.brands||'')}" style="width:100%" onchange="updateField(${idx},'brands',this.value)" placeholder="Nike, Adidas, Zara…"></div>
    <div class="form-grid">
      <div class="form-group"><label>Logo Size</label><select onchange="updateField(${idx},'size',this.value)">
        <option value="small" ${sec.size==='small'?'selected':''}>Small</option>
        <option value="medium" ${(sec.size||'medium')==='medium'?'selected':''}>Medium</option>
        <option value="large" ${sec.size==='large'?'selected':''}>Large</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.clickableFilter!==false?'checked':''} onchange="updateField(${idx},'clickableFilter',this.checked)" style="width:16px;height:16px"> Clicking Filters by Brand</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showNameBelowLogo!==false?'checked':''} onchange="updateField(${idx},'showNameBelowLogo',this.checked)" style="width:16px;height:16px"> Show Name Below Logo</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.grayscaleUntilHover?'checked':''} onchange="updateField(${idx},'grayscaleUntilHover',this.checked)" style="width:16px;height:16px"> Grayscale Until Hover</label>
    </div>`;
  }

  else if (type === 'reviewsCarousel') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Customer Reviews')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="Customer Reviews"></div>
      <div class="form-group"><label>Reviews to Show</label><input type="number" value="${sec.count||6}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Stars</label><select onchange="updateField(${idx},'minStars',parseInt(this.value))">
        <option value="3" ${(sec.minStars||4)===3?'selected':''}>3+ Stars</option>
        <option value="4" ${(sec.minStars||4)===4?'selected':''}>4+ Stars</option>
        <option value="5" ${sec.minStars===5?'selected':''}>5 Stars Only</option>
      </select></div>
      <div class="form-group"><label>Auto-Rotate Every (secs)</label><input type="number" value="${sec.interval||4}" min="2" max="30" style="width:100%" onchange="updateField(${idx},'interval',parseInt(this.value))"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showReviewerPhoto?'checked':''} onchange="updateField(${idx},'showReviewerPhoto',this.checked)" style="width:16px;height:16px"> Show Reviewer Photo</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showProductReviewed!==false?'checked':''} onchange="updateField(${idx},'showProductReviewed',this.checked)" style="width:16px;height:16px"> Show Product Reviewed</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.allowManualNavigation!==false?'checked':''} onchange="updateField(${idx},'allowManualNavigation',this.checked)" style="width:16px;height:16px"> Show Nav Arrows & Dots</label>
    </div>`;
  }

  else if (type === 'activity') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Message Template (use {n} for count)</label><input type="text" value="${escAttr(sec.messageTemplate||'{n} people shopped with us recently')}" style="width:100%" onchange="updateField(${idx},'messageTemplate',this.value)" placeholder="{n} people shopped with us recently"></div>
      <div class="form-group"><label>Min Count to Show</label><input type="number" value="${sec.minCount||1}" min="0" style="width:100%" onchange="updateField(${idx},'minCount',parseInt(this.value))"></div>
      <div class="form-group"><label>Time Window</label><select onchange="updateField(${idx},'window',this.value)">
        <option value="24h" ${(sec.window||'24h')==='24h'?'selected':''}>Last 24 Hours</option>
        <option value="7d" ${sec.window==='7d'?'selected':''}>Last 7 Days</option>
        <option value="month" ${sec.window==='month'?'selected':''}>This Month</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.randomizeSlightly?'checked':''} onchange="updateField(${idx},'randomizeSlightly',this.checked)" style="width:16px;height:16px"> Randomize Count Slightly (for social proof)</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnProductPage?'checked':''} onchange="updateField(${idx},'showOnProductPage',this.checked)" style="width:16px;height:16px"> Also Show on Product Page</label>
    </div>`;
  }

  else if (type === 'recent') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Max Products to Show</label><input type="number" value="${sec.maxProducts||8}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'maxProducts',parseInt(this.value))"></div>
      <div class="form-group"><label>Remember for (days)</label><input type="number" value="${sec.persistDays||30}" min="1" max="90" style="width:100%" onchange="updateField(${idx},'persistDays',parseInt(this.value))"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showForGuests!==false?'checked':''} onchange="updateField(${idx},'showForGuests',this.checked)" style="width:16px;height:16px"> Show for Guest Visitors</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnlyLoggedIn?'checked':''} onchange="updateField(${idx},'showOnlyLoggedIn',this.checked)" style="width:16px;height:16px"> Show Only for Logged-In Users</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.includeOutOfStock?'checked':''} onchange="updateField(${idx},'includeOutOfStock',this.checked)" style="width:16px;height:16px"> Include Out-of-Stock Products</label>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:10px">Product history is stored in the visitor's browser (localStorage). No personal data is sent to the server.</div>`;
  }

  else if (type === 'complete') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Suggestions per Item</label><input type="number" value="${sec.suggestionsPerItem||3}" min="1" max="10" style="width:100%" onchange="updateField(${idx},'suggestionsPerItem',parseInt(this.value))"></div>
      <div class="form-group"><label>Strategy</label><select onchange="updateField(${idx},'strategy',this.value)">
        <option value="Same category" ${(sec.strategy||'Same category')==='Same category'?'selected':''}>Same Category</option>
        <option value="Complementary" ${sec.strategy==='Complementary'?'selected':''}>Complementary</option>
        <option value="Admin-curated" ${sec.strategy==='Admin-curated'?'selected':''}>Admin-curated</option>
        <option value="AI picks" ${sec.strategy==='AI picks'?'selected':''}>AI Picks</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showInCartSidebar?'checked':''} onchange="updateField(${idx},'showInCartSidebar',this.checked)" style="width:16px;height:16px"> Show in Cart Sidebar</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnProductPage?'checked':''} onchange="updateField(${idx},'showOnProductPage',this.checked)" style="width:16px;height:16px"> Show on Product Page</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showDiscountIfBoughtTogether?'checked':''} onchange="updateField(${idx},'showDiscountIfBoughtTogether',this.checked)" style="width:16px;height:16px"> Bundle Discount When Bought Together</label>
    </div>`;
  }

  else if (type === 'recommended') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Section Title</label><input type="text" value="${escAttr(sec.headerText||'Recommended For You')}" style="width:100%" onchange="updateField(${idx},'headerText',this.value)" placeholder="Recommended For You"></div>
      <div class="form-group"><label>Products to Show</label><input type="number" value="${sec.count||8}" min="1" max="20" style="width:100%" onchange="updateField(${idx},'count',parseInt(this.value))"></div>
      <div class="form-group"><label>Fallback (when no history)</label><select onchange="updateField(${idx},'fallback',this.value)">
        <option value="trending" ${(sec.fallback||'trending')==='trending'?'selected':''}>Trending</option>
        <option value="arrivals" ${sec.fallback==='arrivals'?'selected':''}>New Arrivals</option>
        <option value="bestsellers" ${sec.fallback==='bestsellers'?'selected':''}>Best Sellers</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnlyLoggedIn?'checked':''} onchange="updateField(${idx},'showOnlyLoggedIn',this.checked)" style="width:16px;height:16px"> Show Only for Logged-In Users</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.personalizedLabel?'checked':''} onchange="updateField(${idx},'personalizedLabel',this.checked)" style="width:16px;height:16px"> Use "Picked For You" Label</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.refreshDaily?'checked':''} onchange="updateField(${idx},'refreshDaily',this.checked)" style="width:16px;height:16px"> Refresh Daily</label>
    </div>`;
  }

  else if (type === 'seasonal') {
    html = `
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#22c55e;margin-bottom:10px">🎄 Full-width — auto-hides outside date range</div>
    <div class="form-grid">
      <div class="form-group"><label>Title</label><input type="text" value="${escAttr(sec.title||'Special Season')}" style="width:100%" onchange="updateField(${idx},'title',this.value)" placeholder="Eid Sale"></div>
      <div class="form-group"><label>Subtitle</label><input type="text" value="${escAttr(sec.subtitle||'')}" style="width:100%" onchange="updateField(${idx},'subtitle',this.value)" placeholder="Limited-time seasonal offers"></div>
      <div class="form-group"><label>Start Date</label><input type="date" value="${sec.startDate||''}" style="width:100%" onchange="updateField(${idx},'startDate',this.value)"></div>
      <div class="form-group"><label>End Date</label><input type="date" value="${sec.endDate||''}" style="width:100%" onchange="updateField(${idx},'endDate',this.value)"></div>
      <div class="form-group"><label>Color Theme</label><select onchange="updateField(${idx},'theme',this.value)">
        <option value="Gold & Purple" ${(sec.theme||'Gold & Purple')==='Gold & Purple'?'selected':''}>Gold & Purple</option>
        <option value="Green & White" ${sec.theme==='Green & White'?'selected':''}>Green & White</option>
        <option value="Red & Gold" ${sec.theme==='Red & Gold'?'selected':''}>Red & Gold</option>
        <option value="Custom" ${sec.theme==='Custom'?'selected':''}>Custom (Dark → Orange)</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.fullWidthBanner!==false?'checked':''} onchange="updateField(${idx},'fullWidthBanner',this.checked)" style="width:16px;height:16px"> Full Width Banner</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.animateEntrance!==false?'checked':''} onchange="updateField(${idx},'animateEntrance',this.checked)" style="width:16px;height:16px"> Animate Entrance</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showCountdownToEvent?'checked':''} onchange="updateField(${idx},'showCountdownToEvent',this.checked)" style="width:16px;height:16px"> Show Countdown to End Date</label>
    </div>`;
  }

  else if (type === 'referral') {
    html = `
    <div class="form-grid">
      <div class="form-group"><label>Reward for Referrer (EGP)</label><input type="number" value="${sec.rewardReferrer||50}" min="0" style="width:100%" onchange="updateField(${idx},'rewardReferrer',parseInt(this.value))"></div>
      <div class="form-group"><label>Reward for New User (EGP)</label><input type="number" value="${sec.rewardNewUser||30}" min="0" style="width:100%" onchange="updateField(${idx},'rewardNewUser',parseInt(this.value))"></div>
      <div class="form-group"><label>Min Order to Qualify (EGP)</label><input type="number" value="${sec.minOrder||200}" min="0" style="width:100%" onchange="updateField(${idx},'minOrder',parseInt(this.value))"></div>
      <div class="form-group"><label>Button / CTA Text</label><input type="text" value="${escAttr(sec.ctaText||'Invite Friends & Earn!')}" style="width:100%" onchange="updateField(${idx},'ctaText',this.value)" placeholder="Invite Friends & Earn!"></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.shareViaWhatsApp!==false?'checked':''} onchange="updateField(${idx},'shareViaWhatsApp',this.checked)" style="width:16px;height:16px"> Share via WhatsApp</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.shareViaLink!==false?'checked':''} onchange="updateField(${idx},'shareViaLink',this.checked)" style="width:16px;height:16px"> Copy Link Button</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showInUserDashboard?'checked':''} onchange="updateField(${idx},'showInUserDashboard',this.checked)" style="width:16px;height:16px"> Show in User Dashboard</label>
    </div>`;
  }

  else if (type === 'announcement') {
    html = `
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">📢 Full-width — appears above the page header</div>
    <div class="form-group" style="margin-bottom:12px"><label>Message</label><textarea style="width:100%;min-height:60px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:6px;font-family:inherit;font-size:13px" onchange="updateField(${idx},'message',this.value)">${escHtml(sec.message||'Welcome to Ramo Store! Free shipping on orders over 500 EGP.')}</textarea></div>
    <div class="form-grid">
      <div class="form-group"><label>Scroll Speed</label><select onchange="updateField(${idx},'speed',this.value)">
        <option value="slow" ${sec.speed==='slow'?'selected':''}>Slow</option>
        <option value="normal" ${(sec.speed||'normal')==='normal'?'selected':''}>Normal</option>
        <option value="fast" ${sec.speed==='fast'?'selected':''}>Fast</option>
        <option value="static" ${sec.speed==='static'?'selected':''}>Static (no scroll)</option>
      </select></div>
      <div class="form-group"><label>Bar Color</label><select onchange="updateField(${idx},'barColor',this.value)">
        <option value="dark" ${(sec.barColor||'dark')==='dark'?'selected':''}>Dark (black)</option>
        <option value="orange" ${sec.barColor==='orange'?'selected':''}>Brand Orange</option>
        <option value="navy" ${sec.barColor==='navy'?'selected':''}>Navy</option>
        <option value="white" ${sec.barColor==='white'?'selected':''}>White</option>
      </select></div>
    </div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px">
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.dismissableByUser!==false?'checked':''} onchange="updateField(${idx},'dismissableByUser',this.checked)" style="width:16px;height:16px"> Dismissable by User</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnAllPages!==false?'checked':''} onchange="updateField(${idx},'showOnAllPages',this.checked)" style="width:16px;height:16px"> Show on All Pages</label>
      <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer"><input type="checkbox" ${sec.showOnlyToGuests?'checked':''} onchange="updateField(${idx},'showOnlyToGuests',this.checked)" style="width:16px;height:16px"> Show Only to Guests</label>
    </div>`;
  }

  else {
    html = `<div style="font-size:12px;color:var(--muted)">Raw JSON for this section:</div>
    <textarea style="width:100%;height:120px;font-family:monospace;font-size:12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:6px;margin-top:8px"
      onchange="updateRaw(${idx},this.value)">${escHtml(JSON.stringify(sec,null,2))}</textarea>`;
  }

  return html;
}

function buildCatItem(idx, ii, item) {
  return `<div class="item-row" id="catItem-${idx}-${ii}">
    <select style="width:140px" onchange="updateCatItem(${idx},${ii},'category',parseInt(this.value))">
      <option value="">Select category</option>${catOptions(item.category)}
    </select>
    <input type="text" value="${escAttr(item.label||'')}" placeholder="Label" style="width:100px" onchange="updateCatItem(${idx},${ii},'label',this.value)">
    <input type="text" value="${escAttr(item.image||'')}" placeholder="Image URL" style="flex:1" onchange="updateCatItem(${idx},${ii},'image',this.value)">
    <input type="color" value="${(item.colors&&item.colors[0])||'#3CC2BF'}" style="width:36px;height:36px;border:none;background:none;cursor:pointer" onchange="updateCatItem(${idx},${ii},'colors',[this.value,this.value])" title="Color">
    <button class="btn btn-danger btn-sm" onclick="removeCatItem(${idx},${ii})">×</button>
  </div>`;
}

function buildBannerItem(idx, ii, item) {
  return `<div class="item-row" id="bannerItem-${idx}-${ii}">
    <input type="text" value="${escAttr(item.image||'')}" placeholder="Image URL" style="flex:2" onchange="updateBannerItem(${idx},${ii},'image',this.value)">
    <select style="width:150px" onchange="updateBannerItem(${idx},${ii},'category',this.value?parseInt(this.value):undefined)">
      <option value="">No link</option>${catOptions(item.category)}
    </select>
    <button class="btn btn-danger btn-sm" onclick="removeBannerItem(${idx},${ii})">×</button>
  </div>`;
}

// ── RENDER ALL ─────────────────────────────────────────────────────
function renderAll() {
  const list = document.getElementById('sectionList');
  list.innerHTML = sections.map((s, i) => buildCard(s, i)).join('');
  initSortable();
}

function initSortable() {
  Sortable.create(document.getElementById('sectionList'), {
    handle: '.tl-drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd(evt) {
      const moved = sections.splice(evt.oldIndex, 1)[0];
      sections.splice(evt.newIndex, 0, moved);
      renderAll();
    }
  });
}

// ── ACTIONS ────────────────────────────────────────────────────────
function toggleBody(idx) {
  const body = document.getElementById('body-' + idx);
  if (body) body.classList.toggle('open');
}

function removeSection(idx) {
  if (!confirm('Remove this section?')) return;
  sections.splice(idx, 1);
  renderAll();
}

function updateField(idx, key, value) {
  sections[idx][key] = value;
}

function toggleHidden(idx) {
  sections[idx].hidden = !sections[idx].hidden;
  renderAll();
  setTimeout(() => { const b = document.getElementById('body-'+idx); if(b) b.classList.add('open'); }, 50);
}

function setFlashEndFromNow(idx) {
  const durEl = document.getElementById('flash-dur-' + idx);
  const hours = durEl ? (parseInt(durEl.value) || 4) : (sections[idx].duration || 4);
  const endMs = Date.now() + hours * 3600 * 1000;
  sections[idx].endTime = endMs;
  const dtEl = document.getElementById('flash-endtime-' + idx);
  if (dtEl) {
    const local = new Date(endMs - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    dtEl.value = local;
  }
  renderAll();
  setTimeout(() => { const b = document.getElementById('body-'+idx); if(b) b.classList.add('open'); }, 50);
}

function updateRaw(idx, raw) {
  try {
    sections[idx] = JSON.parse(raw);
    // Don't re-render, keep editor open
  } catch(e) {}
}

// Category item helpers
function addCatItem(idx) {
  if (!sections[idx].items) sections[idx].items = [];
  sections[idx].items.push({ category: '', label: '', image: '', colors: ['#3CC2BF','#3CC2BF'] });
  const ii = sections[idx].items.length - 1;
  const container = document.getElementById('catItems-' + idx);
  container.insertAdjacentHTML('beforeend', buildCatItem(idx, ii, sections[idx].items[ii]));
}

function removeCatItem(idx, ii) {
  sections[idx].items.splice(ii, 1);
  renderAll();
  document.getElementById('body-' + idx) && document.getElementById('body-' + idx).classList.add('open');
}

function updateCatItem(idx, ii, key, value) {
  if (!sections[idx].items) sections[idx].items = [];
  if (!sections[idx].items[ii]) sections[idx].items[ii] = {};
  sections[idx].items[ii][key] = value;
}

// Stats item helpers
function addStatsItem(idx) {
  if (!sections[idx].items) sections[idx].items = [];
  const statKeys = ['products','vendors','categories','brands','orders','reviews'];
  const usedKeys = sections[idx].items.map(i => i.key);
  const nextKey = statKeys.find(k => !usedKeys.includes(k)) || 'products';
  sections[idx].items.push({ key: nextKey, label: nextKey.charAt(0).toUpperCase()+nextKey.slice(1) });
  const ii = sections[idx].items.length - 1;
  const container = document.getElementById('statsItems-' + idx);
  const statKeys2 = ['products','vendors','categories','brands','orders','reviews'];
  const item = sections[idx].items[ii];
  const row = document.createElement('div');
  row.className = 'item-row';
  row.id = `statsItem-${idx}-${ii}`;
  row.innerHTML = `<select style="width:140px" onchange="updateStatsItem(${idx},${ii},'key',this.value)">${statKeys2.map(k=>`<option value="${k}" ${item.key===k?'selected':''}>${k.charAt(0).toUpperCase()+k.slice(1)}</option>`).join('')}</select><input type="text" value="${item.label||''}" placeholder="Label" style="flex:1" onchange="updateStatsItem(${idx},${ii},'label',this.value)"><button class="btn btn-danger btn-sm" onclick="removeStatsItem(${idx},${ii})">×</button>`;
  container.appendChild(row);
}

function removeStatsItem(idx, ii) {
  sections[idx].items.splice(ii, 1);
  renderAll();
  setTimeout(() => { const b = document.getElementById('body-'+idx); if(b) b.classList.add('open'); }, 50);
}

function updateStatsItem(idx, ii, key, value) {
  if (!sections[idx].items) sections[idx].items = [];
  if (!sections[idx].items[ii]) sections[idx].items[ii] = {};
  sections[idx].items[ii][key] = value;
}

// Banner item helpers
function addBannerItem(idx) {
  if (!sections[idx].items) sections[idx].items = [];
  sections[idx].items.push({ image: '', padding: 7 });
  const ii = sections[idx].items.length - 1;
  const container = document.getElementById('bannerItems-' + idx);
  container.insertAdjacentHTML('beforeend', buildBannerItem(idx, ii, sections[idx].items[ii]));
}

function removeBannerItem(idx, ii) {
  sections[idx].items.splice(ii, 1);
  renderAll();
  document.getElementById('body-' + idx) && document.getElementById('body-' + idx).classList.add('open');
}

function updateBannerItem(idx, ii, key, value) {
  if (!sections[idx].items) sections[idx].items = [];
  if (!sections[idx].items[ii]) sections[idx].items[ii] = {};
  if (value === undefined) delete sections[idx].items[ii][key];
  else sections[idx].items[ii][key] = value;
}

// Add new section
const DEFAULTS = {
  bannerImage:       { layout:'bannerImage', design:'default', isSlider:true, autoPlay:true, radius:2, items:[] },
  category:          { layout:'category', type:'icon', wrap:false, size:1, radius:50, items:[] },
  twoColumn:         { layout:'twoColumn', headerText:'New Section', maxItemsToShow:8, category:'' },
  saleImages:        { layout:'saleImages', headerText:'Featured Products', maxItemsToShow:8, category:'' },
  seupermarketstars: { layout:'seupermarketstars', name:'Featured', category:'' },
  topVendors:        { layout:'topVendors', headerText:'Top Sellers', maxItemsToShow:6, sortBy:'products' },
  brands:            { layout:'brands' },
  coupons:           { layout:'coupons', headerText:"This Week's Deals", subLabel:'Use code at checkout', maxItemsToShow:6, sortBy:'amount', showExpiredFallback:true, hideWhenEmpty:true },
  statsBar:          { layout:'statsBar', bgColor:'#111111', textColor:'#ffffff', items:[{key:'products',label:'Products'},{key:'vendors',label:'Vendors'},{key:'categories',label:'Categories'},{key:'brands',label:'Brands'}] },
  promoBlock:        { layout:'promoBlock', headline:'Special Offer', subtext:'Discover exclusive deals and limited-time offers.', btnText:'Shop Now', btnLink:'/shop', bgColor:'#1a1a2e', textColor:'#ffffff', btnColor:'#e85d26', align:'left' },
  testimonials:      { layout:'testimonials', headerText:'What Our Customers Say', maxItemsToShow:4, minRating:4 },
  newsletter:        { layout:'newsletter', headline:'Stay in the Loop', subtext:'Get the latest deals and new arrivals delivered to your inbox.', btnText:'Subscribe', placeholder:'Your email address', bgColor:'#f0ede8' },
  trending:          { layout:'trending', headerText:'Trending Now', count:10, algo:'sold7d', refreshInterval:24, showRankBadge:true, showSoldToday:false, autoScroll:false },
  arrivals:          { layout:'arrivals', headerText:'New Arrivals', count:8, speed:'normal', tag:'Just Arrived', pauseOnHover:true, showCategoryChip:false, loopInfinitely:true },
  brandLogos:        { layout:'brandLogos', headerText:'Shop by Brand', brands:'', size:'medium', clickableFilter:true, showNameBelowLogo:true, grayscaleUntilHover:false },
  reviewsCarousel:   { layout:'reviewsCarousel', headerText:'Customer Reviews', count:6, minStars:4, interval:4, showReviewerPhoto:false, showProductReviewed:true, allowManualNavigation:true },
  activity:          { layout:'activity', messageTemplate:'{n} people shopped with us recently', minCount:1, window:'24h', randomizeSlightly:true, showOnProductPage:false },
  recent:            { layout:'recent', maxProducts:8, persistDays:30, showForGuests:true, showOnlyLoggedIn:false, includeOutOfStock:false },
  bundle:            { layout:'bundle', title:'Bundle Deal', category:'', minQty:2, freeItems:1, autoAddFreeItem:false, showSavingsBadge:true },
  loyalty:           { layout:'loyalty', rate:10, minRedeem:100, convRate:'100 pts = 5 EGP', showInNav:false, notifyOnMilestone:false, doublePointsWeekends:false },
  seasonal:          { layout:'seasonal', title:'Special Season', subtitle:'Limited-time offers for this season', startDate:'', endDate:'', theme:'Gold & Purple', fullWidthBanner:true, animateEntrance:true, showCountdownToEvent:false },
  referral:          { layout:'referral', rewardReferrer:50, rewardNewUser:30, minOrder:200, ctaText:'Invite Friends & Earn!', shareViaWhatsApp:true, shareViaLink:true, showInUserDashboard:false },
  complete:          { layout:'complete', suggestionsPerItem:3, strategy:'Same category', showInCartSidebar:false, showOnProductPage:false, showDiscountIfBoughtTogether:false },
  recommended:       { layout:'recommended', headerText:'Recommended For You', count:8, fallback:'trending', showOnlyLoggedIn:false, personalizedLabel:false, refreshDaily:false },
  announcement:      { layout:'announcement', message:'Welcome to Ramo Store! Free shipping on orders over 500 EGP.', speed:'normal', barColor:'dark', dismissableByUser:true, showOnAllPages:true, showOnlyToGuests:false },
  flash:             { layout:'flash', title:'Flash Sale', discount:20, duration:4, minOrder:0, showOnHomepage:true, showCountdownSeconds:true, autoDismissWhenExpired:false, applyTo:'all', targetCategories:[], targetProductIds:[] },
  spacer:            { layout:'spacer', height:24 },
  divider:           { layout:'divider' },
};

// ── WIDGET PREVIEWS ────────────────────────────────────────────────
const WIDGET_INFO = {
  bannerImage:      { title:'Banner / Slider', desc:'Full-width hero image or auto-playing slideshow with multiple banners. Each slide can link to a category or product.', tags:['Full-width','Configurable','Auto-play','Multiple slides'] },
  category:         { title:'Categories Strip', desc:'Horizontal row of category icons with labels. Great for quick navigation to product categories.', tags:['Icon strip','Scrollable','Configurable'] },
  twoColumn:        { title:'Products Grid', desc:'2–4 column product grid for a specific category or all products. Shows price, sale badge, and wishlist button.', tags:['Product cards','Category filter','Max items'] },
  saleImages:       { title:'Products Scroll', desc:'Horizontal scrollable strip of product cards. Ideal for showcasing a collection without taking up vertical space.', tags:['Horizontal scroll','Category filter','Compact'] },
  seupermarketstars:{ title:'Featured Items', desc:'Wide product grid (4 columns) showcasing featured or category products with larger cards.', tags:['Product cards','4 columns','Category filter'] },
  topVendors:       { title:'Top Vendors', desc:'Horizontal scroll of vendor store cards with logo, name, and product count. Sorted by products, rating, or newest.', tags:['Vendor cards','Sort options','Auto-loaded'] },
  brands:           { title:'Brands Strip', desc:'All brands from your database displayed as clickable chips. Auto-loaded, no configuration needed.', tags:['Auto-loaded','No config'] },
  coupons:          { title:'Coupons Strip', desc:'Shows active discount coupons with copy-to-clipboard buttons. Loaded automatically from your coupon database.', tags:['Auto-loaded','Copy button','Configurable'] },
  statsBar:         { title:'Stats Bar', desc:'Full-width dark bar showing live store statistics: total products, vendors, categories, brands, or orders.', tags:['Live stats','Dark bar','Configurable items'] },
  promoBlock:       { title:'Promo Block', desc:'Bold promotional banner with headline, subtext, a CTA button, and an optional side image. Fully colored.', tags:['Custom colors','CTA button','Optional image'] },
  testimonials:     { title:'Testimonials Grid', desc:'Grid of star-rated customer review cards. Reviews are randomly selected from approved reviews in the database.', tags:['Auto-loaded','Min stars filter','Grid layout'] },
  reviewsCarousel:  { title:'Reviews Carousel', desc:'Auto-rotating single review spotlight with star rating, reviewer name, and product name. Shows one review at a time.', tags:['Auto-rotate','Navigation dots','Large quote'] },
  newsletter:       { title:'Newsletter Signup', desc:'Centered email subscription block with headline, subtext, and a styled input + button. Custom background color.', tags:['Email capture','Custom background','Configurable'] },
  bundle:           { title:'Bundle Deal Card', desc:'Promotional card advertising a buy-X-get-Y-free bundle offer with a "Special Deal" badge and Shop Now CTA.', tags:['Promotional card','Buy X get Y free'] },
  loyalty:          { title:'Loyalty Points Banner', desc:'Dark gradient banner promoting your loyalty program. Shows points rate, redemption info, and optional weekend bonus.', tags:['Points rate','Weekend 2×','CTA button'] },
  activity:         { title:'Live Activity Banner', desc:'Small live counter showing how many people shopped recently — powerful social proof. Count comes from your orders database.', tags:['Live order count','Social proof','Configurable window'] },
  referral:         { title:'Referral Widget', desc:'Share-and-earn card with a unique referral link. Shows rewards for both the referrer and new user. WhatsApp sharing included.', tags:['Referral link','WhatsApp share','Configurable rewards'] },
  recent:           { title:'Recently Viewed', desc:'Shows products the visitor has viewed in this browser session. Uses localStorage — no personal data collected. Hidden when empty.', tags:['Browser history','No backend needed','Auto-hides'] },
  recommended:      { title:'Recommended For You', desc:'Personalised product strip. Falls back to trending/bestsellers for guests. Same data as Trending but different label.', tags:['Personalized','Trending fallback','Configurable'] },
  complete:         { title:'Complete the Look', desc:'A nudge widget encouraging customers to explore complementary products. Links to the shop with a configurable strategy.', tags:['Cross-sell','Strategy options','CTA button'] },
  trending:         { title:'Trending Now Strip', desc:'Horizontal scroll of your best-selling products sorted by total sales. Numbered rank badges (#1, #2…) highlight top sellers.', tags:['DB-powered','Rank badges','Sold count'] },
  arrivals:         { title:'New Arrivals Ticker', desc:'Auto-scrolling horizontal ticker of your newest products sorted by listing date. Pauses on hover.', tags:['Auto-scroll','DB-powered','Infinite loop'] },
  brandLogos:       { title:'Brand Logos Row', desc:'Grid of branded logo chips. Enter brand names manually or leave blank to auto-load all brands from your database.', tags:['Clickable filter','Custom brands','Logo initials'] },
  announcement:     { title:'Announcement Bar', desc:'Full-width scrolling ticker above the page header. Perfect for free shipping thresholds, sales, or news. Dismissable.', tags:['Full-width','Scrolling text','Above header','Dismissable'] },
  flash:            { title:'Flash Sale Timer', desc:'Urgency countdown bar in fire-red at the top of the page. Shows hours, minutes and seconds counting down from a set duration.', tags:['Countdown timer','Full-width','Auto-dismiss option'] },
  seasonal:         { title:'Seasonal Banner', desc:'Full-width gradient banner with a seasonal theme. Automatically shows/hides based on a start and end date you set.', tags:['Date range control','Color themes','Countdown option'] },
  spacer:           { title:'Spacer', desc:'Adds vertical empty space between sections. Configurable height in pixels.', tags:['Layout','Height control'] },
  divider:          { title:'Divider Line', desc:'Adds a subtle horizontal rule between sections. No configuration needed.', tags:['Layout','No config'] },
};

const WIDGET_MOCKUPS = {
  bannerImage: `<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;height:100px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden"><div style="color:#fff;text-align:center"><div style="font-size:11px;opacity:.6;margin-bottom:4px;letter-spacing:1px">HERO BANNER</div><div style="font-size:18px;font-weight:800">🖼️ Image Slider</div></div><div style="position:absolute;bottom:8px;left:50%;transform:translateX(-50%);display:flex;gap:5px"><span style="width:18px;height:4px;border-radius:2px;background:#e85d26;display:block"></span><span style="width:8px;height:4px;border-radius:2px;background:rgba(255,255,255,.3);display:block"></span><span style="width:8px;height:4px;border-radius:2px;background:rgba(255,255,255,.3);display:block"></span></div></div>`,

  category: `<div style="display:flex;gap:10px;flex-wrap:wrap">${['👗 Clothes','👟 Shoes','👜 Bags','📱 Phones','💄 Beauty'].map(c=>`<div style="display:flex;flex-direction:column;align-items:center;gap:4px"><div style="width:44px;height:44px;border-radius:50%;background:#f0ede8;border:2px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:18px">${c.split(' ')[0]}</div><span style="font-size:10px;color:#333;font-weight:600">${c.split(' ')[1]}</span></div>`).join('')}</div>`,

  twoColumn: `<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">${Array(4).fill(0).map((_,i)=>`<div style="border-radius:7px;overflow:hidden;border:1px solid #eee"><div style="height:70px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8fd'][i]};display:flex;align-items:center;justify-content:center;font-size:22px">🛍️</div><div style="padding:6px"><div style="height:7px;background:#eee;border-radius:4px;margin-bottom:4px"></div><div style="height:9px;background:#e85d26;border-radius:4px;width:60%"></div></div></div>`).join('')}</div>`,

  saleImages: `<div style="display:flex;gap:8px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:100px;border-radius:8px;overflow:hidden;border:1px solid #eee"><div style="height:80px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:24px">🛍️</div><div style="padding:5px"><div style="height:6px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="height:8px;background:#e85d26;border-radius:3px;width:55%"></div></div></div>`).join('')}<div style="flex-shrink:0;width:24px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:18px">›</div></div>`,

  seupermarketstars: `<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">${Array(4).fill(0).map((_,i)=>`<div style="border-radius:7px;overflow:hidden;border:1px solid #eee"><div style="height:80px;background:${['#fff8f0','#f0f8ff','#f0fff4','#fff0f8'][i]};display:flex;align-items:center;justify-content:center;font-size:26px">⭐</div><div style="padding:6px"><div style="height:7px;background:#eee;border-radius:4px;margin-bottom:4px"></div><div style="height:9px;background:#e85d26;border-radius:4px;width:65%"></div></div></div>`).join('')}</div>`,

  topVendors: `<div style="display:flex;gap:10px;overflow:hidden">${['🏪','🛒','🏬','🏪'].map((ic,i)=>`<div style="flex-shrink:0;width:90px;border-radius:10px;border:1px solid #eee;padding:10px 8px;text-align:center"><div style="width:40px;height:40px;border-radius:50%;background:#f5f5f5;margin:0 auto 6px;font-size:20px;display:flex;align-items:center;justify-content:center">${ic}</div><div style="height:7px;background:#ddd;border-radius:4px;margin-bottom:4px"></div><div style="font-size:10px;color:#999">12 items</div></div>`).join('')}</div>`,

  brands: `<div style="display:flex;flex-wrap:wrap;gap:6px">${['Nike','Adidas','Zara','H&M','Gucci','Puma','Reebok','Levi\'s'].map(b=>`<span style="padding:4px 10px;border:1px solid #ddd;border-radius:20px;font-size:11px;font-weight:600;color:#555">${b}</span>`).join('')}</div>`,

  coupons: `<div><div style="display:flex;justify-content:space-between;margin-bottom:10px"><span style="font-size:13px;font-weight:700;color:#333">This Week's Deals</span><span style="font-size:11px;color:#999">Use code at checkout</span></div><div style="display:flex;gap:8px">${[['#e85d26','SAVE20','20% off'],['#1a1a2e','FREESHIP','Free shipping']].map(([bg,code,label])=>`<div style="background:${bg};border-radius:8px;padding:10px 14px;color:#fff;flex:1"><div style="font-size:14px;font-weight:800">${label}</div><div style="display:flex;align-items:center;gap:6px;margin-top:5px"><span style="background:rgba(255,255,255,.25);padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700">${code}</span><span style="font-size:10px;background:rgba(255,255,255,.2);padding:2px 6px;border-radius:4px">Copy</span></div></div>`).join('')}</div></div>`,

  statsBar: `<div style="background:#111;border-radius:8px;padding:16px;display:flex;justify-content:space-around">${[['1,240','Products'],['48','Vendors'],['32','Categories'],['120','Brands']].map(([n,l])=>`<div style="text-align:center"><div style="font-size:22px;font-weight:800;color:#fff">${n}</div><div style="font-size:10px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-top:2px">${l}</div></div>`).join('')}</div>`,

  promoBlock: `<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;padding:16px 20px;display:flex;align-items:center;gap:16px"><div style="width:70px;height:56px;border-radius:8px;background:rgba(255,255,255,.1);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:24px">📣</div><div style="flex:1"><div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:4px">Special Offer</div><div style="font-size:11px;color:rgba(255,255,255,.6);margin-bottom:10px">Discover exclusive deals and limited-time offers.</div><span style="background:#e85d26;color:#fff;padding:5px 14px;border-radius:20px;font-size:11px;font-weight:700">Shop Now</span></div></div>`,

  testimonials: `<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px">${[['Sarah K.','Amazing quality!',5],['Omar M.','Fast shipping.',4],['Nour A.','Great store!',5],['Ali H.','Worth every penny.',4]].map(([n,c,r])=>`<div style="border:1px solid #eee;border-radius:8px;padding:10px"><div style="color:#f5a623;font-size:11px;margin-bottom:4px">${'★'.repeat(r)}${'☆'.repeat(5-r)}</div><div style="font-size:11px;color:#555;margin-bottom:6px">"${c}"</div><div style="display:flex;align-items:center;gap:5px"><div style="width:20px;height:20px;border-radius:50%;background:#e85d26;color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center">${n[0]}</div><span style="font-size:10px;font-weight:600;color:#333">${n}</span></div></div>`).join('')}</div>`,

  reviewsCarousel: `<div style="border:1px solid #eee;border-radius:10px;padding:20px;text-align:center"><div style="color:#f5a623;font-size:16px;margin-bottom:8px">★★★★★</div><div style="font-size:13px;color:#444;font-style:italic;margin-bottom:12px">"Absolutely love this product! Quality exceeded my expectations."</div><div style="display:flex;align-items:center;justify-content:center;gap:8px"><div style="width:28px;height:28px;border-radius:50%;background:#e85d26;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center">S</div><div style="text-align:left"><div style="font-size:12px;font-weight:700;color:#333">Sarah K.</div><div style="font-size:10px;color:#999">iPhone 15 Case</div></div></div><div style="display:flex;justify-content:center;gap:5px;margin-top:12px"><span style="width:18px;height:4px;border-radius:2px;background:#e85d26;display:inline-block"></span><span style="width:8px;height:4px;border-radius:2px;background:#ddd;display:inline-block"></span><span style="width:8px;height:4px;border-radius:2px;background:#ddd;display:inline-block"></span></div></div>`,

  newsletter: `<div style="background:#f0ede8;border-radius:8px;padding:18px;text-align:center"><div style="font-size:15px;font-weight:800;color:#333;margin-bottom:4px">Stay in the Loop</div><div style="font-size:11px;color:#888;margin-bottom:12px">Get the latest deals delivered to your inbox.</div><div style="display:flex;gap:6px;max-width:280px;margin:auto"><input type="text" placeholder="Your email address" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:20px;font-size:11px;background:#fff" readonly><button style="padding:7px 14px;background:#333;color:#fff;border:none;border-radius:20px;font-size:11px;font-weight:700;cursor:default">Subscribe</button></div></div>`,

  bundle: `<div style="background:linear-gradient(135deg,#fff8f4,#fde8d8);border:1.5px solid #fbd5bd;border-radius:8px;padding:14px;position:relative"><span style="position:absolute;top:10px;right:10px;background:#e85d26;color:#fff;font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;text-transform:uppercase">Special Deal</span><div style="display:flex;align-items:center;gap:12px"><span style="font-size:36px">🎁</span><div><div style="font-size:14px;font-weight:800;color:#333;margin-bottom:3px">Bundle Deal</div><div style="font-size:12px;color:#888">Buy <strong>2</strong> items, get <strong>1</strong> FREE!</div></div><span style="background:#e85d26;color:#fff;padding:7px 14px;border-radius:20px;font-size:11px;font-weight:700;margin-left:auto;cursor:default">Shop Now</span></div></div>`,

  loyalty: `<div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:8px;padding:14px 18px"><div style="display:flex;align-items:center;gap:12px"><span style="font-size:30px">⭐</span><div style="flex:1"><div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:2px">Earn 10 points per EGP spent!</div><div style="font-size:11px;color:rgba(255,255,255,.6)">Redeem from 100 pts · 100 pts = 5 EGP</div></div><span style="background:#e85d26;color:#fff;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;cursor:default">Start Earning</span></div></div>`,

  activity: `<div style="display:flex;justify-content:center"><div style="display:inline-flex;align-items:center;gap:8px;background:#fff8f4;border:1.5px solid #fde8d8;border-radius:30px;padding:7px 16px"><span style="width:8px;height:8px;border-radius:50%;background:#e85d26;display:inline-block"></span><span style="font-size:12px;font-weight:500;color:#7c3826">142 people shopped with us recently</span></div></div>`,

  referral: `<div style="background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1.5px solid #bae6fd;border-radius:8px;padding:14px"><div style="display:flex;gap:12px;align-items:flex-start"><span style="font-size:30px">🎁</span><div><div style="font-size:13px;font-weight:800;color:#0c4a6e;margin-bottom:3px">Invite Friends & Earn!</div><div style="font-size:11px;color:#0369a1;margin-bottom:8px">You earn <strong>50 EGP</strong> and your friend gets <strong>30 EGP</strong> off!</div><div style="display:flex;gap:6px"><input type="text" value="ramostore.com/ref/USER" style="flex:1;padding:5px 8px;border:1px solid #bae6fd;border-radius:6px;font-size:10px;background:#fff" readonly><span style="padding:5px 10px;background:#25d366;color:#fff;border-radius:6px;font-size:10px;font-weight:700;cursor:default">WhatsApp</span></div></div></div></div>`,

  recent: `<div><div style="font-size:12px;font-weight:700;color:#333;margin-bottom:8px">Recently Viewed</div><div style="display:flex;gap:8px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:90px;text-align:center"><div style="width:90px;height:80px;border-radius:8px;background:${['#fdf0e8','#e8f0fd','#e8fdf0','#fde8fd'][i]};display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:4px">🛍️</div><div style="font-size:9px;color:#555;line-height:1.3">Product Name</div><div style="font-size:10px;font-weight:700;color:#e85d26;margin-top:2px">199 EGP</div></div>`).join('')}</div></div>`,

  recommended: `<div><div style="font-size:12px;font-weight:700;color:#333;margin-bottom:8px">💡 Recommended For You</div><div style="display:flex;gap:8px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:90px;border-radius:8px;overflow:hidden;border:1px solid #eee"><div style="height:75px;background:${['#fff0e8','#e8f0ff','#e8fff0','#ffe8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:22px">🛍️</div><div style="padding:5px"><div style="height:6px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="height:8px;background:#e85d26;border-radius:3px;width:55%"></div></div></div>`).join('')}</div></div>`,

  complete: `<div style="background:#fff;border:1.5px solid #eee;border-radius:8px;padding:16px"><div style="display:flex;align-items:center;gap:12px"><span style="font-size:28px">👗</span><div><div style="font-size:14px;font-weight:700;color:#333;margin-bottom:3px">Complete the Look</div><div style="font-size:11px;color:#888">Find same-category items that go perfectly together.</div></div><span style="background:#333;color:#fff;padding:7px 14px;border-radius:20px;font-size:11px;font-weight:700;margin-left:auto;cursor:default">Browse →</span></div></div>`,

  trending: `<div><div style="display:flex;justify-content:space-between;margin-bottom:8px"><span style="font-size:12px;font-weight:700;color:#333">🔥 Trending Now</span><span style="font-size:10px;color:#e85d26">View all →</span></div><div style="display:flex;gap:8px;overflow:hidden">${Array(4).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:90px;border-radius:8px;overflow:hidden;border:1px solid #eee;position:relative"><span style="position:absolute;top:5px;left:5px;background:#e85d26;color:#fff;font-size:9px;font-weight:800;padding:1px 5px;border-radius:10px">#${i+1}</span><div style="height:75px;background:${['#fff0e8','#e8f0ff','#e8fff0','#ffe8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:22px">🛍️</div><div style="padding:5px"><div style="height:6px;background:#eee;border-radius:3px;margin-bottom:3px"></div><div style="font-size:9px;color:#e85d26;font-weight:600">${[240,185,130,98][i]}+ sold</div></div></div>`).join('')}</div></div>`,

  arrivals: `<div><div style="display:flex;justify-content:space-between;margin-bottom:8px"><span style="font-size:12px;font-weight:700;color:#333">✨ New Arrivals</span><span style="font-size:10px;color:#e85d26">See all →</span></div><div style="display:flex;gap:8px;overflow:hidden">${Array(5).fill(0).map((_,i)=>`<div style="flex-shrink:0;width:80px;border-radius:8px;overflow:hidden;border:1px solid #eee"><div style="height:70px;background:${['#f0e8ff','#e8fff0','#fff0e8','#e8f0ff','#ffe8f0'][i]};display:flex;align-items:center;justify-content:center;font-size:22px">✨</div><div style="padding:4px"><span style="background:#e85d2615;color:#e85d26;font-size:8px;font-weight:700;padding:1px 5px;border-radius:8px">New</span><div style="height:6px;background:#eee;border-radius:3px;margin-top:3px"></div></div></div>`).join('')}</div></div>`,

  brandLogos: `<div><div style="font-size:12px;font-weight:700;color:#333;margin-bottom:8px">Shop by Brand</div><div style="display:flex;flex-wrap:wrap;gap:8px">${['Nike','Adidas','Zara','H&M','Puma','Gucci'].map(b=>`<div style="display:flex;flex-direction:column;align-items:center;border:1px solid #eee;border-radius:8px;padding:8px 12px;min-width:60px"><div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#e85d26,#f59e0b);color:#fff;font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:4px">${b[0]}</div><div style="font-size:9px;font-weight:600;color:#666">${b}</div></div>`).join('')}</div></div>`,

  announcement: `<div style="background:#111;color:#fff;padding:8px 16px;display:flex;align-items:center;gap:12px;border-radius:6px;overflow:hidden"><div style="flex:1;overflow:hidden"><div style="white-space:nowrap;animation:none;font-size:12px">🎉 Welcome to Ramo Store! Free shipping on orders over 500 EGP &nbsp;·&nbsp; Welcome to Ramo Store! Free shipping on orders over 500 EGP</div></div><span style="font-size:16px;opacity:.6;cursor:default">×</span></div>`,

  flash: `<div style="background:linear-gradient(135deg,#c0392b,#e74c3c);border-radius:8px;padding:12px 16px"><div style="display:flex;align-items:center;gap:10px"><span style="font-size:22px">⚡</span><div style="flex:1"><div style="font-size:14px;font-weight:800;color:#fff">Flash Sale</div><span style="background:rgba(255,255,255,.2);padding:1px 8px;border-radius:10px;font-size:12px;font-weight:700;color:#fff">20% OFF</span></div><div style="display:flex;gap:4px">${[['03','HRS'],['42','MIN'],['17','SEC']].map(([n,l])=>`<div style="background:rgba(0,0,0,.25);border-radius:6px;padding:4px 7px;text-align:center"><div style="font-size:16px;font-weight:800;color:#fff">${n}</div><div style="font-size:8px;color:rgba(255,255,255,.7)">${l}</div></div>`).join('<span style="color:rgba(255,255,255,.7);font-size:16px;font-weight:800;align-self:center">:</span>')}</div><span style="background:#fff;color:#c0392b;padding:6px 12px;border-radius:20px;font-size:11px;font-weight:800;cursor:default">Shop Now →</span></div></div>`,

  seasonal: `<div style="background:linear-gradient(135deg,#6c3483,#f9ca24);border-radius:8px;padding:16px 20px"><div style="display:flex;align-items:center;gap:16px"><div style="flex:1"><div style="font-size:16px;font-weight:800;color:#fff;margin-bottom:4px">🎄 Special Season</div><div style="font-size:11px;color:rgba(255,255,255,.85)">Limited-time offers for this season</div></div><div style="font-size:13px;font-weight:800;color:#fff">05d 12h 30m</div><span style="background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.4);color:#fff;padding:7px 14px;border-radius:20px;font-size:11px;font-weight:700;cursor:default">Shop Now →</span></div></div>`,

  spacer: `<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:50px;border:2px dashed #ddd;border-radius:8px;color:#ccc;gap:4px"><div style="font-size:16px">↕️</div><div style="font-size:10px;font-weight:600">24px vertical space</div></div>`,

  divider: `<div style="display:flex;align-items:center;gap:10px;padding:8px 0"><div style="flex:1;height:1px;background:#e5e7eb"></div><span style="font-size:12px;color:#ccc">➖</span><div style="flex:1;height:1px;background:#e5e7eb"></div></div>`,
};

let currentPreviewType = null;

function showPreview(type) {
  if (currentPreviewType === type) return;
  currentPreviewType = type;

  // Highlight active button
  document.querySelectorAll('.wp-btn').forEach(b => b.classList.remove('active'));
  event.currentTarget.classList.add('active');

  const info = WIDGET_INFO[type] || { title: type, desc: 'No description available.', tags: [] };
  const mockup = WIDGET_MOCKUPS[type] || `<div style="text-align:center;padding:20px;color:#999;font-size:13px">No visual preview for this widget.</div>`;

  const top = document.getElementById('wpPreviewTop');
  top.innerHTML = `
    <div class="wp-preview-title">${info.title}</div>
    <div class="wp-preview-desc">${info.desc}</div>
    <div class="wp-preview-tags">${info.tags.map(t=>`<span class="wp-preview-tag">${t}</span>`).join('')}</div>
    <div class="wp-preview-mockup">${mockup}</div>
  `;

  const bottom = document.getElementById('wpPreviewBottom');
  bottom.style.display = 'flex';
  document.getElementById('wpAddBtn').onclick = () => addSection(type);
}

function addSection(type) {
  sections.push({ ...(DEFAULTS[type] || { layout: type }) });
  document.getElementById('typePicker').style.display = 'none';
  currentPreviewType = null;
  renderAll();
  // auto-open the last card
  setTimeout(() => {
    const bodies = document.querySelectorAll('.tl-body');
    if (bodies.length) bodies[bodies.length-1].classList.add('open');
  }, 50);
}

document.getElementById('addSectionBtn').addEventListener('click', () => {
  const picker = document.getElementById('typePicker');
  const isOpen = picker.style.display !== 'none';
  picker.style.display = isOpen ? 'none' : 'block';
  if (!isOpen) { currentPreviewType = null; document.querySelectorAll('.wp-btn').forEach(b=>b.classList.remove('active')); }
});

// ── SAVE ───────────────────────────────────────────────────────────
async function saveTimeline() {
  const btn = document.getElementById('saveBtn');
  const status = document.getElementById('saveStatus');
  btn.disabled = true;
  btn.textContent = 'Saving…';
  status.textContent = '';
  status.style.color = 'var(--muted)';

  try {
    const res = await fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
      },
      body: JSON.stringify({ lang: LANG, payload: JSON.stringify(sections.map(s => { const c = Object.assign({}, s); delete c._productNames; return c; })) })
    });
    const data = await res.json();
    if (data.success) {
      status.textContent = '✓ Saved! Changes are live on the homepage.';
      status.style.color = 'var(--green)';
    } else {
      status.textContent = data.error || 'Error saving.';
      status.style.color = 'var(--red)';
    }
  } catch(e) {
    status.textContent = 'Network error: ' + e.message;
    status.style.color = 'var(--red)';
  }
  btn.disabled = false;
  btn.textContent = 'Save Timeline';
  setTimeout(() => { status.textContent = ''; }, 5000);
}

function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
  return String(s||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Flash Sale targeting panel ──────────────────────────────────────
function renderFlashTargeting(idx) {
  const sec = sections[idx];
  const el  = document.getElementById('flash-targeting-' + idx);
  if (!el) return;
  const applyTo = sec.applyTo || 'all';

  if (applyTo === 'all') {
    el.innerHTML = '<p style="font-size:12px;color:var(--muted);margin:0">The discount will apply to every product in the store.</p>';
    return;
  }

  if (applyTo === 'categories') {
    const selected = Array.isArray(sec.targetCategories) ? sec.targetCategories.map(Number) : [];
    let html = '<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px">';
    CATEGORIES.forEach(cat => {
      const checked = selected.includes(Number(cat.id)) ? 'checked' : '';
      html += `<label style="display:flex;align-items:center;gap:5px;font-size:12px;cursor:pointer;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:6px;padding:5px 10px">
        <input type="checkbox" value="${cat.id}" ${checked} onchange="flashToggleCategory(${idx},${cat.id},this.checked)" style="width:14px;height:14px"> ${escHtml(cat.name)}
      </label>`;
    });
    html += '</div>';
    if (selected.length === 0) html += '<p style="font-size:12px;color:#f59e0b;margin:0">⚠ No categories selected — discount won\'t apply to any product.</p>';
    else html += `<p style="font-size:12px;color:var(--muted);margin:0">${selected.length} categor${selected.length===1?'y':'ies'} selected.</p>`;
    el.innerHTML = html;
    return;
  }

  if (applyTo === 'products') {
    const selected = Array.isArray(sec.targetProductIds) ? sec.targetProductIds.map(Number) : [];
    let chips = selected.map(id => {
      const name = (sec._productNames || {})[id] || ('Product #' + id);
      return `<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(232,93,38,.15);border:1px solid rgba(232,93,38,.3);color:#e85d26;border-radius:20px;padding:3px 10px;font-size:12px">
        ${escHtml(name)} <button onclick="flashRemoveProduct(${idx},${id})" style="background:none;border:none;color:#e85d26;cursor:pointer;font-size:14px;line-height:1;padding:0">×</button>
      </span>`;
    }).join('');
    el.innerHTML = `
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px">${chips || '<span style="font-size:12px;color:var(--muted)">No products selected yet.</span>'}</div>
      <div style="display:flex;gap:8px;align-items:center">
        <input type="text" id="flash-psearch-${idx}" placeholder="Search products by name…" style="flex:1;font-size:13px" oninput="flashSearchProducts(${idx},this.value)">
      </div>
      <div id="flash-presults-${idx}" style="margin-top:6px;max-height:200px;overflow-y:auto;border:1px solid rgba(255,255,255,.1);border-radius:8px;display:none"></div>
      ${selected.length===0?'<p style="font-size:12px;color:#f59e0b;margin-top:8px">⚠ No products selected — discount won\'t apply to any product.</p>':''}
    `;
  }
}

function flashToggleCategory(idx, catId, checked) {
  if (!sections[idx].targetCategories) sections[idx].targetCategories = [];
  catId = Number(catId);
  if (checked) {
    if (!sections[idx].targetCategories.includes(catId)) sections[idx].targetCategories.push(catId);
  } else {
    sections[idx].targetCategories = sections[idx].targetCategories.filter(c => c !== catId);
  }
  renderFlashTargeting(idx);
}

function flashRemoveProduct(idx, prodId) {
  prodId = Number(prodId);
  if (!sections[idx].targetProductIds) sections[idx].targetProductIds = [];
  sections[idx].targetProductIds = sections[idx].targetProductIds.filter(p => p !== prodId);
  if (sections[idx]._productNames) delete sections[idx]._productNames[prodId];
  renderFlashTargeting(idx);
}

let _flashSearchTimer = null;
function flashSearchProducts(idx, q) {
  clearTimeout(_flashSearchTimer);
  const resultsEl = document.getElementById('flash-presults-' + idx);
  if (!resultsEl) return;
  if (q.trim().length < 1) { resultsEl.style.display = 'none'; return; }
  _flashSearchTimer = setTimeout(async () => {
    resultsEl.style.display = 'block';
    resultsEl.innerHTML = '<div style="padding:10px;font-size:12px;color:var(--muted)">Searching…</div>';
    try {
      const resp = await fetch(`/admin/products/search?q=${encodeURIComponent(q.trim())}`);
      const data = await resp.json();
      if (!data.length) { resultsEl.innerHTML = '<div style="padding:10px;font-size:12px;color:var(--muted)">No products found.</div>'; return; }
      const selected = Array.isArray(sections[idx].targetProductIds) ? sections[idx].targetProductIds.map(Number) : [];
      resultsEl.innerHTML = data.map(p => {
        const isSel = selected.includes(Number(p.id));
        return `<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px">
          <span>${escHtml(p.name)}</span>
          <button onclick="flashAddProduct(${idx},${p.id},'${escAttr(p.name)}')" style="background:${isSel?'rgba(34,197,94,.15)':'rgba(232,93,38,.15)'};border:1px solid ${isSel?'rgba(34,197,94,.3)':'rgba(232,93,38,.3)'};color:${isSel?'#22c55e':'#e85d26'};border-radius:6px;padding:3px 10px;font-size:12px;cursor:pointer">${isSel?'✓ Added':'+ Add'}</button>
        </div>`;
      }).join('');
    } catch(e) {
      resultsEl.innerHTML = '<div style="padding:10px;font-size:12px;color:#ef4444">Search failed.</div>';
    }
  }, 300);
}

function flashAddProduct(idx, prodId, prodName) {
  prodId = Number(prodId);
  if (!sections[idx].targetProductIds) sections[idx].targetProductIds = [];
  if (!sections[idx]._productNames) sections[idx]._productNames = {};
  if (!sections[idx].targetProductIds.includes(prodId)) {
    sections[idx].targetProductIds.push(prodId);
    sections[idx]._productNames[prodId] = prodName;
  }
  renderFlashTargeting(idx);
  const inp = document.getElementById('flash-psearch-' + idx);
  if (inp) flashSearchProducts(idx, inp.value);
}

// ── INIT ───────────────────────────────────────────────────────────
renderAll();
</script>
@endsection
