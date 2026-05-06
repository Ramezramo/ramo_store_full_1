<?php $__env->startSection('title', 'Order #' . $order->id); ?>
<?php $__env->startSection('page-title', 'Order #' . $order->id); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;gap:12px;margin-bottom:20px">
  <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-secondary">← Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

  <div style="display:flex;flex-direction:column;gap:20px">

    
    <div class="section">
      <div class="section-header">
        <div class="section-title">Order Details</div>
        <?php
          $sc = match($order->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        ?>
        <span class="badge <?php echo e($sc); ?>" style="font-size:13px;padding:5px 14px"><?php echo e($order->status); ?></span>
      </div>
      <div style="padding:20px">
        <div class="detail-grid">
          <div class="detail-item">
            <div class="label">Order ID</div>
            <div class="val">#<?php echo e($order->id); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Order Key</div>
            <div class="val" style="font-size:12px;color:var(--muted)"><?php echo e($order->order_key ?: '—'); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Payment Method</div>
            <div class="val"><?php echo e($order->payment_method_title ?: '—'); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Currency</div>
            <div class="val"><?php echo e($order->currency); ?> <?php echo e($order->currency_symbol); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Subtotal</div>
            <div class="val"><?php echo e(number_format($order->original_total, 2)); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Discount</div>
            <div class="val"><?php echo e(number_format($order->discount_total, 2)); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Shipping</div>
            <div class="val"><?php echo e(number_format($order->shipping_total, 2)); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Final Total</div>
            <div class="val" style="font-size:18px;font-weight:800;color:var(--accent)"><?php echo e(number_format($order->final_total, 2)); ?> <?php echo e($order->currency_symbol); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Date Created</div>
            <div class="val"><?php echo e($order->date_created ? date('M d, Y H:i', strtotime($order->date_created)) : '—'); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Date Paid</div>
            <div class="val"><?php echo e($order->date_paid ? date('M d, Y H:i', strtotime($order->date_paid)) : 'Not paid'); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Customer Note</div>
            <div class="val"><?php echo e($order->customer_note ?: '—'); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Location</div>
            <div class="val">
              <?php $bill = json_decode($order->billing, true) ?? []; ?>
              <?php if(!empty($bill['latitude']) && !empty($bill['longitude'])): ?>
                <a href="https://www.google.com/maps?q=<?php echo e($bill['latitude']); ?>,<?php echo e($bill['longitude']); ?>" target="_blank" rel="noopener"><?php echo e($bill['latitude']); ?>, <?php echo e($bill['longitude']); ?></a>
              <?php else: ?>
                —
              <?php endif; ?>
            </div>
          </div>
          <div class="detail-item">
            <div class="label">Coupon</div>
            <div class="val"><?php echo e($order->coupon_code ?: '—'); ?></div>
          </div>
        </div>
      </div>
    </div>

    
    <?php if($order->line_items): ?>
    <div class="section">
      <div class="section-header"><div class="section-title">Line Items</div></div>
      <div style="padding:16px 20px">
        <?php $items = json_decode($order->line_items, true) ?? []; ?>
        <?php if(count($items)): ?>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
              <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  <td style="font-weight:600"><?php echo e($item['name'] ?? '—'); ?></td>
                  <td><?php echo e($item['quantity'] ?? $item['qty'] ?? '—'); ?></td>
                  <td><?php echo e(isset($item['price']) ? number_format($item['price'], 2) : '—'); ?></td>
                  <td><?php echo e(isset($item['total']) ? number_format($item['total'], 2) : '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p style="color:var(--muted)">No line items data.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <div style="display:flex;flex-direction:column;gap:20px">

    
    <div class="section">
      <div class="section-header"><div class="section-title">Update Status</div></div>
      <div style="padding:20px">
        <form method="POST" action="<?php echo e(route('admin.orders.status', $order->id)); ?>">
          <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
          <div class="form-group">
            <label class="form-label">New Status</label>
            <select name="status" class="form-control">
              <option value="pending" <?php echo e($order->status === 'pending' ? 'selected' : ''); ?>>Pending</option>
              <option value="processing" <?php echo e($order->status === 'processing' ? 'selected' : ''); ?>>Processing</option>
              <option value="on-hold" <?php echo e($order->status === 'on-hold' ? 'selected' : ''); ?>>On Hold</option>
              <option value="shipped" <?php echo e($order->status === 'shipped' ? 'selected' : ''); ?>>Shipped</option>
              <option value="completed" <?php echo e($order->status === 'completed' ? 'selected' : ''); ?>>Completed</option>
              <option value="cancelled" <?php echo e($order->status === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
              <option value="refunded" <?php echo e($order->status === 'refunded' ? 'selected' : ''); ?>>Refunded</option>
              <option value="failed" <?php echo e($order->status === 'failed' ? 'selected' : ''); ?>>Failed</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%">Update Status</button>
        </form>
      </div>
    </div>

    
    <div class="section">
      <div class="section-header"><div class="section-title">Customer</div></div>
      <div style="padding:20px">
        <?php if($customer): ?>
          <div class="detail-item" style="margin-bottom:12px">
            <div class="label">Name</div>
            <div class="val"><?php echo e($customer->name); ?></div>
          </div>
          <div class="detail-item" style="margin-bottom:12px">
            <div class="label">Email</div>
            <div class="val" style="font-size:13px"><?php echo e($customer->email); ?></div>
          </div>
          <div class="detail-item">
            <div class="label">Phone</div>
            <div class="val"><?php echo e($customer->phone ?: '—'); ?></div>
          </div>
          <?php if($customer->is_blocked): ?>
            <div style="margin-top:12px">
              <span class="badge badge-red">User is blocked</span>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <p style="color:var(--muted)">Customer ID: <?php echo e($order->customer_id ?: 'Guest'); ?></p>
        <?php endif; ?>
      </div>
    </div>

    
    <?php if($order->billing): ?>
    <div class="section">
      <div class="section-header"><div class="section-title">Billing Address</div></div>
      <div style="padding:20px">
        <?php $billing = json_decode($order->billing, true) ?? []; ?>
        <?php $__currentLoopData = ['first_name','last_name','address_1','address_2','city','state','postcode','country','phone','email']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php if(!empty($billing[$field])): ?>
            <div style="font-size:13px;color:var(--text);margin-bottom:4px"><?php echo e($billing[$field]); ?></div>
          <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if(!empty($billing['latitude']) && !empty($billing['longitude'])): ?>
          <div style="margin-top:10px">
            <iframe
              width="100%"
              height="220"
              style="border:0;border-radius:12px"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q=<?php echo e($billing['latitude']); ?>,<?php echo e($billing['longitude']); ?>&z=15&output=embed">
            </iframe>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/orders/show.blade.php ENDPATH**/ ?>