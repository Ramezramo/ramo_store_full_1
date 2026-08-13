<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Services\FlashSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    public function setLocale(Request $request, string $lang)
    {
        $lang = strtolower(trim($lang));
        $isAvailable = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', $lang)
            ->exists();

        abort_unless($isAvailable, 404);
        $request->session()->put('locale', $lang);

        $redirect = (string) $request->input('redirect', '');
        $baseUrl = rtrim(url('/'), '/');
        if ($redirect === '' || ! str_starts_with($redirect, $baseUrl)) {
            $redirect = route('home');
        }

        return redirect()->to($redirect);
    }

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

        $sections  = $configRow ? (json_decode($configRow->value, true) ?? []) : [];
        $flashSale = FlashSaleService::getActive($lang);

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
        $sectionCategoryCards      = [];
        $sectionFeaturedProduct    = [];
        $sectionFeaturedVariations = [];

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
                    ->map(fn($p) => $this->localizeTimelineProductText($this->parseProduct($p, $flashSale), $lang));
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
                        'p.id', 'p.name', 'p.slug', 'p.images', 'p.total_sales', 'p.translations',
                        'p.description', 'p.stock_quantity', 'p.unit', 'p.vendor_id',
                        DB::raw('MIN(pv.price) as price'),
                        DB::raw('MIN(pv.sale_price) as sale_price'),
                        DB::raw('MAX(p.discount_percentage) as discount_percentage')
                    )
                    ->addSelect(DB::raw("(SELECT string_agg(pc_sub.category_id::text, ',') FROM product_category pc_sub WHERE pc_sub.product_id = p.id) as product_cat_ids"))
                    ->groupBy('p.id','p.name','p.slug','p.images','p.total_sales','p.translations',
                              'p.description','p.stock_quantity','p.unit','p.vendor_id')
                    ->orderBy($orderCol === 'avg_rating' ? DB::raw('MIN(pv.price)') : DB::raw('p.total_sales'), 'desc')
                    ->limit($max)->get()->map(fn($p) => $this->localizeTimelineProductText($this->parseProduct($p, $flashSale), $lang));
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
                        'p.id', 'p.name', 'p.slug', 'p.images', 'p.date_created', 'p.translations',
                        'p.description', 'p.stock_quantity', 'p.unit', 'p.vendor_id',
                        DB::raw('MIN(pv.price) as price'),
                        DB::raw('MIN(pv.sale_price) as sale_price'),
                        DB::raw('MAX(p.discount_percentage) as discount_percentage')
                    )
                    ->addSelect(DB::raw("(SELECT string_agg(pc_sub.category_id::text, ',') FROM product_category pc_sub WHERE pc_sub.product_id = p.id) as product_cat_ids"))
                    ->groupBy('p.id','p.name','p.slug','p.images','p.date_created','p.translations',
                              'p.description','p.stock_quantity','p.unit','p.vendor_id')
                    ->orderBy('p.date_created', 'desc')
                    ->limit($max)->get()->map(fn($p) => $this->localizeTimelineProductText($this->parseProduct($p, $flashSale), $lang));
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

            if ($layout === 'categoryCards') {
                $max         = (int) ($section['maxItemsToShow'] ?? 12);
                $max         = max(1, min($max, 24));
                $parentOnly  = $section['parentOnly'] ?? true;
                $q = DB::table('categories2 as c')
                    ->leftJoin('product_category as pc', 'c.id', '=', 'pc.category_id')
                    ->leftJoin('products_data as p', function ($join) {
                        $join->on('p.id', '=', 'pc.product_id')
                             ->where('p.status', '=', 'publish')
                             ->where('p.acceptance_status', '=', 'approved');
                    })
                    ->select('c.id', 'c.name', 'c.slug', 'c.image as cat_image')
                    ->selectRaw('COUNT(DISTINCT pc.product_id) as product_count')
                    ->selectRaw('MIN(p.images) as first_product_images')
                    ->groupBy('c.id', 'c.name', 'c.slug', 'c.image')
                    ->orderByRaw('COUNT(DISTINCT pc.product_id) DESC')
                    ->limit($max);
                if ($parentOnly) {
                    $q->where(fn($sq) => $sq->where('c.parent', 0)->orWhereNull('c.parent'));
                }
                $sectionCategoryCards[$i] = $q->get()->map(function ($c) {
                    $c->thumbnail_url = $c->cat_image
                        ? AppConstants::imageUrl($c->cat_image)
                        : AppConstants::productThumbnailUrl($c->first_product_images);
                    return $c;
                });
            }

            if ($layout === 'productCustomizer') {
                $productId = (int)($section['productId'] ?? 0);
                if ($productId > 0) {
                    $p = $this->baseProductQuery()->where('p.id', $productId)->first();
                    if ($p) {
                        $sectionFeaturedProduct[$i] = $this->localizeTimelineProductText($this->parseProduct($p, $flashSale), $lang);
                        $sectionFeaturedVariations[$i] = DB::table('product_variations')
                            ->where('product_id', $productId)
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
                            });
                    }
                }
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
            'sectionTrending', 'sectionArrivals', 'sectionActivity', 'sectionReviewsCarousel',
            'sectionCategoryCards', 'sectionFeaturedProduct', 'sectionFeaturedVariations'
        ));
    }

    public function shop(Request $request)
    {
        // Keep the navigation/filter shell fast. Product data is loaded by the
        // browser immediately after the shell paints via the AJAX branch below.
        $isProductRequest = $request->ajax();
        $locale = strtolower((string) session('locale', 'en'));
        $flashSale = $isProductRequest ? FlashSaleService::getActive($locale) : null;
        $query = $this->baseProductQuery();

        // Build category hierarchy for sidebar
        $allCats = DB::table('categories2')->orderBy('menu_order')->orderBy('name')->get();

        $parentCats = $allCats->filter(fn($c) => $c->parent == 0 || $c->parent === null)->values();
        $childCats  = $allCats->filter(fn($c) => $c->parent > 0)->groupBy('parent');

        // Product counts per category
        $catCounts = DB::table('product_category as pc')
            ->join('products_data as p', 'p.id', '=', 'pc.product_id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->select('pc.category_id', DB::raw('count(*) as cnt'))
            ->groupBy('pc.category_id')
            ->pluck('cnt', 'category_id');

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

        if ($request->filled('brand')) {
            $brandName = $request->brand;
            $brandId = DB::table('brands')->where('name', $brandName)->value('id');
            if ($brandId) {
                $query->where('p.brand_id', (string) $brandId);
            }
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

        // AJAX / infinite-scroll request — return JSON with rendered card HTML
        if ($isProductRequest) {
            $rawProducts = $query->paginate(16)->withQueryString();
            $products    = $rawProducts->through(fn($p) =>
                $this->localizeTimelineProductText($this->parseProduct($p, $flashSale), $locale)
            );
            $productIds  = $products->pluck('id')->all();
            $cardVariations = [];

            if (!empty($productIds)) {
                $cardVariations = DB::table('product_variations')
                    ->whereIn('product_id', $productIds)
                    ->orderBy('main_variation', 'desc')
                    ->get()
                    ->map(function ($variation) {
                        $variation->attributes = is_string($variation->attributes)
                            ? (json_decode($variation->attributes, true) ?? json_decode(stripslashes($variation->attributes), true) ?? [])
                            : (array) $variation->attributes;
                        $variation->images = is_string($variation->images)
                            ? (json_decode($variation->images, true) ?? json_decode(stripslashes($variation->images), true) ?? [])
                            : (array) $variation->images;
                        return $variation;
                    })
                    ->groupBy('product_id')
                    ->toArray();
            }

            $html = '';
            foreach ($products as $p) {
                $html .= view('web.partials.product-card', [
                    'p'              => $p,
                    'cardVariations' => $cardVariations[$p->id] ?? [],
                ])->render();
            }
            return response()->json([
                'html'     => $html,
                'hasMore'  => $products->hasMorePages(),
                'nextPage' => $products->currentPage() + 1,
                'total'    => $products->total(),
            ]);
        }

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

        $activeBrandName = $request->filled('brand') ? $request->brand : null;
        $allBrands = DB::table('brands')->orderBy('name')->get();

        // This setting changes only the narrow-phone card layout. The desktop and
        // tablet grid remain unchanged, and an invalid/missing value safely keeps
        // the existing horizontal mobile presentation.
        $rawMobileLayout = DB::table('app_configs')
            ->where('config_key', 'shop_mobile_product_layout')
            ->whereNull('lang')
            ->value('value');
        $decodedMobileLayout = is_string($rawMobileLayout) ? json_decode($rawMobileLayout, true) : null;
        $shopMobileLayout = is_string($decodedMobileLayout)
            ? $decodedMobileLayout
            : trim((string) $rawMobileLayout, " \t\n\r\0\x0B\"");
        if (!in_array($shopMobileLayout, ['grid', 'horizontal'], true)) {
            $shopMobileLayout = 'horizontal';
        }
        // Arabic shop mode uses the compact two-column phone grid so localized
        // product cards remain consistent with the Arabic storefront direction.
        if ($locale === 'ar') {
            $shopMobileLayout = 'grid';
        }

        // Deliberately leave $products null here. Rendering product cards in the
        // initial response was the main reason the shop felt stuck before paint.
        $products = null;

        return response()
            ->view('web.shop', compact(
                'products', 'parentCats', 'childCats',
                'activeCategoryId', 'activeParentId', 'catCounts',
                'activeBrandName', 'allBrands', 'shopMobileLayout'
            ))
            ->header('Cache-Control', 'private, no-cache');
    }

    public function product($id)
    {
        $flashSale = FlashSaleService::getActive();
        $raw = $this->baseProductQuery()
            ->where('p.id', $id)
            ->first();

        if (! $raw) abort(404);

        $product    = $this->parseProduct($raw, $flashSale);
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
                    ->map(fn($p) => $this->parseProduct($p, $flashSale));
            }
        }

        $related = $this->baseProductQuery()
            ->where('p.id', '!=', $id)
            ->orderByRaw('RANDOM()')
            ->limit(4)
            ->get()
            ->map(fn($p) => $this->parseProduct($p, $flashSale));

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

        // Check wishlist state from DB (logged-in) or session (guest)
        $inWishlist = Auth::check()
            ? DB::table('wishlists')->where('user_id', Auth::id())->where('product_id', $id)->exists()
            : in_array((int)$id, session('ramo_wishlist', []));

        return view('web.product', compact(
            'product', 'variations', 'related',
            'vendor', 'vendorProducts',
            'reviews', 'userReviewed', 'userReview',
            'distribution', 'helpfulVoted',
            'inWishlist'
        ));
    }

    public function vendor($id)
    {
        $flashSale = FlashSaleService::getActive();
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
        $products    = $rawProducts->through(fn($p) => $this->parseProduct($p, $flashSale));

        return view('web.vendor', compact('vendor', 'products'));
    }

    private function baseProductQuery()
    {
        return DB::table('products_data as p')
            ->select(
                'p.id', 'p.name', 'p.slug', 'p.images', 'p.translations',
                'p.description', 'p.stock_quantity', 'p.unit',
                'p.minimum_order_qty', 'p.max_orders_per_person', 'p.sold_individually',
                'p.vendor_id',
                DB::raw('MIN(pv.price) as price'),
                DB::raw('MIN(pv.regular_price) as regular_price'),
                DB::raw('MIN(pv.sale_price) as sale_price'),
                DB::raw('MAX(p.discount_percentage) as discount_percentage'),
                DB::raw("(SELECT string_agg(pc_sub.category_id::text, ',') FROM product_category pc_sub WHERE pc_sub.product_id = p.id) as product_cat_ids")
            )
            ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->groupBy(
                'p.id', 'p.name', 'p.slug', 'p.images', 'p.translations',
                'p.description', 'p.stock_quantity', 'p.unit',
                'p.minimum_order_qty', 'p.max_orders_per_person', 'p.sold_individually',
                'p.vendor_id'
            );
    }

    /**
     * Add Timeline-only product text for the active language. The main English
     * name and description are retained whenever the selected locale is absent.
     */
    private function localizeTimelineProductText($product, string $locale)
    {
        $product->timeline_name = $product->name;
        $product->timeline_description = $product->description;

        if (strtolower($locale) === 'en') {
            return $product;
        }

        $translations = $product->translations ?? [];
        if (is_string($translations)) {
            $translations = json_decode($translations, true) ?? [];
        }

        foreach ((array) $translations as $translation) {
            if (! is_array($translation) || strtolower((string) ($translation['locale'] ?? '')) !== strtolower($locale)) {
                continue;
            }

            if (filled($translation['name'] ?? null)) {
                $product->timeline_name = $translation['name'];
            }
            if (filled($translation['description'] ?? null)) {
                $product->timeline_description = $translation['description'];
            }
            break;
        }

        return $product;
    }

    private function parseProduct($p, $flashSale = null)
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

        $p->price         = (float) ($p->price ?? 0);
        $p->regular_price = (float) ($p->regular_price ?? 0);
        $p->sale_price    = (float) ($p->sale_price ?? 0);
        $discPct          = (float) ($p->discount_percentage ?? 0);

        // Use regular_price as the authoritative original (undiscounted) price when available
        $basePrice = $p->regular_price > 0 ? $p->regular_price : $p->price;

        // If discount_percentage is set but no valid sale price exists, compute from regular_price
        if ($discPct > 0 && ($p->sale_price <= 0 || $p->sale_price >= $basePrice) && $basePrice > 0) {
            $p->sale_price = round($basePrice * (1 - $discPct / 100), 2);
        }

        $p->on_sale       = $p->sale_price > 0 && $p->sale_price < $basePrice;
        $p->display_price = $p->on_sale ? $p->sale_price : $basePrice;

        // Set price to the original (regular) price so the strikethrough shows the correct value
        if ($p->on_sale) {
            $p->price = $basePrice;
        }

        // Flash sale discount — applied after existing product pricing
        $p->flash_sale         = false;
        $p->flash_discount_pct = 0;
        if ($flashSale) {
            $qualifies = false;
            if ($flashSale->applyTo === 'all') {
                $qualifies = true;
            } elseif ($flashSale->applyTo === 'products') {
                $qualifies = in_array((int) $p->id, $flashSale->targetProductIds);
            } elseif ($flashSale->applyTo === 'categories') {
                $catIds    = array_map('intval', array_filter(explode(',', $p->product_cat_ids ?? '')));
                $qualifies = !empty(array_intersect($catIds, $flashSale->targetCategories));
            }

            if ($qualifies && $flashSale->discount > 0) {
                $origBase   = $p->on_sale ? $p->sale_price : ($p->regular_price > 0 ? $p->regular_price : $p->price);
                $flashPrice = round($origBase * (1 - $flashSale->discount / 100), 2);
                if ($flashPrice > 0 && $flashPrice < $origBase) {
                    if (!$p->on_sale) {
                        $p->price = $origBase;
                    }
                    $p->sale_price         = $flashPrice;
                    $p->on_sale            = true;
                    $p->display_price      = $flashPrice;
                    $p->flash_sale         = true;
                    $p->flash_discount_pct = $flashSale->discount;
                }
            }
        }

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
