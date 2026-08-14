<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q          = trim($request->input('q', ''));
        $locale     = strtolower((string) session('locale', 'en'));
        $minPrice   = $request->input('min_price') !== null && $request->input('min_price') !== '' ? (float) $request->input('min_price') : null;
        $maxPrice   = $request->input('max_price') !== null && $request->input('max_price') !== '' ? (float) $request->input('max_price') : null;
        $inStock    = $request->boolean('in_stock');
        $sort = $request->input('sort', 'relevance');
        $categoryId = filter_var($request->input('category'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $categoryId = $categoryId === false ? null : $categoryId;
        $perPage = 12;

        // Global price range for products that are actually sellable on the storefront.
        $priceRange = DB::table('product_variations as pv')
            ->join('products_data as p', 'p.id', '=', 'pv.product_id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->where('pv.regular_price', '>', 0)
            ->selectRaw('MIN(pv.price::numeric) as min_price, MAX(pv.price::numeric) as max_price')
            ->first();

        // Base query. Discovery surfaces must never leak a draft, rejected, or
        // unpriced product even when it remains in a legacy search index.
        $query = DB::table('products_data as p')
            ->select(
                'p.id', 'p.name', 'p.slug', 'p.images', 'p.translations',
                'p.description', 'p.stock_quantity', 'p.unit',
                'p.minimum_order_qty', 'p.max_orders_per_person', 'p.sold_individually',
                DB::raw('MIN(pv.price::numeric) as price'),
                DB::raw('MIN(pv.regular_price::numeric) as regular_price'),
                DB::raw('MIN(pv.sale_price::numeric) as sale_price'),
                DB::raw('MAX(p.discount_percentage) as discount_percentage')
            )
            ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->whereExists(function ($variations) {
                $variations->selectRaw('1')
                    ->from('product_variations as sellable_pv')
                    ->whereColumn('sellable_pv.product_id', 'p.id')
                    ->where('sellable_pv.regular_price', '>', 0);
            })
            ->groupBy(
                'p.id', 'p.name', 'p.slug', 'p.images', 'p.translations',
                'p.description', 'p.stock_quantity', 'p.unit',
                'p.minimum_order_qty', 'p.max_orders_per_person', 'p.sold_individually'
            );

        // Precision-first customer search: a product must match its own name,
        // URL slug, or one of its localized *names*. `search_text`, descriptions,
        // and translated descriptions are intentionally excluded because incidental
        // phrases (for example, a sneaker description mentioning "jeans") are not
        // product identity and should not surface unrelated merchandise.
        if ($q !== '') {
            $needle = '%' . $q . '%';
            $query->where(function ($sub) use ($needle) {
                $sub->where('p.name', 'ILIKE', $needle)
                    ->orWhere('p.slug', 'ILIKE', $needle)
                    ->orWhereRaw(<<<'SQL'
                        EXISTS (
                            SELECT 1
                            FROM jsonb_array_elements(
                                COALESCE(NULLIF(p.translations, '')::jsonb, '[]'::jsonb)
                            ) AS translation
                            WHERE COALESCE(translation->>'name', '') ILIKE ?
                        )
                    SQL, [$needle]);
            });
        }

        // Category filter
        if ($categoryId !== null) {
            $query->join('product_category as pc', 'pc.product_id', '=', 'p.id')
                  ->where('pc.category_id', $categoryId);
        }

        // In-stock filter
        if ($inStock) {
            $query->whereExists(function ($variations) {
                $variations->selectRaw('1')
                    ->from('product_variations as in_stock_pv')
                    ->whereColumn('in_stock_pv.product_id', 'p.id')
                    ->where('in_stock_pv.stock_quantity', '>', 0);
            });
        }

        // Price filter via HAVING (after GROUP BY)
        if ($minPrice !== null) {
            $query->havingRaw('MIN(pv.price::numeric) >= ?', [$minPrice]);
        }
        if ($maxPrice !== null) {
            $query->havingRaw('MIN(pv.price::numeric) <= ?', [$maxPrice]);
        }

        // Sort
        switch ($sort) {
            case 'price_asc':  $query->orderByRaw('MIN(pv.price::numeric) ASC');  break;
            case 'price_desc': $query->orderByRaw('MIN(pv.price::numeric) DESC'); break;
            case 'newest':     $query->orderBy('p.id', 'desc');                   break;
            case 'name_asc':   $query->orderBy('p.name', 'asc');                  break;
            case 'name_desc':  $query->orderBy('p.name', 'desc');                 break;
            default:
                if ($q !== '') {
                    $query->orderByRaw(
                        'CASE WHEN p.name ILIKE ? THEN 0 WHEN p.name ILIKE ? THEN 1 ELSE 2 END',
                        [$q, '%' . $q . '%']
                    );
                }
                $query->orderBy('p.id', 'desc');
        }

        $raw      = $query->paginate($perPage);
        $products = $raw->through(fn($p) => $this->localizeSearchProductText($this->parseProduct($p), $locale));
        $productIds = $products->pluck('id')->all();
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

        $categories = DB::table('categories2')->orderBy('name')->get();

        // Active filters for display chips
        $activeFilters = [];
        if ($q)          $activeFilters[] = ['type' => 'q',        'label' => "\"$q\"",      'remove' => 'q'];
        if ($categoryId) {
            $cat = $categories->firstWhere('id', $categoryId);
            $activeFilters[] = ['type' => 'category', 'label' => $cat?->name ?? "#$categoryId", 'remove' => 'category'];
        }
        if ($minPrice !== null || $maxPrice !== null) {
            $pLabel = ($minPrice !== null ? number_format($minPrice) : '0') . ' – ' . ($maxPrice !== null ? number_format($maxPrice) : number_format($priceRange->max_price ?? 0)) . ' EGP';
            $activeFilters[] = ['type' => 'price', 'label' => $pLabel, 'remove' => ['min_price','max_price']];
        }
        if ($inStock) $activeFilters[] = ['type' => 'in_stock', 'label' => 'In Stock Only', 'remove' => 'in_stock'];

        return view('web.search', compact(
            'products', 'q', 'minPrice', 'maxPrice', 'inStock', 'sort',
            'categoryId', 'categories', 'priceRange', 'activeFilters', 'cardVariations'
        ));
    }

    private function localizeSearchProductText($product, string $locale)
    {
        $product->tl_display_name = $product->name;
        $product->tl_display_description = $product->description ?? '';

        if ($locale !== 'ar' || empty($product->translations)) {
            return $product;
        }

        $rows = is_string($product->translations)
            ? json_decode($product->translations, true)
            : $product->translations;
        $rows = is_array($rows) ? $rows : [];
        foreach ($rows as $row) {
            if (is_array($row) && ($row['locale'] ?? '') === 'ar') {
                $product->tl_display_name = trim((string) ($row['name'] ?? '')) ?: $product->name;
                $product->tl_display_description = trim((string) ($row['description'] ?? '')) ?: ($product->description ?? '');
                break;
            }
        }

        return $product;
    }

    private function parseProduct($p)
    {
        $imgs = [];
        if ($p->images) {
            $decoded = is_string($p->images) ? json_decode($p->images, true) : (array) $p->images;
            if (is_array($decoded)) $imgs = $decoded;
        }

        $p->thumbnail_url = \App\Constants\AppConstants::productThumbnailUrl($p->images);
        $p->gallery_urls  = \App\Constants\AppConstants::productGalleryUrls($p->images);

        $p->price         = (float) ($p->price ?? 0);
        $p->regular_price = (float) ($p->regular_price ?? 0);
        $p->sale_price    = (float) ($p->sale_price ?? 0);

        // Use regular_price as the authoritative original (undiscounted) price when available
        $basePrice = $p->regular_price > 0 ? $p->regular_price : $p->price;

        $p->on_sale       = $p->sale_price > 0 && $p->sale_price < $basePrice;
        $p->display_price = $p->on_sale ? $p->sale_price : $basePrice;

        // Set price to the original so the strikethrough shows the correct value
        if ($p->on_sale) {
            $p->price = $basePrice;
        }

        $unitRaw = $p->unit ?? null;
        if ($unitRaw && is_string($unitRaw)) {
            $unitDecoded = json_decode($unitRaw, true) ?? json_decode(stripslashes($unitRaw), true);
            if (is_array($unitDecoded)) {
                $p->unit_label = implode(', ', array_map(fn($u, $q) => "$q $u", array_keys($unitDecoded), array_values($unitDecoded)));
            } else {
                $p->unit_label = $unitRaw;
            }
        } else {
            $p->unit_label = null;
        }

        return $p;
    }
}
