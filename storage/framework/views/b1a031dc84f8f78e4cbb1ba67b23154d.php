<?php
  $pageTitle = 'Order #'.$order->id;
  $cancelled = in_array($order->status, ['cancelled','refunded','failed']);
  $hasSubOrders = isset($subOrders) && $subOrders->count() > 0;
  $messageCount = 0;
  if ($hasSubOrders) {
    foreach ($subOrders as $s) {
      $messageCount += isset($s->messages) ? count($s->messages) : 0;
    }
  }
?>

<?php $__env->startSection('account-content'); ?>
<div class="acc-section-title" style="margin-bottom:20px">Order #<?php echo e($order->id); ?></div>

<?php if($cancelled): ?>
  <div class="acc-alert acc-alert-error" style="margin-bottom:24px">This order has been <strong><?php echo e(ucfirst($order->status)); ?></strong>.</div>
<?php endif; ?>

<?php if(session('success')): ?>
  <div class="acc-alert acc-alert-success" style="margin-bottom:16px"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
  <div class="acc-alert acc-alert-error" style="margin-bottom:16px"><?php echo e(session('error')); ?></div>
<?php endif; ?>


<div class="order-detail-card">
  <div class="od-row"><span class="od-label">Order #</span><strong>#<?php echo e($order->id); ?></strong></div>
  <div class="od-row"><span class="od-label">Status</span><span class="status-badge status-<?php echo e($order->status); ?>"><?php echo e(ucfirst($order->status)); ?></span></div>
  <div class="od-row"><span class="od-label">Date</span><span><?php echo e(\Carbon\Carbon::parse($order->date_created)->format('M d, Y h:i A')); ?></span></div>
  <div class="od-row"><span class="od-label">Payment</span><span><?php echo e($order->payment_method_title); ?></span></div>
  <div class="od-row"><span class="od-label">Total Paid</span><strong style="color:#e85d26"><?php echo e(number_format($order->final_total, 2)); ?> EGP</strong></div>
  <?php if($order->customer_note): ?>
    <div class="od-row"><span class="od-label">Notes</span><span><?php echo e($order->customer_note); ?></span></div>
  <?php endif; ?>
</div>


<?php if(!empty($billing)): ?>
<div class="order-detail-card" style="margin-top:16px">
  <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Shipping Address</h3>
  <p style="font-size:14px;line-height:1.8;color:var(--c-dark)">
    <?php echo e($billing['first_name'] ?? ''); ?> <?php echo e($billing['last_name'] ?? ''); ?><br>
    <?php echo e($billing['address_1'] ?? ''); ?><br>
    <?php echo e($billing['city'] ?? ''); ?><?php if(!empty($billing['state'])): ?>, <?php echo e($billing['state']); ?><?php endif; ?><br>
    <?php echo e($billing['phone'] ?? ''); ?>

    <?php if(!empty($billing['latitude']) && !empty($billing['longitude'])): ?>
      <br>
      <iframe
        width="100%"
        height="220"
        style="border:0;border-radius:12px;margin-top:10px"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps?q=<?php echo e($billing['latitude']); ?>,<?php echo e($billing['longitude']); ?>&z=15&output=embed">
      </iframe>
    <?php endif; ?>
  </p>
</div>
<?php endif; ?>




