<?php $__env->startSection('title', 'Orders'); ?>
<?php $__env->startSection('page-title', 'Orders Management'); ?>

<?php $__env->startSection('content'); ?>

<div class="section">
  <div class="section-header">
    <div class="section-title">All Orders</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $sc = match($s->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        ?>
        <span class="badge <?php echo e($sc); ?>"><?php echo e($s->status); ?>: <?php echo e($s->cnt); ?></span>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search order ID, customer…" class="search-input">
    <select name="status" class="filter-select">
      <option value="all" <?php echo e($status === 'all' ? 'selected' : ''); ?>>All Statuses</option>
      <option value="pending" <?php echo e($status === 'pending' ? 'selected' : ''); ?>>Pending</option>
      <option value="processing" <?php echo e($status === 'processing' ? 'selected' : ''); ?>>Processing</option>
      <option value="on-hold" <?php echo e($status === 'on-hold' ? 'selected' : ''); ?>>On Hold</option>
      <option value="shipped" <?php echo e($status === 'shipped' ? 'selected' : ''); ?>>Shipped</option>
      <option value="completed" <?php echo e($status === 'completed' ? 'selected' : ''); ?>>Completed</option>
      <option value="cancelled" <?php echo e($status === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
      <option value="refunded" <?php echo e($status === 'refunded' ? 'selected' : ''); ?>>Refunded</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    <?php if($search || $status !== 'all'): ?>
      <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Total</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $sc = match($order->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        ?>
        <tr>
          <td><a href="<?php echo e(route('admin.orders.show', $order->id)); ?>" style="color:var(--accent);font-weight:700">#<?php echo e($order->id); ?></a></td>
          <td style="color:var(--muted)"><?php echo e($order->customer_id ? 'User #'.$order->customer_id : 'Guest'); ?></td>
          <td><span class="badge <?php echo e($sc); ?>"><?php echo e($order->status); ?></span></td>
          <td style="color:var(--muted)"><?php echo e($order->payment_method_title ?: '—'); ?></td>
          <td style="font-weight:600"><?php echo e(number_format($order->final_total, 2)); ?> <?php echo e($order->currency_symbol); ?></td>
          <td style="color:var(--muted)"><?php echo e($order->date_created ? date('M d, Y', strtotime($order->date_created)) : '—'); ?></td>
          <td>
            <div style="display:flex;gap:6px;align-items:center">
              <a href="<?php echo e(route('admin.orders.show', $order->id)); ?>" class="btn btn-secondary btn-sm">View</a>
              <form method="POST" action="<?php echo e(route('admin.orders.status', $order->id)); ?>" style="display:flex;gap:4px">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <select name="status" class="filter-select" style="padding:4px 8px;font-size:11px">
                  <option value="pending" <?php echo e($order->status === 'pending' ? 'selected' : ''); ?>>Pending</option>
                  <option value="processing" <?php echo e($order->status === 'processing' ? 'selected' : ''); ?>>Processing</option>
                  <option value="on-hold" <?php echo e($order->status === 'on-hold' ? 'selected' : ''); ?>>On Hold</option>
                  <option value="shipped" <?php echo e($order->status === 'shipped' ? 'selected' : ''); ?>>Shipped</option>
                  <option value="completed" <?php echo e($order->status === 'completed' ? 'selected' : ''); ?>>Completed</option>
                  <option value="cancelled" <?php echo e($order->status === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                  <option value="refunded" <?php echo e($order->status === 'refunded' ? 'selected' : ''); ?>>Refunded</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:32px">No orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination"><?php echo e($orders->links()); ?></div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/orders/index.blade.php ENDPATH**/ ?>