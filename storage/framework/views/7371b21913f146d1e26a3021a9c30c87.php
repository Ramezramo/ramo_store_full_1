<?php $__env->startSection('title', 'Shop — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span><strong>Shop</strong>
    <?php if(request('search')): ?><span>/</span><span>"<?php echo e(request('search')); ?>"</span><?php endif; ?>
  </div>

  <button class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    Filters & Categories
  </button>

  <div class="shop-layout">

    
    <aside class="sidebar">
      <h3>Categories</h3>
      <ul class="cat-list">
        <li>
          <a href="<?php echo e(route('shop', array_filter(request()->except('category','page')))); ?>"
             class="<?php echo e(!request('category') ? 'active' : ''); ?>">All Products</a>
        </li>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
          <a href="<?php echo e(route('shop', array_merge(request()->except('category','page'), ['category'=>$cat->id]))); ?>"
             class="<?php echo e(request('category') == $cat->id ? 'active' : ''); ?>"><?php echo e($cat->name); ?></a>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>

      <hr class="sidebar-divider">
      <h3>Sort By</h3>
      <form method="GET" action="<?php echo e(route('shop')); ?>" id="sort-form">
        <?php $__currentLoopData = request()->except('sort','page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
          <option value=""          <?php echo e(!request('sort') ? 'selected' : ''); ?>>Latest</option>
          <option value="price_asc" <?php echo e(request('sort')==='price_asc'  ? 'selected' : ''); ?>>Price: Low → High</option>
          <option value="price_desc"<?php echo e(request('sort')==='price_desc' ? 'selected' : ''); ?>>Price: High → Low</option>
        </select>
      </form>
    </aside>

    
    <div>
      <div class="shop-toolbar">
        <span class="result-count"><?php echo e($products->total()); ?> product<?php echo e($products->total()!=1?'s':''); ?></span>
        <div class="search-bar">
          <form method="GET" action="<?php echo e(route('shop')); ?>" style="display:contents">
            <?php if(request('category')): ?><input type="hidden" name="category" value="<?php echo e(request('category')); ?>"><?php endif; ?>
            <input type="text" name="search" placeholder="Search…" value="<?php echo e(request('search')); ?>">
            <button type="submit">🔍</button>
          </form>
        </div>
      </div>

      <?php if($products->count()): ?>
        <div class="product-grid">
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="product-card">
            <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img">
              <?php if($p->thumbnail_url): ?>
                <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
              <?php else: ?>
                <div class="placeholder">👕</div>
              <?php endif; ?>
              <?php if($p->on_sale): ?><span class="badge-sale"><?php if($p->discount_percentage > 0): ?>-<?php echo e(round($p->discount_percentage)); ?>%<?php else: ?> SALE <?php endif; ?></span><?php endif; ?>
              <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($p->id); ?>)" title="Add to Wishlist">♡</button>
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
              <button class="card-add-btn" onclick="addToCart(<?php echo e($p->id); ?>,'<?php echo e(addslashes($p->name)); ?>',<?php echo e($p->display_price); ?>,'<?php echo e($p->thumbnail_url); ?>')">Add to Cart</button>
              <?php if(!empty($p->coupon)): ?>
              <?php $__c=$p->coupon;$__b=$p->on_sale?$p->sale_price:$p->price;$__cp=$__c->discount_type==='percent'?$__b*(1-(float)$__c->amount/100):max(0,$__b-(float)$__c->amount); ?>
              <a href="<?php echo e(route('cart')); ?>" class="pc-coupon-bar" onclick="event.preventDefault();saveCouponAndGo('<?php echo e(strtoupper($__c->code)); ?>','<?php echo e(route('cart')); ?>')" title="Click to apply this coupon at checkout">
                <span class="pc-coupon-left">🏷️ WITH CODE <strong class="pc-coupon-code"><?php echo e(strtoupper($__c->code)); ?></strong></span>
                <span class="pc-coupon-right">↓ <?php echo e(number_format($__cp,0)); ?> EGP</span>
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if($products->hasPages()): ?>
        <div class="pagination-wrap">
          <?php if($products->onFirstPage()): ?><span>‹</span><?php else: ?><a href="<?php echo e($products->previousPageUrl()); ?>">‹</a><?php endif; ?>
          <?php $__currentLoopData = $products->getUrlRange(max(1,$products->currentPage()-2), min($products->lastPage(),$products->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($page == $products->currentPage()): ?><span class="active-page"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a><?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php if($products->hasMorePages()): ?><a href="<?php echo e($products->nextPageUrl()); ?>">›</a><?php else: ?><span>›</span><?php endif; ?>
        </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="empty">
          <div class="empty-icon">🔍</div>
          <h3>No products found</h3>
          <p>Try a different search term or browse all categories.</p>
          <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="margin-top:20px">Clear filters</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleShopFilter() {
  const sidebar = document.querySelector('.sidebar');
  const btn = document.getElementById('shop-filter-btn');
  const open = sidebar.classList.toggle('mobile-open');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Close Filters'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters & Categories';
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/shop.blade.php ENDPATH**/ ?>