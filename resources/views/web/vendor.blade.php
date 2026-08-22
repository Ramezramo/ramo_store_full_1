@extends('layouts.app')
@php $isAr = session('locale') === 'ar'; @endphp
@section('title', $vendor->shop_name . ($isAr ? ' — Ramo Store' : ' — Ramo Store'))

@section('content')
<div class="page vendor-storefront {{ $isAr ? 'vendor-storefront-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

  {{-- VENDOR HEADER --}}
  <div class="vendor-page-header">
    @if($vendor->banner_url)
      <div class="vendor-page-banner" style="background-image:url('{{ $vendor->banner_url }}')"></div>
    @else
      <div class="vendor-page-banner vendor-page-banner-default"></div>
    @endif

    <div class="vendor-page-identity">
      <div class="vendor-page-logo-wrap">
        @if($vendor->logo_url)
          <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="vendor-page-logo">
        @else
          <div class="vendor-page-logo-ph">🏪</div>
        @endif
      </div>
      <div class="vendor-page-meta">
        <h1 class="vendor-page-name">{{ $vendor->shop_name }}</h1>
        <div class="vendor-page-stats">
          @if((float)$vendor->rating > 0)
            <span class="vendor-stat"><span style="color:#f5a623">★</span> {{ number_format((float)$vendor->rating,1) }}
              @if($vendor->rating_count > 0)({{ $vendor->rating_count }})@endif
            </span>
          @endif
          @if($vendor->product_count > 0)
            <span class="vendor-stat">🛍️ {{ $vendor->product_count }} {{ $isAr ? 'منتج' : 'products' }}</span>
          @endif
          @if($vendor->shop_address)
            <span class="vendor-stat">📍 {{ $vendor->shop_address }}</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- SORT + PRODUCT GRID --}}
  <div class="vendor-page-toolbar">
    <div style="font-size:14px;color:var(--c-mid)">
      {{ $products->total() }} {{ $isAr ? 'منتج' : 'product'.($products->total() != 1 ? 's' : '') }}
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <span style="font-size:13px;color:var(--c-mid)">{{ $isAr ? 'رتّب:' : 'Sort:' }}</span>
      <select onchange="location.href=this.value" class="sort-select" style="font-size:13px">
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'']) }}" {{ !request('sort') ? 'selected' : '' }}>{{ $isAr ? 'الأحدث' : 'Newest' }}</option>
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_asc']) }}" {{ request('sort')==='price_asc' ? 'selected' : '' }}>{{ $isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low → High' }}</option>
        <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_desc']) }}" {{ request('sort')==='price_desc' ? 'selected' : '' }}>{{ $isAr ? 'السعر: من الأعلى للأقل' : 'Price: High → Low' }}</option>
      </select>
    </div>
  </div>

  @if($products->count())
    <div class="product-grid vendor-products-grid" style="margin-bottom:40px">
      @foreach($products as $p)
        @include('web.partials.product-card', ['p' => $p, 'cardVariations' => []])
      @endforeach
    </div>

    <div style="margin-bottom:40px">
      {{ $products->links() }}
    </div>
  @else
    <div style="text-align:center;padding:80px 20px;color:var(--c-mid)">
      <div style="font-size:48px;margin-bottom:16px">📦</div>
      <div style="font-size:18px;font-weight:600">{{ $isAr ? 'مفيش منتجات لسه' : 'No products yet' }}</div>
      <div style="font-size:14px;margin-top:8px">{{ $isAr ? 'المتجر ده ما نزلش منتجات لسه.' : "This vendor hasn't listed any products." }}</div>
      <a href="{{ route('shop') }}" class="btn btn-primary" style="margin-top:24px;display:inline-block">{{ $isAr ? 'تصفّح المتجر' : 'Browse Shop' }}</a>
    </div>
  @endif

</div>
@endsection

@push('styles')
<style>
.vendor-page-header { margin-bottom: 32px; }

