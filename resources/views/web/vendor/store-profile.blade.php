@extends('web.vendor.layout')
@section('title', 'Store Profile')
@section('page-title', 'Store Profile')

@push('styles')
<style>
.img-upload-card{background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:14px}
.img-upload-card h3{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid)}
.img-preview-wrap{position:relative;border-radius:10px;overflow:hidden;border:2px dashed var(--light);background:#fafaf8;display:flex;align-items:center;justify-content:center;transition:.2s}
.img-preview-wrap.has-img{border-style:solid;border-color:var(--light)}
.img-preview-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.img-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:var(--mid);font-size:12px;height:100%;padding:20px}
.img-placeholder svg{opacity:.4}
.upload-btn-wrap{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.img-remove-btn{font-size:12px;color:var(--red);font-weight:600;cursor:pointer;background:none;border:none;padding:0;text-decoration:underline}
.section-card{background:#fff;border:1px solid var(--light);border-radius:12px;padding:24px;margin-bottom:20px}
.section-card h2{font-size:13px;font-weight:700;color:var(--dark);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--light);display:flex;align-items:center;gap:8px}
.vs-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 20px}
.vs-toggle-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--light)}
.vs-toggle-row:last-child{border-bottom:none}
.vs-toggle-label{font-size:13px;font-weight:600}
.vs-toggle-desc{font-size:12px;color:var(--mid);margin-top:2px}
.vs-toggle{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.vs-toggle input{opacity:0;width:0;height:0}
.vs-toggle-slider{position:absolute;inset:0;background:#d1d5db;border-radius:24px;cursor:pointer;transition:.2s}
.vs-toggle-slider:before{content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
.vs-toggle input:checked + .vs-toggle-slider{background:var(--orange)}
.vs-toggle input:checked + .vs-toggle-slider:before{transform:translateX(18px)}
@media(max-width:640px){.vs-form-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('vendor.store.profile.update') }}" enctype="multipart/form-data" id="profile-form">
@csrf

{{-- ── STORE INFO ───────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Store Information
  </h2>

  <div class="vs-form-grid">
    <div class="vs-form-group">
      <label class="vs-label">Shop Name <span style="color:var(--red)">*</span></label>
      <input type="text" name="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}"
             class="vs-input {{ $errors->has('shop_name') ? 'err' : '' }}" required maxlength="255"
             placeholder="Your store name">
      @error('shop_name')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Phone Number <span style="color:var(--red)">*</span></label>
      <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}"
             class="vs-input {{ $errors->has('phone') ? 'err' : '' }}" required maxlength="20"
             placeholder="+20 10 0000 0000">
      @error('phone')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group" style="grid-column:1/-1">
      <label class="vs-label">Shop Address <span style="color:var(--red)">*</span></label>
      <input type="text" name="shop_address" value="{{ old('shop_address', $vendor->shop_address) }}"
             class="vs-input {{ $errors->has('shop_address') ? 'err' : '' }}" required maxlength="500"
             placeholder="Street, city, country">
      @error('shop_address')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

{{-- ── DELIVERY SETTINGS ────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
    Delivery & Orders
  </h2>

  <div class="vs-form-grid" style="margin-bottom:16px">
    <div class="vs-form-group">
      <label class="vs-label">Minimum Order Amount (EGP)</label>
      <input type="number" name="minimum_order_amount" min="0" step="1"
             value="{{ old('minimum_order_amount', $vendor->minimum_order_amount ?? 0) }}"
             class="vs-input {{ $errors->has('minimum_order_amount') ? 'err' : '' }}"
             placeholder="0 = no minimum">
      @error('minimum_order_amount')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Free Delivery Over (EGP)</label>
      <input type="number" name="free_delivery_over_amount" min="0" step="1"
             value="{{ old('free_delivery_over_amount', $vendor->free_delivery_over_amount ?? 0) }}"
             class="vs-input {{ $errors->has('free_delivery_over_amount') ? 'err' : '' }}"
             placeholder="0 = always charge delivery">
      @error('free_delivery_over_amount')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
  </div>

  <div>
    <div class="vs-toggle-row">
      <div>
        <div class="vs-toggle-label">Enable Free Delivery</div>
        <div class="vs-toggle-desc">Show free delivery option to customers</div>
      </div>
      <label class="vs-toggle">
        <input type="checkbox" name="free_delivery_status" value="1"
               {{ old('free_delivery_status', $vendor->free_delivery_status) ? 'checked' : '' }}>
        <span class="vs-toggle-slider"></span>
      </label>
    </div>

    <div class="vs-toggle-row">
      <div>
        <div class="vs-toggle-label">Temporarily Close Store</div>
        <div class="vs-toggle-desc">Pause all orders without losing your store listing</div>
      </div>
      <label class="vs-toggle">
        <input type="checkbox" name="temporary_close" value="1"
               {{ old('temporary_close', $vendor->temporary_close) ? 'checked' : '' }}>
        <span class="vs-toggle-slider"></span>
      </label>
    </div>
  </div>
</div>

{{-- ── VACATION MODE ────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Vacation Mode
  </h2>

  <div class="vs-toggle-row" style="margin-bottom:16px">
    <div>
      <div class="vs-toggle-label">Enable Vacation Mode</div>
      <div class="vs-toggle-desc">Display a vacation notice on your store page</div>
    </div>
    <label class="vs-toggle">
      <input type="checkbox" name="vacation_status" value="1" id="vacation-toggle"
             {{ old('vacation_status', $vendor->vacation_status) ? 'checked' : '' }}
             onchange="document.getElementById('vacation-dates').style.display=this.checked?'grid':'none'">
      <span class="vs-toggle-slider"></span>
    </label>
  </div>

  <div id="vacation-dates" style="display:{{ $vendor->vacation_status ? 'grid' : 'none' }};grid-template-columns:1fr 1fr;gap:0 20px">
    <div class="vs-form-group">
      <label class="vs-label">Vacation Start Date</label>
      <input type="date" name="vacation_start_date"
             value="{{ old('vacation_start_date', ($vendor->vacation_start_date !== 'empty' ? $vendor->vacation_start_date : '')) }}"
             class="vs-input">
    </div>
    <div class="vs-form-group">
      <label class="vs-label">Vacation End Date</label>
      <input type="date" name="vacation_end_date"
             value="{{ old('vacation_end_date', ($vendor->vacation_end_date !== 'empty' ? $vendor->vacation_end_date : '')) }}"
             class="vs-input">
    </div>
  </div>
</div>

{{-- ── STORE IMAGES ────────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Store Images
  </h2>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">

    {{-- LOGO --}}
    <div class="img-upload-card">
      <h3>Shop Logo</h3>
      <div class="img-preview-wrap {{ $vendor->shop_logo ? 'has-img' : '' }}" style="height:160px" id="preview-logo">
        @if($vendor->shop_logo_url)
          <img src="{{ $vendor->shop_logo_url }}" id="img-logo" alt="Shop logo">
        @else
          <div class="img-placeholder" id="ph-logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <span>No logo yet</span>
          </div>
        @endif
      </div>
      <div class="upload-btn-wrap">
        <label for="logo-input" class="vs-btn vs-btn-ghost vs-btn-sm" style="cursor:pointer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Logo
        </label>
        <input type="file" id="logo-input" name="shop_logo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewImage(this,'img-logo','ph-logo','preview-logo')">
        @if($vendor->shop_logo)
          <button type="submit" name="remove_image" value="shop_logo" class="img-remove-btn" onclick="return confirm('Remove logo?')">Remove</button>
        @endif
      </div>
      <div style="font-size:11px;color:var(--mid)">Square image · max 4 MB · JPG / PNG / WebP · 600×600px</div>
      @error('shop_logo')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    {{-- MAIN BANNER --}}
    <div class="img-upload-card">
      <h3>Main Banner</h3>
      <div class="img-preview-wrap {{ $vendor->shop_banner ? 'has-img' : '' }}" style="height:160px" id="preview-banner">
        @if($vendor->shop_banner_url)
          <img src="{{ $vendor->shop_banner_url }}" id="img-banner" alt="Main banner">
        @else
          <div class="img-placeholder" id="ph-banner">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2l-4 5-4-5"/></svg>
            <span>No banner yet</span>
          </div>
        @endif
      </div>
      <div class="upload-btn-wrap">
        <label for="banner-input" class="vs-btn vs-btn-ghost vs-btn-sm" style="cursor:pointer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Banner
        </label>
        <input type="file" id="banner-input" name="shop_banner" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewImage(this,'img-banner','ph-banner','preview-banner')">
        @if($vendor->shop_banner)
          <button type="submit" name="remove_image" value="shop_banner" class="img-remove-btn" onclick="return confirm('Remove main banner?')">Remove</button>
        @endif
      </div>
      <div style="font-size:11px;color:var(--mid)">Wide image · max 4 MB · JPG / PNG / WebP · 1200×400px recommended</div>
      @error('shop_banner')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    {{-- SECONDARY BANNER --}}
    <div class="img-upload-card">
      <h3>Secondary Banner</h3>
      <div class="img-preview-wrap {{ $vendor->secondary_banner ? 'has-img' : '' }}" style="height:160px" id="preview-secondary">
        @if($vendor->secondary_banner_url)
          <img src="{{ $vendor->secondary_banner_url }}" id="img-secondary" alt="Secondary banner">
        @else
          <div class="img-placeholder" id="ph-secondary">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2l-4 5-4-5"/></svg>
            <span>No banner yet</span>
          </div>
        @endif
      </div>
      <div class="upload-btn-wrap">
        <label for="secondary-input" class="vs-btn vs-btn-ghost vs-btn-sm" style="cursor:pointer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Banner
        </label>
        <input type="file" id="secondary-input" name="secondary_banner" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewImage(this,'img-secondary','ph-secondary','preview-secondary')">
        @if($vendor->secondary_banner)
          <button type="submit" name="remove_image" value="secondary_banner" class="img-remove-btn" onclick="return confirm('Remove secondary banner?')">Remove</button>
        @endif
      </div>
      <div style="font-size:11px;color:var(--mid)">Wide image · max 4 MB · JPG / PNG / WebP</div>
      @error('secondary_banner')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    {{-- BOTTOM BANNER --}}
    <div class="img-upload-card">
      <h3>Bottom Banner</h3>
      <div class="img-preview-wrap {{ ($vendor->bottom_banner && $vendor->bottom_banner !== 'empty') ? 'has-img' : '' }}" style="height:160px" id="preview-bottom">
        @if($vendor->bottom_banner_url)
          <img src="{{ $vendor->bottom_banner_url }}" id="img-bottom" alt="Bottom banner">
        @else
          <div class="img-placeholder" id="ph-bottom">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            <span>No banner yet</span>
          </div>
        @endif
      </div>
      <div class="upload-btn-wrap">
        <label for="bottom-input" class="vs-btn vs-btn-ghost vs-btn-sm" style="cursor:pointer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Banner
        </label>
        <input type="file" id="bottom-input" name="bottom_banner" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewImage(this,'img-bottom','ph-bottom','preview-bottom')">
        @if($vendor->bottom_banner && $vendor->bottom_banner !== 'empty')
          <button type="submit" name="remove_image" value="bottom_banner" class="img-remove-btn" onclick="return confirm('Remove bottom banner?')">Remove</button>
        @endif
      </div>
      <div style="font-size:11px;color:var(--mid)">Wide image · max 4 MB · JPG / PNG / WebP</div>
      @error('bottom_banner')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    {{-- OFFER BANNER --}}
    <div class="img-upload-card">
      <h3>Offer Banner</h3>
      <div class="img-preview-wrap {{ ($vendor->offer_banner && $vendor->offer_banner !== 'empty') ? 'has-img' : '' }}" style="height:160px" id="preview-offer">
        @if($vendor->offer_banner_url)
          <img src="{{ $vendor->offer_banner_url }}" id="img-offer" alt="Offer banner">
        @else
          <div class="img-placeholder" id="ph-offer">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            <span>No banner yet</span>
          </div>
        @endif
      </div>
      <div class="upload-btn-wrap">
        <label for="offer-input" class="vs-btn vs-btn-ghost vs-btn-sm" style="cursor:pointer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Banner
        </label>
        <input type="file" id="offer-input" name="offer_banner" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="previewImage(this,'img-offer','ph-offer','preview-offer')">
        @if($vendor->offer_banner && $vendor->offer_banner !== 'empty')
          <button type="submit" name="remove_image" value="offer_banner" class="img-remove-btn" onclick="return confirm('Remove offer banner?')">Remove</button>
        @endif
      </div>
      <div style="font-size:11px;color:var(--mid)">Wide image · max 4 MB · JPG / PNG / WebP</div>
      @error('offer_banner')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

  </div>{{-- end grid --}}
</div>

{{-- ── BANKING & PAYOUT ─────────────────────────────────────── --}}
<div class="section-card">
  <h2>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
    Banking & Payout Details
  </h2>
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;font-size:12px;color:#92400e;margin-bottom:18px;display:flex;gap:8px;align-items:flex-start">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    This information is only visible to RamoStore admins and is used to process your commission payouts.
  </div>

  <div class="vs-form-grid">
    <div class="vs-form-group">
      <label class="vs-label">Account Holder Name</label>
      <input type="text" name="holder_name"
             value="{{ old('holder_name', $vendor->holder_name !== '0' ? $vendor->holder_name : '') }}"
             class="vs-input {{ $errors->has('holder_name') ? 'err' : '' }}"
             placeholder="Full name as it appears on bank account" maxlength="200">
      @error('holder_name')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Bank Name</label>
      <input type="text" name="bank_name"
             value="{{ old('bank_name', !in_array($vendor->bank_name, ['not set','0']) ? $vendor->bank_name : '') }}"
             class="vs-input {{ $errors->has('bank_name') ? 'err' : '' }}"
             placeholder="e.g. CIB, NBE, Banque Misr" maxlength="200">
      @error('bank_name')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Branch</label>
      <input type="text" name="branch"
             value="{{ old('branch', !in_array($vendor->branch, ['not set','0']) ? $vendor->branch : '') }}"
             class="vs-input {{ $errors->has('branch') ? 'err' : '' }}"
             placeholder="Branch name or code" maxlength="200">
      @error('branch')<div class="vs-err">{{ $message }}</div>@enderror
    </div>

    <div class="vs-form-group">
      <label class="vs-label">Account Number</label>
      <input type="text" name="account_no"
             value="{{ old('account_no', ($vendor->account_no && $vendor->account_no != 0) ? $vendor->account_no : '') }}"
             class="vs-input {{ $errors->has('account_no') ? 'err' : '' }}"
             placeholder="Your bank account number" maxlength="50">
      @error('account_no')<div class="vs-err">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

{{-- ── SAVE BUTTON ─────────────────────────────────────────── --}}
<div style="display:flex;justify-content:flex-end;gap:12px">
  <a href="{{ route('vendor.dashboard') }}" class="vs-btn vs-btn-ghost">Cancel</a>
  <button type="submit" class="vs-btn vs-btn-primary" id="save-btn">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    Save Changes
  </button>
</div>

</form>

<script>
function previewImage(input, imgId, phId, wrapId) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const reader = new FileReader();
  reader.onload = function(e) {
    const wrap = document.getElementById(wrapId);
    wrap.classList.add('has-img');
    // Replace or create the img
    let img = document.getElementById(imgId);
    if (!img) {
      img = document.createElement('img');
      img.id = imgId;
      img.alt = '';
      wrap.innerHTML = '';
      wrap.appendChild(img);
    } else {
      const ph = document.getElementById(phId);
      if (ph) ph.style.display = 'none';
    }
    img.src = e.target.result;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';
    img.style.display = 'block';
  };
  reader.readAsDataURL(file);
}
</script>

@endsection
