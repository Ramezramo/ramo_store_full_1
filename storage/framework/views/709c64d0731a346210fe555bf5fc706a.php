<?php $pageTitle = 'My Refund Requests'; ?>

<?php $__env->startSection('account-content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div class="acc-section-title" style="margin-bottom:0">Refund & Return Requests</div>
  <a href="<?php echo e(route('account.refunds.create')); ?>" class="btn btn-dark" style="font-size:13px;padding:9px 18px">+ New Request</a>
</div>

<?php if($refunds->count()): ?>
<div class="orders-table-wrap">
  <table class="orders-table">
    <thead>
      <tr>
        <th>Req #</th>
        <th>Order #</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $sc = match($r->status) {
          'approved'  => 'status-completed',
          'rejected'  => 'status-cancelled',
          'completed' => 'status-completed',
          'cancelled' => 'status-cancelled',
          default     => 'status-processing',
        };
        $reasonLabel = match($r->reason) {
          'damaged'          => 'Item Damaged',
          'wrong_item'       => 'Wrong Item',
          'changed_mind'     => 'Changed Mind',
          'not_as_described' => 'Not as Described',
          default            => 'Other',
        };
      ?>
      <tr>
        <td><strong>#<?php echo e($r->id); ?></strong></td>
        <td><a href="<?php echo e(route('account.order', $r->order_id)); ?>" style="color:inherit">#<?php echo e($r->order_id); ?></a></td>
        <td><span style="font-weight:600;text-transform:capitalize"><?php echo e($r->type); ?></span></td>
        <td style="color:#666;font-size:13px"><?php echo e($reasonLabel); ?></td>
        <td><span class="status-badge <?php echo e($sc); ?>"><?php echo e(ucfirst($r->status)); ?></span></td>
        <td style="font-size:12px;color:#888"><?php echo e(\Carbon\Carbon::parse($r->created_at)->format('M d, Y')); ?></td>
        <td>
          <a href="<?php echo e(route('account.refunds.show', $r->id)); ?>" class="btn btn-outline" style="font-size:12px;padding:5px 12px">View</a>
          <?php if($r->status === 'pending'): ?>
            <form method="POST" action="<?php echo e(route('account.refunds.cancel', $r->id)); ?>" style="display:inline" onsubmit="return confirm('Cancel this request?')">
              <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
              <button class="btn btn-outline" style="font-size:12px;padding:5px 12px;margin-left:4px;color:#dc2626;border-color:#dc2626">Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>

  <?php if($refunds->hasPages()): ?>
  <div class="pagination-wrap" style="margin:20px 18px">
    <?php if($refunds->onFirstPage()): ?><span>‹</span><?php else: ?><a href="<?php echo e($refunds->previousPageUrl()); ?>">‹</a><?php endif; ?>
    <?php $__currentLoopData = $refunds->getUrlRange(max(1,$refunds->currentPage()-2), min($refunds->lastPage(),$refunds->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if($page == $refunds->currentPage()): ?><span class="active-page"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($refunds->hasMorePages()): ?><a href="<?php echo e($refunds->nextPageUrl()); ?>">›</a><?php else: ?><span>›</span><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
  <div class="empty">
    <div class="empty-icon">🔄</div>
    <h3>No requests yet</h3>
    <p>Submit a refund or return request for any eligible order.</p>
    <a href="<?php echo e(route('account.refunds.create')); ?>" class="btn btn-dark" style="margin-top:20px">Request Refund / Return</a>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/refunds.blade.php ENDPATH**/ ?>