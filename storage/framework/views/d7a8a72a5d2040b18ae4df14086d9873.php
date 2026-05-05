<?php $__env->startSection('title', $vendor->shop_name . ' — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  
  <div class="vendor-page-header">
    <?php if($vendor->banner_url): ?>
      <div class="vendor-page-banner" style="background-image:url('<?php echo e($vendor->banner_url); ?>')"></div>
    <?php else: ?>
      <div class="vendor-page-banner vendor-page-banner-default"></div>
    <?php endif; ?>

    <div class="vendor-page-identity">
      <div class="vendor-page-logo-wrap">
        <?php if($vendor->logo_url): ?>
          <img src="<?php echo e($vendor->logo_url); ?>" alt="<?php echo e($vendor->shop_name); ?>" class="vendor-page-logo">
        <?php else: ?>
          <div class="vendor-page-logo-ph">🏪</div>
        <?php endif; ?>
      </div>
      <div class="vendor-page-meta">
        <h1 class="vendor-page-name"><?php echo e($vendor->shop_name); ?></h1>
        <div class="vendor-page-stats">
          <?php if((float)$vendor->rating > 0): ?>
            <span class="vendor-stat"><span style="color:#f5a623">★</span> <?php echo e(number_format((float)$vendor->rating,1)); ?>

              <?php if($vendor->rating_count > 0): ?>(<?php echo e($vendor->rating_count); ?>)<?php endif; ?>
            </span>
          <?php endif; ?>
          <?php if($vendor->product_count > 0): ?>
            <span class="vendor-stat">🛍️ <?php echo e($vendor->product_count); ?> products</span>
          <?php endif; ?>
          <?php if($vendor->shop_address): ?>
            <span class="vendor-stat">📍 <?php echo e($vendor->shop_address); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  
  <div class="vendor-page-toolbar">
    <div style="font-size:14px;color:var(--c-mid)">
      <?php echo e($products->total()); ?> product<?php echo e($products->total() != 1 ? 's' : ''); ?>

    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <span style="font-size:13px;color:var(--c-mid)">Sort:</span>
      <select onchange="location.href=this.value" class="sort-select" style="font-size:13px">
        <option value="<?php echo e(request()->fullUrlWithQuery(['sort'=>''])); ?>" <?php echo e(!request('sort') ? 'selected' : ''); ?>>Newest</option>
        <option value="<?php echo e(request()->fullUrlWithQuery(['sort'=>'price_asc'])); ?>" <?php echo e(request('sort')==='price_asc' ? 'selected' : ''); ?>>Price: Low → High</option>
        <option value="<?php echo e(request()->fullUrlWithQuery(['sort'=>'price_desc'])); ?>" <?php echo e(request('sort')==='price_desc' ? 'selected' : ''); ?>>Price: High → Low</option>
      </select>
    </div>
  </div>

  <?php if($products->count()): ?>
    <div class="product-grid" style="margin-bottom:40px">
      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="product-card">
        <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img">
          <?php if($p->thumbnail_url): ?>
            <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
          <?php else: ?>
            <div class="placeholder">🛍️</div>
          <?php endif; ?>
          <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
          <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($p->id); ?>)" title="Wishlist">♡</button>
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
          <button class="card-add-btn"
                  onclick="addToCart(<?php echo e($p->id); ?>,'<?php echo e(addslashes($p->name)); ?>',<?php echo e($p->display_price); ?>,'<?php echo e($p->thumbnail_url); ?>')">
            Add to Cart
          </button>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div style="margin-bottom:40px">
      <?php echo e($products->links()); ?>

    </div>
  <?php else: ?>
    <div style="text-align:center;padding:80px 20px;color:var(--c-mid)">
      <div style="font-size:48px;margin-bottom:16px">📦</div>
      <div style="font-size:18px;font-weight:600">No products yet</div>
      <div style="font-size:14px;margin-top:8px">This vendor hasn't listed any products.</div>
      <a href="<?php echo e(route('shop')); ?>" class="btn btn-primary" style="margin-top:24px;display:inline-block">Browse Shop</a>
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.vendor-page-header { margin-bottom: 32px; }

.vendor-page-banner {
  width: 100%; height: 180px; border-radius: 14px; object-fit: cover;
  background-size: cover; background-position: center;
  margin-bottom: -50px;
}
.vendor-page-banner-default {
  background: linear-gradient(135deg, #e85d26 0%, #f59e0b 50%, #22c55e 100%);
}

.vendor-page-identity {
  display: flex; align-items: flex-end; gap: 16px;
  padding: 0 4px; position: relative; z-index: 1;
}

.vendor-page-logo-wrap {
  width: 90px; height: 90px; border-radius: 16px;
  border: 3px solid #fff; overflow: hidden;
  background: #f5f5f5; flex-shrink: 0;
  box-shadow: 0 2px 12px rgba(0,0,0,.12);
}
.vendor-page-logo { width: 100%; height: 100%; object-fit: cover; }
.vendor-page-logo-ph {
  width: 100%; height: 100%; display: flex; align-items: center;
  justify-content: center; font-size: 36px; background: #f0f0ec;
}

.vendor-page-meta { padding-bottom: 4px; }
.vendor-page-name { font-size: 22px; font-weight: 700; margin: 0 0 6px; }
.vendor-page-stats { display: flex; flex-wrap: wrap; gap: 12px; }
.vendor-stat { font-size: 13px; color: var(--c-mid); }

.vendor-page-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 20px; padding: 12px 16px;
  background: #fafaf8; border-radius: 10px;
  border: 1px solid #ebebeb;
}

.sort-select {
  padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px;
  background: #fff; cursor: pointer; outline: none;
}

@media(max-width:640px) {
  .vendor-page-banner { height: 120px; margin-bottom: -36px; }
  .vendor-page-logo-wrap { width: 68px; height: 68px; }
  .vendor-page-name { font-size: 17px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/vendor.blade.php ENDPATH**/ ?>