@extends('layouts.app')
@section('title', (session('locale') === 'ar' ? 'تعيين كلمة سر جديدة' : 'Set New Password') . ' — Ramo Store')

@section('content')
@php $isAr = session('locale') === 'ar'; @endphp
<div class="page" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="max-width:420px;margin:0 auto;{{ $isAr ? 'text-align:right;font-family:Tahoma,Arial,sans-serif' : '' }}">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">{{ $isAr ? 'عيّن كلمة سر جديدة' : 'Set new password' }}</h2>
    <p class="auth-sub">{{ $isAr ? 'اختار كلمة سر قوية لحسابك.' : 'Choose a strong password for your account.' }}</p>

    @if($errors->any())
      <div class="alert-box alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div class="form-group" style="margin-bottom:6px">
        <label style="font-size:13px;font-weight:600;color:#555;margin-bottom:6px;display:block">{{ $isAr ? 'الإيميل' : 'Email' }}</label>
        <div style="font-size:14px;color:#333;background:#f9f9f9;border:1.5px solid #e5e7eb;border-radius:10px;padding:12px 14px">
          {{ $email }}
        </div>
      </div>

      <div class="form-group" style="margin-top:16px">
        <label>{{ $isAr ? 'كلمة السر الجديدة' : 'New Password' }}</label>
        <input type="password" name="password" required autofocus
               placeholder="{{ $isAr ? '8 حروف أو أرقام على الأقل' : 'Min. 8 characters' }}"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
        @error('password')<span style="font-size:12px;color:#e53e3e;margin-top:4px;display:block">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>{{ $isAr ? 'أكد كلمة السر الجديدة' : 'Confirm New Password' }}</label>
        <input type="password" name="password_confirmation" required
               placeholder="{{ $isAr ? 'اكتب كلمة السر تاني' : 'Repeat your password' }}"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <button type="submit" class="btn btn-dark"
              style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:8px;font-size:14px">
        {{ $isAr ? 'غيّر كلمة السر' : 'Reset Password' }}
      </button>
    </form>

    <div style="text-align:center;margin-top:18px">
      <a href="{{ route('login') }}" style="font-size:13px;color:#888">{{ $isAr ? 'ارجع لتسجيل الدخول →' : '← Back to login' }}</a>
    </div>
  </div>
</div>
@endsection
