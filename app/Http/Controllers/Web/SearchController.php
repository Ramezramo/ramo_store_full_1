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
        $minPrice   = $request->input('min_price') !== null && $request->input('min_price') !== '' ? (float) $request->input('min_price') : null;
        $maxPrice   = $request->input('max_price') !== null && $request->input('max_price') !== '' ? (float) $request->input('max_price') : null;
        $inStock    = $request->boolean('in_stock');
        $sort       = $request->input('sort', 'relevance');
        $categoryId = $request->input('category');
        $perPage    = 12;

        // Global price range for slider
        $priceRange = DB::table('product_variations')
            ->selectRaw('MIN(price::numeric) as min_price, MAX(price::numeric) as max_price')
            ->first();

        // Base query
        $query = DB::table('products_data as p')
            ->select(
                'p.id', 'p.name', 'p.slug', 'p.images',
                'p.description', 'p.stock_quantity', 'p.unit',
                DB::raw('MIN(pv.price::numeric) as price'),
                DB::raw('MIN(pv.sale_price::numeric) as sale_price'),
                DB::raw('MAX(p.discount_percentage) as discount_percentage')
            )
            ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
            ->groupBy('p.id', 'p.name', 'p.slug', 'p.images', 'p.description', 'p.stock_quantity', 'p.unit');

        // Search query
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereRaw('LOWER(p.name) LIKE ?', ['%' . strtolower($q) . '%'])
                    ->orWhereRaw('LOWER(p.description) LIKE ?', ['%' . strtolower($q) . '%']);
            });
        }

        // Category filter
        if ($categoryId) {
            $query->join('product_category as pc', 'pc.product_id', '=', 'p.id')
                  ->where('pc.category_id', $categoryId);
        }

        // In-stock filter
        if ($inStock) {
            $query->where('p.stock_quantity', '>', 0);
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
                $query->orderBy('p.id', 'desc');
        }

        $raw      = $query->paginate($perPage);
        $products = $raw->through(fn($p) => $this->parseProduct($p));

        $categories = DB::table('categories2')->orderBy('name')->get();

        // Active filters for display chips
        $activeFilters = [];
        if ($q)          $activeFilters[] = ['type' => 'q',        'label' => "\"$q\"",      'remove' => 'q'];
        if ($categoryId) {
            $cat = $categories->firstWhere('id', $categoryId);
            $activeFilters[] = ['type' => 'category', 'label' => $cat?->name ?? "#$categoryId", 'remove' => 'category'];
        }
        if ($minPrice !== null || $maxPrice !== null) {
            $pLabel = ($minPrice !== null ? number_format($minPrice) : '0') . ' – ' . ($maxPrice !== null ? number_format($maxPrice) : number_format($priceRange->max_price)) . ' EGP';
            $activeFilters[] = ['type' => 'price', 'label' => $pLabel, 'remove' => ['min_price','max_price']];
        }
        if ($inStock) $activeFilters[] = ['type' => 'in_stock', 'label' => 'In Stock Only', 'remove' => 'in_stock'];

        return view('web.search', compact(
            'products', 'q', 'minPrice', 'maxPrice', 'inStock', 'sort',
            'categoryId', 'categories', 'priceRange', 'activeFilters'
        ));
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

        $p->price      = (float) ($p->price ?? 0);
        $p->sale_price = (float) ($p->sale_price ?? 0);
        $p->on_sale    = $p->sale_price > 0 && $p->sale_price < $p->price;
        $p->display_price = $p->on_sale ? $p->sale_price : $p->price;

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
