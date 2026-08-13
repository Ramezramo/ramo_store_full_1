@extends('layouts.app')

@php
  $otpRtl = session('locale', 'en') === 'ar';
  $otpCopy = $otpRtl ? [
    'pageTitle' => 'تأكيد كود الموبايل — Ramo Store',
    'title' => 'اكتب كود التأكيد', 'sentTo' => 'الكود اتبعت على', 'loading' => 'جاري التحميل…',
    'verify' => 'تأكيد الكود', 'verifyLoading' => 'جاري التأكيد',
    'resendIn' => 'إعادة الإرسال خلال', 'seconds' => 'ثانية', 'resend' => 'إعادة إرسال الكود',
    'changePhone' => 'تغيير رقم الموبايل', 'devTitle' => 'وضع التجربة — كود التأكيد',
    'devNote' => 'وضع تجربة فقط · الكود ما اتبعتش برسالة SMS حقيقية',
    'incomplete' => 'اكتب الـ6 أرقام كلها.', 'verified' => 'تم التأكيد. جاري التحويل…',
    'incorrect' => 'الكود غير صحيح.', 'networkError' => 'في مشكلة في النت. جرّب تاني.',
    'resending' => 'جاري إعادة الإرسال', 'resent' => 'تم إعادة إرسال الكود.',
    'resendFailed' => 'مش قادرين نعيد إرسال الكود دلوقتي.',
  ] : [
    'pageTitle' => 'Verify OTP — Ramo Store',
    'title' => 'Enter OTP Code', 'sentTo' => 'Code sent to', 'loading' => 'Loading…',
    'verify' => 'Verify Code', 'verifyLoading' => 'Verifying',
    'resendIn' => 'Resend in', 'seconds' => 's', 'resend' => 'Resend OTP',
    'changePhone' => 'Change phone number', 'devTitle' => 'Dev Mode — OTP Code',
    'devNote' => 'Development preview only · not sent via real SMS',
    'incomplete' => 'Please enter all 6 digits.', 'verified' => 'Verified! Redirecting…',
    'incorrect' => 'Incorrect code.', 'networkError' => 'Network error. Please try again.',
    'resending' => 'Resending', 'resent' => 'OTP resent!', 'resendFailed' => 'Could not resend OTP.',
  ];
@endphp

@section('title', $otpCopy['pageTitle'])

