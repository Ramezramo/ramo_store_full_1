<?php $__env->startSection('title', 'Checkout — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">
  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span>
    <a href="<?php echo e(route('cart')); ?>">Cart</a><span>/</span>
    <strong>Checkout</strong>
  </div>

  <div class="checkout-layout">
    
    <div>
      <form method="POST" action="<?php echo e(route('checkout.place')); ?>" id="checkout-form">
        <?php echo csrf_field(); ?>

        
        <div class="ck-section">
          <h3 class="ck-title">Contact Information</h3>
          <?php if(!auth()->check()): ?>
            <p class="ck-login-hint">Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in</a></p>
          <?php endif; ?>
          <div class="form-grid-2">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="first_name" value="<?php echo e(old('first_name', $user->first_name ?? '')); ?>" required>
              <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="last_name" value="<?php echo e(old('last_name', $user->last_name ?? '')); ?>" required>
              <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>
          <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" value="<?php echo e(old('email', $user->email ?? '')); ?>" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="form-group">
              <label>Phone Number *</label>
              <input type="tel" name="phone" value="<?php echo e(old('phone', $user->phone ?? '')); ?>" placeholder="01xxxxxxxxx" inputmode="tel" pattern="[0-9+\-\s()]{7,20}" required>
            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>

        
        <div class="ck-section">
          <h3 class="ck-title">Shipping Address</h3>
          <div class="form-group">
            <label>Pin Your Location on Map</label>
            <button type="button" class="btn btn-outline" id="use-current-location-btn" style="margin-bottom:12px">📍 Use My Current Location</button>
            <div id="checkout-map" style="width:100%;height:280px;border-radius:14px;overflow:hidden;border:1px solid rgba(0,0,0,.08);margin-bottom:12px"></div>
            <div id="location-status" style="font-size:12px;color:var(--muted)"></div>
          </div>
          <div class="form-group">
            <label>Street Address *</label>
            <input type="text" name="address" value="<?php echo e(old('address')); ?>" required>
            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>City *</label>
              <input type="text" name="city" value="<?php echo e(old('city')); ?>" required>
              <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="form-group">
              <label>State / Governorate *</label>
              <select name="state" required>
                <option value="">Select governorate</option>
                <?php $__currentLoopData = ['Cairo','Giza','Alexandria','Aswan','Asyut','Beheira','Beni Suef','Dakahlia','Damietta','Faiyum','Gharbia','Ismailia','Kafr El Sheikh','Luxor','Matrouh','Minya','Monufia','New Valley','North Sinai','Port Said','Qalyubia','Qena','Red Sea','Sharqia','Sohag','South Sinai','Suez']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($gov); ?>" <?php echo e(old('state') === $gov ? 'selected' : ''); ?>><?php echo e($gov); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>
          <div class="form-group">
            <label>Apartment / Floor / Additional details</label>
            <textarea name="address_note" rows="2" placeholder="Apartment / Floor / Additional details"><?php echo e(old('address_note')); ?></textarea>
          </div>
          <input type="hidden" name="latitude" id="checkout-latitude" value="<?php echo e(old('latitude', $user->latitude ?? '')); ?>">
          <input type="hidden" name="longitude" id="checkout-longitude" value="<?php echo e(old('longitude', $user->longitude ?? '')); ?>">
          <div class="form-group" style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" name="save_address" value="1" id="save-address" <?php echo e(old('save_address', session('checkout_save_address', true)) ? 'checked' : ''); ?>>
            <label for="save-address" style="margin:0">Save this address for future use</label>
          </div>
        </div>

        
        <div class="ck-section">
          <h3 class="ck-title">Payment Method</h3>
          <div class="pay-methods">
            <?php $__currentLoopData = [
              ['cod',           '💵', 'Cash on Delivery',  'Pay when your order arrives'],
              ['vodafone_cash', '📱', 'Vodafone Cash',     'Send to 01xxxxxxxxx'],
              ['bank_transfer', '🏦', 'Bank Transfer',     'Transfer to our bank account'],
              ['fawry',         '🏪', 'Fawry',             'Pay at any Fawry outlet'],
              ['credit_card',   '💳', 'Credit Card',       'Visa / Mastercard'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$val, $ico, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="pay-option <?php echo e(old('payment_method','cod') === $val ? 'selected' : ''); ?>" data-val="<?php echo e($val); ?>">
              <input type="radio" name="payment_method" value="<?php echo e($val); ?>" <?php echo e(old('payment_method','cod') === $val ? 'checked' : ''); ?>>
              <span class="pay-icon"><?php echo e($ico); ?></span>
              <div>
                <div class="pay-title"><?php echo e($title); ?></div>
                <div class="pay-desc"><?php echo e($desc); ?></div>
              </div>
            </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="ck-section">
          <div class="form-group">
            <label>Order Notes (optional)</label>
            <textarea name="notes" rows="3" placeholder="Any special instructions for your order…"><?php echo e(old('notes')); ?></textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-dark place-order-btn">Place Order →</button>
      </form>
    </div>

    
    <div class="ck-summary">
      <h3 class="ck-title">Order Summary</h3>
      <div class="ck-items">
        <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="ck-item">
          <div class="ck-item-img">
            <?php if($item['image']): ?>
              <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">👕</div>
            <?php endif; ?>
            <span class="ck-item-qty"><?php echo e($item['qty']); ?></span>
          </div>
          <div class="ck-item-name">
            <?php echo e(Str::limit($item['name'], 35)); ?>

            <?php if(!empty($item['attrs'])): ?>
              <div class="ck-item-attrs">
                <?php $__currentLoopData = $item['attrs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <span><?php echo e($k); ?>: <strong><?php echo e($v); ?></strong></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="ck-item-price"><?php echo e(number_format($item['price'] * $item['qty'], 2)); ?> EGP</div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <div class="ck-totals">
        <div class="summary-row"><span>Subtotal</span><span><?php echo e(number_format($subtotal, 2)); ?> EGP</span></div>
        <?php if($coupon && $discount > 0): ?>
          <div class="summary-row discount-row"><span>Coupon (<?php echo e($coupon['code']); ?>)</span><span>−<?php echo e(number_format($discount, 2)); ?> EGP</span></div>
        <?php endif; ?>
        <div class="summary-row"><span>Estimated Delivery</span><span>2–4 days</span></div>
        <div class="summary-row"><span>Shipping</span><span>Free</span></div>
        <div class="summary-divider"></div>
        <div class="summary-row total-row"><span>Total</span><span><?php echo e(number_format($total, 2)); ?> EGP</span></div>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
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
      useLocationBtn.addEventListener('click', () => {
        setStatus('Locating...');
        navigator.geolocation.getCurrentPosition((pos) => {
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
          setStatus('Could not detect your location. Please allow location access and try again.');
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
      });
    }
  }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/checkout.blade.php ENDPATH**/ ?>