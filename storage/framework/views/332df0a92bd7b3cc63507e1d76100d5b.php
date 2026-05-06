<?php $pageTitle = 'My Reviews'; ?>

<?php $__env->startSection('account-content'); ?>
<div class="acc-section-title">My Reviews</div>

<?php if($reviews->count()): ?>
  <div class="acc-reviews-list">
    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="acc-review-card">
      <div class="acc-review-top">
        <div>
          <a href="<?php echo e(route('product', $review->product_id)); ?>" class="acc-review-product">
            <?php echo e($review->product_name ?? 'Product'); ?>

          </a>
          <div class="acc-review-stars">
            <?php for($i = 1; $i <= 5; $i++): ?>
              <span style="color:<?php echo e($i <= $review->rating ? '#f59e0b' : 'var(--c-light)'); ?>;font-size:16px">★</span>
            <?php endfor; ?>
            <span style="font-size:12px;color:var(--c-mid);margin-left:4px"><?php echo e($review->rating); ?>/5</span>
          </div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <?php if($review->approved): ?>
            <span class="acc-review-badge approved">Published</span>
          <?php else: ?>
            <span class="acc-review-badge pending">Pending</span>
          <?php endif; ?>
          <?php if($review->is_verified_purchase): ?>
            <div style="font-size:11px;color:#22a35c;margin-top:4px;font-weight:600">✓ Verified Purchase</div>
          <?php endif; ?>
        </div>
      </div>

      <?php if($review->title): ?>
        <div class="acc-review-title"><?php echo e($review->title); ?></div>
      <?php endif; ?>
      <p class="acc-review-body"><?php echo e($review->body); ?></p>

      <div class="acc-review-footer">
        <span style="font-size:12px;color:var(--c-mid)">
          <?php echo e(\Carbon\Carbon::parse($review->created_at)->format('M d, Y')); ?>

        </span>
        <?php if($review->helpful_count > 0): ?>
          <span style="font-size:12px;color:var(--c-mid)">
            👍 <?php echo e($review->helpful_count); ?> found helpful
          </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php else: ?>
  <div class="empty">
    <div class="empty-icon">⭐</div>
    <h3>No reviews yet</h3>
    <p>After purchasing a product, share your experience to help other shoppers.</p>
    <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="margin-top:20px">Browse Products</a>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.account.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/account/reviews.blade.php ENDPATH**/ ?>