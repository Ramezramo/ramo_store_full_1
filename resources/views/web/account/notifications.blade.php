@extends('web.account.layout')

@php
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'الإشعارات' : 'Notifications';
  $orderStatusesAr = [
    'order_placed' => 'تم تسجيل الطلب', 'pending' => 'في الانتظار', 'confirmed' => 'اتأكد',
    'processing' => 'جاري التجهيز', 'packed' => 'اتجهز للشحن', 'on-hold' => 'متوقف مؤقتًا',
    'shipped' => 'اتشحن', 'partially_shipped' => 'جزء منه اتشحن', 'out_for_delivery' => 'في الطريق للتوصيل',
    'delivered' => 'اتسلّم', 'partially_delivered' => 'جزء منه اتسلّم', 'completed' => 'مكتمل',
    'cancelled' => 'اتلغى', 'canceled' => 'اتلغى', 'partially_cancelled' => 'جزء منه اتلغى',
    'refund-req' => 'تم طلب الاسترداد', 'refunded' => 'تم استرداد المبلغ', 'returned' => 'اترجع', 'failed' => 'فشل',
  ];
  $paymentStatusesAr = [
    'pending_payment' => 'في انتظار الدفع',
    'pending_verification' => 'في انتظار المراجعة', 'confirmed' => 'اتأكد',
    'rejected' => 'اترَفض', 'expired' => 'انتهت مهلة الدفع', 'refunded' => 'تم استرداد المبلغ', 'failed' => 'فشل',
  ];
@endphp

