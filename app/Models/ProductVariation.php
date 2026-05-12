<?php

namespace App\Models;

use App\Constants\AppConstants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $table = 'product_variations';

    protected $fillable = [
        'product_id',
        'attributes',
        'price',
        'main_variation',
        'regular_price',
        'sale_price',
        'stock_quantity',
        'images',
    ];

    protected $casts = [
        'attributes' => 'array',
        'images' => 'array',
    ];

    /** ----------------------------------------------------------------
     * Relationships
     * ---------------------------------------------------------------- */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** ----------------------------------------------------------------
     * Accessor: Convert stored relative paths → full public URLs
     * ---------------------------------------------------------------- */
    protected function images(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Handle old string-encoded JSON just in case
                if (is_string($value)) {
                    $value = json_decode($value, true) ?? [];
                }

                if (empty($value)) {
                    return [];
                }

                $baseUrl = $this->getFullImageBase();

                return array_map(function ($path) use ($baseUrl) {
                    // Normalize slashes and remove leading/trailing mess
                    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');

                    // If the path already contains "storage/", avoid duplication
                    if (str_starts_with($cleanPath, 'storage/')) {
                        $cleanPath = substr($cleanPath, 8); // remove leading "storage/"
                    }

                    return $baseUrl.$cleanPath;
                }, (array) $value);
            }
        );
    }

    /** ----------------------------------------------------------------
     * Private helper – centralized image base URL
     * ---------------------------------------------------------------- */
    private function getFullImageBase(): string
    {
        return AppConstants::imageBase();
    }

    /** ----------------------------------------------------------------
     * Helper to get a variation attribute by key (e.g. Color, Size)
     * ---------------------------------------------------------------- */
    public function attribute(string $key, $default = null)
    {
        return data_get($this->attributes, "attributes.{$key}", $default);
        // or: $this->attributes['attributes'][$key] ?? $default;
    }

    public function getRawImagePaths(): array
    {
        $raw = $this->getOriginal('images');
        if (is_string($raw)) {
            return json_decode($raw, true) ?? [];
        }
        return (array) ($raw ?? []);
    }
    
}
