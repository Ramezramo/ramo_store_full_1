<?php
// Auto-find Laravel root (works from any depth)
$dir = __DIR__;
for ($i = 0; $i < 20; $i++) {
    if (file_exists($dir.'/.env') || file_exists($dir.'/artisan')) {
        $root = $dir;
        break;
    }
    $dir = dirname($dir);
}

if (! file_exists($root.'/.env')) {
    exit('FATAL: .env not found!');
}

require_once $root.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->load();

// Image base URL: reads IMAGE_BASE_URL env var (CDN / original server).
// Falls back to APP_URL + /storage/ for local storage.
define('IMAGE_BASE_URL', rtrim(
    env('IMAGE_BASE_URL') ?: rtrim(env('APP_URL', ''), '/') . '/storage',
    '/'
) . '/');

$product_array_fields = ['images',
    'unit',
    'translations',
    'attributes',
    'tags',
    'categories',
    'lang',
    'dimensions',
    'whatsapp',
    'meta_data',
    'downloads',
    '_links',
    'attributesData',
    'default_attributes',
    'variations',
    'grouped_products',
    'upsell_ids',
    'cross_sell_ids',
    'related_ids'];
$product_hide_fields = ['_links', 'meta_data', 'attributesData', 'global_unique_id', 'better_featured_image', 'translations', 'search_text'];
