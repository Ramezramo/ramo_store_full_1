<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    public function home()
    {
        // ── Load horizon_layout timeline config ──────────────────
        $lang = session('locale', 'en');
        $configRow = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', $lang)
            ->first();

        if (! $configRow) {
            $configRow = DB::table('app_configs')
                ->where('config_key', 'horizon_layout')
                ->first();
        }

        $sections = $configRow ? (json_decode($configRow->value, true) ?? []) : [];

        // ── Pre-load product data for each section ────────────────
        $sectionProducts        = [];
        $sectionVendors         = [];
        $sectionTestimonials    = [];
        $sectionStats           = [];
        $sectionCoupons         = [];
        $sectionTrending        = [];
        $sectionArrivals        = [];
        $sectionActivity        = [];
        $sectionReviewsCarousel = [];

        foreach ($sections as $i => $section) {
            $layout = $section['layout'] ?? '';

            if (in_array($layout, ['twoColumn', 'saleImages', 'seupermarketstars'])) {
                $catId = $section['category'] ?? null;
                $max   = (int) ($section['maxItemsToShow'] ?? 8);
                $max   = max(1, min($max, 20));
                $query = $this->baseProductQuery();
                if ($catId) {
                    $query->join('product_category as tl_pc', function ($j) use ($catId) {
                        $j->on('tl_pc.product_id', '=', 'p.id')
                          ->where('tl_pc.category_id', (int)$catId);
                    });
                }
                $sectionProducts[$i] = $query->limit($max)->get()
                    ->map(fn($p) => $this->parseProduct($p));
            }

            if ($layout === 'testimonials') {
                $max = (int) ($section['maxItemsToShow'] ?? 4);
                $max = max(1, min($max, 12));
                $sectionTestimonials[$i] = DB::table('product_reviews as r')
                    ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                    ->leftJoin('products_data as p', 'p.id', '=', 'r.product_id')
                    ->where('r.approved', true)
                    ->where('r.rating', '>=', ($section['minRating'] ?? 4))
                    ->select(
                        'r.id', 'r.rating', 'r.body as comment', 'r.created_at',
                        DB::raw("COALESCE(
                            NULLIF(TRIM(COALESCE(u.first_name,'') || ' ' || COALESCE(u.last_name,'')), ''),
                            NULLIF(TRIM(COALESCE(u.firstname,'') || ' ' || COALESCE(u.lastname,'')), ''),
                            u.name, u.display_name, 'Customer'
                        ) as reviewer_name"),
                        'p.name as product_name'
                    )
                    ->orderByRaw('RANDOM()')
                    ->limit($max)
                    ->get();
            }

            if ($layout === 'statsBar') {
                $sectionStats[$i] = [
                    'products'   => DB::table('products_data')->where('status', 'publish')->where('acceptance_status', 'approved')->count(),
                    'vendors'    => DB::table('vendor_users')->where('status', 'approved')->count(),
                    'categories' => DB::table('categories2')->count(),
                    'brands'     => DB::table('brands')->count(),
                    'orders'     => DB::table('orders')->count(),
                    'reviews'    => DB::table('product_reviews')->where('approved', true)->count(),
                ];
            }

            if ($layout === 'coupons') {
                $max    = (int) ($section['maxItemsToShow'] ?? 6);
                $max    = max(1, min($max, 20));
                $sortBy = $section['sortBy'] ?? 'amount';
                $orderCol = $sortBy === 'newest' ? 'date_created' : 'amount';
                $couponsQuery = DB::table('coupons')
                    ->where('status', 'publish')
                    ->whereRaw("(date_expires IS NULL OR date_expires > NOW())")
                    ->orderBy($orderCol, 'desc')
                    ->limit($max)
                    ->get();
                if ($couponsQuery->isEmpty() && ($section['showExpiredFallback'] ?? true)) {
                    $couponsQuery = DB::table('coupons')
                        ->where('status', 'publish')
                        ->orderBy($orderCol, 'desc')
                        ->limit($max)
                        ->get();
                }
                $sectionCoupons[$i] = $couponsQuery;
            }

            if ($layout === 'trending' || $layout === 'recommended') {
                $max  = (int) ($section['count'] ?? 10);
                $max  = max(1, min($max, 20));
                $algo = $section['algo'] ?? 'sold7d';
                $orderCol = ($algo === 'rated') ? 'avg_rating' : 'p.total_sales';
                $q = DB::table('products_data as p')
                    ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
                    ->where('p.status', 'publish')
                    ->where('p.acceptance_status', 'approved')
                    ->select(
                        'p.id', 'p.name', 'p.slug', 'p.images', 'p.total_sales',
                        'p.description', 'p.stock_quantity', 'p.unit', 'p.vendor_id',
                        DB::raw('MIN(pv.price) as price'),
                        DB::raw('MIN(pv.sale_price) as sale_price'),
                        DB::raw('MAX(p.discount_percentage) as discount_percentage')
                    )
                    ->groupBy('p.id','p.name','p.slug','p.images','p.total_sales',
                              'p.description','p.stock_quantity','p.unit','p.vendor_id')
                    ->orderBy($orderCol === 'avg_rating' ? DB::raw('MIN(pv.price)') : DB::raw('p.total_sales'), 'desc')
                    ->limit($max)->get()->map(fn($p) => $this->parseProduct($p));
                $sectionTrending[$i] = $q;
            }

            if ($layout === 'arrivals') {
                $max = (int) ($section['count'] ?? 8);
                $max = max(1, min($max, 20));
                $sectionArrivals[$i] = DB::table('products_data as p')
                    ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
                    ->where('p.status', 'publish')
                    ->where('p.acceptance_status', 'approved')
                    ->select(
                        'p.id', 'p.name', 'p.slug', 'p.images', 'p.date_created',
                        'p.description', 'p.stock_quantity', 'p.unit', 'p.vendor_id',
                        DB::raw('MIN(pv.price) as price'),
                        DB::raw('MIN(pv.sale_price) as sale_price'),
                        DB::raw('MAX(p.discount_percentage) as discount_percentage')
                    )
                    ->groupBy('p.id','p.name','p.slug','p.images','p.date_created',
                              'p.description','p.stock_quantity','p.unit','p.vendor_id')
                    ->orderBy('p.date_created', 'desc')
                    ->limit($max)->get()->map(fn($p) => $this->parseProduct($p));
            }

            if ($layout === 'activity') {
                $window   = $section['window'] ?? '24h';
                $interval = match($window) {
                    '7d'    => '7 days',
                    'month' => '1 month',
                    default => '24 hours',
                };
                $sectionActivity[$i] = DB::table('orders')
                    ->whereRaw("date_created > NOW() - INTERVAL '$interval'")
                    ->count();
            }

            if ($layout === 'reviewsCarousel') {
                $max      = (int) ($section['count'] ?? 6);
                $max      = max(1, min($max, 20));
                $minStars = (int) ($section['minStars'] ?? 4);
                $sectionReviewsCarousel[$i] = DB::table('product_reviews as r')
                    ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
                    ->leftJoin('products_data as p', 'p.id', '=', 'r.product_id')
                    ->where('r.approved', true)
                    ->where('r.rating', '>=', $minStars)
                    ->select(
                        'r.id', 'r.rating', 'r.body as comment', 'r.created_at',
                        DB::raw("COALESCE(
                            NULLIF(TRIM(COALESCE(u.first_name,'') || ' ' || COALESCE(u.last_name,'')), ''),
                            NULLIF(TRIM(COALESCE(u.firstname,'') || ' ' || COALESCE(u.lastname,'')), ''),
                            u.name, u.display_name, 'Customer'
                        ) as reviewer_name"),
                        'p.name as product_name'
                    )
                    ->orderByRaw('RANDOM()')
                    ->limit($max)->get();
            }

            if ($layout === 'topVendors') {
                $max    = (int) ($section['maxItemsToShow'] ?? 6);
                $max    = max(1, min($max, 20));
                $sortBy = $section['sortBy'] ?? 'products';
                $orderCol = match($sortBy) {
                    'rating'  => 'rating',
                    'newest'  => 'created_at',
                    default   => 'product_count',
                };
                $sectionVendors[$i] = DB::table('vendor_users')
                    ->where('status', 'approved')
                    ->select('id','shop_name','shop_logo','rating','rating_count','product_count','shop_address')
                    ->orderBy($orderCol, 'desc')
                    ->limit($max)
                    ->get()
                    ->map(function ($v) {
                        $v->logo_url = AppConstants::imageUrl($v->shop_logo);
                        return $v;
                    });
            }
        }

        // ── Bulk-load variations for all section products ─────────
        $allProductIds = collect($sectionProducts)->flatten(1)->pluck('id')->unique()->values()->toArray();
        $sectionVariations = [];
        if (!empty($allProductIds)) {
            $sectionVariations = DB::table('product_variations')
                ->whereIn('product_id', $allProductIds)
                ->orderBy('main_variation', 'desc')
                ->get()
                ->map(function ($v) {
                    $v->attributes = is_string($v->attributes)
                        ? (json_decode($v->attributes, true) ?? json_decode(stripslashes($v->attributes), true) ?? [])
                        : (array) $v->attributes;
                    $v->images = is_string($v->images)
                        ? (json_decode($v->images, true) ?? json_decode(stripslashes($v->images), true) ?? [])
                        : (array) $v->images;
                    return $v;
                })
                ->groupBy('product_id')
                ->toArray();
        }

        // ── Load categories for category-strip sections ───────────
        $allCategories = DB::table('categories2')->orderBy('name')->get(['id', 'name', 'slug'])->keyBy('id');

        // ── Brands ───────────────────────────────────────────────
        $brands = DB::table('brands')->orderBy('name')->get();

        return view('web.home', compact(
            'sections', 'sectionProducts', 'sectionVariations',
            'sectionVendors', 'allCategories', 'brands',
            'sectionTestimonials', 'sectionStats', 'sectionCoupons',
            'sectionTrending', 'sectionArrivals', 'sectionActivity', 'sectionReviewsCarousel'
        ));
    }

    public function shop(Request $request)
    {
        $query = $this->baseProductQuery();

        // Build category hierarchy for sidebar
        $allCats = DB::table('categories2')->orderBy('menu_order')->orderBy('name')->get();

        $parentCats = $allCats->filter(fn($c) => $c->parent == 0 || $c->parent === null)->values();
        $childCats  = $allCats->filter(fn($c) => $c->parent > 0)->groupBy('parent');

        // Collect all child IDs for a given category ID (including itself)
        $activeCategoryId = $request->filled('category') ? (int) $request->category : null;
        $filterCategoryIds = [];

        if ($activeCategoryId) {
            $filterCategoryIds[] = $activeCategoryId;
            // If this is a parent, also include its children
            if (isset($childCats[$activeCategoryId])) {
                foreach ($childCats[$activeCategoryId] as $child) {
                    $filterCategoryIds[] = $child->id;
                }
            }
        }

        if (!empty($filterCategoryIds)) {
            $ids = $filterCategoryIds;
            $query->join('product_category as pc', function ($j) use ($ids) {
                $j->on('pc.product_id', '=', 'p.id')
                  ->whereIn('pc.category_id', $ids);
            })->distinct();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'ilike', "%$search%")
                  ->orWhere('p.description', 'ilike', "%$search%");
            });
        }

        if ($request->sort === 'price_asc')  $query->orderBy('price', 'asc');
        elseif ($request->sort === 'price_desc') $query->orderBy('price', 'desc');
        else $query->orderBy('p.id', 'desc');

        $rawProducts = $query->paginate(16)->withQueryString();
        $products    = $rawProducts->through(fn($p) => $this->parseProduct($p));

        // Find active parent: if a child is selected, its parent is considered "open"
        $activeParentId = null;
        if ($activeCategoryId) {
            $activeCat = $allCats->firstWhere('id', $activeCategoryId);
            if ($activeCat && $activeCat->parent > 0) {
                $activeParentId = $activeCat->parent;
            } else {
                $activeParentId = $activeCategoryId;
            }
        }

        return view('web.shop', compact(
            'products', 'parentCats', 'childCats',
            'activeCategoryId', 'activeParentId'
        ));
    }

    public function product($id)
    {
        $raw = $this->baseProductQuery()
            ->where('p.id', $id)
            ->first();

        if (! $raw) abort(404);

        $product    = $this->parseProduct($raw);
        $variations = DB::table('product_variations')
            ->where('product_id', $id)
            ->orderBy('main_variation', 'desc')
            ->get()
            ->map(function ($v) {
                if (is_string($v->attributes)) {
                    $v->attributes = json_decode($v->attributes, true)
                        ?? json_decode(stripslashes($v->attributes), true)
                        ?? [];
                } else {
                    $v->attributes = (array) $v->attributes;
                }
                $v->images = is_string($v->images)
                    ? (json_decode($v->images, true) ?? json_decode(stripslashes($v->images), true) ?? [])
                    : (array) $v->images;
                return $v;
            });

        // ── Vendor info + more products from this vendor ──────────
        $vendor = null;
        $vendorProducts = collect();
        if (!empty($raw->vendor_id)) {
            $vendor = DB::table('vendor_users')
                ->where('id', $raw->vendor_id)
                ->first(['id','shop_name','shop_logo','rating','rating_count','product_count','shop_address','status']);
            if ($vendor) {
                $vendor->logo_url = AppConstants::imageUrl($vendor->shop_logo);
                $vendorProducts = $this->baseProductQuery()
                    ->where('p.vendor_id', $raw->vendor_id)
                    ->where('p.id', '!=', $id)
                    ->limit(8)
                    ->get()
                    ->map(fn($p) => $this->parseProduct($p));
            }
        }

        $related = $this->baseProductQuery()
            ->where('p.id', '!=', $id)
            ->orderByRaw('RANDOM()')
            ->limit(4)
            ->get()
            ->map(fn($p) => $this->parseProduct($p));

        $reviews = DB::table('product_reviews as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.product_id', $id)
            ->where('r.approved', true)
            ->select(
                'r.*',
                DB::raw("COALESCE(
                    NULLIF(TRIM(COALESCE(u.first_name,'') || ' ' || COALESCE(u.last_name,'')), ''),
                    NULLIF(TRIM(COALESCE(u.firstname,'') || ' ' || COALESCE(u.lastname,'')), ''),
                    u.name,
                    u.display_name,
                    'Customer'
                ) as reviewer_name"),
                'u.avatar'
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        // Rating distribution (5 down to 1)
        $distribution = DB::table('product_reviews')
            ->where('product_id', $id)
            ->where('approved', true)
            ->select('rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('rating')
            ->get()
            ->keyBy('rating');

        $userReview = Auth::check()
            ? DB::table('product_reviews')
                ->where('user_id', Auth::id())
                ->where('product_id', $id)
                ->first()
            : null;
        $userReviewed = (bool) $userReview;

        $helpfulVoted = session('review_helpful_voted', []);

        return view('web.product', compact(
            'product', 'variations', 'related',
            'vendor', 'vendorProducts',
            'reviews', 'userReviewed', 'userReview',
            'distribution', 'helpfulVoted'
        ));
    }

    public function vendor($id)
    {
        $vendor = DB::table('vendor_users')
            ->where('id', $id)
            ->where('status', 'approved')
            ->first(['id','shop_name','shop_logo','shop_banner','rating','rating_count','product_count','shop_address','created_at']);

        if (! $vendor) abort(404);

        $vendor->logo_url   = AppConstants::imageUrl($vendor->shop_logo);
        $vendor->banner_url = AppConstants::imageUrl($vendor->shop_banner);

        $query = $this->baseProductQuery()
            ->where('p.vendor_id', $id);

        if (request()->sort === 'price_asc')       $query->orderBy('price', 'asc');
        elseif (request()->sort === 'price_desc')  $query->orderBy('price', 'desc');
        else                                        $query->orderBy('p.id', 'desc');

        $rawProducts = $query->paginate(12)->withQueryString();
        $products    = $rawProducts->through(fn($p) => $this->parseProduct($p));

        return view('web.vendor', compact('vendor', 'products'));
    }

    private function baseProductQuery()
    {
        return DB::table('products_data as p')
            ->select(
                'p.id', 'p.name', 'p.slug', 'p.images',
                'p.description', 'p.stock_quantity', 'p.unit',
                'p.vendor_id',
                DB::raw('MIN(pv.price) as price'),
                DB::raw('MIN(pv.sale_price) as sale_price'),
                DB::raw('MAX(p.discount_percentage) as discount_percentage')
            )
            ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->groupBy(
                'p.id', 'p.name', 'p.slug', 'p.images',
                'p.description', 'p.stock_quantity', 'p.unit', 'p.vendor_id'
            );
    }

    private function parseProduct($p)
    {
        $imgs = [];
        if ($p->images) {
            $decoded = is_string($p->images) ? json_decode($p->images, true) : (array) $p->images;
            if (is_array($decoded)) $imgs = $decoded;
        }

        $p->thumbnail_url = AppConstants::productThumbnailUrl($p->images);
        $p->gallery_urls  = AppConstants::productGalleryUrls($p->images);

        // Separate image groups for the product detail page
        $imgArr = [];
        if ($p->images) {
            $decoded = is_string($p->images) ? json_decode($p->images, true) : (array) $p->images;
            if (is_array($decoded)) $imgArr = $decoded;
        }
        $p->other_images_urls   = array_values(array_filter(array_map(
            fn($path) => AppConstants::imageUrl($path),
            (array) ($imgArr['other_images'] ?? [])
        )));
        $p->natural_images_urls = array_values(array_filter(array_map(
            fn($path) => AppConstants::imageUrl($path),
            (array) ($imgArr['natural_images'] ?? [])
        )));

        $p->price      = (float) ($p->price ?? 0);
        $p->sale_price = (float) ($p->sale_price ?? 0);
        $discPct       = (float) ($p->discount_percentage ?? 0);

        // If discount_percentage is set but no real sale price was stored, compute it
        if ($discPct > 0 && ($p->sale_price <= 0 || $p->sale_price >= $p->price) && $p->price > 0) {
            $p->sale_price = round($p->price * (1 - $discPct / 100), 2);
        }

        $p->on_sale    = $p->sale_price > 0 && $p->sale_price < $p->price;
        $p->display_price = $p->on_sale ? $p->sale_price : $p->price;

        $p->unit_label = null;
        if (!empty($p->unit)) {
            $unitData = is_string($p->unit) ? json_decode($p->unit, true) : (array)$p->unit;
            if (is_array($unitData)) {
                $unitName  = array_key_first($unitData);
                $unitValue = $unitData[$unitName];
                $p->unit_label = $unitValue . ' ' . $unitName;
            }
        }

        // Attach any active coupon that targets this product
        static $couponMap = null;
        if ($couponMap === null) {
            $couponMap = [];
            $coupons = DB::table('coupons')
                ->where('status', 'publish')
                ->whereRaw("(date_expires IS NULL OR date_expires > NOW())")
                ->get(['id', 'code', 'amount', 'discount_type', 'product_ids']);
            foreach ($coupons as $coupon) {
                $pids = json_decode($coupon->product_ids ?? '[]', true) ?? [];
                foreach ($pids as $pid) {
                    if (!isset($couponMap[(int)$pid])) {
                        $couponMap[(int)$pid] = $coupon;
                    }
                }
            }
        }
        $p->coupon = $couponMap[$p->id] ?? null;

        return $p;
    }
}
