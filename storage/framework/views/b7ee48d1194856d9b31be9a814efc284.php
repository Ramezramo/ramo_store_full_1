<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'Users Management'); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Name, email, phone…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Role</label>
    <select name="role">
      <option value="">All Roles</option>
      <option value="customer" <?php echo e($role=='customer'?'selected':''); ?>>Customer</option>
      <option value="admin" <?php echo e($role=='admin'?'selected':''); ?>>Admin</option>
      <option value="vendor" <?php echo e($role=='vendor'?'selected':''); ?>>Vendor</option>
    </select>
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="active" <?php echo e($status=='active'?'selected':''); ?>>Active</option>
      <option value="blocked" <?php echo e($status=='blocked'?'selected':''); ?>>Blocked</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $role || $status): ?>
    <div class="form-group" style="justify-content:flex-end">
      <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">
  <?php echo e($users->total()); ?> user(s) found
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td style="color:var(--muted)">#<?php echo e($user->id); ?></td>
        <td style="font-weight:600;max-width:150px"><?php echo e($user->name); ?></td>
        <td style="color:var(--muted);font-size:12px"><?php echo e($user->email); ?></td>
        <td style="color:var(--muted);font-size:12px"><?php echo e($user->phone); ?></td>
        <td>
          <span class="badge badge-gray"><?php echo e(trim(strip_tags(str_replace(['"','[',']','\\'], '', $user->role)))); ?></span>
        </td>
        <td>
          <?php if($user->is_blocked): ?>
            <span class="badge badge-red">Blocked</span>
          <?php else: ?>
            <span class="badge badge-green">Active</span>
          <?php endif; ?>
        </td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?php echo e($user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—'); ?></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            <?php if($user->is_blocked): ?>
              <form method="POST" action="<?php echo e(route('admin.users.unblock', $user->id)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-success btn-sm">Unblock</button>
              </form>
            <?php else: ?>
              <form method="POST" action="<?php echo e(route('admin.users.block', $user->id)); ?>" onsubmit="return confirm('Block this user?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.users.role', $user->id)); ?>" style="display:flex;gap:4px">
              <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
              <select name="role" style="padding:4px 6px;font-size:12px;height:28px">
                <option value="customer" <?php echo e(str_contains($user->role,'customer')?'selected':''); ?>>Customer</option>
                <option value="admin" <?php echo e(str_contains($user->role,'admin')?'selected':''); ?>>Admin</option>
                <option value="vendor" <?php echo e(str_contains($user->role,'vendor')?'selected':''); ?>>Vendor</option>
              </select>
              <button class="btn btn-ghost btn-sm">Set</button>
            </form>

            <form method="POST" action="<?php echo e(route('admin.users.delete', $user->id)); ?>" onsubmit="return confirm('Permanently delete this user and all their data?')">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No users found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination">
  <?php echo e($users->links('admin.pagination')); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/users.blade.php ENDPATH**/ ?>