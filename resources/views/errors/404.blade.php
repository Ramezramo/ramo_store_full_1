@php($isAr = session('locale') === 'ar')
<!doctype html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,follow">
  <title>{{ $isAr ? 'الصفحة مش موجودة' : 'Page not found' }} | Ramo Store</title>
  <style>
    :root{color-scheme:light}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at top right,#fff1e9,transparent 42%),#f8fafc;color:#1f2937;font-family:Arial,Tahoma,sans-serif}.error-card{width:min(100%,680px);padding:clamp(28px,7vw,64px);text-align:center;background:#fff;border:1px solid #f0d8c9;border-radius:24px;box-shadow:0 24px 60px rgba(31,41,55,.12)}.brand{font-size:22px;font-weight:900;letter-spacing:-.04em}.brand span,.code{color:#e85d26}.code{margin:26px 0 6px;font-size:clamp(58px,16vw,112px);font-weight:900;line-height:1}.error-card h1{margin:0;font-size:clamp(25px,5vw,36px)}.error-card p{max-width:480px;margin:15px auto 0;color:#667085;line-height:1.75}.actions{display:flex;justify-content:center;flex-wrap:wrap;gap:12px;margin-top:30px}.actions a{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border:1px solid #d0d5dd;border-radius:11px;color:#344054;font-weight:800;text-decoration:none}.actions a.primary{border-color:#e85d26;background:#e85d26;color:#fff}.actions a:hover{transform:translateY(-1px)}
  </style>
</head>
<body>
  <main class="error-card">
    <div class="brand">Ramo<span>Store</span></div>
    <div class="code">404</div>
    <h1>{{ $isAr ? 'الصفحة دي مش موجودة' : 'This page does not exist' }}</h1>
    <p>{{ $isAr ? 'ممكن الرابط يكون اتغيّر أو الصفحة اتنقلت. تقدر ترجع للمتجر وتكمل تسوق بسهولة.' : 'The link may have changed or the page may have moved. Return to the shop to continue browsing.' }}</p>
    <nav class="actions" aria-label="{{ $isAr ? 'خيارات التنقل' : 'Navigation options' }}">
      <a class="primary" href="{{ route('home') }}">{{ $isAr ? 'الصفحة الرئيسية' : 'Home' }}</a>
      <a href="{{ route('shop') }}">{{ $isAr ? 'تسوّق دلوقتي' : 'Shop now' }}</a>
      <a href="{{ route('search') }}">{{ $isAr ? 'دور على منتج' : 'Search products' }}</a>
    </nav>
  </main>
</body>
</html>
