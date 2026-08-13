<?php

declare(strict_types=1);

use App\Constants\AppConstants;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$strict = in_array('--strict', $argv, true);

/** @return array<int, string> */
function imageLikeValues(mixed $value): array
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return imageLikeValues($decoded);
        }

        return preg_match('/\.(?:avif|gif|jpe?g|png|svg|webp)(?:[?#].*)?$/i', $value)
            || preg_match('/^https?:\/\//i', $value)
            || str_contains($value, '/storage/')
            ? [$value]
            : [];
    }

    if (! is_array($value)) {
        return [];
    }

    $paths = [];
    foreach ($value as $item) {
        $paths = [...$paths, ...imageLikeValues($item)];
    }

    return array_values(array_unique($paths));
}

function decoded(mixed $value): mixed
{
    if (! is_string($value)) {
        return $value;
    }

    return json_decode($value, true) ?? $value;
}

$products = DB::table('products_data')
    ->select(['id', 'name', 'slug', 'status', 'acceptance_status', 'images'])
    ->orderBy('id')
    ->get();

$categories = DB::table('categories2')
    ->select(['id', 'name', 'slug', 'image'])
    ->orderBy('id')
    ->get();

$variations = DB::table('product_variations')
    ->select(['id', 'product_id', 'main_variation', 'images'])
    ->orderBy('product_id')
    ->orderBy('id')
    ->get();

$report = [
    'products_total' => $products->count(),
    'categories_total' => $categories->count(),
    'variations_total' => $variations->count(),
    'products_with_external_media' => [],
    'categories_with_external_media' => [],
    'variations_with_external_media' => [],
    'published_products_without_usable_media' => [],
    'focus_products' => [],
];

foreach ($products as $product) {
    $paths = imageLikeValues(decoded($product->images));
    $external = array_values(array_filter($paths, fn (string $path) => preg_match('/^https?:\/\//i', $path) === 1));
    $isPublic = $product->status === 'publish' && $product->acceptance_status === 'approved';
    $thumbnailUrl = AppConstants::productThumbnailUrl($product->images);

    if ($external !== []) {
        $report['products_with_external_media'][] = [
            'id' => (int) $product->id,
            'name' => $product->name,
            'paths' => $external,
        ];
    }

    if ($isPublic && $thumbnailUrl === null) {
        $report['published_products_without_usable_media'][] = [
            'id' => (int) $product->id,
            'name' => $product->name,
            'paths' => $paths,
        ];
    }

    if ((int) $product->id === 22 || preg_match('/midi|hoodie|velvet|jeans/i', (string) $product->name)) {
        $report['focus_products'][] = [
            'id' => (int) $product->id,
            'name' => $product->name,
            'status' => $product->status,
            'acceptance_status' => $product->acceptance_status,
            'thumbnail_url' => $thumbnailUrl,
            'image_paths' => $paths,
        ];
    }
}

foreach ($categories as $category) {
    $paths = imageLikeValues(decoded($category->image));
    $external = array_values(array_filter($paths, fn (string $path) => preg_match('/^https?:\/\//i', $path) === 1));

    if ($external !== []) {
        $report['categories_with_external_media'][] = [
            'id' => (int) $category->id,
            'name' => $category->name,
            'paths' => $external,
        ];
    }
}

foreach ($variations as $variation) {
    $paths = imageLikeValues(decoded($variation->images));
    $external = array_values(array_filter($paths, fn (string $path) => preg_match('/^https?:\/\//i', $path) === 1));

    if ($external !== []) {
        $report['variations_with_external_media'][] = [
            'id' => (int) $variation->id,
            'product_id' => (int) $variation->product_id,
            'main_variation' => (bool) $variation->main_variation,
            'paths' => $external,
        ];
    }
}

$report['summary'] = [
    'external_product_records' => count($report['products_with_external_media']),
    'external_category_records' => count($report['categories_with_external_media']),
    'external_variation_records' => count($report['variations_with_external_media']),
    'published_products_without_usable_media' => count($report['published_products_without_usable_media']),
];

$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "Unable to encode catalog media inventory.\n");
    exit(1);
}

fwrite(STDOUT, $json . PHP_EOL);

if ($strict && array_sum($report['summary']) > 0) {
    fwrite(STDERR, "Catalog media audit failed strict validation.\n");
    exit(2);
}
