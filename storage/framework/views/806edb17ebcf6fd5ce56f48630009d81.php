<?php $__env->startSection('title', 'Create Account — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:480px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title">Create an account</h2>
    <p class="auth-sub">Join RamoStore for a better shopping experience</p>

    <?php if($errors->any()): ?>
      <div class="alert-box alert-err"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>
    <?php if(session('success')): ?>
      <div class="alert-box alert-ok"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('register')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-grid-2">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" required>
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" value="<?php echo e(old('email')); ?>" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Phone Number *</label>
        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" required placeholder="01xxxxxxxxx">
      </div>
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" required placeholder="Min 6 characters">
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="password_confirmation" required placeholder="Repeat password">
      </div>
      <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center;border-radius:10px;padding:14px">Create Account</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in →</a>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/register.blade.php ENDPATH**/ ?>