.vendor-page-banner {
  width: 100%; height: 180px; border-radius: 14px; object-fit: cover;
  background-size: cover; background-position: center;
  margin-bottom: -50px;
}
.vendor-page-banner-default {
  background: linear-gradient(135deg, #e85d26 0%, #f59e0b 50%, #22c55e 100%);
}

.vendor-page-identity {
  display: flex; align-items: flex-end; gap: 16px;
  padding: 0 4px; position: relative; z-index: 1;
}

.vendor-page-logo-wrap {
  width: 90px; height: 90px; border-radius: 16px;
  border: 3px solid #fff; overflow: hidden;
  background: #f5f5f5; flex-shrink: 0;
  box-shadow: 0 2px 12px rgba(0,0,0,.12);
}
.vendor-page-logo { width: 100%; height: 100%; object-fit: cover; }
.vendor-page-logo-ph {
  width: 100%; height: 100%; display: flex; align-items: center;
  justify-content: center; font-size: 36px; background: #f0f0ec;
}

.vendor-page-meta { padding-bottom: 4px; }
.vendor-page-name { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
.vendor-page-stats { display: flex; flex-wrap: wrap; gap: 12px; }
.vendor-stat { font-size: 13px; color: var(--c-mid); }

.vendor-page-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 20px; padding: 12px 16px;
  background: #fafaf8; border-radius: 10px;
  border: 1px solid #ebebeb;
}

.sort-select {
  padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px;
  background: #fff; cursor: pointer; outline: none;
}

.vendor-storefront-ar{font-family:'Cairo','Tahoma',sans-serif;text-align:right}.vendor-storefront-ar .vendor-page-identity,.vendor-storefront-ar .vendor-page-toolbar{direction:rtl}.vendor-storefront-ar .vendor-page-toolbar{flex-direction:row}

/* Vendor storefront cards keep a comfortable desktop layout while the phone view
   uses a deliberately compact two-column grid. */
.vendor-products-grid{align-items:stretch}
.vendor-products-grid .product-card{min-width:0;border-radius:14px;box-shadow:0 5px 18px rgba(35,25,16,.06)}
.vendor-products-grid .product-card-img{aspect-ratio:1 / 1.08}
.vendor-products-grid .product-card-body{min-width:0;padding:12px 10px 10px;gap:7px}
.vendor-products-grid .product-card-name{min-width:0}
.vendor-products-grid .product-card-price{gap:4px 6px}
.vendor-products-grid .pc-actions{min-width:0}
.vendor-products-grid .card-add-btn,.vendor-products-grid .card-details-btn{letter-spacing:0}

@media(max-width:600px){
  .vendor-storefront{padding-bottom:calc(var(--mobile-nav-height, 72px) + 24px)}
  .vendor-page-header{margin-bottom:24px}
  .vendor-page-banner{border-radius:16px}
  .vendor-page-identity{gap:10px;padding:0 2px}
  .vendor-page-meta{min-width:0;padding-bottom:2px}
  .vendor-page-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .vendor-page-stats{gap:5px 8px}
  .vendor-stat{font-size:11px;line-height:1.35}
  .vendor-page-toolbar{gap:10px;margin-bottom:16px;padding:10px 12px;border-radius:14px}
  .vendor-page-toolbar > div:first-child{font-size:12px !important;white-space:nowrap}
  .vendor-page-toolbar > div:last-child{min-width:0;gap:5px !important}
  .vendor-page-toolbar > div:last-child > span{font-size:11px !important;white-space:nowrap}
  .vendor-page-toolbar .sort-select{max-width:52vw;padding:8px 8px;font-size:12px !important}
  .vendor-products-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:12px 10px}
  .vendor-products-grid .product-card-img{aspect-ratio:1 / 1.08}
  .vendor-products-grid .product-card-body{padding:9px 8px 8px;gap:6px}
  .vendor-products-grid .product-card-name{font-size:12px;line-height:1.45}
  .vendor-products-grid .product-card-price{padding-top:0;gap:3px 5px}
  .vendor-products-grid .price-main{font-size:12.5px}
  .vendor-products-grid .price-old{font-size:11px}
  .vendor-products-grid .card-add-btn,.vendor-products-grid .card-details-btn{padding:8px 5px;font-size:10px;line-height:1.25}
  .vendor-products-grid .wish-btn{top:8px;right:8px;width:30px;height:30px;font-size:15px}
  .vendor-products-grid .product-card-img .badge-sale{top:8px;left:8px;padding:3px 6px;font-size:9px}
}

@media(max-width:360px){
  .vendor-products-grid{gap:10px 8px !important}
  .vendor-products-grid .product-card-body{padding-left:6px;padding-right:6px}
  .vendor-products-grid .card-add-btn,.vendor-products-grid .card-details-btn{padding-left:3px;padding-right:3px;font-size:9.5px}
}

@media(max-width:640px) {
  .vendor-page-banner { height: 120px; margin-bottom: -36px; }
  .vendor-page-logo-wrap { width: 68px; height: 68px; }
  .vendor-page-name { font-size: 17px; }
}
</style>
@endpush
