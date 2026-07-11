<?php $__env->startSection('title', $product->name . ' — Ramo Store'); ?>

<?php $__env->startSection('content'); ?>
<div class="page">

  <div class="breadcrumb">
    <a href="<?php echo e(route('home')); ?>">Home</a><span>/</span>
    <a href="<?php echo e(route('shop')); ?>">Shop</a><span>/</span>
    <strong><?php echo e(Str::limit($product->name, 40)); ?></strong>
  </div>

  <div class="product-layout">

    
    <div>
      <div class="gallery-main" id="gallery-main-wrap">
        <?php if($product->thumbnail_url): ?>
          <img src="<?php echo e($product->thumbnail_url); ?>" alt="<?php echo e($product->name); ?>" id="main-img"
               onerror="handleImgError(this)">
        <?php else: ?>
          <img src="" alt="<?php echo e($product->name); ?>" id="main-img" style="display:none"
               onerror="handleImgError(this)">
          <div id="main-img-placeholder" class="img-placeholder-box" style="width:100%;height:100%">
            <span class="img-placeholder-icon">🖼️</span>
            <span class="img-placeholder-text">No image</span>
          </div>
        <?php endif; ?>
      </div>
      <?php
        $allImages = array_values(array_filter(array_merge(
          $product->thumbnail_url ? [$product->thumbnail_url] : [],
          $product->gallery_urls ?? []
        )));
      ?>
      <div class="gallery-thumbs" id="gallery-thumbs">
        <?php $__currentLoopData = $allImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="gallery-thumb <?php echo e($i === 0 ? 'active' : ''); ?>" onclick="switchImg(this,'<?php echo e($url); ?>')">
          <img src="<?php echo e($url); ?>" alt="Image <?php echo e($i+1); ?>" loading="lazy"
               onerror="handleThumbError(this)">
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      
      <?php if(!empty($product->other_images_urls) || !empty($product->natural_images_urls)): ?>
      <div class="product-image-sections">

        <?php if(!empty($product->other_images_urls)): ?>
        <div class="img-section">
          <div class="img-section-header">
            <span class="img-section-label">📷 Product Images</span>
            <span class="img-section-count"><?php echo e(count($product->other_images_urls)); ?> photo<?php echo e(count($product->other_images_urls) != 1 ? 's' : ''); ?></span>
          </div>
          <div class="img-section-grid">
            <?php $__currentLoopData = $product->other_images_urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="img-section-item" onclick="switchImgFromSection('<?php echo e($url); ?>')">
              <img src="<?php echo e($url); ?>" alt="Product image" loading="lazy"
                   onerror="handleSectionImgError(this)">
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($product->natural_images_urls)): ?>
        <div class="img-section">
          <div class="img-section-header">
            <span class="img-section-label">🌿 Natural / Lifestyle Images</span>
            <span class="img-section-count"><?php echo e(count($product->natural_images_urls)); ?> photo<?php echo e(count($product->natural_images_urls) != 1 ? 's' : ''); ?></span>
          </div>
          <div class="img-section-grid">
            <?php $__currentLoopData = $product->natural_images_urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="img-section-item" onclick="switchImgFromSection('<?php echo e($url); ?>')">
              <img src="<?php echo e($url); ?>" alt="Natural image" loading="lazy"
                   onerror="handleSectionImgError(this)">
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <?php endif; ?>
    </div>

    
    <div class="product-info">

      
      <div class="pi-title-row">
        <h1 class="pi-title"><?php echo e($product->name); ?></h1>
        <button class="pi-wish-btn <?php echo e($inWishlist ? 'wished' : ''); ?>" id="wish-btn"
                onclick="toggleWishlist(this, <?php echo e($product->id); ?>)"
                title="<?php echo e($inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'); ?>">
          <?php echo e($inWishlist ? '♥' : '♡'); ?>

        </button>
      </div>

      
      <?php
        $totalRev = $reviews->count();
        $avgRating = $totalRev ? round($reviews->avg('rating'), 1) : 0;
      ?>
      <div class="pi-rating-row">
        <div class="pi-stars">
          <?php for($s=1;$s<=5;$s++): ?>
            <span class="<?php echo e($s <= round($avgRating) ? 'pi-star-filled' : 'pi-star-empty'); ?>">★</span>
          <?php endfor; ?>
        </div>
        <?php if($totalRev): ?>
          <span class="pi-rating-val"><?php echo e($avgRating); ?></span>
          <a href="#reviews" class="pi-rating-count">(<?php echo e($totalRev); ?> review<?php echo e($totalRev!=1?'s':''); ?>)</a>
        <?php else: ?>
          <span class="pi-rating-none">No reviews yet</span>
        <?php endif; ?>
      </div>

      
      <div id="stock-display" class="pi-stock">
        <?php if($product->stock_quantity > 0): ?>
          <span class="badge-stock-ok">✓ In Stock (<?php echo e(number_format($product->stock_quantity)); ?> available)</span>
        <?php else: ?>
          <span class="badge-stock-no">Out of Stock</span>
        <?php endif; ?>
      </div>

      
      <?php
        $discPct  = (float)($product->discount_percentage ?? 0);
        $hasDisc  = $discPct > 0;
        $varEffPrices = $variations->map(function ($v) use ($discPct) {
          $reg = (float)$v->regular_price;
          $eff = (float)$v->price;
          if ($discPct > 0 && $reg > 0 && $eff >= $reg) {
            return round($reg * (1 - $discPct / 100), 2);
          }
          return $eff;
        })->sort()->values();
        $varRegPrices = $variations->pluck('regular_price')->map(fn($p) => (float)$p)->sort()->values();
        $minEff = $varEffPrices->first() ?? $product->display_price;
        $maxEff = $varEffPrices->last()  ?? $product->display_price;
        $minReg = $varRegPrices->first() ?? $minEff;
        $isRange = $variations->count() > 0 && round($minEff, 2) !== round($maxEff, 2);
      ?>

      <div class="pi-price-block" id="price-block">
        <div class="pi-price-row">
          <?php if($isRange): ?>
            <span class="pi-price-main on-sale" id="price-display"><?php echo e(number_format($minEff,2)); ?> – <?php echo e(number_format($maxEff,2)); ?> EGP</span>
            <span class="pi-price-orig" id="orig-display" style="display:none"></span>
          <?php elseif($hasDisc): ?>
            <span class="pi-price-main on-sale" id="price-display"><?php echo e(number_format($minEff,2)); ?> EGP</span>
            <span class="pi-price-orig" id="orig-display"><?php echo e(number_format($minReg,2)); ?> EGP</span>
          <?php else: ?>
            <span class="pi-price-main" id="price-display"><?php echo e(number_format($minEff,2)); ?> EGP</span>
            <span class="pi-price-orig" id="orig-display" style="display:none"></span>
          <?php endif; ?>
          <?php if($hasDisc): ?>
            <span class="pi-disc-badge" id="disc-badge"><?php echo e(round($discPct)); ?>% OFF</span>
          <?php else: ?>
            <span class="pi-disc-badge" id="disc-badge" style="display:none"></span>
          <?php endif; ?>
        </div>
        <?php if($hasDisc): ?>
        <div class="pi-sale-note">🏷️ Sale price — you save <?php echo e(round($discPct)); ?>% off the original price</div>
        <?php endif; ?>
      </div>

      <div class="var-selected-label" id="product-sel-summary" aria-live="polite" style="margin:4px 0 12px;font-size:13px;color:var(--c-mid)"></div>

      
      <?php
        $varData = $variations->map(fn($v) => [
          'id'     => $v->id,
          'reg'    => (float)$v->regular_price,
          'price'  => (float)$v->price,
          'sale'   => (float)$v->sale_price,
          'stock'  => (int)$v->stock_quantity,
          'attrs'  => is_array($v->attributes) ? $v->attributes : [],
          'main'   => (bool)$v->main_variation,
          'images' => array_values(array_map(
              fn($p) => \App\Constants\AppConstants::imageUrl($p),
              array_filter($v->images ?? [], fn($p) => \Illuminate\Support\Facades\Storage::disk('public')->exists($p))
          )),
        ])->values();

        $attrMap = [];
        foreach ($varData as $v) {
          foreach (($v['attrs'] ?? []) as $k => $val) {
            if (!isset($attrMap[$k])) $attrMap[$k] = [];
            if (!in_array($val, $attrMap[$k])) $attrMap[$k][] = $val;
          }
        }
      ?>

      <?php if(!empty($attrMap)): ?>
      <div class="pi-variations-wrap">
        <?php $__currentLoopData = $attrMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrKey => $attrValues): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $isColor = strtolower($attrKey) === 'color'; ?>
          <div class="pi-var-group">
            <div class="var-label">
              <?php echo e($attrKey); ?>

              <?php if($isColor): ?> <span class="var-selected-label" id="sel-<?php echo e(Str::slug($attrKey)); ?>"></span><?php endif; ?>
            </div>
            <div class="var-options" id="opts-<?php echo e(Str::slug($attrKey)); ?>">
              <?php $__currentLoopData = $attrValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($isColor): ?>
                  <button class="var-swatch"
                          data-attr-key="<?php echo e($attrKey); ?>"
                          data-attr-val="<?php echo e($val); ?>"
                          onclick="selectAttr('<?php echo e($attrKey); ?>','<?php echo e($val); ?>',this)"
                          onmouseenter="previewColorImage('<?php echo e($attrKey); ?>','<?php echo e($val); ?>')"
                          onmouseleave="restoreImage()"
                          title="<?php echo e($val); ?>"
                          style="background-color: var(--swatch-<?php echo e(Str::slug($val)); ?>, #999)">
                  </button>
                <?php else: ?>
                  <button class="var-btn"
                          data-attr-key="<?php echo e($attrKey); ?>"
                          data-attr-val="<?php echo e($val); ?>"
                          onclick="selectAttr('<?php echo e($attrKey); ?>','<?php echo e($val); ?>',this)"><?php echo e($val); ?></button>
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="var-hint" id="hint-<?php echo e(Str::slug($attrKey)); ?>"></div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

      
      <div class="pi-cart-row">
        <div class="qty-input">
          <button type="button" onclick="changeQty(-1)">−</button>
          <input type="number" id="qty" value="1" min="1" max="<?php echo e($product->stock_quantity ?: 99); ?>">
          <button type="button" onclick="changeQty(1)">+</button>
        </div>
        <button class="add-to-cart-btn pi-atc-btn" id="add-to-cart-btn"
                onclick="handleAddToCart(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', <?php echo e($product->display_price); ?>, '<?php echo e($product->thumbnail_url); ?>')">
          🛒 Add to Cart
        </button>
      </div>

      
      <div class="pi-coupon-wrap">
        <div class="pi-coupon-label">🏷️ Have a coupon?</div>
        <div class="pi-coupon-row">
          <input type="text" id="pi-coupon-input" class="pi-coupon-input" placeholder="Enter promo code" maxlength="50">
          <button class="pi-coupon-btn" onclick="applyProductCoupon()">Apply</button>
        </div>
        <div id="pi-coupon-msg" class="pi-coupon-msg"></div>
      </div>

      <?php if($product->description || $product->unit_label): ?>
      <div class="desc-block pi-desc">
        <?php if($product->description): ?><p><?php echo e($product->description); ?></p><?php endif; ?>
        <?php if($product->unit_label): ?><p style="margin-top:10px;font-size:13px"><strong>Unit:</strong> <?php echo e($product->unit_label); ?></p><?php endif; ?>
      </div>
      <?php endif; ?>

    </div>

  </div>

  
  <?php
    $totalReviews = $reviews->count();
    $avgRating    = $totalReviews ? round($reviews->avg('rating'), 1) : 0;
    $avatarColors = ['#e85d26','#3b82f6','#22c55e','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
  ?>
  <div class="reviews-section" id="reviews">

    
    <div class="rv-overview">
      <div class="rv-score-box">
        <div class="rv-big-num"><?php echo e($totalReviews ? $avgRating : '—'); ?></div>
        <div class="rv-big-stars">
          <?php for($s=1;$s<=5;$s++): ?>
            <span style="color:<?php echo e($s <= round($avgRating) ? '#f5a623' : '#e0e0e0'); ?>">★</span>
          <?php endfor; ?>
        </div>
        <div class="rv-total-label"><?php echo e($totalReviews); ?> review<?php echo e($totalReviews!=1?'s':''); ?></div>
      </div>

      <div class="rv-distribution">
        <?php $__currentLoopData = [5,4,3,2,1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $star): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $cnt = $distribution->get($star)?->cnt ?? 0; $pct = $totalReviews ? round($cnt / $totalReviews * 100) : 0; ?>
          <div class="dist-row">
            <span class="dist-label"><?php echo e($star); ?> ★</span>
            <div class="dist-bar-wrap"><div class="dist-bar-fill" style="width:<?php echo e($pct); ?>%"></div></div>
            <span class="dist-num"><?php echo e($cnt); ?></span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <?php if(auth()->guard()->check()): ?>
        <?php if(!$userReviewed): ?>
        <button class="rv-write-btn" onclick="document.getElementById('review-form-wrap').scrollIntoView({behavior:'smooth'})">
          Write a Review
        </button>
        <?php else: ?>
        <div class="rv-wrote-badge">✓ You reviewed this product</div>
        <?php endif; ?>
      <?php else: ?>
        <a href="<?php echo e(route('login')); ?>" class="rv-write-btn">Sign in to Review</a>
      <?php endif; ?>
    </div>

    
    <?php if(session('success')): ?><div class="rv-flash rv-flash-ok">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if(session('error')): ?><div class="rv-flash rv-flash-err">⚠ <?php echo e(session('error')); ?></div><?php endif; ?>

    
    <?php if($totalReviews > 0): ?>
    <div class="rv-toolbar">
      <span class="rv-toolbar-count"><?php echo e($totalReviews); ?> Review<?php echo e($totalReviews!=1?'s':''); ?></span>
      <select class="rv-sort-select" onchange="sortReviews(this.value)">
        <option value="newest">Newest First</option>
        <option value="highest">Highest Rated</option>
        <option value="lowest">Lowest Rated</option>
        <option value="helpful">Most Helpful</option>
      </select>
    </div>
    <?php endif; ?>

    
    <div id="review-list">
      <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $initial   = strtoupper(substr($review->reviewer_name ?? 'C', 0, 1));
        $avatarBg  = $avatarColors[ord(strtolower($initial)) % count($avatarColors)];
        $isOwn     = Auth::check() && Auth::id() === (int)$review->user_id;
        $alreadyHelpful = in_array($review->id, $helpfulVoted ?? []);
      ?>
      <div class="rv-card" data-rating="<?php echo e($review->rating); ?>" data-helpful="<?php echo e($review->helpful_count); ?>" data-ts="<?php echo e(strtotime($review->created_at)); ?>">
        <div class="rv-card-head">
          <div class="rv-avatar" style="background:<?php echo e($avatarBg); ?>"><?php echo e($initial); ?></div>
          <div class="rv-card-meta">
            <div class="rv-name-row">
              <span class="rv-name"><?php echo e($review->reviewer_name); ?></span>
              <?php if($review->is_verified_purchase): ?>
                <span class="rv-verified">✓ Verified Purchase</span>
              <?php endif; ?>
              <?php if($isOwn): ?>
                <span class="rv-own-badge">Your review</span>
              <?php endif; ?>
            </div>
            <div class="rv-date"><?php echo e(\Carbon\Carbon::parse($review->created_at)->format('M d, Y')); ?></div>
          </div>
          <div class="rv-card-stars">
            <?php for($s=1;$s<=5;$s++): ?><span style="color:<?php echo e($s<=$review->rating?'#f5a623':'#e0e0e0'); ?>">★</span><?php endfor; ?>
          </div>
        </div>

        <?php if($review->title): ?>
          <div class="rv-review-title"><?php echo e($review->title); ?></div>
        <?php endif; ?>
        <div class="rv-review-body"><?php echo e($review->body); ?></div>

        <div class="rv-card-foot">
          <button class="rv-helpful <?php echo e($alreadyHelpful ? 'voted' : ''); ?>"
                  onclick="markHelpful(this, <?php echo e($review->id); ?>)"
                  <?php echo e($alreadyHelpful ? 'disabled' : ''); ?>>
            👍 Helpful <span class="rv-helpful-cnt">(<?php echo e($review->helpful_count ?: 0); ?>)</span>
          </button>
          <?php if($isOwn): ?>
            <button class="rv-delete" onclick="deleteReview(this, <?php echo e($review->id); ?>, <?php echo e($product->id); ?>)">Delete</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="rv-empty">
          <div style="font-size:48px;margin-bottom:12px">✍️</div>
          <p>No reviews yet — be the first to share your thoughts!</p>
        </div>
      <?php endif; ?>
    </div>

    
    <div id="review-form-wrap" class="<?php echo e($userReviewed ? 'hidden' : ''); ?>">
      <?php if(auth()->guard()->check()): ?>
        <?php if(!$userReviewed): ?>
        <div class="rv-form-card">
          <h3 class="rv-form-title">Write a Review</h3>
          <p class="rv-form-sub">Share your honest experience with this product</p>
          <form method="POST" action="<?php echo e(route('review.store')); ?>" id="review-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">

            
            <div class="rv-form-row">
              <label class="rv-form-label">Your Rating *</label>
              <div class="rv-star-picker" id="star-picker">
                <?php for($s=1;$s<=5;$s++): ?>
                  <span class="rv-star" data-val="<?php echo e($s); ?>"
                        onmouseenter="hoverStar(<?php echo e($s); ?>)"
                        onmouseleave="resetStarHover()"
                        onclick="setRating(<?php echo e($s); ?>)">★</span>
                <?php endfor; ?>
              </div>
              <input type="hidden" name="rating" id="rating-input" value="">
              <span class="rv-star-label" id="star-label">Click to rate</span>
              <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="rv-field-err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="rv-form-row">
              <label class="rv-form-label">Review Title</label>
              <input type="text" name="title" class="rv-input" maxlength="150"
                     placeholder="Sum up your experience in one line"
                     value="<?php echo e(old('title')); ?>">
              <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="rv-field-err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="rv-form-row">
              <label class="rv-form-label">Your Review *</label>
              <textarea name="body" class="rv-textarea" rows="5" id="review-body"
                        maxlength="1000" required
                        placeholder="What did you think? Quality, fit, value for money…"><?php echo e(old('body')); ?></textarea>
              <div class="rv-char-counter"><span id="char-count">0</span> / 1000</div>
              <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="rv-field-err"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="rv-submit" id="rv-submit-btn" onclick="return validateReviewForm()">
              Publish Review
            </button>
          </form>
        </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="rv-signin-prompt">
          <div style="font-size:36px;margin-bottom:12px">⭐</div>
          <p>Have this product? Share your experience!</p>
          <a href="<?php echo e(route('login')); ?>" class="rv-write-btn" style="display:inline-flex;margin-top:16px">Sign in to Write a Review</a>
        </div>
      <?php endif; ?>
    </div>

  </div>

  
  <?php if($vendor): ?>
  <div class="vendor-section" style="margin-top:56px">
    <div class="vendor-banner-card">
      <div class="vendor-banner-left">
        <?php if($vendor->logo_url): ?>
          <img src="<?php echo e($vendor->logo_url); ?>" alt="<?php echo e($vendor->shop_name); ?>" class="vendor-banner-logo">
        <?php else: ?>
          <div class="vendor-banner-logo-ph">🏪</div>
        <?php endif; ?>
        <div class="vendor-banner-info">
          <div class="vendor-banner-name"><?php echo e($vendor->shop_name); ?></div>
          <div class="vendor-banner-meta">
            <?php if((float)$vendor->rating > 0): ?>
              <span style="color:#f5a623">★</span> <?php echo e(number_format((float)$vendor->rating,1)); ?> · 
            <?php endif; ?>
            <?php echo e($vendor->shop_address); ?>

          </div>
        </div>
      </div>
      <a href="<?php echo e(route('vendor.store', $vendor->id)); ?>" class="vendor-banner-btn">Visit Store →</a>
    </div>

    <?php if($vendorProducts->count()): ?>
    <div class="sec-head" style="margin-top:28px;margin-bottom:16px">
      <h2 class="sec-title">More from <?php echo e(Str::limit($vendor->shop_name, 24)); ?></h2>
      <a href="<?php echo e(route('vendor.store', $vendor->id)); ?>" class="sec-link">See all →</a>
    </div>
    <div class="tl-scroll-section" style="margin-bottom:8px">
      <div class="tl-scroll-track">
        <?php $__currentLoopData = $vendorProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="tl-scroll-card">
          <div class="product-card">
            <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img" style="height:180px">
              <?php if($p->thumbnail_url): ?>
                <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
              <?php else: ?>
                <div class="placeholder">🛍️</div>
              <?php endif; ?>
              <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
            </a>
            <div class="product-card-body" style="padding:8px">
              <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name" style="font-size:12px"><?php echo e(Str::limit($p->name, 28)); ?></a>
              <div class="product-card-price">
                <?php if($p->on_sale): ?>
                  <span class="price-main sale" style="font-size:12px"><?php echo e(number_format($p->sale_price, 0)); ?> EGP</span>
                <?php else: ?>
                  <span class="price-main" style="font-size:12px"><?php echo e(number_format($p->price, 0)); ?> EGP</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  
  <?php if($related->count()): ?>
  <div style="margin-top:64px">
    <div class="sec-head">
      <h2 class="sec-title">You may also like</h2>
      <a href="<?php echo e(route('shop')); ?>" class="sec-link">See all →</a>
    </div>
    <div class="product-grid cols-4">
      <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="product-card">
        <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img">
          <?php if($p->thumbnail_url): ?>
            <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
          <?php else: ?>
            <div class="placeholder">👕</div>
          <?php endif; ?>
          <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
          <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($p->id); ?>)" title="Wishlist">♡</button>
        </a>
        <div class="product-card-body">
          <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name"><?php echo e($p->name); ?></a>
          <div class="product-card-price">
            <?php if($p->on_sale): ?>
              <span class="price-main sale"><?php echo e(number_format($p->sale_price,2)); ?> EGP</span>
              <span class="price-old"><?php echo e(number_format($p->price,2)); ?></span>
            <?php else: ?>
              <span class="price-main"><?php echo e(number_format($p->price,2)); ?> EGP</span>
            <?php endif; ?>
          </div>
          <button class="card-add-btn" onclick="addToCart(<?php echo e($p->id); ?>,'<?php echo e(addslashes($p->name)); ?>',<?php echo e($p->display_price); ?>,'<?php echo e($p->thumbnail_url); ?>')">Add to Cart</button>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
