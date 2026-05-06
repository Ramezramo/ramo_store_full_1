<?php $__env->startSection('title', 'Coupons'); ?>
<?php $__env->startSection('page-title', 'Coupons Management'); ?>

<?php $__env->startSection('content'); ?>


<div class="card" style="margin-bottom:24px">
  <div class="card-title">Create New Coupon</div>
  <form method="POST" action="<?php echo e(route('admin.coupons.create')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-row" style="align-items:flex-end;flex-wrap:wrap">
      <div class="form-group">
        <label>Coupon Code *</label>
        <input type="text" name="code" required placeholder="e.g. SAVE20" style="width:160px;text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
      </div>
      <div class="form-group">
        <label>Discount Type</label>
        <select name="discount_type">
          <option value="percent">Percentage (%)</option>
          <option value="fixed_cart">Fixed Amount</option>
        </select>
      </div>
      <div class="form-group">
        <label>Amount *</label>
        <input type="number" name="amount" required min="0" step="0.01" placeholder="e.g. 20" style="width:110px">
      </div>
      <div class="form-group">
        <label>Usage Limit</label>
        <input type="number" name="usage_limit" min="1" placeholder="Unlimited" style="width:120px">
      </div>
      <div class="form-group">
        <label>Expires</label>
        <input type="date" name="date_expires" style="width:150px">
      </div>
      <div class="form-group">
        <label>Min Order</label>
        <input type="number" name="minimum_amount" min="0" step="0.01" placeholder="0" style="width:110px">
      </div>
      <div class="form-group">
        <label>Vendor Scope</label>
        <select name="vendor_id" style="width:180px">
          <option value="">Global / All Vendors</option>
          <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($v->id); ?>"><?php echo e($v->shop_name); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Create Coupon</button>
    </div>
    <?php if($errors->any()): ?>
      <div class="alert alert-error"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>
  </form>
</div>


<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($coupons->total()); ?> coupon(s)</div>
<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap">
  <a href="<?php echo e(route('admin.coupons')); ?>" class="btn btn-ghost btn-sm">All</a>
  <a href="<?php echo e(route('admin.coupons', ['vendor' => 'global'])); ?>" class="btn btn-ghost btn-sm">Global</a>
  <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('admin.coupons', ['vendor' => $v->id])); ?>" class="btn btn-ghost btn-sm"><?php echo e($v->shop_name); ?></a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Vendor</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Usage</th>
        <th>Min Order</th>
        <th>Expires</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td>
          <span style="font-family:monospace;font-weight:700;font-size:13px;color:var(--accent)"><?php echo e($coupon->code); ?></span>
        </td>
        <td style="font-size:12px;color:var(--muted)"><?php echo e($coupon->vendor_shop_name ?? 'Global'); ?></td>
        <td>
          <span class="badge <?php echo e($coupon->discount_type === 'percent' ? 'badge-blue' : 'badge-orange'); ?>">
            <?php echo e($coupon->discount_type === 'percent' ? 'Percentage' : 'Fixed'); ?>

          </span>
        </td>
        <td style="font-weight:700">
          <?php echo e($coupon->discount_type === 'percent' ? $coupon->amount.'%' : number_format($coupon->amount, 2)); ?>

        </td>
        <td style="color:var(--muted)">
          <?php echo e($coupon->usage_count); ?>

          <?php if($coupon->usage_limit): ?> / <?php echo e($coupon->usage_limit); ?> <?php else: ?> / ∞ <?php endif; ?>
          <?php if($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit): ?>
            <span class="badge badge-red" style="margin-left:4px">Maxed</span>
          <?php endif; ?>
        </td>
        <td style="color:var(--muted);font-size:12px">
          <?php echo e($coupon->minimum_amount > 0 ? number_format($coupon->minimum_amount, 2) : '—'); ?>

        </td>
        <td style="font-size:12px;color:var(--muted)">
          <?php if($coupon->date_expires): ?>
            <?php $exp = \Carbon\Carbon::parse($coupon->date_expires); ?>
            <span style="<?php echo e($exp->isPast() ? 'color:var(--red)' : ''); ?>">
              <?php echo e($exp->format('d M Y')); ?>

              <?php if($exp->isPast()): ?> <span class="badge badge-red" style="margin-left:2px">Expired</span><?php endif; ?>
            </span>
          <?php else: ?>
            Never
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?php echo e($coupon->status === 'publish' ? 'badge-green' : 'badge-gray'); ?>">
            <?php echo e($coupon->status === 'publish' ? 'Active' : 'Disabled'); ?>

          </span>
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          <?php echo e(\Carbon\Carbon::parse($coupon->date_created)->format('d M Y')); ?>

        </td>
        <td>
          <div style="display:flex;gap:6px">
            <form method="POST" action="<?php echo e(route('admin.coupons.toggle', $coupon->id)); ?>">
              <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
              <?php if($coupon->status === 'publish'): ?>
                <button class="btn btn-warning btn-sm">Disable</button>
              <?php else: ?>
                <button class="btn btn-success btn-sm">Enable</button>
              <?php endif; ?>
            </form>
            <form method="POST" action="<?php echo e(route('admin.coupons.delete', $coupon->id)); ?>" onsubmit="return confirm('Delete coupon <?php echo e($coupon->code); ?>?')">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:32px">No coupons yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($coupons->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/coupons.blade.php ENDPATH**/ ?>