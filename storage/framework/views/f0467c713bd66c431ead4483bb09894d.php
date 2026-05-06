<?php $__env->startSection('title', 'Complete Profile — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.profile-phone-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  border-radius: 10px;
  padding: 10px 14px;
  margin-bottom: 20px;
  font-size: 14px;
  color: #15803d;
  font-weight: 600;
}
.profile-phone-badge svg { flex-shrink: 0; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:420px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Almost there!</h2>
    <p class="auth-sub">Your phone is verified. Fill in your details to finish.</p>

    <?php if($errors->any()): ?>
      <div class="alert-box alert-err"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="profile-phone-badge">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      Phone verified: <?php echo e(session('otp_temp_phone')); ?>

    </div>

    <form method="POST" action="<?php echo e(route('auth.complete-profile.post')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="temp_token" value="<?php echo e(session('otp_temp_token')); ?>">

      <div class="form-group">
        <label>Full Name <span style="color:#e53e3e">*</span></label>
        <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
               placeholder="Your full name" autofocus
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <div class="form-group">
        <label>Email Address <span style="color:#999;font-weight:400;font-size:12px">(optional)</span></label>
        <input type="email" name="email" value="<?php echo e(old('email')); ?>"
               placeholder="you@example.com"
               style="border-radius:10px;border:1.5px solid #e5e7eb;padding:13px 14px;font-size:14px;width:100%;outline:none">
      </div>

      <button type="submit" class="btn btn-dark"
        style="width:100%;justify-content:center;border-radius:10px;padding:14px;margin-top:8px;font-size:14px">
        Create Account
      </button>
    </form>

    <div style="text-align:center;margin-top:16px">
      <a href="<?php echo e(route('login')); ?>" style="color:#999;font-size:13px">← Back to login</a>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/complete-profile.blade.php ENDPATH**/ ?>