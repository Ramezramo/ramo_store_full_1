<?php $__env->startSection('title', 'Order Confirmed — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page" style="max-width:700px;margin:0 auto">

  <div class="success-card">
    <div class="success-icon">✅</div>
    <h1 class="success-title">Order Placed!</h1>
    <p class="success-sub">Thank you for shopping with RamoStore. Your order has been received and is being processed.</p>
    <div class="success-badge">Order #<?php echo e($order->id); ?></div>
  </div>

  <div class="order-detail-card">
    <div class="od-row"><span class="od-label">Status</span><span class="status-badge status-<?php echo e($order->status); ?>"><?php echo e(ucfirst($order->status)); ?></span></div>
    <div class="od-row"><span class="od-label">Payment</span><span><?php echo e($order->payment_method_title); ?></span></div>
    <div class="od-row"><span class="od-label">Date</span><span><?php echo e(\Carbon\Carbon::parse($order->date_created)->format('M d, Y h:i A')); ?></span></div>
    <div class="od-row"><span class="od-label">Total</span><span class="od-total"><?php echo e(number_format($order->final_total, 2)); ?> EGP</span></div>
  </div>

  
  <?php if(isset($subOrders) && $subOrders->count() > 1): ?>
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">Your order ships in <?php echo e($subOrders->count()); ?> separate packages</h3>
      <p style="font-size:13px;color:#6b7280;margin-bottom:16px">Each vendor will ship independently. You'll receive tracking per shipment.</p>

      <?php $__currentLoopData = $subOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:12px">
          <div style="font-size:13px;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
            <span><?php echo e($sub->vendor_shop_name ?: 'Store'); ?></span>
            <span style="font-size:11px;color:#6b7280;font-weight:400">Sub-order #<?php echo e($sub->id); ?></span>
          </div>
          <?php $__currentLoopData = $sub->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #f3f4f6">
              <span><?php echo e($item['name']); ?> <span style="color:#6b7280">× <?php echo e($item['quantity']); ?></span></span>
              <span style="font-weight:600"><?php echo e(number_format($item['subtotal'],2)); ?> EGP</span>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;padding-top:8px;color:#e85d26">
            <span>Shipment Total</span>
            <span><?php echo e(number_format($sub->total,2)); ?> EGP</span>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

  <?php elseif(isset($subOrders) && $subOrders->count() === 1): ?>
    
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items Ordered</h3>
      <?php $__currentLoopData = $subOrders->first()->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="order-item-row">
          <div class="order-item-info">
            <span class="order-item-name"><?php echo e($item['name']); ?></span>
            <?php if(!empty($item['attributes'])): ?><span class="order-item-attr"><?php $__currentLoopData = $item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($k); ?>: <?php echo e($v); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></span><?php endif; ?>
          </div>
          <span class="order-item-qty">× <?php echo e($item['quantity']); ?></span>
          <span class="order-item-price"><?php echo e(number_format($item['subtotal'],2)); ?> EGP</span>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php else: ?>
    
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items Ordered</h3>
      <?php $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="order-item-row">
          <div class="order-item-info">
            <span class="order-item-name"><?php echo e($item['name']); ?></span>
            <?php if(!empty($item['attributes'])): ?><span class="order-item-attr"><?php $__currentLoopData = $item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($k); ?>: <?php echo e($v); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></span><?php endif; ?>
          </div>
          <span class="order-item-qty">× <?php echo e($item['quantity']); ?></span>
          <span class="order-item-price"><?php echo e(number_format($item['subtotal'],2)); ?> EGP</span>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>

  <?php if(auth()->guard()->guest()): ?>
  <?php
    $billing = json_decode($order->billing ?? '{}', true) ?? [];
    $guestEmail = $billing['email'] ?? '';
  ?>
  <div class="order-detail-card" style="margin-top:16px;background:linear-gradient(135deg,#f9fafb,#eff6ff);border-color:#bfdbfe">
    <div style="display:flex;align-items:flex-start;gap:14px">
      <div style="font-size:28px;line-height:1;flex-shrink:0">📧</div>
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e40af;margin-bottom:4px">Save your order details</div>
        <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:14px">
          You checked out as a guest. Note your order number <strong>#<?php echo e($order->id); ?></strong> — you can look it up anytime using your email address.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a href="<?php echo e(route('guest.order')); ?>?order_id=<?php echo e($order->id); ?>"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            🔍 Track This Order
          </a>
          <a href="<?php echo e(route('register')); ?>"
             style="display:inline-block;background:#fff;border:1.5px solid #1a1a1a;color:#1a1a1a;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            Create Account →
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="success-actions">
    <?php if(auth()->guard()->check()): ?>
      <a href="<?php echo e(route('account.orders')); ?>" class="btn btn-dark">View My Orders</a>
    <?php endif; ?>
    <a href="<?php echo e(route('shop')); ?>" class="btn btn-outline">Continue Shopping</a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/order-success.blade.php ENDPATH**/ ?>