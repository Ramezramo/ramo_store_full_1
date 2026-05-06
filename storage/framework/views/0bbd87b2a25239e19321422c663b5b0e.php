<?php $__env->startSection('title', ($q ? "\"$q\" — Search Results" : 'Search') . ' — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span>
    <strong><?php echo e($q ? "Search: \"$q\"" : 'Search'); ?></strong>
  </div>

  
  <div class="search-hero">
    <form method="GET" action="<?php echo e(route('search')); ?>" id="search-form">
      <div class="search-hero-bar">
        <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search products…" id="search-q" autocomplete="off">
        <button type="submit">Search</button>
      </div>
      
      <?php if($sort && $sort !== 'relevance'): ?> <input type="hidden" name="sort" value="<?php echo e($sort); ?>"> <?php endif; ?>
      <?php if($categoryId): ?> <input type="hidden" name="category" value="<?php echo e($categoryId); ?>"> <?php endif; ?>
    </form>
    <?php if($q): ?>
      <p class="search-result-meta"><?php echo e($products->total()); ?> result<?php echo e($products->total() != 1 ? 's' : ''); ?> for <strong>"<?php echo e($q); ?>"</strong></p>
    <?php endif; ?>
  </div>

  
  <?php if(count($activeFilters) > 0): ?>
  <div class="filter-chips">
    <?php $__currentLoopData = $activeFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $removeParams = array_merge(request()->except(is_array($f['remove']) ? $f['remove'] : [$f['remove']]));
        if($f['type'] === 'in_stock') unset($removeParams['in_stock']);
      ?>
      <a href="<?php echo e(route('search', $removeParams)); ?>" class="filter-chip">
        <?php echo e($f['label']); ?> <span>×</span>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('search', $q ? ['q' => $q] : [])); ?>" class="filter-chip-clear">Clear all</a>
  </div>
  <?php endif; ?>

  <div class="search-layout">

    
    <aside class="search-sidebar">
      <form method="GET" action="<?php echo e(route('search')); ?>" id="filter-form">
        <?php if($q): ?> <input type="hidden" name="q" value="<?php echo e($q); ?>"> <?php endif; ?>

        
        <div class="filter-section">
          <div class="filter-label">Sort By</div>
          <select name="sort" class="sort-select" onchange="document.getElementById('filter-form').submit()">
            <option value="relevance" <?php echo e($sort === 'relevance' ? 'selected' : ''); ?>>Relevance</option>
            <option value="newest"    <?php echo e($sort === 'newest'    ? 'selected' : ''); ?>>Newest First</option>
            <option value="price_asc" <?php echo e($sort === 'price_asc' ? 'selected' : ''); ?>>Price: Low to High</option>
            <option value="price_desc"<?php echo e($sort === 'price_desc'? 'selected' : ''); ?>>Price: High to Low</option>
            <option value="name_asc"  <?php echo e($sort === 'name_asc'  ? 'selected' : ''); ?>>Name: A–Z</option>
            <option value="name_desc" <?php echo e($sort === 'name_desc' ? 'selected' : ''); ?>>Name: Z–A</option>
          </select>
        </div>

        
        <div class="filter-section">
          <div class="filter-label">Price Range <span id="price-label" class="filter-val"></span></div>
          <div class="price-range-wrap">
            <div class="price-range-track" id="range-track">
              <div class="price-range-fill" id="range-fill"></div>
              <input type="range" id="range-min" class="range-input range-min"
                     min="<?php echo e(floor($priceRange->min_price)); ?>" max="<?php echo e(ceil($priceRange->max_price)); ?>"
                     value="<?php echo e($minPrice ?? floor($priceRange->min_price)); ?>" step="5">
              <input type="range" id="range-max" class="range-input range-max"
                     min="<?php echo e(floor($priceRange->min_price)); ?>" max="<?php echo e(ceil($priceRange->max_price)); ?>"
                     value="<?php echo e($maxPrice ?? ceil($priceRange->max_price)); ?>" step="5">
            </div>
            <div class="price-inputs-row">
              <div class="price-input-box">
                <span>Min</span>
                <input type="number" name="min_price" id="min-price-input"
                       value="<?php echo e($minPrice !== null ? (int)$minPrice : ''); ?>"
                       placeholder="<?php echo e(floor($priceRange->min_price)); ?>"
                       min="<?php echo e(floor($priceRange->min_price)); ?>" max="<?php echo e(ceil($priceRange->max_price)); ?>">
              </div>
              <div class="price-input-sep">–</div>
              <div class="price-input-box">
                <span>Max</span>
                <input type="number" name="max_price" id="max-price-input"
                       value="<?php echo e($maxPrice !== null ? (int)$maxPrice : ''); ?>"
                       placeholder="<?php echo e(ceil($priceRange->max_price)); ?>"
                       min="<?php echo e(floor($priceRange->min_price)); ?>" max="<?php echo e(ceil($priceRange->max_price)); ?>">
              </div>
            </div>
          </div>
        </div>

        
        <div class="filter-section">
          <label class="toggle-row">
            <span class="filter-label" style="margin:0">In Stock Only</span>
            <label class="toggle-switch">
              <input type="checkbox" name="in_stock" value="1" id="in-stock-toggle"
                     <?php echo e($inStock ? 'checked' : ''); ?> onchange="document.getElementById('filter-form').submit()">
              <span class="toggle-slider"></span>
            </label>
          </label>
        </div>

        
        <div class="filter-section">
          <div class="filter-label">Category</div>
          <div class="cat-filter-list">
            <a href="<?php echo e(route('search', array_merge(request()->except('category'), ['category' => '']))); ?>"
               class="cat-filter-item <?php echo e(!$categoryId ? 'active' : ''); ?>">All Categories</a>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <a href="<?php echo e(route('search', array_merge(request()->except('category'), ['category' => $cat->id]))); ?>"
                 class="cat-filter-item <?php echo e($categoryId == $cat->id ? 'active' : ''); ?>">
                <?php echo e($cat->name); ?>

              </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>

        
        <button type="submit" class="apply-filters-btn">Apply Filters</button>
      </form>
    </aside>

    
    <div class="search-results">

      <?php if($products->isEmpty()): ?>
        <div class="search-empty">
          <div style="font-size:64px;margin-bottom:20px">🔍</div>
          <h3>No products found</h3>
          <?php if($q): ?>
            <p>We couldn't find anything for <strong>"<?php echo e($q); ?>"</strong>. Try a different search or remove some filters.</p>
          <?php else: ?>
            <p>Try adjusting the filters or browsing all products.</p>
          <?php endif; ?>
          <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap">
            <a href="<?php echo e(route('search')); ?>" class="btn btn-outline" style="border-radius:10px;padding:11px 22px">Clear all filters</a>
            <a href="<?php echo e(route('shop')); ?>" class="btn btn-dark" style="border-radius:10px;padding:11px 22px">Browse shop</a>
          </div>
        </div>
      <?php else: ?>
        <div class="search-toolbar">
          <span class="result-count"><?php echo e($products->total()); ?> product<?php echo e($products->total()!=1?'s':''); ?></span>
          <span style="font-size:13px;color:var(--c-mid)">Page <?php echo e($products->currentPage()); ?> of <?php echo e($products->lastPage()); ?></span>
        </div>

        <div class="product-grid" style="margin-bottom:32px">
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="product-card">
            <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img">
              <?php if($p->thumbnail_url): ?>
                <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
              <?php else: ?>
                <div class="placeholder">👕</div>
              <?php endif; ?>
              <?php if($p->on_sale): ?><span class="badge-sale"><?php if($p->discount_percentage > 0): ?>-<?php echo e(round($p->discount_percentage)); ?>%<?php else: ?> SALE <?php endif; ?></span><?php endif; ?>
              <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($p->id); ?>)" title="Wishlist">♡</button>
            </a>
            <div class="product-card-body">
              <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name">
                <?php if($q): ?>
                  <?php echo preg_replace('/(' . preg_quote(e($q), '/') . ')/i', '<mark class="search-hl">$1</mark>', e($p->name)); ?>

                <?php else: ?>
                  <?php echo e($p->name); ?>

                <?php endif; ?>
              </a>
              <div class="product-card-price">
                <?php if($p->on_sale): ?>
                  <span class="price-main sale"><?php echo e(number_format($p->sale_price,2)); ?> EGP</span>
                  <span class="price-old"><?php echo e(number_format($p->price,2)); ?></span>
                <?php else: ?>
                  <span class="price-main"><?php echo e(number_format($p->price,2)); ?> EGP</span>
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

        
        <?php if($products->lastPage() > 1): ?>
        <div class="pagination-wrap">
          <?php if($products->onFirstPage()): ?>
            <span>←</span>
          <?php else: ?>
            <a href="<?php echo e($products->previousPageUrl()); ?>">←</a>
          <?php endif; ?>

          <?php for($pg = max(1, $products->currentPage()-2); $pg <= min($products->lastPage(), $products->currentPage()+2); $pg++): ?>
            <?php if($pg === $products->currentPage()): ?>
              <span class="active-page"><?php echo e($pg); ?></span>
            <?php else: ?>
              <a href="<?php echo e($products->url($pg)); ?>"><?php echo e($pg); ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if($products->hasMorePages()): ?>
            <a href="<?php echo e($products->nextPageUrl()); ?>">→</a>
          <?php else: ?>
            <span>→</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
