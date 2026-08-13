<?php

declare(strict_types=1);

use App\Models\ImageGalleryImage;
use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/**
 * Return image paths that map to Laravel's public disk. External assets are
 * identified separately because this checker must not make uncontrolled HTTP
 * requests to third-party hosts.
 *
 * @param mixed $value
 * @return array<int, string>
 */
function localImagePaths(mixed $value): array
{
    $paths = [];

    if (is_array($value)) {
        foreach ($value as $item) {
            $paths = array_merge($paths, localImagePaths($item));
        }

        return array_values(array_unique($paths));
    }

    if (!is_string($value) || $value === '') {
        return [];
    }

    $path = parse_url($value, PHP_URL_PATH) ?: $value;
    $path = ltrim($path, '/');

    if (str_starts_with($path, 'storage/')) {
        return [substr($path, strlen('storage/'))];
    }

    if (str_starts_with($path, 'image-gallery/') || str_starts_with($path, 'products/')) {
        return [$path];
    }

    return [];
}

/**
 * @param mixed $value
 * @return array<int, string>
 */
function externalImageUrls(mixed $value): array
{
    $urls = [];

    if (is_array($value)) {
        foreach ($value as $item) {
            $urls = array_merge($urls, externalImageUrls($item));
        }

        return array_values(array_unique($urls));
    }

    if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
        return [$value];
    }

    return [];
}

$issues = [
    'products_missing_required_data' => [],
    'products_missing_local_media' => [],
    'products_using_external_media' => [],
    'gallery_records_missing_local_media' => [],
];

$products = Product::query()
    ->orderBy('id')
    ->get();

foreach ($products as $product) {
    $missing = [];

    foreach (['name', 'sku'] as $field) {
        if ($product->getAttribute($field) === null || $product->getAttribute($field) === '') {
            $missing[] = $field;
        }
    }

    // Storefront prices are derived from product variations, not the legacy
    // products_data.price column. Do not flag legitimate variation products.
    if (!$product->variations()->where('regular_price', '>', 0)->exists()) {
        $missing[] = 'sellable_variation_price';
    }

    if (!$product->categories()->exists()) {
        $missing[] = 'category';
    }

    $images = $product->images ?? [];
    $localPaths = localImagePaths($images);
    $externalUrls = externalImageUrls($images);

    if ($images === [] || $images === null || (count($localPaths) === 0 && count($externalUrls) === 0)) {
        $missing[] = 'image';
    }

    if ($missing !== []) {
        $issues['products_missing_required_data'][] = [
            'id' => $product->id,
            'sku' => $product->sku,
            'missing' => $missing,
        ];
    }

    foreach ($localPaths as $path) {
        if (!Storage::disk('public')->exists($path)) {
            $issues['products_missing_local_media'][] = [
                'id' => $product->id,
                'sku' => $product->sku,
                'path' => $path,
            ];
        }
    }

    if ($externalUrls !== []) {
        $issues['products_using_external_media'][] = [
            'id' => $product->id,
            'sku' => $product->sku,
            'count' => count($externalUrls),
        ];
    }
}

foreach (ImageGalleryImage::query()->orderBy('id')->get() as $image) {
    if (!Storage::disk('public')->exists($image->path)) {
        $issues['gallery_records_missing_local_media'][] = [
            'id' => $image->id,
            'path' => $image->path,
            'original_name' => $image->original_name,
        ];
    }
}

$report = [
    'generated_at' => now()->toIso8601String(),
    'product_count' => $products->count(),
    'gallery_record_count' => ImageGalleryImage::count(),
    'publication_status_distribution' => $products->groupBy(fn (Product $product) => (string) ($product->status ?? '(null)'))->map->count()->sortKeys()->all(),
    'acceptance_status_distribution' => $products->groupBy(fn (Product $product) => (string) ($product->acceptance_status ?? '(null)'))->map->count()->sortKeys()->all(),
    'issue_counts' => array_map('count', $issues),
    'issues' => $issues,
];

$reportPath = storage_path('app/audits/catalog-health-' . now()->format('Ymd-His') . '.json');
if (!is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0775, true);
}
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

fwrite(STDOUT, json_encode([
    'product_count' => $report['product_count'],
    'gallery_record_count' => $report['gallery_record_count'],
    'publication_status_distribution' => $report['publication_status_distribution'],
    'acceptance_status_distribution' => $report['acceptance_status_distribution'],
    'issue_counts' => $report['issue_counts'],
    'report_path' => $reportPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

exit(array_sum($report['issue_counts']) === 0 ? 0 : 2);
