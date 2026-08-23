@extends('layouts.app')
@section('title', (session('locale') === 'ar' ? 'إنشاء حساب' : 'Create Account') . ' — Ramo Store')

@section('content')
@php $isAr = session('locale') === 'ar'; @endphp
<div class="page" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="max-width:480px;margin:0 auto;{{ $isAr ? 'text-align:right;font-family:Tahoma,Arial,sans-serif' : '' }}">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">{{ $isAr ? 'اعمل حساب' : 'Create an account' }}</h2>
    <p class="auth-sub">{{ $isAr ? 'انضم لرامو ستور وتجربة تسوّق أحلى.' : 'Join RamoStore for a better shopping experience' }}</p>

    @if(!empty($referralInviterName))
      <div id="referral-invitation" style="display:flex;align-items:center;gap:11px;margin:16px 0;padding:13px 14px;border:1px solid #ffd7b8;border-radius:13px;background:linear-gradient(135deg,#fff7ef,#fff);color:#6d4935">
        <span style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 34px;border-radius:50%;background:#f06a22;color:#fff;font-size:18px" aria-hidden="true">↗</span>
        <span style="display:block;font-size:13px;line-height:1.55"><strong style="display:block;color:#bd5317">{{ $isAr ? 'تمت دعوتك بواسطة' : 'You were invited by' }} {{ $referralInviterName }}</strong><small style="display:block;margin-top:2px;color:#8b7568">{{ $isAr ? 'سجّل من الدعوة دي وابدأ التسوق معانا.' : 'Sign up through this invitation and start shopping with us.' }}</small></span>
      </div>
    @endif

    @if($errors->any())
      <div class="alert-box alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
      <div class="alert-box alert-ok">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" onsubmit="document.getElementById('referral-invitation')?.remove()">
      @csrf
      <div class="form-grid-2">
        <div class="form-group">
          <label>{{ $isAr ? 'الاسم الأول' : 'First Name' }} *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" required>
        </div>
        <div class="form-group">
          <label>{{ $isAr ? 'اسم العيلة' : 'Last Name' }} *</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}" required>
        </div>
      </div>
      <div class="form-group">
        <label>{{ $isAr ? 'الإيميل' : 'Email Address' }} *</label>
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>{{ $isAr ? 'رقم الموبايل' : 'Phone Number' }} *</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx">
      </div>
      <div class="form-group">
        <label>{{ $isAr ? 'كلمة السر' : 'Password' }} *</label>
        <input type="password" name="password" required placeholder="{{ $isAr ? '6 حروف أو أرقام على الأقل' : 'Min 6 characters' }}">
      </div>
      <div class="form-group">
        <label>{{ $isAr ? 'أكد كلمة السر' : 'Confirm Password' }} *</label>
        <input type="password" name="password_confirmation" required placeholder="{{ $isAr ? 'اكتب كلمة السر تاني' : 'Repeat password' }}">
      </div>
      <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:14px">{{ $isAr ? 'اعمل حساب' : 'Create Account' }}</button>
    </form>

    <div class="auth-footer">
      {{ $isAr ? 'عندك حساب بالفعل؟' : 'Already have an account?' }} <a href="{{ route('login') }}">{{ $isAr ? 'سجّل دخول ←' : 'Sign in →' }}</a>
    </div>
  </div>
</div>
@if(!empty($referralInviterName))
<script>
(() => {
  const url = new URL(window.location.href);
  if (url.searchParams.has('ref')) {
    url.searchParams.delete('ref');
    window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
  }
})();
</script>
@endif
@endsection