@push('styles')
<style>
.otp-boxes { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:clamp(5px,2vw,10px); width:100%; max-width:100%; margin:24px 0; direction:ltr; }
.otp-boxes input {
  width:100%; min-width:0; height:clamp(48px,15vw,56px); box-sizing:border-box; text-align:center; font-size:22px; font-weight:700;
  border:2px solid #e5e7eb; border-radius:10px; outline:none; transition:.15s;
  background:#fff; color:#1a1a1a;
}
.otp-boxes input:focus { border-color:#1a1a1a; }
.otp-boxes input.filled { border-color:#22c55e; }
.otp-sent-to { font-size:13px; color:#888; text-align:center; margin-bottom:4px; }
.otp-phone-display { font-size:15px; font-weight:700; text-align:center; color:#1a1a1a; direction:ltr; }
.otp-resend-row { text-align:center; font-size:13px; color:#888; margin-top:8px; }
.otp-resend-row a { color:#1a1a1a; font-weight:600; cursor:pointer; }
.otp-change-phone { text-align:center; margin-top:10px; }
.otp-change-phone a { font-size:13px; color:#888; text-decoration:underline; cursor:pointer; }
#otp-err { text-align:center; color:#e53e3e; font-size:13px; min-height:18px; margin-top:4px; }
#otp-ok  { text-align:center; color:#38a169; font-size:13px; min-height:18px; margin-top:4px; }
#verify-btn{position:relative;min-height:47px;}
#verify-btn .verify-spinner{display:none;width:20px;height:20px;border:2.5px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:otp-verify-spin .7s linear infinite;}
#verify-btn.is-loading{cursor:wait;}
#verify-btn.is-loading .verify-btn-label{display:none;}
#verify-btn.is-loading .verify-spinner{display:block;}
@keyframes otp-verify-spin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){#verify-btn .verify-spinner{animation:none;border-color:#fff;}}
</style>
@endpush

@section('content')
<div class="page otp-page" style="max-width:400px;margin:0 auto" @if($otpRtl) lang="ar" dir="rtl" @endif>
  <div class="auth-card">
    <div class="auth-logo">Ramo<span>Store</span></div>
    <h2 class="auth-title" style="text-align:center">{{ $otpCopy['title'] }}</h2>
    <p class="otp-sent-to">{{ $otpCopy['sentTo'] }}</p>
    <p class="otp-phone-display" id="display-phone">{{ $otpCopy['loading'] }}</p>

    <div class="otp-boxes" id="otp-boxes">
      @for($i = 0; $i < 6; $i++)
        <input type="text" inputmode="numeric" maxlength="1" class="otp-input" autocomplete="one-time-code">
      @endfor
    </div>

    <div id="otp-err"></div>
    <div id="otp-ok"></div>

    <button id="verify-btn" onclick="verifyOtp()" class="btn btn-dark"
      style="width:100%;justify-content:center;border-radius:10px;padding:13px;margin-top:14px;font-size:14px" aria-busy="false">
      <span class="verify-btn-label">{{ $otpCopy['verify'] }}</span>
      <span class="verify-spinner" role="status" aria-label="{{ $otpCopy['verifyLoading'] }}"></span>
    </button>

    <div class="otp-resend-row" style="margin-top:14px">
      <span id="resend-timer">{{ $otpCopy['resendIn'] }} <strong id="countdown">60</strong> {{ $otpCopy['seconds'] }}</span>
      <span id="resend-link" style="display:none"><a onclick="resendOtp()">{{ $otpCopy['resend'] }}</a></span>
    </div>

    <div class="otp-change-phone">
      <a href="{{ route('login') }}">{{ $otpRtl ? '→' : '←' }} {{ $otpCopy['changePhone'] }}</a>
    </div>

    <div id="dev-otp-box" aria-live="polite" style="display:none;margin-top:16px;padding:12px 14px;background:#fffbeb;border:1.5px dashed #f59e0b;border-radius:10px;text-align:center">
      <div style="font-size:11px;color:#92400e;font-weight:600;letter-spacing:.5px;margin-bottom:4px">{{ $otpCopy['devTitle'] }}</div>
      <div id="dev-otp-val" style="font-size:26px;font-weight:800;letter-spacing:6px;color:#92400e;font-family:monospace"></div>
      <div style="font-size:10px;color:#b45309;margin-top:4px">{{ $otpCopy['devNote'] }}</div>
    </div>
  </div>
</div>

<script>
const otpCopy = @json($otpCopy);
const phone = sessionStorage.getItem('otp_phone') || '';
let countdownVal = 60;
let countdownTimer;

document.getElementById('display-phone').textContent = '+20' + phone.replace(/^0/, '');

if (!phone) { window.location.href = '{{ route("login") }}'; }

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
  if (otp.length < boxes.length) { setErr(otpCopy.incomplete); return; }
  const btn = document.getElementById('verify-btn');
  btn.disabled = true;
  btn.classList.add('is-loading');
  btn.setAttribute('aria-busy', 'true');
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
      setOk(otpCopy.verified);
      sessionStorage.removeItem('otp_phone');
      setTimeout(() => { window.location.href = data.redirect; }, 600);
    } else {
      setErr(data.message || otpCopy.incorrect);
      btn.disabled = false;
      btn.classList.remove('is-loading');
      btn.setAttribute('aria-busy', 'false');
      boxes.forEach(b => b.value = '');
      updateFilled();
      boxes[0].focus();
    }
  } catch (e) {
    setErr(e?.message || otpCopy.networkError);
    btn.disabled = false;
    btn.classList.remove('is-loading');
    btn.setAttribute('aria-busy', 'false');
  }
}

async function resendOtp() {
  setErr(''); setOk(otpCopy.resending);
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
      setOk(otpCopy.resent);
      showDevelopmentOtp(data.dev_otp);
      startCountdown(60);
    } else {
      setErr(data.message || otpCopy.resendFailed);
      if (data.wait) startCountdown(data.wait);
    }
  } catch (e) {
    setErr(e?.message || otpCopy.networkError);
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

function showDevelopmentOtp(devOtp) {
  if (!devOtp || !/^\d{6}$/.test(String(devOtp))) return;
  document.getElementById('dev-otp-box').style.display = 'block';
  document.getElementById('dev-otp-val').textContent = String(devOtp);
}

document.addEventListener('DOMContentLoaded', () => {
  boxes[0].focus();
  startCountdown(60);
  const devOtp = sessionStorage.getItem('dev_otp');
  showDevelopmentOtp(devOtp);
  sessionStorage.removeItem('dev_otp');
});
</script>
@endsection
