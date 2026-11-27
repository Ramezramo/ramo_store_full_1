<?php

namespace App\Support;

class ProductAttributes
{
    /** Internal metadata is never a customer-facing option. */
    public static function visible(mixed $attributes): array
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?? json_decode(stripslashes($attributes), true);
        }
        if (! is_array($attributes)) return [];

        return array_filter($attributes, fn ($value, $key) =>
            is_string($key) && trim($key) !== '' && ! str_starts_with(trim($key), '_')
            && is_scalar($value), ARRAY_FILTER_USE_BOTH);
    }
}
