<?php $__env->startSection('title', 'Set New Password — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:420px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Set new password</h2>
    <p class="auth-sub">Choose a strong password for your account.</p>

    <?php if($errors->any()): ?>
      <div class="alert-box alert-err"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.reset')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="token" value="<?php echo e($token); ?>">
      <input type="hidden" name="email" value="<?php echo e($email); ?>">

      <div class="form-group" style="margin-bottom:6px">
        <label style="font-size:13px;font-weight:600;color:#555;margin-bottom:6px;display:block">Email</label>
        <div style="font-size:14px;color:#333;background:#f9f9f9;border:1.5px solid #e5e7eb;border-radius:10px;padding:12px 14px">
          <?php echo e($email); ?>

        </div>
      </div>

      <div class="form-group" style="margin-top:16px">
        <label>New Password</label>
        <input type="password" name="password" required autofocus
               placeholder="Min. 8 characters"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="font-size:12px;color:#e53e3e;margin-top:4px;display:block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div class="form-group">
        <label>Confirm New Password</label>
        <input type="password" name="password_confirmation" required
               placeholder="Repeat your password"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <button type="submit" class="btn btn-dark"
              style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:8px;font-size:14px">
        Reset Password
      </button>
    </form>

    <div style="text-align:center;margin-top:18px">
      <a href="<?php echo e(route('login')); ?>" style="font-size:13px;color:#888">← Back to login</a>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/reset-password.blade.php ENDPATH**/ ?>