@extends('layouts.app')
@section('title', session('locale', 'en') === 'ar' ? 'تسوّق — Ramo Store' : 'Shop — Ramo Store')

@php
  $shopRtl = session('locale', 'en') === 'ar';
  $shopCopy = $shopRtl ? [
    'home' => 'الرئيسية', 'shop' => 'تسوّق', 'search' => 'دوّر على منتجات…',
    'filters' => 'الفلاتر والتصنيفات', 'closeFilters' => 'اقفل الفلاتر',
    'loadingProducts' => 'بنحمّل المنتجات…', 'noProducts' => 'ملقيناش منتجات',
    'noProductsHint' => 'جرّب تدوّر بكلمة تانية أو اتصفح كل التصنيفات.',
    'clearFilters' => 'امسح الفلاتر', 'loadingMore' => 'بنحمّل منتجات أكتر…',
    'allSeen' => 'شفت كل المنتجات', 'categories' => 'التصنيفات',
    'allProducts' => 'كل المنتجات', 'brands' => 'الماركات', 'sortBy' => 'ترتيب حسب',
    'latest' => 'الأحدث', 'lowHigh' => 'السعر: من الأقل للأعلى',
    'highLow' => 'السعر: من الأعلى للأقل', 'products' => 'منتج',
    'loadError' => 'حصلت مشكلة وإحنا بنحمّل المنتجات. حدّث الصفحة وجرب تاني.',
    'unavailable' => 'المنتجات مش متاحة دلوقتي',
  ] : [];
  $shopJsCopy = $shopRtl ? $shopCopy : [
    'filters' => 'Filters & Categories', 'closeFilters' => 'Close Filters',
    'products' => 'product', 'productsPlural' => 'products',
    'loadError' => 'Products could not be loaded. Please refresh and try again.',
    'unavailable' => 'Products unavailable',
  ];
@endphp

