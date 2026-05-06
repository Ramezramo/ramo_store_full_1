<?php $__env->startSection('title', 'Auth Settings'); ?>
<?php $__env->startSection('page-title', 'Auth Settings'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.settings-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; }
@media(max-width:1100px){ .settings-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:700px){ .settings-grid { grid-template-columns:1fr; } }

.setting-section { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; }
.setting-section-title {
  font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
  color:var(--muted); margin-bottom:16px; display:flex; align-items:center; gap:8px;
}
.setting-section-title svg { width:14px; height:14px; }

.toggle-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid rgba(255,255,255,.04); }
.toggle-row:last-child { border-bottom:none; padding-bottom:0; }
.toggle-label { font-size:13px; font-weight:500; }
.toggle-sub { font-size:11px; color:var(--muted); margin-top:2px; }

.toggle-switch { position:relative; width:40px; height:22px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
  position:absolute; inset:0; background:#374151; border-radius:22px;
  cursor:pointer; transition:.2s;
}
.toggle-slider::before {
  content:''; position:absolute; width:16px; height:16px; left:3px; top:3px;
  background:#fff; border-radius:50%; transition:.2s;
}
.toggle-switch input:checked + .toggle-slider { background:var(--accent); }
.toggle-switch input:checked + .toggle-slider::before { transform:translateX(18px); }

.num-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid rgba(255,255,255,.04); gap:10px; }
.num-row:last-child { border-bottom:none; }
.num-input {
  width:70px; text-align:center; border-radius:7px;
  border:1px solid var(--border); background:var(--bg);
  color:var(--text); padding:5px 8px; font-size:13px; font-weight:600;
}
.num-input:focus { border-color:var(--accent); outline:none; }

