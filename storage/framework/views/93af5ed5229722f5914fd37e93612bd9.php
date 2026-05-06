<?php $__env->startSection('title','Refund Request #'.$refund->id); ?>
<?php $__env->startSection('page-title','Refund Request #'.$refund->id); ?>

<?php $__env->startSection('content'); ?>

<div style="margin-bottom:16px">
  <a href="<?php echo e(route('admin.refunds')); ?>" style="color:var(--muted);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Requests
  </a>
</div>

<?php
  $sc = match($refund->status) {
    'approved','completed' => 'badge-green',
    'rejected','cancelled' => 'badge-red',
    default => 'badge-yellow',
  };
  $reasonLabel = match($refund->reason) {
    'damaged'          => 'Item Arrived Damaged',
    'wrong_item'       => 'Wrong Item Received',
    'changed_mind'     => 'Changed Mind',
    'not_as_described' => 'Not as Described',
    default            => 'Other',
  };
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Request Details</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Request #</td><td style="font-weight:600">#<?php echo e($refund->id); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Order</td><td><a href="<?php echo e(route('admin.orders.detail', $refund->order_id)); ?>" style="color:var(--accent);font-weight:600">#<?php echo e($refund->order_id); ?></a></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Order Total</td><td style="font-weight:700"><?php echo e(number_format($refund->order_total, 2)); ?> EGP</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Type</td><td style="font-weight:600;text-transform:capitalize"><?php echo e($refund->type); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Reason</td><td><?php echo e($reasonLabel); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Status</td><td><span class="badge <?php echo e($sc); ?>"><?php echo e(ucfirst($refund->status)); ?></span></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Vendor</td><td><?php echo e($refund->vendor_shop_name ?? '—'); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Submitted</td><td style="font-size:12px"><?php echo e(\Carbon\Carbon::parse($refund->created_at)->format('d M Y, H:i')); ?></td></tr>
    </table>
  </div>

  
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Customer</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Name</td><td style="font-weight:600"><?php echo e($refund->customer_name); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Email</td><td><?php echo e($refund->customer_email); ?></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Phone</td><td><?php echo e($refund->customer_phone ?: '—'); ?></td></tr>
    </table>
  </div>
</div>

<?php if($refund->description): ?>
<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--border)">Customer Description</div>
  <p style="font-size:13px;line-height:1.7;color:var(--text)"><?php echo e($refund->description); ?></p>
</div>
<?php endif; ?>


<?php if(!in_array($refund->status, ['cancelled'])): ?>
<div class="card" style="padding:20px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Update Status</div>
  <form method="POST" action="<?php echo e(route('admin.refunds.update', $refund->id)); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
    <div class="form-row" style="flex-wrap:wrap;align-items:flex-end">
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="pending"   <?php echo e($refund->status=='pending'?'selected':''); ?>>Pending</option>
          <option value="approved"  <?php echo e($refund->status=='approved'?'selected':''); ?>>Approved</option>
          <option value="rejected"  <?php echo e($refund->status=='rejected'?'selected':''); ?>>Rejected</option>
          <option value="completed" <?php echo e($refund->status=='completed'?'selected':''); ?>>Completed</option>
        </select>
      </div>
      <div class="form-group" style="flex:1;min-width:260px">
        <label>Note to Customer (optional)</label>
        <input type="text" name="admin_note" value="<?php echo e($refund->admin_note ?? ''); ?>" placeholder="e.g. Refund processed to original payment method" style="width:100%">
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/refunds/show.blade.php ENDPATH**/ ?>