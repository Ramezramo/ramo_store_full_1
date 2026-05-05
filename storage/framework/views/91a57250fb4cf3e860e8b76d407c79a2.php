<?php $__env->startSection('title', 'Sub-Order #'.$subOrder->id.' — Order #'.$order->id); ?>
<?php $__env->startSection('page-title', 'Order Detail'); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root{--orange:#f97316;--red:#ef4444;--green:#22c55e;--mid:#6b7280;--light:#e5e7eb;--dark:#111827;--yellow:#f59e0b}
.od-grid{display:grid;grid-template-columns:1fr 340px;gap:16px}
@media(max-width:900px){.od-grid{grid-template-columns:1fr}}
.od-card{background:#fff;border:1px solid var(--light);border-radius:14px;margin-bottom:16px;overflow:hidden}
.od-card-head{padding:14px 20px;border-bottom:1px solid var(--light);background:#fafafa;display:flex;align-items:center;justify-content:space-between}
.od-card-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--dark)}
.od-card-body{padding:18px 20px}
.dr{display:grid;grid-template-columns:130px 1fr;gap:6px 12px;font-size:13px;margin-bottom:6px}
.dr-label{color:var(--mid);font-weight:500}
.dr-value{color:var(--dark);font-weight:500}
.badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700}
.s-pending{background:#fef3c7;color:#92400e}.s-processing{background:#dbeafe;color:#1e40af}.s-shipped{background:#f3e8ff;color:#6b21a8}.s-completed{background:#dcfce7;color:#166534}.s-cancelled{background:#fee2e2;color:#991b1b}.s-on-hold,.s-refunded{background:#f3f4f6;color:var(--mid)}
.item-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f3f4f6}.item-row:last-child{border-bottom:none}
.item-thumb{width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid var(--light);flex-shrink:0;background:#f3f4f6;display:flex;align-items:center;justify-content:center}
.item-info{flex:1;min-width:0}.item-name{font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.item-attr{font-size:11px;color:var(--mid);margin-top:2px}.item-price{text-align:right;font-size:13px;font-weight:700;flex-shrink:0}
.sf-select{padding:8px 12px;border:1px solid var(--light);border-radius:8px;font-size:13px;outline:none;flex:1;min-width:140px}.sf-select:focus{border-color:var(--orange)}
.sf-note{width:100%;padding:8px 12px;border:1px solid var(--light);border-radius:8px;font-size:12px;resize:none;outline:none;min-height:50px;margin-top:6px}.sf-note:focus{border-color:var(--orange)}
.tl-item{display:flex;gap:10px;margin-bottom:12px}.tl-dot{width:10px;height:10px;border-radius:50%;background:var(--orange);flex-shrink:0;margin-top:4px}.tl-body{flex:1}
.tl-status{font-size:12px;font-weight:700;color:var(--dark);text-transform:capitalize}.tl-note{font-size:11px;color:var(--mid);margin-top:2px}.tl-time{font-size:11px;color:var(--mid)}
.msg{border:1px solid var(--light);border-radius:12px;padding:12px 14px;background:#fff;margin-bottom:10px}
.msg.vendor{background:#fff7ed;border-color:#fdba74}
.msg-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:8px;font-size:12px}
.msg-role-vendor{color:var(--orange);font-weight:800}.msg-role-customer{color:var(--dark);font-weight:800}
.msg-body{font-size:13px;line-height:1.7;color:var(--dark)}
.msg-badge{display:inline-block;margin-top:8px;background:rgba(249,115,22,.12);color:var(--orange);font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px}
.vs-alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:16px}
.vs-alert-err{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:16px}
.ot-wrap{position:relative;display:flex;justify-content:space-between;margin-bottom:24px}
.ot-line{position:absolute;top:14px;left:0;right:0;height:3px;background:var(--light);z-index:0}
.ot-fill{position:absolute;top:14px;left:0;height:3px;background:var(--orange);z-index:1;transition:width .4s}
.ot-step{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:4px;flex:1}
.ot-dot{width:28px;height:28px;border-radius:50%;border:3px solid var(--light);background:#fff;display:flex;align-items:center;justify-content:center;transition:.2s}
.ot-step.done .ot-dot{background:var(--orange);border-color:var(--orange);color:#fff}
.ot-step.current .ot-dot{border-color:var(--orange);background:#fff7ed;color:var(--orange)}
.ot-label{font-size:10px;font-weight:600;color:var(--mid);text-align:center;max-width:60px}
.ot-step.done .ot-label,.ot-step.current .ot-label{color:var(--dark)}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
  <a href="<?php echo e(route('vendor.orders')); ?>" style="color:var(--mid);font-size:13px;text-decoration:none">← All Orders</a>
</div>

<?php if(session('success')): ?><div class="vs-alert-ok"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if(session('error')): ?><div class="vs-alert-err"><?php echo e(session('error')); ?></div><?php endif; ?>


<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:8px">
  <div>
    <div style="font-size:22px;font-weight:800">Sub-Order #<?php echo e($subOrder->id); ?></div>
    <div style="font-size:13px;color:var(--mid);margin-top:3px">
      Part of Order <strong>#<?php echo e($order->id); ?></strong>
      · Placed <?php echo e(\Carbon\Carbon::parse($order->created_at)->format('d M Y, g:i A')); ?>

      · <?php echo e($order->payment_method_title); ?>

    </div>
  </div>
  <span class="badge s-<?php echo e($subOrder->status); ?>" style="font-size:13px;padding:5px 16px"><?php echo e(ucfirst($subOrder->status)); ?></span>
</div>

<?php if($subOrder->tracking_number): ?>
  <div style="margin-bottom:16px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;font-size:13px;font-weight:600;color:#1e40af">
    🚚 Tracking: <span style="font-family:monospace"><?php echo e($subOrder->tracking_number); ?></span>
    <?php if($subOrder->tracking_carrier): ?> via <?php echo e($subOrder->tracking_carrier); ?> <?php endif; ?>
  </div>
<?php endif; ?>


<?php
  $steps = ['pending'=>'Received','processing'=>'Processing','shipped'=>'Shipped','completed'=>'Delivered'];
  $cancelled = in_array($subOrder->status, ['cancelled']);
  $stepKeys = array_keys($steps);
  $curIdx = array_search($subOrder->status, $stepKeys);
  if ($curIdx === false) $curIdx = 0;
  $fillPct = $cancelled ? 0 : match($curIdx){0=>0,1=>33,2=>67,3=>100,default=>0};
?>
<div class="od-card" style="margin-bottom:20px">
  <div class="od-card-head"><span class="od-card-title">Fulfillment Progress</span></div>
  <div class="od-card-body">
    <?php if($cancelled): ?>
      <div style="text-align:center;padding:10px;font-size:13px;color:var(--red);font-weight:600">This sub-order is cancelled.</div>
    <?php else: ?>
      <div class="ot-wrap">
        <div class="ot-line"></div><div class="ot-fill" style="width:<?php echo e($fillPct); ?>%"></div>
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $idx = array_search($key, $stepKeys); ?>
          <div class="ot-step <?php echo e($idx < $curIdx ? 'done' : ($idx === $curIdx ? 'current' : '')); ?>">
            <div class="ot-dot">
              <?php if($idx < $curIdx): ?><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              <?php elseif($idx === $curIdx): ?><div style="width:8px;height:8px;border-radius:50%;background:var(--orange)"></div><?php endif; ?>
            </div>
            <div class="ot-label"><?php echo e($label); ?></div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="od-grid">
  <div>

    
    <div class="od-card">
      <div class="od-card-head">
        <span class="od-card-title">Your Items (<?php echo e(count($vendorItems)); ?>)</span>
        <span style="font-size:13px;font-weight:700;color:var(--orange)"><?php echo e(number_format($vendorTotal,2)); ?> EGP</span>
      </div>
      <div class="od-card-body">
        <?php $__currentLoopData = $vendorItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="item-row">
            <div class="item-thumb">
              <?php if(!empty($item['thumbnail'])): ?>
                <img src="<?php echo e($item['thumbnail']); ?>" style="width:52px;height:52px;border-radius:8px;object-fit:cover" alt="">
              <?php else: ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <?php endif; ?>
            </div>
            <div class="item-info">
              <div class="item-name"><?php echo e($item['name']); ?></div>
              <?php if(!empty($item['attributes'])): ?>
                <div class="item-attr">
                  <?php $__currentLoopData = $item['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($k); ?>: <?php echo e($v); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              <?php endif; ?>
              <div style="font-size:11px;color:var(--mid);margin-top:2px"><?php echo e(number_format($item['price'],2)); ?> EGP × <?php echo e($item['quantity']); ?></div>
            </div>
            <div class="item-price"><?php echo e(number_format($item['subtotal'],2)); ?> EGP</div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div style="border-top:2px solid var(--light);margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;font-weight:700;font-size:14px">
          <span>Subtotal</span><span><?php echo e(number_format($subOrder->subtotal,2)); ?> EGP</span>
        </div>
        <?php if($subOrder->discount_total > 0): ?>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:4px;color:var(--green)">
            <span>Discount applied</span><span>−<?php echo e(number_format($subOrder->discount_total,2)); ?> EGP</span>
          </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;margin-top:8px;color:var(--orange)">
          <span>Your Total</span><span><?php echo e(number_format($vendorTotal,2)); ?> EGP</span>
        </div>
      </div>
    </div>

    
    <?php if(!$cancelled): ?>
    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Update Fulfillment Status</span></div>
      <div class="od-card-body">
        <form method="POST" action="<?php echo e(route('vendor.orders.status', $subOrder->id)); ?>">
          <?php echo csrf_field(); ?>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <select name="status" class="sf-select">
              <?php $__currentLoopData = ['processing'=>'Processing — Preparing order','shipped'=>'Shipped — Handed to courier','completed'=>'Completed — Delivered','cancelled'=>'Cancel this shipment']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e($subOrder->status===$val?'selected':''); ?>><?php echo e($lbl); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="vs-btn vs-btn-primary" style="padding:8px 18px;font-size:13px;white-space:nowrap">Update Status</button>
          </div>
          <textarea name="note" class="sf-note" placeholder="Optional note (e.g. tracking number, courier name)…"></textarea>
          <div style="display:flex;gap:8px;margin-top:8px">
            <input type="text" name="tracking_number" value="<?php echo e($subOrder->tracking_number); ?>" placeholder="Tracking #" style="flex:1;padding:7px 11px;border:1px solid var(--light);border-radius:8px;font-size:12px;outline:none">
            <input type="text" name="tracking_carrier" value="<?php echo e($subOrder->tracking_carrier); ?>" placeholder="Carrier (e.g. Aramex)" style="flex:1;padding:7px 11px;border:1px solid var(--light);border-radius:8px;font-size:12px;outline:none">
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    
    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Customer Messages</span></div>
      <div class="od-card-body">
        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="msg <?php echo e($msg->sender_type === 'vendor' ? 'vendor' : ''); ?>">
            <div class="msg-head">
              <span class="<?php echo e($msg->sender_type === 'vendor' ? 'msg-role-vendor' : 'msg-role-customer'); ?>"><?php echo e($msg->sender_type === 'vendor' ? ($msg->vendor_shop_name ?: 'You (Vendor)') : 'Customer'); ?></span>
              <span style="color:var(--mid)"><?php echo e(\Carbon\Carbon::parse($msg->created_at)->format('d M Y, g:i A')); ?></span>
            </div>
            <div class="msg-body"><?php echo e($msg->message); ?></div>
            <?php if($msg->is_vendor_response): ?><div class="msg-badge">Your reply</div><?php endif; ?>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div style="color:var(--mid);font-size:13px">No messages from customer yet.</div>
        <?php endif; ?>
      </div>
    </div>

    
    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Send Reply to Customer</span></div>
      <div class="od-card-body">
        <form method="POST" action="<?php echo e(route('vendor.orders.reply', $subOrder->id)); ?>">
          <?php echo csrf_field(); ?>
          <textarea name="message" class="sf-note" rows="4" placeholder="Reply to the customer's message or provide shipping update…"></textarea>
          <div style="margin-top:10px"><button class="vs-btn vs-btn-primary">Send Reply</button></div>
        </form>
      </div>
    </div>

    
    <?php if(!empty($timeline)): ?>
    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Activity Log</span></div>
      <div class="od-card-body">
        <?php $__currentLoopData = array_reverse($timeline); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-body">
              <div class="tl-status"><?php echo e(ucfirst($tl['status'])); ?></div>
              <?php if(!empty($tl['note'])): ?><div class="tl-note"><?php echo e($tl['note']); ?></div><?php endif; ?>
              <div class="tl-time"><?php echo e(\Carbon\Carbon::parse($tl['at'])->format('d M Y, g:i A')); ?></div>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  
  <div>
    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Customer</span></div>
      <div class="od-card-body">
        <?php $b = $billing; ?>
        <div style="font-weight:700;font-size:14px;margin-bottom:10px"><?php echo e(trim(($b['first_name']??'').(' '.($b['last_name']??''))) ?: 'Guest'); ?></div>
        <?php if(!empty($b['email'])): ?><div class="dr"><span class="dr-label">Email</span><span class="dr-value" style="word-break:break-all"><?php echo e($b['email']); ?></span></div><?php endif; ?>
        <?php if(!empty($b['phone'])): ?><div class="dr"><span class="dr-label">Phone</span><span class="dr-value"><?php echo e($b['phone']); ?></span></div><?php endif; ?>
        <?php if(!empty($b['latitude']) && !empty($b['longitude'])): ?><div class="dr"><span class="dr-label">Location</span><span class="dr-value"><a href="https://www.google.com/maps?q=<?php echo e($b['latitude']); ?>,<?php echo e($b['longitude']); ?>" target="_blank" rel="noopener"><?php echo e($b['latitude']); ?>, <?php echo e($b['longitude']); ?></a></span></div><?php endif; ?>
        <?php if(!empty($order->customer_note)): ?><div style="margin-top:10px;padding:8px 10px;background:#fffbeb;border-radius:8px;font-size:12px;color:#92400e;border:1px solid #fde68a"><strong>Note:</strong> <?php echo e($order->customer_note); ?></div><?php endif; ?>
      </div>
    </div>

    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Shipping Address</span></div>
      <div class="od-card-body" style="font-size:13px;line-height:1.7;color:var(--dark)">
        <?php $s = $shipping ?: $billing; ?>
        <?php echo e(trim(($s['first_name']??'').(' '.($s['last_name']??'')))); ?><br>
        <?php if(!empty($s['address_1'])): ?> <?php echo e($s['address_1']); ?><br><?php endif; ?>
        <?php if(!empty($s['city'])): ?> <?php echo e($s['city']); ?><?php if(!empty($s['state'])): ?>, <?php echo e($s['state']); ?><?php endif; ?><br><?php endif; ?>
        <?php if(!empty($s['country'])): ?> <?php echo e($s['country']); ?><?php endif; ?>
        <?php if(!empty($s['phone'])): ?> <br>📞 <?php echo e($s['phone']); ?><?php endif; ?>
        <?php if(!empty($s['latitude']) && !empty($s['longitude'])): ?>
          <br>
          <iframe
            width="100%"
            height="220"
            style="border:0;border-radius:12px;margin-top:10px"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.google.com/maps?q=<?php echo e($s['latitude']); ?>,<?php echo e($s['longitude']); ?>&z=15&output=embed">
          </iframe>
        <?php endif; ?>
      </div>
    </div>

    <div class="od-card">
      <div class="od-card-head"><span class="od-card-title">Sub-Order Summary</span></div>
      <div class="od-card-body">
        <div class="dr"><span class="dr-label">Parent Order</span><span class="dr-value">#<?php echo e($order->id); ?></span></div>
        <div class="dr"><span class="dr-label">Sub-Order</span><span class="dr-value">#<?php echo e($subOrder->id); ?></span></div>
        <div class="dr"><span class="dr-label">Status</span><span class="dr-value"><span class="badge s-<?php echo e($subOrder->status); ?>"><?php echo e(ucfirst($subOrder->status)); ?></span></span></div>
        <div class="dr"><span class="dr-label">Payment</span><span class="dr-value"><?php echo e($order->payment_method_title); ?></span></div>
        <div class="dr"><span class="dr-label">Order Total</span><span class="dr-value" style="font-weight:700"><?php echo e(number_format($order->final_total,2)); ?> EGP</span></div>
        <div style="border-top:1px dashed var(--light);margin-top:10px;padding-top:10px">
          <div class="dr"><span class="dr-label">Your Portion</span><span class="dr-value" style="font-weight:800;color:var(--orange)"><?php echo e(number_format($vendorTotal,2)); ?> EGP</span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.vendor.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/vendor/orders/show.blade.php ENDPATH**/ ?>