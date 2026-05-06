<?php $__env->startSection('title', 'Reviews'); ?>
<?php $__env->startSection('page-title', 'Product Reviews'); ?>

<?php $__env->startSection('content'); ?>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Review text, title…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Rating</label>
    <select name="rating">
      <option value="">All Ratings</option>
      <?php for($i=5;$i>=1;$i--): ?>
        <option value="<?php echo e($i); ?>" <?php echo e($rating==$i?'selected':''); ?>><?php echo e($i); ?> ★</option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="approved">
      <option value="">All</option>
      <option value="1" <?php echo e($approved==='1'?'selected':''); ?>>Approved</option>
      <option value="0" <?php echo e($approved==='0'?'selected':''); ?>>Pending</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  <?php if($search || $rating || $approved !== ''): ?>
    <div class="form-group" style="justify-content:flex-end">
      <a href="<?php echo e(route('admin.reviews')); ?>" class="btn btn-ghost">Clear</a>
    </div>
  <?php endif; ?>
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px"><?php echo e($reviews->total()); ?> review(s)</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>User</th>
        <th>Rating</th>
        <th>Title</th>
        <th>Review</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td style="color:var(--muted)">#<?php echo e($review->id); ?></td>
        <td style="font-size:12px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <?php echo e($review->product_name ?? 'Product #'.$review->product_id); ?>

        </td>
        <td style="font-size:12px;color:var(--muted)">
          <div><?php echo e($review->user_name ?? '—'); ?></div>
          <?php if($review->is_verified_purchase): ?>
            <span class="badge badge-green" style="font-size:10px;padding:2px 5px">Verified</span>
          <?php endif; ?>
        </td>
        <td>
          <span style="color:<?php echo e($review->rating >= 4 ? 'var(--green)' : ($review->rating >= 3 ? 'var(--yellow)' : 'var(--red)')); ?>;font-weight:700;font-size:14px">
            <?php echo e(str_repeat('★', $review->rating)); ?><?php echo e(str_repeat('☆', 5 - $review->rating)); ?>

          </span>
        </td>
        <td style="font-weight:600;font-size:13px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <?php echo e($review->title ?: '—'); ?>

        </td>
        <td style="font-size:12px;color:var(--muted);max-width:200px">
          <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo e($review->body); ?>">
            <?php echo e($review->body); ?>

          </div>
          <div style="font-size:11px;margin-top:2px">👍 <?php echo e($review->helpful_count); ?> helpful</div>
        </td>
        <td>
          <?php if($review->approved): ?>
            <span class="badge badge-green">Approved</span>
          <?php else: ?>
            <span class="badge badge-yellow">Pending</span>
          <?php endif; ?>
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          <?php echo e($review->created_at ? \Carbon\Carbon::parse($review->created_at)->format('d M Y') : '—'); ?>

        </td>
        <td>
          <div style="display:flex;gap:6px">
            <form method="POST" action="<?php echo e(route('admin.reviews.toggle', $review->id)); ?>">
              <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
              <?php if($review->approved): ?>
                <button class="btn btn-warning btn-sm">Unapprove</button>
              <?php else: ?>
                <button class="btn btn-success btn-sm">Approve</button>
              <?php endif; ?>
            </form>
            <form method="POST" action="<?php echo e(route('admin.reviews.delete', $review->id)); ?>" onsubmit="return confirm('Delete this review?')">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No reviews found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="pagination"><?php echo e($reviews->links('admin.pagination')); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/reviews.blade.php ENDPATH**/ ?>