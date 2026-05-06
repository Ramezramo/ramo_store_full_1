<?php $__env->startSection('title', 'Orders'); ?>
<?php $__env->startSection('page-title', 'Orders Management'); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Order ID, customer ID…" style="width:200px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All Statuses</option>
      <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($s); ?>" <?php echo e($status==$s?'selected':''); ?>><?php echo e(ucfirst($s)); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $status): ?>
    <div class="form-group" style="justify-content:flex-end">
      <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($orders->total()); ?> order(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Order</th>
        <th>Customer ID</th>
        <th>Status</th>
        <th>Payment</th>
        <th>Total</th>
        <th>Date</th>
        <th>Update Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $sc = match($order->status) {
          'completed'           => 'badge-green',
          'pending'             => 'badge-yellow',
          'processing'          => 'badge-blue',
          'shipped'             => 'badge-purple',
          'cancelled', 'failed' => 'badge-red',
          'refunded', 'on-hold' => 'badge-gray',
          default               => 'badge-gray',
        };
      ?>
      <tr>
        <td style="font-weight:700">#<?php echo e($order->id); ?></td>
        <td style="color:var(--muted)"><?php echo e($order->customer_id ?? '—'); ?></td>
        <td><span class="badge <?php echo e($sc); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
        <td style="color:var(--muted);font-size:12px"><?php echo e($order->payment_method_title ?? '—'); ?></td>
        <td style="font-weight:600"><?php echo e($order->currency_symbol); ?><?php echo e(number_format($order->final_total, 2)); ?></td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?php echo e($order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y') : '—'); ?></td>
        <td>
          <form method="POST" action="<?php echo e(route('admin.orders.status', $order->id)); ?>" style="display:flex;gap:4px">
            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
            <select name="status" style="padding:4px 6px;font-size:12px;height:28px">
              <?php $__currentLoopData = ['pending','processing','shipped','on-hold','completed','cancelled','refunded','failed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php echo e($order->status==$s?'selected':''); ?>><?php echo e(ucfirst($s)); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn-ghost btn-sm">Save</button>
          </form>
        </td>
        <td><a href="<?php echo e(route('admin.orders.detail', $order->id)); ?>" class="btn btn-ghost btn-sm">View</a></td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No orders found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($orders->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/orders.blade.php ENDPATH**/ ?>