/* ── Search Page Styles ── */
.search-hero{margin-bottom:28px}
.search-hero-bar{display:flex;background:var(--c-white);border:2px solid var(--c-dark);border-radius:50px;overflow:hidden;max-width:680px}
.search-hero-bar input{flex:1;padding:14px 20px;border:none;outline:none;font-size:15px;font-family:inherit;background:none}
.search-hero-bar button{padding:12px 24px;background:var(--c-dark);color:#fff;border:none;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s}
.search-hero-bar button:hover{background:var(--c-accent-h)}
.search-result-meta{margin-top:10px;font-size:14px;color:var(--c-mid)}
.search-result-meta strong{color:var(--c-dark)}

.filter-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
.filter-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--c-dark);color:#fff;border-radius:50px;font-size:12.5px;font-weight:600;transition:opacity .15s}
.filter-chip:hover{opacity:.8}
.filter-chip span{font-size:15px;line-height:1}
.filter-chip-clear{display:inline-flex;align-items:center;padding:5px 12px;background:var(--c-tag);color:var(--c-mid);border-radius:50px;font-size:12.5px;font-weight:600;transition:all .15s}
.filter-chip-clear:hover{background:var(--c-light);color:var(--c-dark)}

.search-layout{display:grid;grid-template-columns:240px 1fr;gap:28px;align-items:start}
.search-sidebar{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:20px;position:sticky;top:84px}
.filter-section{border-bottom:1px solid var(--c-light);padding-bottom:18px;margin-bottom:18px}
.filter-section:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
.filter-label{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
.filter-val{font-size:11px;color:var(--c-orange);font-weight:600;text-transform:none;letter-spacing:0}

/* Price range slider */
.price-range-wrap{user-select:none}
.price-range-track{position:relative;height:6px;background:var(--c-light);border-radius:3px;margin:14px 4px 18px}
.price-range-fill{position:absolute;height:100%;background:var(--c-dark);border-radius:3px;pointer-events:none}
.range-input{position:absolute;width:100%;height:6px;-webkit-appearance:none;appearance:none;background:none;border:none;pointer-events:none;top:0;left:0;margin:0;padding:0}
.range-input::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:50%;background:var(--c-dark);cursor:pointer;pointer-events:all;border:2.5px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.range-input::-moz-range-thumb{width:18px;height:18px;border-radius:50%;background:var(--c-dark);cursor:pointer;pointer-events:all;border:2.5px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.price-inputs-row{display:flex;align-items:center;gap:6px}
.price-input-box{flex:1;display:flex;flex-direction:column;gap:3px}
.price-input-box span{font-size:10px;color:var(--c-mid);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.price-input-box input{width:100%;padding:7px 10px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:var(--c-bg)}
.price-input-box input:focus{border-color:#999}
.price-input-sep{color:var(--c-mid);flex-shrink:0;padding-top:16px}

/* Toggle switch */
.toggle-row{display:flex;align-items:center;justify-content:space-between;cursor:pointer}
.toggle-switch{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#ddd;border-radius:24px;transition:.2s}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked + .toggle-slider{background:var(--c-dark)}
.toggle-switch input:checked + .toggle-slider::before{transform:translateX(18px)}

/* Category filter list */
.cat-filter-list{display:flex;flex-direction:column;gap:2px}
.cat-filter-item{display:block;padding:7px 10px;border-radius:8px;font-size:13.5px;color:var(--c-mid);transition:all .12s}
.cat-filter-item:hover,.cat-filter-item.active{background:var(--c-tag);color:var(--c-dark);font-weight:600}

.apply-filters-btn{width:100%;margin-top:18px;padding:11px;background:var(--c-dark);color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s}
.apply-filters-btn:hover{background:var(--c-accent-h)}

.search-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.search-empty{text-align:center;padding:64px 20px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg)}
.search-empty h3{font-size:20px;font-weight:800;margin-bottom:12px}
.search-empty p{color:var(--c-mid);font-size:14px}
mark.search-hl{background:#fff3cd;color:inherit;border-radius:2px;padding:0 1px}
</style>

<script>
const PRICE_MIN_ABS = <?php echo e(floor($priceRange->min_price)); ?>;
const PRICE_MAX_ABS = <?php echo e(ceil($priceRange->max_price)); ?>;

const rangeMin   = document.getElementById('range-min');
const rangeMax   = document.getElementById('range-max');
const fill       = document.getElementById('range-fill');
const priceLabel = document.getElementById('price-label');
const minInput   = document.getElementById('min-price-input');
const maxInput   = document.getElementById('max-price-input');

function updateRange() {
  const mn = parseInt(rangeMin.value);
  const mx = parseInt(rangeMax.value);
  const total = PRICE_MAX_ABS - PRICE_MIN_ABS;
  const leftPct  = ((mn - PRICE_MIN_ABS) / total) * 100;
  const rightPct = ((mx - PRICE_MIN_ABS) / total) * 100;

  fill.style.left  = leftPct  + '%';
  fill.style.width = (rightPct - leftPct) + '%';

  priceLabel.textContent = mn + ' – ' + mx + ' EGP';
  minInput.value = mn === PRICE_MIN_ABS ? '' : mn;
  maxInput.value = mx === PRICE_MAX_ABS ? '' : mx;
}

rangeMin.addEventListener('input', () => {
  if (parseInt(rangeMin.value) > parseInt(rangeMax.value) - 5) {
    rangeMin.value = parseInt(rangeMax.value) - 5;
  }
  updateRange();
});

rangeMax.addEventListener('input', () => {
  if (parseInt(rangeMax.value) < parseInt(rangeMin.value) + 5) {
    rangeMax.value = parseInt(rangeMin.value) + 5;
  }
  updateRange();
});

// Sync typed inputs back to sliders
minInput?.addEventListener('input', () => {
  const v = parseInt(minInput.value) || PRICE_MIN_ABS;
  rangeMin.value = Math.min(v, parseInt(rangeMax.value) - 5);
  updateRange();
});
maxInput?.addEventListener('input', () => {
  const v = parseInt(maxInput.value) || PRICE_MAX_ABS;
  rangeMax.value = Math.max(v, parseInt(rangeMin.value) + 5);
  updateRange();
});

// Auto-focus search
document.getElementById('search-q')?.focus();

// Init
updateRange();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/search.blade.php ENDPATH**/ ?>