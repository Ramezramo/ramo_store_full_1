<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify your email — Ramo Store</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif">
  <div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
    <div style="background:#1a1a1a;padding:28px 32px">
      <span style="font-size:22px;font-weight:800;color:#fff">Ramo<span style="color:#f97316">Store</span></span>
    </div>
    <div style="padding:32px">
      <h1 style="margin:0 0 8px;font-size:22px;color:#1a1a1a">Verify your email address</h1>
      <p style="color:#555;font-size:15px;margin:0 0 24px;line-height:1.6">
        Hi {{ $user->name ?? 'there' }}, thanks for signing up! Click the button below to confirm your email address and activate your account.
      </p>
      <a href="{{ $url }}"
         style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 28px;border-radius:10px">
        Verify Email Address
      </a>
      <p style="color:#999;font-size:12px;margin:24px 0 0;line-height:1.6">
        This link expires in 60 minutes. If you didn't create an account, you can safely ignore this email.
      </p>
      <p style="color:#bbb;font-size:11px;margin:16px 0 0">
        Or copy this URL: <a href="{{ $url }}" style="color:#1d4ed8;word-break:break-all">{{ $url }}</a>
      </p>
    </div>
  </div>
</body>
</html>
