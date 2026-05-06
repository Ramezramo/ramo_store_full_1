<?php $pageTitle = 'New Refund / Return Request'; ?>

<?php $__env->startSection('account-content'); ?>
<div style="margin-bottom:20px">
  <a href="<?php echo e(route('account.refunds')); ?>" style="font-size:13px;color:#888;text-decoration:none">← Back to Requests</a>
</div>
<div class="acc-section-title" style="margin-bottom:20px">New Refund / Return Request</div>

<?php if($errors->any()): ?>
  <div class="acc-alert acc-alert-error" style="margin-bottom:16px"><?php echo e($errors->first()); ?></div>
<?php endif; ?>

<div class="order-detail-card">
  <form method="POST" action="<?php echo e(route('account.refunds.store')); ?>">
    <?php echo csrf_field(); ?>

    <div style="margin-bottom:18px">
      <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Order *</label>
      <select name="order_id" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
        <option value="">Select an order…</option>
        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $disabled = in_array($o->id, $existingIds); ?>
          <option value="<?php echo e($o->id); ?>"
            <?php echo e((old('order_id', $orderId) == $o->id) ? 'selected' : ''); ?>

            <?php echo e($disabled ? 'disabled' : ''); ?>>
            #<?php echo e($o->id); ?> — <?php echo e(\Carbon\Carbon::parse($o->date_created)->format('M d, Y')); ?> — <?php echo e(number_format($o->final_total, 2)); ?> EGP (<?php echo e(ucfirst($o->status)); ?>)
            <?php echo e($disabled ? '[Already requested]' : ''); ?>

          </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
      <div>
        <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Request Type *</label>
        <select name="type" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
          <option value="refund" <?php echo e(old('type')=='refund'?'selected':''); ?>>Refund — Get my money back</option>
          <option value="return" <?php echo e(old('type')=='return'?'selected':''); ?>>Return — Send item back</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Reason *</label>
        <select name="reason" required style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none">
          <option value="">Select a reason…</option>
          <option value="damaged"          <?php echo e(old('reason')=='damaged'?'selected':''); ?>>Item arrived damaged</option>
          <option value="wrong_item"       <?php echo e(old('reason')=='wrong_item'?'selected':''); ?>>Wrong item received</option>
          <option value="not_as_described" <?php echo e(old('reason')=='not_as_described'?'selected':''); ?>>Not as described</option>
          <option value="changed_mind"     <?php echo e(old('reason')=='changed_mind'?'selected':''); ?>>Changed my mind</option>
          <option value="other"            <?php echo e(old('reason')=='other'?'selected':''); ?>>Other</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom:22px">
      <label style="display:block;font-size:12px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px">Additional Details</label>
      <textarea name="description" rows="4" placeholder="Describe your issue in detail…" style="width:100%;padding:10px 13px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;background:#fff;outline:none;resize:vertical"><?php echo e(old('description')); ?></textarea>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn btn-dark">Submit Request</button>
      <a href="<?php echo e(route('account.refunds')); ?>" class="btn btn-outline">Cancel</a>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/refund-create.blade.php ENDPATH**/ ?>