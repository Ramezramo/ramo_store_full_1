@extends('web.vendor.layout')

@php
  $isAr = session('locale') === 'ar';
  $text = static fn ($en, $ar) => $isAr ? $ar : $en;
  $live = in_array($vendor->status, ['approved', 'active'], true);
  $status = match ($vendor->status) {
    'approved', 'active' => $text('Live', 'متاح للعملاء'),
    'pending' => $text('Under review', 'تحت المراجعة'),
    'rejected' => $text('Declined', 'مرفوض'),
    'blocked' => $text('Suspended', 'موقوف'),
    default => ucfirst($vendor->status ?? ''),
  };
@endphp

@section('title', $text('Seller Dashboard', 'لوحة البائع'))
@section('page-title', $text('Dashboard', 'لوحة البائع'))

@push('styles')
<style>
.seller-dashboard{width:min(100%,1120px);margin-inline:auto;display:grid;gap:16px}
.seller-dashboard h1{font-size:clamp(1.375rem,3vw,2rem);line-height:1.25;overflow-wrap:anywhere}
.seller-dashboard h2{font-size:1rem}
.seller-dash-heading{display:grid;gap:5px}
.seller-dash-heading p{color:var(--mid)}
.seller-dash-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.seller-dash-stats div,.seller-dash-panel{min-inline-size:0;background:var(--white);border:1px solid var(--light);border-radius:12px;padding:14px}
.seller-dash-stats dt{color:var(--mid);font-size:.75rem;line-height:1.35;overflow-wrap:anywhere}
.seller-dash-stats dd{margin-block-start:5px;font-size:clamp(1.125rem,3vw,1.75rem);font-weight:800;overflow-wrap:anywhere}
.seller-dash-panels{display:grid;gap:16px}
.seller-dash-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-block-start:12px}
.seller-dash-actions a{display:flex;align-items:center;justify-content:center;min-block-size:48px;white-space:normal;padding:8px;text-align:center;font-size:.8125rem}
.seller-dash-contact{display:grid;gap:8px;margin-block-start:12px;color:var(--mid);overflow-wrap:anywhere}
.seller-dash-contact div{display:grid;gap:2px}
.seller-dash-contact dt{font-size:.75rem}
.seller-dash-contact dd{color:var(--dark)}
.seller-dash-panel-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.seller-dash-all{display:inline-flex;align-items:center;min-block-size:44px;font-size:.8125rem;color:#993915;font-weight:700}
.seller-dash-products{list-style:none;margin-block-start:6px}
.seller-dash-products li{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px 12px;align-items:center;padding-block:10px;border-block-start:1px solid var(--light)}
.seller-dash-product-name{font-weight:700;overflow-wrap:anywhere}
.seller-dash-price{font-size:.8125rem;line-height:1.5}
.seller-dash-price s{color:var(--mid);font-size:.75rem}
.seller-dash-products .vs-btn{align-self:center;min-block-size:44px}
.seller-dash-empty{padding-block:12px;color:var(--mid)}
.seller-dashboard a:focus-visible{outline:2px solid var(--orange);outline-offset:2px}
@media(min-width:900px){
  .seller-dashboard{gap:20px}
  .seller-dash-stats{gap:16px}
  .seller-dash-stats div,.seller-dash-panel{padding:20px}
  .seller-dash-panels{grid-template-columns:repeat(2,minmax(0,1fr))}
  .seller-dash-products li{grid-template-columns:minmax(0,1fr) minmax(130px,.45fr) minmax(85px,.25fr) auto}
}
@media(max-width:899px){
  .seller-dash-product-name{grid-column:1/-1}
  .seller-dash-products .vs-btn{grid-column:2;grid-row:2/4}
}
</style>
@endpush

@section('content')
<main class="seller-dashboard">
  <header class="seller-dash-heading">
    <h1>{{ $vendor->shop_name }}</h1>
    <p>{{ $live ? $text('Your store is open to customers.', 'متجرك مفتوح وبيظهر للعملاء.') : $status }}</p>
  </header>

  <dl class="seller-dash-stats">
    <div><dt>{{ $text('Products', 'المنتجات') }}</dt><dd>{{ number_format($stats['products']) }}</dd></div>
    <div><dt>{{ $text('Orders', 'الطلبات') }}</dt><dd>{{ number_format($stats['orders']) }}</dd></div>
    <div><dt>{{ $text('Avg. rating', 'متوسط التقييم') }}</dt><dd>{{ number_format((float) $stats['rating'], 1) }}</dd></div>
  </dl>

  <div class="seller-dash-panels">
    <section class="seller-dash-panel" aria-labelledby="seller-actions">
      <h2 id="seller-actions">{{ $text('Quick actions', 'إجراءات سريعة') }}</h2>
      <div class="seller-dash-actions">
        <a class="vs-btn vs-btn-primary" href="{{ route('vendor.products.create') }}">{{ $text('Add a product', 'أضف منتج') }}</a>
        <a class="vs-btn vs-btn-ghost" href="{{ route('vendor.orders') }}">{{ $text('View orders', 'شوف الطلبات') }}</a>
        <a class="vs-btn vs-btn-ghost" href="{{ route('vendor.store', $vendor->id) }}" target="_blank" rel="noopener">{{ $text('View my store', 'افتح متجري') }}</a>
        <a class="vs-btn vs-btn-ghost" href="{{ route('shop') }}" target="_blank" rel="noopener">{{ $text('Browse marketplace', 'تصفّح رامو') }}</a>
      </div>
    </section>

    <section class="seller-dash-panel" aria-labelledby="seller-details">
      <h2 id="seller-details">{{ $text('Store details', 'بيانات المتجر') }}</h2>
      <dl class="seller-dash-contact">
        <div><dt>{{ $text('Email', 'البريد الإلكتروني') }}</dt><dd><bdi>{{ $vendor->email }}</bdi></dd></div>
        <div><dt>{{ $text('Phone', 'التليفون') }}</dt><dd><bdi>{{ $vendor->phone }}</bdi></dd></div>
        @if($vendor->shop_address)
          <div><dt>{{ $text('Address', 'العنوان') }}</dt><dd>{{ $vendor->shop_address }}</dd></div>
        @endif
      </dl>
    </section>
  </div>

  <section class="seller-dash-panel" aria-labelledby="seller-products">
    <header class="seller-dash-panel-header">
      <h2 id="seller-products">{{ $text('Recent products', 'أحدث المنتجات') }}</h2>
      <a class="seller-dash-all" href="{{ route('vendor.products') }}">{{ $text('All products', 'كل المنتجات') }}</a>
    </header>
    @if($recentProducts->isNotEmpty())
      <ul class="seller-dash-products">
        @foreach($recentProducts as $product)
          @php
            $productStatus = $product->status ?? 'active';
            $statusLabel = match ($productStatus) {
              'active' => $text('Active', 'نشط'),
              'pending' => $text('Pending', 'قيد المراجعة'),
              'blocked' => $text('Blocked', 'موقوف'),
              default => ucfirst($productStatus),
            };
            $onSale = $product->on_sale && $product->min_sale > 0;
            $price = $onSale ? $product->min_sale : ($product->min_regular ?? $product->min_sale ?? 0);
          @endphp
          <li>
            <span class="seller-dash-product-name">{{ $product->name }}</span>
            <span class="seller-dash-price">
              <bdi>{{ number_format($price, 2) }} {{ $text('EGP', 'ج.م.') }}</bdi>
              @if($onSale)<s><bdi>{{ number_format($product->min_regular, 2) }}</bdi></s>@endif
            </span>
            <span><span class="badge-{{ $productStatus === 'active' ? 'approved' : ($productStatus === 'pending' ? 'pending' : 'blocked') }}">{{ $statusLabel }}</span></span>
            <a class="vs-btn vs-btn-ghost vs-btn-sm" href="{{ route('product', $product->id) }}" target="_blank" rel="noopener"
               aria-label="{{ $text('View', 'اعرض') }} {{ $product->name }}">{{ $text('View', 'عرض') }}</a>
          </li>
        @endforeach
      </ul>
    @else
      <div class="seller-dash-empty"><p>{{ $text('No products yet', 'لسه مفيش منتجات') }}</p></div>
    @endif
  </section>
</main>
@endsection
