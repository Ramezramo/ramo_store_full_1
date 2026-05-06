<?php $__env->startSection('title','Refund Requests'); ?>
<?php $__env->startSection('page-title','Refund & Return Requests'); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Order # or customer…" style="width:200px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="pending"   <?php echo e($status=='pending'?'selected':''); ?>>Pending</option>
      <option value="approved"  <?php echo e($status=='approved'?'selected':''); ?>>Approved</option>
      <option value="rejected"  <?php echo e($status=='rejected'?'selected':''); ?>>Rejected</option>
      <option value="completed" <?php echo e($status=='completed'?'selected':''); ?>>Completed</option>
      <option value="cancelled" <?php echo e($status=='cancelled'?'selected':''); ?>>Cancelled</option>
    </select>
  </div>
  <div class="form-group">
    <label>Type</label>
    <select name="type">
      <option value="">All</option>
      <option value="refund" <?php echo e($type=='refund'?'selected':''); ?>>Refund</option>
      <option value="return" <?php echo e($type=='return'?'selected':''); ?>>Return</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $status || $type): ?>
    <div class="form-group">
      <a href="<?php echo e(route('admin.refunds')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($refunds->total()); ?> request(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Order</th>
        <th>Customer</th>
        <th>Vendor</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $sc = match($r->status) {
          'approved','completed' => 'badge-green',
          'rejected','cancelled' => 'badge-red',
          default => 'badge-yellow',
        };
        $reasonLabel = match($r->reason) {
          'damaged'          => 'Damaged',
          'wrong_item'       => 'Wrong Item',
          'changed_mind'     => 'Changed Mind',
          'not_as_described' => 'Not as Described',
          default            => 'Other',
        };
      ?>
      <tr>
        <td style="font-weight:600">#<?php echo e($r->id); ?></td>
        <td><a href="<?php echo e(route('admin.orders.detail', $r->order_id)); ?>" style="color:var(--accent)">#<?php echo e($r->order_id); ?></a></td>
        <td>
          <div style="font-weight:500"><?php echo e($r->customer_name); ?></div>
          <div style="font-size:11px;color:var(--muted)"><?php echo e($r->customer_email); ?></div>
        </td>
        <td style="font-size:12px;color:var(--muted)"><?php echo e($r->vendor_shop_name ?? '—'); ?></td>
        <td><span class="badge badge-blue" style="text-transform:capitalize"><?php echo e($r->type); ?></span></td>
        <td style="font-size:12px;color:var(--muted)"><?php echo e($reasonLabel); ?></td>
        <td><span class="badge <?php echo e($sc); ?>"><?php echo e(ucfirst($r->status)); ?></span></td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?php echo e(\Carbon\Carbon::parse($r->created_at)->format('d M Y')); ?></td>
        <td>
          <a href="<?php echo e(route('admin.refunds.show', $r->id)); ?>" class="btn btn-secondary btn-sm">Review</a>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No requests found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($refunds->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/refunds.blade.php ENDPATH**/ ?>