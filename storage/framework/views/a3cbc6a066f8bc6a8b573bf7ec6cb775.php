<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — RamoStore</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f1117;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }
  .card {
    background: #1a1d27;
    border: 1px solid #2a2d3a;
    border-radius: 16px;
    padding: 44px 40px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
  }
  .logo {
    text-align: center;
    margin-bottom: 32px;
  }
  .logo-text {
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.5px;
  }
  .logo-text span { color: #6c63ff; }
  .subtitle {
    font-size: 13px;
    color: #6b7280;
    margin-top: 4px;
  }
  .form-group { margin-bottom: 18px; }
  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #9ca3af;
    margin-bottom: 6px;
  }
  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 12px 14px;
    background: #0f1117;
    border: 1px solid #2a2d3a;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    outline: none;
    transition: border-color .2s;
  }
  input:focus { border-color: #6c63ff; }
  .error-msg {
    background: rgba(239,68,68,.12);
    border: 1px solid rgba(239,68,68,.3);
    color: #f87171;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 18px;
  }
  .field-error {
    color: #f87171;
    font-size: 12px;
    margin-top: 4px;
  }
  .btn {
    width: 100%;
    padding: 13px;
    background: #6c63ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .1s;
    margin-top: 6px;
  }
  .btn:hover { background: #5a52e0; }
  .btn:active { transform: scale(.98); }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
  }
  .back-link:hover { color: #9ca3af; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-text">Ramo<span>Store</span></div>
    <div class="subtitle">Admin Panel Access</div>
  </div>

  <?php if(session('error')): ?>
    <div class="error-msg"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <?php if($errors->has('email')): ?>
    <div class="error-msg"><?php echo e($errors->first('email')); ?></div>
  <?php endif; ?>

  <form method="POST" action="<?php echo e(route('admin.login.post')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" value="<?php echo e(old('email')); ?>"
             placeholder="admin@example.com" autofocus required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn">Sign in to Admin</button>
  </form>

  <a href="<?php echo e(route('home')); ?>" class="back-link">← Back to store</a>
</div>
</body>
</html>
<?php /**PATH /home/runner/workspace/resources/views/admin/login.blade.php ENDPATH**/ ?>