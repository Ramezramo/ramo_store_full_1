@extends('layouts.app')
@section('title', 'Create Account — Ramo Store')

@section('content')
<div class="page" style="max-width:480px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Create an account</h2>
    <p class="auth-sub">Join RamoStore for a better shopping experience</p>

    @if($errors->any())
      <div class="alert-box alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
      <div class="alert-box alert-ok">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
      @csrf
      <div class="form-grid-2">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" required>
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Phone Number *</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx">
      </div>
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" required placeholder="Min 6 characters">
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="password_confirmation" required placeholder="Repeat password">
      </div>
      <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:14px">Create Account</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="{{ route('login') }}">Sign in →</a>
    </div>
  </div>
</div>
@endsection
