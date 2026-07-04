<?php
  $pid = $p->id;
  $vars = collect($cardVariations ?? []);
  $co = $cardOptions ?? [];
  $coShowBadge     = $co['showBadge']     ?? true;
  $coShowWishlist  = $co['showWishlist']  ?? true;
  $coShowSwatches  = $co['showSwatches']  ?? true;
  $coShowSizes     = $co['showSizes']     ?? true;
  $coShowOldPrice  = $co['showOldPrice']  ?? true;
  $coShowAddToCart = $co['showAddToCart'] ?? true;
  $coShowDetails   = $co['showDetails']   ?? true;
  $coShowCoupon    = $co['showCoupon']    ?? true;
  $coShowRating    = $co['showRating']    ?? false;

  /* ── Build color & size maps ────────────────── */
  $colorMap  = [];
  $sizeList  = [];
  $swatchHex = [
    'white'=>'#f5f5f5','black'=>'#222','green'=>'#22a35c','red'=>'#e53e3e',
    'blue'=>'#3182ce','yellow'=>'#f0c200','gray'=>'#a0aec0','grey'=>'#a0aec0',
    'navy'=>'#1a365d','pink'=>'#ed64a6','orange'=>'#ed8936','purple'=>'#805ad5',
    'brown'=>'#c05621','beige'=>'#f5e6cc','cream'=>'#fffdd0','maroon'=>'#800000',
    'gold'=>'#d4af37','silver'=>'#c0c0c0','teal'=>'#319795','indigo'=>'#5a67d8',
    'cyan'=>'#00b5d8','lime'=>'#68d391','rose'=>'#fc8181','mint'=>'#98d8c8',
    'khaki'=>'#c3b091','charcoal'=>'#36454f','coral'=>'#ff7f50','turquoise'=>'#40e0d0',
  ];
  foreach ($vars as $v) {
    $attrs = (array)($v->attributes ?? []);
    $imgs  = (array)($v->images ?? []);
    $imgUrl = $imgs ? \App\Constants\AppConstants::imageUrl($imgs[0]) : null;
    if (isset($attrs['Color'])) {
      $c = $attrs['Color'];
      if (!isset($colorMap[$c])) {
        $colorMap[$c] = ['img' => $imgUrl, 'hex' => ($swatchHex[strtolower($c)] ?? '#ccc')];
      }
    }
    if (isset($attrs['Size']) && !in_array($attrs['Size'], $sizeList)) {
      $sizeList[] = $attrs['Size'];
    }
  }

  /* ── Resolve display image upfront ─────────── */
  $displayImg = $p->thumbnail_url;
  if (!$displayImg && count($colorMap)) {
    $first = array_values($colorMap)[0];
    $displayImg = $first['img'] ?? null;
  }

  /* ── Slim variation payload for JS ─────────── */
  $jsVars = $vars->map(fn($v) => [
    'id'    => $v->id,
    'price' => (float)$v->price,
    'sale'  => (float)$v->sale_price,
    'stock' => (int)$v->stock_quantity,
    'attrs' => (array)($v->attributes ?? []),
    'img'   => (is_array($v->images) && count($v->images)) ? \App\Constants\AppConstants::imageUrl($v->images[0]) : null,
  ])->values()->toArray();

  $basePrice = $p->on_sale ? $p->sale_price : $p->price;
  $hasColors = count($colorMap) > 1;
  $hasSizes  = count($sizeList) > 0;
?>

