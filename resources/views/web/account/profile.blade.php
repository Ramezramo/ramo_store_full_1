@extends('web.account.layout')
@php
  $hasPlaceholderEmail = str_ends_with($user->email ?? '', '@ramostore.local');
  $displayEmail = $hasPlaceholderEmail ? '' : $user->email;
  $isAr = session('locale') === 'ar';
  $pageTitle = $isAr ? 'بياناتي' : 'My Profile';
@endphp

@section('account-content')
<div class="acc-section-title">{{ $isAr ? 'بياناتي الشخصية' : 'Personal Information' }}</div>

@if(session('success'))
  <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#15803d;font-size:14px;display:flex;align-items:center;gap:8px">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
  </div>
@endif

@if($hasPlaceholderEmail)
  <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <div>
      <div style="font-weight:700;font-size:14px;color:#92400e;margin-bottom:3px">{{ $isAr ? 'ضيف إيميلك' : 'Add your email address' }}</div>
      <div style="font-size:13px;color:#b45309">{{ $isAr ? 'حسابك اتعمل بتأكيد الموبايل. إضافة إيميل بتساعدك تغيّر كلمة السر ويوصلك تحديثات الطلب.' : 'Your account was created with phone OTP. Adding an email lets you reset your password and receive order updates.' }}</div>
    </div>
  </div>
@elseif(!$user->email_verified_at)
  <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap">
    <div style="display:flex;gap:12px;align-items:flex-start">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      <div>
        <div style="font-weight:700;font-size:14px;color:#1e40af;margin-bottom:3px">{{ $isAr ? 'أكد إيميلك' : 'Verify your email address' }}</div>
        <div style="font-size:13px;color:#1d4ed8">{{ $user->email }} — {{ $isAr ? 'بص في إيميلك عشان لينك التأكيد.' : 'check your inbox for the verification link.' }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('email.verify.resend') }}" style="flex-shrink:0">
      @csrf
      <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;cursor:pointer">
        {{ $isAr ? 'ابعت اللينك تاني' : 'Resend Link' }}
      </button>
    </form>
  </div>
@endif

<form action="{{ route('account.profile.update') }}" method="POST" class="acc-form">
  @csrf

  <div class="acc-form-row">
    <div class="acc-form-group">
      <label class="acc-label">{{ $isAr ? 'الاسم الأول' : 'First Name' }}</label>
      <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="acc-input @error('first_name') error @enderror" placeholder="{{ $isAr ? 'الاسم الأول' : 'First name' }}">
      @error('first_name')<span class="acc-field-error">{{ $message }}</span>@enderror
    </div>
    <div class="acc-form-group">
      <label class="acc-label">{{ $isAr ? 'اسم العيلة' : 'Last Name' }}</label>
      <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="acc-input @error('last_name') error @enderror" placeholder="{{ $isAr ? 'اسم العيلة' : 'Last name' }}">
      @error('last_name')<span class="acc-field-error">{{ $message }}</span>@enderror
    </div>
  </div>

  <div class="acc-form-group">
    <label class="acc-label">
      {{ $isAr ? 'الإيميل' : 'Email Address' }}
      @if($hasPlaceholderEmail)
        <span style="font-size:11px;font-weight:500;color:#d97706;background:#fef9c3;padding:2px 7px;border-radius:20px;margin-left:6px">{{ $isAr ? 'مش مضاف' : 'Not set' }}</span>
      @endif
    </label>
    <input type="email" name="email"
           value="{{ old('email', $displayEmail) }}"
           class="acc-input @error('email') error @enderror"
           placeholder="{{ $hasPlaceholderEmail ? ($isAr ? 'ضيف إيميلك' : 'Add your email address') : 'your@email.com' }}">
    @error('email')<span class="acc-field-error">{{ $message }}</span>@enderror
    @if($hasPlaceholderEmail)
      <span style="font-size:12px;color:#92400e;margin-top:4px;display:block">{{ $isAr ? 'اختياري — سيبه فاضي لو مش عايز تضيفه دلوقتي.' : 'Optional — leave blank to skip for now.' }}</span>
    @endif
  </div>

  <div class="acc-form-group">
    <label class="acc-label">{{ $isAr ? 'رقم الموبايل' : 'Phone Number' }}</label>
    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="acc-input @error('phone') error @enderror" placeholder="+20 ...">
    @error('phone')<span class="acc-field-error">{{ $message }}</span>@enderror
  </div>

  <hr class="acc-divider">

  @php $isOtpUser = ($user->registration_method === 'phone_otp'); @endphp

  @if($isOtpUser)
    <div class="acc-section-title" style="margin-top:0">{{ $isAr ? 'عيّن كلمة سر' : 'Set a Password' }}</div>
    <p style="font-size:13px;color:var(--c-mid);margin-bottom:20px">
      {{ $isAr ? 'اعمل كلمة سر عشان تقدر تسجّل دخول بإيميلك بعد كده. سيبها فاضية لو هتستخدم تسجيل الموبايل بس.' : 'Create a password so you can also sign in with your email address in the future. Leave blank to keep using phone OTP login only.' }}
    </p>
  @else
    <div class="acc-section-title" style="margin-top:0">{{ $isAr ? 'غيّر كلمة السر' : 'Change Password' }}</div>
    <p style="font-size:13px;color:var(--c-mid);margin-bottom:20px">{{ $isAr ? 'سيبها فاضية لو عايز تفضل على كلمة السر الحالية.' : 'Leave blank to keep your current password.' }}</p>

    <div class="acc-form-group">
      <label class="acc-label">{{ $isAr ? 'كلمة السر الحالية' : 'Current Password' }}</label>
      <input type="password" name="current_password" class="acc-input @error('current_password') error @enderror" placeholder="{{ $isAr ? 'اكتب كلمة السر الحالية' : 'Enter current password' }}">
      @error('current_password')<span class="acc-field-error">{{ $message }}</span>@enderror
    </div>
  @endif

  <div class="acc-form-row">
    <div class="acc-form-group">
      <label class="acc-label">{{ $isAr ? 'كلمة السر الجديدة' : 'New Password' }}</label>
      <input type="password" name="new_password" class="acc-input @error('new_password') error @enderror"
             placeholder="{{ $isAr ? 'اعمل كلمة سر' : ($isOtpUser ? 'Create a password' : 'New password') }}">
      @error('new_password')<span class="acc-field-error">{{ $message }}</span>@enderror
    </div>
    <div class="acc-form-group">
      <label class="acc-label">{{ $isAr ? 'أكد كلمة السر' : 'Confirm Password' }}</label>
      <input type="password" name="new_password_confirmation" class="acc-input"
             placeholder="{{ $isAr ? 'اكتب كلمة السر تاني' : ($isOtpUser ? 'Repeat password' : 'Repeat new password') }}">
    </div>
  </div>

  <div style="margin-top:28px">
    <button type="submit" class="btn btn-dark" style="padding:12px 32px;font-size:14px">{{ $isAr ? 'احفظ التعديلات' : 'Save Changes' }}</button>
  </div>
</form>
@endsection
