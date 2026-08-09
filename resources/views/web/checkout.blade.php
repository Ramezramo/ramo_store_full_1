@extends('layouts.app')
@section('title', 'Checkout — Ramo Store')

@section('content')
<div class="page">
  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span>
    <a href="{{ route('cart') }}">Cart</a><span>/</span>
    <strong>Checkout</strong>
  </div>

  <div class="checkout-layout">
    {{-- FORM --}}
    <div>
      <form method="POST" action="{{ route('checkout.place') }}" id="checkout-form">
        @csrf

        {{-- CONTACT INFO --}}
        <div class="ck-section">
          <h3 class="ck-title">Contact Information</h3>
          @if(!auth()->check())
            <p class="ck-login-hint">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
          @endif
          <div class="form-grid-2">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
              @error('first_name')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
              @error('last_name')<span class="err">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            @error('email')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-group">
              <label>Phone Number *</label>
              <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="01xxxxxxxxx" inputmode="tel" pattern="[0-9+\-\s()]{7,20}" required>
            @error('phone')<span class="err">{{ $message }}</span>@enderror
          </div>
        </div>

        {{-- SHIPPING ADDRESS --}}
        <div class="ck-section">
          <h3 class="ck-title">Shipping Address</h3>
          <div class="form-group">
            <label>Pin Your Location on Map</label>
            <button type="button" class="btn btn-outline" id="use-current-location-btn" style="margin-bottom:12px">📍 Use My Current Location</button>
            <div style="position:relative;width:100%;margin-bottom:12px">
              <div id="checkout-map" style="width:100%;height:280px;border-radius:14px;overflow:hidden;border:1px solid rgba(0,0,0,.08)"></div>
              <div id="map-locating-overlay" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.75);border-radius:14px;z-index:999;flex-direction:column;align-items:center;justify-content:center;gap:10px">
                <div style="width:38px;height:38px;border:4px solid #e85d26;border-top-color:transparent;border-radius:50%;animation:map-spin .8s linear infinite"></div>
                <span style="font-size:13px;font-weight:600;color:#e85d26">Getting your location…</span>
              </div>
            </div>
            <style>@keyframes map-spin{to{transform:rotate(360deg)}}</style>
            <div id="location-status" style="font-size:12px;color:var(--muted)"></div>
          </div>
          <div class="form-group">
            <label>Street Address *</label>
            <input type="text" name="address" value="{{ old('address') }}" required>
            @error('address')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>City *</label>
              <input type="text" name="city" value="{{ old('city') }}" required>
              @error('city')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label>State / Governorate *</label>
              <select name="state" required>
                <option value="">Select governorate</option>
                @foreach(['Cairo','Giza','Alexandria','Aswan','Asyut','Beheira','Beni Suef','Dakahlia','Damietta','Faiyum','Gharbia','Ismailia','Kafr El Sheikh','Luxor','Matrouh','Minya','Monufia','New Valley','North Sinai','Port Said','Qalyubia','Qena','Red Sea','Sharqia','Sohag','South Sinai','Suez'] as $gov)
                  <option value="{{ $gov }}" {{ old('state') === $gov ? 'selected' : '' }}>{{ $gov }}</option>
                @endforeach
              </select>
              @error('state')<span class="err">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="form-group">
            <label>Apartment / Floor / Additional details</label>
            <textarea name="address_note" rows="2" placeholder="Apartment / Floor / Additional details">{{ old('address_note') }}</textarea>
          </div>
          <input type="hidden" name="latitude" id="checkout-latitude" value="{{ old('latitude', $user->latitude ?? '') }}">
          <input type="hidden" name="longitude" id="checkout-longitude" value="{{ old('longitude', $user->longitude ?? '') }}">
          <div class="form-group" style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" name="save_address" value="1" id="save-address" {{ old('save_address', session('checkout_save_address', true)) ? 'checked' : '' }}>
            <label for="save-address" style="margin:0">Save this address for future use</label>
          </div>
        </div>

        {{-- PAYMENT --}}
        <div class="ck-section">
          <h3 class="ck-title">Payment Method</h3>
          @php
            $selectedPaymentMethod = old('payment_method', array_key_first($paymentMethods) ?: 'cod');
          @endphp
          <div class="pay-methods">
            @foreach($paymentMethods as $val => $method)
            <label class="pay-option {{ $selectedPaymentMethod === $val ? 'selected' : '' }}" data-val="{{ $val }}">
              <input type="radio" name="payment_method" value="{{ $val }}" {{ $selectedPaymentMethod === $val ? 'checked' : '' }}>
              <span class="pay-icon">{{ $method['icon'] ?? '💳' }}</span>
              <div>
                <div class="pay-title">{{ $method['title'] }}</div>
                <div class="pay-desc">{{ $method['description'] }}</div>
                @if($method['data'] ?? '')
                  <div class="pay-data">
                    <span>{{ $method['data_label'] ?? 'Details' }}:</span>
                    <strong>{{ $method['data'] }}</strong>
                    @if($method['link'] ?? null)
                      <a href="{{ $method['link'] }}" target="_blank" rel="noopener">Open link</a>
                    @endif
                  </div>
                @endif
              </div>
            </label>
            @endforeach
          </div>
          @error('payment_method')<span class="err">{{ $message }}</span>@enderror
          <div style="font-size:12px;color:#6b7280;margin-top:10px">For Wallet or InstaPay, place the order first, transfer the amount, then upload your receipt from the order page.</div>
        </div>

        {{-- ORDER NOTES --}}
        <div class="ck-section">
          <div class="form-group">
            <label>Order Notes (optional)</label>
            <textarea name="notes" rows="3" placeholder="Any special instructions for your order…">{{ old('notes') }}</textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-dark place-order-btn">Place Order →</button>
      </form>
    </div>

    {{-- ORDER SUMMARY --}}
    <div class="ck-summary">
      <h3 class="ck-title">Order Summary</h3>
      <div class="ck-items">
        @foreach($cart as $item)
        <div class="ck-item">
          <div class="ck-item-img">
            @if($item['image'])
              <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
            @else
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">👕</div>
            @endif
            <span class="ck-item-qty">{{ $item['qty'] }}</span>
          </div>
          <div class="ck-item-name">
            {{ Str::limit($item['name'], 35) }}
            @if(!empty($item['sku']))
              <div class="ck-item-sku">SKU: {{ $item['sku'] }}</div>
            @endif
            @if(!empty($item['attrs']))
              <div class="ck-item-attrs">
                @foreach($item['attrs'] as $k => $v)
                  <span>{{ $k }}: <strong>{{ $v }}</strong></span>
                @endforeach
              </div>
            @endif
          </div>
          <div class="ck-item-price">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</div>
        </div>
        @endforeach
      </div>
      <div class="ck-totals">
        <div class="summary-row"><span>Subtotal</span><span>{{ number_format($subtotal, 2) }} EGP</span></div>
        @if($coupon && $discount > 0)
          <div class="summary-row discount-row"><span>Coupon ({{ $coupon['code'] }})</span><span>−{{ number_format($discount, 2) }} EGP</span></div>
        @endif
        <div class="summary-row"><span>Estimated Delivery</span><span>2–4 days</span></div>
        <div class="summary-row"><span>Shipping</span><span>Free</span></div>
        <div class="summary-divider"></div>
        <div class="summary-row total-row"><span>Total</span><span>{{ number_format($total, 2) }} EGP</span></div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
  const updateSelectedMethod = () => {
    document.querySelectorAll('.pay-option').forEach((option) => {
      option.classList.toggle('selected', option.querySelector('input')?.checked === true);
    });
  };
  paymentOptions.forEach((input) => input.addEventListener('change', updateSelectedMethod));
  updateSelectedMethod();
  const useLocationBtn = document.getElementById('use-current-location-btn');
  const addressInput = document.querySelector('input[name="address"]');
  const cityInput = document.querySelector('input[name="city"]');
  const stateSelect = document.querySelector('select[name="state"]');
  const mapEl = document.getElementById('checkout-map');
  const locationStatus = document.getElementById('location-status');
  const latitudeInput = document.getElementById('checkout-latitude');
  const longitudeInput = document.getElementById('checkout-longitude');
  let map = null;
  let marker = null;

  const setStatus = (msg) => {
    if (locationStatus) locationStatus.textContent = msg;
  };
  const setCoords = (lat, lng) => {
    if (latitudeInput) latitudeInput.value = lat ?? '';
    if (longitudeInput) longitudeInput.value = lng ?? '';
  };
  const norm = (v) => (v || '').toString().trim().toLowerCase();
  const matchGovernorate = (value) => {
    const aliases = {
      'al minya': 'Minya',
      'el minya': 'Minya',
      'minya': 'Minya',
      'minia': 'Minya',
      'menya': 'Minya',
      'al minyā': 'Minya',
    };
    const normalized = aliases[norm(value)] || value;
    const found = Array.from(stateSelect?.options || []).find(opt => norm(opt.value) === norm(normalized) || norm(opt.textContent) === norm(normalized));
    if (found && stateSelect) stateSelect.value = found.value;
    return !!found;
  };
  const updateFields = async (lat, lng) => {
    setCoords(lat, lng);
    try {
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=15&addressdetails=1`);
      const data = await res.json();
      const addr = data.address || {};
      const street = [addr.house_number, addr.road, addr.neighbourhood, addr.suburb, addr.hamlet, addr.village].filter(Boolean).join(' ') || data.display_name || '';
      const city = addr.city || addr.town || addr.village || addr.municipality || addr.county || addr.suburb || addr.city_district || addr.hamlet || '';
      const state = addr.state || addr.region || addr.state_district || addr.province || addr.county || addr.country || '';
      if (addressInput) addressInput.value = street;
      if (cityInput) cityInput.value = city;
      if (addressInput) addressInput.dispatchEvent(new Event('input', { bubbles: true }));
      if (cityInput) cityInput.dispatchEvent(new Event('input', { bubbles: true }));
      matchGovernorate(state);
      setStatus(city || state ? `Selected: ${[city, state].filter(Boolean).join(', ')}` : 'Location selected.');
    } catch (_) {
      setStatus('Location selected, but address details could not be loaded.');
    }
  };
  const initMap = (lat, lng) => {
    if (!window.L || !mapEl || map) return;
    map = L.map(mapEl, { zoomControl: true }).setView([lat, lng], 14);
    L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      attribution: 'Google Maps'
    }).addTo(map);
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => {
      const p = marker.getLatLng();
      updateFields(p.lat, p.lng);
    });
    map.on('click', (e) => {
      marker.setLatLng(e.latlng);
      updateFields(e.latlng.lat, e.latlng.lng);
    });
  };
  const savedLat = parseFloat(latitudeInput?.value || '');
  const savedLng = parseFloat(longitudeInput?.value || '');
  const startLat = Number.isFinite(savedLat) ? savedLat : 30.0444;
  const startLng = Number.isFinite(savedLng) ? savedLng : 31.2357;
  const loadMap = () => {
    const css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(css);
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => initMap(startLat, startLng);
    document.body.appendChild(script);
  };
  if (mapEl) {
    if (window.L) initMap(startLat, startLng);
    else loadMap();
    if (useLocationBtn && navigator.geolocation) {
      const mapOverlay = document.getElementById('map-locating-overlay');
      function showMapLoading() { if (mapOverlay) mapOverlay.style.display = 'flex'; }
      function hideMapLoading() { if (mapOverlay) mapOverlay.style.display = 'none'; }

      function fetchLocation() {
        setStatus('Locating...');
        showMapLoading();
        navigator.geolocation.getCurrentPosition((pos) => {
          hideMapLoading();
          const { latitude, longitude, accuracy } = pos.coords;
          setCoords(latitude, longitude);
          if (map) {
            map.setView([latitude, longitude], 14);
            marker?.setLatLng([latitude, longitude]);
          } else {
            initMap(latitude, longitude);
          }
          updateFields(latitude, longitude);
          setStatus(accuracy ? `Location detected (${Math.round(accuracy)}m accuracy). You can drag the pin to adjust it.` : 'Location detected. You can drag the pin to adjust it.');
        }, () => {
          hideMapLoading();
          setStatus('Could not detect your location. Please allow location access and try again.');
        }, { enableHighAccuracy: true, timeout: 60000, maximumAge: 0 });
      }

      useLocationBtn.addEventListener('click', () => {
        // If Permissions API is available, watch for the grant so the user
        // doesn't have to click the button a second time after allowing.
        if (navigator.permissions) {
          navigator.permissions.query({ name: 'geolocation' }).then((result) => {
            if (result.state === 'granted') {
              // Already granted — fetch immediately.
              fetchLocation();
            } else if (result.state === 'prompt') {
              // Permission dialog is about to appear; start a high-timeout
              // request so it survives the dialog, then watch for the grant.
              fetchLocation();
              result.onchange = () => {
                if (result.state === 'granted') {
                  result.onchange = null;
                  fetchLocation();
                } else if (result.state === 'denied') {
                  result.onchange = null;
                  setStatus('Location access was denied. Please enable it in your browser settings and try again.');
                }
              };
            } else {
              setStatus('Location access is blocked. Please enable it in your browser settings and try again.');
            }
          });
        } else {
          // Fallback for browsers without Permissions API.
          fetchLocation();
        }
      });
    }
  }
});
</script>
@endpush