<div class="product-card" id="pc-<?php echo e($pid); ?>"
     data-pid="<?php echo e($pid); ?>"
     data-base-img="<?php echo e($displayImg); ?>"
     data-base-price="<?php echo e($basePrice); ?>"
     data-vars='<?php echo json_encode($jsVars, 15, 512) ?>'>

  <a href="<?php echo e(route('product', $pid)); ?>" class="product-card-img">
    <?php if($displayImg): ?>
      <img src="<?php echo e($displayImg); ?>" alt="<?php echo e($p->name); ?>" loading="lazy" id="pc-img-<?php echo e($pid); ?>">
    <?php else: ?>
      <div class="placeholder" id="pc-img-<?php echo e($pid); ?>">🛍️</div>
    <?php endif; ?>
    <?php if($coShowBadge && $p->on_sale): ?>
      <?php if(!empty($p->flash_sale)): ?>
        <span class="badge-sale badge-flash">⚡ <?php echo e(round($p->flash_discount_pct)); ?>% OFF</span>
      <?php elseif($p->discount_percentage > 0): ?>
        <span class="badge-sale">-<?php echo e(round($p->discount_percentage)); ?>%</span>
      <?php else: ?>
        <span class="badge-sale">SALE</span>
      <?php endif; ?>
    <?php endif; ?>
    <?php if($coShowWishlist): ?>
    <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($pid); ?>)" title="Wishlist">♡</button>
    <?php endif; ?>
  </a>

  <div class="product-card-body">
    <a href="<?php echo e(route('product', $pid)); ?>" class="product-card-name"><?php echo e($p->name); ?></a>

    <?php if($coShowRating): ?>
    <?php
      $__avgRating = \Illuminate\Support\Facades\DB::table('product_reviews')
        ->where('product_id', $pid)->where('approved', true)->avg('rating') ?? 0;
      $__avgRating = round((float)$__avgRating, 1);
    ?>
    <?php if($__avgRating > 0): ?>
    <div class="pc-rating-row">
      <?php for($__s=1;$__s<=5;$__s++): ?><span style="color:<?php echo e($__s<=round($__avgRating)?'#f5a623':'#ddd'); ?>;font-size:11px">★</span><?php endfor; ?>
      <span style="font-size:11px;color:#666;margin-left:3px"><?php echo e($__avgRating); ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    
    <?php if($coShowSwatches && $hasColors): ?>
    <div class="pc-swatches" id="pc-swatches-<?php echo e($pid); ?>">
      <?php $__currentLoopData = $colorMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colorName => $cdata): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <button class="pc-swatch"
              title="<?php echo e($colorName); ?>"
              data-color="<?php echo e($colorName); ?>"
              data-img="<?php echo e($cdata['img'] ?? ''); ?>"
              style="background:<?php echo e($cdata['hex']); ?>;<?php echo e($cdata['hex'] === '#f5f5f5' ? 'border-color:#bbb' : ''); ?>"
              onclick="event.preventDefault();pcPickColor(<?php echo e($pid); ?>,'<?php echo e(addslashes($colorName)); ?>',this)">
      </button>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="pc-selected" id="pc-selected-<?php echo e($pid); ?>" aria-live="polite"></div>

    
    <?php if($coShowSizes && $hasSizes): ?>
    <div class="pc-sizes" id="pc-sizes-<?php echo e($pid); ?>">
      <?php $__currentLoopData = $sizeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <button class="pc-size"
              data-size="<?php echo e($sz); ?>"
              onclick="event.preventDefault();pcPickSize(<?php echo e($pid); ?>,'<?php echo e(addslashes($sz)); ?>',this)">
        <?php echo e($sz); ?>

      </button>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="product-card-price">
      <?php if($p->on_sale): ?>
        <span class="price-main sale" id="pc-price-<?php echo e($pid); ?>"><?php echo e(number_format($p->sale_price, 2)); ?> EGP</span>
        <?php if($coShowOldPrice): ?>
        <span class="price-old" id="pc-orig-<?php echo e($pid); ?>"><?php echo e(number_format($p->price, 2)); ?></span>
        <?php endif; ?>
      <?php else: ?>
        <span class="price-main" id="pc-price-<?php echo e($pid); ?>"><?php echo e(number_format($p->price, 2)); ?> EGP</span>
      <?php endif; ?>
    </div>

    <?php if($coShowAddToCart): ?>
    <button class="card-add-btn" id="pc-add-<?php echo e($pid); ?>"
            data-name="<?php echo e(addslashes($p->name)); ?>"
            data-img="<?php echo e($displayImg); ?>"
            onclick="pcAddToCart(<?php echo e($pid); ?>)">
      Add to Cart
    </button>
    <?php endif; ?>

    <?php if($coShowDetails): ?>
    <a href="<?php echo e(route('product', $pid)); ?>" class="card-details-btn">
      See details
    </a>
    <?php endif; ?>

    
    <?php if($coShowCoupon && !empty($p->coupon)): ?>
    <?php
      $__coupon = $p->coupon;
      $__base   = $p->on_sale ? $p->sale_price : $p->price;
      $__cprice = $__coupon->discount_type === 'percent'
          ? $__base * (1 - (float)$__coupon->amount / 100)
          : max(0, $__base - (float)$__coupon->amount);
    ?>
    <a href="<?php echo e(route('cart')); ?>" class="pc-coupon-bar" onclick="event.preventDefault();saveCouponAndGo('<?php echo e(strtoupper($__coupon->code)); ?>','<?php echo e(route('cart')); ?>')" title="Click to apply this coupon at checkout">
      <span class="pc-coupon-left">
        🏷️ WITH CODE <strong class="pc-coupon-code"><?php echo e(strtoupper($__coupon->code)); ?></strong>
      </span>
      <span class="pc-coupon-right">
        ↓ <?php echo e(number_format($__cprice, 0)); ?> EGP
      </span>
    </a>
    <?php endif; ?>

  </div>
</div>

<?php if (! $__env->hasRenderedOnce('a94687f0-f743-4017-9f40-dc2fdfa8f05b')): $__env->markAsRenderedOnce('a94687f0-f743-4017-9f40-dc2fdfa8f05b'); ?>
<style>
.pc-coupon-bar{display:flex;text-decoration:none;border-radius:0 0 10px 10px;overflow:hidden;margin:10px -14px -14px;font-size:11px;font-weight:700;line-height:1}
.pc-coupon-left{flex:1;background:#7c3aed;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-coupon-code{background:rgba(255,255,255,.2);border-radius:4px;padding:1px 5px;letter-spacing:.03em}
.pc-coupon-right{background:#5b21b6;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:4px;white-space:nowrap;flex-shrink:0}
</style>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/web/partials/product-card.blade.php ENDPATH**/ ?>