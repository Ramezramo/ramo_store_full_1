@extends('web.account.layout')
@php
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'طلب استرجاع جديد' : 'New Refund / Return Request';
@endphp

@section('account-content')
<div style="margin-bottom:20px">
  <a href="{{ route('account.refunds') }}" style="font-size:13px;color:#888;text-decoration:none">{{ $isAr ? 'ارجع للطلبات →' : '← Back to Requests' }}</a>
</div>
<div class="acc-section-title" style="margin-bottom:20px">{{ $isAr ? 'طلب استرجاع أو مرتجع جديد' : 'New Refund / Return Request' }}</div>

@if($errors->any())
  <div class="acc-alert acc-alert-error" style="margin-bottom:16px">{{ $errors->first() }}</div>
@endif

<div class="order-detail-card">
  <form method="POST" action="{{ route('account.refunds.store') }}">
    @csrf

    <div style="margin-bottom:18px">
      <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">{{ $isAr ? 'الطلب' : 'Order' }} *</label>
      <select name="order_id" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
        <option value="">{{ $isAr ? 'اختار طلب…' : 'Select an order…' }}</option>
        @foreach($orders as $o)
          @php $disabled = in_array($o->id, $existingIds); @endphp
          <option value="{{ $o->id }}"
            {{ (old('order_id', $orderId) == $o->id) ? 'selected' : '' }}
            {{ $disabled ? 'disabled' : '' }}>
            #{{ $o->id }} — {{ $isAr ? \Carbon\Carbon::parse($o->date_created)->locale('ar')->translatedFormat('j F Y') : \Carbon\Carbon::parse($o->date_created)->format('M d, Y') }} — {{ number_format($o->final_total, 2) }} EGP ({{ $isAr ? match($o->status) { 'pending' => 'في الانتظار', 'processing' => 'جاري التجهيز', 'shipped' => 'اتشحن', 'delivered' => 'اتسلّم', 'cancelled' => 'اتلغى', default => $o->status } : ucfirst($o->status) }})
            {{ $disabled ? ($isAr ? '[اتعمل طلب قبل كده]' : '[Already requested]') : '' }}
          </option>
        @endforeach
      </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
      <div>
        <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">{{ $isAr ? 'نوع الطلب' : 'Request Type' }} *</label>
        <select name="type" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
          <option value="refund" {{ old('type')=='refund'?'selected':'' }}>{{ $isAr ? 'استرجاع فلوس — خد فلوسك تاني' : 'Refund — Get my money back' }}</option>
          <option value="return" {{ old('type')=='return'?'selected':'' }}>{{ $isAr ? 'مرتجع — رجّع المنتج' : 'Return — Send item back' }}</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">{{ $isAr ? 'السبب' : 'Reason' }} *</label>
        <select name="reason" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
          <option value="">{{ $isAr ? 'اختار السبب…' : 'Select a reason…' }}</option>
          <option value="damaged"          {{ old('reason')=='damaged'?'selected':'' }}>{{ $isAr ? 'المنتج وصل تالف' : 'Item arrived damaged' }}</option>
          <option value="wrong_item"       {{ old('reason')=='wrong_item'?'selected':'' }}>{{ $isAr ? 'وصل منتج غلط' : 'Wrong item received' }}</option>
          <option value="not_as_described" {{ old('reason')=='not_as_described'?'selected':'' }}>{{ $isAr ? 'مش زي الوصف' : 'Not as described' }}</option>
          <option value="changed_mind"     {{ old('reason')=='changed_mind'?'selected':'' }}>{{ $isAr ? 'غيّرت رأيي' : 'Changed my mind' }}</option>
          <option value="other"            {{ old('reason')=='other'?'selected':'' }}>{{ $isAr ? 'سبب تاني' : 'Other' }}</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom:22px">
      <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">{{ $isAr ? 'تفاصيل إضافية' : 'Additional Details' }}</label>
      <textarea name="description" rows="4" placeholder="{{ $isAr ? 'احكيلنا المشكلة بالتفصيل…' : 'Describe your issue in detail…' }}" style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none;resize:vertical">{{ old('description') }}</textarea>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn btn-dark">{{ $isAr ? 'ابعت الطلب' : 'Submit Request' }}</button>
      <a href="{{ route('account.refunds') }}" class="btn btn-outline">{{ $isAr ? 'إلغاء' : 'Cancel' }}</a>
    </div>
  </form>
</div>
@endsection
