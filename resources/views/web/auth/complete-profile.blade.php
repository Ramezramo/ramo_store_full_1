@extends('layouts.app')
@section('title', (session('locale') === 'ar' ? 'كمّل بياناتك' : 'Complete Profile') . ' — Ramo Store')

@push('styles')
<style>
.profile-phone-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  border-radius: 10px;
  padding: 10px 14px;
  margin-bottom: 20px;
  font-size: 14px;
  color: #15803d;
  font-weight: 600;
}
.profile-phone-badge svg { flex-shrink: 0; }
</style>
@endpush

@section('content')
@php $isAr = session('locale') === 'ar'; @endphp
<div class="page" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="max-width:420px;margin:0 auto;{{ $isAr ? 'text-align:right;font-family:Tahoma,Arial,sans-serif' : '' }}">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">{{ $isAr ? 'فاضلك خطوة!' : 'Almost there!' }}</h2>
    <p class="auth-sub">{{ $isAr ? 'رقم موبايلك اتأكد. كمّل بياناتك عشان تفتح حسابك.' : 'Your phone is verified. Fill in your details to finish.' }}</p>

    @if($errors->any())
      <div class="alert-box alert-err">{{ $errors->first() }}</div>
    @endif

    <div class="profile-phone-badge">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ $isAr ? 'رقم الموبايل اتأكد:' : 'Phone verified:' }} {{ session('otp_temp_phone') }}
    </div>

    <form method="POST" action="{{ route('auth.complete-profile.post') }}">
      @csrf
      <input type="hidden" name="temp_token" value="{{ session('otp_temp_token') }}">

      <div class="form-group">
        <label>{{ $isAr ? 'الاسم بالكامل' : 'Full Name' }} <span style="color:#e53e3e">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" required
               placeholder="{{ $isAr ? 'اكتب اسمك بالكامل' : 'Your full name' }}" autofocus
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <div class="form-group">
        <label>{{ $isAr ? 'الإيميل' : 'Email Address' }} <span style="color:#999;font-weight:400;font-size:12px">{{ $isAr ? '(اختياري)' : '(optional)' }}</span></label>
        <input type="email" name="email" value="{{ old('email') }}"
               placeholder="you@example.com"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <button type="submit" class="btn btn-dark"
        style="width:100%;justify-content:center;border-radius:10px;padding:14px;margin-top:8px;font-size:14px">
        {{ $isAr ? 'اعمل حساب' : 'Create Account' }}
      </button>
    </form>

    <div style="text-align:center;margin-top:16px">
      <a href="{{ route('login') }}" style="color:#999;font-size:13px">{{ $isAr ? 'ارجع لتسجيل الدخول →' : '← Back to login' }}</a>
    </div>
  </div>
</div>
@endsection
