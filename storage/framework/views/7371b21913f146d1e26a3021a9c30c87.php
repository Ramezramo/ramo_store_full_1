<?php $__env->startSection('title', 'Shop — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ══════════════════════════════════════════════
   CATEGORY WIDGET
══════════════════════════════════════════════ */
.cat-widget { }

/* Section label */
.cat-widget-label {
  display: flex; align-items: center; gap: 7px;
  font-size: 10px; font-weight: 800; letter-spacing: .1em;
  text-transform: uppercase; color: var(--c-mid);
  margin-bottom: 10px;
}
.cat-widget-label svg { color: var(--c-mid); }

/* "All Products" pill */
.cat-all-pill {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 13px; border-radius: 9px; font-size: 13.5px;
  font-weight: 600; color: var(--c-dark); margin-bottom: 4px;
  transition: all .15s; cursor: pointer; text-decoration: none;
  background: transparent;
}
.cat-all-pill:hover { background: var(--c-tag); }
.cat-all-pill.active {
  background: var(--c-dark); color: #fff;
}
.cat-all-pill.active .cat-count-badge { background: rgba(255,255,255,.18); color: #fff; }

/* Count badge */
.cat-count-badge {
  font-size: 10px; font-weight: 700;
  background: var(--c-bg); color: var(--c-mid);
  padding: 2px 7px; border-radius: 20px;
  border: 1px solid var(--c-light);
  min-width: 24px; text-align: center;
  flex-shrink: 0;
}

/* Parent item */
.cat-parent-item { margin-bottom: 2px; }

.cat-parent-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 9px 13px; border-radius: 9px;
  background: transparent; border: none; cursor: pointer;
  font-family: inherit; font-size: 13.5px; font-weight: 600;
  color: var(--c-dark); text-align: left; transition: all .15s;
  text-decoration: none;
}
.cat-parent-btn:hover { background: var(--c-tag); }
.cat-parent-btn.active {
  background: var(--c-orange); color: #fff;
}
.cat-parent-btn.active .cat-count-badge {
  background: rgba(255,255,255,.25); color: #fff; border-color: transparent;
}
.cat-parent-btn.parent-open:not(.active) { background: var(--c-tag); }

.cat-parent-left { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
.cat-parent-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Chevron */
.cat-chevron {
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; transition: all .2s;
  color: var(--c-mid);
}
.cat-parent-btn.active .cat-chevron { color: rgba(255,255,255,.7); }
.cat-parent-btn.parent-open .cat-chevron svg { transform: rotate(180deg); }
.cat-chevron svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display: block; }

/* Children container */
.cat-children-wrap {
  overflow: hidden;
  max-height: 0;
  transition: max-height .3s cubic-bezier(.4,0,.2,1);
}
.cat-children-wrap.open { max-height: 600px; }

.cat-children-inner {
  padding: 3px 0 6px 12px;
  margin-left: 13px;
  border-left: 2px solid var(--c-light);
}

/* Child link */
.cat-child-link {
  display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 7px 11px; border-radius: 8px;
  font-size: 13px; font-weight: 500; color: var(--c-mid);
  text-decoration: none; transition: all .13s; margin-bottom: 1px;
}
.cat-child-link::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%;
  background: var(--c-light); flex-shrink: 0; transition: background .13s;
}
.cat-child-link:hover { background: var(--c-tag); color: var(--c-dark); }
.cat-child-link:hover::before { background: #ccc; }
.cat-child-link.active {
  background: #fff5f2; color: var(--c-orange); font-weight: 700;
  border: 1px solid #fde0d5;
}
.cat-child-link.active::before { background: var(--c-orange); }
.cat-child-link.active .cat-count-badge {
  background: #fde0d5; color: var(--c-orange); border-color: transparent;
}

/* Divider */
.cat-divider { border: none; border-top: 1.5px solid var(--c-light); margin: 16px 0; }

/* Active crumb strip in toolbar */
.active-cat-strip {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fff5f2; border: 1px solid #fde0d5;
  color: var(--c-orange); font-size: 12px; font-weight: 700;
  padding: 4px 10px 4px 8px; border-radius: 20px;
}
.active-cat-strip a {
  display: flex; align-items: center; color: var(--c-orange);
  opacity: .7; transition: opacity .13s; margin-left: 4px;
}
.active-cat-strip a:hover { opacity: 1; }

/* Mobile toggle */
.shop-filter-toggle {
  display: none; align-items: center; gap: 7px;
  padding: 10px 16px; border-radius: 50px;
  background: var(--c-white); border: 1.5px solid var(--c-light);
  font-size: 13.5px; font-weight: 600; color: var(--c-dark);
  margin-bottom: 16px; cursor: pointer; transition: all .15s;
}
.shop-filter-toggle:hover { background: var(--c-tag); }

@media (max-width: 860px) {
  .shop-layout { grid-template-columns: 1fr; }
  .shop-filter-toggle { display: inline-flex; }
  .sidebar { display: none; position: static; }
  .sidebar.mobile-open { display: block; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  
  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span>
    <a href="<?php echo e(route('shop')); ?>">Shop</a>
    <?php if($activeCategoryId): ?>
      <?php
        $allCatsFlat = $parentCats->merge($childCats->flatten());
        $activeCatObj = $allCatsFlat->firstWhere('id', $activeCategoryId);
        $activeCatName = $activeCatObj->name ?? '';
        $isChildActive = $activeCatObj && $activeCatObj->parent > 0;
        if ($isChildActive) {
          $parentCatObj = $parentCats->firstWhere('id', $activeCatObj->parent);
        }
      ?>
      <?php if($isChildActive && isset($parentCatObj)): ?>
        <span>/</span>
        <a href="<?php echo e(route('shop', ['category' => $parentCatObj->id])); ?>"><?php echo e($parentCatObj->name); ?></a>
      <?php endif; ?>
      <span>/</span><strong><?php echo e($activeCatName); ?></strong>
    <?php endif; ?>
    <?php if(request('search')): ?><span>/</span><span>"<?php echo e(request('search')); ?>"</span><?php endif; ?>
  </div>

  <button class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    Filters & Categories
  </button>

  <div class="shop-layout">

    
    <aside class="sidebar" id="shop-sidebar">

      
      <div class="cat-widget">
        <div class="cat-widget-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Categories
        </div>

        
        <?php $totalProducts = $products->total(); ?>
        <a href="<?php echo e(route('shop', array_filter(request()->except('category','page')))); ?>"
           class="cat-all-pill <?php echo e(!$activeCategoryId ? 'active' : ''); ?>">
          <span>All Products</span>
          <span class="cat-count-badge"><?php echo e($products->total()); ?></span>
        </a>

        <hr class="cat-divider" style="margin:10px 0">

        
        <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $hasChildren  = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
            $isActive     = $activeCategoryId == $parent->id;
            $isOpen       = $activeParentId == $parent->id;
            $parentUrl    = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));

            // Count: own products + children products
            $ownCount     = $catCounts[$parent->id] ?? 0;
            $childrenCount = 0;
            if ($hasChildren) {
              foreach ($childCats[$parent->id] as $ch) {
                $childrenCount += ($catCounts[$ch->id] ?? 0);
              }
            }
            $totalCount = $ownCount + $childrenCount;
          ?>

          <div class="cat-parent-item">

            <?php if($hasChildren): ?>
              
              <div class="cat-parent-btn <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($isOpen && !$isActive ? 'parent-open' : ''); ?>"
                   style="padding:0;background:transparent;">
                <a href="<?php echo e($parentUrl); ?>"
                   style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 0 9px 13px;color:inherit;text-decoration:none;min-width:0;font-size:13.5px;font-weight:600;">
                  <span class="cat-parent-name"><?php echo e($parent->name); ?></span>
                  <?php if($totalCount > 0): ?>
                    <span class="cat-count-badge"><?php echo e($totalCount); ?></span>
                  <?php endif; ?>
                </a>
                <button onclick="toggleCatChildren('cc-<?php echo e($parent->id); ?>', this)"
                        class="cat-chevron <?php echo e($isOpen ? 'open' : ''); ?>"
                        style="background:none;border:none;cursor:pointer;padding:9px 13px;border-radius:0 9px 9px 0;flex-shrink:0;"
                        aria-label="Toggle sub-categories">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"
                       style="transition:transform .25s;<?php echo e($isOpen ? 'transform:rotate(180deg)' : ''); ?>">
                    <polyline points="6 9 12 15 18 9"/>
                  </svg>
                </button>
              </div>

              
              <?php if($isActive || $isOpen): ?>
              <style>
                #parent-row-<?php echo e($parent->id); ?> { background: <?php echo e($isActive ? 'var(--c-orange)' : 'var(--c-tag)'); ?>; border-radius:9px; }
              </style>
              <?php endif; ?>

            <?php else: ?>
              <a href="<?php echo e($parentUrl); ?>"
                 class="cat-parent-btn <?php echo e($isActive ? 'active' : ''); ?>">
                <span class="cat-parent-left">
                  <span class="cat-parent-name"><?php echo e($parent->name); ?></span>
                </span>
                <?php if($totalCount > 0): ?>
                  <span class="cat-count-badge"><?php echo e($totalCount); ?></span>
                <?php endif; ?>
              </a>
            <?php endif; ?>

            
            <?php if($hasChildren): ?>
              <div class="cat-children-wrap <?php echo e($isOpen ? 'open' : ''); ?>" id="cc-<?php echo e($parent->id); ?>">
                <div class="cat-children-inner">
                  <?php $__currentLoopData = $childCats[$parent->id]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                      $childActive = $activeCategoryId == $child->id;
                      $childCount  = $catCounts[$child->id] ?? 0;
                      $childUrl    = route('shop', array_merge(request()->except('category','page'), ['category' => $child->id]));
                    ?>
                    <a href="<?php echo e($childUrl); ?>" class="cat-child-link <?php echo e($childActive ? 'active' : ''); ?>">
                      <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?php echo e($child->name); ?>

                      </span>
                      <?php if($childCount > 0): ?>
                        <span class="cat-count-badge"><?php echo e($childCount); ?></span>
                      <?php endif; ?>
                    </a>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      
      <hr class="cat-divider">
      <div class="cat-widget-label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/></svg>
        Sort By
      </div>
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
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <span class="result-count">
            <?php echo e($products->total()); ?> product<?php echo e($products->total()!=1?'s':''); ?>

          </span>
          <?php if($activeCategoryId && isset($activeCatName) && $activeCatName): ?>
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <?php echo e($activeCatName); ?>

              <?php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]) && $childCats[$activeCategoryId]->count() > 0; ?>
              <?php if($isParentFilter): ?>
                <span style="opacity:.6;font-weight:500">+ sub-categories</span>
              <?php endif; ?>
              <a href="<?php echo e(route('shop', array_filter(request()->except('category','page')))); ?>" title="Clear category">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          <?php endif; ?>
        </div>
        <div class="search-bar">
          <form method="GET" action="<?php echo e(route('shop')); ?>" style="display:contents">
            <?php if($activeCategoryId): ?><input type="hidden" name="category" value="<?php echo e($activeCategoryId); ?>"><?php endif; ?>
            <input type="text" name="search" placeholder="Search products…" value="<?php echo e(request('search')); ?>">
            <button type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
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
  const sidebar = document.getElementById('shop-sidebar');
  const btn = document.getElementById('shop-filter-btn');
  const open = sidebar.classList.toggle('mobile-open');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Close Filters'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters & Categories';
}

function toggleCatChildren(id, chevronBtn) {
  const wrap = document.getElementById(id);
  const svg  = chevronBtn.querySelector('svg');
  const open = wrap.classList.toggle('open');
  svg.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/shop.blade.php ENDPATH**/ ?>