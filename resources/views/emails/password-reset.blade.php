@php($arabic = session('locale') === 'ar')
<!DOCTYPE html>
<html lang="{{ $arabic ? 'ar' : 'en' }}" dir="{{ $arabic ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><title>{{ $arabic ? 'تغيير كلمة السر' : 'Reset your password' }}</title></head>
<body style="background:#f4f4f5;color:#1a1a1a;font-family:Arial,sans-serif">
  <div style="max-width:520px;margin:32px auto;padding:28px;background:white;border-radius:12px">
    <strong>Ramo<span style="color:#e85d26">Store</span></strong>
    <h1 style="font-size:22px">{{ $arabic ? 'تغيير كلمة السر' : 'Reset your password' }}</h1>
    <p>{{ $arabic ? 'استخدم اللينك ده عشان تختار كلمة سر جديدة لحسابك.' : 'Use this link to choose a new password for your account.' }}</p>
    <a href="{{ $url }}" style="display:inline-block;padding:12px 20px;background:#151515;color:white;border-radius:8px;text-decoration:none">{{ $arabic ? 'غيّر كلمة السر' : 'Reset password' }}</a>
    <p style="color:#555">{{ $arabic ? 'اللينك صالح لمدة ساعة. لو ما طلبتش تغيير كلمة السر، تقدر تتجاهل الرسالة.' : 'This link expires in one hour. If you didn’t request a password reset, you can ignore this email.' }}</p>
  </div>
</body>
</html>
