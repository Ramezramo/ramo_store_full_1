@extends('web.account.layout')
@php
  $isAr = session('locale') === 'ar';
  $pageTitle = ($isAr ? 'طلب رقم ' : 'Request #') . '#'.$refund->id;
@endphp

@section('account-content')
<div style="margin-bottom:20px">
  <a href="{{ route('account.refunds') }}" style="font-size:13px;color:#888;text-decoration:none">{{ $isAr ? 'ارجع للطلبات →' : '← Back to Requests' }}</a>
</div>
<div class="acc-section-title" style="margin-bottom:20px">{{ $isAr ? 'طلب رقم' : 'Request #' }} #{{ $refund->id }}</div>

@php
  $sc = match($refund->status) {
    'approved'  => 'status-completed',
    'rejected'  => 'status-cancelled',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled',
    default     => 'status-processing',
  };
  $reasonLabel = match($refund->reason) {
    'damaged'          => $isAr ? 'المنتج تالف' : 'Item Damaged',
    'wrong_item'       => $isAr ? 'منتج غلط' : 'Wrong Item',
    'changed_mind'     => $isAr ? 'غيّرت رأيي' : 'Changed Mind',
    'not_as_described' => $isAr ? 'مش زي الوصف' : 'Not as Described',
    default            => $isAr ? 'سبب تاني' : 'Other',
  };
  $typeLabel = $isAr ? ($refund->type === 'return' ? 'مرتجع' : 'استرجاع فلوس') : $refund->type;
  $statusLabel = $isAr ? match($refund->status) {
    'pending' => 'في الانتظار', 'approved' => 'اتوافق عليه', 'rejected' => 'اترفض',
    'completed' => 'اتم', 'cancelled' => 'اتلغى', default => $refund->status,
  } : ucfirst($refund->status);
@endphp

<div class="order-detail-card">
  <div class="od-row"><span class="od-label">{{ $isAr ? 'رقم الطلب' : 'Request #' }}</span><strong>#{{ $refund->id }}</strong></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'الأوردر' : 'Order' }}</span><a href="{{ route('account.order', $refund->order_id) }}" style="color:inherit;font-weight:600">#{{ $refund->order_id }}</a></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'إجمالي الأوردر' : 'Order Total' }}</span><span>{{ number_format($refund->order_total, 2) }} EGP</span></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'النوع' : 'Type' }}</span><span style="font-weight:600;text-transform:capitalize">{{ $typeLabel }}</span></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'السبب' : 'Reason' }}</span><span>{{ $reasonLabel }}</span></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'الحالة' : 'Status' }}</span><span class="status-badge {{ $sc }}">{{ $statusLabel }}</span></div>
  <div class="od-row"><span class="od-label">{{ $isAr ? 'اتقدم يوم' : 'Submitted' }}</span><span>{{ $isAr ? \Carbon\Carbon::parse($refund->created_at)->locale('ar')->translatedFormat('j F Y - h:i A') : \Carbon\Carbon::parse($refund->created_at)->format('M d, Y h:i A') }}</span></div>
</div>

@if($refund->description)
<div class="order-detail-card" style="margin-top:16px">
  <h3 style="font-size:14px;font-weight:700;margin-bottom:10px">{{ $isAr ? 'تفاصيلك' : 'Your Description' }}</h3>
  <p style="font-size:14px;line-height:1.7;color:#444">{{ $refund->description }}</p>
</div>
@endif

@if($refund->admin_note)
<div class="order-detail-card" style="margin-top:16px;border-left:3px solid #e85d26">
  <h3 style="font-size:14px;font-weight:700;margin-bottom:10px">{{ $isAr ? 'رد خدمة العملاء' : 'Response from Support' }}</h3>
  <p style="font-size:14px;line-height:1.7;color:#444">{{ $refund->admin_note }}</p>
</div>
@endif

@if($refund->status === 'pending')
<div style="margin-top:20px">
  <form method="POST" action="{{ route('account.refunds.cancel', $refund->id) }}" onsubmit="return confirm('{{ $isAr ? 'تلغي الطلب ده؟' : 'Cancel this request?' }}')">
    @csrf @method('PATCH')
    <button class="btn btn-outline" style="color:#dc2626;border-color:#dc2626">{{ $isAr ? 'إلغاء الطلب' : 'Cancel Request' }}</button>
  </form>
</div>
@endif
@endsection
