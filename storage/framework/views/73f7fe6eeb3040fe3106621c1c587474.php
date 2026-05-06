<?php $__env->startSection('title', 'Devices'); ?>
<?php $__env->startSection('page-title', 'Device Management'); ?>

<?php $__env->startSection('content'); ?>


<div class="card" style="margin-bottom:20px">
  <div class="card-title">Block All Tokens by Device ID</div>
  <form method="POST" action="<?php echo e(route('admin.devices.block-by-id')); ?>" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    <?php echo csrf_field(); ?>
    <div class="form-group">
      <label>Device ID</label>
      <input type="text" name="device_id" placeholder="e.g. NRD90M or unique-device-id-12345" style="width:320px">
    </div>
    <button type="submit" class="btn btn-danger" onclick="return confirm('Block ALL tokens for this device ID?')">Block All Tokens</button>
  </form>
</div>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Device ID, identifier, name…" style="width:260px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="blocked">
      <option value="">All Devices</option>
      <option value="0" <?php echo e($blocked==='0'?'selected':''); ?>>Active</option>
      <option value="1" <?php echo e($blocked==='1'?'selected':''); ?>>Blocked</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $blocked !== ''): ?>
    <div class="form-group" style="justify-content:flex-end">
      <a href="<?php echo e(route('admin.devices')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($devices->total()); ?> device token(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Device ID</th>
        <th>Identifier</th>
        <th>User ID</th>
        <th>Device Info</th>
        <th>Last Used</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $info = @json_decode($device->about_device, true);
        $model = $info['model'] ?? ($info['model'] ?? null);
        $brand = $info['brand'] ?? null;
        $os    = $info['version']['release'] ?? $info['os'] ?? null;
      ?>
      <tr>
        <td style="color:var(--muted)">#<?php echo e($device->id); ?></td>
        <td style="font-size:12px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace" title="<?php echo e($device->device_id); ?>"><?php echo e($device->device_id); ?></td>
        <td style="font-size:12px;color:var(--muted)"><?php echo e($device->identifier ?: '—'); ?></td>
        <td style="color:var(--muted)"><?php echo e($device->tokenable_id ?: '—'); ?></td>
        <td style="font-size:12px;color:var(--muted)">
          <?php if($model): ?> <span><?php echo e($brand ? $brand.' ' : ''); ?><?php echo e($model); ?></span><?php endif; ?>
          <?php if($os): ?> <span style="display:block;font-size:11px">Android <?php echo e($os); ?></span><?php endif; ?>
          <?php if(!$model && !$os): ?> — <?php endif; ?>
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?php echo e($device->last_used_at ? \Carbon\Carbon::parse($device->last_used_at)->format('d M Y') : '—'); ?></td>
        <td>
          <?php if($device->blocked): ?>
            <span class="badge badge-red">Blocked</span>
          <?php else: ?>
            <span class="badge badge-green">Active</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:6px">
            <?php if($device->blocked): ?>
              <form method="POST" action="<?php echo e(route('admin.devices.unblock', $device->id)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-success btn-sm">Unblock</button>
              </form>
            <?php else: ?>
              <form method="POST" action="<?php echo e(route('admin.devices.block', $device->id)); ?>" onsubmit="return confirm('Block this device token?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.devices.delete', $device->id)); ?>" onsubmit="return confirm('Delete this device token?')">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No devices found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($devices->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/devices.blade.php ENDPATH**/ ?>