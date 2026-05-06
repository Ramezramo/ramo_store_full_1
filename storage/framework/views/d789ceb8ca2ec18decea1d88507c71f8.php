<?php $__env->startSection('title', isset($order) && $order ? 'Order #'.$order->id.' — Details' : 'Look Up Your Order'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span>
    <strong>My Order</strong>
  </div>

  
  <div class="track-hero">
    <div class="track-form-card">
      <div class="track-icon">🛍️</div>
      <h1 class="track-title">Find Your Order</h1>
      <p class="track-sub">Enter your order number and the email address you used at checkout.</p>

      <?php if(session('error')): ?>
        <div class="track-error">
          <span>⚠</span> <?php echo e(session('error')); ?>

        </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('guest.order.lookup')); ?>" class="track-form">
        <?php echo csrf_field(); ?>
        <div class="track-fields">
          <div class="track-field">
            <label>Order Number</label>
            <input type="number" name="order_id" placeholder="e.g. 1042"
                   value="<?php echo e(old('order_id')); ?>"
                   min="1" required autofocus>
            <?php $__errorArgs = ['order_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div class="track-field">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@example.com"
                   value="<?php echo e(old('email')); ?>" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        <button type="submit" class="track-submit">Find My Order →</button>
      </form>

      <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--c-light);display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <?php if(auth()->guard()->check()): ?>
          <a href="<?php echo e(route('account.orders')); ?>" class="track-link" style="font-size:13px">📋 View all orders in your account</a>
        <?php else: ?>
          <a href="<?php echo e(route('login')); ?>" class="track-link" style="font-size:13px">🔐 Sign in to your account</a>
        <?php endif; ?>
        <a href="<?php echo e(route('order.track')); ?>" class="track-link" style="font-size:13px;color:var(--c-mid)">Track by phone number instead</a>
      </div>
    </div>
  </div>

  
  <?php if(isset($order) && $order): ?>
  <?php
    $status = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($order->status ?? 'pending');
    $steps  = ['pending','processing','shipped','completed'];
    $curIdx = array_search(strtolower($order->status ?? 'pending'), $steps);
    if ($curIdx === false) $curIdx = 0;
    $cancelled = in_array(strtolower($order->status ?? ''), ['cancelled','refunded','failed']);
  ?>

  <div class="order-result">

    
    <div class="or-header">
      <div>
        <h2 class="or-title">Order <span>#<?php echo e($order->id); ?></span></h2>
        <div class="or-date">Placed on <?php echo e($order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y, g:i A') : \Carbon\Carbon::parse($order->created_at)->format('d M Y')); ?></div>
      </div>
      <div class="or-status-pill" style="background:<?php echo e($status['bg']); ?>;color:<?php echo e($status['color']); ?>;border:1.5px solid <?php echo e($status['color']); ?>20">
        <?php echo e($status['icon']); ?> <?php echo e($status['label']); ?>

      </div>
    </div>

    
    <?php if(!$cancelled): ?>
    <div class="or-progress-wrap">
      <div class="or-progress">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $stepStatus = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($step);
          $done   = $i <= $curIdx;
          $active = $i === $curIdx;
        ?>
        <div class="or-step <?php echo e($done ? 'done' : ''); ?> <?php echo e($active ? 'active' : ''); ?>">
          <div class="or-step-circle"><?php echo e($done ? '✓' : ($i+1)); ?></div>
          <div class="or-step-label"><?php echo e($stepStatus['icon']); ?> <?php echo e($stepStatus['label']); ?></div>
        </div>
        <?php if($i < count($steps)-1): ?>
          <div class="or-step-line <?php echo e($i < $curIdx ? 'done' : ''); ?>"></div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php else: ?>
    <div class="or-cancelled-banner" style="background:<?php echo e($status['bg']); ?>;color:<?php echo e($status['color']); ?>">
      <?php echo e($status['icon']); ?> This order has been <strong><?php echo e($status['label']); ?></strong>.
      <?php if(strtolower($order->status) === 'refunded'): ?> A refund has been processed. <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="or-body">

      
      <div class="or-section">
        <div class="or-section-title">Order Items</div>
        <div class="or-items">
          <?php $__empty_1 = true; $__currentLoopData = $lineItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="or-item">
            <div class="or-item-img">
              <?php if($item['thumbnail'] ?? null): ?>
                <img src="<?php echo e($item['thumbnail']); ?>" alt="<?php echo e($item['name']); ?>" loading="lazy">
              <?php else: ?>
                <div class="or-item-placeholder">👕</div>
              <?php endif; ?>
            </div>
            <div class="or-item-info">
              <div class="or-item-name"><?php echo e($item['name']); ?></div>
              <?php if(!empty($item['attributes'])): ?>
                <div class="or-item-attrs">
                  <?php $__currentLoopData = (array)$item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e($attr); ?>: <strong><?php echo e($val); ?></strong></span>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              <?php endif; ?>
              <div class="or-item-meta">
                Qty: <strong><?php echo e($item['quantity']); ?></strong>
                &nbsp;·&nbsp;
                <?php echo e(number_format($item['price'] ?? 0, 2)); ?> EGP each
              </div>
            </div>
            <div class="or-item-total"><?php echo e(number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2)); ?> EGP</div>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div style="color:var(--c-mid);font-size:13px;padding:16px 0">No item details available.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="or-right-col">

        
        <div class="or-section">
          <div class="or-section-title">Order Summary</div>
          <div class="or-summary">
            <div class="or-summary-row">
              <span>Subtotal</span>
              <span><?php echo e(number_format($order->original_total ?? 0, 2)); ?> EGP</span>
            </div>
            <?php if(($order->discount_total ?? 0) > 0): ?>
            <div class="or-summary-row" style="color:#22a35c">
              <span>Discount <?php if($order->coupon_code): ?>(<code><?php echo e($order->coupon_code); ?></code>)<?php endif; ?></span>
              <span>−<?php echo e(number_format($order->discount_total, 2)); ?> EGP</span>
            </div>
            <?php endif; ?>
            <?php if(($order->shipping_total ?? 0) > 0): ?>
            <div class="or-summary-row">
              <span>Shipping</span>
              <span><?php echo e(number_format($order->shipping_total, 2)); ?> EGP</span>
            </div>
            <?php endif; ?>
            <div class="or-summary-row or-summary-total">
              <span>Total</span>
              <span><?php echo e(number_format($order->final_total ?? $order->original_total, 2)); ?> EGP</span>
            </div>
            <div class="or-summary-row" style="font-size:12px;color:var(--c-mid)">
              <span>Payment</span>
              <span><?php echo e($order->payment_method_title ?? ucfirst($order->payment_method ?? 'N/A')); ?></span>
            </div>
          </div>
        </div>

        
        <div class="or-section">
          <div class="or-section-title">Shipping Address</div>
          <div class="or-address">
            <?php $sh = $shipping ?? $billing ?? []; ?>
            <div class="or-address-name"><?php echo e(($sh['first_name'] ?? '') . ' ' . ($sh['last_name'] ?? '')); ?></div>
            <?php if($sh['address_1'] ?? null): ?><div><?php echo e($sh['address_1']); ?></div><?php endif; ?>
            <?php if($sh['city'] ?? null): ?><div><?php echo e($sh['city']); ?><?php if($sh['state'] ?? null): ?>, <?php echo e($sh['state']); ?><?php endif; ?></div><?php endif; ?>
            <?php if($sh['country'] ?? null): ?><div><?php echo e($sh['country']); ?></div><?php endif; ?>
            <?php if($sh['phone'] ?? null): ?><div style="margin-top:6px;font-weight:600">📞 <?php echo e($sh['phone']); ?></div><?php endif; ?>
            <?php if($sh['email'] ?? null): ?><div style="font-size:12px;color:var(--c-mid)">✉ <?php echo e($sh['email']); ?></div><?php endif; ?>
          </div>
        </div>

        
        <?php if($order->customer_note ?? null): ?>
        <div class="or-section">
          <div class="or-section-title">Your Note</div>
          <div class="or-note"><?php echo e($order->customer_note); ?></div>
        </div>
        <?php endif; ?>

        
        <?php if(auth()->guard()->guest()): ?>
        <div class="or-section" style="background:linear-gradient(135deg,#f9fafb,#eff6ff)">
          <div class="or-section-title" style="color:#1d4ed8">Create an Account</div>
          <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:12px">
            Save your details and track all your orders in one place.
          </p>
          <a href="<?php echo e(route('register')); ?>"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            Sign Up Free →
          </a>
        </div>
        <?php endif; ?>

      </div>
    </div>

    
    <div class="or-footer">
      <a href="<?php echo e(route('guest.order')); ?>" class="btn btn-outline" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Look Up Another Order</a>
      <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Continue Shopping</a>
    </div>
  </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
