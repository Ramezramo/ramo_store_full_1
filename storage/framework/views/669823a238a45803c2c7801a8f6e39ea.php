<?php $__env->startSection('title', 'Sign In — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.auth-methods { display:flex; flex-direction:column; gap:12px; }
.method-divider { display:flex; align-items:center; gap:10px; color:#999; font-size:13px; margin:4px 0; }
.method-divider::before,.method-divider::after { content:''; flex:1; height:1px; background:#e5e7eb; }
.otp-phone-row { display:flex; gap:8px; }
.otp-phone-row .country-prefix { display:flex; align-items:center; gap:6px; background:#f9f9f9; border:1.5px solid #e5e7eb; border-radius:10px; padding:0 12px; font-size:14px; font-weight:600; white-space:nowrap; color:#333; }
.otp-phone-row input { flex:1; }
.google-btn { display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:13px; border-radius:10px; border:1.5px solid #e5e7eb; background:#fff; color:#333; font-size:14px; font-weight:600; cursor:pointer; transition:.15s; text-decoration:none; }
.google-btn:hover { border-color:#aaa; background:#fafafa; }
.google-btn svg { width:20px; height:20px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:420px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>

    <?php
      $intendedUrl   = session('url.intended', '');
      $fromCheckout  = str_contains($intendedUrl, '/checkout');
      $guestAllowed  = $authConfig['guest_checkout'] ?? false;
    ?>

    <?php if($fromCheckout): ?>
      <h2 class="auth-title">Sign in to continue</h2>
      <p class="auth-sub">Sign in to your account to complete your order</p>
    <?php else: ?>
      <h2 class="auth-title">Welcome back</h2>
      <p class="auth-sub">Sign in to your account</p>
    <?php endif; ?>

    <?php if(session('status')): ?>
      <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#15803d;font-size:14px">
        <?php echo e(session('status')); ?>

      </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
      <div class="alert-box alert-err"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="auth-methods">
      <?php if($authConfig['phone_otp_login'] ?? false): ?>
      <div>
        <div class="otp-phone-row">
          <div class="country-prefix">🇪🇬 +20</div>
          <input type="tel" id="otp-phone" placeholder="01xxxxxxxxx" maxlength="11" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;outline:none;width:100%;transition:.15s" oninput="this.value=this.value.replace(/[^0-9]/,'')">
        </div>
        <button onclick="sendOtp()" id="send-otp-btn" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:10px;font-size:14px">Send OTP Code</button>
        <div id="otp-msg" style="font-size:12px;margin-top:6px;text-align:center;color:#888"></div>
      </div>
      <?php endif; ?>

      <?php if(($authConfig['phone_otp_login'] ?? false) && (($authConfig['google_login'] ?? false) || ($authConfig['email_login'] ?? true))): ?>
      <div class="method-divider">or</div>
      <?php endif; ?>

      <?php if($authConfig['google_login'] ?? false): ?>
      <a href="<?php echo e(route('auth.google')); ?>" class="google-btn">
        <svg viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
        Continue with Google
      </a>
      <?php endif; ?>

      <?php if(($authConfig['email_login'] ?? true) && ($authConfig['google_login'] ?? false)): ?>
      <div class="method-divider">or</div>
      <?php endif; ?>

      <?php if($authConfig['email_login'] ?? true): ?>
      <form method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </div>
        <div class="form-check-row" style="display:flex;align-items:center;justify-content:space-between">
          <label class="form-check"><input type="checkbox" name="remember"> Remember me</label>
          <a href="<?php echo e(route('password.forgot')); ?>" style="font-size:13px;color:#666;text-decoration:none">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:13px">Sign In</button>
      </form>
      <?php endif; ?>

      <?php if($fromCheckout && $guestAllowed): ?>
      <div class="method-divider">or</div>
      <a href="<?php echo e(route('checkout')); ?>?guest=1"
         style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:13px;border-radius:10px;border:1.5px dashed #d1d5db;background:#fafafa;color:#555;font-size:14px;font-weight:600;text-decoration:none;transition:.15s"
         onmouseover="this.style.borderColor='#9ca3af';this.style.background='#f3f4f6'"
         onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fafafa'">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Continue as Guest
      </a>
      <?php endif; ?>
    </div>

    <?php if(!$fromCheckout): ?>
    <p style="text-align:center;font-size:13px;color:#888;margin-top:18px">
      Don't have an account? <a href="<?php echo e(route('register')); ?>" style="color:#1a1a1a;font-weight:600">Sign up</a>
    </p>
    <?php endif; ?>
  </div>
</div>
<script>
async function sendOtp() {
  const phoneInput = document.getElementById('otp-phone');
  const btn = document.getElementById('send-otp-btn');
  const msg = document.getElementById('otp-msg');
  const rawPhone = phoneInput.value.trim();
  if (!rawPhone || rawPhone.length < 9) {
    msg.style.color = '#e53e3e';
    msg.textContent = 'Please enter a valid phone number.';
    return;
  }
  btn.disabled = true;
  btn.textContent = 'Sending...';
  try {
    const resp = await fetch('/auth/send-otp', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ phone: rawPhone }) });
    const contentType = resp.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await resp.json() : { success: false, message: await resp.text() };
    if (data.success) {
      sessionStorage.setItem('otp_phone', rawPhone);
      if (data.dev_otp) sessionStorage.setItem('dev_otp', data.dev_otp);
      window.location.href = '/auth/otp-verify';
    } else {
      msg.style.color = '#e53e3e';
      msg.textContent = data.message || 'Failed to send OTP.';
      btn.disabled = false;
      btn.textContent = 'Send OTP Code';
    }
  } catch (e) {
    msg.style.color = '#e53e3e';
    msg.textContent = e?.message || 'Network error. Please try again.';
    btn.disabled = false;
    btn.textContent = 'Send OTP Code';
  }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/login.blade.php ENDPATH**/ ?>