/* Gallery image switching */
#main-img { transition: opacity 0.18s ease; }
#main-img.img-switching { opacity: 0; }
#gallery-thumbs:empty { display: none; }

/* ── Image placeholder ── */
.img-placeholder-box {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  background: #f5f5f3; border-radius: 12px; gap: 8px; min-height: 260px;
}
.img-placeholder-icon { font-size: 56px; opacity: .45; }
.img-placeholder-text { font-size: 13px; color: #aaa; font-weight: 500; }

/* ── Product image sections ── */
.product-image-sections { margin-top: 20px; display: flex; flex-direction: column; gap: 20px; }

.img-section { border: 1px solid #eee; border-radius: 14px; overflow: hidden; background: #fff; }

.img-section-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; background: #fafaf8; border-bottom: 1px solid #eee;
}
.img-section-label { font-size: 13px; font-weight: 700; color: #333; }
.img-section-count { font-size: 12px; color: #aaa; }

.img-section-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
  gap: 8px; padding: 12px;
}
.img-section-item {
  aspect-ratio: 1; border-radius: 10px; overflow: hidden;
  background: #f5f5f3; cursor: pointer; border: 2px solid transparent;
  transition: border-color .18s, transform .15s; position: relative;
}
.img-section-item:hover { border-color: var(--c-orange, #e85d26); transform: scale(1.04); }

.img-section-item img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.img-section-item .section-img-placeholder {
  width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
  font-size: 28px; color: #ccc;
}

/* ── Thumb error placeholder ── */
.gallery-thumb.img-error { background: #f5f5f3; }
.gallery-thumb.img-error img { display: none !important; }
.gallery-thumb.img-error::after {
  content: '🖼️'; font-size: 22px; display: flex;
  align-items: center; justify-content: center;
  width: 100%; height: 100%; opacity: .4;
}

/* Color swatch CSS variables */
:root {
  --swatch-white:#f5f5f5; --swatch-black:#1a1a1a; --swatch-green:#22a35c;
  --swatch-red:#e53e3e; --swatch-blue:#3182ce; --swatch-yellow:#f6e05e;
  --swatch-gray:#a0aec0; --swatch-grey:#a0aec0; --swatch-navy:#1a365d;
  --swatch-pink:#ed64a6; --swatch-orange:#ed8936; --swatch-purple:#805ad5;
  --swatch-brown:#c05621; --swatch-beige:#f5e6cc; --swatch-cream:#fffdd0;
  --swatch-maroon:#800000; --swatch-gold:#d4af37; --swatch-silver:#c0c0c0;
  --swatch-teal:#319795; --swatch-indigo:#5a67d8; --swatch-cyan:#00b5d8;
  --swatch-lime:#68d391; --swatch-rose:#fc8181;
}
</style>
<script>
// ── Variation Engine ──────────────────────────────────────────────────
const VAR_DATA  = <?php echo json_encode($varData, 15, 512) ?>;
const DISC_PCT  = <?php echo e((float)($product->discount_percentage ?? 0)); ?>;
const ATTR_KEYS = [...new Set(VAR_DATA.flatMap(v => Object.keys(v.attrs)))];
let selectedAttrs = {};
let currentVariation = null;
let _lockedImgUrl = null;

// Returns valid values for `key` considering only selections from keys that come
// before `key` in ATTR_KEYS order. This ensures e.g. Color is never hidden by a
// selected Size, but Size IS filtered by the selected Color.
function validValuesFor(key) {
  const keyIndex = ATTR_KEYS.indexOf(key);
  const precedingSelected = Object.fromEntries(
    Object.entries(selectedAttrs).filter(([k]) => ATTR_KEYS.indexOf(k) < keyIndex)
  );
  return new Set(
    VAR_DATA
      .filter(v => Object.entries(precedingSelected).every(([k, sv]) => v.attrs[k] === sv))
      .map(v => v.attrs[key])
      .filter(v => v !== undefined)
  );
}

// Show/hide buttons based on what's valid; auto-select sole remaining option
function updateAvailability() {
  ATTR_KEYS.forEach(key => {
    const valid = validValuesFor(key);
    document.querySelectorAll(`[data-attr-key="${key}"]`).forEach(btn => {
      btn.style.display = valid.has(btn.dataset.attrVal) ? '' : 'none';
    });

    // Auto-select if exactly one option is visible and this key has no selection yet
    if (selectedAttrs[key] === undefined && valid.size === 1) {
      const onlyVal = [...valid][0];
      const onlyBtn = document.querySelector(`[data-attr-key="${key}"][data-attr-val="${onlyVal}"]`);
      if (onlyBtn) {
        selectedAttrs[key] = onlyVal;
        onlyBtn.classList.add('selected');
      }
    }
  });
}

// Auto-select default (main) variation on load
(function () {
  const main = VAR_DATA.find(v => v.main) || VAR_DATA[0] || null;
  if (main) {
    Object.entries(main.attrs).forEach(([k, val]) => {
      const btn = document.querySelector(`[data-attr-key="${k}"][data-attr-val="${val}"]`);
      if (btn) { selectedAttrs[k] = val; btn.classList.add('selected'); }
    });
    // Lock the main variation's image on load
    if (main.images && main.images.length > 0) {
      _lockedImgUrl = main.images[0];
      const img = document.getElementById('main-img');
      if (img) img.dataset.originalSrc = img.src;
      setMainImg(main.images[0]);
      const colorKey = Object.keys(main.attrs).find(k => k.toLowerCase() === 'color');
      if (colorKey) updateVariationThumbs(colorKey, main.attrs[colorKey]);
    }
    currentVariation = main;
    renderPriceStock(main);
    updateSelectedLabels();
  }
  updateAvailability();
  tryFindVariation();
})();

function selectAttr(key, value, btn) {
  if (selectedAttrs[key] === value) {
    // Deselect this value
    delete selectedAttrs[key];
    btn.classList.remove('selected');
  } else {
    // Select new value, deselect others in same group
    document.querySelectorAll(`[data-attr-key="${key}"]`).forEach(b => b.classList.remove('selected'));
    selectedAttrs[key] = value;
    btn.classList.add('selected');

    // Lock the color image on click so mouseout doesn't revert it
    if (key.toLowerCase() === 'color') {
      const colorImg = getColorImage(key, value);
      if (colorImg) {
        _lockedImgUrl = colorImg;
        const img = document.getElementById('main-img');
        if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
        setMainImg(colorImg, true);
        updateVariationThumbs(key, value);
      }
    }

    // Clear selections in other groups that are now incompatible
    ATTR_KEYS.forEach(otherKey => {
      if (otherKey === key) return;
      const cur = selectedAttrs[otherKey];
      if (cur === undefined) return;
      const stillValid = VAR_DATA.some(v => v.attrs[otherKey] === cur && v.attrs[key] === value);
      if (!stillValid) {
        delete selectedAttrs[otherKey];
        document.querySelectorAll(`[data-attr-key="${otherKey}"]`).forEach(b => b.classList.remove('selected'));
      }
    });
  }

  updateAvailability();
  tryFindVariation();
  updateSelectedLabels();
  updateSelectedSummary();
}

function tryFindVariation() {
  const allSelected = ATTR_KEYS.every(k => selectedAttrs[k] !== undefined);
  if (allSelected) {
    currentVariation = VAR_DATA.find(v =>
      Object.entries(selectedAttrs).every(([k, sv]) => v.attrs[k] === sv)
    ) || null;
  } else {
    currentVariation = null;
  }
  renderPriceStock(currentVariation);
  updateHints();
}

function renderPriceStock(v) {
  const priceEl  = document.getElementById('price-display');
  const origEl   = document.getElementById('orig-display');
  const badgeEl  = document.getElementById('disc-badge');
  const stockEl  = document.getElementById('stock-display');
  const qtyInput = document.getElementById('qty');
  const addBtn   = document.getElementById('add-to-cart-btn');

  function showDiscount(effectivePrice, originalPrice) {
    if (priceEl) {
      priceEl.textContent = effectivePrice.toFixed(2) + ' EGP';
      priceEl.classList.toggle('sale-price', effectivePrice < originalPrice);
    }
    if (origEl) {
      if (originalPrice > 0 && effectivePrice < originalPrice) {
        origEl.textContent = originalPrice.toFixed(2) + ' EGP';
        origEl.style.display = '';
      } else {
        origEl.style.display = 'none';
      }
    }
    if (badgeEl) {
      if (DISC_PCT > 0 && effectivePrice < originalPrice) {
        badgeEl.textContent = Math.round(DISC_PCT) + '% OFF';
        badgeEl.style.display = '';
      } else {
        badgeEl.style.display = 'none';
      }
    }
  }

  if (v) {
    // Compute the true effective price — fall back to discount_percentage when
    // the variation's price column wasn't updated (price == regular_price).
    const reg = v.reg > 0 ? v.reg : v.price;
    let   eff = v.price;
    if (DISC_PCT > 0 && reg > 0 && eff >= reg) {
      eff = Math.round(reg * (1 - DISC_PCT / 100) * 100) / 100;
    }
    showDiscount(eff, reg);
    if (priceEl) priceEl.classList.toggle('on-sale', eff < reg);

    if (stockEl) {
      stockEl.innerHTML = v.stock > 0
        ? `<span class="badge-stock-ok">✓ In Stock (${v.stock.toLocaleString()} available)</span>`
        : `<span class="badge-stock-no">Out of Stock</span>`;
    }
    if (qtyInput) {
      qtyInput.max = v.stock || 99;
      if (parseInt(qtyInput.value) > v.stock) qtyInput.value = Math.max(1, v.stock);
    }
    if (addBtn) {
      addBtn.disabled    = v.stock === 0;
      addBtn.textContent = v.stock === 0 ? 'Out of Stock' : 'Add to Cart';
      // Update cart price to the effective price
      const productId    = addBtn.getAttribute('data-pid') || addBtn.closest('[data-pid]')?.dataset.pid;
      addBtn.onclick     = () => handleAddToCart(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', eff, '<?php echo e($product->thumbnail_url); ?>');
    }
  } else {
    // No variation fully selected — show effective price range
    if (priceEl && VAR_DATA.length > 0) {
      const effPrices = VAR_DATA.map(vd => {
        const reg = vd.reg > 0 ? vd.reg : vd.price;
        if (DISC_PCT > 0 && reg > 0 && vd.price >= reg) {
          return Math.round(reg * (1 - DISC_PCT / 100) * 100) / 100;
        }
        return vd.price;
      });
      const mn = Math.min(...effPrices), mx = Math.max(...effPrices);
      priceEl.textContent = mn === mx ? `${mn.toFixed(2)} EGP` : `${mn.toFixed(2)} – ${mx.toFixed(2)} EGP`;
      priceEl.classList.toggle('sale-price', DISC_PCT > 0);
    }
    if (origEl) origEl.style.display = 'none';
    if (badgeEl) {
      if (DISC_PCT > 0) {
        badgeEl.textContent = Math.round(DISC_PCT) + '% OFF';
        badgeEl.style.display = '';
      } else {
        badgeEl.style.display = 'none';
      }
    }
    if (addBtn) { addBtn.disabled = false; addBtn.textContent = 'Add to Cart'; }
  }
}

function updateSelectedSummary() {
  const el = document.getElementById('product-sel-summary');
  if (!el) return;
  const parts = Object.entries(selectedAttrs).map(([k, v]) => `${k}: ${v}`);
  el.textContent = parts.length ? parts.join(' • ') : '';
}

function updateHints() {
  ATTR_KEYS.forEach(key => {
    const el = document.getElementById('hint-' + slugify(key));
    if (!el) return;
    const missing = ATTR_KEYS.filter(k => !selectedAttrs[k]);
    if (missing.length === 0) { el.textContent = ''; return; }
    if (Object.keys(selectedAttrs).length > 0 && missing.includes(key)) {
      el.textContent = `Please select a ${key}`;
    } else {
      el.textContent = '';
    }
  });
}

function updateSelectedLabels() {
  ATTR_KEYS.forEach(key => {
    const el = document.getElementById('sel-' + slugify(key));
    if (el) el.textContent = selectedAttrs[key] ? ': ' + selectedAttrs[key] : '';
  });
}

function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

// ── Cart integration ──────────────────────────────────────────────────
function handleAddToCart(id, name, basePrice, image) {
  // Validate all attributes selected
  if (ATTR_KEYS.length > 0 && !currentVariation) {
    const missing = ATTR_KEYS.filter(k => !selectedAttrs[k]);
    missing.forEach(k => {
      const el = document.getElementById('hint-' + slugify(k));
      if (el) { el.textContent = `Please select a ${k}`; el.style.color = 'var(--c-orange)'; }
    });
    // Shake the first unselected group
    const firstGroup = document.getElementById('opts-' + slugify(missing[0]));
    if (firstGroup) {
      firstGroup.style.animation = 'shake .35s';
      setTimeout(() => firstGroup.style.animation = '', 400);
    }
    return;
  }

  const qty   = parseInt(document.getElementById('qty').value) || 1;
  const varId = currentVariation ? currentVariation.id : null;

  // Compute the true effective price — mirror the discount fallback from renderPriceStock.
  // currentVariation.price may equal regular_price when discount hasn't been written to DB.
  let price;
  let oldPrice = null;
  if (currentVariation) {
    const reg = currentVariation.reg > 0 ? currentVariation.reg : currentVariation.price;
    price = currentVariation.price;
    if (DISC_PCT > 0 && reg > 0 && price >= reg) {
      price = Math.round(reg * (1 - DISC_PCT / 100) * 100) / 100;
    }
    if (reg > price) oldPrice = reg;
  } else {
    price = basePrice;
  }

  // Build variation label for cart display
  const varLabel = currentVariation
    ? Object.entries(currentVariation.attrs).map(([k,v]) => `${k}: ${v}`).join(', ')
    : null;

  addToCart(id, name, price, image, varId, qty, varLabel, oldPrice);
}

// ── Gallery ───────────────────────────────────────────────────────────

function setMainImg(url, fade) {
  const img = document.getElementById('main-img');
  const placeholder = document.getElementById('main-img-placeholder');
  if (!img || !url) return;
  if (fade) {
    img.classList.add('img-switching');
    setTimeout(() => {
      img.src = url;
      img.style.display = '';
      if (placeholder) placeholder.style.display = 'none';
      img.classList.remove('img-switching');
    }, 160);
  } else {
    img.src = url;
    img.style.display = '';
    if (placeholder) placeholder.style.display = 'none';
  }
}

function switchImg(thumb, url) {
  setMainImg(url, true);
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

// ── Image error / placeholder handlers ───────────────────────────────

function handleImgError(img) {
  img.style.display = 'none';
  const placeholder = document.getElementById('main-img-placeholder');
  if (placeholder) {
    placeholder.style.display = 'flex';
  } else {
    const wrap = document.getElementById('gallery-main-wrap');
    if (wrap) {
      const ph = document.createElement('div');
      ph.id = 'main-img-placeholder';
      ph.className = 'img-placeholder-box';
      ph.style.width = '100%';
      ph.style.height = '100%';
      ph.innerHTML = '<span class="img-placeholder-icon">🖼️</span><span class="img-placeholder-text">Image unavailable</span>';
      wrap.appendChild(ph);
    }
  }
}

function handleThumbError(img) {
  const thumb = img.closest('.gallery-thumb');
  if (thumb) thumb.classList.add('img-error');
}

function handleSectionImgError(img) {
  img.style.display = 'none';
  const item = img.closest('.img-section-item');
  if (item) {
    const ph = document.createElement('div');
    ph.className = 'section-img-placeholder';
    ph.textContent = '🖼️';
    item.appendChild(ph);
    item.style.cursor = 'default';
    item.onclick = null;
  }
}

function switchImgFromSection(url) {
  setMainImg(url, true);
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  window.scrollTo({ top: document.getElementById('gallery-main-wrap')?.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
}

// ── Color swatch image preview ────────────────────────────────────────

function getColorImages(attrKey, attrVal) {
  const v = VAR_DATA.find(v => v.attrs[attrKey] === attrVal);
  return v ? (v.images || []) : [];
}

function getColorImage(attrKey, attrVal) {
  const imgs = getColorImages(attrKey, attrVal);
  return imgs.length > 0 ? imgs[0] : null;
}

function updateVariationThumbs(attrKey, attrVal) {
  const strip = document.getElementById('gallery-thumbs');
  if (!strip) return;
  const imgs = getColorImages(attrKey, attrVal);
  if (imgs.length === 0) return;
  strip.innerHTML = '';
  imgs.forEach((url, i) => {
    const div = document.createElement('div');
    div.className = 'gallery-thumb' + (i === 0 ? ' active' : '');
    div.addEventListener('click', () => {
      setMainImg(url, true);
      strip.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
      div.classList.add('active');
    });
    const im = document.createElement('img');
    im.src = url;
    im.alt = attrVal + ' ' + (i + 1);
    im.loading = 'lazy';
    div.appendChild(im);
    strip.appendChild(div);
  });
}

function previewColorImage(attrKey, attrVal) {
  const url = getColorImage(attrKey, attrVal);
  if (!url) return;
  const img = document.getElementById('main-img');
  if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
  setMainImg(url, false);
}

function restoreImage() {
  const target = _lockedImgUrl || document.getElementById('main-img')?.dataset.originalSrc;
  if (target) setMainImg(target, false);
}

// ── Quantity ──────────────────────────────────────────────────────────
function changeQty(delta) {
  const input = document.getElementById('qty');
  input.value = Math.max(1, Math.min(parseInt(input.max) || 99, (parseInt(input.value) || 1) + delta));
}

// ── Star rating picker ────────────────────────────────────────────────
let _selectedRating = 0;
const starLabels = ['','Terrible','Poor','OK','Good','Excellent'];

function hoverStar(val) {
  document.querySelectorAll('.rv-star').forEach((s,i) => s.classList.toggle('hover', i < val));
}
function resetStarHover() {
  document.querySelectorAll('.rv-star').forEach(s => s.classList.remove('hover'));
}
function setRating(val) {
  _selectedRating = val;
  document.getElementById('rating-input').value = val;
  document.querySelectorAll('.rv-star').forEach((s,i) => {
    s.classList.toggle('lit', i < val);
    s.classList.remove('hover');
  });
  const lbl = document.getElementById('star-label');
  if (lbl) lbl.textContent = starLabels[val] || '';
}

// ── Review form validation ─────────────────────────────────────────────
function validateReviewForm() {
  if (!_selectedRating) {
    const lbl = document.getElementById('star-label');
    if (lbl) { lbl.textContent = 'Please pick a rating!'; lbl.style.color = '#e85d26'; }
    document.getElementById('star-picker').scrollIntoView({behavior:'smooth', block:'center'});
    return false;
  }
  return true;
}

// ── Character counter ─────────────────────────────────────────────────
document.getElementById('review-body')?.addEventListener('input', function() {
  const el = document.getElementById('char-count');
  if (el) el.textContent = this.value.length;
});

// ── Sort reviews ──────────────────────────────────────────────────────
function sortReviews(by) {
  const list = document.getElementById('review-list');
  if (!list) return;
  const cards = Array.from(list.querySelectorAll('.rv-card'));
  cards.sort((a, b) => {
    if (by === 'newest')  return parseInt(b.dataset.ts) - parseInt(a.dataset.ts);
    if (by === 'highest') return parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
    if (by === 'lowest')  return parseInt(a.dataset.rating) - parseInt(b.dataset.rating);
    if (by === 'helpful') return parseInt(b.dataset.helpful) - parseInt(a.dataset.helpful);
    return 0;
  });
  cards.forEach(c => list.appendChild(c));
}

// ── Helpful vote ──────────────────────────────────────────────────────
function markHelpful(btn, id) {
  if (btn.disabled) return;
  btn.disabled = true;
  fetch(`/reviews/${id}/helpful`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json'},
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const cnt = btn.querySelector('.rv-helpful-cnt');
      if (cnt) cnt.textContent = `(${data.count})`;
      btn.classList.add('voted');
      btn.title = 'Thanks for your feedback!';
      const card = btn.closest('.rv-card');
      if (card) card.dataset.helpful = data.count;
    } else {
      btn.disabled = true;
      btn.classList.add('voted');
    }
  })
  .catch(() => { btn.disabled = false; });
}

// ── Delete review ─────────────────────────────────────────────────────
function deleteReview(btn, id, productId) {
  if (!confirm('Delete your review? This cannot be undone.')) return;
  fetch(`/reviews/${id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Accept': 'application/json',
    },
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.closest('.rv-card')?.remove();
      // Show write form again
      const fw = document.getElementById('review-form-wrap');
      if (fw) fw.classList.remove('hidden');
      // Optionally reload to update stats
      setTimeout(() => location.reload(), 600);
    } else {
      alert(data.message || 'Could not delete review.');
    }
  });
}

// ── Wishlist btn initial state ────────────────────────────────────────
// State already rendered server-side via $inWishlist — nothing to do here.

// ── Product page coupon ───────────────────────────────────────────────
function applyProductCoupon() {
  const code = document.getElementById('pi-coupon-input')?.value?.trim();
  const msg  = document.getElementById('pi-coupon-msg');
  if (!code) { if (msg) { msg.textContent = 'Please enter a coupon code.'; msg.className = 'pi-coupon-msg error'; } return; }

  fetch('/cart/coupon', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Accept': 'application/json',
    },
    body: 'code=' + encodeURIComponent(code),
  })
  .then(r => r.json())
  .then(data => {
    if (!msg) return;
    if (data.success) {
      msg.textContent = '✓ Coupon applied! Discount will be reflected at checkout.';
      msg.className = 'pi-coupon-msg success';
      document.getElementById('pi-coupon-input').value = '';
    } else {
      msg.textContent = data.message || 'Invalid coupon code.';
      msg.className = 'pi-coupon-msg error';
    }
  })
  .catch(() => { if (msg) { msg.textContent = 'Could not apply coupon. Try again.'; msg.className = 'pi-coupon-msg error'; } });
}
</script>
<style>
@keyframes shake {
  0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)}
}

/* ── Product Info Panel (pi-*) ─────────────────────────────────────── */

/* Title row with wishlist heart */
.pi-title-row {
  display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;
}
.pi-title {
  flex: 1; font-size: 22px; font-weight: 800; color: #1a1a1a;
  line-height: 1.3; margin: 0;
}
.pi-wish-btn {
  flex-shrink: 0; width: 42px; height: 42px; border-radius: 50%;
  border: 2px solid #e0e0e0; background: #fff; font-size: 20px;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: #ccc; transition: border-color .2s, color .2s, transform .15s;
  margin-top: 2px;
}
.pi-wish-btn:hover { border-color: #e85d26; color: #e85d26; transform: scale(1.1); }
.pi-wish-btn.wished { border-color: #e85d26; color: #e85d26; }

/* Rating row */
.pi-rating-row {
  display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
}
.pi-stars { display: flex; gap: 2px; }
.pi-star-filled { color: #f5a623; font-size: 15px; }
.pi-star-empty  { color: #ddd;    font-size: 15px; }
.pi-rating-val  { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.pi-rating-count { font-size: 13px; color: var(--c-mid, #888); text-decoration: underline; text-underline-offset: 2px; }
.pi-rating-count:hover { color: #e85d26; }
.pi-rating-none { font-size: 13px; color: #bbb; font-style: italic; }

/* Stock */
.pi-stock { margin-bottom: 14px; }

/* Price block */
.pi-price-block { margin-bottom: 16px; }
.pi-price-row {
  display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;
}
.pi-price-main {
  font-size: 28px; font-weight: 800; color: #1a1a1a; letter-spacing: -.5px;
}
.pi-price-main.on-sale { color: #e85d26; }
.pi-price-orig {
  font-size: 16px; color: #aaa; text-decoration: line-through; font-weight: 400;
}
.pi-disc-badge {
  background: #e85d26; color: #fff; font-size: 12px; font-weight: 700;
  padding: 3px 9px; border-radius: 20px; letter-spacing: .03em;
}
.pi-sale-note {
  font-size: 12px; color: #22a35c; font-weight: 600;
  background: #f0fdf4; border: 1px solid #bbf7d0;
  padding: 6px 12px; border-radius: 8px; margin-top: 6px; display: inline-block;
}

/* Variations wrapper */
.pi-variations-wrap { margin-bottom: 16px; }
.pi-var-group { margin-bottom: 10px; }

/* Cart row */
.pi-cart-row {
  display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;
}
.pi-atc-btn {
  flex: 1; min-width: 160px; font-size: 15px; font-weight: 700;
  padding: 14px 20px; border-radius: 12px;
}

/* Coupon block */
.pi-coupon-wrap {
  border: 1px dashed #ddd; border-radius: 12px;
  padding: 14px 16px; margin-bottom: 16px; background: #fafaf8;
}
.pi-coupon-label {
  font-size: 13px; font-weight: 700; color: #555; margin-bottom: 8px;
}
.pi-coupon-row { display: flex; gap: 8px; }
.pi-coupon-input {
  flex: 1; padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px;
  font-size: 13px; outline: none; background: #fff; text-transform: uppercase;
  letter-spacing: .05em; transition: border-color .2s;
}
.pi-coupon-input:focus { border-color: #e85d26; }
.pi-coupon-btn {
  padding: 9px 16px; background: #1a1a1a; color: #fff; border: none;
  border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer;
  transition: background .2s;
}
.pi-coupon-btn:hover { background: #e85d26; }
.pi-coupon-msg {
  font-size: 12px; margin-top: 6px; font-weight: 600; min-height: 16px;
}
.pi-coupon-msg.success { color: #22a35c; }
.pi-coupon-msg.error   { color: #e85d26; }

/* Description */
.pi-desc { margin-top: 4px; }

/* ═══ Reviews Section ═══════════════════════════════════════════════ */
.reviews-section { margin-top: 64px; }

/* Overview panel */
.rv-overview {
  display: flex; align-items: flex-start; gap: 32px; flex-wrap: wrap;
  background: #fafaf8; border: 1px solid #eee; border-radius: 16px;
  padding: 28px 32px; margin-bottom: 28px;
}
.rv-score-box { text-align: center; min-width: 90px; }
.rv-big-num { font-size: 52px; font-weight: 800; line-height: 1; color: #1a1a1a; }
.rv-big-stars { font-size: 20px; margin: 6px 0 4px; letter-spacing: 2px; }
.rv-total-label { font-size: 12px; color: var(--c-mid); white-space: nowrap; }

.rv-distribution { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 6px; justify-content: center; }
.dist-row { display: flex; align-items: center; gap: 8px; }
.dist-label { font-size: 12px; color: #666; width: 30px; text-align: right; white-space: nowrap; }
.dist-bar-wrap { flex: 1; height: 8px; background: #e8e8e8; border-radius: 99px; overflow: hidden; }
.dist-bar-fill { height: 100%; background: linear-gradient(90deg, #f5a623, #e85d26); border-radius: 99px; transition: width .4s; }
.dist-num { font-size: 12px; color: #888; width: 24px; }

.rv-write-btn {
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--c-orange); color: #fff; font-size: 14px; font-weight: 700;
  padding: 12px 22px; border-radius: 10px; border: none; cursor: pointer;
  text-decoration: none; white-space: nowrap; align-self: center;
  transition: background .2s, transform .1s;
}
.rv-write-btn:hover { background: #d44f1a; transform: translateY(-1px); }
.rv-wrote-badge {
  font-size: 13px; font-weight: 600; color: #22c55e;
  background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 16px;
  border-radius: 10px; align-self: center;
}

/* Flash */
.rv-flash { padding: 12px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-bottom: 16px; }
.rv-flash-ok { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.rv-flash-err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

/* Sort bar */
.rv-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  padding-bottom: 16px; border-bottom: 1px solid #eee; margin-bottom: 20px;
}
.rv-toolbar-count { font-size: 15px; font-weight: 700; color: #1a1a1a; }
.rv-sort-select {
  font-size: 13px; padding: 7px 12px; border: 1px solid #e0e0e0; border-radius: 8px;
  background: #fff; color: #333; cursor: pointer; outline: none;
}
.rv-sort-select:focus { border-color: var(--c-orange); }

/* Review card */
.rv-card {
  border: 1px solid #eee; border-radius: 14px; padding: 20px 22px;
  margin-bottom: 14px; background: #fff;
  transition: box-shadow .2s;
}
.rv-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.rv-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.rv-avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 16px; font-weight: 800; letter-spacing: -.5px;
}
.rv-card-meta { flex: 1; min-width: 0; }
.rv-name-row { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.rv-name { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.rv-verified { font-size: 11px; font-weight: 600; color: #22c55e; background: #f0fdf4; padding: 2px 8px; border-radius: 99px; border: 1px solid #bbf7d0; }
.rv-own-badge { font-size: 11px; font-weight: 600; color: #3b82f6; background: #eff6ff; padding: 2px 8px; border-radius: 99px; border: 1px solid #bfdbfe; }
.rv-date { font-size: 12px; color: #aaa; margin-top: 2px; }
.rv-card-stars { font-size: 16px; letter-spacing: 1px; flex-shrink: 0; }
.rv-review-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
.rv-review-body { font-size: 14px; color: #444; line-height: 1.65; }
.rv-card-foot { display: flex; align-items: center; gap: 12px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
.rv-helpful {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 13px; color: #666; background: #f5f5f5; border: 1px solid #e0e0e0;
  padding: 5px 12px; border-radius: 8px; cursor: pointer; transition: all .2s;
}
.rv-helpful:hover:not(:disabled) { background: #fff0e8; border-color: var(--c-orange); color: var(--c-orange); }
.rv-helpful.voted { background: #fff0e8; border-color: var(--c-orange); color: var(--c-orange); cursor: default; }
.rv-delete {
  font-size: 13px; color: #ef4444; background: none; border: 1px solid #fecaca;
  padding: 5px 12px; border-radius: 8px; cursor: pointer; transition: all .2s; margin-left: auto;
}
.rv-delete:hover { background: #fef2f2; }

.rv-empty { text-align: center; padding: 48px 24px; color: var(--c-mid); }

/* Write review form */
.rv-form-card {
  background: #fff; border: 1px solid #eee; border-radius: 16px;
  padding: 32px; margin-top: 32px;
}
.rv-form-title { font-size: 20px; font-weight: 800; color: #1a1a1a; margin: 0 0 4px; }
.rv-form-sub { font-size: 13px; color: var(--c-mid); margin: 0 0 24px; }
.rv-form-row { margin-bottom: 18px; }
.rv-form-label { display: block; font-size: 13px; font-weight: 700; color: #333; margin-bottom: 8px; }

.rv-star-picker { display: flex; gap: 4px; }
.rv-star {
  font-size: 32px; cursor: pointer; color: #e0e0e0;
  transition: color .1s, transform .1s;
}
.rv-star.lit, .rv-star.hover { color: #f5a623; }
.rv-star:hover { transform: scale(1.15); }
.rv-star-label { font-size: 13px; color: #888; margin-left: 8px; line-height: 32px; vertical-align: top; }
.rv-field-err { display: block; font-size: 12px; color: #ef4444; margin-top: 4px; }

.rv-input {
  width: 100%; padding: 11px 14px; border: 1px solid #e0e0e0; border-radius: 10px;
  font-size: 14px; outline: none; background: #fafafa; box-sizing: border-box;
  transition: border-color .2s, background .2s;
}
.rv-input:focus { border-color: var(--c-orange); background: #fff; }
.rv-textarea {
  width: 100%; padding: 11px 14px; border: 1px solid #e0e0e0; border-radius: 10px;
  font-size: 14px; resize: vertical; outline: none; background: #fafafa;
  box-sizing: border-box; transition: border-color .2s, background .2s; font-family: inherit;
}
.rv-textarea:focus { border-color: var(--c-orange); background: #fff; }
.rv-char-counter { font-size: 12px; color: #aaa; text-align: right; margin-top: 4px; }

.rv-submit {
  background: var(--c-orange); color: #fff; font-size: 15px; font-weight: 700;
  padding: 14px 32px; border: none; border-radius: 12px; cursor: pointer;
  transition: background .2s, transform .1s; width: 100%;
}
.rv-submit:hover { background: #d44f1a; transform: translateY(-1px); }

.rv-signin-prompt {
  text-align: center; padding: 48px 24px; background: #fafaf8;
  border: 1px dashed #ddd; border-radius: 16px; margin-top: 32px;
  color: var(--c-mid); font-size: 15px;
}

.hidden { display: none !important; }

@media(max-width:640px) {
  .rv-overview { flex-direction: column; gap: 20px; padding: 20px; }
  .rv-score-box { display: flex; align-items: center; gap: 12px; }
  .rv-big-num { font-size: 40px; }
  .rv-form-card { padding: 20px; }
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/product.blade.php ENDPATH**/ ?>