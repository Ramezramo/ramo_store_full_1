<?php $__env->startSection('title', 'Shop — Ramo Store'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ══════════════════════════════════════════════
   FOLDABLE WIDGET SECTIONS
══════════════════════════════════════════════ */
.widget {
  margin-bottom: 4px;
}

/* Widget header — the clickable fold toggle */
.widget-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 4px; cursor: pointer; user-select: none;
  border-radius: 8px; transition: background .13s;
  gap: 8px;
}
.widget-header:hover { background: var(--c-tag); padding-left: 8px; padding-right: 8px; }

.widget-header-left {
  display: flex; align-items: center; gap: 7px;
  font-size: 10px; font-weight: 800; letter-spacing: .1em;
  text-transform: uppercase; color: var(--c-mid);
}
.widget-header-left svg { flex-shrink: 0; }

.widget-fold-icon {
  display: flex; align-items: center; justify-content: center;
  width: 18px; height: 18px; border-radius: 5px;
  background: var(--c-bg); border: 1px solid var(--c-light);
  color: var(--c-mid); flex-shrink: 0; transition: background .13s;
}
.widget-header:hover .widget-fold-icon { background: var(--c-light); }
.widget-fold-icon svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display:block; }
.widget.collapsed .widget-fold-icon svg { transform: rotate(-90deg); }

/* Widget body — the foldable content */
.widget-body {
  overflow: hidden;
  max-height: 1200px;
  transition: max-height .35s cubic-bezier(.4,0,.2,1), opacity .25s ease;
  opacity: 1;
}
.widget.collapsed .widget-body {
  max-height: 0;
  opacity: 0;
}