<?php if($hasSubOrders): ?>

  <?php $__currentLoopData = $subOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $subStepIndex = match($sub->status) {
        'pending'    => 0,
        'processing' => 1,
        'shipped'    => 2,
        'completed'  => 3,
        default      => 0,
      };
      $subCancelled = in_array($sub->status, ['cancelled']);
      $subFillPct   = $subCancelled ? 0 : match($subStepIndex) { 0=>0,1=>33,2=>66,3=>100,default=>0 };
      $subSteps     = ['pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','completed'=>'Delivered'];
    ?>

    <div style="margin-top:20px;border:2px solid #e5e7eb;border-radius:16px;overflow:hidden">

      
      <div style="background:#f9fafb;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb;flex-wrap:wrap;gap:8px">
        <div>
          <div style="font-size:13px;font-weight:800;color:#111827">
            <?php echo e($sub->vendor_shop_name ?: 'Store'); ?>

            <span style="font-weight:400;color:#6b7280;font-size:12px">— Sub-order #<?php echo e($sub->id); ?></span>
          </div>
          <?php if($sub->tracking_number): ?>
            <div style="font-size:12px;color:#6b7280;margin-top:3px">
              Tracking: <strong style="font-family:monospace"><?php echo e($sub->tracking_number); ?></strong>
              <?php if($sub->tracking_carrier): ?> via <?php echo e($sub->tracking_carrier); ?> <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <span class="status-badge status-<?php echo e($sub->status); ?>" style="font-size:12px"><?php echo e(ucfirst($sub->status)); ?></span>
      </div>

      
      <?php if(!$subCancelled): ?>
      <div style="padding:16px 18px 4px;background:#fff">
        <div style="position:relative;display:flex;justify-content:space-between;margin-bottom:20px">
          <div style="position:absolute;top:14px;left:0;right:0;height:3px;background:#e5e7eb;z-index:0"></div>
          <div style="position:absolute;top:14px;left:0;height:3px;background:#e85d26;z-index:1;width:<?php echo e($subFillPct); ?>%;transition:width .4s"></div>
          <?php $__currentLoopData = $subSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sKey => $sLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $si = array_search($sKey, array_keys($subSteps));
              $sCls = $si < $subStepIndex ? 'done' : ($si === $subStepIndex ? 'current' : '');
            ?>
            <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:4px;flex:1">
              <div style="width:28px;height:28px;border-radius:50%;border:3px solid <?php echo e($sCls==='done'?'#e85d26':($sCls==='current'?'#e85d26':'#e5e7eb')); ?>;background:<?php echo e($sCls==='done'?'#e85d26':($sCls==='current'?'#fff7ed':'#fff')); ?>;display:flex;align-items:center;justify-content:center">
                <?php if($sCls === 'done'): ?>
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                <?php elseif($sCls === 'current'): ?>
                  <div style="width:8px;height:8px;border-radius:50%;background:#e85d26"></div>
                <?php endif; ?>
              </div>
              <div style="font-size:10px;font-weight:600;color:<?php echo e($sCls?'#111827':'#6b7280'); ?>;text-align:center;max-width:60px"><?php echo e($sLabel); ?></div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
      <?php else: ?>
      <div style="padding:10px 18px;background:#fff;font-size:13px;color:#ef4444;font-weight:600">This shipment was cancelled.</div>
      <?php endif; ?>

      
      <div style="padding:0 18px 16px;background:#fff">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:10px">Items</div>
        <?php $__currentLoopData = $sub->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f3f4f6">
            <div style="flex:1">
              <a href="<?php echo e(route('product', $item['product_id'])); ?>" style="font-weight:600;font-size:13px;color:#111827;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px"><?php echo e($item['name']); ?></a>
              <?php if(!empty($item['attributes'])): ?>
                <div style="font-size:11px;color:#6b7280;margin-top:2px">
                  <?php $__currentLoopData = $item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($k); ?>: <?php echo e($v); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              <?php endif; ?>
            </div>
            <div style="font-size:12px;color:#6b7280;white-space:nowrap">× <?php echo e($item['quantity']); ?></div>
            <div style="font-size:13px;font-weight:700;white-space:nowrap;color:#111827"><?php echo e(number_format($item['subtotal'], 2)); ?> EGP</div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div style="display:flex;justify-content:space-between;padding-top:12px;font-size:13px">
          <span style="color:#6b7280">Sub-total</span>
          <strong><?php echo e(number_format($sub->subtotal, 2)); ?> EGP</strong>
        </div>
        <?php if($sub->discount_total > 0): ?>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:4px">
            <span style="color:#22c55e">Discount</span>
            <strong style="color:#22c55e">−<?php echo e(number_format($sub->discount_total, 2)); ?> EGP</strong>
          </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:800;margin-top:8px;padding-top:8px;border-top:2px solid #e5e7eb">
          <span>Vendor Total</span>
          <span style="color:#e85d26"><?php echo e(number_format($sub->total, 2)); ?> EGP</span>
        </div>
      </div>

      
      <div style="padding:16px 18px;background:#fafafa;border-top:1px solid #e5e7eb">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:12px">
          Messages with <?php echo e($sub->vendor_shop_name ?: 'Vendor'); ?>

        </div>

        <?php if(isset($sub->messages) && count($sub->messages) > 0): ?>
          <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
            <?php $__currentLoopData = $sub->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div style="border:1px solid <?php echo e($msg->is_vendor_response ? '#fdba74' : '#e5e7eb'); ?>;background:<?php echo e($msg->is_vendor_response ? '#fff7ed' : '#fff'); ?>;border-radius:10px;padding:10px 13px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                  <strong style="color:<?php echo e($msg->is_vendor_response ? '#e85d26' : '#111827'); ?>">
                    <?php echo e($msg->is_vendor_response ? ($msg->vendor_shop_name ?: 'Vendor') : 'You'); ?>

                  </strong>
                  <span style="color:#6b7280"><?php echo e(\Carbon\Carbon::parse($msg->created_at)->format('d M Y, g:i A')); ?></span>
                </div>
                <div style="font-size:13px;line-height:1.7;color:#111827"><?php echo e($msg->message); ?></div>
                <?php if($msg->is_vendor_response): ?>
                  <div style="margin-top:6px;display:inline-block;background:rgba(232,93,38,.12);color:#e85d26;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px">Vendor</div>
                <?php endif; ?>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php else: ?>
          <div style="color:#6b7280;font-size:13px;margin-bottom:12px">No messages yet. Ask a question below.</div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('account.order.messages.store', $order->id)); ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="sub_order_id" value="<?php echo e($sub->id); ?>">
          <textarea name="message" rows="3" placeholder="Message <?php echo e($sub->vendor_shop_name ?: 'Vendor'); ?>..." style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
          <div style="margin-top:8px">
            <button type="submit" class="btn btn-dark" style="font-size:12px;padding:8px 16px">Send Message</button>
          </div>
        </form>
      </div>

      
      <?php if(in_array($sub->status, ['completed','shipped','processing'])): ?>
        <div style="padding:12px 18px;background:#fff;border-top:1px solid #e5e7eb">
          <a href="<?php echo e(route('account.refunds.create', ['order_id' => $order->id])); ?>" style="font-size:12px;color:#e85d26;text-decoration:none;font-weight:600">⚠ Request Refund / Return for this shipment →</a>
        </div>
      <?php endif; ?>

    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php else: ?>
  
  <div class="order-detail-card" style="margin-top:16px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items</h3>
    <?php $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="order-item-row">
      <div class="order-item-info">
        <a href="<?php echo e(route('product', $item['product_id'])); ?>" class="order-item-name"><?php echo e($item['name']); ?></a>
        <?php if(!empty($item['attributes'])): ?>
          <span class="order-item-attr"><?php $__currentLoopData = $item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($k); ?>: <?php echo e($v); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></span>
        <?php endif; ?>
      </div>
      <span class="order-item-qty">× <?php echo e($item['quantity']); ?></span>
      <span class="order-item-price"><?php echo e(number_format($item['subtotal'], 2)); ?> EGP</span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div class="ck-totals" style="margin-top:16px">
      <div class="summary-row"><span>Subtotal</span><span><?php echo e(number_format($order->original_total, 2)); ?> EGP</span></div>
      <?php if($order->discount_total > 0): ?>
        <div class="summary-row discount-row"><span>Discount</span><span>−<?php echo e(number_format($order->discount_total, 2)); ?> EGP</span></div>
      <?php endif; ?>
      <div class="summary-divider"></div>
      <div class="summary-row total-row"><span>Total</span><span><?php echo e(number_format($order->final_total, 2)); ?> EGP</span></div>
    </div>
  </div>
<?php endif; ?>

<div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
  <a href="<?php echo e(route('account.orders')); ?>" class="btn btn-outline">← Back to Orders</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/order-detail.blade.php ENDPATH**/ ?>