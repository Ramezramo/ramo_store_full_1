@extends('web.account.layout')
@php
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'طلب رقم #'.$order->id : 'Order #'.$order->id;
  $statusLabel = fn ($status) => $isAr ? match(strtolower($status ?? 'pending')) {
    'pending', 'pending_payment' => 'في الانتظار', 'processing' => 'جاري التجهيز', 'shipped' => 'اتشحن', 'completed', 'delivered' => 'اتسلّم',
    'cancelled' => 'اتلغى', 'refunded' => 'اترجع', 'failed', 'payment_failed' => 'فشل الدفع', 'rejected' => 'اترفض', default => $status,
  } : app(\App\Services\OrderStatusService::class)->label($status);
  $cancelled = in_array($order->status, ['cancelled', 'canceled', 'refunded', 'failed'], true);
  $awaitingPayment = !$cancelled && in_array($order->payment_status, \App\Services\OrderPaymentExpiryService::AWAITING_PAYMENT_STATUSES, true);
  $receiptUnderReview = $order->payment_status === 'pending_verification';
  $uploadWindowClosed = \App\Services\OrderPaymentExpiryService::deadlinePassed($order);
  $hasSubOrders = isset($subOrders) && $subOrders->count() > 0;
  $messageCount = 0;
  if ($hasSubOrders) {
    foreach ($subOrders as $s) {
      $messageCount += isset($s->messages) ? count($s->messages) : 0;
    }
  }

  // ── Savings calculation ──
  $saleSavings = 0;
  foreach ($lineItems as $li) {
    $regPrice  = floatval($li['variation']['regular_price'] ?? $li['regular_price'] ?? 0);
    $paidPrice = floatval($li['variation']['price_used']    ?? $li['price']['final'] ?? (is_numeric($li['price'] ?? null) ? $li['price'] : 0));
    $lineQty   = intval($li['quantity'] ?? 1);
    if ($regPrice > $paidPrice && $paidPrice > 0) {
      $saleSavings += ($regPrice - $paidPrice) * $lineQty;
    }
  }
  $couponSavings = floatval($order->discount_total ?? 0);
  $totalSavings  = $saleSavings + $couponSavings;
  $couponCode    = $order->coupon_code ?? null;
  $receiptUploadLimit = \App\Http\Controllers\Web\PaymentReceiptController::MAX_UPLOADS_PER_ORDER;
  $receiptUploadCount = isset($paymentReceipts) ? $paymentReceipts->count() : 0;
  $receiptUploadsRemaining = max(0, $receiptUploadLimit - $receiptUploadCount);
  $canUploadReceipt = $receiptUploadCount < $receiptUploadLimit;
@endphp

