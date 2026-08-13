@extends('web.account.layout')
@php
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'طلبات الاسترجاع' : 'My Refund Requests';
@endphp

@section('account-content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div class="acc-section-title" style="margin-bottom:0">{{ $isAr ? 'طلبات الاسترجاع والمرتجعات' : 'Refund & Return Requests' }}</div>
  <a href="{{ route('account.refunds.create') }}" class="btn btn-dark" style="font-size:13px;padding:9px 18px">{{ $isAr ? '+ طلب جديد' : '+ New Request' }}</a>
</div>

@if($refunds->count())
<div class="orders-table-wrap">
  <table class="orders-table">
    <thead>
      <tr>
        <th>{{ $isAr ? 'رقم الطلب' : 'Req #' }}</th>
        <th>{{ $isAr ? 'رقم الأوردر' : 'Order #' }}</th>
        <th>{{ $isAr ? 'النوع' : 'Type' }}</th>
        <th>{{ $isAr ? 'السبب' : 'Reason' }}</th>
        <th>{{ $isAr ? 'الحالة' : 'Status' }}</th>
        <th>{{ $isAr ? 'التاريخ' : 'Date' }}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($refunds as $r)
      @php
        $sc = match($r->status) {
          'approved'  => 'status-completed',
          'rejected'  => 'status-cancelled',
          'completed' => 'status-completed',
          'cancelled' => 'status-cancelled',
          default     => 'status-processing',
        };
        $reasonLabel = match($r->reason) {
          'damaged'          => $isAr ? 'المنتج تالف' : 'Item Damaged',
          'wrong_item'       => $isAr ? 'منتج غلط' : 'Wrong Item',
          'changed_mind'     => $isAr ? 'غيّرت رأيي' : 'Changed Mind',
          'not_as_described' => $isAr ? 'مش زي الوصف' : 'Not as Described',
          default            => $isAr ? 'سبب تاني' : 'Other',
        };
        $typeLabel = $isAr ? ($r->type === 'return' ? 'مرتجع' : 'استرجاع فلوس') : $r->type;
        $statusLabel = $isAr ? match($r->status) {
          'pending' => 'في الانتظار', 'approved' => 'اتوافق عليه', 'rejected' => 'اترفض',
          'completed' => 'اتم', 'cancelled' => 'اتلغى', default => $r->status,
        } : ucfirst($r->status);
      @endphp
      <tr>
        <td><strong>#{{ $r->id }}</strong></td>
        <td><a href="{{ route('account.order', $r->order_id) }}" style="color:inherit">#{{ $r->order_id }}</a></td>
        <td><span style="font-weight:600;text-transform:capitalize">{{ $typeLabel }}</span></td>
        <td style="color:#666;font-size:13px">{{ $reasonLabel }}</td>
        <td><span class="status-badge {{ $sc }}">{{ $statusLabel }}</span></td>
        <td style="font-size:12px;color:#888">{{ $isAr ? \Carbon\Carbon::parse($r->created_at)->locale('ar')->translatedFormat('j F Y') : \Carbon\Carbon::parse($r->created_at)->format('M d, Y') }}</td>
        <td>
          <a href="{{ route('account.refunds.show', $r->id) }}" class="btn btn-outline" style="font-size:12px;padding:5px 12px">{{ $isAr ? 'شوف' : 'View' }}</a>
          @if($r->status === 'pending')
            <form method="POST" action="{{ route('account.refunds.cancel', $r->id) }}" style="display:inline" onsubmit="return confirm('{{ $isAr ? 'تلغي الطلب ده؟' : 'Cancel this request?' }}')">
              @csrf @method('PATCH')
              <button class="btn btn-outline" style="font-size:12px;padding:5px 12px;margin-left:4px;color:#dc2626;border-color:#dc2626">{{ $isAr ? 'إلغاء' : 'Cancel' }}</button>
            </form>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if($refunds->hasPages())
  <div class="pagination-wrap" style="margin:20px 18px">
    @if($refunds->onFirstPage())<span>‹</span>@else<a href="{{ $refunds->previousPageUrl() }}">‹</a>@endif
    @foreach($refunds->getUrlRange(max(1,$refunds->currentPage()-2), min($refunds->lastPage(),$refunds->currentPage()+2)) as $page => $url)
      @if($page == $refunds->currentPage())<span class="active-page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
    @endforeach
    @if($refunds->hasMorePages())<a href="{{ $refunds->nextPageUrl() }}">›</a>@else<span>›</span>@endif
  </div>
  @endif
</div>
@else
  <div class="empty">
    <div class="empty-icon">🔄</div>
    <h3>{{ $isAr ? 'لسه مفيش طلبات استرجاع' : 'No requests yet' }}</h3>
    <p>{{ $isAr ? 'قدّم طلب استرجاع فلوس أو مرتجع لأي طلب مؤهل.' : 'Submit a refund or return request for any eligible order.' }}</p>
    <a href="{{ route('account.refunds.create') }}" class="btn btn-dark" style="margin-top:20px">{{ $isAr ? 'طلب استرجاع / مرتجع' : 'Request Refund / Return' }}</a>
  </div>
@endif
@endsection
