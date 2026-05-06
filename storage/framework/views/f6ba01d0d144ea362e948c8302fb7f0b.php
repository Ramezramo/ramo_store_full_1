<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'Users Management'); ?>

<?php $__env->startSection('content'); ?>

<div class="section">
  <div class="section-header">
    <div class="section-title">All Users</div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name, email, phone…" class="search-input">
    <select name="filter" class="filter-select">
      <option value="all" <?php echo e($filter === 'all' ? 'selected' : ''); ?>>All Users</option>
      <option value="active" <?php echo e($filter === 'active' ? 'selected' : ''); ?>>Active</option>
      <option value="blocked" <?php echo e($filter === 'blocked' ? 'selected' : ''); ?>>Blocked</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    <?php if($search || $filter !== 'all'): ?>
      <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>

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
          <td style="color:var(--muted)"><?php echo e($user->id); ?></td>
          <td style="font-weight:600"><?php echo e($user->name ?: '—'); ?></td>
          <td style="color:var(--muted)"><?php echo e($user->email); ?></td>
          <td style="color:var(--muted)"><?php echo e($user->phone ?: '—'); ?></td>
          <td>
            <?php $role = is_string($user->role) ? trim($user->role, '[]"') : $user->role; ?>
            <span class="badge <?php echo e($role === 'admin' || str_contains($role,'administrator') ? 'badge-purple' : 'badge-gray'); ?>">
              <?php echo e($role ?: 'user'); ?>

            </span>
          </td>
          <td>
            <?php if($user->is_blocked): ?>
              <span class="badge badge-red">Blocked</span>
            <?php else: ?>
              <span class="badge badge-green">Active</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted)"><?php echo e($user->created_at ? date('M d, Y', strtotime($user->created_at)) : '—'); ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              <?php if($user->is_blocked): ?>
                <form method="POST" action="<?php echo e(route('admin.users.unblock', $user->id)); ?>">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="btn btn-success btn-sm">Unblock</button>
                </form>
              <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.users.block', $user->id)); ?>" id="block-user-<?php echo e($user->id); ?>">
                  <?php echo csrf_field(); ?>
                  <button type="button" class="btn btn-warning btn-sm" onclick="confirm_action('block-user-<?php echo e($user->id); ?>', 'Block this user?')">Block</button>
                </form>
              <?php endif; ?>

              
              <form method="POST" action="<?php echo e(route('admin.users.role', $user->id)); ?>" style="display:flex;gap:4px">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <select name="role" class="filter-select" style="padding:4px 8px;font-size:11px">
                  <option value="normal_user" <?php echo e($role === 'normal_user' || $role === 'customer' ? 'selected' : ''); ?>>User</option>
                  <option value="vendor" <?php echo e(str_contains($role,'vendor') ? 'selected' : ''); ?>>Vendor</option>
                  <option value="admin" <?php echo e(str_contains($role,'admin') ? 'selected' : ''); ?>>Admin</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Set</button>
              </form>

              <form method="POST" action="<?php echo e(route('admin.users.delete', $user->id)); ?>" id="del-user-<?php echo e($user->id); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirm_action('del-user-<?php echo e($user->id); ?>', 'Permanently delete this user and all their data?')">Delete</button>
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
    <?php echo e($users->links()); ?>

  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/users/index.blade.php ENDPATH**/ ?>