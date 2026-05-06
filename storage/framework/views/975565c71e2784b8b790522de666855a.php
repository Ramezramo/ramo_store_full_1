<?php $__env->startSection('title', 'Vendors'); ?>
<?php $__env->startSection('page-title', 'Vendor Management'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
  <div class="alert alert-success" style="margin-bottom:16px"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="GET" class="form-row" style="margin-bottom:20px">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Shop name, email, owner…" style="width:240px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="pending"  <?php echo e($status=='pending'  ?'selected':''); ?>>Pending</option>
      <option value="approved" <?php echo e($status=='approved' ?'selected':''); ?>>Approved</option>
      <option value="blocked"  <?php echo e($status=='blocked'  ?'selected':''); ?>>Blocked</option>
      <option value="rejected" <?php echo e($status=='rejected' ?'selected':''); ?>>Rejected</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $status): ?>
    <div class="form-group">
      <a href="<?php echo e(route('admin.vendors')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($vendors->total()); ?> vendor(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Shop Name</th>
        <th>Owner</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Commission</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $sc = match($v->status) {
          'approved'=>'badge-green','pending'=>'badge-yellow',
          'blocked'=>'badge-red','rejected'=>'badge-gray',default=>'badge-gray'
        };
      ?>
      <tr>
        <td style="color:var(--muted);font-size:12px">#<?php echo e($v->id); ?></td>
        <td>
          <a href="<?php echo e(route('admin.vendors.show', $v->id)); ?>" style="font-weight:600;color:var(--primary);text-decoration:none">
            <?php echo e($v->shop_name); ?>

          </a>
        </td>
        <td><?php echo e($v->first_name); ?> <?php echo e($v->last_name); ?></td>
        <td style="color:var(--muted);font-size:12px"><?php echo e($v->email); ?></td>
        <td style="color:var(--muted);font-size:12px"><?php echo e($v->phone ?: '—'); ?></td>
        <td><span class="badge <?php echo e($sc); ?>"><?php echo e(ucfirst($v->status)); ?></span></td>
        <td style="font-size:13px;text-align:center">
          <?php echo e($v->sales_commission_percentage !== null ? $v->sales_commission_percentage.'%' : '—'); ?>

        </td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap">
          <?php echo e($v->created_at ? \Carbon\Carbon::parse($v->created_at)->format('d M Y') : '—'); ?>

        </td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
            <a href="<?php echo e(route('admin.vendors.show', $v->id)); ?>" class="btn btn-ghost btn-sm">View</a>
            <?php if($v->status !== 'approved'): ?>
              <form method="POST" action="<?php echo e(route('admin.vendors.approve', $v->id)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-success btn-sm">Approve</button>
              </form>
            <?php endif; ?>
            <?php if($v->status !== 'blocked'): ?>
              <form method="POST" action="<?php echo e(route('admin.vendors.block', $v->id)); ?>" onsubmit="return confirm('Block this vendor?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            <?php endif; ?>
            <?php if($v->status !== 'rejected'): ?>
              <form method="POST" action="<?php echo e(route('admin.vendors.reject', $v->id)); ?>" onsubmit="return confirm('Reject this vendor?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-danger btn-sm">Reject</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.vendors.delete', $v->id)); ?>" onsubmit="return confirm('Permanently delete this vendor?')">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No vendors found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($vendors->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/vendors.blade.php ENDPATH**/ ?>