/* ── Category items ──────────────────────────────────────── */
.cat-all-pill {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 13px; border-radius: 9px; font-size: 13.5px;
  font-weight: 600; color: var(--c-dark); margin-bottom: 4px;
  transition: all .15s; text-decoration: none; background: transparent;
}
.cat-all-pill:hover { background: var(--c-tag); }
.cat-all-pill.active { background: var(--c-dark); color: #fff; }
.cat-all-pill.active .cat-count-badge { background: rgba(255,255,255,.18); color: #fff; }

.cat-count-badge {
  font-size: 10px; font-weight: 700;
  background: var(--c-bg); color: var(--c-mid);
  padding: 2px 7px; border-radius: 20px;
  border: 1px solid var(--c-light);
  min-width: 24px; text-align: center; flex-shrink: 0;
}

.cat-parent-item { margin-bottom: 2px; }

.cat-parent-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 9px 13px; border-radius: 9px;
  background: transparent; border: none; cursor: pointer;
  font-family: inherit; font-size: 13.5px; font-weight: 600;
  color: var(--c-dark); text-align: left; transition: all .15s; text-decoration: none;
}
.cat-parent-btn:hover { background: var(--c-tag); }
.cat-parent-btn.active { background: var(--c-orange); color: #fff; }
.cat-parent-btn.active .cat-count-badge { background: rgba(255,255,255,.25); color: #fff; border-color: transparent; }
.cat-parent-btn.parent-open:not(.active) { background: var(--c-tag); }

.cat-parent-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.cat-chevron {
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; transition: all .2s; color: var(--c-mid);
}
.cat-chevron svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display: block; }

.cat-children-wrap {
  overflow: hidden; max-height: 0;
  transition: max-height .3s cubic-bezier(.4,0,.2,1);
}
.cat-children-wrap.open { max-height: 600px; }

.cat-children-inner {
  padding: 3px 0 6px 12px; margin-left: 13px;
  border-left: 2px solid var(--c-light);
}

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
.cat-child-link.active .cat-count-badge { background: #fde0d5; color: var(--c-orange); border-color: transparent; }

/* ── Divider ──────────────────────────────────────────────── */
.widget-divider { border: none; border-top: 1.5px solid var(--c-light); margin: 8px 0; }

/* ── Toolbar active strip ─────────────────────────────────── */
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

/* ── Mobile toggle ────────────────────────────────────────── */
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
        $allCatsFlat  = $parentCats->merge($childCats->flatten());
        $activeCatObj = $allCatsFlat->firstWhere('id', $activeCategoryId);
        $activeCatName = $activeCatObj->name ?? '';
        $isChildActive = $activeCatObj && $activeCatObj->parent > 0;
        if ($isChildActive) { $parentCatObj = $parentCats->firstWhere('id', $activeCatObj->parent); }
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

      
      <div class="widget" id="widget-categories">

        <div class="widget-header" onclick="toggleWidget('widget-categories')" title="Click to collapse">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Categories
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          
          <a href="<?php echo e(route('shop', array_filter(request()->except('category','page')))); ?>"
             class="cat-all-pill <?php echo e(!$activeCategoryId ? 'active' : ''); ?>">
            <span>All Products</span>
            <span class="cat-count-badge"><?php echo e($products->total()); ?></span>
          </a>

          <hr class="widget-divider" style="margin:8px 0">

          
          <?php $__currentLoopData = $parentCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $hasChildren   = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
              $isActive      = $activeCategoryId == $parent->id;
              $isOpen        = $activeParentId == $parent->id;
              $parentUrl     = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));
              $ownCount      = $catCounts[$parent->id] ?? 0;
              $childrenCount = 0;
              if ($hasChildren) foreach ($childCats[$parent->id] as $ch) $childrenCount += ($catCounts[$ch->id] ?? 0);
              $totalCount    = $ownCount + $childrenCount;
            ?>

            <div class="cat-parent-item">
              <?php if($hasChildren): ?>
                <div style="display:flex;align-items:center;border-radius:9px;<?php echo e($isActive ? 'background:var(--c-orange);' : ($isOpen ? 'background:var(--c-tag);' : '')); ?>">
                  <a href="<?php echo e($parentUrl); ?>"
                     style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 0 9px 13px;color:<?php echo e($isActive ? '#fff' : 'var(--c-dark)'); ?>;text-decoration:none;min-width:0;font-size:13.5px;font-weight:600;">
                    <span class="cat-parent-name"><?php echo e($parent->name); ?></span>
                    <?php if($totalCount > 0): ?>
                      <span class="cat-count-badge" style="<?php echo e($isActive ? 'background:rgba(255,255,255,.25);color:#fff;border-color:transparent;' : ''); ?>"><?php echo e($totalCount); ?></span>
                    <?php endif; ?>
                  </a>
                  <button onclick="toggleCatChildren('cc-<?php echo e($parent->id); ?>', this)"
                          style="background:none;border:none;cursor:pointer;padding:9px 13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:<?php echo e($isActive ? 'rgba(255,255,255,.7)' : 'var(--c-mid)'); ?>;"
                          aria-label="Toggle sub-categories">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"
                         class="cat-chevron-svg"
                         style="transition:transform .25s;<?php echo e($isOpen ? 'transform:rotate(180deg)' : ''); ?>">
                      <polyline points="6 9 12 15 18 9"/>
                    </svg>
                  </button>
                </div>
              <?php else: ?>
                <a href="<?php echo e($parentUrl); ?>" class="cat-parent-btn <?php echo e($isActive ? 'active' : ''); ?>">
                  <span class="cat-parent-name"><?php echo e($parent->name); ?></span>
                  <?php if($totalCount > 0): ?><span class="cat-count-badge"><?php echo e($totalCount); ?></span><?php endif; ?>
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
                        <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($child->name); ?></span>
                        <?php if($childCount > 0): ?><span class="cat-count-badge"><?php echo e($childCount); ?></span><?php endif; ?>
                      </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <hr class="widget-divider">

      
      <div class="widget" id="widget-sort">

        <div class="widget-header" onclick="toggleWidget('widget-sort')" title="Click to collapse">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/></svg>
            Sort By
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          <form method="GET" action="<?php echo e(route('shop')); ?>" id="sort-form" style="padding-bottom:4px">
            <?php $__currentLoopData = request()->except('sort','page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
              <option value=""          <?php echo e(!request('sort') ? 'selected' : ''); ?>>Latest</option>
              <option value="price_asc" <?php echo e(request('sort')==='price_asc'  ? 'selected' : ''); ?>>Price: Low → High</option>
              <option value="price_desc"<?php echo e(request('sort')==='price_desc' ? 'selected' : ''); ?>>Price: High → Low</option>
            </select>
          </form>
        </div>

      </div>

    </aside>

    
    <div>
      <div class="shop-toolbar">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <span class="result-count"><?php echo e($products->total()); ?> product<?php echo e($products->total()!=1?'s':''); ?></span>
          <?php if($activeCategoryId && isset($activeCatName) && $activeCatName): ?>
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              <?php echo e($activeCatName); ?>

              <?php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]) && $childCats[$activeCategoryId]->count() > 0; ?>
              <?php if($isParentFilter): ?><span style="opacity:.6;font-weight:500">+ sub-categories</span><?php endif; ?>
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
        <div class="product-grid" id="infinite-product-grid">
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('web.partials.product-card', ['p' => $p, 'cardVariations' => []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div id="scroll-sentinel" style="height:1px;margin-top:8px"></div>
        <div id="scroll-loader" style="display:none;text-align:center;padding:24px 0">
          <span style="display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--c-mid)">
            <svg style="animation:spin .8s linear infinite" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
            Loading more products…
          </span>
        </div>
        <div id="scroll-end" style="display:none;text-align:center;padding:20px 0;font-size:13px;color:var(--c-mid)">
          You've seen all <?php echo e($products->total()); ?> products
        </div>

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
/* ── Widget fold/unfold ────────────────────────────────────── */
const STORAGE_KEY = 'ramo_shop_widgets';

function getWidgetState() {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch { return {}; }
}
function saveWidgetState(state) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch {}
}

function toggleWidget(id) {
  const widget = document.getElementById(id);
  widget.classList.toggle('collapsed');
  const state = getWidgetState();
  state[id] = widget.classList.contains('collapsed');
  saveWidgetState(state);
}

/* Restore collapsed state on page load */
document.addEventListener('DOMContentLoaded', function () {
  const state = getWidgetState();
  Object.keys(state).forEach(function (id) {
    if (state[id]) {
      const el = document.getElementById(id);
      if (el) el.classList.add('collapsed');
    }
  });
});

/* ── Category sub-list toggle ──────────────────────────────── */
function toggleCatChildren(id, btn) {
  const wrap = document.getElementById(id);
  const svg  = btn.querySelector('.cat-chevron-svg');
  const open = wrap.classList.toggle('open');
  if (svg) svg.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

/* ── Mobile sidebar toggle ─────────────────────────────────── */
function toggleShopFilter() {
  const sidebar = document.getElementById('shop-sidebar');
  const btn = document.getElementById('shop-filter-btn');
  const open = sidebar.classList.toggle('mobile-open');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Close Filters'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters & Categories';
}

/* ── Infinite scroll ────────────────────────────────────────── */
(function () {
  const grid      = document.getElementById('infinite-product-grid');
  const sentinel  = document.getElementById('scroll-sentinel');
  const loader    = document.getElementById('scroll-loader');
  const endMsg    = document.getElementById('scroll-end');

  if (!grid || !sentinel) return;

  let nextPage    = <?php echo e($products->hasMorePages() ? $products->currentPage() + 1 : 'null'); ?>;
  let loading     = false;

  /* Build base URL from current page URL (strip ?page=…) */
  function buildUrl(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    return url.toString();
  }

  function loadMore() {
    if (loading || nextPage === null) return;
    loading = true;
    loader.style.display = 'block';

    fetch(buildUrl(nextPage), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(data => {
        /* Append new cards */
        grid.insertAdjacentHTML('beforeend', data.html);

        if (data.hasMore) {
          nextPage = data.nextPage;
          loader.style.display = 'none';
        } else {
          nextPage = null;
          loader.style.display = 'none';
          endMsg.style.display = 'block';
          observer.disconnect();
        }
        loading = false;
      })
      .catch(() => {
        loader.style.display = 'none';
        loading = false;
      });
  }

  const observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' });

  if (nextPage !== null) {
    observer.observe(sentinel);
  } else {
    /* Already showing all products on first load */
    endMsg.style.display = 'block';
  }
})();
</script>

<?php $__env->startPush('styles'); ?>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/shop.blade.php ENDPATH**/ ?>