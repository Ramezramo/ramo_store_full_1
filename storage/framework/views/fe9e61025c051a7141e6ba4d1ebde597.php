<?php
  $pageTitle = 'My Profile';
  $hasPlaceholderEmail = str_ends_with($user->email ?? '', '@ramostore.local');
  $displayEmail = $hasPlaceholderEmail ? '' : $user->email;
?>

<?php $__env->startSection('account-content'); ?>
<div class="acc-section-title">Personal Information</div>

<?php if(session('success')): ?>
  <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#15803d;font-size:14px;display:flex;align-items:center;gap:8px">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <?php echo e(session('success')); ?>

  </div>
<?php endif; ?>

<?php if($hasPlaceholderEmail): ?>
  <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <div>
      <div style="font-weight:700;font-size:14px;color:#92400e;margin-bottom:3px">Add your email address</div>
      <div style="font-size:13px;color:#b45309">Your account was created with phone OTP. Adding an email lets you reset your password and receive order updates.</div>
    </div>
  </div>
<?php elseif(!$user->email_verified_at): ?>
  <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap">
    <div style="display:flex;gap:12px;align-items:flex-start">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      <div>
        <div style="font-weight:700;font-size:14px;color:#1e40af;margin-bottom:3px">Verify your email address</div>
        <div style="font-size:13px;color:#1d4ed8"><?php echo e($user->email); ?> — check your inbox for the verification link.</div>
      </div>
    </div>
    <form method="POST" action="<?php echo e(route('email.verify.resend')); ?>" style="flex-shrink:0">
      <?php echo csrf_field(); ?>
      <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;cursor:pointer">
        Resend Link
      </button>
    </form>
  </div>
<?php endif; ?>

<form action="<?php echo e(route('account.profile.update')); ?>" method="POST" class="acc-form">
  <?php echo csrf_field(); ?>

  <div class="acc-form-row">
    <div class="acc-form-group">
      <label class="acc-label">First Name</label>
      <input type="text" name="first_name" value="<?php echo e(old('first_name', $user->first_name)); ?>" class="acc-input <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="First name">
      <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="acc-form-group">
      <label class="acc-label">Last Name</label>
      <input type="text" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>" class="acc-input <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Last name">
      <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
  </div>

  <div class="acc-form-group">
    <label class="acc-label">
      Email Address
      <?php if($hasPlaceholderEmail): ?>
        <span style="font-size:11px;font-weight:500;color:#d97706;background:#fef9c3;padding:2px 7px;border-radius:20px;margin-left:6px">Not set</span>
      <?php endif; ?>
    </label>
    <input type="email" name="email"
           value="<?php echo e(old('email', $displayEmail)); ?>"
           class="acc-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
           placeholder="<?php echo e($hasPlaceholderEmail ? 'Add your email address' : 'your@email.com'); ?>">
    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <?php if($hasPlaceholderEmail): ?>
      <span style="font-size:12px;color:#92400e;margin-top:4px;display:block">Optional — leave blank to skip for now.</span>
    <?php endif; ?>
  </div>

  <div class="acc-form-group">
    <label class="acc-label">Phone Number</label>
    <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" class="acc-input <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="+20 ...">
    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
  </div>

  <hr class="acc-divider">

  <?php $isOtpUser = ($user->registration_method === 'phone_otp'); ?>

  <?php if($isOtpUser): ?>
    <div class="acc-section-title" style="margin-top:0">Set a Password</div>
    <p style="font-size:13px;color:var(--c-mid);margin-bottom:20px">
      Create a password so you can also sign in with your email address in the future.
      Leave blank to keep using phone OTP login only.
    </p>
  <?php else: ?>
    <div class="acc-section-title" style="margin-top:0">Change Password</div>
    <p style="font-size:13px;color:var(--c-mid);margin-bottom:20px">Leave blank to keep your current password.</p>

    <div class="acc-form-group">
      <label class="acc-label">Current Password</label>
      <input type="password" name="current_password" class="acc-input <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter current password">
      <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
  <?php endif; ?>

  <div class="acc-form-row">
    <div class="acc-form-group">
      <label class="acc-label"><?php echo e($isOtpUser ? 'New Password' : 'New Password'); ?></label>
      <input type="password" name="new_password" class="acc-input <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
             placeholder="<?php echo e($isOtpUser ? 'Create a password' : 'New password'); ?>">
      <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="acc-field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="acc-form-group">
      <label class="acc-label">Confirm Password</label>
      <input type="password" name="new_password_confirmation" class="acc-input"
             placeholder="<?php echo e($isOtpUser ? 'Repeat password' : 'Repeat new password'); ?>">
    </div>
  </div>

  <div style="margin-top:28px">
    <button type="submit" class="btn btn-dark" style="padding:12px 32px;font-size:14px">Save Changes</button>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/profile.blade.php ENDPATH**/ ?>