@push('styles')
<style>
/* Shop results use their own presentation for the shared product markup. */
.shop-results-grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(min(220px,100%),1fr));
  align-items:start;gap:16px;min-inline-size:0;
}
.shop-product-card.product-card{
  display:grid;grid-template-columns:minmax(0,1fr);min-inline-size:0;
  border:1px solid #e9e9e9;border-radius:14px;background:var(--c-white);
  box-shadow:0 2px 8px #1111;
}
.shop-product-card .pc-media{
  position:relative;isolation:isolate;min-inline-size:0;
  aspect-ratio:1.18;background:#f5f5f4;
}
.shop-product-card .product-card-img{position:absolute;inset:0;aspect-ratio:auto;background:transparent}
.shop-product-card .product-card-img img{display:block;inline-size:100%;block-size:100%;object-fit:cover}
.shop-product-card.pc-out-of-stock img{opacity:.55}
.shop-product-card .badge-sale{
  position:absolute;z-index:1;inset-block-start:10px;inset-inline-start:8px;
  padding:3px 7px;border-radius:999px;background:#e8541e;color:#fff;
  font-size:.6875rem;font-weight:800;line-height:1.2;
}
.shop-product-card .badge-flash{background:#bf4519}
.shop-product-card .wish-btn{
  inset:auto;inset-block-start:2px;inset-inline-end:2px;
  inline-size:44px;block-size:44px;background:transparent;
  color:#111;font-size:18px;isolation:isolate;transform:none;
}
.shop-product-card .wish-btn::before{
  content:"";position:absolute;inset:6px;z-index:-1;
  background:#fff;border:1px solid #e9e9e9;border-radius:50%;box-shadow:0 1px 4px #1112;
}
.shop-product-card .wish-btn.wished{color:#c82333}
.shop-product-card .product-card-body{min-inline-size:0;padding:12px;gap:6px;text-align:start}
.shop-product-card .product-card-name{
  color:var(--c-dark);font-size:.875rem;font-weight:750;line-height:1.35;overflow-wrap:anywhere;
}
.shop-product-card .pc-option-area{min-inline-size:0;display:grid}
.shop-product-card :is(.pc-swatches,.pc-sizes){
  display:flex;flex-wrap:nowrap;overflow-x:auto;align-items:center;
  margin:0;padding:2px;gap:4px;scrollbar-width:thin;
}
.shop-product-card :is(.pc-option-area,.pc-swatches,.pc-sizes):not(:has(button:not([hidden]))){display:none}
.shop-product-card .pc-swatch{
  display:grid;place-items:center;inline-size:44px;block-size:44px;
  border:0;outline:0;background:none;
}
.shop-product-card .pc-swatch::before{
  content:"";inline-size:26px;block-size:26px;border-radius:50%;
  background:var(--pc-swatch-color);box-shadow:inset 0 0 0 1px #1113;
  outline:1.5px solid transparent;outline-offset:2px;
}
.shop-product-card .pc-swatch.selected::before{outline-color:var(--c-orange)}
.shop-product-card .pc-size{
  flex:none;min-block-size:36px;padding:4px 8px;border-radius:8px;
  background:#f5f5f4;font-size:.6875rem;white-space:nowrap;
}
.shop-product-card .pc-size.selected{background:var(--c-dark);color:#fff}
.shop-product-card .unavail{opacity:.45;cursor:not-allowed}
.shop-product-card .pc-selected{
  position:absolute;inline-size:1px;block-size:1px;margin:-1px;padding:0;
  overflow:hidden;clip-path:inset(50%);white-space:nowrap;
}
.shop-product-card .pc-option-link{display:none}
.shop-product-card .product-card-price{padding:0;align-items:baseline;gap:2px 6px}
.shop-product-card :is(.price-main,.price-old){min-inline-size:0;white-space:normal;overflow-wrap:anywhere}
.shop-product-card .price-main{font-size:1rem;font-weight:800}
.shop-product-card .price-old,.shop-product-card .pc-stock-label{font-size:.75rem;color:var(--c-mid)}
.shop-product-card .pc-actions{display:flex;flex-wrap:wrap;gap:6px;padding-block-start:2px}
.shop-product-card .pc-add-row{display:flex}
.shop-product-card .pc-actions > *{flex:1 1 88px}
.shop-product-card :is(.card-add-btn,.card-details-btn){
  display:flex;align-items:center;justify-content:center;
  min-block-size:44px;min-inline-size:0;margin:0;padding:6px;
  border-radius:8px;font-size:.6875rem;font-weight:750;line-height:1.2;text-align:center;
  letter-spacing:.02em;overflow-wrap:anywhere;
}
.shop-product-card .card-add-btn:disabled{opacity:.5;cursor:not-allowed}
.shop-product-card :is(button,a):focus-visible{outline:2px solid var(--c-orange);outline-offset:-2px}
.shop-product-card .pc-coupon-bar{margin:0;flex-wrap:wrap;line-height:1.25}
.shop-product-card :is(.pc-coupon-left,.pc-coupon-right){min-inline-size:0;white-space:normal;flex-wrap:wrap;overflow-wrap:anywhere}
.shop-product-card [hidden]{display:none!important}

@media(max-width:1100px){
  .shop-results-grid{grid-template-columns:repeat(auto-fill,minmax(min(190px,100%),1fr))}
}
@media(max-width:600px){
  .shop-results-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .shop-product-card .product-card-body{padding:9px}
  .shop-product-card .product-card-name{font-size:.82rem}
  .shop-product-card .pc-size{min-inline-size:40.25px;min-block-size:40.25px;padding:4.6px 8px;font-size:.69rem}
  .shop-product-card .price-main{font-size:1rem}
  .shop-product-card .price-old,.shop-product-card .pc-stock-label{font-size:.69rem}
  .shop-product-card :is(.card-add-btn,.card-details-btn){
    min-block-size:40.25px;padding:5px 4px;font-size:.575rem;
  }
}
@media(max-width:480px){
  .shop-toolbar .search-bar{display:none}
  .shop-toolbar{flex-wrap:wrap;gap:6px;margin-bottom:12px}
  .shop-results-grid.shop-mobile-layout-horizontal{grid-template-columns:minmax(0,1fr)}
  .shop-mobile-layout-horizontal .shop-product-card{grid-template-columns:minmax(0,43.2fr) minmax(0,56.8fr)}
  .shop-mobile-layout-horizontal .shop-product-card .pc-media{aspect-ratio:auto;min-block-size:120px}
  .shop-mobile-layout-horizontal .shop-product-card .product-card-body{padding:8px 10px;gap:4px}
  .shop-mobile-layout-horizontal .shop-product-card :is(.pc-swatches,.pc-sizes){padding-block:1px}
  .shop-mobile-layout-horizontal .shop-product-card .pc-swatch{block-size:42px}
  .shop-mobile-layout-horizontal .shop-product-card .pc-size{min-block-size:38px}
  .shop-mobile-layout-horizontal .shop-product-card .pc-actions{padding-block-start:0}
  .shop-mobile-layout-horizontal .shop-product-card .pc-actions > *{flex:1 1 0;min-inline-size:0}
}

/* ══════════════════════════════════════════════
   FOLDABLE WIDGET SECTIONS
══════════════════════════════════════════════ */
.widget {
  margin-bottom: 4px;
}

/* Widget header — the clickable fold toggle */
.widget-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 4px; cursor: pointer; user-select: none;
  border-radius: 8px; transition: background .13s;
  gap: 8px;
}
.widget-header:hover { background: var(--c-tag); padding-left: 8px; padding-right: 8px; }

.widget-header-left {
  display: flex; align-items: center; gap: 7px;
  font-size: 10px; font-weight: 800; letter-spacing: .1em;
  text-transform: uppercase; color: var(--c-mid);
}
.widget-header-left svg { flex-shrink: 0; }

.widget-fold-icon {
  display: flex; align-items: center; justify-content: center;
  width: 18px; height: 18px; border-radius: 5px;
  background: var(--c-bg); border: 1px solid var(--c-light);
  color: var(--c-mid); flex-shrink: 0; transition: background .13s;
}
.widget-header:hover .widget-fold-icon { background: var(--c-light); }
.widget-fold-icon svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display:block; }
.widget.collapsed .widget-fold-icon svg { transform: rotate(-90deg); }

/* Widget body — the foldable content */
.widget-body {
  overflow: hidden;
  max-height: 1200px;
  transition: max-height .35s cubic-bezier(.4,0,.2,1), opacity .25s ease;
  opacity: 1;
}
.widget.collapsed .widget-body {
  max-height: 0;
  opacity: 0;
}

/* ── Category items ──────────────────────────────────────── */
.cat-all-pill {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 13px; border-radius: 9px; font-size: 13.5px;
  font-weight: 600; color: var(--c-dark); margin-bottom: 4px;
  transition: all .15s; text-decoration: none; background: transparent;
}
.cat-all-pill:hover { background: var(--c-tag); }
.cat-all-pill.active { background: var(--c-dark); color: #fff; }
.cat-all-pill.active .cat-count-badge { background: rgba(255,255,255,.18); color: #fff; }

.cat-count-badge {
  font-size: 10px; font-weight: 700;
  background: var(--c-bg); color: var(--c-mid);
  padding: 2px 7px; border-radius: 20px;
  border: 1px solid var(--c-light);
  min-width: 24px; text-align: center; flex-shrink: 0;
}

.cat-parent-item { margin-bottom: 2px; }

.cat-parent-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 9px 13px; border-radius: 9px;
  background: transparent; border: none; cursor: pointer;
  font-family: inherit; font-size: 13.5px; font-weight: 600;
  color: var(--c-dark); text-align: left; transition: all .15s; text-decoration: none;
}
.cat-parent-btn:hover { background: var(--c-tag); }
.cat-parent-btn.active { background: var(--c-orange); color: #fff; }
.cat-parent-btn.active .cat-count-badge { background: rgba(255,255,255,.25); color: #fff; border-color: transparent; }
.cat-parent-btn.parent-open:not(.active) { background: var(--c-tag); }

.cat-parent-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.cat-chevron {
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; transition: all .2s; color: var(--c-mid);
}
.cat-chevron svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display: block; }

.cat-children-wrap {
  overflow: hidden; max-height: 0;
  transition: max-height .3s cubic-bezier(.4,0,.2,1);
}
.cat-children-wrap.open { max-height: 600px; }

.cat-children-inner {
  padding: 3px 0 6px 12px; margin-left: 13px;
  border-left: 2px solid var(--c-light);
}

.cat-child-link {
  display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 7px 11px; border-radius: 8px;
  font-size: 13px; font-weight: 500; color: var(--c-mid);
  text-decoration: none; transition: all .13s; margin-bottom: 1px;
}
.cat-child-link::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%;
  background: var(--c-light); flex-shrink: 0; transition: background .13s;
}
.cat-child-link:hover { background: var(--c-tag); color: var(--c-dark); }
.cat-child-link:hover::before { background: #ccc; }
.cat-child-link.active {
  background: #fff5f2; color: var(--c-orange); font-weight: 700;
  border: 1px solid #fde0d5;
}
.cat-child-link.active::before { background: var(--c-orange); }
.cat-child-link.active .cat-count-badge { background: #fde0d5; color: var(--c-orange); border-color: transparent; }

/* ── Divider ──────────────────────────────────────────────── */
.widget-divider { border: none; border-top: 1.5px solid var(--c-light); margin: 8px 0; }

/* ── Toolbar active strip ─────────────────────────────────── */
.active-cat-strip {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fff5f2; border: 1px solid #fde0d5;
  color: var(--c-orange); font-size: 12px; font-weight: 700;
  padding: 4px 10px 4px 8px; border-radius: 20px;
}
.active-cat-strip a {
  display: flex; align-items: center; color: var(--c-orange);
  opacity: .7; transition: opacity .13s; margin-left: 4px;
}
.active-cat-strip a:hover { opacity: 1; }

/* ── Price range widget ────────────────────────────────────── */
.shop-page #widget-price .widget-header{padding:8px 2px;background:transparent}
.shop-page #widget-price .widget-header:hover{padding-left:6px;padding-right:6px;background:#f7f7f7}
.shop-price-form{padding:2px 2px 6px}
.shop-price-range-labels{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:2px 2px 8px;color:#686868;font-size:11px;font-weight:650;font-variant-numeric:tabular-nums}
.shop-price-sliders{position:relative;height:28px;margin:0 9px 8px}
.shop-price-track{position:absolute;top:11px;left:0;right:0;height:5px;border-radius:99px;background:#e9e9e9}
.shop-price-track-fill{position:absolute;top:0;height:100%;left:var(--range-start,0%);right:calc(100% - var(--range-end,100%));border-radius:99px;background:var(--c-orange)}
.shop-price-sliders input[type="range"]{position:absolute;inset:0;width:100%;height:28px;margin:0;appearance:none;-webkit-appearance:none;background:transparent;pointer-events:none}
.shop-price-sliders input[type="range"]::-webkit-slider-runnable-track{height:28px;background:transparent}
.shop-price-sliders input[type="range"]::-moz-range-track{height:28px;background:transparent}
.shop-price-sliders input[type="range"]::-webkit-slider-thumb{appearance:none;-webkit-appearance:none;width:18px;height:18px;margin-top:5px;border-radius:50%;background:#fff;border:2px solid var(--c-orange);box-shadow:0 1px 4px rgba(0,0,0,.16);cursor:grab;pointer-events:auto}
.shop-price-sliders input[type="range"]::-moz-range-thumb{width:14px;height:14px;border-radius:50%;background:#fff;border:2px solid var(--c-orange);box-shadow:0 1px 4px rgba(0,0,0,.16);cursor:grab;pointer-events:auto}
.shop-price-sliders #shop-min-range{z-index:2}
.shop-price-sliders #shop-max-range{z-index:1}
.shop-price-inputs{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.shop-price-inputs label{display:flex;min-width:0;flex-direction:column;gap:5px;color:#6d6d6d;font-size:10px;font-weight:700}
.shop-price-input-shell{display:flex;align-items:center;gap:6px;min-width:0;padding:0 10px;border:1px solid #dedede;border-radius:10px;background:#fff;transition:border-color .15s,box-shadow .15s}
.shop-price-input-shell:focus-within{border-color:var(--c-orange);box-shadow:0 0 0 3px rgba(238,91,34,.1)}
.shop-price-input-shell input{width:100%;min-width:0;padding:9px 0;border:0;background:transparent;color:#191919;font:inherit;font-size:13px;font-weight:650;outline:0}
.shop-price-input-shell span{flex:0 0 auto;color:#888;font-size:9px;font-weight:750}
.shop-price-context{margin:9px 2px 0;color:#8a8a8a;font-size:10px;line-height:1.4}
.shop-price-submit{display:none}
.shop-price-submit:active,.shop-filter-apply:active { transform:scale(.98); }

/* ── Mobile filter alert / bottom sheet ─────────────────────── */
.shop-filter-alert { display:contents; }
.shop-filter-backdrop,.shop-filter-panel-header,.shop-filter-panel-actions { display:none; }
.shop-filter-panel-scroll { display:contents; }

/* ── Mobile toggle ────────────────────────────────────────── */
.shop-filter-toggle {
  display: none; align-items: center; gap: 7px;
  padding: 10px 16px; border-radius: 50px;
  background: var(--c-white); border: 1.5px solid var(--c-light);
  font-size: 13.5px; font-weight: 600; color: var(--c-dark);
  margin-bottom: 16px; cursor: pointer; transition: all .15s;
}
.shop-filter-toggle:hover { background: var(--c-tag); }

/* Airbnb-inspired product filters: a compact chip rail and a centered modal. */
.shop-layout{grid-template-columns:minmax(0,1fr)!important;gap:0!important}
.shop-filter-toggle{display:inline-flex;align-items:center;gap:8px;margin:0;padding:10px 16px;border:1px solid #d7d7d7;border-radius:999px;background:#fff;font-size:13px;font-weight:700;box-shadow:0 1px 2px rgba(0,0,0,.05)}
.shop-filter-toggle:hover{background:#f7f7f7;border-color:#999}
.shop-filter-alert{display:none;position:fixed;inset:0;z-index:10050}
.shop-filter-alert.open{display:block}
.shop-filter-backdrop{display:block;position:absolute;inset:0;width:100%;height:100%;padding:0;border:0;background:rgba(0,0,0,.38);backdrop-filter:blur(2px)}
.shop-filter-panel{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:min(680px,calc(100vw - 32px));max-height:min(82vh,760px);display:flex;flex-direction:column;overflow:hidden;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:24px;box-shadow:0 24px 80px rgba(0,0,0,.24)}
.shop-filter-panel-header{display:flex;align-items:center;justify-content:space-between;gap:14px;flex:0 0 auto;padding:18px 24px;border-bottom:1px solid #eee}
.shop-filter-kicker{display:block;color:var(--c-orange);font-size:9px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.shop-filter-panel-header h2{margin:2px 0 0;font-size:20px;line-height:1.25}
.shop-filter-close{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:38px;height:38px;border:1px solid #e5e5e5;border-radius:50%;background:#fff;color:#222}
.shop-filter-panel-scroll{display:block;min-height:0;flex:1 1 auto;overflow-y:auto;overscroll-behavior:contain;padding:8px 24px 18px}
.shop-filter-panel-scroll .sidebar{position:static;width:100%;max-height:none;overflow:visible;padding:0;border:0;border-radius:0;background:#fff;box-shadow:none}
.shop-filter-panel-scroll .widget-header{min-height:48px;box-sizing:border-box}
.shop-filter-panel-scroll .widget-divider{margin:14px 0}
.shop-legacy-category-filters{display:none}
.shop-category-search{display:flex;align-items:center;gap:9px;margin:8px 0 16px;padding:0 14px;height:44px;border:1px solid #ddd;border-radius:12px;color:#777}
.shop-category-search:focus-within{border-color:#222;box-shadow:0 0 0 1px #222}
.shop-category-search input{width:100%;border:0;outline:0;background:transparent;font:inherit;font-size:13px;color:#222}
.shop-category-bubbles{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(190px,100%),1fr));align-items:start;gap:10px 14px}
.shop-category-family{display:flex;flex-direction:column;gap:8px}
.shop-category-parent-row{display:flex;align-items:center;gap:8px}
.shop-category-expand{display:flex;align-items:center;justify-content:center;flex:0 0 36px;width:36px;height:36px;border:1px solid #e1e1e1;border-radius:50%;background:#fff;color:#444;transition:transform .16s,background .16s}
.shop-category-expand:hover{background:#f6f6f6;border-color:#aaa}
.shop-category-family.expanded .shop-category-expand{transform:rotate(180deg)}
.shop-category-family:not(.expanded):not(.search-expanded) .shop-child-bubbles{display:none}
.shop-category-family[hidden],.shop-category-bubble[hidden]{display:none!important}
.shop-category-bubble{display:inline-flex;align-items:center;justify-content:flex-start;gap:9px;min-height:40px;width:max-content;max-width:100%;padding:0 14px;border:1px solid #ddd;border-radius:999px;background:#fff;color:#292929;font-size:12px;font-weight:650;cursor:pointer;transition:background .15s,border-color .15s,box-shadow .15s}
.shop-category-bubble:hover{border-color:#777}
.shop-category-bubble.selected{border-color:#222;background:#222;color:#fff;box-shadow:0 2px 6px rgba(0,0,0,.12)}
.shop-category-bubble input{position:absolute;opacity:0;pointer-events:none}
.shop-category-bubble small{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#f1f1f1;color:#555;font-size:10px;font-weight:700}
.shop-category-bubble.selected small{background:rgba(255,255,255,.18);color:#fff}
.shop-child-bubbles{display:flex;flex-wrap:wrap;gap:8px;padding:0 0 0 20px}
.shop-category-bubble--child{min-height:34px;padding:0 12px;border-color:#e6e6e6;background:#fafafa;font-size:11px;font-weight:550}
.shop-child-bubbles{display:flex;flex-wrap:wrap;gap:7px;padding:0 0 0 8px}
@media(max-width:430px){.shop-category-bubbles{grid-template-columns:minmax(0,1fr);gap:8px}}
.search-match-child .shop-category-bubble[data-bubble-label]{opacity:.7}
.shop-category-chips{margin-top:14px}
.shop-filter-panel-actions{display:flex;align-items:center;gap:18px;flex:0 0 auto;padding:14px 24px;border-top:1px solid #eee;background:#fff}
.shop-filter-apply{flex:0 0 auto;min-height:46px;padding:0 24px;border:0;border-radius:10px;background:#171717;color:#fff;font:inherit;font-size:13px;font-weight:800;cursor:pointer}
.shop-filter-clear{margin-left:auto;color:#555;font-size:13px;font-weight:700;text-decoration:underline}
.shop-category-chips{
  display:flex;align-items:center;flex-wrap:nowrap;gap:9px;min-inline-size:0;
  overflow-x:auto;overflow-y:hidden;overscroll-behavior-inline:contain;
  -webkit-overflow-scrolling:touch;scrollbar-width:none;
  margin:0 0 20px;padding:3px 1px 8px;
}
.shop-category-chips::-webkit-scrollbar{display:none}
.shop-category-chips--wrap{flex-wrap:wrap;overflow:visible}
.shop-page[data-filter-price="0"] #widget-price,
.shop-page[data-filter-categories="0"] #widget-categories,
.shop-page[data-filter-brands="0"] #widget-brands,
.shop-page[data-filter-sort="0"] #widget-sort{display:none}
.shop-page[data-filter-price="0"] #widget-price + .widget-divider,
.shop-page[data-filter-categories="0"] #widget-categories + .widget-divider,
.shop-page[data-filter-brands="0"] #widget-brands + .widget-divider{display:none}
.shop-category-chip{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;min-height:38px;padding:0 16px;border:1px solid #dedede;border-radius:999px;background:#fff;color:#333;font-size:12px;font-weight:600;white-space:nowrap;transition:border-color .15s,background .15s}
.shop-category-chip:hover{border-color:#222;background:#fafafa}
.shop-category-chip.active{border-color:#222;background:#222;color:#fff}
.shop-toolbar{margin-bottom:16px}
body.shop-filter-open{overflow:hidden}

@media (max-width: 860px) {
  .shop-layout { grid-template-columns: minmax(0, 1fr); }
  .shop-filter-toggle { display: inline-flex; }

  .shop-page .shop-filter-alert {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10050;
  }
  .shop-page .shop-filter-alert.open { display: block; }
  .shop-page .shop-filter-backdrop {
    display: block;
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(17,17,17,.48);
    backdrop-filter: blur(2px);
    cursor: pointer;
  }
  .shop-page .shop-filter-panel {
    position: absolute;
    left: max(10px, env(safe-area-inset-left));
    right: max(10px, env(safe-area-inset-right));
    bottom: calc(var(--mobile-nav-height, 0px) + var(--mobile-nav-viewport-offset, 0px) + max(10px, env(safe-area-inset-bottom)));
    max-height: calc(100dvh - var(--mobile-nav-height, 0px) - var(--mobile-nav-viewport-offset, 0px) - 20px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--c-white);
    border: 1px solid rgba(17,17,17,.1);
    border-radius: 22px;
    box-shadow: 0 18px 60px rgba(0,0,0,.24);
    animation: shop-filter-alert-in .2s cubic-bezier(.23,1,.32,1);
  }
  .shop-page .shop-filter-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex: 0 0 auto;
    padding: 17px 18px 13px;
    border-bottom: 1px solid var(--c-light);
  }
  .shop-page .shop-filter-kicker { display:block; color:var(--c-orange); font-size:9px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
  .shop-page .shop-filter-panel-header h2 { margin-top:2px; color:var(--c-dark); font-size:19px; line-height:1.25; }
  .shop-page .shop-filter-close { display:flex; align-items:center; justify-content:center; flex:0 0 auto; width:36px; height:36px; border:1px solid var(--c-light); border-radius:50%; background:var(--c-bg); color:var(--c-dark); cursor:pointer; }
  .shop-page .shop-filter-panel-scroll {
    display: block;
    min-height: 0;
    flex: 1 1 auto;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    scrollbar-gutter: stable;
    padding: 5px 18px 14px;
  }
  .shop-page .shop-filter-panel-scroll .sidebar {
    display: block;
    position: static;
    width: 100%;
    max-height: none;
    overflow: visible;
    padding: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
  }
  .shop-page .shop-filter-panel-scroll .widget-header { min-height: 44px; box-sizing:border-box; }
  .shop-page .shop-filter-panel-scroll .widget-divider { margin: 12px 0; }
  .shop-page .shop-filter-panel-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
    padding: 12px 18px calc(12px + env(safe-area-inset-bottom));
    border-top: 1px solid var(--c-light);
    background: rgba(255,255,255,.98);
  }
  .shop-page .shop-filter-apply { flex: 1; min-height: 42px; border:0; border-radius:10px; background:var(--c-orange); color:#fff; font:inherit; font-size:13px; font-weight:800; cursor:pointer; }
  .shop-page .shop-filter-clear { flex:0 0 auto; padding:8px 3px; color:var(--c-mid); font-size:12px; font-weight:700; text-decoration:underline; }
  body.shop-filter-open { overflow: hidden; }
}

@keyframes shop-filter-alert-in { from { opacity:0; transform:translateY(12px) scale(.985); } to { opacity:1; transform:translateY(0) scale(1); } }
@media (prefers-reduced-motion: reduce) { .shop-filter-panel { animation:none !important; } }

/* ── Arabic RTL shop page and phone search ────────────────── */
.shop-mobile-search{display:none;}
.shop-page[dir="rtl"]{direction:rtl;text-align:right;}
.shop-page[dir="rtl"] .breadcrumb,.shop-page[dir="rtl"] .shop-toolbar{direction:rtl;}
.shop-page[dir="rtl"] .cat-parent-btn{text-align:right;}
.shop-page[dir="rtl"] .cat-children-inner{padding:3px 12px 6px 0;margin-left:0;margin-right:13px;border-left:0;border-right:2px solid var(--c-light);}
.shop-page[dir="rtl"] .active-cat-strip{padding:4px 8px 4px 10px;}
.shop-page[dir="rtl"] .active-cat-strip a{margin-left:0;margin-right:4px;}
.shop-page[dir="rtl"] .widget-header-left{letter-spacing:0;text-transform:none;}
@media (max-width: 860px) {
  .shop-mobile-search{display:block;margin:0 0 12px;}
  .shop-mobile-search form{display:flex;align-items:center;gap:8px;width:100%;padding:7px 8px 7px 12px;border:1.5px solid var(--c-light);border-radius:13px;background:#fff;box-sizing:border-box;box-shadow:0 3px 12px rgba(24,24,24,.04);}
  .shop-mobile-search input{min-width:0;flex:1;border:0;outline:0;background:transparent;font:inherit;font-size:14px;color:var(--c-dark);padding:7px 3px;}
  .shop-mobile-search button{width:38px;height:38px;border:0;border-radius:10px;background:var(--c-dark);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 auto;}
  .shop-page[dir="rtl"] .shop-mobile-search form{direction:rtl;}
  .shop-page[dir="rtl"] .shop-mobile-search input{text-align:right;}
}

</style>
@endpush

@section('content')
@php
  $showFilterPanel = $shopDisplay['filters_enabled'] && (
    $shopDisplay['filter_price'] || $shopDisplay['filter_categories']
    || ($shopDisplay['filter_brands'] && $allBrands->isNotEmpty()) || $shopDisplay['filter_sort']
  );
@endphp
<div class="page shop-page" @if($shopRtl) lang="ar" dir="rtl" @endif
  data-filter-price="{{ (int) $shopDisplay['filter_price'] }}"
  data-filter-categories="{{ (int) $shopDisplay['filter_categories'] }}"
  data-filter-brands="{{ (int) $shopDisplay['filter_brands'] }}"
  data-filter-sort="{{ (int) $shopDisplay['filter_sort'] }}">

  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $shopRtl ? $shopCopy['home'] : 'Home' }}</a><span>/</span>
    <a href="{{ route('shop') }}">{{ $shopRtl ? $shopCopy['shop'] : 'Shop' }}</a>
    @if($activeCategoryId)
      @php
        $allCatsFlat  = $parentCats->merge($childCats->flatten());
        $activeCatObj = $allCatsFlat->firstWhere('id', $activeCategoryId);
        $activeCatName = \App\Support\StorefrontLabels::category($activeCatObj->name ?? '', $shopRtl);
        $isChildActive = $activeCatObj && $activeCatObj->parent > 0;
        if ($isChildActive) { $parentCatObj = $parentCats->firstWhere('id', $activeCatObj->parent); }
      @endphp
      @if($isChildActive && isset($parentCatObj))
        <span>/</span>
        <a href="{{ route('shop', ['category' => $parentCatObj->id]) }}">{{ \App\Support\StorefrontLabels::category($parentCatObj->name, $shopRtl) }}</a>
      @endif
      <span>/</span><strong>{{ $activeCatName }}</strong>
    @endif
    @if($activeBrandName ?? false)<span>/</span><strong>{{ $activeBrandName }}</strong>@endif
    @if(request('search'))<span>/</span><span>"{{ request('search') }}"</span>@endif
  </div>

  <div class="shop-mobile-search">
    <form method="GET" action="{{ route('shop') }}">
      @foreach(request()->except(['search', 'page', 'category', 'categories']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
      @endforeach
      @foreach($selectedCategoryIds ?? [] as $selectedCategoryId)<input type="hidden" name="categories[]" value="{{ $selectedCategoryId }}">@endforeach
      <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $shopRtl ? $shopCopy['search'] : 'Search products…' }}" aria-label="{{ $shopRtl ? $shopCopy['search'] : 'Search products' }}">
      <button type="submit" aria-label="{{ $shopRtl ? 'دوّر' : 'Search' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="17" height="17"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16" y2="16"/></svg>
      </button>
    </form>
  </div>

  @if($showFilterPanel)
  <button type="button" class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()" aria-expanded="false" aria-controls="shop-filter-alert">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    {{ $shopRtl ? $shopCopy['filters'] : 'Filters & Categories' }}
  </button>
  @endif

  @if($shopDisplay['category_layout'] !== 'hidden')
  <nav class="shop-category-chips {{ $shopDisplay['category_layout'] === 'wrap' ? 'shop-category-chips--wrap' : '' }}" aria-label="{{ $shopRtl ? 'تصفّح التصنيفات' : 'Browse product categories' }}">
    @php $allShopCategories = $parentCats->merge($childCats->flatten())->keyBy('id'); @endphp
    <a class="shop-category-chip {{ empty($selectedCategoryIds) ? 'active' : '' }}" href="{{ route('shop', array_filter(request()->except(['category','categories','page']))) }}">{{ $shopRtl ? $shopCopy['allProducts'] : 'All products' }}</a>
    @foreach($selectedCategoryIds ?? [] as $selectedChipId)
      @php $selectedChipCategory = $allShopCategories->get($selectedChipId); $remainingSelected = array_values(array_filter($selectedCategoryIds, fn($id) => (int)$id !== (int)$selectedChipId)); @endphp
      @if($selectedChipCategory)
        <a class="shop-category-chip active" href="{{ route('shop', array_merge(request()->except(['category','categories','page']), $remainingSelected ? ['categories' => $remainingSelected] : [])) }}">{{ \App\Support\StorefrontLabels::category($selectedChipCategory->name, $shopRtl) }} <span aria-hidden="true">×</span></a>
      @endif
    @endforeach
    @php $remainingChipSlots = max(0, $shopDisplay['category_limit'] - count($selectedCategoryIds ?? [])); @endphp
    @foreach($parentCats as $chipCategory)
      @if(!empty($shopDisplay['category_ids']) && !in_array((int) $chipCategory->id, $shopDisplay['category_ids'], true))
        @continue
      @endif
      @if(!in_array((int)$chipCategory->id, $selectedCategoryIds ?? [], true))
        @if($remainingChipSlots > 0)
          @php $nextSelected = array_values(array_unique(array_merge($selectedCategoryIds ?? [], [(int)$chipCategory->id]))); $remainingChipSlots--; @endphp
          <a class="shop-category-chip" href="{{ route('shop', array_merge(request()->except(['category','categories','page']), ['categories' => $nextSelected])) }}">{{ \App\Support\StorefrontLabels::category($chipCategory->name, $shopRtl) }}</a>
        @endif
      @endif
    @endforeach
  </nav>
  @endif

  <div class="shop-layout">

    {{-- ══ SIDEBAR / MOBILE FILTER ALERT ═══════════════════════ --}}
    @if($showFilterPanel)
    <div class="shop-filter-alert" id="shop-filter-alert" aria-hidden="true">
      <button type="button" class="shop-filter-backdrop" onclick="closeShopFilter()" aria-label="{{ $shopRtl ? 'اقفل نافذة الفلاتر' : 'Close filters' }}"></button>
      <section class="shop-filter-panel" role="dialog" aria-modal="true" aria-labelledby="shop-filter-title" tabindex="-1">
        <header class="shop-filter-panel-header">
          <div>
            <span class="shop-filter-kicker">{{ $shopRtl ? 'تصفية المنتجات' : 'REFINE PRODUCTS' }}</span>
            <h2 id="shop-filter-title">{{ $shopRtl ? 'اختار اللي يناسبك' : 'Find what fits' }}</h2>
          </div>
          <button type="button" class="shop-filter-close" id="shop-filter-close" onclick="closeShopFilter()" aria-label="{{ $shopRtl ? 'اقفل الفلاتر' : 'Close filters' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="17" height="17"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </header>
        <div class="shop-filter-panel-scroll">
    <aside class="sidebar" id="shop-sidebar">

      {{-- ── Widget: Price Range ──────────────────────────────── --}}
      @php
        $shopPriceMinBound = max(0, (float) ($priceRange->min_price ?? 0));
        $shopPriceMaxBound = max($shopPriceMinBound, (float) ($priceRange->max_price ?? $shopPriceMinBound));
        $shopPriceMinValue = $minPrice ?? $shopPriceMinBound;
        $shopPriceMaxValue = $maxPrice ?? $shopPriceMaxBound;
      @endphp
      <div class="widget" id="widget-price">
        <div class="widget-header" onclick="toggleWidget('widget-price')" title="{{ $shopRtl ? 'اضغط عشان تقفل القسم' : 'Click to collapse' }}">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
            {{ $shopRtl ? 'نطاق السعر' : 'Price range' }}
          </span>
          <span class="widget-fold-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg></span>
        </div>
        <div class="widget-body">
          <form method="GET" action="{{ route('shop') }}" id="shop-price-form" class="shop-price-form" onsubmit="event.preventDefault(); applyShopPriceFilter();">
            @foreach(request()->except(['min_price', 'max_price', 'page', 'category', 'categories']) as $k => $v)
              <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            @foreach($selectedCategoryIds ?? [] as $selectedCategoryId)<input type="hidden" name="categories[]" value="{{ $selectedCategoryId }}">@endforeach
            <div class="shop-price-range-labels">
              <span>{{ number_format($shopPriceMinBound, 2) }} EGP</span>
              <span>{{ number_format($shopPriceMaxBound, 2) }} EGP</span>
            </div>
            <div class="shop-price-sliders" aria-label="{{ $shopRtl ? 'اختار أقل وأعلى سعر' : 'Choose minimum and maximum price' }}">
              <div class="shop-price-track" aria-hidden="true"><span class="shop-price-track-fill"></span></div>
              <input type="range" id="shop-min-range" min="{{ $shopPriceMinBound }}" max="{{ $shopPriceMaxBound }}" step="0.01" value="{{ $shopPriceMinValue }}" aria-label="{{ $shopRtl ? 'أقل سعر' : 'Minimum price' }}">
              <input type="range" id="shop-max-range" min="{{ $shopPriceMinBound }}" max="{{ $shopPriceMaxBound }}" step="0.01" value="{{ $shopPriceMaxValue }}" aria-label="{{ $shopRtl ? 'أعلى سعر' : 'Maximum price' }}">
            </div>
            <div class="shop-price-inputs">
              <label>
                <span>{{ $shopRtl ? 'من' : 'From' }}</span>
                <div class="shop-price-input-shell"><input type="number" id="shop-min-price" name="min_price" min="{{ $shopPriceMinBound }}" max="{{ $shopPriceMaxBound }}" step="0.01" value="{{ $minPrice !== null ? $minPrice : '' }}" placeholder="{{ number_format($shopPriceMinBound, 2) }}"><span>EGP</span></div>
              </label>
              <label>
                <span>{{ $shopRtl ? 'لحد' : 'To' }}</span>
                <div class="shop-price-input-shell"><input type="number" id="shop-max-price" name="max_price" min="{{ $shopPriceMinBound }}" max="{{ $shopPriceMaxBound }}" step="0.01" value="{{ $maxPrice !== null ? $maxPrice : '' }}" placeholder="{{ number_format($shopPriceMaxBound, 2) }}"><span>EGP</span></div>
              </label>
            </div>
            <p class="shop-price-context">{{ !empty($selectedCategoryIds) ? ($shopRtl ? 'نطاق السعر متاح ضمن التصنيفات المختارة' : 'Price range for the selected categories') : ($shopRtl ? 'نطاق السعر لكل المنتجات المتاحة' : 'Price range across available products') }}</p>
          </form>
        </div>
      </div>

      <hr class="widget-divider">

      {{-- ── Widget: Categories ───────────────────────────────── --}}
      <div class="widget" id="widget-categories">

        <div class="widget-header" onclick="toggleWidget('widget-categories')" title="{{ $shopRtl ? 'اضغط عشان تقفل القسم' : 'Click to collapse' }}">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            {{ $shopRtl ? $shopCopy['categories'] : 'Categories' }}
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          <label class="shop-category-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input id="shop-category-search" type="search" placeholder="{{ $shopRtl ? 'دوّر على قسم…' : 'Search categories…' }}" oninput="filterCategoryBubbles(this.value)"></label>
          <div class="shop-category-bubbles" id="shop-category-bubbles">
            @foreach($parentCats as $parent)
              @php $parentSelected = in_array((int)$parent->id, $selectedCategoryIds ?? [], true); @endphp
              <div class="shop-category-family {{ $parentSelected ? 'expanded' : '' }}" data-category-search="{{ strtolower($parent->name) }}">
                <div class="shop-category-parent-row">
                  <label class="shop-category-bubble {{ $parentSelected ? 'selected' : '' }}" data-bubble-label>
                    <input type="checkbox" class="shop-category-check" value="{{ $parent->id }}" {{ $parentSelected ? 'checked' : '' }} onchange="toggleCategoryFamily(this)">
                    <span>{{ \App\Support\StorefrontLabels::category($parent->name, $shopRtl) }}</span>
                  </label>
                  @if(isset($childCats[$parent->id]) && $childCats[$parent->id]->isNotEmpty())
                    <button type="button" class="shop-category-expand" aria-expanded="{{ $parentSelected ? 'true' : 'false' }}" aria-label="{{ $shopRtl ? 'عرض التصنيفات الفرعية' : 'Show subcategories' }}" onclick="toggleCategoryChildren(this)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><polyline points="6 9 12 15 18 9"/></svg></button>
                  @endif
                </div>
                @if(isset($childCats[$parent->id]) && $childCats[$parent->id]->isNotEmpty())
                  <div class="shop-child-bubbles">
                    @foreach($childCats[$parent->id] as $child)
                      @php $childSelected = in_array((int)$child->id, $selectedCategoryIds ?? [], true); @endphp
                      <label class="shop-category-bubble shop-category-bubble--child {{ $childSelected ? 'selected' : '' }}" data-child-label data-category-search="{{ strtolower($parent->name.' '.$child->name) }}">
                        <input type="checkbox" class="shop-category-check" value="{{ $child->id }}" data-parent="{{ $parent->id }}" {{ $childSelected ? 'checked' : '' }} onchange="syncCategoryBubble(this)">
                        <span>{{ \App\Support\StorefrontLabels::category($child->name, $shopRtl) }}</span>
                      </label>
                    @endforeach
                  </div>
                @endif
              </div>
            @endforeach
          </div>
          <div class="shop-legacy-category-filters">
          {{-- All Products --}}
          <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}"
             class="cat-all-pill {{ !$activeCategoryId ? 'active' : '' }}">
            <span>{{ $shopRtl ? $shopCopy['allProducts'] : 'All Products' }}</span>
            <span class="cat-count-badge" id="shop-total-sidebar">…</span>
          </a>

          <hr class="widget-divider" style="margin:8px 0">

          {{-- Parent → Children --}}
          @foreach($parentCats as $parent)
            @php
              $hasChildren   = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
              $isActive      = $activeCategoryId == $parent->id;
              $isOpen        = $activeParentId == $parent->id;
              $parentUrl     = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));
              $ownCount      = $catCounts[$parent->id] ?? 0;
              $childrenCount = 0;
              if ($hasChildren) foreach ($childCats[$parent->id] as $ch) $childrenCount += ($catCounts[$ch->id] ?? 0);
              $totalCount    = $ownCount + $childrenCount;
            @endphp

            <div class="cat-parent-item">
              @if($hasChildren)
                <div style="display:flex;align-items:center;border-radius:9px;{{ $isActive ? 'background:var(--c-orange);' : ($isOpen ? 'background:var(--c-tag);' : '') }}">
                  <a href="{{ $parentUrl }}"
                     style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 0 9px 13px;color:{{ $isActive ? '#fff' : 'var(--c-dark)' }};text-decoration:none;min-width:0;font-size:13.5px;font-weight:600;">
                    <span class="cat-parent-name">{{ \App\Support\StorefrontLabels::category($parent->name, $shopRtl) }}</span>
                    @if($totalCount > 0)
                      <span class="cat-count-badge" style="{{ $isActive ? 'background:rgba(255,255,255,.25);color:#fff;border-color:transparent;' : '' }}">{{ $totalCount }}</span>
                    @endif
                  </a>
                  <button onclick="toggleCatChildren('cc-{{ $parent->id }}', this)"
                          style="background:none;border:none;cursor:pointer;padding:9px 13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:{{ $isActive ? 'rgba(255,255,255,.7)' : 'var(--c-mid)' }};"
                          aria-label="{{ $shopRtl ? 'افتح أو اقفل التصنيفات الفرعية' : 'Toggle sub-categories' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"
                         class="cat-chevron-svg"
                         style="transition:transform .25s;{{ $isOpen ? 'transform:rotate(180deg)' : '' }}">
                      <polyline points="6 9 12 15 18 9"/>
                    </svg>
                  </button>
                </div>
              @else
                <a href="{{ $parentUrl }}" class="cat-parent-btn {{ $isActive ? 'active' : '' }}">
                  <span class="cat-parent-name">{{ \App\Support\StorefrontLabels::category($parent->name, $shopRtl) }}</span>
                  @if($totalCount > 0)<span class="cat-count-badge">{{ $totalCount }}</span>@endif
                </a>
              @endif

              @if($hasChildren)
                <div class="cat-children-wrap {{ $isOpen ? 'open' : '' }}" id="cc-{{ $parent->id }}">
                  <div class="cat-children-inner">
                    @foreach($childCats[$parent->id] as $child)
                      @php
                        $childActive = $activeCategoryId == $child->id;
                        $childCount  = $catCounts[$child->id] ?? 0;
                        $childUrl    = route('shop', array_merge(request()->except('category','page'), ['category' => $child->id]));
                      @endphp
                      <a href="{{ $childUrl }}" class="cat-child-link {{ $childActive ? 'active' : '' }}">
                        <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ \App\Support\StorefrontLabels::category($child->name, $shopRtl) }}</span>
                        @if($childCount > 0)<span class="cat-count-badge">{{ $childCount }}</span>@endif
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          @endforeach
          </div>
        </div>{{-- /widget-body --}}
      </div>{{-- /widget-categories --}}

      @if($allBrands->count())
      <hr class="widget-divider">

      {{-- ── Widget: Brands ───────────────────────────────────── --}}
      <div class="widget" id="widget-brands">

        <div class="widget-header" onclick="toggleWidget('widget-brands')" title="{{ $shopRtl ? 'اضغط عشان تقفل القسم' : 'Click to collapse' }}">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $shopRtl ? $shopCopy['brands'] : 'Brands' }}
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          @foreach($allBrands as $brand)
            @php $isBrandActive = $activeBrandName === $brand->name; @endphp
            <a href="{{ $isBrandActive ? route('shop', array_filter(request()->except('brand','page'))) : route('shop', array_merge(request()->except('brand','page'), ['brand' => $brand->name])) }}"
               class="cat-parent-btn {{ $isBrandActive ? 'active' : '' }}"
               style="margin-bottom:2px">
              {{ $brand->name }}
              @if($isBrandActive)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10" style="margin-left:auto;opacity:.7"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              @endif
            </a>
          @endforeach
        </div>

      </div>{{-- /widget-brands --}}
      @endif

      <hr class="widget-divider">

      {{-- ── Widget: Sort By ──────────────────────────────────── --}}
      <div class="widget" id="widget-sort">

        <div class="widget-header" onclick="toggleWidget('widget-sort')" title="{{ $shopRtl ? 'اضغط عشان تقفل القسم' : 'Click to collapse' }}">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/></svg>
            {{ $shopRtl ? $shopCopy['sortBy'] : 'Sort By' }}
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          <form method="GET" action="{{ route('shop') }}" id="sort-form" style="padding-bottom:4px">
            @foreach(request()->except(['sort','page','category','categories']) as $k => $v)
              <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            @foreach($selectedCategoryIds ?? [] as $selectedCategoryId)<input type="hidden" name="categories[]" value="{{ $selectedCategoryId }}">@endforeach
            <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
              <option value=""          {{ !request('sort') ? 'selected' : '' }}>{{ $shopRtl ? $shopCopy['latest'] : 'Latest' }}</option>
              <option value="price_asc" {{ request('sort')==='price_asc'  ? 'selected' : '' }}>{{ $shopRtl ? $shopCopy['lowHigh'] : 'Price: Low → High' }}</option>
              <option value="price_desc"{{ request('sort')==='price_desc' ? 'selected' : '' }}>{{ $shopRtl ? $shopCopy['highLow'] : 'Price: High → Low' }}</option>
            </select>
          </form>
        </div>

      </div>{{-- /widget-sort --}}

    </aside>
        </div>
        <footer class="shop-filter-panel-actions">
          <button type="button" class="shop-filter-apply" onclick="applyShopPriceFilter()">{{ $shopRtl ? 'تطبيق الفلاتر' : 'Apply filters' }}</button>
          <a href="{{ route('shop') }}" class="shop-filter-clear">{{ $shopRtl ? 'مسح الكل' : 'Clear all' }}</a>
        </footer>
      </section>
    </div>
    @endif

    {{-- ══ MAIN ════════════════════════════════════════════════ --}}
    <div>
      <div class="shop-toolbar">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <span class="result-count" id="shop-result-count" aria-live="polite" aria-atomic="true"></span>
          @if($activeCategoryId && isset($activeCatName) && $activeCatName)
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              {{ $activeCatName }}
              @php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]) && $childCats[$activeCategoryId]->count() > 0; @endphp
              @if($isParentFilter)<span style="opacity:.6;font-weight:500">{{ $shopRtl ? '+ تصنيفات فرعية' : '+ sub-categories' }}</span>@endif
              <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}" title="{{ $shopRtl ? 'امسح التصنيف' : 'Clear category' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          @endif
          @if($activeBrandName ?? false)
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              {{ $shopRtl ? 'الماركة:' : 'Brand:' }} {{ $activeBrandName }}
              <a href="{{ route('shop', array_filter(request()->except('brand','page'))) }}" title="{{ $shopRtl ? 'امسح الماركة' : 'Clear brand' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          @endif
          @if($minPrice !== null || $maxPrice !== null)
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
              {{ $shopRtl ? 'السعر:' : 'Price:' }} {{ $minPrice !== null ? number_format($minPrice, 2) : '0' }} – {{ $maxPrice !== null ? number_format($maxPrice, 2) : number_format((float) ($priceRange->max_price ?? 0), 2) }} EGP
              <a href="{{ route('shop', request()->except(['min_price', 'max_price', 'page'])) }}" title="{{ $shopRtl ? 'امسح نطاق السعر' : 'Clear price range' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          @endif
        </div>
        <div class="search-bar">
          <form method="GET" action="{{ route('shop') }}" style="display:contents">
            @if($activeCategoryId)<input type="hidden" name="category" value="{{ $activeCategoryId }}">@endif
            <input type="text" name="search" placeholder="{{ $shopRtl ? $shopCopy['search'] : 'Search products…' }}" value="{{ request('search') }}">
            <button type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
          </form>
        </div>
      </div>

      <div class="shop-results-grid shop-mobile-layout-{{ $shopMobileLayout }}" id="infinite-product-grid" aria-live="polite">
        <div id="product-loading-state" style="grid-column:1/-1;text-align:center;padding:48px 20px;color:var(--c-mid)">
          <span style="display:inline-flex;align-items:center;gap:8px;font-size:13px">
            <svg style="animation:spin .8s linear infinite" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
            {{ $shopRtl ? $shopCopy['loadingProducts'] : 'Loading products…' }}
          </span>
        </div>
      </div>
      <div id="product-empty-state" class="empty" style="display:none">
        <div class="empty-icon">🔍</div>
        <h3>{{ $shopRtl ? $shopCopy['noProducts'] : 'No products found' }}</h3>
        <p>{{ $shopRtl ? $shopCopy['noProductsHint'] : 'Try a different search term or browse all categories.' }}</p>
        <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:20px">{{ $shopRtl ? $shopCopy['clearFilters'] : 'Clear filters' }}</a>
      </div>

      {{-- Infinite scroll sentinel & loader --}}
      <div id="scroll-sentinel" style="height:1px;margin-top:8px"></div>
      <div id="scroll-loader" style="display:none;text-align:center;padding:24px 0">
        <span style="display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--c-mid)">
          <svg style="animation:spin .8s linear infinite" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
          {{ $shopRtl ? $shopCopy['loadingMore'] : 'Loading more products…' }}
        </span>
      </div>
      <div id="scroll-end" style="display:none;text-align:center;padding:20px 0;font-size:13px;color:var(--c-mid)">
        {{ $shopRtl ? $shopCopy['allSeen'] : "You've seen all products" }}
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
const shopRtl = @json($shopRtl);
const shopI18n = @json($shopJsCopy);

/* ── Widget fold/unfold ────────────────────────────────────── */
const STORAGE_KEY = 'ramo_shop_widgets';

function getWidgetState() {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch { return {}; }
}
function saveWidgetState(state) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch {}
}

function toggleWidget(id) {
  const widget = document.getElementById(id);
  widget.classList.toggle('collapsed');
  const state = getWidgetState();
  state[id] = widget.classList.contains('collapsed');
  saveWidgetState(state);
}

/* Restore collapsed state on page load */
document.addEventListener('DOMContentLoaded', function () {
  const state = getWidgetState();
  Object.keys(state).forEach(function (id) {
    if (state[id]) {
      const el = document.getElementById(id);
      if (el) el.classList.add('collapsed');
    }
  });
});

/* ── Category sub-list toggle ──────────────────────────────── */
function toggleCatChildren(id, btn) {
  const wrap = document.getElementById(id);
  const svg  = btn.querySelector('.cat-chevron-svg');
  const open = wrap.classList.toggle('open');
  if (svg) svg.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

/* ── Mobile filter alert / focus management ─────────────────── */
let shopFilterPreviousFocus = null;

function setShopFilterButton(open) {
  const btn = document.getElementById('shop-filter-btn');
  if (!btn) return;
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> ' + shopI18n.closeFilters
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> ' + shopI18n.filters;
}

function toggleShopFilter() {
  const alert = document.getElementById('shop-filter-alert');
  if (!alert) return;
  if (alert.classList.contains('open')) closeShopFilter();
  else openShopFilter();
}

function openShopFilter() {
  const alert = document.getElementById('shop-filter-alert');
  const panel = alert && alert.querySelector('.shop-filter-panel');
  if (!alert || !panel) return;
  shopFilterPreviousFocus = document.activeElement;
  alert.classList.add('open');
  alert.setAttribute('aria-hidden', 'false');
  document.body.classList.add('shop-filter-open');
  setShopFilterButton(true);
  window.setTimeout(() => panel.focus(), 0);
}

function closeShopFilter() {
  const alert = document.getElementById('shop-filter-alert');
  if (!alert) return;
  alert.classList.remove('open');
  alert.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('shop-filter-open');
  setShopFilterButton(false);
  if (shopFilterPreviousFocus && typeof shopFilterPreviousFocus.focus === 'function') {
    shopFilterPreviousFocus.focus();
  }
  shopFilterPreviousFocus = null;
}

function syncCategoryBubble(input) {
  const label = input.closest('.shop-category-bubble');
  if (label) label.classList.toggle('selected', input.checked);
}
function toggleCategoryFamily(input) {
  syncCategoryBubble(input);
  const family = input.closest('.shop-category-family');
  family?.querySelectorAll('.shop-child-bubbles input').forEach(child => {
    child.checked = input.checked;
    syncCategoryBubble(child);
  });
}
function filterCategoryBubbles(value) {
  const query = String(value || '').trim().toLocaleLowerCase();
  document.querySelectorAll('.shop-category-family').forEach(family => {
    const parentMatch = (family.dataset.categorySearch || '').toLocaleLowerCase().includes(query);
    const childLabels = [...family.querySelectorAll('[data-child-label]')];
    let childMatch = false;
    childLabels.forEach(label => {
      const match = !query || (label.dataset.categorySearch || '').toLocaleLowerCase().includes(query);
      label.hidden = !parentMatch && !match;
      childMatch ||= match;
    });
    family.hidden = Boolean(query) && !parentMatch && !childMatch;
    if (query && childMatch) family.classList.add('expanded');
    else if (!query) family.classList.remove('search-expanded');
    family.classList.toggle('search-expanded', Boolean(query && childMatch));
    if (query && !parentMatch && childMatch) family.classList.add('search-match-child');
    else family.classList.remove('search-match-child');
  });
}
function toggleCategoryChildren(button) {
  const family = button.closest('.shop-category-family');
  if (!family) return;
  const expanded = family.classList.toggle('expanded');
  button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
}

document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape' && document.getElementById('shop-filter-alert')?.classList.contains('open')) {
    event.preventDefault();
    closeShopFilter();
  }
});

/* ── Price range controls ───────────────────────────────────── */
(function () {
  const minRange = document.getElementById('shop-min-range');
  const maxRange = document.getElementById('shop-max-range');
  const minInput = document.getElementById('shop-min-price');
  const maxInput = document.getElementById('shop-max-price');
  const form = document.getElementById('shop-price-form');
  if (!minRange || !maxRange || !minInput || !maxInput || !form) return;

  const formatPrice = value => Number(value).toFixed(2).replace(/\.00$/, '');
  const syncRangeTrack = function () {
    const low = Number(minRange.min);
    const high = Number(maxRange.max);
    const span = high - low || 1;
    const start = Math.max(0, Math.min(100, ((Number(minRange.value) - low) / span) * 100));
    const end = Math.max(start, Math.min(100, ((Number(maxRange.value) - low) / span) * 100));
    form.querySelector('.shop-price-sliders')?.style.setProperty('--range-start', start + '%');
    form.querySelector('.shop-price-sliders')?.style.setProperty('--range-end', end + '%');
  };
  const syncFromSliders = function () {
    let min = Number(minRange.value);
    let max = Number(maxRange.value);
    if (min > max) {
      if (this === minRange) maxRange.value = minRange.value;
      else minRange.value = maxRange.value;
      min = Number(minRange.value);
      max = Number(maxRange.value);
    }
    minInput.value = formatPrice(min);
    maxInput.value = formatPrice(max);
    syncRangeTrack();
  };
  const syncFromInputs = function () {
    let min = minInput.value === '' ? null : Number(minInput.value);
    let max = maxInput.value === '' ? null : Number(maxInput.value);
    if (min !== null && Number.isFinite(min)) min = Math.min(Number(minRange.max), Math.max(Number(minRange.min), min));
    if (max !== null && Number.isFinite(max)) max = Math.min(Number(maxRange.max), Math.max(Number(maxRange.min), max));
    if (min !== null) minRange.value = min;
    if (max !== null) maxRange.value = max;
    if (min !== null) minInput.value = formatPrice(min);
    if (max !== null) maxInput.value = formatPrice(max);
    if (min !== null && max !== null && min > max) {
      max = min;
      maxRange.value = max;
      maxInput.value = formatPrice(max);
    }
    syncRangeTrack();
  };
  minRange.addEventListener('input', syncFromSliders);
  maxRange.addEventListener('input', syncFromSliders);
  minInput.addEventListener('change', syncFromInputs);
  maxInput.addEventListener('change', syncFromInputs);
  syncRangeTrack();
  window.applyShopPriceFilter = function () {
    if (minInput.value !== '' || maxInput.value !== '') syncFromInputs();
    const url = new URL(form.action, window.location.origin);
    new FormData(form).forEach((value, key) => { if (value !== '') url.searchParams.set(key, value); });
    url.searchParams.delete('category');
    url.searchParams.delete('categories[]');
    document.querySelectorAll('#shop-category-bubbles .shop-category-check:checked').forEach(input => url.searchParams.append('categories[]', input.value));
    window.location.assign(url.toString());
  };
})();

/* ── Infinite scroll ────────────────────────────────────────── */
(function () {
  const grid      = document.getElementById('infinite-product-grid');
  const sentinel  = document.getElementById('scroll-sentinel');
  const loader    = document.getElementById('scroll-loader');
  const endMsg    = document.getElementById('scroll-end');

  if (!grid || !sentinel) return;
  const catalogView = grid.closest('[data-catalog-view]');
  const isActive = () => !catalogView || catalogView.dataset.catalogActive === '1';
  const shopBaseUrl = window.location.href;

  let nextPage    = null;
  let loading     = false;
  const cacheLifetime = 5 * 60 * 1000;
  let lastRefresh = Date.now();
  const resultCount = document.getElementById('shop-result-count');
  const sidebarTotal = document.getElementById('shop-total-sidebar');
  const loadingState = document.getElementById('product-loading-state');
  const emptyState = document.getElementById('product-empty-state');

  /* Build base URL from current page URL (strip ?page=…) */
  function buildUrl(page) {
    const url = new URL(shopBaseUrl);
    url.searchParams.set('page', page);
    return url.toString();
  }

  async function loadPage(page, replace = false, forceRefresh = false) {
    if (loading || !isActive()) return;
    loading = true;
    if (!replace) loader.style.display = 'block';

    try {
      const cacheKey = 'ramo-shop-products-v1:' + (shopRtl ? 'ar:' : 'en:') + buildUrl(page);
      let data = null;
      let dataSavedAt = Date.now();
      if (!forceRefresh) {
        try {
          const cached = JSON.parse(sessionStorage.getItem(cacheKey) || 'null');
          if (cached && Date.now() - cached.savedAt >= 0 && Date.now() - cached.savedAt < cacheLifetime) { data = cached.data; dataSavedAt = cached.savedAt; }
          else sessionStorage.removeItem(cacheKey);
        } catch (_) {}
      }
      if (!data) {
        const response = await fetch(buildUrl(page), {
          cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Unable to load shop products');
        data = await response.json();
        if (typeof data.html !== 'string' || typeof data.hasMore !== 'boolean') throw new Error('Invalid shop product response');
        dataSavedAt = Date.now();
        try { sessionStorage.setItem(cacheKey, JSON.stringify({ savedAt: dataSavedAt, data })); } catch (_) {}
      }
      if (replace) lastRefresh = dataSavedAt;
      const batch = document.createElement('template');
      batch.innerHTML = data.html || '';
      if (typeof _pcInitializeVariations === 'function') {
        _pcInitializeVariations(batch.content);
      }
      if (replace) {
        grid.replaceChildren(batch.content);
        if (resultCount) {
          resultCount.textContent = shopRtl
            ? `${data.total} ${shopI18n.products}`
            : `${data.total} ${data.total !== 1 ? shopI18n.productsPlural : shopI18n.products}`;
        }
        if (sidebarTotal) sidebarTotal.textContent = data.total;
        if (emptyState) emptyState.style.display = data.html ? 'none' : 'block';
      } else {
        grid.append(batch.content);
      }

      if (data.html && typeof refreshWishlistState === 'function') {
        refreshWishlistState();
      }

      nextPage = data.hasMore ? data.nextPage : null;
      if (nextPage === null) {
        endMsg.style.display = 'block';
        observer.disconnect();
      } else {
        endMsg.style.display = 'none';
        observer.observe(sentinel);
      }
    } catch {
      if (replace) {
        if (loadingState) loadingState.innerHTML = '<span style="color:var(--c-mid)">' + shopI18n.loadError + '</span>';
        if (resultCount) resultCount.textContent = shopI18n.unavailable;
      }
    } finally {
      loader.style.display = 'none';
      loading = false;
    }
  }

  const observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting && nextPage !== null) loadPage(nextPage);
  }, { rootMargin: '200px' });

  // Fetch products only after the complete page shell is available to the user.
  loadPage(1, true);
  observer.observe(sentinel);
  // Refresh the catalog after five minutes, including when a background tab returns.
  function refreshExpiredProducts() {
    if (!document.hidden && isActive() && Date.now() - lastRefresh >= cacheLifetime && !loading) loadPage(1, true, true);
  }
  setInterval(refreshExpiredProducts, 30000);
  document.addEventListener('visibilitychange', refreshExpiredProducts);
  document.addEventListener('catalog:shown', () => {
    if (isActive()) {
      if (nextPage !== null) { observer.unobserve(sentinel); observer.observe(sentinel); }
      refreshExpiredProducts();
    }
  });
})();
</script>

@push('styles')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@endpush
