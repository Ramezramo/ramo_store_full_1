<?php $__env->startSection('title', 'Wishlist — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">
  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span><strong>Wishlist</strong>
  </div>

  <?php if($products->isEmpty()): ?>
    <div class="empty" style="padding:100px 20px">
      <div class="empty-icon">♡</div>
      <h3>Your wishlist is empty</h3>
      <p>Save products you love to find them easily later.</p>
      <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="margin-top:24px">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="sec-head"><h2 class="sec-title">My Wishlist (<?php echo e($products->count()); ?>)</h2></div>
    <div class="product-grid">
      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="product-card" id="wish-<?php echo e($p->id); ?>">
        <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img" style="display:block">
          <?php if($p->thumbnail_url): ?>
            <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
          <?php else: ?>
            <div class="placeholder">👕</div>
          <?php endif; ?>
        </a>
        <div class="product-card-body">
          <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name"><?php echo e($p->name); ?></a>
          <div class="product-card-price">
            <?php if($p->on_sale): ?>
              <span class="price-main sale"><?php echo e(number_format($p->sale_price, 2)); ?> EGP</span>
              <span class="price-old"><?php echo e(number_format($p->price, 2)); ?></span>
            <?php else: ?>
              <span class="price-main"><?php echo e(number_format($p->price, 2)); ?> EGP</span>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:8px;margin-top:10px">
            <button onclick="addToCart(<?php echo e($p->id); ?>, '<?php echo e(addslashes($p->name)); ?>', <?php echo e($p->on_sale ? $p->sale_price : $p->price); ?>, '<?php echo e($p->thumbnail_url); ?>')" class="btn btn-dark" style="flex:1;padding:9px 12px;font-size:13px;border-radius:8px">Add to Cart</button>
            <form action="<?php echo e(route('wishlist.remove', $p->id)); ?>" method="POST">
              <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
              <button class="btn btn-outline" style="padding:9px 12px;font-size:13px;border-radius:8px;color:#e02020;border-color:#e02020" title="Remove">✕</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/wishlist.blade.php ENDPATH**/ ?>