<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Seller Login — RamoStore</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--orange:#e85d26;--orange2:#d44f1a;--dark:#1a1a1a;--mid:#6b7280;--light:#e5e7eb;--red:#dc2626;--yellow:#92400e}
body{font-family:'Inter',system-ui,sans-serif;background:#f7f7f5;color:var(--dark);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px}
.logo{font-size:22px;font-weight:800;margin-bottom:6px}
.logo span{color:var(--orange)}
.subtitle{font-size:13px;color:var(--mid);margin-bottom:32px}
.box{background:#fff;border:1px solid var(--light);border-radius:16px;padding:36px 32px;width:100%;max-width:400px}
h1{font-size:20px;font-weight:800;margin-bottom:4px}
.box-sub{font-size:13px;color:var(--mid);margin-bottom:24px}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
label{font-size:12px;font-weight:600;color:var(--mid);text-transform:uppercase;letter-spacing:.04em}
input{padding:10px 13px;border:1px solid var(--light);border-radius:8px;font-size:14px;outline:none;transition:.15s;font-family:inherit;width:100%}
input:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(232,93,38,.08)}
input.err{border-color:var(--red)}
.err-text{font-size:12px;color:var(--red);margin-top:2px}
.remember-row{display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:13px;color:var(--mid)}
.btn{width:100%;padding:12px;background:var(--orange);color:#fff;border:none;border-radius:9px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.btn:hover{background:var(--orange2)}
.links{text-align:center;margin-top:18px;font-size:13px;color:var(--mid);display:flex;flex-direction:column;gap:6px}
.links a{color:var(--orange);font-weight:600}
.warn-box{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--yellow)}
.err-box{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--red)}
@media(max-width:420px){.box{padding:24px 18px}}
</style>
</head>
<body>

<a href="<?php echo e(route('home')); ?>" class="logo">Ramo<span>Store</span></a>
<p class="subtitle">Seller Portal</p>

<div class="box">
  <h1>Seller Sign In</h1>
  <p class="box-sub">Access your seller dashboard</p>

  <?php if(session('error')): ?>
    <div class="warn-box"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <?php if($errors->has('email')): ?>
    <div class="<?php echo e(str_contains($errors->first('email'), 'review') ? 'warn-box' : 'err-box'); ?>">
      <?php echo e($errors->first('email')); ?>

    </div>
  <?php endif; ?>
  <?php if($errors->has('password')): ?>
    <div class="err-box"><?php echo e($errors->first('password')); ?></div>
  <?php endif; ?>

  <form method="POST" action="<?php echo e(route('vendor.login.submit')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="<?php echo e($errors->has('email') ? 'err' : ''); ?>" required autofocus placeholder="your@email.com">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="<?php echo e($errors->has('password') ? 'err' : ''); ?>" required placeholder="••••••••">
    </div>
    <div class="remember-row">
      <input type="checkbox" name="remember" id="remember" style="width:auto">
      <label for="remember" style="font-size:13px;text-transform:none;letter-spacing:0;color:var(--mid)">Keep me signed in</label>
    </div>
    <button type="submit" class="btn">Sign In →</button>
  </form>

  <div class="links">
    <div>New seller? <a href="<?php echo e(route('vendor.register')); ?>">Apply now</a></div>
    <div><a href="<?php echo e(route('home')); ?>">← Back to store</a></div>
  </div>
</div>

</body>
</html>
<?php /**PATH /home/runner/workspace/resources/views/web/vendor/login.blade.php ENDPATH**/ ?>