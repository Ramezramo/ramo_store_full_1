<?php $pageTitle = 'My Orders'; ?>

<?php $__env->startSection('account-content'); ?>
<div class="acc-section-title">Order History</div>

<?php if($orders->count()): ?>
<div class="orders-table-wrap">
  <table class="orders-table">
    <thead>
      <tr>
        <th>Order #</th><th>Date</th><th>Status</th><th>Payment</th><th>Total</th><th></th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><strong>#<?php echo e($order->id); ?></strong></td>
        <td><?php echo e(\Carbon\Carbon::parse($order->date_created)->format('M d, Y')); ?></td>
        <td><span class="status-badge status-<?php echo e($order->status); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
        <td><?php echo e($order->payment_method_title); ?></td>
        <td><strong><?php echo e(number_format($order->final_total, 2)); ?> EGP</strong></td>
        <td><a href="<?php echo e(route('account.order', $order->id)); ?>" class="btn btn-outline" style="font-size:12px;padding:6px 14px">View</a></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>

  <?php if($orders->hasPages()): ?>
  <div class="pagination-wrap" style="margin:20px 18px">
    <?php if($orders->onFirstPage()): ?><span>‹</span><?php else: ?><a href="<?php echo e($orders->previousPageUrl()); ?>">‹</a><?php endif; ?>
    <?php $__currentLoopData = $orders->getUrlRange(max(1,$orders->currentPage()-2), min($orders->lastPage(),$orders->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if($page == $orders->currentPage()): ?><span class="active-page"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($orders->hasMorePages()): ?><a href="<?php echo e($orders->nextPageUrl()); ?>">›</a><?php else: ?><span>›</span><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
  <div class="empty">
    <div class="empty-icon">📦</div>
    <h3>No orders yet</h3>
    <p>When you place an order it will appear here.</p>
    <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="margin-top:20px">Shop Now</a>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/orders.blade.php ENDPATH**/ ?>