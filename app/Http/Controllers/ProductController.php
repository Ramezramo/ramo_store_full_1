<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHandlerRam;
use App\Models\Product;
use App\Models\ProductVariation;
// use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;   // <-- GD driver (safe)
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

// use Intervention\Image\Facades\Image;
// use Intervention\Image\Image as InterventionImage;
// use Nette\Utils\Image as UtilsImage;

class ProductController extends Controller
{
    protected string $image_link;

    public function __construct()
    {
        $this->image_link = AppConstants::DOMAIN.AppConstants::IMAGE_PATH;
    }

    private function successResponse($data, $message = '', $code = 200)
    {
        return ResponseHandlerRam::success(
            data: $data,
            message: $message,
            statusCode: $code
        );
    }

    private function failureResponse($message, $code = 400, $forceViewMessageDetails = false)
    {
        return ResponseHandlerRam::error(
            forceViewMessageDetails: $forceViewMessageDetails,
            message: $message,
            statusCode: $code
        );
    }

    private function validatrionErrorResponse($errors, $code = 422)
    {
        return ResponseHandlerRam::validationError(
            errors: $errors,
            message: 'Validation failed',
            statusCode: $code
        );
    }

    public function addNewProduct(Request $request)
    {
        // === 1. Force GD Driver (ImageTragick protection) ===
        Config::set('image.driver', 'gd');
        if (! extension_loaded('gd')) {
            return $this->failureResponse(
                message: 'Image processing is temporarily unavailable.',
                code: Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // === 2. Parse JSON fields safely (before validation) ===
        $jsonFields = ['categories', 'translations', 'attributes', 'tags', 'variations'];
        $input = $request->all();

        foreach ($jsonFields as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $decoded = json_decode($input[$field], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $input[$field] = $decoded;
                } else {
                    unset($input[$field]);
                }
            }
        }

        // === 3. Strict Validation (with variations & per-size pricing) ===
        $validator = Validator::make($input, [
            // ──────── MAIN PRODUCT ────────
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            // 'name_ar' => 'required|string|max:255',
            // 'description_ar' => 'required|string|max:5000',
            'main_variation' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'categories' => 'required|array|min:1|max:50',
            'categories.*' => 'exists:categories2,id',
            'stock_quantity' => 'required|integer|min:1|max:999999',
            'whatsapp_number_available' => 'required|boolean',
            'whatsapp_number' => ['required_if:whatsapp_number_available,true', 'regex:/^(\+?20|0)?1[0125][0-9]{8}$/'],
            'unit' => 'required|string|in:piece,kg,g,liter,ml,meter,cm,box,pack,set',
            'unit_amount' => 'required|numeric|min:0.01|max:999999',
            'product_type' => 'required|string|in:physical,digital',
            'regular_price' => 'required|numeric|min:0.01|max:999999',

            'discount_percentage' => 'nullable|numeric|min:0|max:80.99',

            'thumbnailImage' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120',
            'otherImages' => 'required|array|max:10',
            'otherImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
            'naturalImages' => 'nullable|array|max:10',
            'naturalImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
            'translations' => 'nullable|array|max:10',
            'translations.*.locale' => 'required_with:translations|string|in:ar,en,fr,de,es,it',
            'translations.*.name' => 'required_with:translations|string|max:255',
            'translations.*.description' => 'required_with:translations|string|max:5000',
            'attributes' => 'nullable|array|max:100',
            'attributes.*.name' => 'required_with:attributes|string|max:100',
            'attributes.*.values' => 'required_with:attributes|array|max:20',
            'attributes.*.values.*' => 'string|max:100',
            'tags' => 'nullable|array|max:50',
            'tags.*' => 'string|max:50',
            'max_orders_per_person' => 'nullable|integer|min:1|max:100',
            'minimum_order_qty' => 'nullable|integer|min:1|max:100',

            'variations' => 'required|array|min:1|max:100',

            // Color
            'variations.*.color' => 'required_with:variations|string|max:50',

            // Sizes — NO distinct here! (this was the main bug)
            'variations.*.sizes' => 'required_with:variations|array|min:1|max:30',
            'variations.*.main_variation' => 'required_with:variations|boolean',
            'variations.*.sizes.*' => 'required_with:variations.*.sizes|string|max:25', // removed distinct

            // Stock per size (optional per size)
            'variations.*.stock_quantity' => 'nullable|array',
            'variations.*.stock_quantity.*' => 'integer|min:0|max:999999',

            // ──────── VARIATIONS PRICING – ONLY ONE OF THESE TWO IS REQUIRED ────────
            // Option A: Uniform price for the whole color
            'variations.*.regular_price' => 'required_without:variations.*.price_map|nullable|numeric|min:0.01|max:999999',
            'variations.*.sale_price' => 'nullable|numeric|min:0.01|max:999999|lte:variations.*.regular_price',

            // Option B: Per-size pricing → regular_price is NOT required
            'variations.*.price_map' => 'required_without:variations.*.regular_price|nullable|array',
            'variations.*.price_map.*' => 'numeric|min:0.01|max:999999',

        ], [
            'whatsapp_number.regex' => 'Please enter a valid Egyptian WhatsApp number (e.g. 01012345678)',
            'sale_price.lte' => 'Sale price must be less than or equal to regular price',
            'thumbnailImage.max' => 'Thumbnail image must not exceed 5MB',
            'otherImages.max' => 'You can upload up to 10 other images.',
            'naturalImages.max' => 'You can upload up to 10 natural images.',
            'variationImages.*.*.max' => 'Variation image must not exceed 5MB',
        ]);

        if ($validator->fails()) {
            return $this->validatrionErrorResponse($validator->errors());
        }
        // === CALL THE FUNCTION ===
        $mainVariationError = $this->validateMainVariation($input);
        if ($mainVariationError) {
            return $mainVariationError; // already a proper failure response
        }

        // === 4. Use Validated & Sanitized Data Only ===
        $validated = $validator->validated();
        $sanitized = $this->sanitizeRecursive($validated);

        $cleanName = $sanitized['name'] ?? '';
        $cleanDescription = $sanitized['description'] ?? '';
        $tagsSanitaized = $sanitized['tags'] ?? [];
        // $mainVariation = $sanitized['main_variation'] ?? [];

        // === 5. Process Translations & Determine Locales ===
        $translations = $sanitized['translations'] ?? [];
        $availableLocales = ['en'];
        $primaryLocale = 'en';

        $searchParts = [];

        // 1. Base English content
        $searchParts[] = $cleanName;
        $searchParts[] = $cleanDescription;
        $searchParts[] = implode(' ', $tagsSanitaized);

        // 2. Translations + Locale Detection
        foreach ($translations as $tr) {
            $locale = strtolower(trim($tr['locale'] ?? ''));
            $name = trim($tr['name'] ?? '');
            $description = trim($tr['description'] ?? '');

            if (! in_array($locale, ['ar', 'en', 'fr', 'de', 'es', 'it'])) {
                continue;
            }

            // Update available locales & primary locale logic
            if (! in_array($locale, $availableLocales)) {
                $availableLocales[] = $locale;
                if ($primaryLocale === 'en' && $locale !== 'en') {
                    $primaryLocale = $locale;
                }
            }

            // Add translated content if not empty
            if ($name !== '') {
                $searchParts[] = $name;
            }
            if ($description !== '') {
                $searchParts[] = $description;
            }
        }

        foreach (($sanitized['attributes'] ?? []) as $attr) {
            $attrName = Str::lower(trim($attr['name'] ?? ''));
            if ($attrName === '') {
                continue;
            }

            $searchParts[] = $attrName; // e.g. "size", "color"

            foreach (($attr['values'] ?? []) as $value) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }

                $valLower = Str::lower($value);

                $searchParts[] = $valLower;           // "xxl"
                $searchParts[] = "$attrName:$valLower"; // "size:xxl"
                $searchParts[] = "$valLower $attrName"; // "xxl size"
            }
        }

        // === Final Search Text ===
        $searchText = implode(' ', array_filter($searchParts));
        $searchText = preg_replace('/\s+/', ' ', $searchText);
        $searchText = Str::lower(trim($searchText));

