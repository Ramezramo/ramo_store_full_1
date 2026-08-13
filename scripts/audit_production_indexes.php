<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
    'products_data', 'product_variations', 'product_category', 'categories2',
    'cart_items', 'wishlists', 'orders', 'users',
];

$indexes = DB::select(
    "SELECT tablename, indexname, indexdef
     FROM pg_indexes
     WHERE schemaname = current_schema()
       AND tablename = ANY(?::text[])
     ORDER BY tablename, indexname",
    ['{' . implode(',', $tables) . '}']
);

fwrite(STDOUT, json_encode([
    'schema' => DB::selectOne('SELECT current_schema() AS name')->name,
    'indexes' => $indexes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
