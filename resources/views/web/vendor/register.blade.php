<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Become a Seller — RamoStore</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--orange:#e85d26;--orange2:#d44f1a;--dark:#1a1a1a;--mid:#6b7280;--light:#e5e7eb;--green:#16a34a;--red:#dc2626}
body{font-family:'Inter',system-ui,sans-serif;background:#f7f7f5;color:var(--dark);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:40px 16px 60px}
.logo{font-size:22px;font-weight:800;margin-bottom:8px}
.logo span{color:var(--orange)}
.subtitle{font-size:14px;color:var(--mid);margin-bottom:32px;text-align:center}
.box{background:#fff;border:1px solid var(--light);border-radius:16px;padding:36px 32px;width:100%;max-width:520px}
h1{font-size:20px;font-weight:800;margin-bottom:4px}
.box-sub{font-size:13px;color:var(--mid);margin-bottom:24px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
label{font-size:12px;font-weight:600;color:var(--mid);text-transform:uppercase;letter-spacing:.04em}
input,select,textarea{padding:10px 13px;border:1px solid var(--light);border-radius:8px;font-size:14px;outline:none;transition:.15s;font-family:inherit;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(232,93,38,.08)}
input.err{border-color:var(--red)}
.err-text{font-size:12px;color:var(--red);margin-top:2px}
.btn{width:100%;padding:12px;background:var(--orange);color:#fff;border:none;border-radius:9px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;margin-top:6px}
.btn:hover{background:var(--orange2)}
.login-link{text-align:center;margin-top:18px;font-size:13px;color:var(--mid)}
.login-link a{color:var(--orange);font-weight:600}
.success-box{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:24px;text-align:center;margin-bottom:20px}
.success-box h2{color:var(--green);font-size:18px;margin-bottom:8px}
.success-box p{font-size:13px;color:#166534;line-height:1.6}
.upload-hint{font-size:11px;color:var(--mid);margin-top:3px}
@media(max-width:480px){.form-row{grid-template-columns:1fr}.box{padding:24px 18px}}
</style>
</head>
<body>

<a href="{{ route('home') }}" class="logo">Ramo<span>Store</span></a>
<p class="subtitle">Start selling to thousands of customers today</p>

<div class="box">
  @if(session('registered'))
    <div class="success-box">
      <h2>🎉 Application Received!</h2>
      <p>Thank you for signing up as a seller. Our team will review your application and notify you at your email once it's approved — usually within 24–48 hours.</p>
    </div>
    <div class="login-link"><a href="{{ route('vendor.login') }}">Go to Seller Login</a></div>
  @else
    <h1>Become a Seller</h1>
    <p class="box-sub">Fill in your details to apply for a seller account</p>

    @if($errors->any())
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--red)">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('vendor.register.submit') }}" enctype="multipart/form-data">
      @csrf

      <div class="form-row">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" class="{{ $errors->has('first_name') ? 'err' : '' }}" required placeholder="Ahmed">
          @error('first_name')<span class="err-text">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}" class="{{ $errors->has('last_name') ? 'err' : '' }}" required placeholder="Hassan">
          @error('last_name')<span class="err-text">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-group">
        <label>Shop / Store Name *</label>
        <input type="text" name="shop_name" value="{{ old('shop_name') }}" class="{{ $errors->has('shop_name') ? 'err' : '' }}" required placeholder="e.g. Hassan Electronics">
        @error('shop_name')<span class="err-text">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'err' : '' }}" required placeholder="you@example.com">
        @error('email')<span class="err-text">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" class="{{ $errors->has('phone') ? 'err' : '' }}" required placeholder="+201234567890">
        @error('phone')<span class="err-text">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Shop Address *</label>
        <input type="text" name="shop_address" value="{{ old('shop_address') }}" class="{{ $errors->has('shop_address') ? 'err' : '' }}" required placeholder="123 Cairo Street, Giza, Egypt">
        @error('shop_address')<span class="err-text">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Shop Logo <span style="font-weight:400;text-transform:none">(optional)</span></label>
        <input type="file" name="shop_logo" accept="image/jpeg,image/png,image/webp" class="{{ $errors->has('shop_logo') ? 'err' : '' }}">
        <span class="upload-hint">JPG / PNG / WebP · Max 4 MB</span>
        @error('shop_logo')<span class="err-text">{{ $message }}</span>@enderror
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Password *</label>
          <input type="password" name="password" class="{{ $errors->has('password') ? 'err' : '' }}" required placeholder="Min. 8 characters">
          @error('password')<span class="err-text">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <input type="password" name="password_confirmation" required placeholder="Repeat password">
        </div>
      </div>

      <button type="submit" class="btn">Submit Application →</button>
    </form>

    <div class="login-link">Already a seller? <a href="{{ route('vendor.login') }}">Sign in</a></div>
    <div class="login-link" style="margin-top:6px"><a href="{{ route('home') }}">← Back to store</a></div>
  @endif
</div>

</body>
</html>