@push('styles')
<style>
  .order-inbox{min-inline-size:0}
  .order-inbox-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px 12px;margin-block-end:16px}
  .order-inbox-heading h1{margin:0;color:#171717;font-size:clamp(1.25rem,4vw,1.625rem);line-height:1.3}
  .order-inbox-heading p{margin:3px 0 0;color:#666;font-size:.8125rem}
  .order-inbox-mark{min-block-size:44px;padding:6px 12px;border:1px solid #deded9;border-radius:9px;background:#fff;color:#242424;font:inherit;font-size:.8125rem;font-weight:700;cursor:pointer}
  .order-inbox-list{display:grid;gap:8px;list-style:none;margin:0;padding:0}
  .order-inbox-row{display:flex;align-items:center;gap:10px;inline-size:100%;padding:12px;border:1px solid #e9e9e6;border-radius:12px;background:#fff;color:#171717;font:inherit;text-align:start;cursor:pointer}
  .order-inbox-row:hover{border-color:#bcbcb8}
  .order-inbox-row:focus-visible,.order-inbox-mark:focus-visible{outline:2px solid var(--c-orange);outline-offset:2px}
  .order-inbox-row.is-unread{border-color:#f1c8b6;background:#fff8f4}
  .order-inbox-icon{display:grid;place-items:center;flex:0 0 38px;block-size:38px;border-radius:10px;background:#f3f3f0;color:#525252}
  .order-inbox-row.is-unread .order-inbox-icon{background:#ffeade;color:#9d3615}
  .order-inbox-icon svg{width:19px;height:19px}
  .order-inbox-copy{display:grid;gap:3px;flex:1;min-inline-size:0;overflow-wrap:anywhere;line-height:1.4}
  .order-inbox-copy strong{font-size:.875rem}
  .order-inbox-copy small{color:#646464;font-size:.75rem}
  .order-inbox-new{padding:3px 6px;border-radius:999px;background:#ffeade;color:#8e3215;font-size:.6875rem;font-weight:750}
  .order-inbox-empty{padding:24px 12px;border:1px solid #e9e9e6;border-radius:12px;text-align:center}
  .order-inbox-empty p{margin:6px 0;color:#666;font-size:.875rem}
  .order-inbox-pages{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-block-start:16px;font-size:.8125rem}
  .order-inbox-pages a,.order-inbox-empty a{color:#963615;text-decoration:underline}
</style>
@endpush

@section('account-content')
<section class="order-inbox" aria-labelledby="order-inbox-title">
  <header class="order-inbox-heading">
    <div>
      <h1 id="order-inbox-title">{{ $pageTitle }}</h1>
      <p>{{ $isAr ? 'تابع طلباتك وتحديثات حساب البائع.' : 'Updates about your orders and seller application.' }}</p>
    </div>
    @if($unread)
      <form action="{{ route('account.notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="order-inbox-mark">{{ $isAr ? 'علّم الكل كمقروء' : 'Mark all as read' }}</button>
      </form>
    @endif
  </header>

  @if($vendorNotifications->isNotEmpty())
    <ul class="order-inbox-list" style="margin-bottom:8px">
      @foreach($vendorNotifications as $notice)
        <li>
          <form action="{{ route('account.notifications.vendor.open', $notice->id) }}" method="POST">
            @csrf
            <button type="submit" class="order-inbox-row{{ $notice->read_at ? '' : ' is-unread' }}">
              <span class="order-inbox-icon">@include('web.partials.notification-bell')</span>
              <span class="order-inbox-copy">
                <strong>{{ match ($notice->status) {
                  'approved' => $isAr ? 'تمت الموافقة على طلب البائع' : 'Your vendor application was approved',
                  'on_hold' => $isAr ? 'طلب البائع مؤجل للمراجعة لاحقًا' : 'Your vendor application is on hold',
                  default => $isAr ? 'تم رفض طلب البائع' : 'Your vendor application was declined',
                } }}</strong>
                <small>{{ \Illuminate\Support\Carbon::parse($notice->created_at)->locale($isAr ? 'ar' : 'en')->diffForHumans() }}</small>
              </span>
              @if(!$notice->read_at)<span class="order-inbox-new">{{ $isAr ? 'جديد' : 'New' }}</span>@endif
            </button>
          </form>
        </li>
      @endforeach
    </ul>
  @endif
  @if($notifications->isNotEmpty())
    <ul class="order-inbox-list">
      @foreach($notifications as $notification)
        @php
          $status = (string) $notification->status;
          $statusLabels = $notification->event === 'payment_status' ? $paymentStatusesAr : $orderStatusesAr;
          $label = $isAr ? ($statusLabels[$status] ?? str_replace(['_', '-'], ' ', $status)) : \Illuminate\Support\Str::headline($status);
          $title = match ($notification->event) {
              'placed' => $isAr ? 'تم تسجيل طلبك' : 'Your order has been placed',
              'payment_status' => $isAr ? 'الدفع: '.$label : 'Payment: '.$label,
              default => $isAr ? 'حالة الطلب: '.$label : 'Order status: '.$label,
          };
          $created = \Illuminate\Support\Carbon::parse($notification->created_at)->locale($isAr ? 'ar' : 'en');
        @endphp
        <li>
          <form action="{{ route('account.notifications.open', $notification->id) }}" method="POST">
            @csrf
            <button type="submit" class="order-inbox-row{{ $notification->read_at ? '' : ' is-unread' }}">
              <span class="order-inbox-icon">@include('web.partials.notification-bell')</span>
              <span class="order-inbox-copy">
                <strong>{{ $title }}</strong>
                <small>{{ $isAr ? 'طلب' : 'Order' }} <bdi>#{{ $notification->order_id }}</bdi> · <time datetime="{{ $created->toIso8601String() }}">{{ $created->diffForHumans() }}</time></small>
              </span>
              @if(!$notification->read_at)<span class="order-inbox-new">{{ $isAr ? 'جديد' : 'New' }}</span>@endif
            </button>
          </form>
        </li>
      @endforeach
    </ul>
  @elseif($vendorNotifications->isEmpty())
    <div class="order-inbox-empty">
      <strong>{{ $isAr ? 'لسه مفيش إشعارات' : 'No notifications yet' }}</strong>
      <p>{{ $isAr ? 'هتلاقي تحديثات طلباتك هنا.' : 'Updates will appear here after you place an order.' }}</p>
      <a href="{{ route('account.orders') }}">{{ $isAr ? 'طلباتي' : 'My orders' }}</a>
    </div>
  @endif

  @if($notifications->hasPages())
    <nav class="order-inbox-pages" aria-label="{{ $isAr ? 'صفحات الإشعارات' : 'Notification pages' }}">
      @if($notifications->previousPageUrl())<a href="{{ $notifications->previousPageUrl() }}">{{ $isAr ? 'الأحدث' : 'Newer' }}</a>@endif
      <span>{{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}</span>
      @if($notifications->nextPageUrl())<a href="{{ $notifications->nextPageUrl() }}">{{ $isAr ? 'الأقدم' : 'Older' }}</a>@endif
    </nav>
  @endif
</section>
@endsection
