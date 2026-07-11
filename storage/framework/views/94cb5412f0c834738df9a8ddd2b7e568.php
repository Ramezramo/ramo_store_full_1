<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_users'])); ?></div>
    <div class="stat-label">Total Users</div>
    <div class="stat-sub"><?php echo e($stats['blocked_users']); ?> blocked</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_orders'])); ?></div>
    <div class="stat-label">Total Orders</div>
    <div class="stat-sub"><?php echo e($stats['pending_orders']); ?> pending</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_revenue'], 0)); ?></div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-sub">Completed orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_products'])); ?></div>
    <div class="stat-label">Products</div>
    <div class="stat-sub"><?php echo e($stats['pending_products']); ?> pending review</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_vendors'])); ?></div>
    <div class="stat-label">Vendors</div>
    <div class="stat-sub"><?php echo e($stats['pending_vendors']); ?> awaiting approval</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></div>
    <div class="stat-value"><?php echo e(number_format($stats['total_devices'])); ?></div>
    <div class="stat-label">Devices</div>
    <div class="stat-sub"><?php echo e($stats['blocked_devices']); ?> blocked</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <div class="card">
    <div class="card-title">Recent Orders</div>
    <?php if($recent_orders->isEmpty()): ?>
      <p style="color:var(--muted);font-size:13px">No orders yet.</p>
    <?php else: ?>
    <div class="table-wrap" style="border:none">
      <table>
        <thead><tr><th>#</th><th>Status</th><th>Total</th><th>Payment</th><th></th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $recent_orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="font-weight:700">#<?php echo e($o->id); ?></td>
          <td>
            <?php
              $sc = match($o->status) {
                'completed'=>'badge-green','pending'=>'badge-yellow',
                'processing'=>'badge-blue','cancelled','failed'=>'badge-red',
                default=>'badge-gray'
              };
            ?>
            <span class="badge <?php echo e($sc); ?>"><?php echo e($o->status); ?></span>
          </td>
          <td><?php echo e($o->currency_symbol); ?><?php echo e(number_format($o->final_total, 2)); ?></td>
          <td style="color:var(--muted);font-size:12px"><?php echo e($o->payment_method_title); ?></td>
          <td><a href="<?php echo e(route('admin.orders.detail', $o->id)); ?>" class="btn btn-ghost btn-sm">View</a></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:12px"><a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-ghost btn-sm">View all orders →</a></div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-title">Recent Users</div>
    <?php if($recent_users->isEmpty()): ?>
      <p style="color:var(--muted);font-size:13px">No users yet.</p>
    <?php else: ?>
    <div class="table-wrap" style="border:none">
      <table>
        <thead><tr><th>Name</th><th>Role</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $recent_users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td>
            <div style="font-weight:600"><?php echo e($u->name); ?></div>
            <div style="font-size:11px;color:var(--muted)"><?php echo e($u->email); ?></div>
          </td>
          <td><span class="badge badge-gray"><?php echo e(is_string($u->role) ? trim(strip_tags(str_replace(['"','[',']'], '', $u->role))) : $u->role); ?></span></td>
          <td>
            <?php if($u->is_blocked): ?>
              <span class="badge badge-red">Blocked</span>
            <?php else: ?>
              <span class="badge badge-green">Active</span>
            <?php endif; ?>
          </td>
          <td><a href="<?php echo e(route('admin.users')); ?>" class="btn btn-ghost btn-sm">View</a></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:12px"><a href="<?php echo e(route('admin.users')); ?>" class="btn btn-ghost btn-sm">View all users →</a></div>
    <?php endif; ?>
  </div>

</div>

<?php if($stats['pending_products'] > 0 || $stats['pending_vendors'] > 0): ?>
<div class="alert alert-error" style="margin-top:20px">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  Action required:
  <?php if($stats['pending_vendors'] > 0): ?>
    <a href="<?php echo e(route('admin.vendors')); ?>?status=pending" style="color:inherit;font-weight:700;text-decoration:underline"><?php echo e($stats['pending_vendors']); ?> vendor(s) awaiting approval</a>
  <?php endif; ?>
  <?php if($stats['pending_products'] > 0): ?>
    <?php if($stats['pending_vendors'] > 0): ?> &nbsp;·&nbsp; <?php endif; ?>
    <a href="<?php echo e(route('admin.products')); ?>?acceptance=pending" style="color:inherit;font-weight:700;text-decoration:underline"><?php echo e($stats['pending_products']); ?> product(s) pending review</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>