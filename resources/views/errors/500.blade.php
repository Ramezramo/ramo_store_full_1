@php($isAr = session('locale') === 'ar')
<!doctype html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow">
  <title>{{ $isAr ? 'حصلت مشكلة مؤقتة' : 'Temporary problem' }} | Ramo Store</title>
  <style>
    :root{color-scheme:light}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at top right,#fff1e9,transparent 42%),#f8fafc;color:#1f2937;font-family:Arial,Tahoma,sans-serif}.error-card{width:min(100%,680px);padding:clamp(28px,7vw,64px);text-align:center;background:#fff;border:1px solid #f0d8c9;border-radius:24px;box-shadow:0 24px 60px rgba(31,41,55,.12)}.brand{font-size:22px;font-weight:900;letter-spacing:-.04em}.brand span,.code{color:#e85d26}.code{margin:26px 0 6px;font-size:clamp(58px,16vw,112px);font-weight:900;line-height:1}.error-card h1{margin:0;font-size:clamp(25px,5vw,36px)}.error-card p{max-width:480px;margin:15px auto 0;color:#667085;line-height:1.75}.actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-top:30px}.actions a{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border:1px solid #d0d5dd;border-radius:11px;color:#344054;font-weight:800;text-decoration:none}.actions a.primary{border-color:#e85d26;background:#e85d26;color:#fff}.actions a:hover{transform:translateY(-1px)}
  </style>
</head>
<body>
  <main class="error-card">
    <div class="brand">Ramo<span>Store</span></div>
    <div class="code">500</div>
    <h1>{{ $isAr ? 'حصلت مشكلة مؤقتة' : 'Something went wrong' }}</h1>
    <p>{{ $isAr ? 'بنحاول نصلّح المشكلة. جرّب تفتح الصفحة تاني أو ارجع للتسوق بعد دقيقة.' : 'We are working to fix the problem. Please try the page again, or return to shopping in a moment.' }}</p>
    <nav class="actions" aria-label="{{ $isAr ? 'خيارات التنقل' : 'Navigation options' }}">
      <a class="primary" href="{{ route('home') }}">{{ $isAr ? 'الصفحة الرئيسية' : 'Home' }}</a>
      <a href="{{ route('shop') }}">{{ $isAr ? 'تسوّق دلوقتي' : 'Shop now' }}</a>
    </nav>
  </main>
</body>
</html>
