<?php $__env->startSection('title', 'Shop — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Category sidebar hierarchy ─────────────────────────── */
.cat-list { list-style:none; margin:0; padding:0; }
.cat-list > li { border-bottom:1px solid #f0f0ee; }
.cat-list > li:last-child { border-bottom:none; }

/* Parent row */
.cat-parent-row {
  display:flex; align-items:center; justify-content:space-between;
  gap:6px;
}
.cat-parent-link {
  flex:1; display:block; padding:9px 4px 9px 0;
  font-size:14px; font-weight:600; color:#333;
  transition:.15s;
}
.cat-parent-link:hover { color:#e85d26; }
.cat-parent-link.active { color:#e85d26; }

/* Toggle chevron */
.cat-toggle {
  background:none; border:none; cursor:pointer;
  color:#aaa; padding:6px; border-radius:4px;
  transition:.15s; flex-shrink:0; line-height:1;
}
.cat-toggle:hover { color:#e85d26; background:#fff5f2; }
.cat-toggle svg { display:block; transition:transform .2s; }
.cat-toggle.open svg { transform:rotate(180deg); }

/* Children list */
.cat-children {
  list-style:none; margin:0; padding:0 0 6px 14px;
  display:none;
  border-left:2px solid #f0ede8;
}
.cat-children.open { display:block; }
.cat-children li { }
.cat-children a {
  display:flex; align-items:center; gap:5px;
  padding:5px 4px; font-size:13px; color:#666;
  transition:.15s; border-radius:4px;
}
.cat-children a:hover { color:#e85d26; }
.cat-children a.active { color:#e85d26; font-weight:600; }
.cat-children a::before {
  content:''; width:5px; height:5px;
  border-radius:50%; background:#d5cfc9; flex-shrink:0;
}
.cat-children a.active::before { background:#e85d26; }

/* All Products link */
.cat-all-link {
  display:block; padding:10px 4px; font-size:14px; font-weight:600;
  color:#333; border-bottom:1px solid #f0f0ee; margin-bottom:4px;
  transition:.15s;
}
.cat-all-link:hover,.cat-all-link.active { color:#e85d26; }

/* Product count badge next to category */
.cat-count {
  font-size:11px; color:#bbb; font-weight:400; margin-left:3px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  
  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span><strong>Shop</strong>
    <?php if(request('search')): ?><span>/</span><span>"<?php echo e(request('search')); ?>"</span><?php endif; ?>
    <?php if($activeCategoryId): ?>
      <?php
        $activeCat = $parentCats->firstWhere('id', $activeCategoryId)
          ?? $childCats->flatten()->firstWhere('id', $activeCategoryId);
      ?>
      <?php if($activeCat): ?><span>/</span><span><?php echo e($activeCat->name); ?></span><?php endif; ?>
    <?php endif; ?>
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
             class="cat-all-link <?php echo e(!$activeCategoryId ? 'active' : ''); ?>">
            All Products
          </a>
        </li>

        
        <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $hasChildren = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
            $isParentActive = $activeCategoryId == $parent->id;
            $isOpen = $activeParentId == $parent->id;
            $parentUrl = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));
          ?>
          <li>
            <div class="cat-parent-row">
              <a href="<?php echo e($parentUrl); ?>"
                 class="cat-parent-link <?php echo e($isParentActive ? 'active' : ''); ?>">
                <?php echo e($parent->name); ?>

              </a>
              <?php if($hasChildren): ?>
                <button class="cat-toggle <?php echo e($isOpen ? 'open' : ''); ?>"
                        onclick="toggleChildren('children-<?php echo e($parent->id); ?>', this)"
                        aria-label="Toggle sub-categories">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                    <polyline points="6 9 12 15 18 9"/>
                  </svg>
                </button>
              <?php endif; ?>
            </div>

            <?php if($hasChildren): ?>
              <ul class="cat-children <?php echo e($isOpen ? 'open' : ''); ?>" id="children-<?php echo e($parent->id); ?>">
                <?php $__currentLoopData = $childCats[$parent->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li>
                    <a href="<?php echo e(route('shop', array_merge(request()->except('category','page'), ['category' => $child->id]))); ?>"
                       class="<?php echo e($activeCategoryId == $child->id ? 'active' : ''); ?>">
                      <?php echo e($child->name); ?>

                    </a>
                  </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </ul>
            <?php endif; ?>
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
        <span class="result-count">
          <?php echo e($products->total()); ?> product<?php echo e($products->total()!=1?'s':''); ?>

          <?php if($activeCategoryId && isset($activeCat)): ?>
            in <strong><?php echo e($activeCat->name); ?></strong>
            <?php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]); ?>
            <?php if($isParentFilter): ?>
              <span style="font-size:12px;color:#aaa">(incl. sub-categories)</span>
            <?php endif; ?>
          <?php endif; ?>
        </span>
        <div class="search-bar">
          <form method="GET" action="<?php echo e(route('shop')); ?>" style="display:contents">
            <?php if($activeCategoryId): ?><input type="hidden" name="category" value="<?php echo e($activeCategoryId); ?>"><?php endif; ?>
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

function toggleChildren(listId, btn) {
  const list = document.getElementById(listId);
  const open = list.classList.toggle('open');
  btn.classList.toggle('open', open);
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/shop.blade.php ENDPATH**/ ?>