@extends('web.account.layout')
@php
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'تقييماتي' : 'My Reviews';
@endphp

@section('account-content')
<div class="acc-section-title">{{ $isAr ? 'تقييماتي' : 'My Reviews' }}</div>

@if($reviews->count())
  <div class="acc-reviews-list">
    @foreach($reviews as $review)
    <div class="acc-review-card">
      <div class="acc-review-top">
        <div>
          <a href="{{ route('product', $review->product_id) }}" class="acc-review-product">
            {{ $review->product_name ?? ($isAr ? 'منتج' : 'Product') }}
          </a>
          <div class="acc-review-stars">
            @for($i = 1; $i <= 5; $i++)
              <span style="color:{{ $i <= $review->rating ? '#f59e0b' : 'var(--c-light)' }};font-size:16px">★</span>
            @endfor
            <span style="font-size:12px;color:var(--c-mid);margin-left:4px">{{ $review->rating }}/5</span>
          </div>
        </div>
        <div style="text-align:{{ $isAr ? 'left' : 'right' }};flex-shrink:0">
          @if($review->approved)
            <span class="acc-review-badge approved">{{ $isAr ? 'اتنشر' : 'Published' }}</span>
          @else
            <span class="acc-review-badge pending">{{ $isAr ? 'قيد المراجعة' : 'Pending' }}</span>
          @endif
          @if($review->is_verified_purchase)
            <div style="font-size:11px;color:#22a35c;margin-top:4px;font-weight:600">✓ {{ $isAr ? 'شراء مؤكد' : 'Verified Purchase' }}</div>
          @endif
        </div>
      </div>

      @if($review->title)
        <div class="acc-review-title">{{ $review->title }}</div>
      @endif
      <p class="acc-review-body">{{ $review->body }}</p>

      <div class="acc-review-footer">
        <span style="font-size:12px;color:var(--c-mid)">
          {{ $isAr ? \Carbon\Carbon::parse($review->created_at)->locale('ar')->translatedFormat('j F Y') : \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}
        </span>
        @if($review->helpful_count > 0)
          <span style="font-size:12px;color:var(--c-mid)">
            👍 {{ $review->helpful_count }} {{ $isAr ? 'شافه مفيد' : 'found helpful' }}
          </span>
        @endif
      </div>
    </div>
    @endforeach
  </div>
@else
  <div class="empty">
    <div class="empty-icon">⭐</div>
    <h3>{{ $isAr ? 'لسه مفيش تقييمات' : 'No reviews yet' }}</h3>
    <p>{{ $isAr ? 'بعد ما تشتري منتج، شارك تجربتك وساعد باقي الناس.' : 'After purchasing a product, share your experience to help other shoppers.' }}</p>
    <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:20px">{{ $isAr ? 'تصفّح المنتجات' : 'Browse Products' }}</a>
  </div>
@endif
@endsection
