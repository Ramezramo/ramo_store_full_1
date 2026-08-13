<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function index()
    {
        $sellableProducts = DB::table('products_data as p')
            ->select('p.id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->whereExists(function ($variations) {
                $variations->selectRaw('1')
                    ->from('product_variations as pv')
                    ->whereColumn('pv.product_id', 'p.id')
                    ->where('pv.regular_price', '>', 0);
            });

        $productIds = (clone $sellableProducts)->pluck('id');
        $categoryIds = DB::table('product_category')
            ->whereIn('product_id', $productIds)
            ->distinct()
            ->pluck('category_id');

        $urls = collect([
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('shop'), 'changefreq' => 'daily', 'priority' => '0.9'],
        ]);

        DB::table('categories2')
            ->whereIn('id', $categoryIds)
            ->orderBy('id')
            ->pluck('id')
            ->each(fn ($id) => $urls->push([
                'loc' => route('shop', ['category' => $id]),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ]));

        $productIds->each(fn ($id) => $urls->push([
            'loc' => route('product', ['id' => $id]),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ]));

        return response()
            ->view('sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8'])
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
