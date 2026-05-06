<?php $__env->startSection('title', 'My Requests'); ?>
<?php $__env->startSection('page-title', 'Category & Brand Requests'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <p style="color:var(--mid);font-size:13px">Track the status of your submitted category and brand requests.</p>
  <a href="<?php echo e(route('vendor.requests.create')); ?>" class="vs-btn vs-btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Request
  </a>
</div>

<?php if($requests->isEmpty()): ?>
  <div style="background:var(--white);border:1px solid var(--light);border-radius:12px;padding:48px;text-align:center">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="color:var(--mid);margin-bottom:12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p style="color:var(--mid);font-size:14px">No requests yet. Submit one to get started.</p>
    <a href="<?php echo e(route('vendor.requests.create')); ?>" class="vs-btn vs-btn-primary" style="margin-top:16px;display:inline-flex">Submit a Request</a>
  </div>
<?php else: ?>
  <div class="vs-table-wrap">
    <table class="vs-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Name</th>
          <th>Description</th>
          <th>Status</th>
          <th>Admin Note</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="color:var(--mid)"><?php echo e($req->id); ?></td>
          <td>
            <?php if($req->type === 'category'): ?>
              <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Category</span>
            <?php else: ?>
              <span style="background:#faf5ff;color:#6d28d9;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Brand</span>
            <?php endif; ?>
          </td>
          <td style="font-weight:600"><?php echo e($req->name); ?></td>
          <td style="color:var(--mid);max-width:200px"><?php echo e($req->description ? Str::limit($req->description, 60) : '—'); ?></td>
          <td>
            <?php if($req->status === 'pending'): ?>
              <span class="badge-pending">Pending</span>
            <?php elseif($req->status === 'approved'): ?>
              <span class="badge-approved">Approved</span>
            <?php else: ?>
              <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Rejected</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--mid);font-size:12px;max-width:180px"><?php echo e($req->admin_note ?: '—'); ?></td>
          <td style="color:var(--mid);font-size:12px;white-space:nowrap"><?php echo e(\Carbon\Carbon::parse($req->created_at)->format('M d, Y')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
  <?php if($requests->hasPages()): ?>
    <div style="margin-top:16px"><?php echo e($requests->links()); ?></div>
  <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.vendor.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/vendor/requests/index.blade.php ENDPATH**/ ?>