@section('account-content')
<style>
  .odx-page{--odx-accent:#e85d26;--odx-ink:#171717;--odx-muted:#737373;--odx-line:#ececec;display:grid;gap:16px;color:var(--odx-ink)}
  .odx-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:2px}
  .odx-heading h1{font-size:clamp(20px,3vw,26px);line-height:1.2;margin:0;font-weight:800;letter-spacing:-.02em}
  .odx-heading p{font-size:13px;color:var(--odx-muted);margin:5px 0 0}
  .odx-overview,.odx-card{background:#fff;border:1px solid var(--odx-line);border-radius:18px;box-shadow:0 5px 20px rgba(20,20,20,.035)}
  .odx-overview{padding:20px}
  .odx-overview-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-bottom:16px;border-bottom:1px solid var(--odx-line)}
  .odx-eyebrow{font-size:11px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:var(--odx-muted);margin:0 0 5px}
  .odx-date{font-size:13px;color:var(--odx-muted);margin:0}
  .odx-facts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding-top:16px}
  .odx-fact{min-width:0;padding:12px;border-radius:12px;background:#fafafa}
  .odx-fact-label{display:block;font-size:11px;color:var(--odx-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px}
  .odx-fact-value{display:block;font-size:14px;font-weight:750;overflow-wrap:anywhere}
  .odx-card{overflow:hidden}
  .odx-card-heading{padding:17px 18px;border-bottom:1px solid var(--odx-line);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .odx-card-heading h2{font-size:16px;margin:0;font-weight:800}
  .odx-store{display:flex;align-items:center;gap:11px;min-width:0}
  .odx-store-logo{width:44px;height:44px;flex:0 0 44px;border:1px solid var(--odx-line);border-radius:12px;background:#fafafa;object-fit:cover}
  .odx-store-initial{display:grid;place-items:center;color:var(--odx-accent);font-weight:800;font-size:17px}
  .odx-store-copy{min-width:0}
  .odx-store-name{font-weight:800;font-size:14px;line-height:1.3;overflow-wrap:anywhere}
  .odx-store-sub{font-size:12px;color:var(--odx-muted);margin-top:3px;line-height:1.4}
  .odx-store-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .odx-store-link{font-size:12px;font-weight:750;color:var(--odx-accent);text-decoration:none;white-space:nowrap}
  .odx-products{padding:4px 18px 10px}
  .odx-product{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:13px;align-items:start;padding:14px 0;border-bottom:1px solid #f0f0f0}
  .odx-product:last-child{border-bottom:0}
  .odx-product-thumb{width:72px;height:72px;border-radius:12px;object-fit:cover;background:#f5f5f5;border:1px solid var(--odx-line);display:grid;place-items:center;color:#aaa;font-size:22px}
  .odx-product-thumb[hidden]{display:none!important}
  .odx-product-main{min-width:0}
  .odx-product-name{display:block;color:var(--odx-ink);font-weight:750;font-size:14px;line-height:1.4;text-decoration:none;overflow-wrap:anywhere}
  .odx-product-name:hover{color:var(--odx-accent)}
  .odx-product-description{font-size:12px;color:var(--odx-muted);line-height:1.5;margin:4px 0 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .odx-product-meta{display:flex;flex-wrap:wrap;gap:5px;margin-top:7px}
  .odx-product-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#f6f6f6;color:#555;font-size:11px;line-height:1.2;max-width:100%;overflow-wrap:anywhere}
  .odx-product-total{text-align:end;white-space:nowrap;font-size:13px;font-weight:800;padding-top:2px}
  .odx-product-total small{display:block;color:var(--odx-muted);font-size:11px;font-weight:500;margin-top:4px}
  .odx-totals{padding:13px 18px 17px;background:#fafafa;border-top:1px solid var(--odx-line)}
  .odx-total-row{display:flex;justify-content:space-between;gap:12px;align-items:center;font-size:13px;padding:4px 0;color:var(--odx-muted)}
  .odx-total-row strong{color:var(--odx-ink);white-space:nowrap}
  .odx-total-row.is-final{font-size:15px;font-weight:800;color:var(--odx-ink);padding-top:10px;margin-top:5px;border-top:1px solid #e5e5e5}
  .odx-total-row.is-final strong{color:var(--odx-accent)}
  .odx-tracking{padding:12px 18px;background:#fffaf6;border-top:1px solid #f4e5dc;font-size:12px;color:#6b625e;line-height:1.5}
  .odx-tracking strong{color:var(--odx-ink);overflow-wrap:anywhere}
  .odx-address{font-size:14px;line-height:1.75;color:#454545;overflow-wrap:anywhere}
  .odx-address p{margin:0}
  .odx-address iframe{display:block}
  @media(max-width:600px){
    .odx-page{gap:12px}
    .odx-overview{padding:16px;border-radius:15px}
    .odx-facts{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .odx-fact{padding:10px}
    .odx-card-heading{padding:14px}
    .odx-products{padding-inline:14px}
    .odx-product{grid-template-columns:60px minmax(0,1fr);gap:10px}
    .odx-product-thumb{width:60px;height:60px;border-radius:10px}
    .odx-product-total{grid-column:2;text-align:start;padding-top:0;margin-top:-4px}
    .odx-product-total small{display:inline;margin-inline-start:5px}
    .odx-totals{padding-inline:14px}
    .odx-tracking{padding-inline:14px}
  }
</style>

<div class="odx-page">
<div class="odx-heading">
  <div>
    <h1>{{ $isAr ? 'تفاصيل الطلب' : 'Order details' }}</h1>
    <p>{{ $isAr ? 'رقم الطلب' : 'Order' }} #{{ $order->id }} · {{ $isAr ? \Carbon\Carbon::parse($order->date_created)->locale('ar')->translatedFormat('j F Y، g:i A') : \Carbon\Carbon::parse($order->date_created)->format('M d, Y · h:i A') }}</p>
  </div>
  <a href="{{ route('account.orders') }}" class="btn btn-outline" style="font-size:12px;padding:9px 13px">{{ $isAr ? 'كل الطلبات' : 'All orders' }}</a>
</div>

@if($cancelled)
  <div class="acc-alert acc-alert-error" style="margin-bottom:24px">{{ $isAr ? 'الطلب ده حالته' : 'This order has been' }} <strong>{{ $statusLabel($order->status) }}</strong>{{ $isAr ? '.' : '.' }}</div>
@endif

@if(session('success'))
  <div class="acc-alert acc-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="acc-alert acc-alert-error" style="margin-bottom:16px">{{ session('error') }}</div>
@endif

{{-- ── ORDER META ────────────────────────────────────────────────── --}}
<section class="odx-overview" aria-label="{{ $isAr ? 'ملخص الطلب' : 'Order summary' }}">
  <div class="odx-overview-top">
    <div>
      <p class="odx-eyebrow">{{ $isAr ? 'ملخص الطلب' : 'Order summary' }}</p>
      <p class="odx-date">{{ $isAr ? 'طلبك اتسجل بنجاح' : 'Your order is being tracked here' }}</p>
    </div>
    <span class="status-badge status-{{ $order->status }}">{{ $statusLabel($order->status) }}</span>
  </div>
  <div class="odx-facts">
    <div class="odx-fact"><span class="odx-fact-label">{{ $isAr ? 'رقم الطلب' : 'Order number' }}</span><span class="odx-fact-value">#{{ $order->id }}</span></div>
    <div class="odx-fact"><span class="odx-fact-label">{{ $isAr ? 'الدفع' : 'Payment' }}</span><span class="odx-fact-value">{{ $order->payment_method_title ?: ($isAr ? 'غير محدد' : 'Not specified') }}</span></div>
    <div class="odx-fact"><span class="odx-fact-label">{{ $isAr ? 'إجمالي الطلب' : 'Order total' }}</span><span class="odx-fact-value" style="color:var(--odx-accent)">{{ number_format($order->final_total, 2) }} EGP</span></div>
  </div>
  @if($order->customer_note)
    <div style="margin-top:12px;padding:11px 13px;border-radius:11px;background:#fffaf6;color:#5f514b;font-size:13px;line-height:1.6"><strong>{{ $isAr ? 'ملاحظتك:' : 'Your note:' }}</strong> {{ $order->customer_note }}</div>
  @endif
</section>

@if(\App\Helpers\PaymentConfig::isManualMethod($order->payment_method ?? null))
@php
  $accountPaymentMethod = \App\Helpers\PaymentConfig::detailsFor($order->payment_method);
  $accountHasUploadedReceipt = filled($order->payment_receipt_path);
@endphp
<div class="order-detail-card" id="order-payment" style="margin-top:16px;scroll-margin-block-start:90px;border:1.5px solid #fed7aa;background:#fffaf5">
  <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
    <div>
      <div style="font-size:15px;font-weight:800">{{ $awaitingPayment ? ($isAr ? 'كمّل الدفع' : 'Complete payment') : ($isAr ? 'الدفع' : 'Payment') }}</div>
      <div style="font-size:13px;color:#6b7280;margin-top:4px">
        {{ $isAr ? 'الحالة:' : 'Status:' }}
        <strong style="color:{{ $order->payment_status === 'confirmed' ? '#15803d' : ($order->payment_status === 'rejected' ? '#b91c1c' : '#b45309') }}">
          {{ $isAr ? match(strtolower($order->payment_status ?? 'pending_payment')) { 'confirmed' => 'تم التأكيد', 'pending_payment' => 'في انتظار الدفع', 'pending_verification' => 'الإيصال في انتظار المراجعة', 'rejected' => 'اترفض', 'expired' => 'انتهت المهلة', 'failed' => 'فشل', default => 'في الانتظار' } : ucwords(str_replace('_', ' ', $order->payment_status ?? 'pending_payment')) }}
        </strong>
      </div>
    </div>
    @if($order->payment_receipt_path)
      <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_receipt_path) }}" target="_blank" rel="noopener" class="btn btn-outline" style="font-size:12px">{{ $isAr ? 'شوف آخر إيصال' : 'View latest receipt' }}</a>
    @endif
  </div>
  @if($order->payment_status === 'rejected')
    <div style="margin-top:12px;padding:10px 12px;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:13px">
      {{ $isAr ? 'الإيصال اترفض' : 'Receipt rejected' }}{{ $order->payment_rejection_reason ? ': '.$order->payment_rejection_reason : '.' }} {{ $isAr ? 'ارفع إيصال جديد أوضح تحت.' : 'Upload a clearer new receipt below.' }}
    </div>
  @elseif($order->payment_status === 'confirmed')
    <div style="margin-top:12px;padding:12px 14px;border-radius:9px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:13px;line-height:1.6">
      <strong>✓ {{ $isAr ? 'تم تأكيد الدفع بنجاح.' : 'Your payment has been confirmed.' }}</strong>
      <div style="margin-top:3px">{{ $isAr ? 'الإيصال اتراجع واتقبل، ومش محتاج ترفع حاجة تاني.' : 'Your receipt was reviewed and approved. You do not need to upload anything else.' }}</div>
    </div>
  @elseif($receiptUnderReview && $accountHasUploadedReceipt)
    <div style="margin-top:12px;padding:12px 14px;border-radius:9px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:13px;line-height:1.6">
      <strong>✓ {{ $isAr ? 'تم رفع الإيصال وهو تحت المراجعة.' : 'Your receipt has been uploaded and is pending review.' }}</strong>
      @unless($uploadWindowClosed)
        <div style="margin-top:3px">{{ $isAr ? 'متاح لك '.$receiptUploadsRemaining.' من أصل '.$receiptUploadLimit.' محاولات رفع.' : $receiptUploadsRemaining.' of '.$receiptUploadLimit.' upload attempts remain.' }}</div>
      @endunless
    </div>
  @endif
  @if($order->payment_status === 'expired')
    <div style="margin-top:12px;padding:12px 14px;border-radius:9px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:13px;line-height:1.6">
      <strong>{{ $isAr ? 'انتهت مهلة الدفع واتلغى الطلب.' : 'The payment deadline passed and this order was cancelled.' }}</strong>
      <div>{{ $isAr ? 'لو لسه عايز المنتجات، اعمل طلب جديد.' : 'If you still want these items, place a new order.' }}</div>
    </div>
  @endif
  @if(!$cancelled && ($awaitingPayment || $receiptUnderReview))
      @if($order->payment_due_at && $awaitingPayment)
        <div style="margin-top:12px;padding:11px 13px;border-radius:9px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:13px;line-height:1.55">
          {{ $isAr ? 'ادفع وارفع الإيصال قبل' : 'Pay and upload your receipt by' }}
          <strong>{{ \Carbon\Carbon::parse($order->payment_due_at)->setTimezone('Africa/Cairo')->locale($isAr ? 'ar' : 'en')->translatedFormat($isAr ? 'j F Y، g:i A' : 'M d, Y · g:i A') }} {{ $isAr ? 'بتوقيت القاهرة' : 'Cairo time' }}</strong>.
          {{ $isAr ? 'الطلب هيتلغى تلقائي لو مهلة الـ24 ساعة خلصت من غير إيصال.' : 'The order is cancelled automatically if the 24-hour window ends without a receipt.' }}
        </div>
      @endif
    @if($accountPaymentMethod && (!$uploadWindowClosed || $receiptUnderReview))
      <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-top:12px">
        @if($receiptUnderReview && $accountHasUploadedReceipt)
          {{ $isAr ? 'بنراجع تحويل' : 'We are reviewing your transfer of' }} <strong>{{ number_format($order->final_total, 2) }} EGP</strong>{{ $isAr ? ' إلى ' : ' to ' }}<strong>{{ $accountPaymentMethod['destination'] }}</strong>{{ $isAr ? ' حسب الإيصال المرفوع.' : ' using the uploaded receipt.' }}
        @else
          {{ $isAr ? 'حوّل' : 'Transfer' }} <strong>{{ number_format($order->final_total, 2) }} EGP</strong> {{ $isAr ? 'إلى' : 'to' }} <strong>{{ $accountPaymentMethod['destination'] }}</strong>{{ $isAr ? '، وبعدها ارفع الإيصال تحت.' : ', then upload the receipt below.' }}
        @endif
      </p>
      @if($awaitingPayment && !empty($accountPaymentMethod['link']))
        <a class="btn btn-outline" href="{{ $accountPaymentMethod['link'] }}" target="_blank" rel="noopener">{{ $isAr ? 'افتح رابط إنستاباي' : 'Open InstaPay link' }}</a>
      @endif
    @endif
    @if($uploadWindowClosed)
      <p style="font-size:13px;color:#6b7280;margin-top:12px">{{ $isAr ? 'مهلة رفع الإيصال انتهت.' : 'The receipt upload window has closed.' }}</p>
    @elseif($canUploadReceipt)
      <form method="POST" action="{{ route('account.order.payment-receipt', $order->id) }}" enctype="multipart/form-data" style="margin-top:16px">
        @csrf
        <div style="font-size:12px;font-weight:700;color:#6b7280;margin-bottom:7px">
          {{ $accountHasUploadedReceipt ? ($isAr ? 'لو عايز تغيّر الإيصال الحالي، اختار صورة بديلة. متبقي '.$receiptUploadsRemaining.' من '.$receiptUploadLimit.'.' : 'Choose a replacement image only if you need to change the current receipt. '.$receiptUploadsRemaining.' of '.$receiptUploadLimit.' attempts remain.') : ($isAr ? 'اختار صورة الإيصال وسيتم رفعها تلقائياً. متبقي '.$receiptUploadsRemaining.' من '.$receiptUploadLimit.'.' : 'Choose the receipt image and it will upload automatically. '.$receiptUploadsRemaining.' of '.$receiptUploadLimit.' attempts remain.') }}
        </div>
        <input id="account-receipt-{{ $order->id }}" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0" onchange="if(this.files.length){document.getElementById('account-receipt-status-{{ $order->id }}').textContent='{{ $isAr ? 'جارٍ رفع الإيصال…' : 'Uploading receipt…' }}';this.form.requestSubmit();}">
        <label for="account-receipt-{{ $order->id }}" class="btn btn-dark" style="display:inline-flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer">
          {{ $accountHasUploadedReceipt ? ($isAr ? 'ارفع إيصال بديل' : 'Upload replacement receipt') : ($isAr ? 'ارفع الإيصال' : 'Upload receipt') }}
        </label>
        <div id="account-receipt-status-{{ $order->id }}" aria-live="polite" style="display:inline-block;margin-inline-start:8px;font-size:12px;color:#6b7280"></div>
        <noscript><button class="btn btn-dark" style="font-size:12px;margin-top:8px">{{ $isAr ? 'ابعت الإيصال' : 'Submit receipt' }}</button></noscript>
        @error('receipt')<div class="err" style="margin-top:6px">{{ $message }}</div>@enderror
      </form>
    @else
      <div style="margin-top:16px;padding:12px 14px;border-radius:9px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:13px;line-height:1.6">
        <strong>{{ $isAr ? 'وصلت للحد الأقصى لرفع الإيصالات.' : 'You have reached the receipt upload limit.' }}</strong>
        <div>{{ $isAr ? 'تم استخدام 3 من 3 محاولات. من فضلك استنى مراجعة الدفع.' : 'All 3 of 3 upload attempts have been used. Please wait for payment review.' }}</div>
      </div>
    @endif
  @endif
  @if(isset($paymentReceipts) && $paymentReceipts->count())
    <div style="margin-top:18px;padding-top:14px;border-top:1px solid #fed7aa">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:#92400e;margin-bottom:8px">{{ $isAr ? 'سجل الإيصالات' : 'Receipt history' }}</div>
      @if($order->payment_status === 'confirmed' && $paymentReceipts->contains(fn ($receipt) => $receipt->status === 'superseded'))
        <div style="margin-bottom:8px;padding:9px 11px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:12px;line-height:1.6">
          {{ $isAr ? 'تم تأكيد الدفع. الإيصالات القديمة اتعلّمت كإيصالات مستبدلة ومش محتاجة مراجعة.' : 'Payment is confirmed. Older uploads were marked as replaced and no longer need review.' }}
        </div>
      @endif
      @foreach($paymentReceipts as $receipt)
        @php
          $receiptStatus = match ($receipt->status) {
            'confirmed' => [$isAr ? 'تم التأكيد' : 'Confirmed', '#15803d'],
            'rejected' => [$isAr ? 'مرفوض' : 'Rejected', '#b91c1c'],
            'superseded' => [$isAr ? 'مستبدل' : 'Replaced', '#6b7280'],
            default => [$isAr ? 'قيد المراجعة' : 'Pending review', '#92400e'],
          };
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:12px;padding:7px 0;border-bottom:1px solid #ffedd5">
          <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($receipt->file_path) }}" target="_blank" rel="noopener">{{ $receipt->original_name ?: ($isAr ? 'إيصال رقم '.$receipt->id : 'Receipt #'.$receipt->id) }}</a>
          <span style="color:{{ $receiptStatus[1] }};font-weight:700;white-space:nowrap">{{ $receiptStatus[0] }}</span>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endif

{{-- ── YOU SAVED BANNER ──────────────────────────────────────────── --}}
@if(!$cancelled && $totalSavings > 0)
<div style="margin-top:14px;background:linear-gradient(135deg,#dcfce7 0%,#f0fdf4 100%);border:1.5px solid #86efac;border-radius:14px;padding:16px 20px">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
    <div style="font-size:22px">🎉</div>
    <div>
      <div style="font-size:15px;font-weight:800;color:#15803d">{{ $isAr ? 'وفّرت' : 'You saved' }} {{ number_format($totalSavings, 2) }} EGP {{ $isAr ? 'في الطلب ده!' : 'on this order!' }}</div>
      <div style="font-size:12px;color:#16a34a;margin-top:2px">{{ $isAr ? 'عرض حلو — دي التفاصيل:' : "Great deal — here's the breakdown:" }}</div>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;gap:6px">
    @if($saleSavings > 0)
      <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:8px;padding:8px 12px">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:14px">🏷️</span>
          <span style="font-size:13px;font-weight:600;color:#15803d">{{ $isAr ? 'خصم سعر العرض' : 'Sale price discount' }}</span>
        </div>
        <span style="font-size:13px;font-weight:800;color:#15803d">−{{ number_format($saleSavings, 2) }} EGP</span>
      </div>
    @endif
    @if($couponSavings > 0)
      <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:8px;padding:8px 12px">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:14px">🎟️</span>
          <div>
            <span style="font-size:13px;font-weight:600;color:#15803d">{{ $isAr ? 'كود خصم' : 'Coupon' }}</span>
            @if($couponCode)
              <span style="margin-left:6px;background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;padding:2px 7px;border-radius:999px;font-family:monospace;border:1px solid #86efac">{{ strtoupper($couponCode) }}</span>
            @endif
          </div>
        </div>
        <span style="font-size:13px;font-weight:800;color:#15803d">−{{ number_format($couponSavings, 2) }} EGP</span>
      </div>
    @endif
  </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- SUB-ORDERS (one per vendor)                                        --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@if($hasSubOrders)

  @foreach($subOrders as $sub)
    @php
      $subStepIndex = match($sub->status) {
        'pending'    => 0,
        'processing' => 1,
        'shipped'    => 2,
        'completed', 'delivered' => 3,
        default      => 0,
      };
      $subCancelled = in_array($sub->status, ['cancelled']);
      $subFillPct   = $subCancelled ? 0 : match($subStepIndex) { 0=>0,1=>33,2=>66,3=>100,default=>0 };
      $subSteps     = $isAr ? ['pending'=>'في الانتظار','processing'=>'جاري التجهيز','shipped'=>'اتشحن','delivered'=>'اتسلّم'] : ['pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered'];
      $store = $sub->store ?? null;
      $storeName = $store['name'] ?? $sub->vendor_shop_name ?? ($isAr ? 'متجر رامو' : 'Ramo Store');
    @endphp

    <section class="odx-card">

      {{-- Sub-order header --}}
      <div class="odx-card-heading">
        <div class="odx-store">
          @if(!empty($store['logo']))
            <img class="odx-store-logo" src="{{ $store['logo'] }}" alt="{{ $storeName }}" loading="lazy">
          @else
            <div class="odx-store-logo odx-store-initial" aria-hidden="true">{{ mb_substr($storeName, 0, 1) }}</div>
          @endif
          <div class="odx-store-copy">
            <div class="odx-eyebrow" style="margin-bottom:3px">{{ $isAr ? 'المتجر' : 'Sold by' }}</div>
            <div class="odx-store-name">{{ $storeName }}</div>
            <div class="odx-store-sub">
              {{ $isAr ? 'طلب الشحنة' : 'Shipment' }} #{{ $sub->id }}
              @if(!empty($store['address'])) · {{ $store['address'] }} @endif
              @if(!empty($store['rating_count']) && $store['rating_count'] > 0) · ★ {{ number_format($store['rating'], 1) }} ({{ $store['rating_count'] }}) @endif
            </div>
          </div>
        </div>
        <div class="odx-store-actions">
          @if(!empty($store['id']))<a class="odx-store-link" href="{{ route('vendor.store', $store['id']) }}">{{ $isAr ? 'زيارة المتجر' : 'Visit store' }} →</a>@endif
          <span class="status-badge status-{{ $sub->status }}">{{ $statusLabel($sub->status) }}</span>
        </div>
      </div>
      @if($sub->tracking_number)
        <div class="odx-tracking">{{ $isAr ? 'رقم التتبع:' : 'Tracking number:' }} <strong>{{ $sub->tracking_number }}</strong>@if($sub->tracking_carrier) · {{ $isAr ? 'شركة الشحن' : 'Carrier' }}: <strong>{{ $sub->tracking_carrier }}</strong>@endif</div>
      @endif

      {{-- Sub-order progress bar --}}
      @if(!$subCancelled)
      <div style="padding:16px 18px 4px;background:#fff">
        <div style="position:relative;display:flex;justify-content:space-between;margin-bottom:20px">
          <div style="position:absolute;top:14px;inset-inline:0;height:3px;background:#e5e7eb;z-index:0"></div>
          <div style="position:absolute;top:14px;inset-inline-start:0;height:3px;background:#e85d26;z-index:1;width:{{ $subFillPct }}%;transition:width .4s"></div>
          @foreach($subSteps as $sKey => $sLabel)
            @php
              $si = array_search($sKey, array_keys($subSteps));
              $sCls = $si < $subStepIndex ? 'done' : ($si === $subStepIndex ? 'current' : '');
            @endphp
            <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:4px;flex:1">
              <div style="width:28px;height:28px;border-radius:50%;border:3px solid {{ $sCls==='done'?'#e85d26':($sCls==='current'?'#e85d26':'#e5e7eb') }};background:{{ $sCls==='done'?'#e85d26':($sCls==='current'?'#fff7ed':'#fff') }};display:flex;align-items:center;justify-content:center">
                @if($sCls === 'done')
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif($sCls === 'current')
                  <div style="width:8px;height:8px;border-radius:50%;background:#e85d26"></div>
                @endif
              </div>
              <div style="font-size:10px;font-weight:600;color:{{ $sCls?'#111827':'#6b7280' }};text-align:center;max-width:60px">{{ $sLabel }}</div>
            </div>
          @endforeach
        </div>
      </div>
      @else
      <div style="padding:10px 18px;background:#fff;font-size:13px;color:#ef4444;font-weight:600">{{ $isAr ? 'الشحنة دي اتلغت.' : 'This shipment was cancelled.' }}</div>
      @endif

      {{-- Sub-order items --}}
      <div class="odx-products">
        <div class="odx-eyebrow" style="margin:10px 0 0">{{ $isAr ? 'منتجات الشحنة' : 'Products in this shipment' }}</div>
        @foreach($sub->items as $item)
          <article class="odx-product">
            @if(!empty($item['product_info']['image']))
              <img class="odx-product-thumb" src="{{ $item['product_info']['image'] }}" alt="{{ $item['name'] ?? ($isAr ? 'صورة المنتج' : 'Product image') }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
              <div class="odx-product-thumb" hidden aria-hidden="true">▧</div>
            @else
              <div class="odx-product-thumb" aria-hidden="true">▧</div>
            @endif
            <div class="odx-product-main">
              <a href="{{ route('product', $item['product_id']) }}" class="odx-product-name">{{ $item['name'] ?? ($isAr ? 'منتج' : 'Product') }}</a>
              @if(!empty($item['product_info']['description']))<p class="odx-product-description">{{ strip_tags($item['product_info']['description']) }}</p>@endif
              <div class="odx-product-meta">
                @if(!empty($item['product_info']['sku']))<span class="odx-product-tag">SKU: {{ $item['product_info']['sku'] }}</span>@endif
                @if(!empty($item['model_number']))<span class="odx-product-tag">{{ $isAr ? 'موديل' : 'Model' }}: {{ $item['model_number'] }}</span>@endif
                @if(!empty($item['attributes']) && is_array($item['attributes']))
                  @foreach($item['attributes'] as $k => $v)<span class="odx-product-tag">{{ $k }}: {{ $v }}</span>@endforeach
                @endif
              </div>
            </div>
            <div class="odx-product-total">{{ number_format((float) ($item['subtotal'] ?? 0), 2) }} EGP<small>{{ $isAr ? 'الكمية' : 'Qty' }} {{ $item['quantity'] ?? 1 }}</small></div>
          </article>
        @endforeach
      </div>
      <div class="odx-totals">
        <div class="odx-total-row"><span>{{ $isAr ? 'الإجمالي الفرعي' : 'Subtotal' }}</span><strong>{{ number_format($sub->subtotal, 2) }} EGP</strong></div>
        @if($sub->discount_total > 0)
          <div class="odx-total-row"><span>{{ $isAr ? 'الخصم' : 'Discount' }}</span><strong style="color:#16a34a">−{{ number_format($sub->discount_total, 2) }} EGP</strong></div>
        @endif
        <div class="odx-total-row is-final"><span>{{ $isAr ? 'إجمالي الشحنة' : 'Shipment total' }}</span><strong>{{ number_format($sub->total, 2) }} EGP</strong></div>
      </div>

      {{-- Messages & reply for this sub-order --}}
      <div style="padding:16px 18px;background:#fafafa;border-top:1px solid #e5e7eb">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:12px">
          {{ $isAr ? 'رسايل مع' : 'Messages with' }} {{ $storeName }}
        </div>

        @if(isset($sub->messages) && count($sub->messages) > 0)
          <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
            @foreach($sub->messages as $msg)
              <div style="border:1px solid {{ $msg->is_vendor_response ? '#fdba74' : '#e5e7eb' }};background:{{ $msg->is_vendor_response ? '#fff7ed' : '#fff' }};border-radius:10px;padding:10px 13px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                  <strong style="color:{{ $msg->is_vendor_response ? '#e85d26' : '#111827' }}">
                    {{ $msg->is_vendor_response ? ($msg->vendor_shop_name ?: ($isAr ? 'البائع' : 'Vendor')) : ($isAr ? 'إنت' : 'You') }}
                  </strong>
                  <span style="color:#6b7280">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y, g:i A') }}</span>
                </div>
                <div style="font-size:13px;line-height:1.7;color:#111827">{{ $msg->message }}</div>
                @if($msg->is_vendor_response)
                  <div style="margin-top:6px;display:inline-block;background:rgba(232,93,38,.12);color:#e85d26;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px">{{ $isAr ? 'البائع' : 'Vendor' }}</div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div style="color:#6b7280;font-size:13px;margin-bottom:12px">{{ $isAr ? 'مفيش رسايل لسه. اسأل تحت.' : 'No messages yet. Ask a question below.' }}</div>
        @endif

        <form method="POST" action="{{ route('account.order.messages.store', $order->id) }}">
          @csrf
          <input type="hidden" name="sub_order_id" value="{{ $sub->id }}">
          <textarea name="message" rows="3" placeholder="{{ $isAr ? 'ابعت رسالة لـ ' : 'Message ' }}{{ $storeName }}..." style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
          <div style="margin-top:8px">
            <button type="submit" class="btn btn-dark" style="font-size:12px;padding:8px 16px">{{ $isAr ? 'ابعت الرسالة' : 'Send Message' }}</button>
          </div>
        </form>
      </div>

      {{-- Refund / Return CTA per sub-order --}}
      @if(in_array($sub->status, ['delivered','completed','shipped','processing']))
        <div style="padding:12px 18px;background:#fff;border-top:1px solid #e5e7eb">
          <a href="{{ route('account.refunds.create', ['order_id' => $order->id]) }}" style="font-size:12px;color:#e85d26;text-decoration:none;font-weight:600">⚠ {{ $isAr ? 'اطلب استرجاع للشحنة دي ←' : 'Request Refund / Return for this shipment →' }}</a>
        </div>
      @endif

    </section>{{-- end sub-order card --}}
  @endforeach

@else
  {{-- LEGACY: no sub-orders, show all items as before --}}
  <section class="odx-card">
    <div class="odx-card-heading"><h2>{{ $isAr ? 'منتجات الطلب' : 'Order items' }}</h2></div>
    <div class="odx-products">
    @foreach($lineItems as $item)
      <article class="odx-product">
        @if(!empty($item['product_info']['image']))
          <img class="odx-product-thumb" src="{{ $item['product_info']['image'] }}" alt="{{ $item['name'] ?? ($isAr ? 'صورة المنتج' : 'Product image') }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
          <div class="odx-product-thumb" hidden aria-hidden="true">▧</div>
        @else
          <div class="odx-product-thumb" aria-hidden="true">▧</div>
        @endif
        <div class="odx-product-main">
          <a href="{{ route('product', $item['product_id']) }}" class="odx-product-name">{{ $item['name'] ?? ($isAr ? 'منتج' : 'Product') }}</a>
          @if(!empty($item['product_info']['store']['id']))<div class="odx-store-sub">{{ $isAr ? 'من متجر' : 'Sold by' }} <a class="odx-store-link" href="{{ route('vendor.store', $item['product_info']['store']['id']) }}">{{ $item['product_info']['store']['name'] }}</a></div>@endif
          @if(!empty($item['product_info']['description']))<p class="odx-product-description">{{ strip_tags($item['product_info']['description']) }}</p>@endif
          <div class="odx-product-meta">
            @if(!empty($item['product_info']['sku']))<span class="odx-product-tag">SKU: {{ $item['product_info']['sku'] }}</span>@endif
            @if(!empty($item['model_number']))<span class="odx-product-tag">{{ $isAr ? 'موديل' : 'Model' }}: {{ $item['model_number'] }}</span>@endif
            @if(!empty($item['attributes']) && is_array($item['attributes']))
              @foreach($item['attributes'] as $k => $v)<span class="odx-product-tag">{{ $k }}: {{ $v }}</span>@endforeach
            @endif
          </div>
        </div>
        <div class="odx-product-total">{{ number_format((float) ($item['subtotal'] ?? 0), 2) }} EGP<small>{{ $isAr ? 'الكمية' : 'Qty' }} {{ $item['quantity'] ?? 1 }}</small></div>
      </article>
    @endforeach
    </div>
    <div class="odx-totals">
      <div class="odx-total-row"><span>{{ $isAr ? 'الإجمالي الفرعي' : 'Subtotal' }}</span><strong>{{ number_format($order->original_total, 2) }} EGP</strong></div>
      @if($order->discount_total > 0)
        <div class="odx-total-row"><span>{{ $isAr ? 'الخصم' : 'Discount' }}</span><strong style="color:#16a34a">−{{ number_format($order->discount_total, 2) }} EGP</strong></div>
      @endif
      <div class="odx-total-row is-final"><span>{{ $isAr ? 'الإجمالي' : 'Total' }}</span><strong>{{ number_format($order->final_total, 2) }} EGP</strong></div>
    </div>
  </section>
@endif

{{-- ── SHIPPING ADDRESS ─────────────────────────────────────────── --}}
@if(!empty($billing))
<section class="odx-card">
  <div class="odx-card-heading"><h2>{{ $isAr ? 'عنوان الشحن' : 'Shipping address' }}</h2></div>
  <div class="odx-address" style="padding:16px 18px">
    <p>
      <strong>{{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}</strong><br>
      {{ $billing['address_1'] ?? '' }}<br>
      {{ $billing['city'] ?? '' }}@if(!empty($billing['state'])), {{ $billing['state'] }}@endif<br>
      {{ $billing['phone'] ?? '' }}
    </p>
    @if(!empty($billing['latitude']) && !empty($billing['longitude']))
      <iframe width="100%" height="220" style="border:0;border-radius:12px;margin-top:10px" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q={{ $billing['latitude'] }},{{ $billing['longitude'] }}&z=15&output=embed"></iframe>
    @endif
  </div>
</section>
@endif

<div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
  <a href="{{ route('account.orders') }}" class="btn btn-outline">{{ $isAr ? 'الرجوع للطلبات ←' : '← Back to Orders' }}</a>
</div>
</div>
@endsection
