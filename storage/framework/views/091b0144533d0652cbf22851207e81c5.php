<?php $__env->startSection('title', 'Cart — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <?php if(session('error')): ?>
    <div class="alert-box alert-err"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <?php if(empty($cart)): ?>
    <div class="empty" style="padding:100px 20px">
      <div class="empty-icon">🛒</div>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="margin-top:24px">Start Shopping</a>
    </div>
  <?php else: ?>
  <a href="<?php echo e(route('shop')); ?>" class="cart-back-link">← Back</a>
  <div class="cart-title-row">
    <h1 class="cart-title">Your Cart</h1>
    <span class="cart-count-badge"><?php echo e(count($cart)); ?> Item<?php echo e(count($cart) === 1 ? '' : 's'); ?></span>
  </div>

  <div class="cart-layout">

    
    <div id="cart-items-wrap">

      <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowId => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="cart-row" id="row-<?php echo e($rowId); ?>">
        <div class="cart-prod">
          <?php if($item['image']): ?>
            <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
          <?php else: ?>
            <div class="cart-img-placeholder">👕</div>
          <?php endif; ?>
          <div class="cart-prod-info">
            <a href="<?php echo e(route('product', $item['product_id'])); ?>" class="cart-name"><?php echo e($item['name']); ?></a>
            <?php if(!empty($item['sku'])): ?>
              <div class="cart-model">Model: <?php echo e($item['sku']); ?></div>
            <?php endif; ?>
            <?php if(!empty($item['attrs'])): ?>
              <?php $__currentLoopData = $item['attrs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="cart-attr-line"><span><?php echo e(ucfirst($k)); ?>:</span> <?php echo e($v); ?></div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <div class="cart-row-actions">
              <div class="qty-pill">
                <button type="button" onclick="updateQty('<?php echo e($rowId); ?>', -1)">−</button>
                <input type="number" id="qty-<?php echo e($rowId); ?>" value="<?php echo e($item['qty']); ?>" min="1" max="<?php echo e($item['stock']); ?>" onchange="setQty('<?php echo e($rowId); ?>', this.value)">
                <button type="button" onclick="updateQty('<?php echo e($rowId); ?>', 1)">+</button>
              </div>
              <button class="cart-remove-link" onclick="removeItem('<?php echo e($rowId); ?>')">Remove</button>
            </div>
          </div>
        </div>

        <div class="cart-row-price">
          <div class="cart-sub" id="sub-<?php echo e($rowId); ?>"><?php echo e(number_format($item['price'] * $item['qty'], 2)); ?> EGP</div>
          <div class="cart-sub-old" id="sub-old-<?php echo e($rowId); ?>" style="<?php echo e((!empty($item['regular_price']) && $item['regular_price'] > $item['price']) ? '' : 'display:none'); ?>">
            <?php echo e(!empty($item['regular_price']) ? number_format($item['regular_price'] * $item['qty'], 2) : ''); ?> EGP
          </div>
        </div>
      </div>
      <div class="cart-row-divider"></div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <div class="cart-actions">
        <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline">← Continue Shopping</a>
        <form action="<?php echo e(route('cart.clear')); ?>" method="POST" style="display:inline">
          <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
          <button class="btn btn-outline" style="color:#e02020;border-color:#e02020" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
        </form>
      </div>
    </div>

    
    <div class="cart-summary">
      <h3>Cart Summary</h3>

      <?php if($coupon): ?>
        <div class="applied-coupon-row">
          <span>Coupon: <strong><?php echo e($coupon['code']); ?></strong></span>
          <form action="<?php echo e(route('cart.coupon.remove')); ?>" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button class="coupon-remove-btn">Remove ✕</button>
          </form>
        </div>
      <?php else: ?>
        <div class="coupon-box">
          <span class="coupon-icon">🏷️</span>
          <input type="text" id="coupon-input" placeholder="Add a promo code" class="coupon-input">
          <button onclick="applyCoupon()" class="coupon-apply-btn">Apply</button>
        </div>
        <div id="coupon-msg" style="font-size:13px;margin-top:6px"></div>
      <?php endif; ?>

      <div class="summary-divider"></div>

      <div class="summary-row"><span>Subtotal</span><span id="cart-subtotal"><?php echo e(number_format($subtotal, 2)); ?> EGP</span></div>
      <div class="summary-row"><span>Standard shipping</span><span>Free</span></div>

      <?php if($coupon): ?>
        <div class="summary-row discount-row">
          <span>Coupon (<?php echo e($coupon['code']); ?>)</span>
          <span>−<?php echo e(number_format($discount, 2)); ?> EGP</span>
        </div>
      <?php endif; ?>

      <div class="summary-row"><span>Sales Tax</span><span>TBA</span></div>

      <div class="summary-divider"></div>
      <div class="summary-row total-row">
        <span>Estimated Total</span>
        <span id="cart-total"><?php echo e(number_format($total, 2)); ?> EGP</span>
      </div>

      <a href="<?php echo e(route('checkout')); ?>" class="btn checkout-btn">Checkout</a>
      <div class="payment-icons">
        <span title="Cash on Delivery">💵 COD</span>
        <span title="Vodafone Cash">📱 Vodafone Cash</span>
        <span title="Credit Card">💳 Card</span>
      </div>
    </div>

  </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const CSRF = '<?php echo e(csrf_token()); ?>';

async function updateQty(rowId, delta) {
  const input = document.getElementById('qty-' + rowId);
  const newVal = Math.max(1, parseInt(input.value) + delta);
  input.value = newVal;
  await setQty(rowId, newVal);
}

async function setQty(rowId, val) {
  const res = await fetch(`/cart/update/${rowId}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
    body: JSON.stringify({ qty: parseInt(val) })
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('sub-' + rowId).textContent = data.item_subtotal + ' EGP';
    const oldEl = document.getElementById('sub-old-' + rowId);
    if (oldEl) {
      if (data.item_subtotal_old) {
        oldEl.textContent = data.item_subtotal_old + ' EGP';
        oldEl.style.display = '';
      } else {
        oldEl.style.display = 'none';
      }
    }
    document.getElementById('cart-subtotal').textContent = data.cart_subtotal + ' EGP';
    document.getElementById('cart-total').textContent = data.cart_total + ' EGP';
    updateNavCount(data.count);
  }
}

async function removeItem(rowId) {
  const res = await fetch(`/cart/remove/${rowId}`, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': CSRF}
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('row-' + rowId).remove();
    document.getElementById('cart-subtotal').textContent = data.cart_subtotal + ' EGP';
    document.getElementById('cart-total').textContent = data.cart_total + ' EGP';
    updateNavCount(data.count);
    if (data.count === 0) location.reload();
  }
}

async function applyCoupon(code) {
  const input = document.getElementById('coupon-input');
  const msg   = document.getElementById('coupon-msg');
  if (!input) return;
  const useCode = (code || input.value).trim();
  if (!useCode) return;
  input.value = useCode;
  const res = await fetch('/cart/coupon', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
    body: JSON.stringify({ code: useCode })
  });
  const data = await res.json();
  msg.textContent = data.message;
  msg.style.color = data.success ? '#22a35c' : '#e02020';
  if (data.reload) setTimeout(() => location.reload(), 800);
}

(function autoApplyPendingCoupon() {
  try {
    const code = localStorage.getItem('pending_coupon');
    if (!code) return;
    localStorage.removeItem('pending_coupon');
    const input = document.getElementById('coupon-input');
    if (!input) return;
    input.value = code;
    const notice = document.getElementById('coupon-msg');
    if (notice) { notice.textContent = 'Applying coupon "' + code + '"…'; notice.style.color = '#7c3aed'; }
    setTimeout(() => applyCoupon(code), 400);
  } catch(e) {}
})();

function updateNavCount(n) {
  const badge = document.getElementById('cart-badge');
  if (badge) badge.textContent = n;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/cart.blade.php ENDPATH**/ ?>