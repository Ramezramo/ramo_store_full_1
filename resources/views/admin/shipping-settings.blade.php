@extends('admin.layout')
@section('title', 'Shipping Settings')
@section('page-title', 'Shipping Settings')

@push('styles')
<style>
.settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
@media(max-width:900px){ .settings-grid { grid-template-columns:1fr; } }

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
  width:110px; text-align:center; border-radius:7px;
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
@endpush

@section('content')
<form id="shipping-settings-form">
@csrf

<div class="settings-grid">

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      Free Shipping
    </div>

    <div class="toggle-row">
      <div>
        <div class="toggle-label">Enable Free Shipping</div>
        <div class="toggle-sub">Waive shipping fee once the order subtotal reaches the threshold below</div>
      </div>
      <label class="toggle-switch"><input type="checkbox" name="free_shipping_enabled" {{ ($config['free_shipping_enabled'] ?? true) ? 'checked' : '' }}><span class="toggle-slider"></span></label>
    </div>

    <div class="num-row">
      <div>
        <div class="toggle-label">Free Shipping Threshold (EGP)</div>
        <div class="toggle-sub">Orders at or above this subtotal ship free</div>
      </div>
      <input type="number" name="free_shipping_threshold" class="num-input" min="0" step="1" value="{{ $config['free_shipping_threshold'] ?? 1000 }}">
    </div>
  </div>

  <div class="setting-section">
    <div class="setting-section-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
      Standard Shipping
    </div>

    <div class="num-row">
      <div>
        <div class="toggle-label">Standard Shipping Fee (EGP)</div>
        <div class="toggle-sub">Charged when the order is below the free shipping threshold</div>
      </div>
      <input type="number" name="standard_shipping_fee" class="num-input" min="0" step="1" value="{{ $config['standard_shipping_fee'] ?? 0 }}">
    </div>
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
  const form = document.getElementById('shipping-settings-form');
  const btn = document.getElementById('save-btn');
  const status = document.getElementById('save-status');

  btn.disabled = true;
  btn.textContent = 'Saving...';
  status.className = 'save-status';
  status.textContent = 'Saving...';

  const formData = new FormData(form);
  const data = {
    free_shipping_enabled: formData.has('free_shipping_enabled'),
    free_shipping_threshold: parseFloat(formData.get('free_shipping_threshold') || '0'),
    standard_shipping_fee: parseFloat(formData.get('standard_shipping_fee') || '0'),
  };

  try {
    const resp = await fetch('{{ route('admin.shipping-settings.update') }}', {
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
@endsection