.save-bar {
  position:sticky; bottom:0; left:0; right:0;
  background:var(--sidebar); border-top:1px solid var(--border);
  padding:14px 28px; display:flex; align-items:center; justify-content:space-between;
  z-index:10; margin: 24px -28px -28px;
}
.save-status { font-size:13px; color:var(--muted); }
.save-status.ok  { color:var(--green); }
.save-status.err { color:var(--red); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<form id="auth-settings-form">
<?php echo csrf_field(); ?>

<div class="settings-grid">

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      Login Methods
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Email &amp; Password</div><div class="toggle-sub">Classic email/password login</div></div>
      <label class="toggle-switch"><input type="checkbox" name="email_login" <?php echo e(($config['email_login'] ?? true) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Google Login</div><div class="toggle-sub">OAuth via Google account</div></div>
      <label class="toggle-switch"><input type="checkbox" name="google_login" <?php echo e(($config['google_login'] ?? false) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Phone OTP</div><div class="toggle-sub">SMS one-time password login</div></div>
      <label class="toggle-switch"><input type="checkbox" name="phone_otp_login" <?php echo e(($config['phone_otp_login'] ?? false) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

  </div>

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Checkout
    </div>

    <div class="toggle-row">
      <div>
        <div class="toggle-label">Guest Checkout</div>
        <div class="toggle-sub">Allow customers to order without creating an account</div>
        <div style="margin-top:6px;font-size:11px;background:rgba(245,158,11,.12);color:#b45309;border-radius:6px;padding:4px 8px;display:inline-block">
          When OFF → customers must login before checkout
        </div>
      </div>
      <label class="toggle-switch"><input type="checkbox" name="guest_checkout" <?php echo e(($config['guest_checkout'] ?? false) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>
  </div>

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      Registration
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Auto-register via Google</div><div class="toggle-sub">Create account on first Google login</div></div>
      <label class="toggle-switch"><input type="checkbox" name="auto_register_google" <?php echo e(($config['auto_register_google'] ?? true) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Auto-register via OTP</div><div class="toggle-sub">Create account on first OTP verify</div></div>
      <label class="toggle-switch"><input type="checkbox" name="auto_register_otp" <?php echo e(($config['auto_register_otp'] ?? true) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Require Name</div><div class="toggle-sub">Name field mandatory on signup</div></div>
      <label class="toggle-switch"><input type="checkbox" name="require_name_on_register" <?php echo e(($config['require_name_on_register'] ?? true) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>

    <div class="toggle-row">
      <div><div class="toggle-label">Require Email</div><div class="toggle-sub">Email mandatory on OTP signup</div></div>
      <label class="toggle-switch"><input type="checkbox" name="require_email_on_register" <?php echo e(($config['require_email_on_register'] ?? false) ? 'checked' : ''); ?>><span class="toggle-slider"></span></label>
    </div>
  </div>

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
      OTP Configuration
    </div>

    <div class="num-row"><div><div class="toggle-label">OTP Length</div><div class="toggle-sub">Number of digits (4–8)</div></div><input type="number" name="otp_length" class="num-input" min="4" max="8" value="<?php echo e($config['otp_length'] ?? 6); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">OTP Expiry</div><div class="toggle-sub">Minutes before code expires</div></div><input type="number" name="otp_expiry_minutes" class="num-input" min="1" max="30" value="<?php echo e($config['otp_expiry_minutes'] ?? 5); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">Max Attempts</div><div class="toggle-sub">Wrong guesses before block</div></div><input type="number" name="max_otp_attempts" class="num-input" min="1" max="10" value="<?php echo e($config['max_otp_attempts'] ?? 3); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">Resend Cooldown</div><div class="toggle-sub">Seconds between resend requests</div></div><input type="number" name="resend_cooldown_seconds" class="num-input" min="10" max="300" value="<?php echo e($config['resend_cooldown_seconds'] ?? 60); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">Max Resends/Hour</div><div class="toggle-sub">OTP requests per phone per hour</div></div><input type="number" name="max_resends_per_hour" class="num-input" min="1" max="20" value="<?php echo e($config['max_resends_per_hour'] ?? 3); ?>"></div>
  </div>

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Security
    </div>

    <div class="num-row"><div><div class="toggle-label">Max Login Attempts</div><div class="toggle-sub">Before account lockout</div></div><input type="number" name="max_login_attempts" class="num-input" min="1" max="20" value="<?php echo e($config['max_login_attempts'] ?? 5); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">Lockout Duration</div><div class="toggle-sub">Minutes account stays locked</div></div><input type="number" name="lockout_duration_minutes" class="num-input" min="1" max="1440" value="<?php echo e($config['lockout_duration_minutes'] ?? 15); ?>"></div>
    <div class="num-row"><div><div class="toggle-label">Session Expiry</div><div class="toggle-sub">Hours before auto-logout</div></div><input type="number" name="session_expiry_hours" class="num-input" min="1" max="720" value="<?php echo e($config['session_expiry_hours'] ?? 24); ?>"></div>
  </div>

</div>

<div class="save-bar">
  <span class="save-status" id="save-status">Unsaved changes will be lost</span>
  <button type="button" onclick="saveSettings()" class="btn btn-primary" id="save-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    Save Settings
  </button>
</div>

</form>

<script>
async function saveSettings() {
  const form = document.getElementById('auth-settings-form');
  const btn = document.getElementById('save-btn');
  const status = document.getElementById('save-status');

  btn.disabled = true;
  btn.textContent = 'Saving...';
  status.className = 'save-status';
  status.textContent = 'Saving...';

  const formData = new FormData(form);
  const data = {};

  ['email_login','google_login','phone_otp_login','guest_checkout','auto_register_google','auto_register_otp','require_name_on_register','require_email_on_register'].forEach(k => {
    data[k] = formData.has(k);
  });

  ['otp_length','otp_expiry_minutes','max_otp_attempts','resend_cooldown_seconds','max_resends_per_hour','max_login_attempts','lockout_duration_minutes','session_expiry_hours'].forEach(k => {
    data[k] = parseInt(formData.get(k) || '0', 10);
  });

  try {
    const resp = await fetch('/admin/auth-settings', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      },
      body: JSON.stringify(data),
    });

    const contentType = resp.headers.get('content-type') || '';
    const body = contentType.includes('application/json') ? await resp.json() : { success: false, message: await resp.text() };

    if (resp.ok && body.success) {
      status.className = 'save-status ok';
      status.textContent = '✓ Settings saved successfully';
    } else {
      status.className = 'save-status err';
      status.textContent = body.message || `Failed to save (${resp.status})`;
    }
  } catch (e) {
    status.className = 'save-status err';
    status.textContent = 'Network error. Please try again.';
  }

  btn.disabled = false;
  btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Settings';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/auth-settings.blade.php ENDPATH**/ ?>