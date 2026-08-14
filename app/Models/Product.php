<?php

namespace App\Models;

use App\Constants\AppConstants;
use App\Models\Models\Category;
use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /** --------------------------------------------------------------
     *  Table & primary key
     *  -------------------------------------------------------------- */
    protected $table = 'products_data';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';
// public function variations()
// {
//     return $this->hasMany(ProductVariation::class, 'product_id');
// }
    /** --------------------------------------------------------------
     *  Fillable columns (never put relationships here!)
     *  -------------------------------------------------------------- */
    // protected $hidden = ['search_text'];
    protected $fillable = [
        'digital_file_ready',
        'total_wheigh',
        'lang',
        'brand_id',
        'translations',
        'unit',
        'discount_percentage',
        // 'main_variation',
        'minimum_order_qty',
        'max_orders_per_person',
        'search_text',
        // 'description_ar',
        // 'name_ar',

        // Core Woo-style fields
        'name',
        'slug',
        'permalink',
        'date_created',
        'date_modified',
        'type',
        'featured',
        'catalog_visibility',
        'description',
        'short_description',
        'sku',
        'price',
        'regular_price',
        'sale_price',
        'on_sale',
        'purchasable',
        'total_sales',
        'virtual',
        'downloadable',
        'manage_stock',
        'stock_quantity',
        'backorders',
        'backorders_allowed',
        'backordered',
        'weight',
        'dimensions',
        'shipping_required',
        'shipping_taxable',
        'shipping_class',
        'sold_individually',
        'reviews_allowed',
        'average_rating',
        'rating_count',
        'upsell_ids',
        'cross_sell_ids',
        'parent_id',
        'purchase_note',
        'tags',
        'images',
        'attributes',
        'default_attributes',
        'variations',
        'grouped_products',
        'menu_order',
        'price_html',
        'related_ids',
        'meta_data',
        'stock_status',
        'has_options',
        'is_purchased',
        'is_wallet_product',
        '_links',
        'attributesData',
        'date_created_gmt',
        'date_modified_gmt',
        'date_on_sale_from',
        'date_on_sale_from_gmt',
        'date_on_sale_to',
        'date_on_sale_to_gmt',
        'downloads',
        'download_limit',
        'download_expiry',
        'external_url',
        'button_text',
        'low_stock_amount',
        'shipping_class_id',
        'whatsapp',
        'global_unique_id',
        'better_featured_image',
        'button_mode',
    ];

    /** --------------------------------------------------------------
     *  JSON / Array casting – API will return real objects
     *  -------------------------------------------------------------- */
    protected $casts = [
        // JSON columns
        'images'             => 'array',
        'unit'               => 'array',
        'translations'       => 'array',
        'attributes'         => 'array',
        'tags'               => 'array',
        'lang'               => 'array',
        'dimensions'         => 'array',
        'whatsapp'           => 'array',
        'meta_data'          => 'array',
        'downloads'          => 'array',
        '_links'             => 'array',
        'attributesData'     => 'array',
        'default_attributes' => 'array',
        'variations'         => 'array',
        'grouped_products'   => 'array',
        'upsell_ids'         => 'array',
        'cross_sell_ids'     => 'array',
        'related_ids'        => 'array',
        'categories'         => 'array',

        // Booleans
        'featured' => 'boolean',
        'on_sale' => 'boolean',
        'purchasable' => 'boolean',
        'virtual' => 'boolean',
        'downloadable' => 'boolean',
        'manage_stock' => 'boolean',
        'backorders_allowed' => 'boolean',
        'backordered' => 'boolean',
        'shipping_required' => 'boolean',
        'shipping_taxable' => 'boolean',
        'sold_individually' => 'boolean',
        'reviews_allowed' => 'boolean',
        'has_options' => 'boolean',
        'is_purchased' => 'boolean',
        'is_wallet_product' => 'boolean',

        // Dates
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'date_created_gmt' => 'datetime',
        'date_modified_gmt' => 'datetime',
        'date_on_sale_from' => 'datetime',
        'date_on_sale_from_gmt' => 'datetime',
        'date_on_sale_to' => 'datetime',
        'date_on_sale_to_gmt' => 'datetime',
    ];



/** ----------------------------------------------------------------
     *  Helper: Full image base URL (never stored in DB)
     *  ---------------------------------------------------------------- */
    private function getFullImageBase(): string
    {
        return AppConstants::imageBase();
    }

    /** ----------------------------------------------------------------
     *  Mutator/Accessor for product images
     *  ---------------------------------------------------------------- */
    // protected function images(): Attribute
    // {
    //     return Attribute::make(
    //         get: function ($value) {
    //             if (is_null($value)) return null;

    //             // $value is already an array because of $casts
    //             return $this->prependBaseUrlToImages($value);
    //         }
    //     );
    // }

    /** ----------------------------------------------------------------
     *  Accessor for variations → decode + prepend full URLs
     *  ---------------------------------------------------------------- */
    public function getVariationsAttribute($value)
    {
        if (empty($value)) return [];

        $variations = is_string($value) ? json_decode($value, true) : $value;

        foreach ($variations as &$variation) {
            // Fix attributes JSON string inside variation
            if (isset($variation['attributes']) && is_string($variation['attributes'])) {
                $variation['attributes'] = json_decode($variation['attributes'], true);
            }

            // Fix variation images
            if (!empty($variation['images'])) {
                $images = is_string($variation['images'])
                    ? json_decode($variation['images'], true)
                    : $variation['images'];

                $variation['images'] = array_map(function ($img) {
                    return $this->getFullImageBase() . ltrim($img, '/');
                }, (array) $images);
            }
        }

        return $variations;
    }

    /** ----------------------------------------------------------------
     *  Generic helper to add base URL to any image structure
     *  ---------------------------------------------------------------- */
    private function prependBaseUrlToImages($images)
    {
        if (!is_array($images)) {
            $images = json_decode($images, true) ?? [];
        }

        $base = $this->getFullImageBase();

        foreach ($images as $key => &$paths) {
            if (is_array($paths)) {
                $images[$key] = array_map(fn($path) => $base . ltrim($path, '/'), $paths);
            } elseif (is_string($paths)) {
                $images[$key] = $base . ltrim($paths, '/');
            }
        }

        return $images;
    }

    /** ----------------------------------------------------------------
     *  If you load variations via relationship (recommended)
     *  ---------------------------------------------------------------- */
    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'product_category',
            'product_id',
            'category_id'
        );
    }

    /** ----------------------------------------------------------------
     *  toJson / toArray – automatically apply full URLs
     *  ---------------------------------------------------------------- */
    public function toArray()
    {
        $array = parent::toArray();

        // Ensure main product images have full URLs
        if (isset($array['images'])) {
            $array['images'] = $this->prependBaseUrlToImages($array['images']);
        }

        return $array;
    }
}
