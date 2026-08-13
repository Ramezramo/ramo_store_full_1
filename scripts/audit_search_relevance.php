<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$query = trim($argv[1] ?? 'jeans');
if ($query === '') {
    fwrite(STDERR, "Provide a non-empty search query.\n");
    exit(1);
}

$needle = '%' . $query . '%';
$results = DB::table('products_data as p')
    ->where('p.status', 'publish')
    ->where('p.acceptance_status', 'approved')
    ->whereExists(function ($variations) {
        $variations->selectRaw('1')
            ->from('product_variations as pv')
            ->whereColumn('pv.product_id', 'p.id')
            ->where('pv.regular_price', '>', 0);
    })
    ->where(function ($matches) use ($needle) {
        $matches->where('p.name', 'ILIKE', $needle)
            ->orWhere('p.slug', 'ILIKE', $needle)
            ->orWhere('p.search_text', 'ILIKE', $needle)
            ->orWhere('p.description', 'ILIKE', $needle)
            ->orWhere('p.translations', 'ILIKE', $needle);
    })
    ->select('p.id', 'p.name', 'p.slug', 'p.search_text', 'p.description', 'p.translations')
    ->orderBy('p.id')
    ->get()
    ->map(function ($product) use ($query) {
        $lowerQuery = mb_strtolower($query);
        $matches = [];
        foreach (['name', 'slug', 'search_text', 'description', 'translations'] as $field) {
            if (mb_stripos((string) $product->{$field}, $lowerQuery) !== false) {
                $matches[] = $field;
            }
        }
        return [
            'id' => $product->id,
            'name' => $product->name,
            'matched_fields' => $matches,
            'search_text' => $product->search_text,
        ];
    });

fwrite(STDOUT, json_encode([
    'query' => $query,
    'result_count' => $results->count(),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
