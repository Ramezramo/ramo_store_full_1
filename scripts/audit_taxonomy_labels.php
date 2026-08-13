<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$categoryNames = ['Bags-ramo', 'Bag-ramo'];
$brandNames = ['Apple', 'Intel', 'Microsoft', 'Samsung', 'Sony'];

$categories = DB::table('categories2 as c')
    ->leftJoin('product_category as pc', 'pc.category_id', '=', 'c.id')
    ->whereIn('c.name', $categoryNames)
    ->select('c.id', 'c.name', 'c.parent', DB::raw('COUNT(pc.product_id) AS product_count'))
    ->groupBy('c.id', 'c.name', 'c.parent')
    ->orderBy('c.id')
    ->get();

$brands = DB::table('brands as b')
    ->leftJoin('products_data as p', 'p.brand_id', '=', DB::raw('b.id::text'))
    ->whereIn('b.name', $brandNames)
    ->select('b.id', 'b.name', DB::raw('COUNT(p.id) AS product_count'))
    ->groupBy('b.id', 'b.name')
    ->orderBy('b.id')
    ->get();

$placeholderBrandProducts = DB::table('products_data as p')
    ->join('brands as b', 'p.brand_id', '=', DB::raw('b.id::text'))
    ->whereIn('b.name', $brandNames)
    ->select('p.id', 'p.name', 'p.slug', 'p.status', 'p.acceptance_status', 'b.name as brand_name')
    ->orderBy('p.id')
    ->get();

$output = [
    'categories' => $categories,
    'placeholder_brands' => $brands,
    'placeholder_brand_products' => $placeholderBrandProducts,
    'available_brands' => DB::table('brands')->select('id', 'name')->orderBy('name')->get(),
];

fwrite(STDOUT, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
