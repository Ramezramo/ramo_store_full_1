<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$apply = in_array('--apply', $argv, true);
$categoryRenames = [
    'Bags-ramo' => 'Bags',
    'Bag-ramo' => 'Bag',
];
$placeholderBrands = ['Apple', 'Intel', 'Microsoft', 'Samsung', 'Sony'];

$preview = [
    'mode' => $apply ? 'apply' : 'dry-run',
    'category_renames' => DB::table('categories2')
        ->whereIn('name', array_keys($categoryRenames))
        ->select('id', 'name', 'parent')
        ->orderBy('id')
        ->get(),
    'brand_assignments_to_clear' => DB::table('products_data as p')
        ->join('brands as b', 'p.brand_id', '=', DB::raw('b.id::text'))
        ->whereIn('b.name', $placeholderBrands)
        ->select('p.id', 'p.name', 'b.name as brand_name')
        ->orderBy('p.id')
        ->get(),
    'replacement_brand' => 'Unbranded',
    'brands_to_remove' => DB::table('brands')
        ->whereIn('name', $placeholderBrands)
        ->select('id', 'name')
        ->orderBy('id')
        ->get(),
];

if (! $apply) {
    fwrite(STDOUT, json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    fwrite(STDOUT, "Dry run only. Re-run with --apply to make these exact changes.\n");
    exit(0);
}

$result = DB::transaction(function () use ($categoryRenames, $placeholderBrands, $preview) {
    foreach ($categoryRenames as $from => $to) {
        $category = DB::table('categories2')->where('name', $from)->lockForUpdate()->first();
        if (! $category) {
            continue;
        }

        $collision = DB::table('categories2')
            ->where('name', $to)
            ->where('id', '!=', $category->id)
            ->lockForUpdate()
            ->exists();
        if ($collision) {
            throw new RuntimeException("Cannot rename category {$from}: the target label {$to} already exists.");
        }

        DB::table('categories2')->where('id', $category->id)->update(['name' => $to]);
    }

    $brands = DB::table('brands')
        ->whereIn('name', $placeholderBrands)
        ->lockForUpdate()
        ->get(['id', 'name']);
    $brandIds = $brands->pluck('id')->map(fn ($id) => (string) $id)->all();

    $replacementBrand = DB::table('brands')
        ->whereRaw('LOWER(name) = ?', ['unbranded'])
        ->lockForUpdate()
        ->first();
    if (! $replacementBrand) {
        $replacementBrandId = DB::table('brands')->insertGetId(['name' => 'Unbranded']);
        $replacementBrand = (object) ['id' => $replacementBrandId, 'name' => 'Unbranded'];
    }

    $reassignedProducts = empty($brandIds)
        ? 0
        : DB::table('products_data')
            ->whereIn('brand_id', $brandIds)
            ->update(['brand_id' => (string) $replacementBrand->id]);
    $deletedBrands = empty($brandIds)
        ? 0
        : DB::table('brands')->whereIn('id', $brands->pluck('id'))->delete();

    return [
        'renamed_categories' => $preview['category_renames']->count(),
        'replacement_brand' => $replacementBrand->name,
        'reassigned_placeholder_brand_products' => $reassignedProducts,
        'removed_placeholder_brands' => $deletedBrands,
    ];
});

fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
