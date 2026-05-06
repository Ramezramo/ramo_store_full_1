<?php $__env->startSection('title', 'Category & Brand Requests'); ?>
<?php $__env->startSection('page-title', 'Category & Brand Requests'); ?>

<?php $__env->startSection('topbar-actions'); ?>
  <a href="<?php echo e(route('admin.cbr')); ?>?status=pending" class="btn btn-sm <?php echo e($status === 'pending' ? 'btn-primary' : 'btn-ghost'); ?>">
    Pending <?php if($counts['pending'] > 0): ?><span style="background:rgba(255,255,255,.25);border-radius:10px;padding:0 5px;margin-left:2px;font-size:10px"><?php echo e($counts['pending']); ?></span><?php endif; ?>
  </a>
  <a href="<?php echo e(route('admin.cbr')); ?>?status=approved" class="btn btn-sm <?php echo e($status === 'approved' ? 'btn-primary' : 'btn-ghost'); ?>">Approved</a>
  <a href="<?php echo e(route('admin.cbr')); ?>?status=rejected" class="btn btn-sm <?php echo e($status === 'rejected' ? 'btn-primary' : 'btn-ghost'); ?>">Rejected</a>
  <a href="<?php echo e(route('admin.cbr')); ?>?status=" class="btn btn-sm <?php echo e($status === '' ? 'btn-primary' : 'btn-ghost'); ?>">All</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.req-note-form{display:none;margin-top:8px}
.req-note-form.open{display:block}
.req-row-actions{display:flex;gap:6px;align-items:flex-start;flex-direction:column}
</style>
<?php $__env->stopPush(); ?>

<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap">
  <?php $__currentLoopData = ['pending'=>['yellow','Pending'],'approved'=>['green','Approved'],'rejected'=>['red','Rejected']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s=>[$color,$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="stat-card" style="min-width:140px;flex:1">
    <div class="stat-value" style="font-size:22px"><?php echo e($counts[$s]); ?></div>
    <div class="stat-label"><?php echo e($label); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if(request('type') || true): ?>
<form method="GET" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
  <input type="hidden" name="status" value="<?php echo e($status); ?>">
  <div class="form-group">
    <label>Filter by Type</label>
    <select name="type" onchange="this.form.submit()" style="min-width:140px">
      <option value="" <?php echo e($type === '' ? 'selected' : ''); ?>>All Types</option>
      <option value="category" <?php echo e($type === 'category' ? 'selected' : ''); ?>>Category</option>
      <option value="brand" <?php echo e($type === 'brand' ? 'selected' : ''); ?>>Brand</option>
    </select>
  </div>
</form>
<?php endif; ?>

<?php if($requests->isEmpty()): ?>
  <div class="card" style="text-align:center;padding:48px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="color:var(--muted);margin-bottom:12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p style="color:var(--muted)">No <?php echo e($status); ?> requests found.</p>
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Name</th>
          <th>Description</th>
          <th>Vendor</th>
          <th>Status</th>
          <th>Date</th>
          <?php if($status === 'pending'): ?><th>Actions</th><?php else: ?><th>Admin Note</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="color:var(--muted)"><?php echo e($req->id); ?></td>
          <td>
            <?php if($req->type === 'category'): ?>
              <span class="badge badge-blue">Category</span>
            <?php else: ?>
              <span class="badge badge-purple">Brand</span>
            <?php endif; ?>
          </td>
          <td style="font-weight:600"><?php echo e($req->name); ?></td>
          <td style="color:var(--muted);max-width:180px;font-size:12px"><?php echo e($req->description ? Str::limit($req->description, 60) : '—'); ?></td>
          <td style="font-size:12px"><?php echo e($req->vendor_name ?? '—'); ?></td>
          <td>
            <?php if($req->status === 'pending'): ?>
              <span class="badge badge-yellow">Pending</span>
            <?php elseif($req->status === 'approved'): ?>
              <span class="badge badge-green">Approved</span>
            <?php else: ?>
              <span class="badge badge-red">Rejected</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--muted);font-size:12px;white-space:nowrap"><?php echo e(\Carbon\Carbon::parse($req->created_at)->format('M d, Y')); ?></td>

          <?php if($status === 'pending'): ?>
          <td>
            <div class="req-row-actions">
              
              <button onclick="toggleNote('approve-<?php echo e($req->id); ?>')" class="btn btn-sm btn-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
              </button>
              <div id="approve-<?php echo e($req->id); ?>" class="req-note-form">
                <form method="POST" action="<?php echo e(route('admin.cbr.approve', $req->id)); ?>">
                  <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                  <input type="text" name="admin_note" class="form-control" placeholder="Note (optional)" style="margin-bottom:4px;padding:5px 8px;border-radius:5px;border:1px solid var(--border);background:var(--card);color:var(--text);font-size:12px;width:200px">
                  <button type="submit" class="btn btn-sm btn-success">Confirm Approve</button>
                </form>
              </div>

              
              <button onclick="toggleNote('reject-<?php echo e($req->id); ?>')" class="btn btn-sm btn-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
              </button>
              <div id="reject-<?php echo e($req->id); ?>" class="req-note-form">
                <form method="POST" action="<?php echo e(route('admin.cbr.reject', $req->id)); ?>">
                  <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                  <input type="text" name="admin_note" class="form-control" placeholder="Reason (optional)" style="margin-bottom:4px;padding:5px 8px;border-radius:5px;border:1px solid var(--border);background:var(--card);color:var(--text);font-size:12px;width:200px">
                  <button type="submit" class="btn btn-sm btn-danger">Confirm Reject</button>
                </form>
              </div>
            </div>
          </td>
          <?php else: ?>
          <td style="color:var(--muted);font-size:12px"><?php echo e($req->admin_note ?: '—'); ?></td>
          <?php endif; ?>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
  <?php if($requests->hasPages()): ?>
    <div class="pagination" style="margin-top:16px"><?php echo e($requests->links()); ?></div>
  <?php endif; ?>
<?php endif; ?>

<script>
function toggleNote(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/category-brand-requests/index.blade.php ENDPATH**/ ?>