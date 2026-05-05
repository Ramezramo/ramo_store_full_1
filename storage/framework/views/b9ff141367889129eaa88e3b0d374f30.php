<?php $__env->startSection('title', 'Ramo Store — Home'); ?>

<?php $__env->startSection('content'); ?>


<?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si => $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(($sec['layout'] ?? '') === 'announcement'): ?>
    <?php
      $aMsg    = $sec['message']    ?? 'Welcome to Ramo Store! Free shipping on orders over 500 EGP.';
      $aSpeed  = $sec['speed']      ?? 'normal';
      $aColor  = $sec['barColor']   ?? 'dark';
      $aDismiss = $sec['dismissableByUser'] ?? true;
      $aBg = match($aColor) {
        'orange' => '#e85d26', 'navy' => '#1a1a2e', 'white' => '#f8f8f8', default => '#111111'
      };
      $aFg = ($aColor === 'white') ? '#111' : '#fff';
      $aSpeed2 = match($aSpeed) { 'slow' => '40s', 'fast' => '15s', 'static' => 'none', default => '25s' };
    ?>
    <div class="tl-announcement" id="announce-<?php echo e($si); ?>" style="background:<?php echo e($aBg); ?>;color:<?php echo e($aFg); ?>">
      <?php if($aDismiss): ?>
      <button class="tl-announce-close" onclick="document.getElementById('announce-<?php echo e($si); ?>').style.display='none'" style="color:<?php echo e($aFg); ?>">×</button>
      <?php endif; ?>
      <?php if($aSpeed === 'static'): ?>
        <div class="tl-announce-static"><?php echo e($aMsg); ?></div>
      <?php else: ?>
        <div class="tl-announce-scroll-wrap"><div class="tl-announce-scroll" style="animation-duration:<?php echo e($aSpeed2); ?>">
          <?php echo e($aMsg); ?> &nbsp;·&nbsp; <?php echo e($aMsg); ?> &nbsp;·&nbsp; <?php echo e($aMsg); ?>

        </div></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="page">

  
  <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si => $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(($sec['layout'] ?? '') === 'flash'): ?>
      <?php
        $fTitle    = $sec['title']    ?? 'Flash Sale';
        $fDiscount = $sec['discount'] ?? 20;
        $fDuration = (int)($sec['duration'] ?? 4) * 3600;
        $fMinOrder = $sec['minOrder'] ?? 0;
        $fSeconds  = $sec['showCountdownSeconds'] ?? true;
      ?>
      <div class="tl-flash-bar" id="flash-<?php echo e($si); ?>">
        <div class="tl-flash-inner">
          <span class="tl-flash-icon">⚡</span>
          <div class="tl-flash-text">
            <span class="tl-flash-title"><?php echo e($fTitle); ?></span>
            <span class="tl-flash-disc"><?php echo e($fDiscount); ?>% OFF</span>
            <?php if($fMinOrder > 0): ?><span class="tl-flash-min">Min. order <?php echo e(number_format($fMinOrder, 0)); ?> EGP</span><?php endif; ?>
          </div>
          <div class="tl-flash-countdown" id="flash-cd-<?php echo e($si); ?>">
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fh-<?php echo e($si); ?>">00</span><span class="tl-cd-lbl">HRS</span></div>
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fm-<?php echo e($si); ?>">00</span><span class="tl-cd-lbl">MIN</span></div>
            <?php if($fSeconds): ?>
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fs-<?php echo e($si); ?>">00</span><span class="tl-cd-lbl">SEC</span></div>
            <?php endif; ?>
          </div>
          <a href="/shop" class="tl-flash-btn">Shop Now →</a>
        </div>
      </div>
      <script>
      (function(){
        var end = Date.now() + <?php echo e($fDuration); ?> * 1000;
        var showSec = <?php echo e($fSeconds ? 'true' : 'false'); ?>;
        function tick() {
          var rem = Math.max(0, Math.floor((end - Date.now()) / 1000));
          if (rem === 0) { var el = document.getElementById('flash-<?php echo e($si); ?>'); if (el && <?php echo e($sec['autoDismissWhenExpired'] ?? 'false'); ?>) el.style.display='none'; return; }
          document.getElementById('fh-<?php echo e($si); ?>').textContent = String(Math.floor(rem/3600)).padStart(2,'0');
          document.getElementById('fm-<?php echo e($si); ?>').textContent = String(Math.floor((rem%3600)/60)).padStart(2,'0');
          if (showSec) document.getElementById('fs-<?php echo e($si); ?>').textContent = String(rem%60).padStart(2,'0');
          setTimeout(tick, 1000);
        }
        tick();
      })();
      </script>
    <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  
  <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si => $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php $layout = $sec['layout'] ?? ''; ?>

    
    <?php if($layout === 'logo'): ?>
      

    
    <?php elseif($layout === 'spacer'): ?>
      <div class="tl-spacer" style="height:<?php echo e($sec['height'] ?? 24); ?>px"></div>

    
    <?php elseif($layout === 'divider'): ?>
      <hr class="tl-divider">

    
    <?php elseif($layout === 'bannerImage'): ?>
      <?php
        $items    = $sec['items'] ?? [];
        $isSlider = ($sec['design'] ?? 'default') !== 'static' && count($items) > 1;
        $radius   = $sec['radius'] ?? 2;
        $sliderId = 'slider-'.$si;
      ?>
      <?php if(count($items)): ?>
        <?php if($isSlider): ?>
        <div class="tl-banner-slider" id="<?php echo e($sliderId); ?>" style="border-radius:<?php echo e($radius); ?>px;margin-bottom:28px">
          <div class="tl-slides" id="<?php echo e($sliderId); ?>-track">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bi => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $url = $item['image'] ?? '';
                $catId = $item['category'] ?? null;
                $href  = $catId ? route('shop', ['category' => $catId]) : '#';
              ?>
              <div class="tl-slide">
                <a href="<?php echo e($href); ?>" class="tl-slide-link">
                  <img src="<?php echo e($url); ?>" alt="Banner <?php echo e($bi+1); ?>" loading="<?php echo e($bi===0?'eager':'lazy'); ?>">
                </a>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php if(count($items) > 1): ?>
          <button class="tl-arrow prev" onclick="slidePrev('<?php echo e($sliderId); ?>')">‹</button>
          <button class="tl-arrow next" onclick="slideNext('<?php echo e($sliderId); ?>')">›</button>
          <div class="tl-dots" id="<?php echo e($sliderId); ?>-dots">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bi => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="tl-dot <?php echo e($bi===0?'active':''); ?>" onclick="slideTo('<?php echo e($sliderId); ?>',<?php echo e($bi); ?>)"></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
          <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $url   = $item['image'] ?? '';
              $catId = $item['category'] ?? null;
              $href  = $catId ? route('shop', ['category' => $catId]) : '#';
              $ht    = isset($sec['height']) ? 'height:'.((float)$sec['height'] * 100).'vw;max-height:280px;' : '';
            ?>
            <a href="<?php echo e($href); ?>" class="tl-static-banner" style="border-radius:<?php echo e($radius); ?>px;overflow:hidden;display:block;margin-bottom:20px">
              <img src="<?php echo e($url); ?>" alt="Banner" style="width:100%;object-fit:cover;<?php echo e($ht); ?>display:block">
            </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
      <?php endif; ?>

    
    <?php elseif($layout === 'category'): ?>
      <?php $catItems = $sec['items'] ?? []; ?>
      <?php if(count($catItems)): ?>
      <div class="tl-cat-strip">
        <?php $__currentLoopData = $catItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $cid    = $ci['category'] ?? null;
            $label  = $ci['label'] ?? ($allCategories[$cid]->name ?? '');
            $img    = $ci['image'] ?? '';
            $color  = ($ci['colors'][0] ?? '#e85d26');
            $href   = $cid ? route('shop', ['category' => $cid]) : route('shop');
          ?>
          <a href="<?php echo e($href); ?>" class="tl-cat-item">
            <div class="tl-cat-img-wrap" style="border-color:<?php echo e($color); ?>22">
              <?php if($img): ?>
                <img src="<?php echo e($img); ?>" alt="<?php echo e($label); ?>" class="tl-cat-img" loading="lazy">
              <?php else: ?>
                <div class="tl-cat-chip" style="background:<?php echo e($color); ?>22">🛍️</div>
              <?php endif; ?>
            </div>
            <span class="tl-cat-label"><?php echo e($label); ?></span>
          </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'twoColumn'): ?>
      <?php
        $products  = $sectionProducts[$si] ?? collect();
        $title     = $sec['headerText'] ?? $sec['name'] ?? 'Products';
        $catId     = $sec['category'] ?? null;
      ?>
      <?php if($products->count()): ?>
      <div class="sec-head">
        <h2 class="sec-title"><?php echo e($title); ?></h2>
        <a href="<?php echo e(route('shop', array_filter(['category' => $catId]))); ?>" class="sec-link">View all →</a>
      </div>
      <div class="product-grid" style="margin-bottom:40px">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('web.partials.product-card', ['p' => $p, 'cardVariations' => $sectionVariations[$p->id] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'saleImages'): ?>
      <?php
        $products = $sectionProducts[$si] ?? collect();
        $title    = $sec['headerText'] ?? 'Products';
        $catId    = $sec['category'] ?? null;
      ?>
      <?php if($products->count()): ?>
      <div class="sec-head">
        <h2 class="sec-title"><?php echo e($title); ?></h2>
        <a href="<?php echo e(route('shop', array_filter(['category' => $catId]))); ?>" class="sec-link">See all →</a>
      </div>
      <div class="tl-scroll-section" style="margin-bottom:36px">
        <div class="tl-scroll-track">
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="tl-scroll-card">
            <div class="product-card">
              <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img" style="height:180px">
                <?php if($p->thumbnail_url): ?>
                  <img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
                <?php else: ?>
                  <div class="placeholder">🛍️</div>
                <?php endif; ?>
                <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
                <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,<?php echo e($p->id); ?>)" title="Wishlist">♡</button>
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

    
    <?php elseif($layout === 'seupermarketstars'): ?>
      <?php
        $products = $sectionProducts[$si] ?? collect();
        $title    = $sec['name'] ?? $sec['headerText'] ?? 'Featured';
        $catId    = $sec['category'] ?? null;
      ?>
      <?php if($products->count()): ?>
      <div class="sec-head">
        <h2 class="sec-title"><?php echo e($title); ?></h2>
        <a href="<?php echo e(route('shop', array_filter(['category' => $catId]))); ?>" class="sec-link">View all →</a>
      </div>
      <div class="product-grid cols-4" style="margin-bottom:40px">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('web.partials.product-card', ['p' => $p, 'cardVariations' => $sectionVariations[$p->id] ?? []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'topVendors'): ?>
      <?php $vendors = $sectionVendors[$si] ?? collect(); ?>
      <?php if($vendors->count()): ?>
      <div class="sec-head" style="margin-bottom:16px">
        <h2 class="sec-title"><?php echo e($sec['headerText'] ?? 'Top Sellers'); ?></h2>
        <a href="<?php echo e(route('shop')); ?>" class="sec-link">Browse all →</a>
      </div>
      <div class="tl-scroll-section" style="margin-bottom:40px">
        <div class="tl-scroll-track" style="gap:14px">
          <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('vendor.store', $v->id)); ?>" class="vendor-card">
            <div class="vendor-card-logo">
              <?php if($v->logo_url): ?>
                <img src="<?php echo e($v->logo_url); ?>" alt="<?php echo e($v->shop_name); ?>" loading="lazy">
              <?php else: ?>
                <div class="vendor-card-logo-placeholder">🏪</div>
              <?php endif; ?>
            </div>
            <div class="vendor-card-name"><?php echo e(Str::limit($v->shop_name, 20)); ?></div>
            <?php if($v->product_count > 0): ?>
            <div class="vendor-card-count"><?php echo e($v->product_count); ?> items</div>
            <?php endif; ?>
            <?php if((float)$v->rating > 0): ?>
            <div class="vendor-card-rating">
              <span style="color:#f5a623">★</span> <?php echo e(number_format((float)$v->rating, 1)); ?>

            </div>
            <?php endif; ?>
          </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'coupons'): ?>
      <?php
        $couponsData = $sectionCoupons[$si] ?? collect();
        $headerText  = $sec['headerText'] ?? "This Week's Deals";
        $subLabel    = $sec['subLabel']   ?? 'Use code at checkout';
        $hideEmpty   = $sec['hideWhenEmpty'] ?? true;
      ?>
      <?php if($couponsData->count() || !$hideEmpty): ?>
      <div class="promo-section">
        <div class="sec-head">
          <h2 class="sec-title"><?php echo e($headerText); ?></h2>
          <?php if($subLabel): ?>
          <span style="font-size:13px;color:var(--c-mid)"><?php echo e($subLabel); ?></span>
          <?php endif; ?>
        </div>
        <?php if($couponsData->count()): ?>
        <div class="promo-scroll">
          <?php $__currentLoopData = $couponsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci => $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="coupon-card coupon-card-<?php echo e($ci % 6); ?>">
            <div class="coupon-pct">
              <?php if($coupon->discount_type === 'percent'): ?>
                <?php echo e((int)$coupon->amount); ?><sup>%</sup><div class="coupon-desc">Off your order</div>
              <?php else: ?>
                <?php echo e(number_format($coupon->amount, 0)); ?><sup> EGP</sup><div class="coupon-desc">Off your order</div>
              <?php endif; ?>
            </div>
            <div class="coupon-code-row">
              <span class="coupon-code"><?php echo e(strtoupper($coupon->code)); ?></span>
              <button class="coupon-copy-btn" onclick="copyCoupon(this,'<?php echo e(strtoupper($coupon->code)); ?>')">Copy</button>
            </div>
            <?php if($coupon->minimum_amount > 0): ?>
            <div class="coupon-min">Min. order <?php echo e(number_format($coupon->minimum_amount, 0)); ?> EGP</div>
            <?php endif; ?>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <p style="color:var(--c-mid);font-size:14px;text-align:center;padding:20px 0">No active coupons at the moment.</p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'statsBar'): ?>
      <?php
        $stats    = $sectionStats[$si] ?? [];
        $bgColor  = $sec['bgColor'] ?? '#111111';
        $txtColor = $sec['textColor'] ?? '#ffffff';
        $items    = $sec['items'] ?? [
          ['key'=>'products',   'label'=>'Products'],
          ['key'=>'vendors',    'label'=>'Vendors'],
          ['key'=>'categories', 'label'=>'Categories'],
          ['key'=>'brands',     'label'=>'Brands'],
        ];
      ?>
      <?php if(!empty($stats)): ?>
      <div class="tl-stats-bar" style="background:<?php echo e($bgColor); ?>;color:<?php echo e($txtColor); ?>;margin-bottom:36px">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $val = $stats[$item['key']] ?? 0; ?>
          <div class="tl-stat-item">
            <div class="tl-stat-num"><?php echo e(number_format($val)); ?>+</div>
            <div class="tl-stat-lbl"><?php echo e($item['label']); ?></div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'promoBlock'): ?>
      <?php
        $bgColor  = $sec['bgColor'] ?? '#111111';
        $txtColor = $sec['textColor'] ?? '#ffffff';
        $headline = $sec['headline'] ?? 'Special Offer';
        $subtext  = $sec['subtext'] ?? '';
        $btnText  = $sec['btnText'] ?? 'Shop Now';
        $btnLink  = $sec['btnLink'] ?? route('shop');
        $btnColor = $sec['btnColor'] ?? '#e85d26';
        $align    = $sec['align'] ?? 'center';
        $imgUrl   = $sec['image'] ?? '';
      ?>
      <div class="tl-promo-block" style="background:<?php echo e($bgColor); ?>;color:<?php echo e($txtColor); ?>;text-align:<?php echo e($align); ?>;margin-bottom:36px;position:relative;overflow:hidden">
        <?php if($imgUrl): ?>
        <div class="tl-promo-img-wrap">
          <img src="<?php echo e($imgUrl); ?>" alt="" class="tl-promo-img">
        </div>
        <?php endif; ?>
        <div class="tl-promo-content">
          <h2 class="tl-promo-headline" style="color:<?php echo e($txtColor); ?>"><?php echo e($headline); ?></h2>
          <?php if($subtext): ?>
          <p class="tl-promo-sub" style="color:<?php echo e($txtColor); ?>80"><?php echo e($subtext); ?></p>
          <?php endif; ?>
          <a href="<?php echo e($btnLink); ?>" class="tl-promo-btn" style="background:<?php echo e($btnColor); ?>;color:#fff"><?php echo e($btnText); ?></a>
        </div>
      </div>

    
    <?php elseif($layout === 'testimonials'): ?>
      <?php
        $reviews = $sectionTestimonials[$si] ?? collect();
        $title   = $sec['headerText'] ?? 'What Our Customers Say';
      ?>
      <?php if($reviews->count()): ?>
      <div class="sec-head" style="margin-bottom:20px">
        <h2 class="sec-title"><?php echo e($title); ?></h2>
      </div>
      <div class="tl-testimonials" style="margin-bottom:44px">
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="tl-testimonial-card">
          <div class="tl-test-stars">
            <?php for($s=1;$s<=5;$s++): ?>
              <span style="color:<?php echo e($s<=$rev->rating ? '#f5a623' : '#ddd'); ?>">★</span>
            <?php endfor; ?>
          </div>
          <p class="tl-test-comment"><?php if($rev->comment): ?>"<?php echo e(Str::limit($rev->comment, 160)); ?>"<?php else: ?><em style="opacity:.5">No comment</em><?php endif; ?></p>
          <div class="tl-test-meta">
            <div class="tl-test-avatar"><?php echo e(strtoupper(substr($rev->reviewer_name, 0, 1))); ?></div>
            <div>
              <div class="tl-test-name"><?php echo e($rev->reviewer_name); ?></div>
              <?php if($rev->product_name): ?>
              <div class="tl-test-product"><?php echo e(Str::limit($rev->product_name, 30)); ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'newsletter'): ?>
      <?php
        $bgColor  = $sec['bgColor'] ?? '#f0ede8';
        $headline = $sec['headline'] ?? 'Stay in the Loop';
        $subtext  = $sec['subtext'] ?? 'Get the latest deals and new arrivals delivered to your inbox.';
        $btnText  = $sec['btnText'] ?? 'Subscribe';
        $placeholder = $sec['placeholder'] ?? 'Your email address';
      ?>
      <div class="tl-newsletter" style="background:<?php echo e($bgColor); ?>;margin-bottom:36px">
        <div class="tl-newsletter-content">
          <h2 class="tl-newsletter-title"><?php echo e($headline); ?></h2>
          <p class="tl-newsletter-sub"><?php echo e($subtext); ?></p>
          <form class="tl-newsletter-form" onsubmit="nlSubmit(event,this)">
            <input type="email" class="tl-newsletter-input" placeholder="<?php echo e($placeholder); ?>" required>
            <button type="submit" class="tl-newsletter-btn"><?php echo e($btnText); ?></button>
          </form>
          <div class="tl-newsletter-thanks" style="display:none">🎉 Thanks for subscribing!</div>
        </div>
      </div>

    
    <?php elseif($layout === 'brands'): ?>
      <?php if($brands->count()): ?>
      <div class="sec-head" style="margin-bottom:12px">
        <h2 class="sec-title"><?php echo e($sec['name'] ?? 'Brands'); ?></h2>
      </div>
      <div class="brand-strip" style="margin-bottom:36px">
        <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span class="brand-chip"><?php echo e($brand->name); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'trending'): ?>
      <?php $products = $sectionTrending[$si] ?? collect(); $title = $sec['headerText'] ?? 'Trending Now'; ?>
      <?php if($products->count()): ?>
      <div class="sec-head"><h2 class="sec-title">🔥 <?php echo e($title); ?></h2><a href="<?php echo e(route('shop')); ?>" class="sec-link">View all →</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="tl-scroll-card" style="position:relative">
          <?php if($sec['showRankBadge'] ?? true): ?><span class="tl-rank-badge">#<?php echo e($loop->iteration); ?></span><?php endif; ?>
          <div class="product-card">
            <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img" style="height:180px">
              <?php if($p->thumbnail_url): ?><img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy"><?php else: ?><div class="placeholder">🛍️</div><?php endif; ?>
              <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
            </a>
            <div class="product-card-body" style="padding:8px">
              <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name" style="font-size:12px"><?php echo e(Str::limit($p->name, 28)); ?></a>
              <?php if(($sec['showSoldToday'] ?? false) && $p->total_sales > 0): ?><div style="font-size:11px;color:#e85d26;font-weight:600"><?php echo e($p->total_sales); ?>+ sold</div><?php endif; ?>
              <div class="product-card-price"><span class="price-main <?php echo e($p->on_sale ? 'sale' : ''); ?>" style="font-size:12px"><?php echo e(number_format($p->on_sale ? $p->sale_price : $p->price, 0)); ?> EGP</span></div>
            </div>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div></div>
      <?php endif; ?>

    
    <?php elseif($layout === 'arrivals'): ?>
      <?php
        $products = $sectionArrivals[$si] ?? collect();
        $tag      = $sec['tag']   ?? 'Just Arrived';
        $loop2    = $sec['loopInfinitely'] ?? true;
        $pause    = $sec['pauseOnHover']   ?? true;
        $tickerId = 'ticker-'.$si;
      ?>
      <?php if($products->count()): ?>
      <div class="sec-head"><h2 class="sec-title">✨ <?php echo e($sec['headerText'] ?? 'New Arrivals'); ?></h2><a href="<?php echo e(route('shop')); ?>" class="sec-link">See all →</a></div>
      <div class="tl-arrivals-wrap" id="<?php echo e($tickerId); ?>" style="margin-bottom:36px" <?php echo e($pause ? 'onmouseenter="this.style.animationPlayState=\'paused\'" onmouseleave="this.style.animationPlayState=\'running\'"' : ''); ?>>
        <div class="tl-arrivals-track" style="<?php echo e($loop2 ? '' : 'animation:none'); ?>">
          <?php $__currentLoopData = array_merge($products->all(), $products->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('product', $p->id)); ?>" class="tl-arrival-card">
            <?php if($p->thumbnail_url): ?><img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" class="tl-arrival-img"><?php else: ?><div class="tl-arrival-placeholder">🛍️</div><?php endif; ?>
            <div class="tl-arrival-body">
              <?php if($sec['showCategoryChip'] ?? false): ?><span class="tl-arrival-tag"><?php echo e($tag); ?></span><?php endif; ?>
              <div class="tl-arrival-name"><?php echo e(Str::limit($p->name, 22)); ?></div>
              <div class="tl-arrival-price"><?php echo e(number_format($p->on_sale ? $p->sale_price : $p->price, 0)); ?> EGP</div>
            </div>
          </a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'brandLogos'): ?>
      <?php
        $bNames = array_filter(array_map('trim', explode(',', $sec['brands'] ?? '')));
        if (empty($bNames)) $bNames = $brands->pluck('name')->take(10)->toArray();
        $bSize = $sec['size'] ?? 'medium';
      ?>
      <?php if(!empty($bNames)): ?>
      <div class="sec-head" style="margin-bottom:12px"><h2 class="sec-title"><?php echo e($sec['headerText'] ?? 'Shop by Brand'); ?></h2></div>
      <div class="tl-brand-logos" style="margin-bottom:36px">
        <?php $__currentLoopData = $bNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('shop', ['brand' => urlencode($bn)])); ?>" class="tl-brand-logo-chip tl-brand-<?php echo e($bSize); ?>" title="<?php echo e($bn); ?>">
          <span class="tl-brand-letter"><?php echo e(strtoupper(substr($bn, 0, 1))); ?></span>
          <?php if($sec['showNameBelowLogo'] ?? true): ?><span class="tl-brand-name"><?php echo e($bn); ?></span><?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'reviewsCarousel'): ?>
      <?php
        $revs     = $sectionReviewsCarousel[$si] ?? collect();
        $interval = (int)($sec['interval'] ?? 4) * 1000;
        $manualNav = $sec['allowManualNavigation'] ?? true;
        $carId    = 'revcar-'.$si;
      ?>
      <?php if($revs->count()): ?>
      <div class="sec-head" style="margin-bottom:20px"><h2 class="sec-title"><?php echo e($sec['headerText'] ?? 'Customer Reviews'); ?></h2></div>
      <div class="tl-revcar" id="<?php echo e($carId); ?>" style="margin-bottom:44px">
        <div class="tl-revcar-track" id="<?php echo e($carId); ?>-track">
          <?php $__currentLoopData = $revs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ri => $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="tl-revcar-slide" id="<?php echo e($carId); ?>-slide-<?php echo e($ri); ?>" style="display:<?php echo e($ri===0 ? 'flex' : 'none'); ?>">
            <div class="tl-test-stars"><?php for($s=1;$s<=5;$s++): ?><span style="color:<?php echo e($s<=$rev->rating ? '#f5a623' : '#ddd'); ?>">★</span><?php endfor; ?></div>
            <p class="tl-test-comment"><?php if($rev->comment): ?>"<?php echo e(Str::limit($rev->comment, 200)); ?>"<?php endif; ?></p>
            <div class="tl-test-meta">
              <div class="tl-test-avatar"><?php echo e(strtoupper(substr($rev->reviewer_name, 0, 1))); ?></div>
              <div><div class="tl-test-name"><?php echo e($rev->reviewer_name); ?></div>
              <?php if(($sec['showProductReviewed'] ?? true) && $rev->product_name): ?><div class="tl-test-product"><?php echo e(Str::limit($rev->product_name, 30)); ?></div><?php endif; ?></div>
            </div>
          </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($manualNav && $revs->count() > 1): ?>
        <div class="tl-revcar-nav">
          <button onclick="revcarPrev('<?php echo e($carId); ?>',<?php echo e($revs->count()); ?>)">‹</button>
          <div class="tl-revcar-dots">
            <?php $__currentLoopData = $revs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ri => $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span class="tl-revcar-dot <?php echo e($ri===0 ? 'active' : ''); ?>" onclick="revcarGo('<?php echo e($carId); ?>',<?php echo e($ri); ?>,<?php echo e($revs->count()); ?>)"></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <button onclick="revcarNext('<?php echo e($carId); ?>',<?php echo e($revs->count()); ?>)">›</button>
        </div>
        <?php endif; ?>
      </div>
      <script>
      (function(){
        var id='<?php echo e($carId); ?>', total=<?php echo e($revs->count()); ?>, cur=0, iv=<?php echo e($interval); ?>;
        function go(n){ document.getElementById(id+'-slide-'+cur).style.display='none'; cur=(n+total)%total; document.getElementById(id+'-slide-'+cur).style.display='flex'; document.querySelectorAll('#'+id+' .tl-revcar-dot').forEach(function(d,i){d.classList.toggle('active',i===cur);}); }
        window.revcarNext=function(i,t){if(i===id)go(cur+1);}; window.revcarPrev=function(i,t){if(i===id)go(cur-1);}; window.revcarGo=function(i,n,t){if(i===id)go(n);};
        if(total>1)setInterval(function(){go(cur+1);}, iv);
      })();
      </script>
      <?php endif; ?>

    
    <?php elseif($layout === 'activity'): ?>
      <?php
        $count   = $sectionActivity[$si] ?? 0;
        $minC    = (int)($sec['minCount'] ?? 1);
        $tpl     = $sec['messageTemplate'] ?? '{n} people shopped with us recently';
        $message = str_replace('{n}', $count, $tpl);
        $rand    = $sec['randomizeSlightly'] ?? false;
        if ($rand && $count > 0) $message = str_replace($count, rand(max(1,$count-3), $count+5), $message);
      ?>
      <?php if($count >= $minC): ?>
      <div class="tl-activity-bar" style="margin-bottom:16px">
        <span class="tl-activity-dot"></span>
        <span class="tl-activity-msg"><?php echo e($message); ?></span>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'recent'): ?>
      <?php
        $maxP  = (int)($sec['maxProducts']  ?? 8);
        $guests = $sec['showForGuests']     ?? true;
        $loggedOnly = $sec['showOnlyLoggedIn'] ?? false;
      ?>
      <?php if(!$loggedOnly || auth()->check()): ?>
      <div id="recent-section-<?php echo e($si); ?>" style="display:none;margin-bottom:36px">
        <div class="sec-head"><h2 class="sec-title">Recently Viewed</h2></div>
        <div class="tl-scroll-section"><div class="tl-scroll-track" id="recent-track-<?php echo e($si); ?>"></div></div>
      </div>
      <script>
      (function(){
        var max=<?php echo e($maxP); ?>, sid=<?php echo e($si); ?>;
        try {
          var viewed = JSON.parse(localStorage.getItem('rv_products')||'[]');
          if (!viewed.length) return;
          var track = document.getElementById('recent-track-'+sid);
          var section = document.getElementById('recent-section-'+sid);
          viewed.slice(0, max).forEach(function(p){
            track.innerHTML += '<div class="tl-scroll-card"><a href="/product/'+p.id+'" style="text-decoration:none"><div style="width:120px;text-align:center"><img src="'+p.img+'" style="width:110px;height:110px;object-fit:cover;border-radius:8px" onerror="this.style.display=\'none\'">'+'<div style="font-size:11px;color:#333;margin-top:5px;line-height:1.3">'+p.name+'</div><div style="font-size:12px;font-weight:700;color:#e85d26;margin-top:2px">'+p.price+' EGP</div></div></a></div>';
          });
          if (track.children.length) section.style.display = 'block';
        } catch(e) {}
      })();
      </script>
      <?php endif; ?>

    
    <?php elseif($layout === 'bundle'): ?>
      <?php
        $bTitle    = $sec['title']         ?? 'Bundle Deal';
        $bMinQty   = $sec['minQty']        ?? 2;
        $bFreeItems = $sec['freeItems']    ?? 1;
        $bCat      = $sec['category']      ?? '';
        $bSavings  = $sec['showSavingsBadge'] ?? true;
      ?>
      <div class="tl-bundle-card" style="margin-bottom:36px">
        <?php if($bSavings): ?><div class="tl-bundle-badge">Special Deal</div><?php endif; ?>
        <div class="tl-bundle-body">
          <div class="tl-bundle-icon">🎁</div>
          <div class="tl-bundle-info">
            <div class="tl-bundle-title"><?php echo e($bTitle); ?></div>
            <div class="tl-bundle-desc">Buy <strong><?php echo e($bMinQty); ?></strong> items, get <strong><?php echo e($bFreeItems); ?></strong> FREE<?php echo e($bCat ? ' from '.$bCat : ''); ?>!</div>
          </div>
          <a href="/shop" class="tl-bundle-btn">Shop Now</a>
        </div>
      </div>

    
    <?php elseif($layout === 'loyalty'): ?>
      <?php
        $lRate    = $sec['rate']      ?? 10;
        $lMin     = $sec['minRedeem'] ?? 100;
        $lConv    = $sec['convRate']  ?? '100 pts = 5 EGP';
        $lDouble  = $sec['doublePointsWeekends'] ?? false;
        $isWeekend = in_array(now()->dayOfWeek, [0, 6]);
      ?>
      <div class="tl-loyalty-bar" style="margin-bottom:36px">
        <div class="tl-loyalty-inner">
          <span class="tl-loyalty-icon">⭐</span>
          <div class="tl-loyalty-text">
            <strong>Earn <?php echo e($lRate); ?> points per EGP spent!</strong>
            <span>Redeem from <?php echo e($lMin); ?> pts · <?php echo e($lConv); ?>

            <?php if($lDouble && $isWeekend): ?> <span class="tl-loyalty-double">2× Points Weekend!</span><?php endif; ?>
            </span>
          </div>
          <a href="/shop" class="tl-loyalty-btn">Start Earning</a>
        </div>
      </div>

    
    <?php elseif($layout === 'seasonal'): ?>
      <?php
        $sTitle    = $sec['title']     ?? 'Special Season';
        $sSub      = $sec['subtitle']  ?? 'Limited-time offers for this season';
        $sStart    = $sec['startDate'] ?? null;
        $sEnd      = $sec['endDate']   ?? null;
        $sTheme    = $sec['theme']     ?? 'Gold & Purple';
        $sCountdown = $sec['showCountdownToEvent'] ?? false;
        $sAnimate  = $sec['animateEntrance'] ?? true;
        $now2      = now();
        $inRange   = (!$sStart || $now2->gte(\Carbon\Carbon::parse($sStart)))
                  && (!$sEnd   || $now2->lte(\Carbon\Carbon::parse($sEnd)));
        $themeCss  = match($sTheme) {
          'Green & White'  => 'background:linear-gradient(135deg,#1a7a3a,#2ecc71);color:#fff',
          'Red & Gold'     => 'background:linear-gradient(135deg,#c0392b,#f39c12);color:#fff',
          'Gold & Purple'  => 'background:linear-gradient(135deg,#6c3483,#f9ca24);color:#fff',
          default          => 'background:linear-gradient(135deg,#1a1a2e,#e85d26);color:#fff',
        };
      ?>
      <?php if($inRange): ?>
      <div class="tl-seasonal <?php echo e($sAnimate ? 'tl-seasonal-animate' : ''); ?>" style="<?php echo e($themeCss); ?>;margin-bottom:36px">
        <div class="tl-seasonal-inner">
          <div class="tl-seasonal-text">
            <h2 class="tl-seasonal-title"><?php echo e($sTitle); ?></h2>
            <p class="tl-seasonal-sub"><?php echo e($sSub); ?></p>
          </div>
          <?php if($sCountdown && $sEnd): ?>
          <div class="tl-seasonal-cd" id="seas-cd-<?php echo e($si); ?>">
            <span id="sd-d-<?php echo e($si); ?>">00</span>d <span id="sd-h-<?php echo e($si); ?>">00</span>h <span id="sd-m-<?php echo e($si); ?>">00</span>m
          </div>
          <script>
          (function(){
            var end=new Date('<?php echo e($sEnd); ?>T23:59:59').getTime();
            function tick2(){var r=Math.max(0,Math.floor((end-Date.now())/1000));document.getElementById('sd-d-<?php echo e($si); ?>').textContent=String(Math.floor(r/86400)).padStart(2,'0');document.getElementById('sd-h-<?php echo e($si); ?>').textContent=String(Math.floor((r%86400)/3600)).padStart(2,'0');document.getElementById('sd-m-<?php echo e($si); ?>').textContent=String(Math.floor((r%3600)/60)).padStart(2,'0');if(r>0)setTimeout(tick2,30000);}tick2();
          })();
          </script>
          <?php endif; ?>
          <a href="/shop" class="tl-seasonal-btn">Shop Now →</a>
        </div>
      </div>
      <?php endif; ?>

    
    <?php elseif($layout === 'referral'): ?>
      <?php
        $rRef   = $sec['rewardReferrer'] ?? 50;
        $rNew   = $sec['rewardNewUser']  ?? 30;
        $rMin   = $sec['minOrder']       ?? 200;
        $rCta   = $sec['ctaText']        ?? 'Invite Friends & Earn!';
        $rWa    = $sec['shareViaWhatsApp'] ?? true;
      ?>
      <div class="tl-referral-card" style="margin-bottom:36px">
        <div class="tl-referral-inner">
          <div class="tl-referral-icon">🎁</div>
          <div class="tl-referral-body">
            <div class="tl-referral-title"><?php echo e($rCta); ?></div>
            <div class="tl-referral-desc">You earn <strong><?php echo e($rRef); ?> EGP</strong> and your friend gets <strong><?php echo e($rNew); ?> EGP</strong> off their first order over <?php echo e($rMin); ?> EGP!</div>
            <div class="tl-referral-actions">
              <input type="text" class="tl-referral-link" value="<?php echo e(url('/')); ?>/ref/<?php echo e(auth()->id() ?? 'YOURCODE'); ?>" readonly onclick="this.select()">
              <?php if($rWa): ?>
              <a href="https://wa.me/?text=<?php echo e(urlencode('Join Ramo Store and get '.($rNew).' EGP off your first order! '.url('/').'/ref/'.(auth()->id() ?? 'YOURCODE'))); ?>" target="_blank" class="tl-referral-wa-btn">Share via WhatsApp</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    
    <?php elseif($layout === 'complete'): ?>
      <?php
        $cStrategy = $sec['strategy'] ?? 'Same category';
        $cDisc     = $sec['showDiscountIfBoughtTogether'] ?? false;
      ?>
      <div class="tl-complete-card" style="margin-bottom:36px">
        <div class="tl-complete-inner">
          <span style="font-size:28px">👗</span>
          <div>
            <div style="font-weight:700;font-size:16px;margin-bottom:4px">Complete the Look</div>
            <div style="font-size:13px;color:var(--c-mid)">Find <?php echo e(strtolower($cStrategy)); ?> items that go perfectly together.<?php if($cDisc): ?> Bundle discount applied at checkout!<?php endif; ?></div>
          </div>
          <a href="/shop" class="btn btn-primary" style="font-size:13px;padding:10px 20px">Browse Collections →</a>
        </div>
      </div>

    
    <?php elseif($layout === 'recommended'): ?>
      <?php
        $products = $sectionTrending[$si] ?? collect();
        $recLabel = $sec['personalizedLabel'] ?? false;
        $title    = $recLabel ? 'Picked For You' : ($sec['headerText'] ?? 'Recommended For You');
      ?>
      <?php if($products->count()): ?>
      <div class="sec-head"><h2 class="sec-title"><?php echo e($title); ?></h2><a href="<?php echo e(route('shop')); ?>" class="sec-link">See all →</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="tl-scroll-card"><div class="product-card">
          <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-img" style="height:180px">
            <?php if($p->thumbnail_url): ?><img src="<?php echo e($p->thumbnail_url); ?>" alt="<?php echo e($p->name); ?>" loading="lazy"><?php else: ?><div class="placeholder">🛍️</div><?php endif; ?>
            <?php if($p->on_sale): ?><span class="badge-sale">SALE</span><?php endif; ?>
          </a>
          <div class="product-card-body" style="padding:8px">
            <a href="<?php echo e(route('product', $p->id)); ?>" class="product-card-name" style="font-size:12px"><?php echo e(Str::limit($p->name, 28)); ?></a>
            <div class="product-card-price"><span class="price-main <?php echo e($p->on_sale ? 'sale' : ''); ?>" style="font-size:12px"><?php echo e(number_format($p->on_sale ? $p->sale_price : $p->price, 0)); ?> EGP</span></div>
          </div>
        </div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div></div>
      <?php endif; ?>

    
    <?php elseif($layout === 'flash' || $layout === 'announcement'): ?>
      

    <?php endif; ?>

  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    
    <div class="hero">
      <div class="hero-text">
        <div class="hero-eyebrow">New Season</div>
        <h1 class="hero-title">Style that speaks<br>for itself.</h1>
        <p class="hero-sub">Discover the latest collections — premium quality, delivered to your door.</p>
        <a href="<?php echo e(route('shop')); ?>" class="btn btn-white">Shop Now →</a>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.tl-stats-bar{display:flex;flex-wrap:wrap;justify-content:center;gap:0;border-radius:var(--radius-lg);padding:36px 20px;margin-top:0}
