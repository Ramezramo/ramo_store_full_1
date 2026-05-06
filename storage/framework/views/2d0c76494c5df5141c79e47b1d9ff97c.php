<?php $__env->startSection('title', 'Verify OTP — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.otp-boxes { display:flex; gap:10px; justify-content:center; margin:24px 0; }
.otp-boxes input {
  width:48px; height:56px; text-align:center; font-size:22px; font-weight:700;
  border:2px solid #e5e7eb; border-radius:10px; outline:none; transition:.15s;
  background:#fff; color:#1a1a1a;
}
.otp-boxes input:focus { border-color:#1a1a1a; }
.otp-boxes input.filled { border-color:#22c55e; }
.otp-sent-to { font-size:13px; color:#888; text-align:center; margin-bottom:4px; }
.otp-phone-display { font-size:15px; font-weight:700; text-align:center; color:#1a1a1a; }
.otp-resend-row { text-align:center; font-size:13px; color:#888; margin-top:8px; }
.otp-resend-row a { color:#1a1a1a; font-weight:600; cursor:pointer; }
.otp-change-phone { text-align:center; margin-top:10px; }
.otp-change-phone a { font-size:13px; color:#888; text-decoration:underline; cursor:pointer; }
#otp-err { text-align:center; color:#e53e3e; font-size:13px; min-height:18px; margin-top:4px; }
#otp-ok  { text-align:center; color:#38a169; font-size:13px; min-height:18px; margin-top:4px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:400px;margin:0 auto">
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title" style="text-align:center">Enter OTP Code</h2>
    <p class="otp-sent-to">Code sent to</p>
    <p class="otp-phone-display" id="display-phone">Loading...</p>

    <div class="otp-boxes" id="otp-boxes">
      <?php for($i = 0; $i < 6; $i++): ?>
        <input type="text" inputmode="numeric" maxlength="1" class="otp-input" autocomplete="one-time-code">
      <?php endfor; ?>
    </div>

    <div id="otp-err"></div>
    <div id="otp-ok"></div>

    <button id="verify-btn" onclick="verifyOtp()" class="btn btn-dark"
      style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:14px;font-size:14px">
      Verify Code
    </button>

    <div class="otp-resend-row" style="margin-top:14px">
      <span id="resend-timer">Resend in <strong id="countdown">60</strong>s</span>
      <span id="resend-link" style="display:none"><a onclick="resendOtp()">Resend OTP</a></span>
    </div>

    <div class="otp-change-phone">
      <a href="<?php echo e(route('login')); ?>">← Change phone number</a>
    </div>

    <div id="dev-otp-box" style="display:none;margin-top:16px;padding:12px 14px;background:#fffbeb;border:1.5px dashed #f59e0b;border-radius:10px;text-align:center">
      <div style="font-size:11px;color:#92400e;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Dev Mode — OTP Code</div>
      <div id="dev-otp-val" style="font-size:26px;font-weight:800;letter-spacing:6px;color:#92400e;font-family:monospace"></div>
      <div style="font-size:10px;color:#b45309;margin-top:4px">SMS_GATEWAY=log · not sent via real SMS</div>
    </div>
  </div>
</div>

<script>
const phone = sessionStorage.getItem('otp_phone') || '';
let countdownVal = 60;
let countdownTimer;

document.getElementById('display-phone').textContent = '+20' + phone.replace(/^0/, '');

if (!phone) { window.location.href = '<?php echo e(route("login")); ?>'; }

const boxes = document.querySelectorAll('.otp-input');
boxes.forEach((box, idx) => {
  box.addEventListener('input', e => {
    const val = e.target.value.replace(/[^0-9]/g, '');
    e.target.value = val.slice(-1);
    if (val && idx < boxes.length - 1) boxes[idx + 1].focus();
    updateFilled();
    if (getOtpValue().length === boxes.length) verifyOtp();
  });
  box.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !box.value && idx > 0) {
      boxes[idx - 1].focus();
      boxes[idx - 1].value = '';
      updateFilled();
    }
  });
  box.addEventListener('paste', e => {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
    [...text].forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
    updateFilled();
    if (text.length >= boxes.length) verifyOtp();
  });
});

function updateFilled() { boxes.forEach(b => b.classList.toggle('filled', b.value !== '')); }
function getOtpValue() { return [...boxes].map(b => b.value).join(''); }

async function verifyOtp() {
  const otp = getOtpValue();
  if (otp.length < boxes.length) { setErr('Please enter all 6 digits.'); return; }
  const btn = document.getElementById('verify-btn');
  btn.disabled = true;
  btn.textContent = 'Verifying...';
  setErr(''); setOk('');
  try {
    const resp = await fetch('/auth/verify-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
      },
      body: JSON.stringify({ phone, otp }),
    });
    const contentType = resp.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await resp.json() : { success: false, message: await resp.text() };
    if (data.success) {
      setOk('Verified! Redirecting...');
      sessionStorage.removeItem('otp_phone');
      setTimeout(() => { window.location.href = data.redirect; }, 600);
    } else {
      setErr(data.message || 'Incorrect code.');
      btn.disabled = false;
      btn.textContent = 'Verify Code';
      boxes.forEach(b => b.value = '');
      updateFilled();
      boxes[0].focus();
    }
  } catch (e) {
    setErr(e?.message || 'Network error. Please try again.');
    btn.disabled = false;
    btn.textContent = 'Verify Code';
  }
}

async function resendOtp() {
  setErr(''); setOk('Sending...');
  document.getElementById('resend-link').style.display = 'none';
  document.getElementById('resend-timer').style.display = '';
  try {
    const resp = await fetch('/auth/send-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
      },
      body: JSON.stringify({ phone }),
    });
    const contentType = resp.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await resp.json() : { success: false, message: await resp.text() };
    if (data.success) {
      setOk('OTP resent!');
      startCountdown(60);
    } else {
      setErr(data.message || 'Could not resend OTP.');
      if (data.wait) startCountdown(data.wait);
    }
  } catch (e) {
    setErr(e?.message || 'Network error.');
  }
}

function startCountdown(seconds) {
  clearInterval(countdownTimer);
  countdownVal = seconds;
  document.getElementById('countdown').textContent = countdownVal;
  document.getElementById('resend-timer').style.display = '';
  document.getElementById('resend-link').style.display = 'none';
  countdownTimer = setInterval(() => {
    countdownVal--;
    document.getElementById('countdown').textContent = countdownVal;
    if (countdownVal <= 0) {
      clearInterval(countdownTimer);
      document.getElementById('resend-timer').style.display = 'none';
      document.getElementById('resend-link').style.display = '';
    }
  }, 1000);
}

function setErr(msg) { document.getElementById('otp-err').textContent = msg; document.getElementById('otp-ok').textContent = ''; }
function setOk(msg) { document.getElementById('otp-ok').textContent = msg; document.getElementById('otp-err').textContent = ''; }

document.addEventListener('DOMContentLoaded', () => {
  boxes[0].focus();
  startCountdown(60);
  const devOtp = sessionStorage.getItem('dev_otp');
  if (devOtp) {
    document.getElementById('dev-otp-box').style.display = 'block';
    document.getElementById('dev-otp-val').textContent = devOtp;
    sessionStorage.removeItem('dev_otp');
  }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/auth/otp-verify.blade.php ENDPATH**/ ?>