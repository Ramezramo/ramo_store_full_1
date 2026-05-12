<?php $__env->startSection('title', 'Order #' . $order->id); ?>
<?php $__env->startSection('page-title', 'Order #' . $order->id); ?>

<?php $__env->startSection('topbar-actions'); ?>
  <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-ghost btn-sm">← Back to Orders</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

  
  <div style="display:flex;flex-direction:column;gap:20px">

    
    <div class="card">
      <div class="card-title">Order Status</div>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        <?php
          $sc = match($order->status) {
            'completed'                       => 'badge-green',
            'pending'                         => 'badge-yellow',
            'processing'                      => 'badge-blue',
            'shipped'                         => 'badge-purple',
            'cancelled', 'failed'             => 'badge-red',
            'refunded', 'on-hold'             => 'badge-gray',
            default                           => 'badge-gray',
          };
        ?>
        <span class="badge <?php echo e($sc); ?>" style="font-size:14px;padding:6px 14px"><?php echo e(ucfirst($order->status)); ?></span>

        <form method="POST" action="<?php echo e(route('admin.orders.status', $order->id)); ?>" style="display:flex;gap:8px;align-items:center">
          <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
          <select name="status">
            <?php $__currentLoopData = ['pending','processing','shipped','completed','cancelled','refunded','failed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($s); ?>" <?php echo e($order->status==$s?'selected':''); ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <button class="btn btn-primary">Update Status</button>
        </form>
      </div>
    </div>

    
    <div class="card">
      <div class="card-title">Items Ordered</div>
      <?php if(!empty($lineItems)): ?>
        <div class="table-wrap" style="border:none">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Variation</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $attrs = $item['attributes'] ?? $item['attrs'] ?? [];
                $attrStr = is_array($attrs)
                  ? collect($attrs)->map(fn($v,$k) => "$k: $v")->implode(' · ')
                  : '';
                $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                $itemTotal = $item['subtotal'] ?? (($item['price'] ?? 0) * $qty);
              ?>
              <tr>
                <td>
                  <div style="font-weight:600"><?php echo e($item['name'] ?? 'Unknown'); ?></div>
                  <?php if($item['variation_id'] ?? null): ?>
                    <div style="font-size:11px;color:var(--muted)">Var #<?php echo e($item['variation_id']); ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($attrStr): ?>
                    <div style="font-size:12px;color:var(--muted);line-height:1.6">
                      <?php $__currentLoopData = is_array($attrs) ? $attrs : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span style="display:inline-block;background:rgba(255,255,255,.07);border-radius:4px;padding:1px 6px;margin:1px;font-size:11px">
                          <strong><?php echo e($k); ?></strong>: <?php echo e($v); ?>

                        </span>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  <?php else: ?>
                    <span style="color:var(--muted);font-size:12px">—</span>
                  <?php endif; ?>
                </td>
                <td><?php echo e($qty); ?></td>
                <td><?php echo e($order->currency_symbol); ?><?php echo e(number_format($item['price'] ?? 0, 2)); ?></td>
                <td style="font-weight:600"><?php echo e($order->currency_symbol); ?><?php echo e(number_format($itemTotal, 2)); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:var(--muted);font-size:13px">No line items recorded.</p>
      <?php endif; ?>
    </div>

    
    <?php if(isset($subOrders) && $subOrders->count() > 0): ?>
    <div class="card">
      <div class="card-title">Vendor Sub-Orders (<?php echo e($subOrders->count()); ?>)</div>
      <?php $__currentLoopData = $subOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $subItems = json_decode($sub->line_items ?? '[]', true) ?: [];
          $subSc = match($sub->status) {
            'completed'           => 'badge-green',
            'pending'             => 'badge-yellow',
            'processing'          => 'badge-blue',
            'shipped'             => 'badge-purple',
            'cancelled', 'failed' => 'badge-red',
            default               => 'badge-gray',
          };
        ?>
        <div style="border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:14px;margin-bottom:12px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
            <div>
              <span style="font-weight:700;font-size:14px"><?php echo e($sub->vendor_shop_name ?: 'No Store Name'); ?></span>
              <span style="font-size:11px;color:var(--muted);margin-left:8px">Sub-order #<?php echo e($sub->id); ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
              <span class="badge <?php echo e($subSc); ?>" style="font-size:12px"><?php echo e(ucfirst($sub->status)); ?></span>
            </div>
          </div>
          <?php $__currentLoopData = $subItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.05)">
              <span style="color:var(--muted)"><?php echo e($si['name']); ?> × <?php echo e($si['quantity']); ?></span>
              <span style="font-weight:600"><?php echo e(number_format($si['subtotal'],2)); ?> EGP</span>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-top:8px;color:var(--accent)">
            <span>Vendor Total</span><span><?php echo e(number_format($sub->total, 2)); ?> EGP</span>
          </div>
          <?php if($sub->tracking_number): ?>
            <div style="margin-top:6px;font-size:12px;color:var(--muted)">
              Tracking: <span style="font-family:monospace;color:var(--accent)"><?php echo e($sub->tracking_number); ?></span>
              <?php if($sub->tracking_carrier): ?> via <?php echo e($sub->tracking_carrier); ?> <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($timeline)): ?>
    <div class="card">
      <div class="card-title">Order Timeline</div>
      <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="display:flex;gap:12px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
          <div style="width:8px;height:8px;border-radius:50%;background:var(--accent);margin-top:5px;flex-shrink:0"></div>
          <div>
            <div style="font-size:13px;font-weight:500"><?php echo e($event['message'] ?? $event['note'] ?? json_encode($event)); ?></div>
            <?php if(isset($event['date_created'])): ?>
              <div style="font-size:11px;color:var(--muted)"><?php echo e($event['date_created']); ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

  </div>

  
  <div style="display:flex;flex-direction:column;gap:20px">

    
    <div class="card">
      <div class="card-title">Summary</div>
      <div class="detail-row"><div class="detail-label">Order ID</div><div class="detail-value">#<?php echo e($order->id); ?></div></div>
      <div class="detail-row"><div class="detail-label">Date</div><div class="detail-value"><?php echo e($order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y, H:i') : '—'); ?></div></div>
      <div class="detail-row"><div class="detail-label">Payment</div><div class="detail-value"><?php echo e($order->payment_method_title ?? '—'); ?></div></div>
      <div class="detail-row"><div class="detail-label">Discount</div><div class="detail-value"><?php echo e($order->currency_symbol); ?><?php echo e(number_format($order->discount_total, 2)); ?></div></div>
      <div class="detail-row"><div class="detail-label">Shipping</div><div class="detail-value"><?php echo e($order->currency_symbol); ?><?php echo e(number_format($order->shipping_total, 2)); ?></div></div>
      <div class="detail-row" style="border-bottom:none">
        <div class="detail-label">Total</div>
        <div class="detail-value" style="font-size:20px;font-weight:800;color:var(--accent)"><?php echo e($order->currency_symbol); ?><?php echo e(number_format($order->final_total, 2)); ?></div>
      </div>
    </div>

    
    <div class="card">
      <div class="card-title">Customer</div>
      <?php if($customer): ?>
        <div class="detail-row"><div class="detail-label">Name</div><div class="detail-value"><?php echo e($customer->name); ?></div></div>
        <div class="detail-row"><div class="detail-label">Email</div><div class="detail-value"><?php echo e($customer->email); ?></div></div>
        <div class="detail-row" style="border-bottom:none"><div class="detail-label">Phone</div><div class="detail-value"><?php echo e($customer->phone ?? '—'); ?></div></div>
      <?php else: ?>
        <p style="color:var(--muted);font-size:13px">Customer #<?php echo e($order->customer_id); ?> (not found)</p>
      <?php endif; ?>
    </div>

    
    <?php if(!empty($billing)): ?>
    <div class="card">
      <div class="card-title">Billing Address</div>
      <div style="font-size:13px;line-height:1.8;color:var(--muted)">
        <?php echo e($billing['first_name'] ?? ''); ?> <?php echo e($billing['last_name'] ?? ''); ?><br>
        <?php if(!empty($billing['address_1'])): ?> <?php echo e($billing['address_1']); ?><br><?php endif; ?>
        <?php if(!empty($billing['city'])): ?> <?php echo e($billing['city']); ?><?php endif; ?>
        <?php if(!empty($billing['postcode'])): ?> , <?php echo e($billing['postcode']); ?><?php endif; ?>
        <?php if(!empty($billing['country'])): ?> &nbsp;<?php echo e($billing['country']); ?><?php endif; ?>
        <?php if(!empty($billing['phone'])): ?> <br><?php echo e($billing['phone']); ?><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($shipping) && array_filter($shipping)): ?>
    <div class="card">
      <div class="card-title">Shipping Address</div>
      <div style="font-size:13px;line-height:1.8;color:var(--muted)">
        <?php echo e($shipping['first_name'] ?? ''); ?> <?php echo e($shipping['last_name'] ?? ''); ?><br>
        <?php if(!empty($shipping['address_1'])): ?> <?php echo e($shipping['address_1']); ?><br><?php endif; ?>
        <?php if(!empty($shipping['city'])): ?> <?php echo e($shipping['city']); ?><?php endif; ?>
        <?php if(!empty($shipping['country'])): ?> &nbsp;<?php echo e($shipping['country']); ?><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/order-detail.blade.php ENDPATH**/ ?>