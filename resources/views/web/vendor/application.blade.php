@php
  $isAr = session('locale') === 'ar';
  $tr = fn ($en, $ar) => $isAr ? $ar : $en;
  $status = $application?->status ?? 'draft';
  $market = $status === 'approved' && !$application?->vendor_id;
  $draft = $application?->marketplace_draft ?? [];
  $fields = [
    'email' => [$tr('Email address', 'البريد الإلكتروني'), 'email'],
    'first_name' => [$tr('First name', 'الاسم الأول'), 'text'],
    'last_name' => [$tr('Last name', 'اسم العائلة'), 'text'],
    'phone' => [$tr('Phone number', 'رقم الموبايل'), 'tel'],
  ];
@endphp
<!doctype html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $tr('Become a vendor', 'ابدأ البيع') }} — Ramo Store</title>
  <style>
    *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;color:#171717;background:#fff}
    .wizard-top{min-height:72px;padding:16px clamp(20px,4vw,52px);display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #eee}
    .brand{font-weight:800;text-decoration:none;color:#171717;font-size:19px}.brand span{color:#e85d26}
    .exit{color:#333;border:1px solid #ddd;padding:10px 16px;border-radius:999px;text-decoration:none;font-size:13px;font-weight:700}
    main{max-width:660px;margin:0 auto;padding:clamp(34px,6vw,76px) 20px 120px}
    h1{font-size:clamp(25px,4vw,36px);line-height:1.18;margin:0 0 12px;letter-spacing:-.025em}
    p{color:#646464;line-height:1.6;margin:0 0 22px}.step-count{color:#ad431e;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px}
    label{display:block;font-size:14px;font-weight:700;margin:18px 0 6px}input,select{font:inherit;width:100%;padding:14px;border:1px solid #ccc;border-radius:10px;background:white;color:#111}
    input:focus,select:focus{outline:2px solid #e85d26;outline-offset:2px}input[type=file]{padding:12px}
    .notice{background:#fff8f4;border:1px solid #f4d9ca;border-radius:12px;padding:15px;font-size:14px;line-height:1.55;margin:18px 0}
    .notice p{margin:4px 0 0;font-size:13px}.error{background:#fff2f1;border-color:#f5b9b4;color:#9e2c20}
    .saved{background:#effaf1;border-color:#c6e9cc}.summary{display:grid;gap:10px}.summary div{padding:12px 14px;background:#f8f8f7;border-radius:10px;overflow-wrap:anywhere}
    .summary small{display:block;color:#777}.step[hidden]{display:none}.wizard-bar{position:fixed;inset-inline:0;bottom:0;background:white;border-top:1px solid #e5e5e5;z-index:5}
    .progress{height:5px;background:#eee}.progress span{display:block;height:100%;background:#e85d26;transition:width .2s}
    .bar-inner{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px clamp(20px,4vw,52px)}
    .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:10px;background:#161616;color:white;padding:13px 22px;font:inherit;font-weight:700;cursor:pointer;text-decoration:none;min-height:46px}
    .btn-light{background:white;color:#222;border:1px solid #ddd}.bar-actions{display:flex;gap:8px;align-items:center}.secondary{font-size:13px}
    [hidden]{display:none!important}@media(max-width:480px){.bar-inner{padding:10px 16px}.btn{padding:10px 14px;font-size:13px}.secondary{font-size:12px}}
  </style>
</head>
<body>
<header class="wizard-top">
  <a class="brand" href="{{ route('home') }}">RAMO<span>STORE</span></a>
  <a class="exit" href="{{ route('account.hub') }}">{{ $tr('Exit', 'خروج') }}</a>
</header>
<main>
  @if(session('success'))<div class="notice saved" role="status">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="notice error" role="alert">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="notice error" role="alert"><strong>{{ $tr('Please check your information', 'راجع بياناتك من فضلك') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  @if($application?->vendor_id && $status === 'approved' && $sellerStatus !== 'approved')
    <h1>{{ $tr('Seller account unavailable', 'حساب البائع غير متاح') }}</h1>
    <p>{{ $tr('Please contact support about your seller account.', 'من فضلك تواصل مع الدعم بخصوص حساب البائع.') }}</p>
    <a class="btn" href="{{ route('account.hub') }}">{{ $tr('Back to your account', 'العودة لحسابك') }}</a>
  @elseif($application?->vendor_id && $status === 'approved')
    <h1>{{ $tr('Your marketplace is ready', 'متجرك جاهز') }}</h1>
    <p>{{ $tr('Use your customer account to open your seller dashboard.', 'استخدم حسابك لفتح لوحة البائع.') }}</p>
    <form method="POST" action="{{ route('vendor.marketplace.switch') }}">@csrf<button class="btn">{{ $tr('Switch to Seller', 'افتح لوحة البائع') }}</button></form>
  @elseif($status === 'pending')
    <h1>{{ $tr('Your application is under review', 'طلبك تحت المراجعة') }}</h1>
    <p>{{ $tr('We will show the result in your account notifications. You can continue shopping while we review it.', 'هتلاقي النتيجة في إشعارات حسابك. تقدر تكمل تسوق أثناء المراجعة.') }}</p>
    <a class="btn" href="{{ route('account.hub') }}">{{ $tr('Back to your account', 'العودة لحسابك') }}</a>
  @elseif($status === 'on_hold')
    <h1>{{ $tr('Your application is on hold', 'طلبك مؤجل للمراجعة لاحقًا') }}</h1>
    <p>{{ $tr('We may consider your application in the future. We will notify you if there is an update.', 'ممكن نراجع طلبك في المستقبل. هنبعتلك إشعار لو فيه تحديث.') }}</p>
    <a class="btn" href="{{ route('account.hub') }}">{{ $tr('Back to your account', 'العودة لحسابك') }}</a>
  @elseif($status === 'rejected' && $application->submission_count >= 2)
    <h1>{{ $tr('Application limit reached', 'وصلت للحد الأقصى للطلبات') }}</h1>
    <p>{{ $tr('You have submitted two applications. Please contact support if you need help.', 'قدمت طلبين. تواصل مع الدعم لو محتاج مساعدة.') }}</p>
    @if($application->rejection_reason)<div class="notice error">{{ $application->rejection_reason }}</div>@endif
    <a class="btn" href="{{ route('account.hub') }}">{{ $tr('Back to your account', 'العودة لحسابك') }}</a>
  @elseif($status === 'approved')
    <form method="POST" action="{{ route('vendor.marketplace.save') }}" enctype="multipart/form-data" id="wizard-form">
      @csrf
      <section class="step">
        <div class="step-count">{{ $tr('Marketplace setup', 'إعداد المتجر') }} · 1 / 5</div>
        <h1>{{ $tr('Give your marketplace a name', 'اختار اسم لمتجرك') }}</h1>
        <p>{{ $tr('Choose a name customers will recognize.', 'اختار اسم يعرفك بيه العملاء.') }}</p>
        <label for="shop-name">{{ $tr('Store name', 'اسم المتجر') }}</label>
        <input id="shop-name" name="shop_name" value="{{ old('shop_name', $draft['shop_name'] ?? '') }}" maxlength="255" required>
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Marketplace setup', 'إعداد المتجر') }} · 2 / 5</div>
        <h1>{{ $tr('What will you sell?', 'هتبيع إيه؟') }}</h1>
        <p>{{ $tr('Choose the category that best describes your store.', 'اختار التصنيف الأقرب لمتجرك.') }}</p>
        <label for="category">{{ $tr('Store category', 'تصنيف المتجر') }}</label>
        <select id="category" name="category_id" required><option value="">{{ $tr('Choose a category', 'اختار تصنيف') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $draft['category_id'] ?? '') === (string) $category->id)>{{ \App\Support\StorefrontLabels::category($category->name, $isAr) }}</option>@endforeach</select>
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Marketplace setup', 'إعداد المتجر') }} · 3 / 5</div>
        <h1>{{ $tr('Where is your store?', 'فين عنوان متجرك؟') }}</h1>
        <p>{{ $tr('Add your store address and contact number.', 'أضف عنوان المتجر ورقم التواصل.') }}</p>
        <label for="shop-address">{{ $tr('Store address', 'عنوان المتجر') }}</label>
        <input id="shop-address" name="shop_address" value="{{ old('shop_address', $draft['shop_address'] ?? '') }}" maxlength="500" required>
        <label for="shop-phone">{{ $tr('Store phone', 'رقم المتجر') }}</label>
        <input id="shop-phone" name="shop_phone" type="tel" value="{{ old('shop_phone', $draft['shop_phone'] ?? ($personal['phone'] ?? '')) }}" maxlength="20" required>
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Marketplace setup', 'إعداد المتجر') }} · 4 / 5</div>
        <h1>{{ $tr('Add your store photos', 'أضف صور متجرك') }}</h1>
        <p>{{ $tr('A logo and banner are optional. You can update them later.', 'الشعار والغلاف اختياريين وتقدر تعدلهم بعدين.') }}</p>
        <label for="shop-logo">{{ $tr('Store logo', 'شعار المتجر') }}</label>
        <input id="shop-logo" type="file" name="shop_logo" accept="image/jpeg,image/png,image/webp">
        @if(!empty($draft['images']['shop_logo']))<p>{{ $tr('A logo is saved in your draft.', 'تم حفظ شعار في المسودة.') }}</p>@endif
        <label for="shop-banner">{{ $tr('Store banner', 'غلاف المتجر') }}</label>
        <input id="shop-banner" type="file" name="shop_banner" accept="image/jpeg,image/png,image/webp">
        @if(!empty($draft['images']['shop_banner']))<p>{{ $tr('A banner is saved in your draft.', 'تم حفظ غلاف في المسودة.') }}</p>@endif
        <p style="font-size:12px;margin-top:8px">{{ $tr('JPG, PNG or WebP, up to 4 MB each.', 'JPG أو PNG أو WebP، بحد أقصى 4 ميجابايت لكل صورة.') }}</p>
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Marketplace setup', 'إعداد المتجر') }} · 5 / 5</div>
        <h1>{{ $tr('Review and create', 'راجع وأنشئ متجرك') }}</h1>
        <p>{{ $tr('Check your details. You can edit your store after creating it.', 'راجع بياناتك. تقدر تعدل متجرك بعد إنشائه.') }}</p>
        <div class="summary" id="market-summary"></div>
      </section>
      <div class="wizard-bar"><div class="progress"><span id="progress"></span></div><div class="bar-inner">
        <button class="btn btn-light" type="button" id="previous" hidden>{{ $tr('Back', 'رجوع') }}</button>
        <div class="bar-actions">
          <button class="btn btn-light secondary" name="action" value="save" formnovalidate>{{ $tr('Save & exit', 'احفظ واخرج') }}</button>
          <button class="btn" type="button" id="next">{{ $tr('Next', 'التالي') }}</button>
          <button class="btn" name="action" value="create" id="finish">{{ $tr('Create marketplace', 'أنشئ المتجر') }}</button>
        </div>
      </div></div>
    </form>
  @else
    @if($status === 'rejected')
      <div class="notice error"><strong>{{ $tr('Your application needs changes', 'طلبك محتاج تعديل') }}</strong><p>{{ $application->rejection_reason }}</p></div>
      <p>{{ $tr('You have one application remaining.', 'متاح لك تقديم طلب واحد كمان.') }}</p>
    @endif
    <form method="POST" action="{{ route('vendor.register.submit') }}" enctype="multipart/form-data" id="wizard-form">
      @csrf
      <section class="step">
        <div class="step-count">{{ $tr('Your application', 'طلبك') }} · 1 / 3</div>
        <h1>{{ $tr('Let’s start with your details', 'خلّينا نبدأ ببياناتك') }}</h1>
        <p>{{ $tr('We’ll use the information already in your account and ask only for missing details.', 'هنستخدم البيانات الموجودة في حسابك وهنطلب الناقص بس.') }}</p>
        @foreach($fields as $key => [$label, $type])
          @if(empty($existing[$key]))
            <label for="{{ $key }}">{{ $label }}</label>
            <input id="{{ $key }}" name="{{ $key }}" type="{{ $type }}" value="{{ old($key, $personal[$key] ?? '') }}" maxlength="{{ $key === 'email' ? 255 : ($key === 'phone' ? 20 : 100) }}" required>
          @endif
        @endforeach
        @if(collect($existing)->filter()->isNotEmpty())
          <div class="notice"><strong>{{ $tr('From your account', 'من حسابك') }}</strong>
            @foreach($fields as $key => [$label, $type])@if(!empty($existing[$key]))<p>{{ $label }}: {{ $existing[$key] }}</p>@endif @endforeach
          </div>
        @endif
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Your application', 'طلبك') }} · 2 / 3</div>
        <h1>{{ $tr('Where do you live?', 'فين مكان إقامتك؟') }}</h1>
        <p>{{ $tr('Your residential address is used for your application and is not shown on your store.', 'عنوان الإقامة للمراجعة ومش هيظهر في متجرك.') }}</p>
        <label for="address">{{ $tr('Residential address', 'عنوان الإقامة') }}</label>
        <input id="address" name="address" value="{{ old('address', $personal['address'] ?? '') }}" maxlength="500" required>
      </section>
      <section class="step">
        <div class="step-count">{{ $tr('Your application', 'طلبك') }} · 3 / 3</div>
        <h1>{{ $tr('Verify your identity', 'تحقق من هويتك') }}</h1>
        <p>{{ $requireIdentity ? $tr('Upload a clear photo of your ID for an admin to review.', 'ارفع صورة واضحة لهويتك لمراجعتها بواسطة الإدارة.') : $tr('You can optionally upload an ID photo for review.', 'تقدر ترفع صورة لهويتك للمراجعة، وده اختياري.') }}</p>
        <label for="identity">{{ $tr('ID photo', 'صورة الهوية') }} {{ $requireIdentity ? '*' : $tr('(optional)', '(اختياري)') }}</label>
        <input id="identity" type="file" name="identity_document" accept="image/jpeg,image/png,image/webp" @if($requireIdentity && !$application?->identity_document) required @endif>
        <p style="font-size:12px;margin-top:8px">{{ $tr('JPG, PNG or WebP, up to 4 MB.', 'JPG أو PNG أو WebP، بحد أقصى 4 ميجابايت.') }} @if($application?->identity_document){{ $tr('An ID is already saved.', 'تم حفظ صورة هوية من قبل.') }}@endif</p>
        <div class="notice"><strong>{{ $tr('Your privacy matters', 'خصوصيتك تهمنا') }}</strong><p>{{ $tr('Your application details and ID are encrypted in storage. Only authorized administrators can review your ID.', 'بيانات طلبك وصورة الهوية مشفرة عند التخزين. الإدارة المخوّلة فقط تقدر تراجع صورة الهوية.') }}</p><p>{{ $tr('Use a secure HTTPS connection when submitting your information.', 'استخدم اتصال HTTPS آمن عند إرسال بياناتك.') }}</p></div>
      </section>
      <div class="wizard-bar"><div class="progress"><span id="progress"></span></div><div class="bar-inner">
        <button class="btn btn-light" type="button" id="previous" hidden>{{ $tr('Back', 'رجوع') }}</button>
        <div class="bar-actions">
          <button class="btn btn-light secondary" name="action" value="save" formnovalidate>{{ $tr('Save & exit', 'احفظ واخرج') }}</button>
          <button class="btn" type="button" id="next">{{ $tr('Next', 'التالي') }}</button>
          <button class="btn" name="action" value="submit" id="finish">{{ $tr('Submit application', 'قدّم الطلب') }}</button>
        </div>
      </div></div>
    </form>
    <details style="margin-top:24px">
      <summary style="cursor:pointer;font-weight:700">{{ $tr('Already have a seller account?', 'عندك حساب بائع بالفعل؟') }}</summary>
      <form method="POST" action="{{ route('vendor.marketplace.link-existing') }}">
        @csrf
        <p style="margin-top:12px">{{ $tr('Sign in to your existing seller account to link it securely.', 'سجل دخول حساب البائع لربطه بحسابك بأمان.') }}</p>
        <label for="seller-email">{{ $tr('Seller email', 'بريد البائع') }}</label>
        <input id="seller-email" name="seller_email" type="email" required autocomplete="username">
        <label for="seller-password">{{ $tr('Seller password', 'كلمة سر البائع') }}</label>
        <input id="seller-password" name="seller_password" type="password" required autocomplete="current-password">
        <button class="btn" style="margin-top:14px">{{ $tr('Link seller account', 'اربط حساب البائع') }}</button>
      </form>
    </details>
  @endif
</main>
<script>
(() => {
  const form = document.getElementById('wizard-form');
  if (!form) return;
  const steps = [...form.querySelectorAll('.step')];
  const back = document.getElementById('previous');
  const next = document.getElementById('next');
  const finish = document.getElementById('finish');
  const progress = document.getElementById('progress');
  let current = 0;
  const render = () => {
    steps.forEach((step, index) => { step.hidden = index !== current; });
    back.hidden = current === 0;
    next.hidden = current === steps.length - 1;
    finish.hidden = current !== steps.length - 1;
    progress.style.width = ((current + 1) / steps.length * 100) + '%';
    const summary = document.getElementById('market-summary');
    if (summary) {
      summary.replaceChildren();
      ['shop_name', 'category_id', 'shop_address', 'shop_phone'].forEach(name => {
        const input = form.elements[name];
        const label = form.querySelector('label[for="' + input.id + '"]');
        const row = document.createElement('div');
        const heading = document.createElement('small');
        heading.textContent = label?.textContent || '';
        const value = document.createElement('strong');
        value.textContent = input.tagName === 'SELECT' ? input.selectedOptions[0]?.textContent : input.value;
        row.append(heading, value); summary.append(row);
      });
    }
    window.scrollTo(0, 0);
  };
  next.addEventListener('click', () => {
    for (const field of steps[current].querySelectorAll('input,select')) {
      if (!field.checkValidity()) { field.reportValidity(); return; }
    }
    current++; render();
  });
  back.addEventListener('click', () => { current--; render(); });
  form.addEventListener('submit', event => {
    if (event.submitter?.value === 'save') return;
    for (let index = 0; index < steps.length; index++) {
      for (const field of steps[index].querySelectorAll('input,select')) {
        if (!field.checkValidity()) { event.preventDefault(); current = index; render(); field.reportValidity(); return; }
      }
    }
  });
  render();
})();
</script>
</body>
</html>
