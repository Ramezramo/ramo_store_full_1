<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class VendorProductController extends Controller
{
    private function vendor()
    {
        return auth()->guard('vendor_web')->user();
    }

    private function imageBase(): string
    {
        return \App\Constants\AppConstants::imageBase();
    }

    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $vendor   = $this->vendor();
        $products = DB::table('products_data')
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('created_at')
            ->get();

        $ids = $products->pluck('id')->toArray();

        $priceRanges = collect();
        $varAlerts   = collect();
        if (count($ids)) {
            $priceRanges = DB::table('product_variations')
                ->whereIn('product_id', $ids)
                ->select(
                    'product_id',
                    DB::raw('MIN(price) as min_price'),
                    DB::raw('MAX(price) as max_price'),
                    DB::raw('SUM(stock_quantity) as total_stock'),
                    DB::raw('COUNT(*) as var_count')
                )
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            $varAlerts = DB::table('product_variations')
                ->whereIn('product_id', $ids)
                ->select(
                    'product_id',
                    DB::raw("SUM(CASE WHEN stock_status = 'outofstock' THEN 1 ELSE 0 END) as out_of_stock_count"),
                    DB::raw("SUM(CASE WHEN stock_status = 'onbackorder' THEN 1 ELSE 0 END) as backorder_count"),
                    DB::raw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as disabled_count")
                )
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');
        }

        return view('web.vendor.products.index', compact('products', 'priceRanges', 'varAlerts'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // BULK PRICE UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function bulkPrice(Request $request)
    {
        $request->validate([
            'product_ids'   => 'required|array|min:1|max:500',
            'product_ids.*' => 'integer|min:1',
            'action'        => 'required|in:set_discount,remove_discount,increase_price,decrease_price',
            'value'         => 'nullable|numeric|min:0|max:100',
        ]);

        $vendor  = $this->vendor();
        $ids     = array_unique(array_map('intval', $request->input('product_ids')));
        $action  = $request->input('action');
        $value   = (float) $request->input('value', 0);
        $now     = now();

        // Verify all product IDs belong to this vendor
        $ownedIds = DB::table('products_data')
            ->where('vendor_id', $vendor->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        if (count($ownedIds) !== count($ids)) {
            return response()->json(['error' => 'One or more products do not belong to your account.'], 403);
        }

        // Fetch product rows so we have discount_percentage for each
        $productRows = DB::table('products_data')
            ->whereIn('id', $ownedIds)
            ->get()
            ->keyBy('id');

        $updated = 0;
        $newPrices = [];

        foreach ($ownedIds as $productId) {
            $productRow = $productRows[$productId] ?? null;
            $vars       = DB::table('product_variations')->where('product_id', $productId)->get();

            if ($vars->isEmpty()) continue;

            switch ($action) {

                case 'set_discount':
                    $discPct = min(max(round($value, 2), 0), 80);
                    DB::table('products_data')->where('id', $productId)->update([
                        'discount_percentage' => (string) $discPct,
                        'on_sale'             => $discPct > 0,
                        'updated_at'          => $now,
                    ]);
                    foreach ($vars as $v) {
                        $newSale = round((float) $v->regular_price * (1 - $discPct / 100), 2);
                        DB::table('product_variations')->where('id', $v->id)->update([
                            'sale_price' => $newSale,
                            'price'      => $newSale,
                            'updated_at' => $now,
                        ]);
                    }
                    $newPrices[$productId] = ['discount' => $discPct];
                    break;

                case 'remove_discount':
                    DB::table('products_data')->where('id', $productId)->update([
                        'discount_percentage' => '0',
                        'on_sale'             => false,
                        'updated_at'          => $now,
                    ]);
                    foreach ($vars as $v) {
                        DB::table('product_variations')->where('id', $v->id)->update([
                            'sale_price' => $v->regular_price,
                            'price'      => $v->regular_price,
                            'updated_at' => $now,
                        ]);
                    }
                    $newPrices[$productId] = ['discount' => 0];
                    break;

                case 'increase_price':
                case 'decrease_price':
                    $factor  = $action === 'increase_price' ? (1 + $value / 100) : (1 - $value / 100);
                    $discPct = (float) ($productRow->discount_percentage ?? 0);
                    DB::table('products_data')->where('id', $productId)->update(['updated_at' => $now]);
                    foreach ($vars as $v) {
                        $newReg  = max(1, round((float) $v->regular_price * $factor, 2));
                        $newSale = $discPct > 0
                            ? round($newReg * (1 - $discPct / 100), 2)
                            : $newReg;
                        DB::table('product_variations')->where('id', $v->id)->update([
                            'regular_price' => $newReg,
                            'sale_price'    => $newSale,
                            'price'         => $newSale,
                            'updated_at'    => $now,
                        ]);
                    }
                    $newPrices[$productId] = ['discount' => $discPct];
                    break;
            }

            $updated++;
        }

        // Fetch refreshed price ranges for all updated products
        $refreshed = DB::table('product_variations')
            ->whereIn('product_id', $ownedIds)
            ->select('product_id', DB::raw('MIN(price) as min_price'), DB::raw('MAX(price) as max_price'))
            ->groupBy('product_id')
            ->get();

        $prices = [];
        foreach ($refreshed as $r) {
            $prices[$r->product_id] = [
                'min'      => (float) $r->min_price,
                'max'      => (float) $r->max_price,
                'discount' => $newPrices[$r->product_id]['discount'] ?? 0,
            ];
        }

        return response()->json(['success' => true, 'updated' => $updated, 'prices' => $prices]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $categories   = DB::table('categories2')->orderBy('parent')->orderBy('name')->get();
        $brands       = DB::table('brands')->orderBy('name')->get();
        $dbVariations = collect();
        $hasVariations = false;

        $isDebug = config('app.debug');

        return view('web.vendor.products.create', compact(
            'categories', 'brands', 'dbVariations', 'hasVariations', 'isDebug'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE (create)
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $vendor        = $this->vendor();
        $hasVariations = $request->boolean('has_variations');

        $request->validate($this->validationRules($hasVariations));

        $slug        = $this->uniqueSlug(Str::slug($this->sanitizeProductText($request->input('name'))));
        $imagesJson  = $this->buildImagesJson($request);
        $relatedJson = $this->buildRelatedJson($request);

        $now = now();
        [$variations, $totalStock] = $this->buildVariations($request, $hasVariations);

        $translations       = $this->buildTranslations($request);
        $availableLocales   = $this->buildLang($translations);
        $tags               = $this->buildTags($request);
        $attributes         = $this->buildAttributes($request);
        $whatsapp           = $this->buildWhatsapp($request);
        $searchText         = $this->buildSearchText($request, $translations, $attributes, $tags);

        $regularPrice       = (float) $request->input('regular_price', 0);
        $discountPct        = (float) $request->input('discount_percentage', 0);
        $salePrice          = $discountPct > 0
            ? round($regularPrice * (1 - $discountPct / 100), 2)
            : ($request->filled('sale_price') ? (float) $request->input('sale_price') : $regularPrice);

        $unitType   = $request->input('unit', 'piece');
        $unitAmount = (float) $request->input('unit_amount', 1);
        $unitJson   = json_encode([$unitType => $unitAmount]);

        $productType = $request->input('product_type', 'physical');

        $productId = DB::table('products_data')->insertGetId([
            'name'                 => $this->sanitizeProductText($request->input('name')),
            'slug'                 => $slug,
            'permalink'            => $slug,
            'status'               => $request->input('status'),
            'short_description'    => $this->sanitizeProductText($request->input('short_description', '')),
            'description'          => $this->sanitizeProductText($request->input('description', '')),
            'sku'                  => $this->sanitizeProductText($request->input('sku', '')),
            'unit'                 => $unitJson,
            'brand_id'             => $request->input('brand_id') ?: '',
            'vendor_id'            => $vendor->id,
            'images'               => $imagesJson,
            'related_ids'          => $relatedJson,
            'button_mode'          => in_array($request->input('button_mode'), ['both','cart_only','details_only']) ? $request->input('button_mode') : 'both',
            'acceptance_status'    => 'pending',
            'stock_quantity'       => $totalStock,
            'stock_status'         => $totalStock > 0 ? 'instock' : 'outofstock',
            'manage_stock'         => true,
            'purchasable'          => true,
            'has_variations'       => $hasVariations,
            'has_options'          => $hasVariations || count($attributes) > 0,
            'reviews_allowed'      => true,
            'average_rating'       => '0',
            'rating_count'         => 0,
            'total_sales'          => 0,
            'discount_percentage'  => (string) $discountPct,
            'translations'         => json_encode($translations),
            'lang'                 => json_encode($availableLocales),
            'tags'                 => json_encode($tags),
            'attributes'           => json_encode($attributes),
            'whatsapp'             => json_encode($whatsapp),
            'search_text'          => $searchText,
            'product_type'         => $productType,
            'type'                 => $productType,
            'minimum_order_qty'    => (int) $request->input('minimum_order_qty', 1),
            'max_orders_per_person'=> (int) $request->input('max_orders_per_person', 0),
            'shipping_required'    => $productType === 'physical',
            'virtual'              => $productType === 'digital',
            'on_sale'              => $discountPct > 0 || ($request->filled('sale_price') && (float) $request->input('sale_price') < $regularPrice),
            'featured'             => false,
            'catalog_visibility'   => 'visible',
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        foreach (array_filter((array) $request->input('categories', [])) as $catId) {
            DB::table('product_category')->insert([
                'product_id'  => $productId,
                'category_id' => (int) $catId,
            ]);
        }

        $variationImageMap = $this->uploadVariationImages($request);

        foreach ($variations as $i => $v) {
            $color = $v['_color'] ?? null;
            $varImages = ($color && isset($variationImageMap[$color]))
                ? json_encode($variationImageMap[$color])
                : '[]';
            unset($v['_color']);

            DB::table('product_variations')->insert(array_merge($v, [
                'product_id'     => $productId,
                'main_variation' => $i === 0,
                'images'         => $varImages,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]));
        }

        return redirect()->route('vendor.products')
            ->with('success', 'Product "' . $request->input('name') . '" added. Pending admin approval.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW (detail view with per-section inline editing)
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $vendor  = $this->vendor();
        $product = DB::table('products_data')->where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (! $product) abort(404);

        $dbVariations = DB::table('product_variations')
            ->where('product_id', $id)
            ->orderByDesc('main_variation')->orderBy('id')->get()
            ->map(function ($v) {
                $v->attributes = is_string($v->attributes) ? (json_decode($v->attributes, true) ?? []) : (array) $v->attributes;
                $v->images     = is_string($v->images)     ? (json_decode($v->images,     true) ?? []) : (array) ($v->images ?? []);
                return $v;
            });

        $hasVariations = (bool) $product->has_variations || $dbVariations->count() > 1
            || ($dbVariations->count() === 1 && ! empty($dbVariations->first()->attributes));

        $variation    = $dbVariations->first();
        $categories   = DB::table('categories2')->orderBy('parent')->orderBy('name')->get();
        $brands       = DB::table('brands')->orderBy('name')->get();
        $selectedCats = DB::table('product_category')->where('product_id', $id)->pluck('category_id')->toArray();
        $relatedIds   = json_decode($product->related_ids ?? '[]', true) ?: [];
        $relatedProds = count($relatedIds)
            ? DB::table('products_data')->whereIn('id', $relatedIds)->select('id', 'name')->get()
            : collect();
        $images       = json_decode($product->images ?? '{}', true) ?: [];
        $translations = json_decode($product->translations ?? '[]', true) ?: [];
        $tags         = json_decode($product->tags ?? '[]', true) ?: [];
        $attributes   = json_decode($product->attributes ?? '[]', true) ?: [];
        $whatsapp     = json_decode($product->whatsapp ?? '{}', true) ?: [];
        $unit         = json_decode($product->unit ?? '{}', true) ?: [];
        $unitType     = $unit ? array_key_first($unit) : 'piece';
        $unitAmount   = $unit ? ($unit[$unitType] ?? 1) : 1;
        $whatsappData = $whatsapp['whatsapp'] ?? [];
        $imgBase      = \Illuminate\Support\Facades\Storage::url('');

        $allCoupons = DB::table('coupons')->where('status', 'publish')->orderBy('code')->get(['id', 'code', 'amount', 'discount_type']);
        $attachedCoupon = null;
        foreach (DB::table('coupons')->where('status', 'publish')->get(['id', 'code', 'amount', 'discount_type', 'product_ids']) as $c) {
            $cpids = json_decode($c->product_ids ?? '[]', true) ?? [];
            if (in_array($id, array_map('intval', $cpids))) { $attachedCoupon = $c; break; }
        }

        return view('web.vendor.products.show', compact(
            'product', 'variation', 'dbVariations', 'hasVariations',
            'categories', 'brands', 'selectedCats', 'relatedIds', 'relatedProds',
            'images', 'translations', 'tags', 'attributes', 'whatsappData',
            'unitType', 'unitAmount', 'imgBase',
            'allCoupons', 'attachedCoupon'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE SECTION (inline per-section save)
    // ─────────────────────────────────────────────────────────────────────
    public function updateSection(Request $request, int $id)
    {
        $vendor  = $this->vendor();
        $product = DB::table('products_data')->where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (! $product) abort(404);

        $section = $request->input('_section');
        $now     = now();

        switch ($section) {

            case 'basic':
                $request->validate([
                    'name'             => 'required|string|max:500',
                    'status'           => 'required|in:publish,draft',
                    'sku'              => 'nullable|string|max:100',
                    'brand_id'         => 'nullable|integer',
                    'product_type'     => 'nullable|in:physical,digital',
                    'unit'             => 'nullable|string|max:50',
                    'unit_amount'      => 'nullable|numeric|min:0.01',
                    'short_description'=> 'nullable|string|max:1000',
                    'description'      => 'nullable|string',
                ]);
                $unitType   = $request->input('unit', 'piece');
                $unitAmount = (float) $request->input('unit_amount', 1);
                $productType = $request->input('product_type', 'physical');
                DB::table('products_data')->where('id', $id)->update([
                    'name'              => $this->sanitizeProductText($request->input('name')),
                    'status'            => $request->input('status'),
                    'sku'               => $this->sanitizeProductText($request->input('sku', '')),
                    'brand_id'          => $request->input('brand_id') ?: '',
                    'product_type'      => $productType,
                    'type'              => $productType,
                    'unit'              => json_encode([$unitType => $unitAmount]),
                    'short_description' => $this->sanitizeProductText($request->input('short_description', '')),
                    'description'       => $this->sanitizeProductText($request->input('description', '')),
                    'shipping_required' => $productType === 'physical',
                    'virtual'           => $productType === 'digital',
                    'updated_at'        => $now,
                ]);
                break;

            case 'translations':
                $translations     = $this->buildTranslations($request);
                $availableLocales = $this->buildLang($translations);
                DB::table('products_data')->where('id', $id)->update([
                    'translations' => json_encode($translations),
                    'lang'         => json_encode($availableLocales),
                    'updated_at'   => $now,
                ]);
                break;

            case 'pricing':
                $request->validate([
                    'discount_percentage'   => 'nullable|numeric|min:0|max:80.99',
                    'minimum_order_qty'     => 'nullable|integer|min:1|max:100',
                    'max_orders_per_person' => 'nullable|integer|min:0|max:100',
                ]);
                $discountPct = (float) $request->input('discount_percentage', 0);
                DB::table('products_data')->where('id', $id)->update([
                    'discount_percentage'   => (string) $discountPct,
                    'on_sale'               => $discountPct > 0,
                    'minimum_order_qty'     => (int) $request->input('minimum_order_qty', 1),
                    'max_orders_per_person' => (int) $request->input('max_orders_per_person', 0),
                    'updated_at'            => $now,
                ]);
                // Always recalculate variation prices when discount changes.
                // When discount is 0, reset sale_price and price back to regular_price.
                $vars = DB::table('product_variations')->where('product_id', $id)->get();
                foreach ($vars as $v) {
                    if ($discountPct > 0) {
                        $newSale = round((float) $v->regular_price * (1 - $discountPct / 100), 2);
                    } else {
                        $newSale = (float) $v->regular_price; // remove discount
                    }
                    DB::table('product_variations')->where('id', $v->id)->update([
                        'sale_price' => $newSale,
                        'price'      => $newSale,
                        'updated_at' => $now,
                    ]);
                }
                break;

            case 'variations':
                $hasVariations = $request->boolean('has_variations');
                [$variations, $totalStock] = $this->buildVariations($request, $hasVariations);
                $variationImageMap = $this->uploadVariationImages($request);
                DB::table('product_variations')->where('product_id', $id)->delete();
                foreach ($variations as $i => $v) {
                    $color     = $v['_color'] ?? null;
                    $varImages = ($color && isset($variationImageMap[$color])) ? json_encode($variationImageMap[$color]) : '[]';
                    unset($v['_color']);
                    DB::table('product_variations')->insert(array_merge($v, [
                        'product_id' => $id, 'main_variation' => $i === 0,
                        'images' => $varImages, 'created_at' => $now, 'updated_at' => $now,
                    ]));
                }
                DB::table('products_data')->where('id', $id)->update([
                    'has_variations' => $hasVariations,
                    'stock_quantity' => $totalStock,
                    'stock_status'   => $totalStock > 0 ? 'instock' : 'outofstock',
                    'updated_at'     => $now,
                ]);
                break;

            case 'attributes':
                $attributes = $this->buildAttributes($request);
                DB::table('products_data')->where('id', $id)->update([
                    'attributes' => json_encode($attributes),
                    'updated_at' => $now,
                ]);
                break;

            case 'tags':
                $tags = $this->buildTags($request);
                DB::table('products_data')->where('id', $id)->update([
                    'tags'       => json_encode($tags),
                    'updated_at' => $now,
                ]);
                break;

            case 'categories':
                DB::table('product_category')->where('product_id', $id)->delete();
                foreach (array_filter((array) $request->input('categories', [])) as $catId) {
                    DB::table('product_category')->insert(['product_id' => $id, 'category_id' => (int) $catId]);
                }
                break;

            case 'images':
                $existingImages = json_decode($product->images ?? '{}', true) ?: [];

                // Delete thumbnail if requested
                if ($request->boolean('delete_thumbnail') && !empty($existingImages['thumbnail'])) {
                    Storage::disk('public')->delete($existingImages['thumbnail']);
                    unset($existingImages['thumbnail']);
                }

                // Delete individual other_images
                foreach ((array) $request->input('delete_other_images', []) as $delPath) {
                    Storage::disk('public')->delete($delPath);
                    $existingImages['other_images'] = array_values(
                        array_filter($existingImages['other_images'] ?? [], fn($p) => $p !== $delPath)
                    );
                }

                // Delete individual natural_images
                foreach ((array) $request->input('delete_natural_images', []) as $delPath) {
                    Storage::disk('public')->delete($delPath);
                    $existingImages['natural_images'] = array_values(
                        array_filter($existingImages['natural_images'] ?? [], fn($p) => $p !== $delPath)
                    );
                }

                $imagesJson = $this->buildImagesJson($request, $existingImages);
                DB::table('products_data')->where('id', $id)->update([
                    'images'     => $imagesJson,
                    'updated_at' => $now,
                ]);
                break;

            case 'var_images':
                $varId = (int) $request->input('variation_id');
                $variation = DB::table('product_variations')
                    ->where('id', $varId)
                    ->where('product_id', $id)
                    ->first();
                if (! $variation) abort(404);

                $varImgs = is_string($variation->images)
                    ? (json_decode($variation->images, true) ?? [])
                    : (array) ($variation->images ?? []);

                foreach ((array) $request->input('delete_images', []) as $delPath) {
                    Storage::disk('public')->delete($delPath);
                    $varImgs = array_values(array_filter($varImgs, fn($p) => $p !== $delPath));
                }

                if ($request->hasFile('new_images')) {
                    $varAttrs  = is_string($variation->attributes)
                        ? (json_decode($variation->attributes, true) ?? [])
                        : (array) ($variation->attributes ?? []);
                    $colorName = $varAttrs['Color'] ?? 'default';
                    $folder    = 'products/variations/' . Str::slug($colorName);
                    Storage::disk('public')->makeDirectory($folder);
                    foreach ($request->file('new_images') as $file) {
                        if ($file && $file->isValid()) {
                            $varImgs[] = $this->storeImage($file, $folder, 1200, 1200);
                        }
                    }
                }

                DB::table('product_variations')->where('id', $varId)->update([
                    'images'     => json_encode(array_values($varImgs)),
                    'updated_at' => $now,
                ]);
                break;

            case 'whatsapp':
                $whatsapp = $this->buildWhatsapp($request);
                DB::table('products_data')->where('id', $id)->update([
                    'whatsapp'   => json_encode($whatsapp),
                    'updated_at' => $now,
                ]);
                break;

            case 'related':
                $relatedJson = $this->buildRelatedJson($request);
                DB::table('products_data')->where('id', $id)->update([
                    'related_ids' => $relatedJson,
                    'updated_at'  => $now,
                ]);
                break;

            case 'coupon':
                $couponId = (int) $request->input('coupon_id');
                // Remove this product from all coupons first
                foreach (DB::table('coupons')->get(['id', 'product_ids']) as $c) {
                    $cpids = json_decode($c->product_ids ?? '[]', true) ?? [];
                    $cpids = array_values(array_filter(array_map('intval', $cpids), fn($p) => $p !== $id));
                    DB::table('coupons')->where('id', $c->id)->update(['product_ids' => json_encode($cpids)]);
                }
                // Add to the selected coupon
                if ($couponId > 0) {
                    $coupon = DB::table('coupons')->where('id', $couponId)->first();
                    if ($coupon) {
                        $cpids = json_decode($coupon->product_ids ?? '[]', true) ?? [];
                        $cpids = array_values(array_map('intval', $cpids));
                        if (!in_array($id, $cpids)) $cpids[] = $id;
                        DB::table('coupons')->where('id', $couponId)->update(['product_ids' => json_encode($cpids)]);
                    }
                }
                break;
        }

        return redirect()->route('vendor.products.show', $id)
            ->with('success', 'Section updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT FORM
    // ─────────────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $vendor  = $this->vendor();
        $product = DB::table('products_data')->where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (! $product) abort(404);

        $dbVariations = DB::table('product_variations')
            ->where('product_id', $id)
            ->orderByDesc('main_variation')
            ->orderBy('id')
            ->get()
            ->map(function ($v) {
                $v->attributes = is_string($v->attributes)
                    ? (json_decode($v->attributes, true) ?? [])
                    : (array) $v->attributes;
                $v->images = is_string($v->images)
                    ? (json_decode($v->images, true) ?? [])
                    : (array) ($v->images ?? []);
                return $v;
            });

        $hasVariations = (bool) $product->has_variations
            || $dbVariations->count() > 1
            || ($dbVariations->count() === 1 && ! empty($dbVariations->first()->attributes));

        $variation    = $dbVariations->first();
        $categories   = DB::table('categories2')->orderBy('parent')->orderBy('name')->get();
        $brands       = DB::table('brands')->orderBy('name')->get();
        $selectedCats = DB::table('product_category')->where('product_id', $id)->pluck('category_id')->toArray();
        $relatedIds   = json_decode($product->related_ids ?? '[]', true) ?: [];
        $relatedProds = count($relatedIds)
            ? DB::table('products_data')->whereIn('id', $relatedIds)->select('id', 'name')->get()
            : collect();
        $images       = json_decode($product->images ?? '{}', true) ?: [];
        $translations = json_decode($product->translations ?? '[]', true) ?: [];
        $tags         = json_decode($product->tags ?? '[]', true) ?: [];
        $attributes   = json_decode($product->attributes ?? '[]', true) ?: [];
        $whatsapp     = json_decode($product->whatsapp ?? '{}', true) ?: [];
        $unit         = json_decode($product->unit ?? '{}', true) ?: [];
        $unitType     = $unit ? array_key_first($unit) : 'piece';
        $unitAmount   = $unit ? ($unit[$unitType] ?? 1) : 1;
        $whatsappData = $whatsapp['whatsapp'] ?? [];

        return view('web.vendor.products.create', compact(
            'product', 'variation', 'dbVariations', 'hasVariations',
            'categories', 'brands',
            'selectedCats', 'relatedIds', 'relatedProds', 'images',
            'translations', 'tags', 'attributes', 'whatsappData',
            'unitType', 'unitAmount'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $vendor  = $this->vendor();
        $product = DB::table('products_data')->where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (! $product) abort(404);

        $hasVariations = $request->boolean('has_variations');
        $request->validate($this->validationRules($hasVariations));

        $existingImages = json_decode($product->images ?? '{}', true) ?: [];
        $imagesJson  = $this->buildImagesJson($request, $existingImages);
        $relatedJson = $this->buildRelatedJson($request);

        $now = now();
        [$variations, $totalStock] = $this->buildVariations($request, $hasVariations);

        $translations       = $this->buildTranslations($request);
        $availableLocales   = $this->buildLang($translations);
        $tags               = $this->buildTags($request);
        $attributes         = $this->buildAttributes($request);
        $whatsapp           = $this->buildWhatsapp($request);
        $searchText         = $this->buildSearchText($request, $translations, $attributes, $tags);

        $regularPrice       = (float) $request->input('regular_price', 0);
        $discountPct        = (float) $request->input('discount_percentage', 0);
        $salePrice          = $discountPct > 0
            ? round($regularPrice * (1 - $discountPct / 100), 2)
            : ($request->filled('sale_price') ? (float) $request->input('sale_price') : $regularPrice);

        $unitType   = $request->input('unit', 'piece');
        $unitAmount = (float) $request->input('unit_amount', 1);
        $unitJson   = json_encode([$unitType => $unitAmount]);

        $productType = $request->input('product_type', 'physical');

        DB::table('products_data')->where('id', $id)->update([
            'name'                 => $this->sanitizeProductText($request->input('name')),
            'short_description'    => $this->sanitizeProductText($request->input('short_description', '')),
            'description'          => $this->sanitizeProductText($request->input('description', '')),
            'sku'                  => $this->sanitizeProductText($request->input('sku', '')),
            'unit'                 => $unitJson,
            'brand_id'             => $request->input('brand_id') ?: '',
            'status'               => $request->input('status'),
            'images'               => $imagesJson,
            'related_ids'          => $relatedJson,
            'stock_quantity'       => $totalStock,
            'stock_status'         => $totalStock > 0 ? 'instock' : 'outofstock',
            'has_variations'       => $hasVariations,
            'has_options'          => $hasVariations || count($attributes) > 0,
            'discount_percentage'  => (string) $discountPct,
            'translations'         => json_encode($translations),
            'lang'                 => json_encode($availableLocales),
            'tags'                 => json_encode($tags),
            'attributes'           => json_encode($attributes),
            'whatsapp'             => json_encode($whatsapp),
            'search_text'          => $searchText,
            'product_type'         => $productType,
            'type'                 => $productType,
            'minimum_order_qty'    => (int) $request->input('minimum_order_qty', 1),
            'max_orders_per_person'=> (int) $request->input('max_orders_per_person', 0),
            'shipping_required'    => $productType === 'physical',
            'virtual'              => $productType === 'digital',
            'on_sale'              => $discountPct > 0,
            'button_mode'          => in_array($request->input('button_mode'), ['both','cart_only','details_only']) ? $request->input('button_mode') : 'both',
            'updated_at'           => $now,
        ]);

        DB::table('product_category')->where('product_id', $id)->delete();
        foreach (array_filter((array) $request->input('categories', [])) as $catId) {
            DB::table('product_category')->insert(['product_id' => $id, 'category_id' => (int) $catId]);
        }

        $variationImageMap = $this->uploadVariationImages($request);

        DB::table('product_variations')->where('product_id', $id)->delete();
        foreach ($variations as $i => $v) {
            $color = $v['_color'] ?? null;
            $varImages = ($color && isset($variationImageMap[$color]))
                ? json_encode($variationImageMap[$color])
                : '[]';
            unset($v['_color']);

            DB::table('product_variations')->insert(array_merge($v, [
                'product_id'     => $id,
                'main_variation' => $i === 0,
                'images'         => $varImages,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]));
        }

        return redirect()->route('vendor.products')
            ->with('success', 'Product updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $vendor  = $this->vendor();
        $product = DB::table('products_data')->where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (! $product) abort(404);

        DB::table('product_variations')->where('product_id', $id)->delete();
        DB::table('product_category')->where('product_id', $id)->delete();
        DB::table('products_data')->where('id', $id)->delete();

        return back()->with('success', 'Product deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SEARCH JSON (for related products autocomplete)
    // ─────────────────────────────────────────────────────────────────────
    public function searchJson(Request $request)
    {
        $vendor  = $this->vendor();
        $q       = $request->input('q', '');
        $exclude = (int) $request->input('exclude', 0);

        $results = DB::table('products_data')
            ->where('vendor_id', $vendor->id)
            ->where('name', 'ilike', '%' . $q . '%')
            ->when($exclude, fn($qb) => $qb->where('id', '!=', $exclude))
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function validationRules(bool $hasVariations): array
    {
        $base = [
            'name'                  => 'required|string|max:500',
            'status'                => 'required|in:publish,draft',
            'short_description'     => 'nullable|string|max:1000',
            'description'           => 'nullable|string',
            'sku'                   => 'nullable|string|max:100',
            'unit'                  => 'nullable|string|max:50',
            'unit_amount'           => 'nullable|numeric|min:0.01|max:999999',
            'brand_id'              => 'nullable|integer',
            'categories'            => 'nullable|array',
            'categories.*'          => 'integer',
            'related_ids'           => 'nullable|string',
            'product_type'          => 'nullable|in:physical,digital',
            'discount_percentage'   => 'nullable|numeric|min:0|max:80.99',
            'minimum_order_qty'     => 'nullable|integer|min:1|max:100',
            'max_orders_per_person' => 'nullable|integer|min:0|max:100',
            'thumbnail'             => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'other_images.*'        => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'natural_images.*'      => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'whatsapp_available'    => 'nullable|boolean',
            'whatsapp_number'       => 'nullable|string|max:20',
            // Translations
            'translations'          => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|in:ar,fr,de,es,it',
            'translations.*.name'   => 'required_with:translations|string|max:500',
            'translations.*.description' => 'nullable|string',
            // Attributes
            'prod_attributes'             => 'nullable|array',
            'prod_attributes.*.name'      => 'required_with:prod_attributes|string|max:100',
            'prod_attributes.*.values'    => 'required_with:prod_attributes|string|max:500',
            // Tags
            'tags_input'            => 'nullable|string|max:500',
        ];

        if (! $hasVariations) {
            $base['regular_price']  = 'required|numeric|min:0';
            $base['sale_price']     = 'nullable|numeric|min:0';
            $base['stock_quantity'] = 'required|integer|min:0';
        } else {
            $base['colors']                          = 'required|array|min:1';
            $base['colors.*.name']                   = 'required|string|max:50';
            $base['colors.*.main_variation']         = 'nullable|boolean';
            $base['colors.*.sizes']                  = 'nullable|array';
            $base['colors.*.sizes.*']                = 'string|max:25';
            $base['colors.*.stock']                  = 'nullable|array';
            $base['colors.*.stock.*']                = 'integer|min:0';
            $base['colors.*.price_map']              = 'nullable|array';
            $base['colors.*.price_map.*']            = 'nullable|numeric|min:0';
            $base['colors.*.sale_price_map']         = 'nullable|array';
            $base['colors.*.sale_price_map.*']       = 'nullable|numeric|min:0';
            $base['colors.*.regular_price']          = 'nullable|numeric|min:0';
            $base['colors.*.sale_price']             = 'nullable|numeric|min:0';
        }

        return $base;
    }

    /**
     * Build variations from the Color+Sizes+price_map structure.
     * Returns [array of DB rows, total stock].
     *
     * Price priority (highest to lowest):
     *   1. Global discount_percentage (applies to all variations)
     *   2. Per-size sale_price_map[size] (if < regular price)
     *   3. Color-level sale_price override (if < regular price)
     *   4. No discount — effective price = regular price
     */
    private function buildVariations(Request $request, bool $hasVariations): array
    {
        $rows       = [];
        $totalStock = 0;

        if (! $hasVariations) {
            $regularPrice   = (float) $request->input('regular_price', 0);
            $discountPct    = (float) $request->input('discount_percentage', 0);
            $salePriceInput = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;

            if ($discountPct > 0) {
                $salePrice = round($regularPrice * (1 - $discountPct / 100), 2);
            } elseif ($salePriceInput !== null && $salePriceInput > 0 && $salePriceInput < $regularPrice) {
                $salePrice = round($salePriceInput, 2);
            } else {
                $salePrice = $regularPrice;
            }

            $price      = $salePrice < $regularPrice ? $salePrice : $regularPrice;
            $stock      = (int) $request->input('stock_quantity', 0);
            $totalStock = $stock;

            $rows[] = [
                '_color'         => null,
                'attributes'     => '{}',
                'price'          => $price,
                'regular_price'  => $regularPrice,
                'sale_price'     => $salePrice,
                'stock_quantity' => $stock,
                'stock_status'   => $request->input('stock_status', 'instock'),
                'status'         => $request->input('variation_status', 'publish'),
            ];

            return [$rows, $totalStock];
        }

        // Variable product: Color + Sizes structure
        $colorsInput = array_values((array) $request->input('colors', []));
        $discountPct = (float) $request->input('discount_percentage', 0);

        foreach ($colorsInput as $colorData) {
            $colorName = ucfirst($this->sanitizeProductText($colorData['name'] ?? ''));
            if (! $colorName) continue;

            $priceMap          = (array) ($colorData['price_map'] ?? []);
            $salePriceMapInput = (array) ($colorData['sale_price_map'] ?? []);
            $sizesRaw          = array_filter(array_map(fn ($size) => $this->sanitizeProductText($size), (array) ($colorData['sizes'] ?? [])));
            // Fall back to price_map keys if no sizes were submitted.
            $sizes             = count($sizesRaw) > 0
                ? array_values($sizesRaw)
                : array_values(array_filter(array_map(fn ($size) => $this->sanitizeProductText($size), array_keys($priceMap))));
            $stockMap          = (array) ($colorData['stock'] ?? []);
            $fallbackRegular   = (float) ($colorData['regular_price'] ?? 0);
            $colorSaleOverride = isset($colorData['sale_price']) && $colorData['sale_price'] !== '' && (float)$colorData['sale_price'] > 0
                ? (float) $colorData['sale_price']
                : null;

            foreach ($sizes as $size) {
                if (! $size) continue;

                $regularPriceForSize = isset($priceMap[$size]) && $priceMap[$size] !== ''
                    ? (float) $priceMap[$size]
                    : $fallbackRegular;

                // Priority: global discount % > per-size sale price > color-level sale price > no discount
                if ($discountPct > 0) {
                    $salePriceForSize = round($regularPriceForSize * (1 - $discountPct / 100), 2);
                } elseif (
                    isset($salePriceMapInput[$size]) &&
                    $salePriceMapInput[$size] !== '' &&
                    (float) $salePriceMapInput[$size] > 0 &&
                    (float) $salePriceMapInput[$size] < $regularPriceForSize
                ) {
                    $salePriceForSize = round((float) $salePriceMapInput[$size], 2);
                } elseif ($colorSaleOverride !== null && $colorSaleOverride < $regularPriceForSize) {
                    $salePriceForSize = round($colorSaleOverride, 2);
                } else {
                    $salePriceForSize = $regularPriceForSize; // no discount
                }

                $price        = $salePriceForSize < $regularPriceForSize ? $salePriceForSize : $regularPriceForSize;
                $stockForSize    = isset($stockMap[$size]) ? max(0, (int) $stockMap[$size]) : 0;
                $totalStock     += $stockForSize;
                $stockStatusMap  = (array) ($colorData['stock_status_map'] ?? []);
                $statusMap       = (array) ($colorData['status_map'] ?? []);

                $rows[] = [
                    '_color'         => $colorName,
                    'attributes'     => json_encode(['Color' => $colorName, 'Size' => $size]),
                    'price'          => $price,
                    'regular_price'  => $regularPriceForSize,
                    'sale_price'     => $salePriceForSize,
                    'stock_quantity' => $stockForSize,
                    'stock_status'   => $stockStatusMap[$size] ?? 'instock',
                    'status'         => $statusMap[$size] ?? 'publish',
                ];
            }
        }

        return [$rows, $totalStock];
    }

    private function buildTranslations(Request $request): array
    {
        $raw = (array) $request->input('translations', []);
        $result = [];
        foreach ($raw as $tr) {
            $locale = strtolower(trim($tr['locale'] ?? ''));
            $name   = $this->sanitizeProductText($tr['name'] ?? '');
            if (! $locale || ! $name) continue;
            $result[] = [
                'locale'      => $locale,
                'name'        => $name,
                'description' => $this->sanitizeProductText($tr['description'] ?? ''),
            ];
        }
        return $result;
    }

    private function buildLang(array $translations): array
    {
        $locales = ['en'];
        foreach ($translations as $tr) {
            $locale = $tr['locale'] ?? '';
            if ($locale && ! in_array($locale, $locales)) {
                $locales[] = $locale;
            }
        }
        return $locales;
    }

    private function buildTags(Request $request): array
    {
        $raw = $this->sanitizeProductText($request->input('tags_input', ''));
        if (! $raw) return [];
        return array_values(array_filter(array_map(fn ($tag) => $this->sanitizeProductText($tag), explode(',', $raw))));
    }

    private function buildAttributes(Request $request): array
    {
        $raw = (array) $request->input('prod_attributes', []);
        $result = [];
        foreach ($raw as $attr) {
            $name   = $this->sanitizeProductText($attr['name'] ?? '');
            $values = $this->sanitizeProductText($attr['values'] ?? '');
            if (! $name || ! $values) continue;
            $result[] = [
                'name'   => $name,
                'values' => array_values(array_filter(array_map(fn ($value) => $this->sanitizeProductText($value), explode(',', $values)))),
            ];
        }
        return $result;
    }

    private function buildWhatsapp(Request $request): array
    {
        $available = $request->boolean('whatsapp_available');
        $number    = $this->sanitizeProductText($request->input('whatsapp_number', ''));
        return [
            'whatsapp' => [
                'available' => $available,
                'number'    => $available ? $number : null,
            ],
        ];
    }

    private function buildSearchText(Request $request, array $translations, array $attributes, array $tags): string
    {
        $parts = [];
        $parts[] = $this->sanitizeProductText($request->input('name', ''));
        $parts[] = $this->sanitizeProductText($request->input('description', ''));
        $parts[] = $this->sanitizeProductText($request->input('short_description', ''));
        $parts[] = implode(' ', $tags);

        foreach ($translations as $tr) {
            $parts[] = $tr['name'] ?? '';
            $parts[] = $tr['description'] ?? '';
        }

        foreach ($attributes as $attr) {
            $parts[] = strtolower($attr['name'] ?? '');
            foreach ((array) ($attr['values'] ?? []) as $val) {
                $parts[] = strtolower(trim($val));
            }
        }

        // Add color/size from variations
        foreach ((array) $request->input('colors', []) as $color) {
            $parts[] = strtolower($this->sanitizeProductText($color['name'] ?? ''));
            foreach ((array) ($color['sizes'] ?? []) as $size) {
                $parts[] = strtolower($this->sanitizeProductText($size));
            }
        }

        $text = implode(' ', array_filter($parts));
        $text = preg_replace('/\s+/', ' ', $text);
        return strtolower(trim($text));
    }

    /**
     * Product fields are plain text in the current product forms. Removing markup at the
     * persistence boundary prevents stored markup from reaching future views or JS consumers.
     */
    private function sanitizeProductText(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }

    private function buildRelatedJson(Request $request): string
    {
        $raw = $request->input('related_ids', '');
        $arr = array_filter(array_map('intval', explode(',', $raw)));
        return count($arr) ? json_encode(array_values($arr)) : '[]';
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'product';
        if (! DB::table('products_data')->where('slug', $slug)->exists()) return $slug;
        $i = 2;
        while (DB::table('products_data')->where('slug', $slug . '-' . $i)->exists()) $i++;
        return $slug . '-' . $i;
    }

    /**
     * Upload variation images per color.
     * Files come in as: variation_images[{colorIdx}][] => files
     * Color names are resolved from: colors[{colorIdx}][name]
     * Returns map keyed by color name (ucfirst).
     */
    private function uploadVariationImages(Request $request): array
    {
        $map      = [];
        $allFiles = $request->allFiles();

        if (! isset($allFiles['variation_images']) || ! is_array($allFiles['variation_images'])) {
            return $map;
        }

        // Build index → color name lookup from the submitted colors array
        $colorsByIndex = [];
        foreach ((array) $request->input('colors', []) as $idx => $colorData) {
            $name = ucfirst($this->sanitizeProductText($colorData['name'] ?? ''));
            if ($name) $colorsByIndex[(string) $idx] = $name;
        }

        foreach ($allFiles['variation_images'] as $idx => $files) {
            // Resolve color name either from the index lookup or use idx directly as name
            $colorName  = $colorsByIndex[(string) $idx] ?? ucfirst(trim((string) $idx));
            if (! $colorName) continue;

            $safeFolder = 'products/variations/' . Str::slug($colorName);
            Storage::disk('public')->makeDirectory($safeFolder);

            $paths = [];
            foreach ((array) $files as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $this->storeImage($file, $safeFolder, 1200, 1200);
                }
            }
            if ($paths) $map[$colorName] = $paths;
        }

        return $map;
    }

    private function buildImagesJson(Request $request, array $existing = []): string
    {
        $images = $existing;

        if ($request->hasFile('thumbnail')) {
            if (! empty($images['thumbnail'])) {
                Storage::disk('public')->delete($images['thumbnail']);
            }
            $images['thumbnail'] = $this->storeImage($request->file('thumbnail'), 'products/thumbnails', 800, 800);
        }

        if ($request->hasFile('other_images')) {
            if (! isset($images['other_images'])) $images['other_images'] = [];
            foreach ($request->file('other_images') as $file) {
                if ($file && $file->isValid()) {
                    $images['other_images'][] = $this->storeImage($file, 'products/other_images', 1200, 1200);
                }
            }
        }

        if ($request->hasFile('natural_images')) {
            if (! isset($images['natural_images'])) $images['natural_images'] = [];
            foreach ($request->file('natural_images') as $file) {
                if ($file && $file->isValid()) {
                    $images['natural_images'][] = $this->storeImage($file, 'products/natural_images', 1200, 1200);
                }
            }
        }

        return json_encode($images);
    }

    private function storeImage($file, string $dir, int $maxW, int $maxH): string
    {
        $name = Str::random(40) . '.jpg';
        $path = $dir . '/' . $name;

        try {
            $manager = new ImageManager(new Driver());
            $img     = $manager->read($file->getRealPath());
            if ($img->width() > $maxW || $img->height() > $maxH) {
                $img->scaleDown($maxW, $maxH);
            }
            Storage::disk('public')->put($path, $img->toJpeg(85)->toString());
        } catch (\Throwable $e) {
            Storage::disk('public')->putFileAs($dir, $file, $name);
        }

        return $path;
    }
}
