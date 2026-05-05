@extends('layouts.app')
@section('title', 'Verify Your Email — Ramo Store')

@section('content')
<div class="page" style="max-width:460px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <div style="text-align:center;margin-bottom:6px">
      <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#f0fdf4;border-radius:50%;margin-bottom:12px">
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </div>
    </div>
    @if(session('registered'))
      <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#15803d;font-size:13px;display:flex;align-items:center;gap:8px;text-align:left">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Account created! One last step — verify your email.
      </div>
    @endif

    <h2 class="auth-title" style="text-align:center">Check your inbox</h2>
    <p class="auth-sub" style="text-align:center">
      We sent a verification link to<br>
      <strong style="color:#1a1a1a">{{ $email }}</strong>
    </p>

    @if(session('expired'))
      <div style="background:#fef3c7;border:1.5px solid #fcd34d;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#92400e;font-size:13px">
        Your previous link has expired. Click below to get a new one.
      </div>
    @endif

    @if(session('resend_error'))
      <div style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:13px">
        {{ session('resend_error') }}
      </div>
    @endif

    @if(session('resent'))
      <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#15803d;font-size:13px;display:flex;align-items:center;gap:8px">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Verification link resent!
      </div>
    @endif

    @if(session('dev_url'))
      <div style="background:#fffbeb;border:1.5px dashed #f59e0b;border-radius:10px;padding:14px;margin-bottom:16px">
        <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Dev Mode — Verification Link</div>
        <a href="{{ session('dev_url') }}"
           style="display:block;word-break:break-all;font-size:12px;color:#1d4ed8;text-decoration:underline;line-height:1.5">
          {{ session('dev_url') }}
        </a>
        <div style="margin-top:10px">
          <a href="{{ session('dev_url') }}"
             style="display:inline-block;background:#1a1a1a;color:#fff;font-size:13px;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none">
            Verify Email →
          </a>
        </div>
        <div style="font-size:10px;color:#b45309;margin-top:8px">Not sent via real email · for development only</div>
      </div>
    @endif

    <div style="background:#f9fafb;border-radius:12px;padding:16px 18px;margin-bottom:20px;font-size:13px;color:#555;line-height:1.6">
      <strong style="color:#1a1a1a;display:block;margin-bottom:4px">Didn't get it?</strong>
      Check your spam or junk folder, or resend the link below.
    </div>

    <form method="POST" action="{{ route('email.verify.resend') }}">
      @csrf
      <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:13px;font-size:14px">
        Resend Verification Link
      </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:12px">
      @csrf
      <button type="submit" style="width:100%;background:none;border:none;color:#888;font-size:13px;cursor:pointer;padding:8px">
        Sign out and use a different account
      </button>
    </form>
  </div>
</div>
@endsection
