<?php $__env->startSection('title', 'Seller Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>


<?php if(in_array($vendor->status, ['approved', 'active'])): ?>
  <div class="vs-alert vs-alert-success" style="margin-bottom:20px">
    ✓ Your store is <strong>live</strong>. Customers can browse your products.
    <a href="<?php echo e(route('vendor.store', $vendor->id)); ?>" target="_blank" style="margin-left:10px;font-weight:700;text-decoration:underline">View My Store →</a>
  </div>
<?php elseif($vendor->status === 'pending'): ?>
  <div class="vs-alert vs-alert-warning" style="margin-bottom:20px">
    ⏳ Your application is <strong>under review</strong>. Our team will approve your store shortly.
  </div>
<?php elseif($vendor->status === 'rejected'): ?>
  <div class="vs-alert vs-alert-error" style="margin-bottom:20px">
    ✕ Your application was <strong>not approved</strong>. Please contact support for more information.
  </div>
<?php else: ?>
  <div class="vs-alert vs-alert-warning" style="margin-bottom:20px">
    ⚠ Your store status is <strong><?php echo e(ucfirst($vendor->status)); ?></strong>. Please contact support if you think this is a mistake.
  </div>
<?php endif; ?>


<div class="vs-stat-grid">
  <div class="vs-stat-card">
    <div class="vs-stat-icon">📦</div>
    <div class="vs-stat-value"><?php echo e($stats['products']); ?></div>
    <div class="vs-stat-label">Products Listed</div>
  </div>
  <div class="vs-stat-card">
    <div class="vs-stat-icon">🛍️</div>
    <div class="vs-stat-value"><?php echo e($stats['orders']); ?></div>
    <div class="vs-stat-label">Total Orders</div>
  </div>
  <div class="vs-stat-card">
    <div class="vs-stat-icon">⭐</div>
    <div class="vs-stat-value"><?php echo e(number_format((float)$stats['rating'], 1)); ?></div>
    <div class="vs-stat-label">Avg Rating</div>
  </div>
</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:14px">Shop Details</div>
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
      <?php if($vendor->shop_logo_url): ?>
        <img src="<?php echo e($vendor->shop_logo_url); ?>" alt="" style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:1px solid var(--light)">
      <?php else: ?>
        <div style="width:60px;height:60px;border-radius:12px;background:#f0f0ec;display:flex;align-items:center;justify-content:center;font-size:24px">🏪</div>
      <?php endif; ?>
      <div>
        <div style="font-size:16px;font-weight:700"><?php echo e($vendor->shop_name); ?></div>
        <span class="badge-<?php echo e($vendor->status); ?>"><?php echo e(ucfirst($vendor->status)); ?></span>
      </div>
    </div>
    <div style="font-size:13px;color:var(--mid);display:flex;flex-direction:column;gap:6px">
      <div>📧 <?php echo e($vendor->email); ?></div>
      <div>📞 <?php echo e($vendor->phone); ?></div>
      <?php if($vendor->shop_address): ?>
        <div>📍 <?php echo e($vendor->shop_address); ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:14px">Quick Actions</div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="<?php echo e(route('vendor.store', $vendor->id)); ?>" target="_blank"
         style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafaf8;border:1px solid var(--light);border-radius:9px;font-size:13px;font-weight:600;transition:.15s"
         onmouseover="this.style.borderColor='#e85d26'" onmouseout="this.style.borderColor='var(--light)'">
        🌐 <span>View My Public Store</span>
      </a>
      <a href="<?php echo e(route('shop')); ?>" target="_blank"
         style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafaf8;border:1px solid var(--light);border-radius:9px;font-size:13px;font-weight:600;transition:.15s"
         onmouseover="this.style.borderColor='#e85d26'" onmouseout="this.style.borderColor='var(--light)'">
        🛒 <span>Browse the Marketplace</span>
      </a>
      <div style="padding:12px 14px;background:#fff9f5;border:1px solid #fde8d8;border-radius:9px;font-size:12px;color:#92400e">
        💡 To add products, ask your account manager or use the seller API.
      </div>
    </div>
  </div>
</div>


<?php if($recentProducts->count()): ?>
<div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
  <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:16px">Recent Products</div>
  <div class="vs-table-wrap">
    <table class="vs-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $recentProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="font-weight:600"><?php echo e($p->name); ?></td>
          <td>
            <?php if($p->on_sale && $p->min_sale > 0): ?>
              <span style="font-weight:700;color:var(--orange)"><?php echo e(number_format($p->min_sale,2)); ?> EGP</span>
              <span style="text-decoration:line-through;color:var(--mid);font-size:11px;margin-left:4px"><?php echo e(number_format($p->min_regular,2)); ?></span>
            <?php else: ?>
              <?php echo e(number_format($p->min_regular ?? $p->min_sale ?? 0, 2)); ?> EGP
            <?php endif; ?>
          </td>
          <td>
            <?php $st = $p->status ?? 'active'; ?>
            <span class="badge-<?php echo e($st === 'active' ? 'approved' : ($st === 'pending' ? 'pending' : 'blocked')); ?>">
              <?php echo e(ucfirst($st)); ?>

            </span>
          </td>
          <td><a href="<?php echo e(route('product', $p->id)); ?>" target="_blank" style="font-size:12px;color:var(--orange);font-weight:600">View →</a></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:32px;text-align:center;color:var(--mid)">
    <div style="font-size:36px;margin-bottom:10px">📦</div>
    <div style="font-weight:600;margin-bottom:4px">No products yet</div>
    <div style="font-size:13px">Once your store is approved, your products will appear here.</div>
  </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.vendor.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/vendor/dashboard.blade.php ENDPATH**/ ?>