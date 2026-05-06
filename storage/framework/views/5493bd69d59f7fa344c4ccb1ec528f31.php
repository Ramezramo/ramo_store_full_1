<?php $__env->startSection('title', 'Vendors'); ?>
<?php $__env->startSection('page-title', 'Vendors Management'); ?>

<?php $__env->startSection('content'); ?>

<div class="section">
  <div class="section-header">
    <div class="section-title">All Vendor Shops</div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search shop name, owner…" class="search-input">
    <select name="status" class="filter-select">
      <option value="all" <?php echo e($status === 'all' ? 'selected' : ''); ?>>All Statuses</option>
      <option value="pending" <?php echo e($status === 'pending' ? 'selected' : ''); ?>>Pending</option>
      <option value="active" <?php echo e($status === 'active' ? 'selected' : ''); ?>>Active</option>
      <option value="blocked" <?php echo e($status === 'blocked' ? 'selected' : ''); ?>>Blocked</option>
      <option value="rejected" <?php echo e($status === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    <?php if($search || $status !== 'all'): ?>
      <a href="<?php echo e(route('admin.vendors')); ?>" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Shop Name</th>
          <th>Owner</th>
          <th>Email</th>
          <th>Status</th>
          <th>Owner Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $sc = match($vendor->status) {
            'active' => 'badge-green',
            'pending' => 'badge-yellow',
            'blocked' => 'badge-red',
            'rejected' => 'badge-gray',
            default => 'badge-gray'
          };
        ?>
        <tr>
          <td style="color:var(--muted)"><?php echo e($vendor->id); ?></td>
          <td style="font-weight:700"><?php echo e($vendor->shop_name); ?></td>
          <td><?php echo e($vendor->owner_name ?: '—'); ?></td>
          <td style="color:var(--muted)"><?php echo e($vendor->owner_email ?: '—'); ?></td>
          <td><span class="badge <?php echo e($sc); ?>"><?php echo e($vendor->status); ?></span></td>
          <td>
            <?php if($vendor->owner_blocked): ?>
              <span class="badge badge-red">User Blocked</span>
            <?php else: ?>
              <span class="badge badge-green">Active</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted)"><?php echo e($vendor->created_at ? date('M d, Y', strtotime($vendor->created_at)) : '—'); ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <?php if($vendor->status !== 'active'): ?>
                <form method="POST" action="<?php echo e(route('admin.vendors.approve', $vendor->id)); ?>">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
              <?php endif; ?>
              <?php if($vendor->status !== 'blocked'): ?>
                <form method="POST" action="<?php echo e(route('admin.vendors.block', $vendor->id)); ?>">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-warning btn-sm">Block</button>
                </form>
              <?php endif; ?>
              <?php if($vendor->status !== 'rejected'): ?>
                <form method="POST" action="<?php echo e(route('admin.vendors.reject', $vendor->id)); ?>">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
                </form>
              <?php endif; ?>
              <form method="POST" action="<?php echo e(route('admin.vendors.delete', $vendor->id)); ?>" id="del-vendor-<?php echo e($vendor->id); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirm_action('del-vendor-<?php echo e($vendor->id); ?>', 'Delete this vendor shop?')">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No vendors found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination"><?php echo e($vendors->links()); ?></div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/vendors/index.blade.php ENDPATH**/ ?>