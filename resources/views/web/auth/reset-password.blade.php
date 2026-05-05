@extends('layouts.app')
@section('title', 'Set New Password — Ramo Store')

@section('content')
<div class="page" style="max-width:420px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Set new password</h2>
    <p class="auth-sub">Choose a strong password for your account.</p>

    @if($errors->any())
      <div class="alert-box alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div class="form-group" style="margin-bottom:6px">
        <label style="font-size:13px;font-weight:600;color:#555;margin-bottom:6px;display:block">Email</label>
        <div style="font-size:14px;color:#333;background:#f9f9f9;border:1.5px solid #e5e7eb;border-radius:10px;padding:12px 14px">
          {{ $email }}
        </div>
      </div>

      <div class="form-group" style="margin-top:16px">
        <label>New Password</label>
        <input type="password" name="password" required autofocus
               placeholder="Min. 8 characters"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
        @error('password')<span style="font-size:12px;color:#e53e3e;margin-top:4px;display:block">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Confirm New Password</label>
        <input type="password" name="password_confirmation" required
               placeholder="Repeat your password"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <button type="submit" class="btn btn-dark"
              style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:8px;font-size:14px">
        Reset Password
      </button>
    </form>

    <div style="text-align:center;margin-top:18px">
      <a href="{{ route('login') }}" style="font-size:13px;color:#888">← Back to login</a>
    </div>
  </div>
</div>
@endsection
