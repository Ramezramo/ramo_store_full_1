<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
    'products_data', 'product_category', 'product_variations', 'categories2',
    'cart_items', 'wishlists', 'orders', 'order_sub_orders',
];

$ownership = DB::select(
    "SELECT c.relname AS table_name, pg_get_userbyid(c.relowner) AS table_owner
     FROM pg_class c
     JOIN pg_namespace n ON n.oid = c.relnamespace
     WHERE n.nspname = current_schema()
       AND c.relkind = 'r'
       AND c.relname = ANY(?::text[])
     ORDER BY c.relname",
    ['{' . implode(',', $tables) . '}']
);

$identity = DB::selectOne('SELECT current_user AS current_user, session_user AS session_user');

fwrite(STDOUT, json_encode([
    'database_identity' => $identity,
    'table_ownership' => $ownership,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
