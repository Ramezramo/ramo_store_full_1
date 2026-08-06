<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Ramo Store') — Fashion & More</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --c-bg:#f8f8f6;--c-white:#fff;--c-dark:#111;--c-mid:#555;--c-light:#e8e8e4;
  --c-accent:#111;--c-accent-h:#333;--c-tag:#f0ede8;--c-orange:#e85d26;
  --radius:10px;--radius-lg:16px;--shadow:0 2px 12px rgba(0,0,0,.07);--shadow-md:0 4px 24px rgba(0,0,0,.11);
}
html{scroll-behavior:smooth;overflow-x:hidden;width:100%}
body{font-family:'Inter',sans-serif;background:var(--c-bg);color:var(--c-dark);font-size:15px;line-height:1.6;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden;width:100%}
a{color:inherit;text-decoration:none}
img{max-width:100%;display:block}
button{cursor:pointer;font-family:inherit}

/* ── NAV ── */
.nav{background:var(--c-white);border-bottom:1px solid var(--c-light);position:sticky;top:0;z-index:100;box-shadow:var(--shadow)}
.nav-inner{max-width:1280px;margin:auto;padding:0 20px;height:64px;display:flex;align-items:center;gap:16px}
.nav-logo{font-size:20px;font-weight:800;letter-spacing:-0.5px;color:var(--c-dark);white-space:nowrap;flex-shrink:0}
.nav-logo span{color:var(--c-orange)}
.nav-links{display:flex;gap:2px}
.nav-links a{padding:7px 13px;border-radius:8px;font-size:13.5px;font-weight:500;color:var(--c-mid);transition:all .15s;white-space:nowrap}
.nav-links a:hover,.nav-links a.active{background:var(--c-tag);color:var(--c-dark)}
.nav-search{flex:1;min-width:0;max-width:480px}
.nav-search form{display:flex;align-items:center;background:var(--c-bg);border:1.5px solid var(--c-light);border-radius:50px;overflow:hidden;transition:border-color .18s,box-shadow .18s;padding-left:14px;gap:4px}
.nav-search form:focus-within{border-color:#999;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.07)}
.nav-search-icon{color:var(--c-mid);flex-shrink:0;pointer-events:none}
.nav-search input{flex:1;min-width:0;padding:9px 4px;background:none;border:none;outline:none;font-size:13.5px;color:var(--c-dark)}
.nav-search input::placeholder{color:var(--c-mid)}
.nav-search button{display:flex;align-items:center;justify-content:center;width:36px;height:36px;margin:3px;border-radius:50px;background:var(--c-dark);border:none;color:#fff;cursor:pointer;flex-shrink:0;transition:background .15s}
.nav-search button:hover{background:#333}
.nav-actions{display:flex;align-items:center;gap:4px;flex-shrink:0}
.nav-icon-btn{position:relative;width:40px;height:40px;border-radius:10px;border:none;background:none;display:flex;align-items:center;justify-content:center;font-size:19px;color:var(--c-mid);cursor:pointer;text-decoration:none;transition:background .15s}
.nav-icon-btn:hover{background:var(--c-tag);color:var(--c-dark)}
.nav-badge{position:absolute;top:4px;right:4px;background:var(--c-orange);color:#fff;font-size:9px;font-weight:800;min-width:16px;height:16px;border-radius:50px;display:flex;align-items:center;justify-content:center;padding:0 3px;line-height:1}
.nav-user-btn{display:flex;align-items:center;gap:6px;padding:7px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--c-mid);background:none;border:none;white-space:nowrap;cursor:pointer;text-decoration:none;transition:all .15s}
.nav-user-btn:hover{background:var(--c-tag);color:var(--c-dark)}
.nav-portal{position:relative;display:inline-flex}
.nav-dashboard-btn{display:flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:var(--c-dark);border:none;white-space:nowrap;cursor:pointer;text-decoration:none;transition:all .15s;user-select:none}
.nav-dashboard-btn:hover{background:#333}
.nav-dashboard-btn .caret{margin-left:2px;transition:transform .2s}
.nav-portal.open .caret{transform:rotate(180deg)}
.nav-dashboard-vendor{background:var(--c-orange)}
.nav-dashboard-vendor:hover{background:#d44f1a}
.nav-portal-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:168px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.13);padding:6px;z-index:200;visibility:hidden;opacity:0;transform:translateY(-4px) scale(.98);transform-origin:top right;transition:opacity .18s ease,transform .18s ease,visibility 0s .18s}
.nav-portal.open .nav-portal-dropdown{visibility:visible;opacity:1;transform:translateY(0) scale(1);transition:opacity .18s ease,transform .18s ease,visibility 0s 0s}
.nav-portal-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:7px;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:.12s;width:100%;background:none;border:none;cursor:pointer;text-align:left;white-space:nowrap}
.nav-portal-item:hover{background:#f5f5f2;color:#111}
.nav-portal-item.danger{color:#dc2626}
.nav-portal-item.danger:hover{background:#fef2f2;color:#dc2626}
.nav-portal-divider{border:none;border-top:1px solid #f0f0ec;margin:4px 0}

/* ── PAGE ── */
.page{flex:1;max-width:1280px;margin:auto;width:100%;padding:32px 24px 64px}

/* ── HERO ── */
.hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border-radius:var(--radius-lg);padding:72px 64px;margin-bottom:56px;display:flex;align-items:center;justify-content:space-between;overflow:hidden;position:relative;min-height:340px}
.hero::after{content:'';position:absolute;right:-60px;top:-60px;width:380px;height:380px;background:rgba(232,93,38,.12);border-radius:50%;pointer-events:none}
.hero-text{position:relative;z-index:1;max-width:520px}
.hero-eyebrow{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--c-orange);margin-bottom:14px}
.hero-title{font-size:44px;font-weight:800;color:#fff;line-height:1.15;margin-bottom:18px;letter-spacing:-1px}
.hero-sub{font-size:16px;color:rgba(255,255,255,.65);margin-bottom:32px;line-height:1.7}
.btn{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;border-radius:50px;font-size:14px;font-weight:700;border:none;transition:all .2s;cursor:pointer}
.btn-white{background:#fff;color:#111}
.btn-white:hover{background:#f0ede8;transform:translateY(-1px)}
.btn-dark{background:var(--c-dark);color:#fff}
.btn-dark:hover{background:var(--c-accent-h);transform:translateY(-1px)}
.btn-outline{background:transparent;border:2px solid var(--c-light);color:var(--c-dark)}
.btn-outline:hover{border-color:#999;background:var(--c-tag)}

/* ── SECTION HEADERS ── */
.sec-head{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:24px}
.sec-title{font-size:22px;font-weight:800;letter-spacing:-.4px}
.sec-link{font-size:13px;color:var(--c-mid);font-weight:500}
.sec-link:hover{color:var(--c-dark)}

/* ── CATEGORY CHIPS ── */
.cat-grid{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:48px}
.cat-chip{display:flex;align-items:center;gap:10px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:50px;padding:10px 20px;font-size:13.5px;font-weight:600;transition:all .18s;white-space:nowrap}
.cat-chip:hover{border-color:#999;background:var(--c-tag);transform:translateY(-2px);box-shadow:var(--shadow)}

/* ── PRODUCT GRID ── */
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;align-items:start}
.product-grid.cols-4{grid-template-columns:repeat(4,1fr)}
.product-card{background:var(--c-white);border-radius:var(--radius-lg);overflow:hidden;border:1.5px solid var(--c-light);transition:all .2s;display:flex;flex-direction:column}
.product-card:hover{box-shadow:var(--shadow-md);border-color:#d0d0d0;transform:translateY(-3px)}
.product-card-img{aspect-ratio:1;background:var(--c-bg);overflow:hidden;position:relative;display:block}
.product-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .35s}
.product-card:hover .product-card-img img{transform:scale(1.05)}
.product-card-img .badge-sale{position:absolute;top:10px;left:10px;background:var(--c-orange);color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:50px;z-index:1}
.product-card-img .badge-flash{background:linear-gradient(135deg,#e85d26,#f59e0b);animation:flash-pulse 1.8s ease-in-out infinite}
@keyframes flash-pulse{0%,100%{box-shadow:0 0 0 0 rgba(232,93,38,.5)}50%{box-shadow:0 0 0 5px rgba(232,93,38,0)}}
/* Cart loading overlay */
.cart-loading-overlay{position:fixed;inset:0;background:rgba(255,255,255,.55);display:none;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(1px)}
.cart-loading-overlay.active{display:flex}
.cart-spinner{width:38px;height:38px;border:3.5px solid rgba(0,0,0,.12);border-top-color:var(--c-dark,#111);border-radius:50%;animation:cart-spin .7s linear infinite}
@keyframes cart-spin{to{transform:rotate(360deg)}}
.wish-btn{position:absolute;top:10px;right:10px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.9);border:none;font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;z-index:1}
.wish-btn:hover{background:#fff;transform:scale(1.1)}
.wish-btn.wished{color:var(--c-orange)}
.placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:48px;color:var(--c-light)}
.product-card-body{padding:16px;display:flex;flex-direction:column;gap:6px}
.product-card-name{font-size:13.5px;font-weight:600;color:var(--c-dark);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.product-card-price{display:flex;align-items:center;gap:6px;padding-top:8px;flex-wrap:wrap;row-gap:2px}
.price-main{font-size:16px;font-weight:800;color:var(--c-dark);white-space:nowrap}
.price-old{font-size:13px;color:#aaa;text-decoration:line-through;white-space:nowrap}
.price-main.sale{color:var(--c-orange)}
.card-add-btn{margin-top:10px;padding:9px 14px;background:var(--c-dark);color:#fff;border:none;border-radius:8px;font-size:12.5px;font-weight:700;width:100%;transition:all .2s}
.card-add-btn:hover{background:var(--c-accent-h)}
.card-details-btn{display:block;margin-top:8px;padding:9px 14px;background:#fff;color:var(--c-dark);border:1.5px solid var(--c-light);border-radius:8px;font-size:12.5px;font-weight:700;width:100%;text-align:center;transition:all .2s;box-sizing:border-box}
.card-details-btn:hover{border-color:var(--c-dark);background:var(--c-bg)}
.pc-coupon-bar{display:flex;text-decoration:none;border-radius:0 0 10px 10px;overflow:hidden;margin:10px -14px -14px;font-size:11px;font-weight:700;line-height:1}
.pc-coupon-left{flex:1;background:#7c3aed;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-coupon-code{background:rgba(255,255,255,.2);border-radius:4px;padding:1px 5px;letter-spacing:.03em}
.pc-coupon-right{background:#5b21b6;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:4px;white-space:nowrap;flex-shrink:0}

/* ── SHOP LAYOUT ── */
.shop-layout{display:grid;grid-template-columns:220px 1fr;gap:28px;align-items:start}
.sidebar{background:var(--c-white);border-radius:var(--radius-lg);padding:24px;border:1.5px solid var(--c-light);position:sticky;top:84px}
.sidebar h3{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--c-mid);margin-bottom:14px}
.cat-list{list-style:none;display:flex;flex-direction:column;gap:2px}
.cat-list a{display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;font-size:13.5px;color:var(--c-mid);transition:all .12s}
.cat-list a:hover,.cat-list a.active{background:var(--c-tag);color:var(--c-dark);font-weight:600}
.sidebar-divider{border:none;border-top:1px solid var(--c-light);margin:18px 0}
.sort-select{width:100%;padding:9px 12px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13.5px;font-family:inherit;background:var(--c-bg);color:var(--c-dark);outline:none;cursor:pointer}
.sort-select:focus{border-color:#999}

/* ── TOOLBAR ── */
.shop-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px}
.result-count{font-size:13px;color:var(--c-mid)}
.search-bar{flex:1;max-width:340px;display:flex;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:8px;overflow:hidden}
.search-bar input{flex:1;padding:9px 14px;border:none;outline:none;font-size:13.5px;background:none}
.search-bar button{padding:9px 14px;background:none;border:none;color:var(--c-mid)}

/* ── PAGINATION ── */
.pagination-wrap{display:flex;justify-content:center;gap:6px;margin-top:36px;flex-wrap:wrap}
.pagination-wrap a,.pagination-wrap span{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 10px;border-radius:8px;font-size:13px;font-weight:500;border:1.5px solid var(--c-light);background:var(--c-white);color:var(--c-mid);transition:all .12s}
.pagination-wrap a:hover{border-color:#999;color:var(--c-dark)}
.pagination-wrap span.active-page{background:var(--c-dark);border-color:var(--c-dark);color:#fff}

/* ── PRODUCT DETAIL ── */
.product-layout{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start}
.gallery-wrap{display:flex;gap:12px;align-items:flex-start}
.gallery-thumbs{display:flex;flex-direction:column;gap:8px;flex-shrink:0;width:74px;max-height:520px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--c-light) transparent}
.gallery-thumbs::-webkit-scrollbar{width:3px}.gallery-thumbs::-webkit-scrollbar-thumb{background:var(--c-light);border-radius:4px}
.gallery-main{flex:1;aspect-ratio:1;background:var(--c-white);border-radius:var(--radius-lg);overflow:hidden;border:1.5px solid var(--c-light)}
.gallery-main img{width:100%;height:100%;object-fit:cover}
.gallery-thumb{width:70px;height:70px;border-radius:8px;overflow:hidden;border:2px solid transparent;cursor:pointer;background:var(--c-white);flex-shrink:0}
.gallery-thumb:hover,.gallery-thumb.active{border-color:var(--c-dark)}
.gallery-thumb img{width:100%;height:100%;object-fit:contain;background:#fff;filter:blur(1.5px) brightness(.82);transition:filter .2s}
.gallery-thumb:hover img,.gallery-thumb.active img{filter:none}
.product-info{padding:8px 0}
.product-info h1{font-size:26px;font-weight:800;letter-spacing:-.4px;margin-bottom:14px;line-height:1.25}
.price-block{display:flex;align-items:baseline;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.price-block .big-price{font-size:32px;font-weight:800}
.price-block .big-price.sale-price{color:var(--c-orange)}
.price-block .orig-price{font-size:18px;color:#aaa;text-decoration:line-through}
.price-block .disc-badge{background:#fff0eb;color:var(--c-orange);font-size:12px;font-weight:700;padding:3px 10px;border-radius:50px}
.var-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:10px}
.var-options{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.var-btn{padding:8px 18px;border-radius:8px;border:1.5px solid var(--c-light);background:var(--c-white);font-size:13px;font-weight:600;color:var(--c-mid);transition:all .12s}
.var-btn:hover,.var-btn.selected{border-color:var(--c-dark);color:var(--c-dark);background:var(--c-tag)}
.var-btn.var-unavailable{opacity:.32;text-decoration:line-through;cursor:not-allowed}
.var-btn.var-unavailable:hover{border-color:var(--c-light);background:var(--c-white);color:var(--c-mid)}
.var-swatch{width:32px;height:32px;border-radius:50%;border:2.5px solid rgba(0,0,0,.08);cursor:pointer;transition:all .12s;position:relative;outline:3px solid transparent;outline-offset:3px;flex-shrink:0}
.var-swatch:hover{outline-color:#ccc}
.var-swatch.selected{outline-color:var(--c-dark)}
.var-swatch.var-unavailable{opacity:.28;cursor:not-allowed}
.var-swatch.var-unavailable::after{content:'';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%) rotate(-45deg);width:2px;height:90%;background:rgba(80,80,80,.6);border-radius:2px}
.var-hint{font-size:12px;color:var(--c-orange);margin-top:-10px;margin-bottom:14px;font-weight:500;min-height:18px}
.var-selected-label{font-weight:700;color:var(--c-dark)}
/* ── PROMO BANNERS ── */
.promo-section{margin-bottom:48px}
.promo-scroll{display:flex;gap:16px;overflow-x:auto;padding-bottom:10px;-webkit-overflow-scrolling:touch}
.promo-scroll::-webkit-scrollbar{height:4px}
.promo-scroll::-webkit-scrollbar-track{background:var(--c-light);border-radius:4px}
.promo-scroll::-webkit-scrollbar-thumb{background:#ccc;border-radius:4px}
.coupon-card{min-width:240px;border-radius:16px;padding:22px 20px;position:relative;overflow:hidden;flex-shrink:0;cursor:default}
.coupon-card::before{content:'';position:absolute;right:-30px;top:-30px;width:120px;height:120px;background:rgba(255,255,255,.1);border-radius:50%;pointer-events:none}
.coupon-card::after{content:'';position:absolute;right:24px;bottom:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none}
.coupon-card-0{background:linear-gradient(135deg,#e85d26,#c0440f);color:#fff}
.coupon-card-1{background:linear-gradient(135deg,#1a1a2e,#0f3460);color:#fff}
.coupon-card-2{background:linear-gradient(135deg,#22a35c,#145c34);color:#fff}
.coupon-card-3{background:linear-gradient(135deg,#805ad5,#5a3aa0);color:#fff}
.coupon-card-4{background:linear-gradient(135deg,#3182ce,#1a56a0);color:#fff}
.coupon-card-5{background:linear-gradient(135deg,#c05621,#8b3d14);color:#fff}
.coupon-pct{font-size:40px;font-weight:900;line-height:1;letter-spacing:-2px}
.coupon-pct sup{font-size:18px;font-weight:700;vertical-align:super;letter-spacing:0}
.coupon-desc{font-size:12.5px;opacity:.8;margin-top:3px}
.coupon-code-row{display:flex;align-items:center;gap:8px;margin-top:14px;background:rgba(255,255,255,.15);border-radius:8px;padding:7px 10px}
.coupon-code{font-size:13.5px;font-weight:800;letter-spacing:.5px;flex:1;font-family:monospace}
.coupon-copy-btn{background:rgba(255,255,255,.25);border:none;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:6px;cursor:pointer;transition:background .15s;white-space:nowrap}
.coupon-copy-btn:hover{background:rgba(255,255,255,.4)}
.coupon-min{font-size:11px;opacity:.65;margin-top:7px}
.desc-block{font-size:14px;color:var(--c-mid);line-height:1.75;border-top:1px solid var(--c-light);padding-top:20px;margin-top:20px}
.desc-intro{margin-bottom:10px}
.desc-bullets{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px}
.desc-bullets li{display:flex;align-items:flex-start;gap:8px;line-height:1.55}
.desc-bullets li::before{content:'•';color:var(--c-orange,#e85d26);font-size:16px;line-height:1.4;flex-shrink:0}
.add-to-cart-row{display:flex;gap:12px;margin-top:24px;align-items:center}
.qty-input{display:flex;align-items:center;border:1.5px solid var(--c-light);border-radius:8px;overflow:hidden;background:var(--c-white)}
.qty-input button{width:36px;height:44px;background:none;border:none;font-size:18px;color:var(--c-mid)}
.qty-input button:hover{background:var(--c-bg)}
.qty-input input{width:48px;height:44px;border:none;text-align:center;font-size:15px;font-weight:600;background:none;outline:none}
.add-to-cart-btn{flex:1;padding:13px 24px;background:var(--c-dark);color:#fff;border:none;border-radius:50px;font-size:15px;font-weight:700;transition:all .2s}
.add-to-cart-btn:hover{background:var(--c-accent-h);transform:translateY(-1px)}
.wish-toggle-btn{padding:13px 16px;border:1.5px solid var(--c-light);border-radius:50px;background:var(--c-white);font-size:20px;transition:all .2s}
.wish-toggle-btn:hover{border-color:var(--c-orange);color:var(--c-orange)}
.wish-toggle-btn.wished{border-color:var(--c-orange);color:var(--c-orange);background:#fff5f2}
.badge-stock-ok{display:inline-block;background:#eefbee;color:#22a35c;font-size:12px;font-weight:600;padding:4px 12px;border-radius:50px;margin-bottom:18px}
.badge-stock-no{display:inline-block;background:#fff0f0;color:#e02020;font-size:12px;font-weight:600;padding:4px 12px;border-radius:50px;margin-bottom:18px}

/* ── REVIEWS ── */
.reviews-section{margin-top:56px;padding-top:40px;border-top:1px solid var(--c-light)}
.reviews-header{display:flex;align-items:center;gap:20px;margin-bottom:28px;flex-wrap:wrap}
.rating-big{font-size:48px;font-weight:800;line-height:1}
.rating-stars-big{display:flex;gap:2px;font-size:22px;margin-bottom:4px}
.rating-count{font-size:13px;color:var(--c-mid)}
.review-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:20px;margin-bottom:14px}
.review-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.reviewer-avatar{width:38px;height:38px;border-radius:50%;background:var(--c-tag);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--c-mid)}
.reviewer-name{font-weight:700;font-size:14px}
.review-date{font-size:12px;color:var(--c-mid)}
.review-stars{display:flex;gap:1px;font-size:13px;margin-bottom:8px}
.review-body{font-size:14px;color:var(--c-mid);line-height:1.65}
.review-form{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px;margin-top:24px}
.review-form h4{font-size:16px;font-weight:700;margin-bottom:16px}
.star-picker{display:flex;gap:6px;margin-bottom:14px;font-size:28px}
.star-picker span{cursor:pointer;color:#ddd;transition:color .1s}
.star-picker span.lit,.star-picker span:hover{color:#f5a623}

/* ── CART ── */
.cart-back-link{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:600;color:var(--c-mid);margin-bottom:18px;text-decoration:none;transition:color .15s}
.cart-back-link:hover{color:var(--c-dark)}
.cart-title-row{display:flex;align-items:baseline;gap:12px;margin-bottom:24px}
.cart-title{font-size:28px;font-weight:800;color:var(--c-dark);margin:0}
.cart-count-badge{font-size:13px;color:var(--c-mid);font-weight:500}
.cart-layout{display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:start}
/* ── Cart item card ── */
.cart-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:14px;padding:18px;margin-bottom:12px;transition:box-shadow .15s,border-color .15s;position:relative}
.cart-row:hover{box-shadow:0 4px 16px rgba(0,0,0,.07);border-color:#d0d0d0}
.cart-row-divider{display:none}
.cart-row-bottom{display:none}
.cart-prod{display:flex;gap:16px;align-items:flex-start;min-width:0;flex:1}
.cart-prod img{width:96px;height:96px;object-fit:cover;border-radius:10px;flex-shrink:0;background:var(--c-bg)}
.cart-img-placeholder{width:96px;height:96px;border-radius:10px;background:var(--c-bg);display:flex;align-items:center;justify-content:center;font-size:36px;flex-shrink:0}
.cart-prod-info{min-width:0;flex:1;padding-top:2px}
.cart-name{font-size:15px;font-weight:700;color:var(--c-dark);display:block;margin-bottom:6px;line-height:1.35;text-decoration:none}
.cart-name:hover{color:var(--c-orange)}
.cart-model{font-size:12px;color:var(--c-mid);margin-bottom:6px}
.cart-sku{font-size:12px;color:var(--c-mid);margin-top:4px}
/* Attribute pills */
.cart-attr-pills{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:10px}
.cart-attr-pill{display:inline-flex;align-items:center;gap:4px;background:var(--c-bg);border:1px solid var(--c-light);border-radius:20px;padding:3px 10px;font-size:12px;color:var(--c-dark);font-weight:500}
.cart-attr-pill-key{color:var(--c-mid);font-weight:400}
/* Qty + remove row */
.cart-row-actions{display:flex;align-items:center;gap:14px;margin-top:4px}
.qty-pill{display:inline-flex;align-items:center;border:1.5px solid var(--c-light);border-radius:50px;overflow:hidden;background:var(--c-white)}
.qty-pill button{width:32px;height:34px;background:none;border:none;font-size:17px;color:var(--c-dark);cursor:pointer;transition:background .12s}
.qty-pill button:hover{background:var(--c-bg)}
.qty-pill input{width:34px;height:34px;border:none;text-align:center;font-size:13.5px;font-weight:700;background:none;outline:none;-moz-appearance:textfield}
.qty-pill input::-webkit-outer-spin-button,.qty-pill input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.cart-unit-price{font-size:12px;color:var(--c-mid);margin-left:2px}
/* Remove button — top-right corner */
.cart-remove-btn{position:absolute;top:14px;right:14px;width:30px;height:30px;border-radius:8px;background:none;border:1.5px solid var(--c-light);color:var(--c-mid);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0}
.cart-remove-btn:hover{background:#fff0f0;border-color:#fca5a5;color:#e02020}
.cart-remove-btn svg{pointer-events:none}
/* Price column */
.cart-row-price{flex-shrink:0;text-align:right;padding-top:40px;min-width:110px}
.cart-sub{font-size:17px;font-weight:800;color:var(--c-dark)}
.cart-sub-old{font-size:12.5px;color:var(--c-mid);text-decoration:line-through;margin-top:3px}
/* Bottom actions */
.cart-actions{display:flex;gap:10px;margin-top:8px;flex-wrap:wrap}
.cart-actions .btn{font-size:13.5px;padding:10px 18px;border-radius:10px}
.cart-clear-btn{background:none;border:1.5px solid #fca5a5;color:#e02020;border-radius:10px;font-size:13.5px;font-weight:600;padding:10px 18px;cursor:pointer;transition:all .15s}
.cart-clear-btn:hover{background:#fff0f0}
.cart-summary{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:16px;padding:24px;position:sticky;top:84px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.cart-summary-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.cart-summary-header h3{font-size:18px;font-weight:800;color:var(--c-dark);margin:0}
.cart-summary-badge{font-size:12px;font-weight:700;color:var(--c-mid);background:var(--c-bg);border-radius:20px;padding:3px 10px}
.summary-row{display:flex;justify-content:space-between;font-size:14px;margin-bottom:13px;align-items:center;color:var(--c-mid)}
.summary-row span:last-child{font-weight:600;color:var(--c-dark)}
.discount-row span{color:#22a35c!important}
.discount-row span:last-child{color:#22a35c!important;font-weight:700}
.summary-divider{border:none;border-top:1px solid var(--c-light);margin:16px 0}
.total-row{font-size:16px;font-weight:800;margin-bottom:0;color:var(--c-dark);background:var(--c-bg);border-radius:10px;padding:13px 14px;margin-left:-14px;margin-right:-14px}
.total-row span:last-child{font-size:18px;color:var(--c-dark)!important}
.checkout-btn{width:100%;justify-content:center;border-radius:12px;padding:15px;margin-top:16px;font-size:15px;font-weight:700;background:var(--c-dark);color:#fff;border:none;display:flex;align-items:center;gap:8px;transition:all .18s;letter-spacing:.01em}
.checkout-btn:hover{background:#111;color:#fff;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,.18)}
.checkout-btn svg{flex-shrink:0}
.coupon-box{display:flex;align-items:center;gap:8px;border:1.5px solid var(--c-light);border-radius:10px;padding:8px 8px 8px 14px;margin-bottom:16px;background:var(--c-bg);transition:border-color .15s}
.coupon-box:focus-within{border-color:var(--c-dark);background:#fff}
.coupon-icon{font-size:14px;flex-shrink:0}
.coupon-input{flex:1;padding:4px 0;border:none;font-size:13.5px;font-family:inherit;outline:none;background:none;color:var(--c-dark)}
.coupon-input::placeholder{color:var(--c-mid)}
.coupon-apply-btn{padding:8px 16px;font-size:12.5px;font-weight:700;border-radius:8px;background:var(--c-dark);border:none;color:#fff;cursor:pointer;transition:background .15s;white-space:nowrap;flex-shrink:0}
.coupon-apply-btn:hover{background:#111}
.coupon-btn{padding:9px 14px;font-size:13px;border-radius:8px}
.coupon-remove-btn{font-size:12px;color:#e02020;background:none;border:none;cursor:pointer;padding:4px 0;font-weight:600}
.coupon-remove-btn:hover{text-decoration:underline}
.applied-coupon-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;margin-bottom:16px;background:#f0fdf4;border:1px solid #bbf7d0;padding:10px 14px;border-radius:10px;color:#166534}
.applied-coupon-row strong{color:#166534}
.payment-icons{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;flex-wrap:wrap}
.payment-chip{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--c-mid);background:var(--c-bg);border:1px solid var(--c-light);border-radius:6px;padding:4px 8px}
.summary-shipping-free{color:#22a35c!important;font-weight:700}
.payment-icons{display:flex;gap:10px;margin-top:16px;font-size:12px;color:var(--c-mid);flex-wrap:wrap;justify-content:center}

/* ── CHECKOUT ── */
.checkout-layout{display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start}
.ck-section{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px;margin-bottom:16px}
.ck-title{font-size:16px;font-weight:800;margin-bottom:18px}
.ck-login-hint{font-size:13px;color:var(--c-mid);margin-bottom:16px;margin-top:-10px}
.ck-login-hint a{color:var(--c-orange);font-weight:600}
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{margin-bottom:14px}
.form-group:last-child{margin-bottom:0}
.form-group label{display:block;font-size:12.5px;font-weight:700;color:var(--c-mid);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:11px 14px;border:1.5px solid var(--c-light);border-radius:8px;font-size:14px;font-family:inherit;background:var(--c-bg);color:var(--c-dark);outline:none;transition:border-color .15s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#999;background:var(--c-white)}
.form-group textarea{resize:vertical;min-height:80px}
.err{font-size:12px;color:#e02020;margin-top:4px;display:block}
.pay-methods{display:flex;flex-direction:column;gap:10px}
.pay-option{display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid var(--c-light);border-radius:var(--radius);cursor:pointer;transition:all .15s}
.pay-option input{display:none}
.pay-option.selected,.pay-option:has(input:checked){border-color:var(--c-dark);background:var(--c-tag)}
.pay-icon{font-size:22px;flex-shrink:0}
.pay-title{font-size:14px;font-weight:700}
.pay-desc{font-size:12px;color:var(--c-mid);margin-top:1px}
.place-order-btn{width:100%;justify-content:center;border-radius:12px;padding:16px;font-size:16px}
.ck-summary{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px;position:sticky;top:84px}
.ck-items{margin-bottom:20px;display:flex;flex-direction:column;gap:12px}
.ck-item{display:flex;align-items:center;gap:12px}
.ck-item-img{width:52px;height:52px;border-radius:8px;background:var(--c-bg);overflow:hidden;position:relative;flex-shrink:0}
.ck-item-img img{width:100%;height:100%;object-fit:cover}
.ck-item-qty{position:absolute;top:-6px;right:-6px;background:var(--c-dark);color:#fff;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.ck-item-name{flex:1;font-size:13px;font-weight:600;color:var(--c-dark);line-height:1.35}
.ck-item-sku{font-size:11px;color:var(--c-mid);margin-top:2px}
.ck-item-attrs{display:flex;flex-wrap:wrap;gap:4px;margin-top:4px}
.ck-item-attrs span{font-size:11px;font-weight:400;color:var(--c-mid);background:var(--c-tag);padding:1px 7px;border-radius:50px}
.ck-item-attrs span strong{font-weight:700;color:var(--c-dark)}
.ck-item-price{font-size:13px;font-weight:700;white-space:nowrap}
.ck-totals{padding-top:16px}

/* ── ORDER SUCCESS ── */
.success-card{text-align:center;background:var(--c-white);border-radius:var(--radius-lg);padding:48px 32px;margin-bottom:20px;border:1.5px solid var(--c-light)}
.success-icon{font-size:56px;margin-bottom:16px}
.success-title{font-size:30px;font-weight:800;margin-bottom:12px}
.success-sub{font-size:15px;color:var(--c-mid);line-height:1.7;max-width:440px;margin:0 auto 20px}
.success-badge{display:inline-block;background:var(--c-tag);border:1.5px solid var(--c-light);padding:8px 20px;border-radius:50px;font-size:15px;font-weight:700}
.order-detail-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px}
.od-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--c-light);font-size:14px}
.od-row:last-child{border-bottom:none}
.od-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--c-mid)}
.od-total{font-size:20px;font-weight:800;color:var(--c-orange)}
.order-item-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--c-light)}
.order-item-row:last-child{border-bottom:none}
.order-item-info{flex:1;min-width:0}
.order-item-name{font-size:14px;font-weight:600;display:block;margin-bottom:2px}
.order-item-attr{font-size:12px;color:var(--c-mid)}
.order-item-qty{font-size:13px;color:var(--c-mid);white-space:nowrap}
.order-item-price{font-size:14px;font-weight:700;white-space:nowrap}
.success-actions{display:flex;gap:12px;margin-top:24px;flex-wrap:wrap}

/* ── STATUS BADGES ── */
.status-badge{display:inline-block;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:50px;text-transform:capitalize}
.status-pending{background:#fff9e6;color:#b7860a}
.status-processing{background:#e6f0ff;color:#1a56db}
.status-shipped{background:#f5f0ff;color:#7c3aed}
.status-completed{background:#eefbee;color:#22a35c}
.status-cancelled{background:#fff0f0;color:#e02020}
.status-refunded{background:#f3f4f6;color:#6b7280}
.status-failed{background:#fff0f0;color:#e02020}
.status-on-hold{background:#f3f4f6;color:#6b7280}

/* ── ACCOUNT ── */
.acc-layout{display:grid;grid-template-columns:220px 1fr;gap:28px;align-items:start}
@media(max-width:680px){.acc-layout{grid-template-columns:1fr}}
.acc-sidebar{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px;display:flex;flex-direction:column;gap:4px;position:sticky;top:20px}
.acc-avatar-block{display:flex;align-items:center;gap:12px;padding-bottom:18px;margin-bottom:12px;border-bottom:1.5px solid var(--c-light)}
.acc-avatar{width:44px;height:44px;border-radius:50%;background:var(--c-dark);color:#fff;font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.acc-avatar-name{font-size:13px;font-weight:700;color:var(--c-dark);line-height:1.3}
.acc-avatar-email{font-size:11.5px;color:var(--c-mid);margin-top:2px;word-break:break-all}
.acc-nav{display:flex;flex-direction:column;gap:2px}
.acc-nav-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--c-mid);text-decoration:none;transition:all .15s}
.acc-nav-item:hover{background:var(--c-tag);color:var(--c-dark)}
.acc-nav-item.active{background:var(--c-dark);color:#fff}
.acc-signout-btn{width:100%;display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:8px;font-size:13px;font-weight:600;color:#e02020;background:none;border:none;cursor:pointer;transition:all .15s}
.acc-signout-btn:hover{background:#fff0f0}
.acc-main{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:28px 32px}
.acc-section-title{font-size:16px;font-weight:800;color:var(--c-dark);margin-bottom:22px}
.acc-alert{padding:12px 16px;border-radius:8px;font-size:13px;font-weight:600;margin-bottom:20px}
.acc-alert-success{background:#eefbee;color:#22a35c;border:1px solid #b7efc5}
.acc-alert-error{background:#fff0f0;color:#e02020;border:1px solid #ffc9c9}
.acc-form{display:flex;flex-direction:column;gap:18px}
.acc-form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:500px){.acc-form-row{grid-template-columns:1fr}}
.acc-form-group{display:flex;flex-direction:column;gap:6px}
.acc-label{font-size:12px;font-weight:700;color:var(--c-dark);text-transform:uppercase;letter-spacing:.4px}
.acc-input{padding:10px 14px;border:1.5px solid var(--c-light);border-radius:8px;font-size:14px;color:var(--c-dark);background:var(--c-bg);outline:none;transition:border-color .15s}
.acc-input:focus{border-color:var(--c-dark);background:#fff}
.acc-input.error{border-color:#e02020}
.acc-field-error{font-size:12px;color:#e02020}
.acc-divider{border:none;border-top:1.5px solid var(--c-light);margin:8px 0}
/* ── ACCOUNT REVIEWS ── */
.acc-reviews-list{display:flex;flex-direction:column;gap:16px}
.acc-review-card{background:var(--c-bg);border:1.5px solid var(--c-light);border-radius:12px;padding:18px 20px}
.acc-review-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px}
.acc-review-product{font-size:13px;font-weight:700;color:var(--c-dark);text-decoration:none}
.acc-review-product:hover{color:var(--c-orange)}
.acc-review-stars{display:flex;align-items:center;margin-top:4px}
.acc-review-title{font-size:14px;font-weight:700;color:var(--c-dark);margin-bottom:6px}
.acc-review-body{font-size:13px;color:var(--c-mid);line-height:1.6;margin:0}
.acc-review-footer{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:10px;border-top:1px solid var(--c-light)}
.acc-review-badge{display:inline-block;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.4px}
.acc-review-badge.approved{background:#eefbee;color:#22a35c}
.acc-review-badge.pending{background:#fff8ee;color:#d97706}
/* ── ORDER TRACKING TIMELINE ── */
.order-timeline{display:flex;align-items:flex-start;justify-content:space-between;position:relative;margin-bottom:32px;padding:0 4px}
.order-timeline::before{content:'';position:absolute;top:18px;left:0;right:0;height:3px;background:var(--c-light);z-index:0}
.order-timeline-fill{position:absolute;top:18px;left:0;height:3px;background:var(--c-dark);z-index:1;transition:width .4s ease}
.otl-step{display:flex;flex-direction:column;align-items:center;gap:10px;z-index:2;flex:1}
.otl-dot{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid var(--c-light);background:#fff;transition:all .3s;flex-shrink:0}
.otl-dot svg{opacity:.35}
.otl-label{font-size:11.5px;font-weight:700;color:var(--c-mid);text-align:center;line-height:1.3;white-space:nowrap}
.otl-date{font-size:10.5px;color:var(--c-mid);text-align:center;margin-top:-6px}
.otl-step.done .otl-dot{background:var(--c-dark);border-color:var(--c-dark)}
.otl-step.done .otl-dot svg{opacity:1;stroke:#fff}
.otl-step.done .otl-label{color:var(--c-dark)}
.otl-step.current .otl-dot{background:var(--c-orange);border-color:var(--c-orange);box-shadow:0 0 0 4px rgba(255,107,0,.15)}
.otl-step.current .otl-dot svg{opacity:1;stroke:#fff}
.otl-step.current .otl-label{color:var(--c-orange);font-weight:800}
.otl-step.cancelled .otl-dot{background:#e02020;border-color:#e02020}
.otl-step.cancelled .otl-dot svg{opacity:1;stroke:#fff}
.otl-step.cancelled .otl-label{color:#e02020}
.acc-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
.orders-table-wrap{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);overflow:hidden}
.orders-table{width:100%;border-collapse:collapse;font-size:14px}
.orders-table th{padding:13px 18px;text-align:left;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--c-mid);background:var(--c-tag);border-bottom:1.5px solid var(--c-light)}
.orders-table td{padding:14px 18px;border-bottom:1px solid var(--c-light)}
.orders-table tr:last-child td{border-bottom:none}
.orders-table tr:hover td{background:var(--c-bg)}

/* ── AUTH ── */
.auth-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:40px;margin-top:40px}
.auth-logo{font-size:22px;font-weight:800;margin-bottom:24px}
.auth-logo span{color:var(--c-orange)}
.auth-title{font-size:22px;font-weight:800;margin-bottom:6px}
.auth-sub{color:var(--c-mid);font-size:14px;margin-bottom:24px}
.form-check-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.form-check{display:flex;align-items:center;gap:8px;font-size:13.5px;cursor:pointer}
.form-check input{width:16px;height:16px;cursor:pointer}
.auth-footer{text-align:center;margin-top:24px;font-size:14px;color:var(--c-mid)}
.auth-footer a{color:var(--c-orange);font-weight:600}

/* ── ALERTS ── */
.alert-box{padding:13px 16px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:16px}
.alert-ok{background:#eefbee;color:#22a35c;border:1px solid #c3f0d0}
.alert-err{background:#fff0f0;color:#e02020;border:1px solid #ffd0d0}

/* ── BREADCRUMB ── */
.breadcrumb{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--c-mid);margin-bottom:28px;flex-wrap:wrap}
.breadcrumb a:hover{color:var(--c-dark)}
.breadcrumb span{color:#ccc}

/* ── EMPTY STATE ── */
.empty{text-align:center;padding:80px 20px;color:var(--c-mid)}
.empty .empty-icon{font-size:56px;margin-bottom:16px}
.empty h3{font-size:20px;font-weight:700;color:var(--c-dark);margin-bottom:8px}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;right:24px;background:var(--c-dark);color:#fff;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:var(--shadow-md);transform:translateY(80px);opacity:0;transition:all .3s;z-index:9999;display:flex;align-items:center;gap:10px;max-width:320px}
.toast.show{transform:none;opacity:1}
.toast.toast-ok{background:#22a35c}
.toast.toast-err{background:#e02020}

/* ── ADDED-TO-CART DRAWER ── */
.atc-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9998;opacity:0;visibility:hidden;transition:opacity .25s}
.atc-overlay.show{opacity:1;visibility:visible}
.atc-drawer{position:fixed;top:0;right:0;height:100%;width:380px;max-width:92vw;background:#fff;box-shadow:-8px 0 30px rgba(0,0,0,.15);z-index:9999;transform:translateX(100%);transition:transform .3s cubic-bezier(.2,.9,.3,1);display:flex;flex-direction:column}
.atc-drawer.show{transform:translateX(0)}
.atc-drawer-head{display:flex;align-items:center;justify-content:space-between;padding:20px 22px;border-bottom:1px solid var(--c-light)}
.atc-drawer-title{display:flex;align-items:center;gap:10px;font-size:16px;font-weight:700;color:var(--c-dark)}
.atc-check{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#22a35c;color:#fff;font-size:12px;font-weight:900}
.atc-close{background:none;border:none;font-size:24px;line-height:1;color:var(--c-mid);cursor:pointer;padding:2px 6px}
.atc-close:hover{color:var(--c-dark)}
.atc-items-list{flex:1;overflow-y:auto;padding:16px 22px}
.atc-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid var(--c-light)}
.atc-item:first-child{padding-top:0}
.atc-item:last-child{border-bottom:none}
.atc-item.atc-item-new{background:#f6fbf8;margin:0 -22px;padding:12px 22px;border-radius:8px}
.atc-item img{width:64px;height:64px;object-fit:cover;border-radius:10px;background:var(--c-bg);flex-shrink:0}
.atc-item-info{display:flex;flex-direction:column;gap:4px;min-width:0;flex:1}
.atc-item-name{font-size:13.5px;font-weight:700;color:var(--c-dark);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.atc-item-meta{font-size:12px;color:var(--c-mid)}
.atc-item-price{font-size:14px;font-weight:800;color:var(--c-dark);display:flex;align-items:center;gap:8px}
.atc-item-price-old{font-size:12px;font-weight:600;color:#aaa;text-decoration:line-through}
.atc-drawer-footer{padding:16px 22px 20px;border-top:1px solid var(--c-light);display:flex;flex-direction:column;gap:12px;flex-shrink:0}
.atc-subtotal-row{display:flex;justify-content:space-between;align-items:center;font-size:14.5px;font-weight:700;color:var(--c-dark)}
.atc-btn-primary{display:block;text-align:center;padding:13px;background:var(--c-dark);color:#fff;border-radius:50px;font-size:14px;font-weight:700;transition:background .2s}
.atc-btn-primary:hover{background:var(--c-accent-h)}
.atc-btn-secondary{display:block;width:100%;text-align:center;padding:13px;background:#fff;color:var(--c-dark);border:1.5px solid var(--c-light);border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s}
.atc-btn-secondary:hover{border-color:var(--c-dark)}
@media(max-width:480px){
  .atc-drawer{width:100%;max-width:100%}
}

/* ── BOTTOM NAV ── */
@media(max-width:768px){
  body{padding-bottom:58px}
  footer{display:none}
  .toast{bottom:72px;right:14px;left:14px;max-width:none;justify-content:center}
  div.phpdebugbar,div.phpdebugbar-openhandler{display:none !important}
  #mob-nav{
    display:flex !important;
    position:fixed;
    bottom:0;left:0;right:0;
    height:58px;
    background:#fff;
    border-top:1.5px solid var(--c-light);
    box-shadow:0 -2px 10px rgba(0,0,0,.07);
    z-index:9999;
    align-items:stretch;
  }
  #mob-nav a{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:3px;
    color:#888;
    font-size:9px;
    font-weight:600;
    text-decoration:none;
    border:none;
    background:none;
    padding:0;
    -webkit-tap-highlight-color:rgba(0,0,0,.08);
    cursor:pointer;
  }
  #mob-nav a.on,#mob-nav a:active{color:var(--c-orange)}
  #mob-nav a svg{display:block}
  #mob-nav a span{display:block;line-height:1}
  #mob-nav .mn-badge{
    position:absolute;
    top:6px;right:calc(50% - 20px);
    background:var(--c-orange);color:#fff;
    font-size:9px;font-weight:800;
    min-width:15px;height:15px;border-radius:50px;
    display:flex;align-items:center;justify-content:center;
    padding:0 3px;border:2px solid #fff;pointer-events:none;
  }
  #mob-nav a{position:relative}
}

/* ── FOOTER ── */
footer{background:var(--c-dark);color:rgba(255,255,255,.6);padding:40px 24px;margin-top:auto}
.footer-inner{max-width:1280px;margin:auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.footer-logo{font-size:18px;font-weight:800;color:#fff}
.footer-logo span{color:var(--c-orange)}
.footer-links{display:flex;gap:20px;font-size:13px;flex-wrap:wrap}
.footer-links a:hover{color:#fff}
.footer-note{font-size:12px}

/* ── MOBILE NAV ── */
.nav-hamburger{display:none;flex-direction:column;justify-content:center;gap:5px;width:40px;height:40px;border:none;background:none;cursor:pointer;padding:8px;border-radius:10px;transition:background .15s;flex-shrink:0;margin-left:auto}
.nav-hamburger:hover{background:var(--c-tag)}
.nav-hamburger span{display:block;height:2px;background:var(--c-dark);border-radius:2px;transition:all .28s}
.nav-hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.nav-hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0)}
.nav-hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.nav-mobile-menu{display:none;position:fixed;top:64px;left:0;right:0;bottom:0;z-index:99}
.nav-mobile-menu.open{display:block}
.nav-mobile-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.4)}
.nav-mobile-panel{position:relative;background:#fff;padding:16px 20px 24px;z-index:1;overflow-y:auto;max-height:calc(100vh - 64px);border-bottom:1px solid var(--c-light);box-shadow:0 8px 32px rgba(0,0,0,.12)}
.nav-mobile-search{display:flex;background:var(--c-bg);border:1.5px solid var(--c-light);border-radius:50px;overflow:hidden;margin-bottom:16px}
.nav-mobile-search input{flex:1;padding:10px 18px;background:none;border:none;outline:none;font-size:14px;color:var(--c-dark)}
.nav-mobile-search button{padding:10px 14px;background:none;border:none;color:var(--c-mid);font-size:16px}
.nav-mobile-links{display:flex;flex-direction:column;gap:2px}
.nav-mobile-links a{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;font-size:15px;font-weight:600;color:var(--c-dark);transition:background .12s;text-decoration:none}
.nav-mobile-links a:hover,.nav-mobile-links a.active{background:var(--c-tag)}
.nav-mobile-divider{border:none;border-top:1.5px solid var(--c-light);margin:10px 0}
.nav-mobile-signout{width:100%;text-align:left;background:none;border:none;display:flex;align-items:center;gap:10px;padding:12px 14px;font-size:15px;font-weight:600;color:#e02020;border-radius:10px;cursor:pointer;font-family:inherit;transition:background .12s}
.nav-mobile-signout:hover{background:#fff0f0}

/* ── SHOP FILTER TOGGLE (mobile only) ── */
.shop-filter-toggle{display:none;align-items:center;gap:8px;padding:10px 18px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:50px;font-size:13.5px;font-weight:700;cursor:pointer;color:var(--c-dark);margin-bottom:14px;transition:all .15s;width:auto}
.shop-filter-toggle:hover{border-color:#999;background:var(--c-tag)}

@media(max-width:1024px){
  .checkout-layout{grid-template-columns:1fr}
  .cart-layout{grid-template-columns:1fr}
  .cart-summary{position:static;order:-1;padding:14px 16px}
  .cart-summary h3{font-size:15px;margin-bottom:12px}
  .summary-row{font-size:12px;margin-bottom:8px}
  .summary-divider{margin:8px 0}
  .total-row{font-size:14px}
  .coupon-box{padding:4px 4px 4px 12px;margin-bottom:10px;font-size:12px}
  .checkout-btn{padding:12px;font-size:13px;margin-top:14px}
  .payment-icons{font-size:11px;gap:8px;margin-top:10px}
  .ck-summary{position:static}
}
@media(max-width:900px){
  .hero{padding:48px 32px;min-height:auto}.hero-title{font-size:32px}
  .shop-layout{grid-template-columns:1fr}
  .sidebar{position:static;display:none}
  .sidebar.mobile-open{display:block}
  .shop-filter-toggle{display:flex}
  .product-layout{grid-template-columns:1fr}
  .gallery-wrap{flex-direction:column-reverse}
  .gallery-thumbs{flex-direction:row;width:100%;max-height:none;overflow-x:auto;overflow-y:hidden}
  .gallery-thumb{flex-shrink:0}
  .product-grid.cols-4{grid-template-columns:repeat(2,1fr)}
  .cart-row{flex-direction:column;align-items:stretch;padding:16px 0}
  .cart-prod{flex:unset;width:100%}
  .cart-row-price{text-align:left;padding-top:12px;margin-left:96px}
  .cart-price{width:auto;font-size:13px}
}
@media(max-width:768px){
  .nav-links{display:none}
  .nav-search{display:none}
  .nav-hamburger{display:flex}
  .page{padding:20px 14px 48px}
  .acc-main{padding:22px 16px}
  /* Hide admin/vendor portal buttons from top nav — use bottom nav instead */
  .nav-portal{display:none}
}
@media(max-width:600px){
  .hero{padding:28px 18px}.hero-title{font-size:22px;letter-spacing:-.3px}.hero::after{display:none}.hero-sub{font-size:13.5px;margin-bottom:22px}
  .product-grid{grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px}
  .product-card-body{padding:10px}
  .product-card-name{font-size:12.5px}
  .price-main{font-size:14px}
  .card-add-btn{padding:8px 10px;font-size:12px}
  .card-details-btn{padding:8px 10px;font-size:12px}
  .sec-title{font-size:18px}
  .form-grid-2{grid-template-columns:1fr}
  .acc-sidebar{padding:14px;border-radius:12px}
  .acc-avatar-block{display:none}
  .acc-nav{flex-direction:row;flex-wrap:nowrap;overflow-x:auto;gap:4px;padding-bottom:6px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
  .acc-nav::-webkit-scrollbar{display:none}
  .acc-nav-item{flex-shrink:0;padding:8px 14px;font-size:12.5px;white-space:nowrap;border-radius:50px}
  .acc-nav-item svg{display:none}
  .acc-signout-btn{display:none}
  .orders-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:var(--radius-lg)}
  .orders-table{min-width:520px}
  .breadcrumb{font-size:12px;margin-bottom:18px}
  footer{padding:28px 16px}
  .footer-inner{flex-direction:column;align-items:flex-start;gap:12px}
  .footer-links{gap:12px}
  .btn{padding:11px 20px;font-size:13.5px}
  .cart-row{border-radius:12px}
  .product-layout{gap:24px}
  .reviews-section{margin-top:36px;padding-top:28px}
  .coupon-card{min-width:200px}
}

/* ── PRODUCT CARD VARIATION SWATCHES ── */
.pc-swatches{display:flex;gap:5px;flex-wrap:wrap;margin:6px 0 4px}
.pc-selected{font-size:11px;line-height:1.3;color:var(--c-mid);min-height:0}
.pc-selected:empty{display:none}
.pc-swatch{width:20px;height:20px;border-radius:50%;border:2px solid rgba(0,0,0,.12);cursor:pointer;transition:all .15s;flex-shrink:0;padding:0;outline:2.5px solid transparent;outline-offset:2px}
.pc-swatch:hover{outline-color:#aaa}
.pc-swatch.selected{outline-color:var(--c-dark);border-color:transparent}
.pc-sizes{display:flex;gap:4px;flex-wrap:wrap;margin:4px 0}
.pc-size{padding:3px 9px;font-size:11px;font-weight:600;border:1.5px solid var(--c-light);border-radius:6px;background:var(--c-white);color:var(--c-mid);cursor:pointer;transition:all .12s;line-height:1.4}
.pc-size:hover,.pc-size.selected{border-color:var(--c-dark);color:var(--c-dark);background:var(--c-tag)}
.pc-size.unavail{opacity:.35;text-decoration:line-through;cursor:not-allowed}
/* ── HOMEPAGE TIMELINE SECTIONS ── */
.tl-banner-slider{position:relative;overflow:hidden;border-radius:8px;margin-bottom:32px}
.tl-slides{display:flex;transition:transform .5s cubic-bezier(.4,0,.2,1)}
.tl-slide{min-width:100%;position:relative}
.tl-slide img{width:100%;display:block;object-fit:cover}
@media(max-width:640px){
  .tl-banner-slider{max-height:none !important}
  .tl-slide img{height:56vw !important;max-height:none !important}
}
.tl-slide-link{display:block;position:relative}
.tl-dots{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);display:flex;gap:6px}
.tl-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.5);cursor:pointer;transition:.2s}
.tl-dot.active{background:#fff;width:20px;border-radius:4px}
.tl-arrow{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.45);color:#fff;border:none;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;z-index:2;transition:.2s}
.tl-arrow:hover{background:rgba(0,0,0,.7)}
.tl-arrow.prev{left:10px}
.tl-arrow.next{right:10px}
.tl-static-banner{display:block;margin-bottom:28px}
.tl-static-banner img{width:100%;object-fit:cover;border-radius:6px}
/* Category strip */
.tl-cat-strip{display:flex;gap:14px;overflow-x:auto;padding:4px 2px 12px;margin-bottom:32px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.tl-cat-strip::-webkit-scrollbar{display:none}
.tl-cat-item{display:flex;flex-direction:column;align-items:center;gap:7px;flex-shrink:0;text-decoration:none;color:inherit;transition:.18s}
.tl-cat-item:hover .tl-cat-img{transform:scale(1.06)}
.tl-cat-img-wrap{width:68px !important;height:68px !important;min-width:68px;min-height:68px;border-radius:50%;overflow:hidden;border:2px solid rgba(0,0,0,.1);flex-shrink:0;background:#f0ede8}
.tl-cat-img-wrap img{width:68px !important;height:68px !important;max-width:68px !important;object-fit:cover;display:block;border-radius:50%}
.tl-cat-chip{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px}
.tl-cat-label{font-size:11px;font-weight:600;color:var(--c-mid);text-align:center;max-width:72px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* Category Cards */
.cc-card{display:block;text-decoration:none;position:relative;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.13);transition:transform .22s ease,box-shadow .22s ease}
.cc-card:hover{transform:scale(1.025);box-shadow:0 10px 36px rgba(0,0,0,.22)}
.cc-card:hover .cc-img{transform:scale(1.07)}
.cc-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s ease}
.cc-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:48px}
.cc-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.72) 0%,rgba(0,0,0,.22) 55%,transparent 100%)}
.cc-label{position:absolute;bottom:0;left:0;right:0;padding:16px 18px}
.cc-name{color:#fff;font-size:16px;font-weight:800;text-shadow:0 1px 8px rgba(0,0,0,.55);line-height:1.3}
.cc-count{color:rgba(255,255,255,.75);font-size:12px;margin-top:4px;font-weight:500}
@media(max-width:640px){.cc-name{font-size:13px}.cc-label{padding:12px 12px}}
/* Scroll sections */
.tl-scroll-section{overflow-x:auto;scrollbar-width:none;padding-bottom:4px;-webkit-overflow-scrolling:touch}
.tl-scroll-section::-webkit-scrollbar{display:none}
.tl-scroll-track{display:flex;gap:12px}
.tl-scroll-card{flex-shrink:0;width:140px}
.tl-scroll-card .product-card-img{height:196px}
.tl-scroll-wrap{position:relative}
.tl-scroll-arrow{position:absolute;top:calc(50% - 14px);transform:translateY(-50%);background:rgba(0,0,0,.5);color:#fff;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:20px;line-height:1;display:flex;align-items:center;justify-content:center;z-index:2;transition:background .2s,opacity .2s;box-shadow:0 2px 8px rgba(0,0,0,.25)}
.tl-scroll-arrow:hover{background:rgba(0,0,0,.75)}
.tl-scroll-arrow.prev{left:-6px}
.tl-scroll-arrow.next{right:-6px}
.tl-scroll-arrow:disabled{opacity:.25;cursor:default;pointer-events:none}
@media(hover:none){.tl-scroll-arrow{display:none !important}}
@media(max-width:768px){.tl-scroll-arrow{display:none !important}}
/* Utility */
.tl-spacer{display:block}
.tl-divider{border:none;border-top:1px solid var(--c-light);margin:8px 0}
.brand-strip{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:32px}
.brand-chip{padding:8px 18px;border:1px solid var(--c-light);border-radius:24px;font-size:13px;font-weight:600;color:var(--c-mid);background:var(--c-white);transition:.15s}
.brand-chip:hover{border-color:var(--c-dark);color:var(--c-dark)}

/* ── Vendor Cards (homepage strip) ─────────────────────────── */
.vendor-card{
  display:flex;flex-direction:column;align-items:center;
  width:120px;flex-shrink:0;text-decoration:none;color:inherit;
  background:#fff;border:1px solid var(--c-light);border-radius:14px;
  padding:14px 10px 12px;transition:box-shadow .2s,transform .15s;
  text-align:center;
}
.vendor-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.1);transform:translateY(-2px);border-color:#ddd}
.vendor-card-logo{
  width:64px;height:64px;border-radius:12px;overflow:hidden;
  background:#f5f5f2;margin-bottom:10px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.vendor-card-logo img{width:100%;height:100%;object-fit:cover}
.vendor-card-logo-placeholder{font-size:28px}
.vendor-card-name{font-size:12px;font-weight:700;color:var(--c-dark);line-height:1.3;margin-bottom:4px}
.vendor-card-count{font-size:11px;color:var(--c-mid);margin-bottom:2px}
.vendor-card-rating{font-size:11px;color:var(--c-mid)}

/* ── Vendor banner on product page ──────────────────────────── */
.vendor-banner-card{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 20px;background:#fafaf8;border:1px solid var(--c-light);
  border-radius:14px;gap:12px;
}
.vendor-banner-left{display:flex;align-items:center;gap:14px;flex:1;min-width:0}
.vendor-banner-logo{
  width:52px;height:52px;border-radius:10px;object-fit:cover;
  border:1px solid var(--c-light);flex-shrink:0;
}
.vendor-banner-logo-ph{
  width:52px;height:52px;border-radius:10px;background:#f0f0ec;
  display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;
}
.vendor-banner-info{min-width:0}
.vendor-banner-name{font-size:15px;font-weight:700;color:var(--c-dark);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vendor-banner-meta{font-size:12px;color:var(--c-mid);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vendor-banner-btn{
  padding:9px 18px;background:var(--c-orange);color:#fff;border-radius:8px;
  font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;flex-shrink:0;
  transition:background .2s;
}
.vendor-banner-btn:hover{background:#d44f1a}
</style>
@stack('styles')
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="nav-inner">
    <a href="{{ route('home') }}" class="nav-logo">Ramo<span>Store</span></a>
    <button class="nav-hamburger" id="nav-hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
    <div class="nav-links">
      <a href="{{ route('home') }}"  class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
      <a href="{{ route('shop') }}"  class="{{ request()->routeIs('shop') ? 'active' : '' }}">Shop</a>
      <a href="{{ route('order.track') }}" class="{{ request()->routeIs('order.track*') ? 'active' : '' }}">Track Order</a>
    </div>
    <div class="nav-search">
      <form action="{{ route('search') }}" method="GET">
        <svg class="nav-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" placeholder="Search products…" value="{{ request('q', request('search')) }}" autocomplete="off">
        <button type="submit" aria-label="Search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </form>
    </div>
    <div class="nav-actions">
      {{-- Wishlist --}}
      <a href="{{ route('wishlist') }}" class="nav-icon-btn" title="Wishlist">
        ♡
        @php $wCount = count(session('ramo_wishlist',[])); @endphp
        @if($wCount)<span class="nav-badge">{{ $wCount }}</span>@endif
      </a>

      {{-- Cart --}}
      <a href="{{ route('cart') }}" class="nav-icon-btn" title="Cart">
        🛒
        @php $cCount = count(session('ramo_cart',[])); @endphp
        @if($cCount)<span class="nav-badge" id="cart-badge">{{ $cCount }}</span>@endif
      </a>

      {{-- Admin dropdown --}}
      @auth
        @php
          $__u = Auth::user();
          $__isAdmin = $__u->email === 'adminramoui@gmail.com'
            || str_contains((string)$__u->role, 'admin');
        @endphp
        @if($__isAdmin)
          <div class="nav-portal" id="admin-portal">
            <button class="nav-dashboard-btn" onclick="togglePortal('admin-portal',event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
              Admin Panel
              <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="nav-portal-dropdown">
              <a href="{{ route('admin.dashboard') }}" class="nav-portal-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
              </a>
              <a href="/api-guide.html" target="_blank" class="nav-portal-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                API Docs
              </a>
              <hr class="nav-portal-divider">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-portal-item danger">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                  Sign Out
                </button>
              </form>
            </div>
          </div>
        @endif
      @endauth

      {{-- Vendor dropdown --}}
      @if(auth()->guard('vendor_web')->check())
        @php $__vw = auth()->guard('vendor_web')->user(); @endphp
        <div class="nav-portal" id="vendor-portal">
          <button class="nav-dashboard-btn nav-dashboard-vendor" onclick="togglePortal('vendor-portal',event)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            {{ Str::limit($__vw->shop_name, 14) }}
            <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="nav-portal-dropdown">
            <a href="{{ route('vendor.dashboard') }}" class="nav-portal-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
              My Dashboard
            </a>
            <a href="{{ route('vendor.store', $__vw->id) }}" target="_blank" class="nav-portal-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
              My Store
            </a>
            <hr class="nav-portal-divider">
            <form method="POST" action="{{ route('vendor.logout') }}">
              @csrf
              <button type="submit" class="nav-portal-item danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
              </button>
            </form>
          </div>
        </div>
      @endif

      {{-- Account --}}
      @auth
        @php $__au = Auth::user(); @endphp
        <div class="nav-portal" id="account-portal">
          <button class="nav-user-btn" onclick="togglePortal('account-portal',event)" style="padding:7px 12px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ Str::limit($__au->first_name ?: $__au->name, 12) }}
            <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="nav-portal-dropdown" style="right:0;left:auto">
            <a href="{{ route('account.profile') }}" class="nav-portal-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Profile
            </a>
            <a href="{{ route('account.orders') }}" class="nav-portal-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
              My Orders
            </a>
            <a href="{{ route('wishlist') }}" class="nav-portal-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
              Wishlist
            </a>
            <hr class="nav-portal-divider">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="nav-portal-item danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
              </button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="nav-user-btn">Sign In</a>
      @endauth
    </div>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="nav-mobile-menu" id="nav-mobile-menu">
  <div class="nav-mobile-backdrop" onclick="closeMobileMenu()"></div>
  <div class="nav-mobile-panel">
    <form action="{{ route('search') }}" method="GET" class="nav-mobile-search">
      <svg style="margin-left:14px;flex-shrink:0;color:var(--c-mid)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Search products…" value="{{ request('q', request('search')) }}" autocomplete="off">
      <button type="submit" aria-label="Search" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;margin:3px;border-radius:50px;background:var(--c-dark);border:none;color:#fff;cursor:pointer;flex-shrink:0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </form>
    <div class="nav-mobile-links">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">🏠 Home</a>
      <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">🛍️ Shop</a>
      <a href="{{ route('cart') }}" class="{{ request()->routeIs('cart') ? 'active' : '' }}">
        🛒 Cart
        @php $__mc = count(session('ramo_cart',[])); @endphp
        @if($__mc) <span style="background:var(--c-orange);color:#fff;font-size:11px;font-weight:800;padding:1px 7px;border-radius:50px;margin-left:4px">{{ $__mc }}</span> @endif
      </a>
      <a href="{{ route('wishlist') }}" class="{{ request()->routeIs('wishlist') ? 'active' : '' }}">♡ Wishlist</a>
      <a href="{{ route('order.track') }}" class="{{ request()->routeIs('order.track*') ? 'active' : '' }}">📦 Track Order</a>
      <hr class="nav-mobile-divider">
      @auth
        @php $__mu = Auth::user(); @endphp
        <a href="{{ route('account.hub') }}">👤 {{ $__mu->first_name ?: $__mu->name }}</a>
        <a href="{{ route('account.orders') }}">📋 My Orders</a>
        @php $__isAdm = $__mu->email === 'adminramoui@gmail.com' || str_contains((string)$__mu->role,'admin'); @endphp
        @if($__isAdm)<a href="{{ route('admin.dashboard') }}">⚙️ Admin Panel</a>@endif
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-mobile-signout">🚪 Sign Out</button>
        </form>
      @else
        <a href="{{ route('login') }}">👤 Sign In</a>
        <a href="{{ route('register') }}" style="color:var(--c-orange)">✨ Register</a>
      @endauth
      @if(auth()->guard('vendor_web')->check())
        @php $__vmu = auth()->guard('vendor_web')->user(); @endphp
        <hr class="nav-mobile-divider">
        <a href="{{ route('vendor.dashboard') }}" style="color:var(--c-orange)">🏪 {{ Str::limit($__vmu->shop_name, 20) }}</a>
        <form method="POST" action="{{ route('vendor.logout') }}">
          @csrf
          <button type="submit" class="nav-mobile-signout">🚪 Sign Out (Vendor)</button>
        </form>
      @endif
    </div>
  </div>
</div>

@yield('content')

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-logo">Ramo<span>Store</span></div>
    <div class="footer-links">
      <a href="{{ route('home') }}">Home</a>
      <a href="{{ route('shop') }}">Shop</a>
      <a href="{{ route('wishlist') }}">Wishlist</a>
      <a href="{{ route('cart') }}">Cart</a>
      @auth<a href="{{ route('account.orders') }}">My Orders</a>@endauth
      <a href="{{ route('vendor.register') }}" style="color:var(--c-orange);font-weight:600">Sell on Ramo</a>
    </div>
    <div class="footer-note">© {{ date('Y') }} RamoStore. All rights reserved.</div>
  </div>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <span id="toast-icon">🛍️</span>
  <span id="toast-msg">Done!</span>
</div>

<!-- ADDED-TO-CART DRAWER -->
<div class="atc-overlay" id="atc-overlay" onclick="closeAtcDrawer()"></div>
<div class="atc-drawer" id="atc-drawer">
  <div class="atc-drawer-head">
    <div class="atc-drawer-title"><span class="atc-check">✓</span> Added to cart</div>
    <button class="atc-close" onclick="closeAtcDrawer()" aria-label="Close">&times;</button>
  </div>
  <div class="atc-items-list" id="atc-items-list"></div>
  <div class="atc-drawer-footer">
    <div class="atc-subtotal-row">
      <span>Subtotal</span>
      <span id="atc-subtotal">EGP 0.00</span>
    </div>
    <a href="{{ route('cart') }}" class="atc-btn-primary" id="atc-go-cart">Go to Cart</a>
    <button class="atc-btn-secondary" onclick="closeAtcDrawer()">Continue Shopping</button>
  </div>
</div>

<!-- MOBILE BOTTOM NAV -->
<nav id="mob-nav" style="display:none">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
    <span>Home</span>
  </a>
  <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    <span>Shop</span>
  </a>
  <a href="{{ route('cart') }}" class="{{ request()->routeIs('cart') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
    @if($cCount ?? 0)<span class="mn-badge" id="mn-cart-badge">{{ $cCount }}</span>@endif
    <span>Cart</span>
  </a>
  <a href="{{ route('wishlist') }}" class="{{ request()->routeIs('wishlist') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
    <span>Wishlist</span>
  </a>
  @auth
  <a href="{{ route('account.hub') }}" class="{{ request()->routeIs('account.*') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span>Account</span>
  </a>
  @else
  <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'on' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
    <span>Sign In</span>
  </a>
  @endauth
</nav>

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';

// ── Device-side page cache (Home + Shop) ──────────────────────────────────
// Registers a Service Worker that caches these two pages in the browser's
// own Cache Storage (nothing stored on the backend). Repeat visits render
// instantly from that cache while a background request refreshes it for
// next time. See public/sw.js for the caching strategy.
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/sw.js').catch(function () {});
  });
}

// Whatever the page shipped with for the cart badge count may be a stale
// cached copy — always reconcile it against the live count right after paint.
(function refreshCartBadgeFromServer() {
  fetch('/cart/count', { headers: { 'Accept': 'application/json' } })
    .then((r) => (r.ok ? r.json() : null))
    .then((data) => {
      if (data && typeof data.count === 'number') updateCartBadge(data.count);
    })
    .catch(() => {});
})();

// ── Remember scroll position per page (stored on-device, in localStorage) ─
// Every page's scroll offset is saved as the user scrolls, keyed by its
// path+query, and restored automatically the next time that exact page is
// opened — whether via Back/Forward, a fresh link click, or the cached
// Home/Shop pages above.
(function scrollMemory() {
  const KEY_PREFIX = 'ramo_scroll::';
  const key = KEY_PREFIX + location.pathname + location.search;

  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  function restore() {
    const saved = sessionStorage.getItem(key) || localStorage.getItem(key);
    if (!saved) return;
    const y = parseInt(saved, 10);
    if (!isNaN(y) && y > 0) window.scrollTo(0, y);
  }

  // Restore once the page (incl. images) has finished laying out, and again
  // shortly after in case late-loading content shifted the page height.
  window.addEventListener('load', function () {
    restore();
    setTimeout(restore, 300);
  });
  if (document.readyState === 'complete') restore();

  let ticking = false;
  function saveScroll() {
    const y = window.scrollY || window.pageYOffset || 0;
    try {
      sessionStorage.setItem(key, String(y));
      localStorage.setItem(key, String(y));
    } catch (e) { /* storage full/blocked — ignore */ }
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(saveScroll);
    }
  }, { passive: true });
  window.addEventListener('pagehide', saveScroll);
  window.addEventListener('beforeunload', saveScroll);
})();

function toggleMobileMenu() {
  const menu = document.getElementById('nav-mobile-menu');
  const btn  = document.getElementById('nav-hamburger');
  const open = menu.classList.toggle('open');
  btn.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}
function closeMobileMenu() {
  document.getElementById('nav-mobile-menu').classList.remove('open');
  document.getElementById('nav-hamburger').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('nav-mobile-menu')?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMobileMenu));

function togglePortal(id, e) {
  if (e) e.stopPropagation();
  const el = document.getElementById(id);
  const isOpen = el.classList.contains('open');
  document.querySelectorAll('.nav-portal.open').forEach(p => p.classList.remove('open'));
  if (!isOpen) el.classList.add('open');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.nav-portal')) {
    document.querySelectorAll('.nav-portal.open').forEach(p => p.classList.remove('open'));
  }
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.nav-portal.open').forEach(p => p.classList.remove('open'));
  }
});

function showToast(msg, type = 'default') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast show' + (type === 'ok' ? ' toast-ok' : type === 'err' ? ' toast-err' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3200);
}

// Cart badge update (top nav + bottom nav)
function updateCartBadge(count) {
  // Top nav badge
  let badge = document.getElementById('cart-badge');
  if (!badge && count > 0) {
    const btn = document.querySelector('a[href="{{ route("cart") }}"].nav-icon-btn');
    if (btn) { badge = document.createElement('span'); badge.id='cart-badge'; badge.className='nav-badge'; btn.appendChild(badge); }
  }
  if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'flex' : 'none'; }
  // Bottom nav badge
  let bnBadge = document.getElementById('bn-cart-badge');
  if (!bnBadge && count > 0) {
    const bnIcon = document.querySelector('#bottom-nav .bn-icon');
    if (bnIcon) { bnBadge = document.createElement('span'); bnBadge.id='bn-cart-badge'; bnBadge.className='bn-badge'; bnIcon.appendChild(bnBadge); }
  }
  if (bnBadge) { bnBadge.textContent = count; bnBadge.style.display = count > 0 ? 'flex' : 'none'; }
}

// Add to cart (AJAX)
async function addToCart(productId, name, price, image, variationId = null, qty = 1, varLabel = null, oldPrice = null) {
  try {
    const res = await fetch('/cart/add', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF_TOKEN},
      body: JSON.stringify({ product_id: productId, variation_id: variationId, qty })
    });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(data.count);
      openAtcDrawer({ image, oldPrice, varLabel, items: data.items, count: data.count, cartTotal: data.cart_total, rowId: data.row_id });
    } else {
      showToast(data.message || 'Could not add to cart.', 'err');
    }
  } catch(e) {
    showToast('Network error. Try again.', 'err');
  }
}

// ── Added-to-cart drawer ─────────────────────────────────────────────
function openAtcDrawer({ image, oldPrice, varLabel, items, count, cartTotal, rowId }) {
  const list = document.getElementById('atc-items-list');
  list.innerHTML = '';

  (items || []).slice().reverse().forEach(item => {
    const isNew = rowId && item.rowId === rowId;
    const attrs = item.attrs && typeof item.attrs === 'object' ? Object.entries(item.attrs).map(([k,v]) => `${k}: ${v}`).join(', ') : (varLabel && isNew ? varLabel : '');
    const metaParts = [];
    if (item.sku) metaParts.push('SKU: ' + item.sku);
    if (attrs) metaParts.push(attrs);
    if (item.qty > 1) metaParts.push('Qty: ' + item.qty);

    const row = document.createElement('div');
    row.className = 'atc-item' + (isNew ? ' atc-item-new' : '');
    row.innerHTML = `
      <img src="${item.image || ''}" alt="${(item.name||'').replace(/"/g,'&quot;')}">
      <div class="atc-item-info">
        <div class="atc-item-name">${item.name || ''}</div>
        <div class="atc-item-meta">${metaParts.join(' • ')}</div>
        <div class="atc-item-price">
          <span>EGP ${Number(item.price).toFixed(2)}</span>
          ${isNew && oldPrice && Number(oldPrice) > Number(item.price) ? `<span class="atc-item-price-old">EGP ${Number(oldPrice).toFixed(2)}</span>` : ''}
        </div>
      </div>`;
    list.appendChild(row);
  });

  document.getElementById('atc-subtotal').textContent = 'EGP ' + Number(cartTotal || 0).toFixed(2);
  document.getElementById('atc-go-cart').textContent = 'Go to Cart (' + (count ?? '') + ')';

  document.getElementById('atc-overlay').classList.add('show');
  document.getElementById('atc-drawer').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeAtcDrawer() {
  document.getElementById('atc-overlay').classList.remove('show');
  document.getElementById('atc-drawer').classList.remove('show');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeAtcDrawer();
});

// ── Product card variation helpers ──────────────────────────────────
function _pcGetCard(pid) { return document.getElementById('pc-'+pid); }
function _pcGetVars(pid) {
  const card = _pcGetCard(pid);
  if (!card) return [];
  try { return JSON.parse(card.dataset.vars || '[]'); } catch(e){ return []; }
}
function _pcGetSel(pid) {
  const card = _pcGetCard(pid);
  if (!card) return {};
  return { color: card.dataset.selColor||'', size: card.dataset.selSize||'' };
}

function pcPickColor(pid, colorVal, btn) {
  const card = _pcGetCard(pid);
  if (!card) return;
  // Toggle off if same
  if (card.dataset.selColor === colorVal) {
    card.dataset.selColor = '';
    btn.classList.remove('selected');
  } else {
    card.dataset.selColor = colorVal;
    card.querySelectorAll('.pc-swatch').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    // Swap image if variation has one
    const imgEl = document.getElementById('pc-img-'+pid);
    if (imgEl && btn.dataset.img) { imgEl.src = btn.dataset.img; }
    else if (imgEl) { imgEl.src = card.dataset.baseImg; }
  }
  _pcUpdateSizes(pid);
  _pcUpdatePrice(pid);
  _pcUpdateSummary(pid);
}

function pcPickSize(pid, sizeVal, btn) {
  const card = _pcGetCard(pid);
  if (!card) return;
  if (card.dataset.selSize === sizeVal) {
    card.dataset.selSize = '';
    btn.classList.remove('selected');
  } else {
    card.dataset.selSize = sizeVal;
    card.querySelectorAll('.pc-size').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
  }
  _pcUpdatePrice(pid);
  _pcUpdateSummary(pid);
}

function _pcUpdateSizes(pid) {
  const card = _pcGetCard(pid);
  if (!card) return;
  const vars   = _pcGetVars(pid);
  const color  = card.dataset.selColor;
  const sizeEl = document.getElementById('pc-sizes-'+pid);
  if (!sizeEl || !color) return;
  // Show only sizes available for this color
  sizeEl.querySelectorAll('.pc-size').forEach(btn => {
    const size = btn.dataset.size;
    const avail = vars.some(v => v.attrs['Color'] === color && v.attrs['Size'] === size && v.stock > 0);
    const exists = vars.some(v => v.attrs['Color'] === color && v.attrs['Size'] === size);
    btn.classList.toggle('unavail', !avail && exists);
    btn.style.display = !exists ? 'none' : '';
  });
  // Deselect size if now hidden
  const selSize = card.dataset.selSize;
  if (selSize) {
    const stillVisible = vars.some(v => v.attrs['Color'] === color && v.attrs['Size'] === selSize);
    if (!stillVisible) { card.dataset.selSize = ''; card.querySelectorAll('.pc-size').forEach(b => b.classList.remove('selected')); }
  }
}

function _pcUpdatePrice(pid) {
  const card = _pcGetCard(pid);
  if (!card) return;
  const vars  = _pcGetVars(pid);
  const color = card.dataset.selColor;
  const size  = card.dataset.selSize;
  const priceEl = document.getElementById('pc-price-'+pid);
  const origEl  = document.getElementById('pc-orig-'+pid);
  const addBtn  = document.getElementById('pc-add-'+pid);
  if (!priceEl || !vars.length) return;
  // Find matching variation
  let match = null;
  if (color && size) {
    match = vars.find(v => v.attrs['Color'] === color && v.attrs['Size'] === size) || null;
  } else if (color && !vars.some(v => v.attrs['Size'])) {
    match = vars.find(v => v.attrs['Color'] === color) || null;
  } else if (size && !vars.some(v => v.attrs['Color'])) {
    match = vars.find(v => v.attrs['Size'] === size) || null;
  }
  if (match) {
    const displayPrice = match.sale && match.sale < match.price ? match.sale : match.price;
    priceEl.textContent = displayPrice.toFixed(2) + ' EGP';
    priceEl.className   = match.sale && match.sale < match.price ? 'price-main sale' : 'price-main';
    if (origEl) origEl.textContent = match.sale && match.sale < match.price ? match.price.toFixed(2) : '';
    card.dataset.selVar   = match.id;
    card.dataset.selPrice = displayPrice;
    if (addBtn) addBtn.textContent = match.stock > 0 ? 'Add to Cart' : 'Out of Stock';
  } else {
    // Show base / range
    const prices = vars.map(v => v.sale && v.sale < v.price ? v.sale : v.price);
    const mn = Math.min(...prices), mx = Math.max(...prices);
    priceEl.textContent = mn===mx ? mn.toFixed(2)+' EGP' : mn.toFixed(2)+' – '+mx.toFixed(2)+' EGP';
    card.dataset.selVar = '';
    card.dataset.selPrice = '';
    if (addBtn) addBtn.textContent = 'Add to Cart';
  }
}

function _pcUpdateSummary(pid) {
  const card = _pcGetCard(pid);
  if (!card) return;
  const el = document.getElementById('pc-selected-' + pid);
  if (!el) return;
  const color = card.dataset.selColor || '';
  const size = card.dataset.selSize || '';
  if (!color && !size) {
    el.textContent = '';
    return;
  }
  const parts = [];
  if (color) parts.push('Color: ' + color);
  if (size) parts.push('Size: ' + size);
  el.textContent = parts.join(' • ');
}

function pcAddToCart(pid) {
  const card    = _pcGetCard(pid);
  if (!card) return;
  const name    = card.querySelector('.card-add-btn')?.dataset.name  || '';
  const baseImg = card.dataset.baseImg || '';
  const curImg  = document.getElementById('pc-img-'+pid)?.src || baseImg;
  const varId   = card.dataset.selVar   ? parseInt(card.dataset.selVar) : null;
  const price   = card.dataset.selPrice ? parseFloat(card.dataset.selPrice) : parseFloat(card.dataset.basePrice);
  addToCart(pid, name, price, curImg, varId, 1);
}

// Toggle wishlist
async function toggleWishlist(btn, productId) {
  try {
    const res = await fetch('/wishlist/toggle', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF_TOKEN},
      body: JSON.stringify({ product_id: productId })
    });
    const data = await res.json();
    if (data.success) {
      const wished = data.action === 'added';
      btn.classList.toggle('wished', wished);
      btn.title = wished ? 'Remove from Wishlist' : 'Add to Wishlist';
      showToast(wished ? '♥ Saved to Wishlist' : 'Removed from Wishlist');
    }
  } catch(e) {}
}

// Mobile bottom nav — show on narrow screens, hide debugbar
(function mobNav() {
  var nav = document.getElementById('mob-nav');
  if (!nav) return;
  function check() {
    var mobile = window.innerWidth <= 768;
    nav.style.display = mobile ? 'flex' : 'none';
    if (mobile) {
      document.querySelectorAll('div.phpdebugbar,div.phpdebugbar-openhandler').forEach(function(e){
        e.style.cssText += ';display:none!important';
      });
    }
  }
  check();
  window.addEventListener('resize', check);
  setTimeout(check, 800);
})();
</script>
@stack('scripts')
<script>
function saveCouponAndGo(code, cartUrl) {
  try { localStorage.setItem('pending_coupon', code); } catch(e) {}
  window.location.href = cartUrl;
}
</script>
</body>
</html>