        // === Continue with image upload...
        $uploadedPaths = [];
        try {
            DB::beginTransaction();

            // ── Main product images ──
            $thumbnailPath = $this->uploadSecureImage($request->file('thumbnailImage'), 'products/thumbnails');
            $uploadedPaths[] = $thumbnailPath;

            $otherImagesPaths = $this->uploadMultipleImages($request->file('otherImages'), 'products/other_images');
            $uploadedPaths = array_merge($uploadedPaths, $otherImagesPaths);

            $naturalImagesPaths = $this->uploadMultipleImages($request->file('naturalImages'), 'products/natural_images');
            $uploadedPaths = array_merge($uploadedPaths, $naturalImagesPaths);
            $variationImageMap = [];
            $allFiles = $request->allFiles();

            // Reconstruct nested variationImages[Color][index] => file
            if (isset($allFiles['variationImages']) && is_array($allFiles['variationImages'])) {
                Log::info('Processing variation images', $allFiles['variationImages']);

                foreach ($allFiles['variationImages'] as $color => $sizeFiles) {
                    $color = ucfirst(trim($color));
                    $safeColorFolder = $this->safeColorFolderName($color);
                    $folder = "products/variations/{$safeColorFolder}";

                    // Ensure directory exists
                    Storage::disk('public')->makeDirectory($folder);

                    if (is_array($sizeFiles)) {
                        $paths = $this->uploadMultipleImages($sizeFiles, $folder);
                        $variationImageMap[$color] = $paths;
                        $uploadedPaths = array_merge($uploadedPaths, $paths);
                    }
                }
            }
            // ── Build main product data ──
            $regularPrice = round($sanitized['regular_price']);
            $discount = round($sanitized['discount_percentage'] ?? 0, 2);
            $salePrice = $discount > 0 ? round($regularPrice * (1 - $discount / 100), 2) : $regularPrice;
            $finalPrice = $salePrice;

            // Smart WhatsApp handling
            $whatsappAvailable = (bool) ($sanitized['whatsapp_number_available'] ?? false);
            $whatsappNumber = $whatsappAvailable ? ($sanitized['whatsapp_number'] ?? null) : null;

            if (! $whatsappAvailable && ! empty($sanitized['whatsapp_number'])) {
                $whatsappNumber = $sanitized['whatsapp_number']; // keep number even if disabled
            }
            $goodmap = [
                'name' => $cleanName,
                'description' => $cleanDescription,
                'search_text' => $searchText,
                // 'description_ar' => $cleanDescriptionAr,
                'short_description' => Str::limit($cleanDescription, 160),
                'images' => [
                    'thumbnail' => $thumbnailPath,
                    'other_images' => $otherImagesPaths,
                    'natural_images' => $naturalImagesPaths,
                ],
                'brand_id' => round($validated['brand_id']),
                // 'main_variation' => $mainVariation,
                'categories' => $validated['categories'],
                'stock_quantity' => round($validated['stock_quantity']),
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'price' => $finalPrice,
                'discount_percentage' => $discount,
                'unit' => [$validated['unit'] => round($validated['unit_amount'])],
                'product_type' => $validated['product_type'],
                'type' => $validated['product_type'],
                'translations' => $translations,
                'discount_percentage' => round($validated['discount_percentage'] ?? 0, 2),
                'lang' => $availableLocales,
                'max_orders_per_person' => (int) ($validated['max_orders_per_person'] ?? 0),
                'minimum_order_qty' => (int) ($validated['minimum_order_qty'] ?? 1),
                'attributes' => $sanitized['attributes'] ?? [],
                'tags' => $tagsSanitaized,
                'slug' => $this->generateUniqueSlug($cleanName),
                'permalink' => url('/products/'.$this->generateUniqueSlug($cleanName)),
                'vendor_id' => $request->user()->id,
                'date_created' => now(),
                'date_modified' => now(),
                'status' => 'publish',
                'featured' => false,
                'catalog_visibility' => 'visible',
                'acceptance_status' => 'pending',
                'sku' => $this->generateUniqueSku(),
                'on_sale' => true,
                'purchasable' => true,
                'total_sales' => 0,
                'virtual' => in_array($validated['product_type'], ['digital']),
                'downloadable' => false,
                'manage_stock' => true,
                'backorders' => 'no',
                'backorders_allowed' => false,
                'backordered' => false,
                'dimensions' => ['length' => '', 'width' => '', 'height' => ''],
                'shipping_required' => $validated['product_type'] === 'physical',
                'shipping_taxable' => false,
                'shipping_class' => '',
                'sold_individually' => false,
                'reviews_allowed' => false,
                'average_rating' => '0.00',
                'rating_count' => 0,
                'upsell_ids' => [],
                'cross_sell_ids' => [],
                'parent_id' => 0,
                'purchase_note' => '',
                'default_attributes' => [],
                'grouped_products' => [],
                'menu_order' => 0,
                'related_ids' => [],
                'stock_status' => 'instock',
                'has_options' => ! empty($sanitized['attributes']) || ! empty($validated['variations']),
                'has_variations' => ! empty($validated['variations']),
                'is_purchased' => false,
                'is_wallet_product' => false,
                '_links' => [],
                'attributesData' => [],
                'date_created_gmt' => now()->utc(),
                'date_modified_gmt' => now()->utc(),
                'date_on_sale_from' => now(),
                'date_on_sale_from_gmt' => now()->utc(),
                'date_on_sale_to' => now(),
                'date_on_sale_to_gmt' => now()->utc(),
                'downloads' => [],
                'download_limit' => -1,
                'download_expiry' => -1,
                'external_url' => '',
                'button_text' => '',
                'low_stock_amount' => 0,
                'shipping_class_id' => 0,
                'global_unique_id' => '',
                'better_featured_image' => '',
                'whatsapp' => [
                    'whatsapp' => [
                        'available' => $whatsappAvailable,
                        'number' => $whatsappNumber,
                    ],
                ],
            ];

            // Meta data
            $goodmap['meta_data'] = [
                ['key' => '_mstore_video_url', 'value' => ''],
                ['key' => '_mstore_video_title', 'value' => $cleanName],
                ['key' => '_mstore_video_description', 'value' => $cleanDescription],
            ];

            // ── Save main product ──
            $product = Product::create($goodmap);
            $product->categories()->sync($validated['categories']);
            // ── Save variations ──
            if (! empty($validated['variations'])) {
                $mainDiscountPercent = $discount ?? 0; // e.g. 33.3

                foreach ($validated['variations'] as $var) {
                    $color = ucfirst(trim($var['color']));
                    $sizes = $var['sizes'] ?? [];
                    $priceMap = $var['price_map'] ?? [];                    // per-size regular price
                    $stockMap = $var['stock_quantity'] ?? [];
                    $mainVar = $var['main_variation'] ?? false;

                    // Optional: allow uniform price fallback (if no price_map)
                    $fallbackRegularPrice = $var['regular_price'] ?? $regularPrice;

                    foreach ($sizes as $size) {
                        // 1. Get REGULAR price for this size (before any discount)
                        $regularPriceForSize = $priceMap[$size] ?? $fallbackRegularPrice;

                        // 2. Apply MAIN product discount ONLY ONCE
                        $salePriceForSize = $mainDiscountPercent > 0
                            ? round($regularPriceForSize * (1 - $mainDiscountPercent / 100), 2)
                            : $regularPriceForSize;

                        ProductVariation::create([
                            'product_id' => $product->id,
                            'attributes' => ['Color' => $color, 'Size' => $size],
                            'regular_price' => $regularPriceForSize,      // e.g. 399.99 or 409.99
                            'sale_price' => $salePriceForSize,       // e.g. 266.79 or 273.46
                            'main_variation' => $mainVar,

                            // 'main_variation' => $mainVar,
                            'price' => $salePriceForSize,         // current selling price
                            'stock_quantity' => $stockMap[$size] ?? 0,
                            'images' => $variationImageMap[$color] ?? [],
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->successResponse(
                data: $product->load(['categories', 'variations']),
                message: 'Product created successfully',
                code: Response::HTTP_CREATED
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Product creation failed', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['thumbnailImage', 'otherImages', 'naturalImages', 'variationImages']),
            ]);

            return $this->failureResponse(
                message: 'Failed to create product: '.$e,
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Add this private method in your ProductController (or a trait/base controller)
    private function validateMainVariation(array $input)
    {
        if (!isset($input['variations']) || !is_array($input['variations']) || empty($input['variations'])) {
            return null;
        }

        $variations      = $input['variations'];
        $mainVariations  = [];

        // ── 1. Collect all variations flagged as main ──────────────────────
        foreach ($variations as $index => $variation) {
            $isMain = filter_var($variation['main_variation'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($isMain) {
                $mainVariations[] = ['index' => $index, 'variation' => $variation];
            }
        }

        // ── 2. Exactly one main variation must exist ───────────────────────
        if (count($mainVariations) === 0) {
            return $this->failureResponse(
                "Exactly one variation must have 'main_variation' = true.",
                422
            );
        }

        if (count($mainVariations) > 1) {
            return $this->failureResponse(
                "Only one variation can have 'main_variation' = true. Found " . count($mainVariations) . ".",
                422
            );
        }

        $mainVariation = $mainVariations[0]['variation'];

        // ── 3. Main variation must have a valid color ──────────────────────
        if (empty($mainVariation['color']) || strtolower(trim($mainVariation['color'])) === '') {
            return $this->failureResponse(
                "The main variation must have a valid 'color'.",
                422
            );
        }

        // ── 4. Main variation must have at least one size ──────────────────
        if (!isset($mainVariation['sizes']) || !is_array($mainVariation['sizes']) || count($mainVariation['sizes']) < 1) {
            return $this->failureResponse(
                "The main variation must have at least one size.",
                422
            );
        }

        // ── 5. Validate price_map covers all sizes ─────────────────────────
        if (!isset($mainVariation['price_map']) || !is_array($mainVariation['price_map'])) {
            return $this->failureResponse(
                "The main variation must have a 'price_map'.",
                422
            );
        }

        foreach ($mainVariation['sizes'] as $size) {
            if (!isset($mainVariation['price_map'][$size])) {
                return $this->failureResponse(
                    "Main variation 'price_map' is missing an entry for size '{$size}'.",
                    422
                );
            }
        }

        // ── 6. Validate stock_quantity covers all sizes ────────────────────
        if (!isset($mainVariation['stock_quantity']) || !is_array($mainVariation['stock_quantity'])) {
            return $this->failureResponse(
                "The main variation must have a 'stock_quantity' map.",
                422
            );
        }

        foreach ($mainVariation['sizes'] as $size) {
            if (!isset($mainVariation['stock_quantity'][$size])) {
                return $this->failureResponse(
                    "Main variation 'stock_quantity' is missing an entry for size '{$size}'.",
                    422
                );
            }
        }

        return null; // ✅ All good
    }
    public function updateProduct(Request $request, $productId)
    {
        // Find product and ensure it belongs to the authenticated vendor
        $product = Product::where('vendor_id', $request->user()->id)
            ->with(['variations', 'categories'])
            ->find($productId);

        if (! $product) {
            return $this->failureResponse(
                message: 'Product not found or you do not have permission to edit it.',
                code: Response::HTTP_NOT_FOUND
            );
        }

        // === 1. Force GD Driver (ImageTragick protection) ===
        Config::set('image.driver', 'gd');
        if (! extension_loaded('gd')) {
            return $this->failureResponse(
                message: 'Image processing is temporarily unavailable.',
                code: Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // === 2. Parse JSON fields safely ===
        $jsonFields = ['categories', 'translations', 'attributes', 'tags', 'variations'];
        $input = $request->all();

        foreach ($jsonFields as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $decoded = json_decode($input[$field], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $input[$field] = $decoded;
                } else {
                    unset($input[$field]);
                }
            }
        }

        // === 3. Validation Rules (same as create, but most fields optional on update) ===
        $validator = Validator::make($input, [
            'name' => 'sometimes|required|string|max:255', // ✅️
            'main_variation' => 'sometimes|required|string|max:255', // ✅️
            'description' => 'sometimes|required|string|max:5000', // ✅️
            'brand_id' => 'sometimes|required|exists:brands,id', // ✅️
            'categories' => 'sometimes|required|array|min:1|max:50', // ✅️
            'categories.*' => 'exists:categories2,id', // ✅️
            'stock_quantity' => 'sometimes|required|integer|min:1|max:999999', // ✅️
            'whatsapp_number_available' => 'sometimes|required|boolean', // ✅️
            'whatsapp_number' => ['sometimes', 'required', 'regex:/^(\+?20|0)?1[0125][0-9]{8}$/'], // ✅️
            'unit' => 'sometimes|required|string|in:piece,kg,g,liter,ml,meter,cm,box,pack', // ✅️
            'unit_amount' => 'sometimes|required|numeric|min:0.01|max:999999', // ✅️
            'product_type' => 'sometimes|required|string|in:physical,digital', // ✅️
            'regular_price' => 'sometimes|required|numeric|min:0.01|max:999999', // ✅️
            // 'sale_price' => 'sometimes|required|numeric|min:0.01|max:999999|lte:regular_price',
            // 'price' => 'sometimes|required|numeric|min:0.01|max:999999',
            // 'sale_price'          => 'nullable|numeric|min:0.01|lte:regular_price',
            'discount_percentage' => 'required_with:regular_price|numeric|min:0|max:80.99', // ✅️
            'thumbnailImage' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120', // ✅️
            'otherImages' => 'nullable|array|max:10', // ✅️
            'otherImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120', // ✅️
            'naturalImages' => 'nullable|array|max:10', // ✅️
            'naturalImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120', // ✅️
            'translations' => 'nullable|array|max:10', // ✅️
            'translations.*.locale' => 'required_with:translations|string|in:ar,en,fr,de,es,it', // ✅️
            'translations.*.name' => 'required_with:translations|string|max:255', // ✅️
            'translations.*.description' => 'required_with:translations|string|max:5000', // ✅️
            'attributes' => 'nullable|array|max:100', // ✅️
            'attributes.*.name' => 'required_with:attributes|string|max:100', // ✅️
            'attributes.*.values' => 'required_with:attributes|array|max:20', // ✅️
            'attributes.*.values.*' => 'string|max:100', // ✅️
            'tags' => 'nullable|array|max:50', // ✅️
            'tags.*' => 'string|max:50', // ✅️
            'max_orders_per_person' => 'nullable|integer|min:1|max:100', // ✅️
            'minimum_order_qty' => 'nullable|integer|min:1|max:100', // ✅️

            // Variations
            'variations' => 'nullable|array|max:50', // ✅️
            'variations.*.color' => 'required_with:variations|string|max:50', // ✅️
            'variations.*.sizes' => 'required_with:variations|array|min:1|max:20', // ✅️
            'variations.*.main_variation' => 'required_with:variations|boolean',
            'variations.*.sizes.*' => 'string|max:25', // ✅️
            'variations.*.stock_quantity' => 'nullable|array', // ✅️
            'variations.*.stock_quantity.*' => 'integer|min:0|max:999999', // ✅️
            'variations.*.price' => 'nullable|numeric|min:0.01|max:999999', // ✅️
            'variations.*.regular_price' => 'nullable|numeric|min:0.01|max:999999', // ✅️
            'variations.*.sale_price' => 'nullable|numeric|min:0.01|max:999999|lte:variations.*.regular_price', // ✅️
            'variations.*.price_map' => 'nullable|array', // ✅️
            'variations.*.price_map.*' => 'numeric|min:0.01|max:999999', // ✅️

            // Variation images (optional on update)
            'variationImages' => 'nullable|array', // ✅️
            'variationImages.*' => 'nullable|array|max:10', // ✅️
            'variationImages.*.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120', // ✅️
        ], [
            'whatsapp_number.regex' => 'Please enter a valid Egyptian WhatsApp number (e.g. 01012345678)',
            'sale_price.lte' => 'Sale price must be less than or equal to regular price',
        ]);

        if ($validator->fails()) {
            return $this->validatrionErrorResponse($validator->errors());
        }
        // Only validate main variation IF variations are being updated
        $mainVariationError = $this->validateMainVariation($input);
        if ($mainVariationError) {
            return $mainVariationError;
        }
        $validatedPreview = $validator->validated();

        // return $this->successResponse(
        //     data: $validatedPreview,
        //     message: 'Validated data',
        //     code: Response::HTTP_OK
        // );

        $validated = $validator->validated();
        $sanitized = $this->sanitizeRecursive($validated);

        $uploadedPaths = []; // Track newly uploaded files for rollback

        try {
            DB::beginTransaction();

            // === Rebuild search text ===
            $cleanName = $sanitized['name'] ?? $product->name;
            $cleanDescription = $sanitized['description'] ?? $product->description;
            $tagsSanitaized = $sanitized['tags'] ?? ($product->tags ?? []);
            // $mainVariation = $sanitized['main_variation'] ?? $product->main_variation;

            $searchParts = [$cleanName, $cleanDescription, implode(' ', $tagsSanitaized)];

            $availableLocales = ['en'];
            $primaryLocale = 'en';
            $translations = $sanitized['translations'] ?? $product->translations;

            foreach ($translations as $tr) {
                $locale = strtolower(trim($tr['locale'] ?? ''));
                if (! in_array($locale, ['ar', 'en', 'fr', 'de', 'es', 'it'])) {
                    continue;
                }

                if (! in_array($locale, $availableLocales)) {
                    $availableLocales[] = $locale;
                    if ($primaryLocale === 'en' && $locale !== 'en') {
                        $primaryLocale = $locale;
                    }
                }
                if (! empty($tr['name'])) {
                    $searchParts[] = $tr['name'];
                }
                if (! empty($tr['description'])) {
                    $searchParts[] = $tr['description'];
                }
            }

            foreach (($sanitized['attributes'] ?? $product->attributes) as $attr) {
                $attrName = Str::lower(trim($attr['name'] ?? ''));
                if ($attrName === '') {
                    continue;
                }
                $searchParts[] = $attrName;
                foreach (($attr['values'] ?? []) as $value) {
                    $valLower = Str::lower(trim($value));
                    if ($valLower === '') {
                        continue;
                    }
                    $searchParts[] = $valLower;
                    $searchParts[] = "$attrName:$valLower";
                    $searchParts[] = "$valLower $attrName";
                }
            }

            $searchText = Str::lower(trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($searchParts)))));

            // === Handle Image Updates ===
            $images = $product->images;

            if ($request->hasFile('thumbnailImage')) {
                $oldThumbnail = $images['thumbnail'] ?? null;
                $newThumbnail = $this->uploadSecureImage($request->file('thumbnailImage'), 'products/thumbnails');
                $images['thumbnail'] = $newThumbnail;
                $uploadedPaths[] = $newThumbnail;
                if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                    Storage::disk('public')->delete($oldThumbnail);
                }
            }

            if ($request->hasFile('otherImages')) {
                $oldOthers = $images['other_images'] ?? [];
                foreach ($oldOthers as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
                $newOthers = $this->uploadMultipleImages($request->file('otherImages'), 'products/other_images');
                $images['other_images'] = $newOthers;
                $uploadedPaths = array_merge($uploadedPaths, $newOthers);
            }

            if ($request->hasFile('naturalImages')) {
                $oldNatural = $images['natural_images'] ?? [];
                foreach ($oldNatural as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
                $newNatural = $this->uploadMultipleImages($request->file('naturalImages'), 'products/natural_images');
                $images['natural_images'] = $newNatural;
                $uploadedPaths = array_merge($uploadedPaths, $newNatural);
            }

            // === Handle Variation Images (per color) ===
            $variationImageMap = [];
            $allFiles = $request->allFiles();
            // Log::info('variationImages ',$allFiles['variationImages']);z

            if (isset($allFiles['variationImages']) && is_array($allFiles['variationImages'])) {
                foreach ($allFiles['variationImages'] as $color => $sizeFiles) {
                    $color = ucfirst(trim($color));
                    $safeColorFolder = $this->safeColorFolderName($color);
                    $folder = "products/variations/{$safeColorFolder}";

                    Storage::disk('public')->makeDirectory($folder);

                    if (is_array($sizeFiles)) {
                        $paths = $this->uploadMultipleImages($sizeFiles, $folder);
                        // Log::info('Pathes ', $paths);
                        $variationImageMap[$color] = $paths;
                        $uploadedPaths = array_merge($uploadedPaths, $paths);
                    }
                }
            }

            $currentUnitType = $product->unit ? array_key_first((array) $product->unit) : 'piece';
            $currentUnitAmount = $product->unit ? $product->unit[$currentUnitType] ?? 1 : 1;

            // === Calculate Sale Price based on Regular Price & Discount Percentage ===
            $regularPrice = $sanitized['regular_price'] ?? $product->regular_price;
            $discountPercentage = round($sanitized['discount_percentage'] ?? ($product->discount_percentage ?? 0), 2);

            // Determine final sale price
            if ($regularPrice !== null && $discountPercentage > 0) {
                $salePrice = round($regularPrice * (1 - $discountPercentage / 100), 2);
            } else {
                // Only fall back to manual sale_price or regular_price if no discount
                $salePrice = $sanitized['sale_price'] ?? $product->sale_price ?? $regularPrice;
            }

            // The price the customer sees/pays
            $displayPrice = $salePrice ?? $regularPrice;
            // ──────────────────────────────────────────────────────────────
            // Auto-sync existing variations when ONLY discount_percentage is sent
            // (i.e. variations array is NOT in request)
            // ──────────────────────────────────────────────────────────────
            if (
                array_key_exists('discount_percentage', $sanitized) &&
                ! isset($sanitized['variations']) &&
                $product->variations()->exists()
            ) {
                $newDiscountPercent = round($sanitized['discount_percentage'] ?? 0, 2);

                Log::info('Discount-only update → Recalculating variation sale prices', [
                    'product_id' => $product->id,
                    'new_discount_percent' => $newDiscountPercent,
                    'variation_count' => $product->variations()->count(),
                ]);

                // CRITICAL: Reload variations fresh from DB to avoid stale data
                $variations = $product->variations()->get();

                foreach ($variations as $variation) {
                    $regularPrice = $variation->regular_price;

                    $newSalePrice = $newDiscountPercent > 0
                        ? round($regularPrice * (1 - $newDiscountPercent / 100), 2)
                        : $regularPrice;

                    $oldSalePrice = $variation->sale_price;

                    // Force update even if same (to be safe) — or compare properly
                    if ($oldSalePrice != $newSalePrice) {
                        $variation->update([
                            'sale_price' => $newSalePrice,
                            'price' => $newSalePrice,
                            'updated_at' => now(), // force timestamp update
                        ]);

                        // if ($updated) {
                        //     Log::info('Variation price updated successfully', [
                        //         'variation_id'   => $variation->id,
                        //         'attributes'     => $variation->attributes,
                        //         'regular_price'  => $regularPrice,
                        //         'old_sale_price' => $oldSalePrice,
                        //         'new_sale_price' => $newSalePrice,
                        //         'discount'       => $newDiscountPercent . '%',
                        //     ]);
                        // } else {
                        //     Log::warning('Variation update failed (no changes or DB error)', [
                        //         'variation_id' => $variation->id,
                        //         'regular_price' => $regularPrice,
                        //         'new_sale_price' => $newSalePrice,
                        //     ]);
                        // }
                    }
                    // else {
                    //     Log::debug('Variation price already correct', [
                    //         'variation_id' => $variation->id,
                    //         'sale_price'   => $newSalePrice,
                    //     ]);
                    // }
                }

                // Log::info('Finished recalculating variation prices', ['product_id' => $product->id]);
            }

            $product->update([
                'name' => $cleanName,
                'description' => $cleanDescription,
                'search_text' => $searchText,
                'short_description' => Str::limit($cleanDescription, 160),
                'images' => $images,
                'brand_id' => $sanitized['brand_id'] ?? $product->brand_id,
                // 'main_variation' => $mainVariation,
                'stock_quantity' => $sanitized['stock_quantity'] ?? $product->stock_quantity,
                'discount_percentage' => $discountPercentage,
                // 'regular_price' => $regularPrice,
                // 'sale_price' => $salePrice,
                // 'price' => $displayPrice,
                'unit' => [
                    $sanitized['unit'] ?? $currentUnitType => $sanitized['unit_amount'] ?? $currentUnitAmount,
                ],
                'translations' => $translations,
                'attributes' => $sanitized['attributes'] ?? $product->attributes,
                'tags' => $tagsSanitaized,

                'lang' => $availableLocales,
                'max_orders_per_person' => (int) ($sanitized['max_orders_per_person'] ?? $product->max_orders_per_person),
                'minimum_order_qty' => (int) ($sanitized['minimum_order_qty'] ?? $product->minimum_order_qty),
                'date_modified' => now(),
                'date_modified_gmt' => now()->utc(),
                'has_options' => ! empty($sanitized['attributes']) || ! empty($sanitized['variations']),
                'has_variations' => ! empty($sanitized['variations']),
            ]);

            // Sync categories
            if (isset($sanitized['categories'])) {
                $product->categories()->sync($sanitized['categories']);
            }

            if (array_key_exists('whatsapp_number_available', $sanitized) || array_key_exists('whatsapp_number', $sanitized)) {

                // Get current values (with fallbacks)
                $current = $product->whatsapp['whatsapp'] ?? ['available' => false, 'number' => null];
                $available = $current['available'];
                $number = $current['number'];

                // Priority 1: If user explicitly sets availability
                if (isset($sanitized['whatsapp_number_available'])) {
                    $available = (bool) $sanitized['whatsapp_number_available'];

                    // If turning OFF → keep the number (old or new)
                    if (! $available) {
                        $number = $sanitized['whatsapp_number'] ?? $current['number'];
                    }
                }

                // Priority 2: If a number is sent → validate and possibly turn ON
                if (isset($sanitized['whatsapp_number'])) {
                    $incomingNumber = trim($sanitized['whatsapp_number']);

                    if (preg_match('/^(\+?20|0)?1[0125][0-9]{8}$/', $incomingNumber) && $incomingNumber !== '0101') {
                        $number = $incomingNumber;
                        $available = true; // auto-enable if valid
                    } else {
                        $number = $current['number']; // keep old number even if invalid input
                        // Don't force available = false here — respect the availability flag
                    }
                }

                // Final save
                $product->whatsapp = [
                    'whatsapp' => [
                        'available' => $available,
                        'number' => $number,
                    ],
                ];
                $product->save();
            }

            // === Update or Create Variations ===
            if (isset($sanitized['variations'])) {
                // Delete old variations
                $product->variations()->delete();

                // Use main product's discount percentage if not overridden
                $mainDiscountPercentage = round($sanitized['discount_percentage'] ?? ($product->discount_percentage ?? 0), 2);

                foreach ($sanitized['variations'] as $var) {
                    $color = ucfirst(trim($var['color']));
                    $sizes = $var['sizes'] ?? [];
                    if (empty($sizes)) {
                        continue;
                    }

                    // Per-variation prices (fallback chain)
                    $varRegularPrice = $var['regular_price'] ?? null;
                    $varSalePrice = $var['sale_price'] ?? null;
                    $varPriceMap = $var['price_map'] ?? [];
                    $rawMain = $var['main_variation'] ?? false;
                    $mainVar = filter_var($rawMain, FILTER_VALIDATE_BOOLEAN);

                    // Fallback to main product prices if variation doesn't define its own
                    $fallbackRegularPrice = $varRegularPrice ?? $product->regular_price;
                    $fallbackSalePrice = $varSalePrice;

                    foreach ($sizes as $size) {
                        // Determine base regular price for this size
                        $sizeRegularPrice = $varPriceMap[$size] ?? $var['price'] ?? $fallbackRegularPrice;

                        // === Sale Price Logic: Auto-calculate from discount if not explicitly set ===
                        $finalSalePrice = null;

                        if ($varSalePrice !== null) {
                            // Explicit sale_price provided per variation → use it
                            $finalSalePrice = $varSalePrice;
                        } elseif ($mainDiscountPercentage > 0 && $sizeRegularPrice > 0) {
                            // Auto-calculate sale price using main product's discount percentage
                            $finalSalePrice = round($sizeRegularPrice * (1 - $mainDiscountPercentage / 100), 2);
                        } else {
                            // No discount, no sale price → sale price = regular price
                            $finalSalePrice = $sizeRegularPrice;
                        }

                        // Final display price (what customer pays)
                        $finalDisplayPrice = $finalSalePrice;

                        ProductVariation::create([
                            'product_id' => $product->id,
                            'attributes' => ['Color' => $color, 'Size' => $size],
                            'regular_price' => round((float) $sizeRegularPrice, 2),
                            'sale_price' => round((float) $finalSalePrice, 2),
                            'price' => round((float) $finalDisplayPrice, 2), // the price shown to customer
                            'stock_quantity' => $var['stock_quantity'][$size] ?? 0,
                            'main_variation' => $mainVar,
                            'images' => $variationImageMap[$color] ?? [],
                        ]);
                    }
                }
            }
            DB::commit();

            return $this->successResponse(
                data: $product->load(['categories', 'variations']),
                message: 'Product updated successfully',
                code: Response::HTTP_OK
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            // Delete any newly uploaded files
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Product update failed', [
                'vendor_id' => $request->user()->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse(
                message: 'Failed to update product: '.$e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // === SECURE HELPER METHODS ===
    private function uploadSecureImage($file, string $folder): string
    {
        // 1. Whitelist: allow subfolders under these bases
        $allowedBases = [
            'products/thumbnails',
            'products/other_images',
            'products/natural_images',
            'products/variations',
        ];

        // Extract first two parts safely
        $parts = explode('/', trim($folder, '/'));
        $base = ($parts[0] ?? '').'/'.($parts[1] ?? '');

        if (! in_array($base, $allowedBases, true)) {
            throw new \InvalidArgumentException("Invalid upload directory: {$folder}");
        }

        if (! $file || ! $file->isValid()) {
            throw new \Exception('Invalid or corrupted file upload.');
        }

        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, WebP allowed.');
        }

        $manager = new ImageManager(new Driver);

        try {
            $image = $manager->read($file->getRealPath());
            if (! $image->width() || ! $image->height()) {
                throw new \Exception('File is not a valid image.');
            }
            $image->scaleDown(width: 1920, height: 1920);
            $encoded = $image->encode(new JpegEncoder(quality: 85));
        } catch (\Throwable $e) {
            throw new \Exception('Failed to process image: '.$e->getMessage());
        }

        $filename = Str::random(40).'.jpg';
        $path = $folder.'/'.$filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($folder);

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function uploadMultipleImages($files, $folder)
    {
        $paths = [];
        if (! $files) {
            return $paths;
        }

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $this->uploadSecureImage($file, $folder);
            }
        }

        return $paths;
    }

    private function generateUniqueSku()
    {
        do {
            $sku = strtoupper(bin2hex(random_bytes(6)));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }

    private function sanitizeRecursive($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeRecursive'], $data);
        }

        return is_string($data) ? strip_tags($data) : $data;
    }

    public function index()
    {

        $allcat = Product::get();

        return response()->json($allcat);
    }

    public function deleteProduct(Request $request, $id)
    {
        $vendor = $request->user();

        // Critical: Only allow vendor to delete their OWN product
        $product = Product::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (! $product) {
            return $this->failureResponse(
                message: 'Product not found or you do not have permission to delete it.',
                code: Response::HTTP_NOT_FOUND
            );
        }

        try {
            // Optional: Delete physical image files from storage
            // $this->deleteProductImages($product);

            // If you're using SoftDeletes, it will soft-delete. Otherwise, hard delete.
            $product->delete();

            // Log the action (very useful for auditing)
            Log::info('Product deleted successfully', [
                'vendor_id' => $vendor->id,
                'product_id' => $id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->successResponse(
                data: [
                    'product_id' => $id,
                ],
                message: 'Product deleted successfully.',
                code: Response::HTTP_OK
            );

        } catch (\Throwable $e) {
            Log::error('Failed to delete product', [
                'vendor_id' => $vendor->id,
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse(
                message: 'An error occurred while deleting the product.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function safeColorFolderName(string $color): string
    {
        return Str::slug($color, '_'); // "Green Whited" → "green_whited", "Red/Blue" → "red_blue"
    }

    public function updateVariation(Request $request, $variationId)
    {
        $variation = ProductVariation::with('product')->findOrFail($variationId);

        // Vendor ownership check (this is all the security you need)
        if ($variation->product->vendor_id !== $request->user()->id) {
            return $this->failureResponse('Unauthorized.', 403);
        }

        $product = $variation->product;

        // Force GD for security
        Config::set('image.driver', 'gd');
        if (! extension_loaded('gd')) {
            return $this->failureResponse('Image processing unavailable.', 503);
        }

        // Block product_id from being sent (extra safety)
        if ($request->has('product_id')) {
            return $this->failureResponse('The product_id field is not allowed.', 422);
        }

        $validator = Validator::make($request->all(), [
            'color' => 'sometimes|required|string|max:50',
            'size' => 'sometimes|required|string|max:25',
            'regular_price' => 'nullable|numeric|min:0.01|max:999999',
            'sale_price' => 'nullable|numeric|min:0.01|max:999999',
            'stock_quantity' => 'nullable|integer|min:0|max:999999',
            'variationImages' => 'nullable|array',
            'variationImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Capture old image paths BEFORE we update the variation
        $oldImagePaths = $variation->getRawImagePaths();
        $newUploadedPaths = [];

        try {
            DB::beginTransaction();

            // Prepare data
            $color = ucfirst(trim($request->input('color', $variation->attribute('Color'))));
            $size = trim($request->input('size', $variation->attribute('Size')));

            // Handle images
            $images = $oldImagePaths; // default: keep existing images

            if ($request->hasFile('variationImages')) {
                $safeColorFolder = $this->safeColorFolderName($color);
                $colorFolder = 'products/variations/'.$safeColorFolder;
                Storage::disk('public')->makeDirectory($colorFolder);

                $images = $this->uploadMultipleImages(
                    $request->file('variationImages'),
                    $colorFolder
                );

                $newUploadedPaths = $images;
            }

            // Calculate prices
            $regularPrice = $request->filled('regular_price')
                ? $request->regular_price
                : $variation->regular_price;

            $discountPercent = (float) ($product->discount_percentage ?? 0);
            $salePrice = $request->filled('sale_price')
                ? $request->sale_price
                : ($discountPercent > 0
                    ? round($regularPrice * (1 - $discountPercent / 100), 2)
                    : $regularPrice);

            $finalPrice = $salePrice;

            // Update the variation
            $variation->update([
                'attributes' => ['Color' => $color, 'Size' => $size],
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'price' => $finalPrice,
                'stock_quantity' => $request->input('stock_quantity', $variation->stock_quantity),
                'images' => $images,
            ]);

            // Update total product stock
            $product->stock_quantity = $product->variations()->sum('stock_quantity');
            $product->saveQuietly();

            // Reload fresh from DB
            $updatedVariation = $variation->fresh();

            DB::commit();

            // Delete old images only if new ones were uploaded
            if ($request->hasFile('variationImages')) {
                foreach ($oldImagePaths as $oldPath) {
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }

            return $this->successResponse(
                data: $updatedVariation,
                message: 'Variation updated successfully.',
                code: 200
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            // Clean up any newly uploaded files if transaction failed
            foreach ($newUploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Variation update failed', [
                'variation_id' => $variationId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse('Failed to update variation.', 500);
        }
    }
    // // Helper: Extract existing images for a color from current variations
    // private function extractExistingVariationImages($product, $color)
    // {
    //     $existing = ProductVariation::where('product_id', $product->id)
    //         ->whereJsonContains('attributes->Color', $color)
    //         ->first();

    //     return $existing ? ($existing->images ?? []) : [];
    // }
    public function updateProductColorImages(Request $request, $id)
    {
        try {
            // Find the product
            $product = Product::findOrFail($id);

            // Validate request data
            $validatedData = $request->validate([
                'colorsOnly' => 'required|array',
                'selectedImagesWithColors.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Get existing images or initialize with default structure
            $existingImages = json_decode($product->images, true) ?? [
                'thumbnail' => null,
                'other_images' => [],
                'natural_images' => [],
                'imageswithcolors' => [],
            ];

            // Get existing attributes or initialize with default structure
            $existingAttributes = json_decode($product->attributes, true) ?? [
                'colors' => [],
                'selectedProductSize' => '',
            ];

            // Update colors in attributes with colorsOnly
            $existingAttributes['colors'] = $request->input('colorsOnly');

            // Handle images with colors update
            if ($request->hasFile('selectedImagesWithColors')) {
                // Delete old color images if they exist
                if (! empty($existingImages['imageswithcolors'])) {
                    foreach ($existingImages['imageswithcolors'] as $oldImage) {
                        if (Storage::disk('public')->exists($oldImage)) {
                            Storage::disk('public')->delete($oldImage);
                        }
                    }
                }

                // Reset imageswithcolors array
                $existingImages['imageswithcolors'] = [];

                // Process new uploaded images
                $uploadedFiles = $request->file('selectedImagesWithColors');
                foreach ($uploadedFiles as $color => $image) {
                    if ($image->isValid()) {
                        // Generate unique filename with color
                        $fileName = time().'_'.$color.'.'.$image->getClientOriginalExtension();
                        $path = $image->storeAs('products/color_images', $fileName, 'public');
                        $existingImages['imageswithcolors'][$color] = $path;
                    }
                }
            }

            // Update product
            $product->images = json_encode($existingImages);
            $product->attributes = json_encode($existingAttributes);
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product images and attributes updated successfully',
                'data' => [
                    'images' => $existingImages,
                    'attributes' => $existingAttributes,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
                'message' => "No product found with ID: {$id}",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProductImages(Request $request, $id)
    {
        // === 1. Ownership Check ===
        $product = Product::where('vendor_id', $request->user()->id)
            ->with('variations')
            ->find($id);

        if (! $product) {
            return $this->failureResponse(
                message: 'Product not found or you do not have permission to edit it.',
                code: Response::HTTP_NOT_FOUND
            );
        }

        //

        // === 2. Force GD Driver (ImageTragick protection) ===
        Config::set('image.driver', 'gd');
        if (! extension_loaded('gd')) {
            return $this->failureResponse(
                message: 'Image processing is temporarily unavailable.',
                code: Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // === 3. Validation (same strict rules as updateProduct) ===
        $validator = Validator::make($request->all(), [
            'thumbnailImage' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120',
            'otherImages' => 'nullable|array|max:10',
            'otherImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
            'naturalImages' => 'nullable|array|max:10',
            'naturalImages.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
            'variationImages' => 'nullable|array',
            'variationImages.*' => 'nullable|array|max:10',
            'variationImages.*.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->validatrionErrorResponse($validator->errors());
        }

        $validated = $validator->validated();
        $uploadedPaths = []; // For rollback

        try {
            DB::beginTransaction();

            $images = $product->images ?? [
                'thumbnail' => null,
                'other_images' => [],
                'natural_images' => [],
            ];

            // === Handle Thumbnail ===
            if ($request->hasFile('thumbnailImage')) {
                $oldThumbnail = $images['thumbnail'] ?? null;

                $newThumbnail = $this->uploadSecureImage(
                    $request->file('thumbnailImage'),
                    'products/thumbnails'
                );

                $images['thumbnail'] = $newThumbnail;
                $uploadedPaths[] = $newThumbnail;

                if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                    Storage::disk('public')->delete($oldThumbnail);
                }
            }

            // === Handle Other Images (replace all) ===
            if ($request->hasFile('otherImages')) {
                $oldOthers = $images['other_images'] ?? [];

                foreach ($oldOthers as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                $newOthers = $this->uploadMultipleImages(
                    $request->file('otherImages'),
                    'products/other_images'
                );

                $images['other_images'] = $newOthers;
                $uploadedPaths = array_merge($uploadedPaths, $newOthers);
            }

            // === Handle Natural Images (replace all) ===
            if ($request->hasFile('naturalImages')) {
                $oldNatural = $images['natural_images'] ?? [];

                foreach ($oldNatural as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                $newNatural = $this->uploadMultipleImages(
                    $request->file('naturalImages'),
                    'products/natural_images'
                );

                $images['natural_images'] = $newNatural;
                $uploadedPaths = array_merge($uploadedPaths, $newNatural);
            }

            // // === Handle Variation Images (by color) ===
            // $variationImageMap = [];
            // $allFiles = $request->allFiles();

            // if (isset($allFiles['variationImages']) && is_array($allFiles['variationImages'])) {
            //     foreach ($allFiles['variationImages'] as $color => $sizeFiles) {
            //         $color = ucfirst(trim($color));
            //         $folder = "products/variations/{$color}";
            //         Storage::disk('public')->makeDirectory($folder);

            //         if (is_array($sizeFiles)) {
            //             $paths = $this->uploadMultipleImages($sizeFiles, $folder);
            //             $variationImageMap[$color] = $paths;
            //             $uploadedPaths = array_merge($uploadedPaths, $paths);
            //         }
            //     }
            // }

            // Update product
            $product->images = $images;
            $product->date_modified = now();
            $product->date_modified_gmt = now()->utc();
            $product->save();

            // Optional: Sync variation images if any were uploaded
            if (! empty($variationImageMap)) {
                foreach ($product->variations as $variation) {
                    $color = $variation->attributes['Color'] ?? null;
                    if ($color && isset($variationImageMap[$color])) {
                        $variation->images = $variationImageMap[$color];
                        $variation->save();
                    }
                }
            }

            DB::commit();

            return $this->successResponse(
                data: $product->load(['variations']),
                message: 'Product images updated successfully',
                code: Response::HTTP_OK
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            // Clean up any uploaded files on failure
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Product images update failed', [
                'vendor_id' => $request->user()->id,
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse(
                message: 'Failed to update product images: '.$e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getProductImages($id, $imageType = 'thumbnail')
    {
        try {
            // Validate image type
            $validImageTypes = ['thumbnail', 'other_images', 'natural_images'];
            if (! in_array($imageType, $validImageTypes)) {
                return [
                    'error' => 'Invalid image type',
                    'valid_types' => $validImageTypes,
                ];
            }

            // Find product
            $product = Product::find($id);
            if (! $product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            // Get existing images
            $existingImages = $product->images ?? [
                'thumbnail' => null,
                'other_images' => [],
                'natural_images' => [],

            ];

            // Fetch images based on type
            $images = [];
            $fullUrls = [];

            switch ($imageType) {
                case 'thumbnail':
                    $images = $existingImages['thumbnail'] ? [$existingImages['thumbnail']] : [];
                    break;

                case 'other_images':
                    $images = $existingImages['other_images'] ?? [];
                    break;

                case 'natural_images':
                    $images = $existingImages['natural_images'] ?? [];
                    break;

                    // case 'imageswithcolors':
                    //     $images = $existingImages['imageswithcolors'] ?? [];
                    //     break;
            }

            // Generate full URLs for each image
            // foreach ($images as $key => $imagePath) {
            //     if ($imagePath) {
            //         // Generate full URL using Laravel's Storage facade
            //         $fullUrls[$key] = Storage::disk('public')->url($imagePath);
            //     }
            // }

            return [
                'success' => true,
                'product_id' => $id,
                'image_type' => $imageType,
                'count' => count($images),
                'images' => $images,
                'paths' => $images, // Original paths if needed
            ];

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProductImageByOldLink(Request $request, $id)
    {

        // **ملخص الكود بالعربي*

        // الدالة دي اسمها `updateProductImageByOldLink` ومهمتها إن البائع يقدر يغيّر صورة من صور المنتج بتاعه عن طريق إنه يبعت اللينك الكامل للصورة القديمة + الصورة الجديدة.

        // أهم المميزات والحلول اللي اتعملت في الكود ده:

        // 1. **البائع بس هو اللي يقدر يعدّل صور منتجه** → بيتأكد من `vendor_id`.
        // 2. **حماية قوية من ثغرة ImageTragick** → بيفرض إستخدام GD مش Imagick.
        // 3. **الفرونت إند بيبعت اللينك كامل** زي
        //    `http://192.168.1.10/images/products/natural_images/صورة_قديمة.jpg`
        //    والكود بيقطّع اللينك ده وياخد الجزء اللي بعد `/images/` بس عشان يقارنه باللي مخزّن في قاعدة البيانات.
        // 4. **بيحافظ على نفس المجلد** (thumbnail / other_images / natural_images) للصورة الجديدة.
        // 5. **بيحذف الصورة القديمة من السيرفر** بعد ما يتأكد إن كل حاجة تمام.
        // 6. **الريسبونس بيرجع كل الصور كـ Full URL** بنفس الـ base اللي الفرونت بعته (يعني لو الفرونت شغال على 192.168.1.10 هيرجع كل الصور بنفس الـ IP ده، مش localhost ولا حاجة تانية).
        // 7. **لو حصل أي غلط** → بيرجّع كل حاجة زي ما كانت (Transaction + حذف الصورة الجديدة لو اتعملت).

        // النتيجة النهائية:
        // - الفرونت إند مش محتاج يعرف المسارات النسبية ولا يتعامل مع localhost/192.168.1.10.
        // - كل الصور في الريسبونس بتيجي بنفس الـ base اللي الفرونت شايفه.
        // - الكود آمن، سريع، وشغال 100% على الموبايل والويب والأجهزة المختلفة.

        // خلاصة بجملة واحدة:
        // "البائع بيبعت لينك الصورة القديمة كامل + الصورة الجديدة، والباك إند بيفهم اللينك، يغيّر الصورة، يحذف القديمة، ويرجع كل الصور بـ Full URL بنفس الدومين اللي الفرونت شايفه... من غير أي صداع!"

        // === 1. Ownership Check ===
        $product = Product::where('vendor_id', $request->user()->id)->find($id);

        if (! $product) {
            return $this->failureResponse(
                message: 'Product not found or you do not have permission to edit it.',
                code: Response::HTTP_NOT_FOUND
            );
        }

        // === 2. Force GD Driver ===
        Config::set('image.driver', 'gd');
        if (! extension_loaded('gd')) {
            return $this->failureResponse(
                message: 'Image processing is temporarily unavailable.',
                code: Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        // === 3. Validation ===
        $validator = Validator::make($request->all(), [
            'oldImageLink' => 'required|string',
            'newImage' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->validatrionErrorResponse($validator->errors());
        }

        $newImageFile = $request->file('newImage');
        if (! $newImageFile->isValid()) {
            return $this->failureResponse('Uploaded file is corrupted or invalid.', Response::HTTP_BAD_REQUEST);
        }

        // === 4. Extract base URL and relative path from oldImageLink ===
        $inputUrl = trim($request->input('oldImageLink'));

        $baseImageUrl = $this->extractBaseImageUrl($inputUrl);     // → http://192.168.1.10/images
        $oldImagePath = $this->extractPathAfterImages($inputUrl);  // → products/natural_images/xxx.jpg

        if (! $baseImageUrl || ! $oldImagePath) {
            return $this->failureResponse(
                message: 'Invalid image URL format. Must contain /images/ in the path.',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $images = $product->images ?? [
            'thumbnail' => null,
            'other_images' => [],
            'natural_images' => [],
        ];

        // === 5. Verify the image belongs to this product ===
        $imageFound = false;
        $imageCategory = null;
        $isThumbnail = ($images['thumbnail'] ?? null) === $oldImagePath;

        if ($isThumbnail) {
            $imageFound = true;
            $imageCategory = 'thumbnail';
        } else {
            foreach (['other_images', 'natural_images'] as $category) {
                if (is_array($images[$category]) && in_array($oldImagePath, $images[$category])) {
                    $imageFound = true;
                    $imageCategory = $category;
                    break;
                }
            }
        }

        if (! $imageFound) {
            return $this->failureResponse(
                message: 'The specified image does not belong to this product.',
                code: Response::HTTP_NOT_FOUND
            );
        }

        $uploadedPath = null;

        try {
            DB::beginTransaction();

            $directory = dirname($oldImagePath);
            if ($directory === '.') {
                $directory = '';
            }

            $newImagePath = $this->uploadSecureImage($newImageFile, $directory);
            if (! $newImagePath) {
                throw new \Exception('Failed to upload new image.');
            }

            $uploadedPath = $newImagePath;

            // Replace old path with new one
            if ($isThumbnail) {
                $images['thumbnail'] = $newImagePath;
            } else {
                $key = array_search($oldImagePath, $images[$imageCategory]);
                if ($key !== false) {
                    $images[$imageCategory][$key] = $newImagePath;
                }
            }

            // Save product
            $product->images = $images;
            $product->date_modified = now();
            $product->date_modified_gmt = now()->utc();
            $product->save();

            // Delete old physical file
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            DB::commit();

            // === CORRECTLY build full URLs using the exact base from frontend ===
            $makeFullUrl = fn ($path) => $path ? $baseImageUrl.'/'.ltrim($path, '/') : null;

            $fullImages = [
                'thumbnail' => $makeFullUrl($images['thumbnail'] ?? null),
                'other_images' => array_map($makeFullUrl, $images['other_images'] ?? []),
                'natural_images' => array_map($makeFullUrl, $images['natural_images'] ?? []),
            ];

            return $this->successResponse(
                data: [
                    'product_id' => $product->id,
                    'old_image' => $inputUrl,
                    'new_image' => $makeFullUrl($newImagePath),
                    'updated_images' => $fullImages,
                ],
                message: 'Product image replaced successfully.',
                code: Response::HTTP_OK
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            Log::error('Failed to replace product image', [
                'vendor_id' => $request->user()->id,
                'product_id' => $id,
                'old_image' => $inputUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse(
                message: 'Failed to update image: '.$e->getMessage(),
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Fixed & Reliable Helpers
    // ─────────────────────────────────────────────────────────────────────────────

    private function extractBaseImageUrl(string $url): ?string
    {
        // Matches: http://192.168.1.10/images  or  https://example.com/images/
        if (preg_match('#^(https?://[^/]+/images)(?:/|$)#i', $url, $matches)) {
            return rtrim($matches[1], '/'); // → http://192.168.1.10/images
        }

        return null;
    }

    private function extractPathAfterImages(string $url): ?string
    {
        // Extract everything after "/images/"
        if (preg_match('#/images/(.*)$#i', $url, $matches)) {
            $path = $matches[1];
            $path = strtok($path, '?');           // remove query string
            $path = trim($path);                  // remove surrounding whitespace & slashes

            // Security checks
            if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
                return null;
            }

            return $path;
        }

        return null;
    }

    /**
     * Find image in the existing structure
     */
    private function findImageInStructure(array $images, string $oldImagePath): array
    {
        foreach ($images as $key => $value) {
            if (is_array($value)) {
                if (($subKey = array_search($oldImagePath, $value)) !== false) {
                    return [true, $key, $subKey];
                }
                if (isset($value[array_key_first($value)]) && $value[array_key_first($value)] === $oldImagePath) {
                    return [true, $key, null];
                }
            } elseif ($value === $oldImagePath) {
                return [true, $key, null];
            }
        }

        return [false, null, null];
    }

    public function updateProductSaleCounter($product_id, $quantity_ordered = 1)
    {
        try {
            // Find the product by ID
            $product = Product::find($product_id);

            // Check if the product exists
            if (! $product) {
                return [
                    'error' => 'Product not found',
                    'product_id' => $product_id,
                ];
            }

            // ✅ VALIDATE STOCK QUANTITY
            if ($product->stock_quantity < $quantity_ordered) {
                return [
                    'error' => 'Insufficient stock',
                    'product_id' => $product_id,
                    'available_stock' => $product->stock_quantity,
                    'quantity_ordered' => $quantity_ordered,
                    'message' => "Only {$product->stock_quantity} items available, but {$quantity_ordered} ordered",
                ];
            }

            // ✅ START DATABASE TRANSACTION
            DB::beginTransaction();

            // ✅ INCREMENT SALE COUNTER BY QUANTITY
            $product->increment('total_sales', $quantity_ordered);

            // ✅ REDUCE STOCK QUANTITY BY QUANTITY ORDERED
            $product->decrement('stock_quantity', $quantity_ordered);

            // ✅ UPDATE STOCK STATUS
            $newStockQuantity = $product->fresh()->stock_quantity; // Get fresh stock after decrement

            if ($newStockQuantity > 0) {
                $newStockStatus = 'instock';
            } else {
                $newStockStatus = 'outofstock';
            }

            $product->update(['stock_status' => $newStockStatus]);

            // ✅ COMMIT TRANSACTION
            DB::commit();

            return [
                'message' => 'Product sale counter and stock updated successfully',
                'product_id' => $product_id,
                'quantity_ordered' => $quantity_ordered,
                'total_sales' => $product->total_sales + $quantity_ordered, // New total
                'stock_quantity' => $newStockQuantity,
                'stock_status' => $newStockStatus,
            ];

        } catch (\Exception $e) {
            // ✅ ROLLBACK TRANSACTION ON ERROR
            DB::rollBack();

            return [
                'error' => 'Failed to update product sale counter and stock',
                'product_id' => $product_id,
                'quantity_ordered' => $quantity_ordered,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function imagesData(Request $request)
    {
        $product = Product::where('id', $request->query('id'))->first();
        $decodedImages = json_decode($product->images, true);
        $imageLinks = collect($decodedImages)->pluck('src')->toArray();
        $the_images = [
            'images' => $imageLinks,
            'color_image' => null,
            'images_full_url' => array_map(function ($image, $index) {
                return [
                    'key' => $image,
                    'path' => null,
                    'status' => 200,
                ];
            }, $imageLinks, array_keys($imageLinks)),
            'color_images_full_url' => [],
        ];

        return response()->json($the_images);
    }

    /**
     * Decode JSON fields in a product
     */
    private function decodeJsonFields($product, array $jsonFields)
    {
        foreach ($jsonFields as $field) {
            if (! empty($product->$field)) {
                $product->$field = json_decode($product->$field, true);
            }
        }

        return $product;
    }

    /**
     * Process image links by adding storage path
     */
    private function processImageLinks($images)
    {
        if (! empty($images)) {
            if (! empty($images['thumbnail'])) {
                $images['thumbnail'] = '/images/'.$images['thumbnail'];
            }

            if (! empty($images['other_images'])) {
                foreach ($images['other_images'] as $key => $img) {
                    $images['other_images'][$key] = '/images/'.$img;
                }
            }

            if (! empty($images['natural_images'])) {
                foreach ($images['natural_images'] as $key => $img) {
                    $images['natural_images'][$key] = '/images/'.$img;
                }
            }

            if (! empty($images['imageswithcolors'])) {
                foreach ($images['imageswithcolors'] as $color => $img) {
                    $images['imageswithcolors'][$color] = '/images/'.$img;
                }
            }
        }

        return $images;
    }

    public function viewOneProduct(Request $request, $id)
    {
        $product = Product::with(['categories', 'variations'])->find($id);

        if (! $product) {
            return $this->failureResponse('Product not found.', 404);
        }

        return $this->successResponse($product, 'Product retrieved successfully.');
    }

    public function getproductsvendor(Request $request)
    {
        try {
            $vendor = auth('vendor')->user();
            if (! $vendor) {
                return $this->failureResponse('Unauthenticated vendor.', 401);
            }

            $search = trim($request->query('search', ''));

            $query = Product::where('vendor_id', $vendor->id)->with(['categories', 'variations']);

            if ($search !== '') {
                $like = '%'.$search.'%';

                $query->where(function ($q) use ($like) {
                    $q->where('search_text', 'LIKE', $like);
                });
            }

            $query->orderBy('created_at', 'desc');
            $products = $query->paginate(10);

            $response = [
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
            ];

            return $this->successResponse($response, 'Products retrieved successfully.', 200);

        } catch (\Throwable $e) {
            // \Log::error('Vendor product search error: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());

            return $this->failureResponse('Server error: '.$e->getMessage(), 500);
        }
    }

    public function getPopularTwoProducts(Request $request)
    {
        try {
            // Ensure the user is authenticated (vendor)
            if (! auth()->check()) {
                return $this->failureResponse('Unauthenticated.', 401);
            }

            // Get the top 2 popular products based on total_sales for the authenticated vendor
            $popularProducts = Product::where('vendor_id', auth()->id())
                ->orderBy('total_sales', 'asc')
                ->take(2)->select([
                    'id',
                    'name',
                    'slug',
                    'images',
                    'regular_price',
                    'sale_price',
                    'price',
                    'total_sales',
                    'stock_quantity',
                    // 'rating_avg',
                    'created_at',
                ])
            // Optional: useful for popularity
                ->get();

            $responseData = [
                'total_size' => $popularProducts->count(), // More accurate than hardcoding 2
                'limit' => 10,
                'offset' => 1,
                'products' => $popularProducts->toArray(),
            ];

            return $this->successResponse(
                data: $responseData,
                message: 'Popular products retrieved successfully.',
                code: 200
            );

        } catch (\Throwable $e) {
            // Log the error in production (optional but recommended)
            Log::error('Error fetching popular products: '.$e->getMessage(), [
                'vendor_id' => auth()->id() ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failureResponse(
                message: $e,
                code: 500
            );
        }
    }
}