.tl-stat-item{flex:1;min-width:140px;text-align:center;padding:16px 24px;border-right:1px solid rgba(255,255,255,.15)}
.tl-stat-item:last-child{border-right:none}
.tl-stat-num{font-size:36px;font-weight:800;line-height:1;margin-bottom:6px}
.tl-stat-lbl{font-size:13px;font-weight:500;opacity:.75;letter-spacing:.5px;text-transform:uppercase}

.tl-promo-block{border-radius:var(--radius-lg);padding:64px 48px;display:flex;align-items:center;gap:48px}
.tl-promo-img-wrap{flex-shrink:0;width:260px;height:200px;border-radius:12px;overflow:hidden}
.tl-promo-img{width:100%;height:100%;object-fit:cover}
.tl-promo-content{flex:1}
.tl-promo-headline{font-size:32px;font-weight:800;letter-spacing:-.5px;margin-bottom:12px;line-height:1.2}
.tl-promo-sub{font-size:15px;line-height:1.6;margin-bottom:24px}
.tl-promo-btn{display:inline-flex;align-items:center;padding:12px 28px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;transition:.2s}
.tl-promo-btn:hover{opacity:.88;transform:translateY(-1px)}

.tl-testimonials{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.tl-testimonial-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:22px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .15s}
.tl-testimonial-card:hover{box-shadow:var(--shadow-md)}
.tl-test-stars{display:flex;gap:2px;font-size:16px}
.tl-test-comment{font-size:13.5px;color:var(--c-mid);line-height:1.65;flex:1}
.tl-test-meta{display:flex;align-items:center;gap:10px;margin-top:4px}
.tl-test-avatar{width:36px;height:36px;border-radius:50%;background:var(--c-orange);color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tl-test-name{font-size:13px;font-weight:700;color:var(--c-dark)}
.tl-test-product{font-size:11px;color:var(--c-mid);margin-top:2px}

.tl-newsletter{border-radius:var(--radius-lg);padding:56px 32px;text-align:center}
.tl-newsletter-content{max-width:520px;margin:auto}
.tl-newsletter-title{font-size:26px;font-weight:800;letter-spacing:-.4px;margin-bottom:10px}
.tl-newsletter-sub{font-size:14px;color:var(--c-mid);margin-bottom:24px;line-height:1.6}
.tl-newsletter-form{display:flex;gap:10px;max-width:420px;margin:auto}
.tl-newsletter-input{flex:1;padding:12px 18px;border:1.5px solid var(--c-light);border-radius:50px;font-size:14px;font-family:inherit;outline:none;background:#fff;transition:border-color .15s}
.tl-newsletter-input:focus{border-color:#999}
.tl-newsletter-btn{padding:12px 24px;background:var(--c-dark);color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s;white-space:nowrap}
.tl-newsletter-btn:hover{background:var(--c-orange)}
.tl-newsletter-thanks{margin-top:16px;font-size:14px;font-weight:600;color:var(--c-orange)}

@media(max-width:640px){
  .tl-stat-item{min-width:120px;padding:12px 16px}
  .tl-stat-num{font-size:28px}
  .tl-promo-block{flex-direction:column;padding:36px 24px;text-align:center}
  .tl-promo-img-wrap{width:100%;height:180px}
  .tl-newsletter-form{flex-direction:column}
}

/* ── ANNOUNCEMENT BAR ── */
.tl-announcement{position:relative;width:100%;padding:9px 40px 9px 16px;font-size:13px;font-weight:500;overflow:hidden;z-index:100}
.tl-announce-close{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;font-size:20px;cursor:pointer;line-height:1;padding:0}
.tl-announce-static{text-align:center}
.tl-announce-scroll-wrap{overflow:hidden;white-space:nowrap}
.tl-announce-scroll{display:inline-block;animation:scrollTicker 25s linear infinite}
@keyframes scrollTicker{from{transform:translateX(0)}to{transform:translateX(-33.333%)}}

/* ── FLASH SALE BAR ── */
.tl-flash-bar{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border-radius:12px;padding:18px 20px;margin-bottom:20px}
.tl-flash-inner{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.tl-flash-icon{font-size:28px;flex-shrink:0}
.tl-flash-text{flex:1;min-width:0}
.tl-flash-title{display:block;font-size:16px;font-weight:800}
.tl-flash-disc{display:inline-block;background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:14px;font-weight:700;margin-top:2px}
.tl-flash-min{display:block;font-size:12px;opacity:.8;margin-top:2px}
.tl-flash-countdown{display:flex;align-items:center;gap:6px;flex-shrink:0}
.tl-cd-unit{text-align:center;background:rgba(0,0,0,.25);border-radius:8px;padding:6px 10px}
.tl-cd-num{display:block;font-size:22px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.tl-cd-lbl{font-size:10px;opacity:.8;letter-spacing:.5px}
.tl-cd-sep{font-size:22px;font-weight:800;opacity:.6}
.tl-flash-btn{flex-shrink:0;background:#fff;color:#c0392b;padding:10px 20px;border-radius:50px;font-size:13px;font-weight:800;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-flash-btn:hover{background:#ffe0e0}

/* ── TRENDING ── */
.tl-rank-badge{position:absolute;top:8px;left:8px;background:#e85d26;color:#fff;font-size:11px;font-weight:800;padding:2px 7px;border-radius:20px;z-index:2}

/* ── NEW ARRIVALS TICKER ── */
.tl-arrivals-wrap{overflow:hidden;cursor:pointer}
.tl-arrivals-track{display:flex;gap:14px;animation:arrivalsScroll 30s linear infinite}
@keyframes arrivalsScroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tl-arrival-card{display:flex;flex-direction:column;flex-shrink:0;width:140px;text-decoration:none;color:inherit;border:1.5px solid var(--c-light);border-radius:10px;overflow:hidden;transition:box-shadow .15s}
.tl-arrival-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.1)}
.tl-arrival-img{width:100%;height:120px;object-fit:cover}
.tl-arrival-placeholder{width:100%;height:120px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;font-size:28px}
.tl-arrival-body{padding:8px}
.tl-arrival-tag{display:inline-block;background:#e85d2618;color:#e85d26;font-size:10px;font-weight:700;padding:1px 6px;border-radius:20px;margin-bottom:4px}
.tl-arrival-name{font-size:11px;font-weight:600;color:var(--c-dark);line-height:1.3;margin-bottom:3px}
.tl-arrival-price{font-size:12px;font-weight:700;color:#e85d26}

/* ── BRAND LOGOS ROW ── */
.tl-brand-logos{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}
.tl-brand-logo-chip{display:flex;flex-direction:column;align-items:center;justify-content:center;border:1.5px solid var(--c-light);border-radius:10px;padding:12px 16px;text-decoration:none;color:inherit;transition:.15s;min-width:80px;background:#fff}
.tl-brand-logo-chip:hover{border-color:#e85d26;box-shadow:0 2px 10px rgba(0,0,0,.08)}
.tl-brand-letter{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#e85d26,#f59e0b);color:#fff;font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:6px}
.tl-brand-name{font-size:11px;font-weight:600;color:var(--c-mid);text-align:center}
.tl-brand-small .tl-brand-letter{width:34px;height:34px;font-size:14px}
.tl-brand-large .tl-brand-letter{width:56px;height:56px;font-size:22px}

/* ── REVIEWS CAROUSEL ── */
.tl-revcar{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:32px;max-width:600px;margin-left:auto;margin-right:auto}
.tl-revcar-slide{flex-direction:column;gap:14px}
.tl-revcar-nav{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:20px}
.tl-revcar-nav button{background:none;border:1.5px solid var(--c-light);border-radius:50%;width:34px;height:34px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.15s}
.tl-revcar-nav button:hover{border-color:#e85d26;color:#e85d26}
.tl-revcar-dots{display:flex;gap:6px}
.tl-revcar-dot{width:8px;height:8px;border-radius:50%;background:#ddd;cursor:pointer;transition:.15s}
.tl-revcar-dot.active{background:#e85d26;width:22px;border-radius:4px}

/* ── LIVE ACTIVITY ── */
.tl-activity-bar{display:flex;align-items:center;gap:10px;background:#fff8f4;border:1.5px solid #fde8d8;border-radius:50px;padding:8px 18px;font-size:13px;font-weight:500;color:#c0392b;width:fit-content}
.tl-activity-dot{width:8px;height:8px;border-radius:50%;background:#e85d26;flex-shrink:0;animation:actPulse 1.5s ease-in-out infinite}
@keyframes actPulse{0%,100%{opacity:1}50%{opacity:.3}}
.tl-activity-msg{color:#7c3826}

/* ── BUNDLE DEAL ── */
.tl-bundle-card{background:linear-gradient(135deg,#fff8f4,#fde8d8);border:1.5px solid #fbd5bd;border-radius:var(--radius-lg);overflow:hidden;position:relative;padding:24px}
.tl-bundle-badge{position:absolute;top:16px;right:16px;background:#e85d26;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px}
.tl-bundle-body{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.tl-bundle-icon{font-size:44px;flex-shrink:0}
.tl-bundle-info{flex:1}
.tl-bundle-title{font-size:20px;font-weight:800;margin-bottom:6px}
.tl-bundle-desc{font-size:14px;color:var(--c-mid);line-height:1.5}
.tl-bundle-btn{flex-shrink:0;background:#e85d26;color:#fff;padding:12px 24px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-bundle-btn:hover{background:#c94d1a}

/* ── LOYALTY POINTS ── */
.tl-loyalty-bar{background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:var(--radius-lg);padding:20px 28px}
.tl-loyalty-inner{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.tl-loyalty-icon{font-size:32px;flex-shrink:0}
.tl-loyalty-text{flex:1;color:#fff}
.tl-loyalty-text strong{display:block;font-size:15px;margin-bottom:3px}
.tl-loyalty-text span{font-size:13px;opacity:.75}
.tl-loyalty-double{background:#f59e0b;color:#000;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:8px}
.tl-loyalty-btn{flex-shrink:0;background:#e85d26;color:#fff;padding:10px 20px;border-radius:50px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap}

/* ── SEASONAL BANNER ── */
.tl-seasonal{border-radius:var(--radius-lg);padding:48px 36px;overflow:hidden;position:relative}
.tl-seasonal-animate{animation:seasonIn .5s ease-out}
@keyframes seasonIn{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:none}}
.tl-seasonal-inner{display:flex;align-items:center;gap:32px;flex-wrap:wrap;position:relative;z-index:1}
.tl-seasonal-text{flex:1}
.tl-seasonal-title{font-size:28px;font-weight:800;margin-bottom:8px;text-shadow:0 2px 8px rgba(0,0,0,.2)}
.tl-seasonal-sub{font-size:15px;opacity:.85;line-height:1.5}
.tl-seasonal-cd{font-size:22px;font-weight:800;letter-spacing:.5px;white-space:nowrap;flex-shrink:0}
.tl-seasonal-btn{flex-shrink:0;background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.5);color:#fff;padding:12px 24px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;backdrop-filter:blur(4px);transition:.15s}
.tl-seasonal-btn:hover{background:rgba(255,255,255,.35)}

/* ── REFERRAL ── */
.tl-referral-card{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1.5px solid #bae6fd;border-radius:var(--radius-lg);padding:28px}
.tl-referral-inner{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap}
.tl-referral-icon{font-size:40px;flex-shrink:0}
.tl-referral-body{flex:1}
.tl-referral-title{font-size:18px;font-weight:800;margin-bottom:6px;color:#0c4a6e}
.tl-referral-desc{font-size:13px;color:#0369a1;line-height:1.5;margin-bottom:14px}
.tl-referral-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tl-referral-link{flex:1;padding:9px 14px;border:1.5px solid #bae6fd;border-radius:8px;font-size:12px;font-family:monospace;background:#fff;cursor:text;min-width:180px;color:#0c4a6e}
.tl-referral-wa-btn{display:inline-flex;align-items:center;gap:6px;background:#25d366;color:#fff;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-referral-wa-btn:hover{background:#1fb958}

/* ── COMPLETE THE LOOK ── */
.tl-complete-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px}
.tl-complete-inner{display:flex;align-items:center;gap:20px;flex-wrap:wrap}

@media(max-width:640px){
  .tl-flash-inner{gap:10px}
  .tl-cd-num{font-size:18px}
  .tl-seasonal{padding:32px 20px}
  .tl-seasonal-title{font-size:22px}
  .tl-loyalty-inner{gap:12px}
  .tl-bundle-body{gap:14px}
  .tl-arrival-card{width:120px}
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function nlSubmit(e, form) {
  e.preventDefault();
  form.style.display = 'none';
  form.nextElementSibling.style.display = 'block';
}

function copyCoupon(btn, code) {
  navigator.clipboard.writeText(code).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    btn.style.background = 'rgba(255,255,255,.55)';
    setTimeout(() => { btn.textContent = orig; btn.style.background = ''; }, 2000);
  }).catch(() => {
    const el = document.createElement('textarea');
    el.value = code;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    btn.textContent = 'Copied!';
    setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
  });
}

// ── SLIDER LOGIC ──────────────────────────────────────────────────
const sliderState = {};

function initSlider(id, count, autoPlay) {
  sliderState[id] = { current: 0, count, timer: null };
  if (autoPlay && count > 1) {
    sliderState[id].timer = setInterval(() => slideNext(id), 4000);
  }
}

function slideTo(id, idx) {
  const state = sliderState[id];
  if (!state) return;
  state.current = (idx + state.count) % state.count;
  const track = document.getElementById(id + '-track');
  if (track) track.style.transform = `translateX(-${state.current * 100}%)`;
  document.querySelectorAll(`#${id}-dots .tl-dot`).forEach((d, i) => {
    d.classList.toggle('active', i === state.current);
  });
  // Reset timer on manual navigation
  if (state.timer) {
    clearInterval(state.timer);
    state.timer = setInterval(() => slideNext(id), 4000);
  }
}

function slideNext(id) {
  const s = sliderState[id];
  if (s) slideTo(id, s.current + 1);
}

function slidePrev(id) {
  const s = sliderState[id];
  if (s) slideTo(id, s.current - 1);
}

// Init all sliders declared in page
<?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si => $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(($sec['layout'] ?? '') === 'bannerImage'): ?>
    <?php
      $items    = $sec['items'] ?? [];
      $isSlider = ($sec['design'] ?? 'default') !== 'static' && count($items) > 1;
      $autoPlay = $sec['autoPlay'] ?? true;
    ?>
    <?php if($isSlider && count($items) > 1): ?>
      initSlider('slider-<?php echo e($si); ?>', <?php echo e(count($items)); ?>, <?php echo e($autoPlay ? 'true' : 'false'); ?>);
    <?php endif; ?>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/web/home.blade.php ENDPATH**/ ?>