.track-hero{display:flex;justify-content:center;padding:20px 0 32px}
.track-form-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:40px 36px;width:100%;max-width:560px;text-align:center}
.track-icon{font-size:48px;margin-bottom:12px;line-height:1}
.track-title{font-size:22px;font-weight:800;margin-bottom:8px}
.track-sub{font-size:14px;color:var(--c-mid);margin-bottom:20px;line-height:1.6}
.track-error{background:#fff0f0;border:1.5px solid #fcc;border-radius:10px;padding:12px 16px;font-size:13.5px;color:#c0392b;margin-bottom:18px;text-align:left;display:flex;gap:8px;align-items:flex-start}
.track-form{text-align:left}
.track-fields{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.track-field label{display:block;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--c-mid);margin-bottom:7px}
.track-field input{width:100%;padding:11px 14px;border:1.5px solid var(--c-light);border-radius:10px;font-size:14px;font-family:inherit;outline:none;background:var(--c-bg);color:var(--c-dark)}
.track-field input:focus{border-color:#aaa;background:var(--c-white)}
.field-err{font-size:12px;color:#e02020;display:block;margin-top:4px}
.track-submit{width:100%;padding:13px;background:var(--c-dark);color:#fff;border:none;border-radius:11px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s;letter-spacing:.2px}
.track-submit:hover{background:#333}
.track-link{color:var(--c-orange);font-weight:600;text-decoration:none}

.order-result{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:32px}
.or-header{display:flex;align-items:flex-start;justify-content:space-between;padding:24px 28px;border-bottom:1.5px solid var(--c-light);flex-wrap:wrap;gap:12px}
.or-title{font-size:20px;font-weight:800}
.or-title span{color:var(--c-orange)}
.or-date{font-size:13px;color:var(--c-mid);margin-top:4px}
.or-status-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:50px;font-size:13px;font-weight:700}
.or-progress-wrap{padding:28px 28px 20px;border-bottom:1.5px solid var(--c-light)}
.or-progress{display:flex;align-items:flex-start;justify-content:center;gap:0}
.or-step{display:flex;flex-direction:column;align-items:center;gap:8px;flex:0 0 auto;min-width:100px}
.or-step-circle{width:36px;height:36px;border-radius:50%;background:var(--c-light);color:var(--c-mid);font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:all .25s;border:2px solid var(--c-light);z-index:1}
.or-step.done .or-step-circle{background:var(--c-dark);color:#fff;border-color:var(--c-dark)}
.or-step.active .or-step-circle{background:var(--c-orange);color:#fff;border-color:var(--c-orange);box-shadow:0 0 0 4px rgba(232,93,38,.15)}
.or-step-label{font-size:11.5px;color:var(--c-mid);text-align:center;font-weight:500;line-height:1.3}
.or-step.done .or-step-label,.or-step.active .or-step-label{color:var(--c-dark);font-weight:700}
.or-step-line{flex:1;height:2px;background:var(--c-light);margin-top:17px;min-width:30px;transition:background .25s}
.or-step-line.done{background:var(--c-dark)}
.or-cancelled-banner{margin:20px 28px;border-radius:10px;padding:14px 18px;font-size:14px;display:flex;align-items:center;gap:8px}
.or-body{display:grid;grid-template-columns:1fr 280px;gap:0;align-items:start}
.or-section{padding:24px 28px;border-bottom:1.5px solid var(--c-light)}
.or-section:last-child{border-bottom:none}
.or-right-col{border-left:1.5px solid var(--c-light)}
.or-right-col .or-section{border-bottom:1.5px solid var(--c-light)}
.or-right-col .or-section:last-child{border-bottom:none}
.or-section-title{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:16px}
.or-items{display:flex;flex-direction:column;gap:12px}
.or-item{display:flex;align-items:flex-start;gap:14px;padding:12px;background:var(--c-bg);border-radius:10px}
.or-item-img{width:60px;height:60px;border-radius:8px;overflow:hidden;background:var(--c-light);flex-shrink:0}
.or-item-img img{width:100%;height:100%;object-fit:cover}
.or-item-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:24px;background:var(--c-light)}
.or-item-info{flex:1;min-width:0}
.or-item-name{font-size:13.5px;font-weight:600;margin-bottom:4px;line-height:1.3}
.or-item-attrs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px}
.or-item-attrs span{font-size:11.5px;background:var(--c-white);border:1px solid var(--c-light);padding:2px 7px;border-radius:5px;color:var(--c-mid)}
.or-item-meta{font-size:12.5px;color:var(--c-mid)}
.or-item-total{font-size:14px;font-weight:700;white-space:nowrap;flex-shrink:0}
.or-summary{display:flex;flex-direction:column;gap:9px}
.or-summary-row{display:flex;justify-content:space-between;font-size:13.5px;align-items:baseline}
.or-summary-row code{background:var(--c-bg);padding:1px 6px;border-radius:4px;font-size:12px}
.or-summary-total{border-top:1.5px solid var(--c-light);padding-top:9px;font-weight:800;font-size:15px}
.or-address{font-size:13.5px;color:var(--c-mid);line-height:1.7}
.or-address-name{font-weight:700;color:var(--c-dark);margin-bottom:2px}
.or-note{font-size:13.5px;color:var(--c-mid);line-height:1.6;background:var(--c-bg);padding:12px;border-radius:8px;font-style:italic}
.or-footer{padding:20px 28px;display:flex;gap:12px;flex-wrap:wrap;border-top:1.5px solid var(--c-light)}

@media(max-width:700px){
  .track-fields{grid-template-columns:1fr}
  .or-body{grid-template-columns:1fr}
  .or-right-col{border-left:none;border-top:1.5px solid var(--c-light)}
  .or-progress{gap:0;overflow-x:auto;padding-bottom:8px;justify-content:flex-start}
  .or-step{min-width:80px}
  .track-form-card{padding:28px 20px}
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/guest-order-lookup.blade.php ENDPATH**/ ?>