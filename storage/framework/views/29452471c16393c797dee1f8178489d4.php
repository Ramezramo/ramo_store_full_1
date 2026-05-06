<?php $__env->startSection('title', 'Forgot Password — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:420px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Reset your password</h2>
    <p class="auth-sub">Enter your email and we'll send you a reset link.</p>

    <?php if(session('status')): ?>
      <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#15803d;font-size:14px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php echo e(session('status')); ?>

      </div>

      <?php if(session('dev_reset_url')): ?>
        <div style="background:#fffbeb;border:1.5px dashed #f59e0b;border-radius:10px;padding:14px;margin-bottom:16px">
          <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Dev Mode — Reset Link</div>
          <a href="<?php echo e(session('dev_reset_url')); ?>"
             style="display:block;word-break:break-all;font-size:12px;color:#1d4ed8;text-decoration:underline;line-height:1.5">
            <?php echo e(session('dev_reset_url')); ?>

          </a>
          <div style="margin-top:10px">
            <a href="<?php echo e(session('dev_reset_url')); ?>"
               style="display:inline-block;background:#1a1a1a;color:#fff;font-size:13px;font-weight:600;padding:9px 18px;border-radius:8px;text-decoration:none">
              Open Reset Link →
            </a>
          </div>
          <div style="font-size:10px;color:#b45309;margin-top:8px">Not sent via real email · for development only</div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if($errors->any()): ?>
      <div class="alert-box alert-err"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.forgot.send')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
               placeholder="you@example.com"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>
      <button type="submit" class="btn btn-dark"
              style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:4px;font-size:14px">
        Send Reset Link
      </button>
    </form>

    <div style="text-align:center;margin-top:18px">
      <a href="<?php echo e(route('login')); ?>" style="font-size:13px;color:#888">← Back to login</a>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/forgot-password.blade.php ENDPATH**/ ?>