<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTimelineController extends Controller
{
    private function getLayout(string $lang): array
    {
        $row = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', $lang)
            ->first();
        return $row ? (json_decode($row->value, true) ?? []) : [];
    }

    public function index(Request $request)
    {
        $lang = $request->input('lang', 'en');
        $sections = $this->getLayout($lang);

        $categories = DB::table('categories2')->orderBy('name')->get(['id', 'name']);
        $langs = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->pluck('lang')
            ->filter()
            ->values();

        return view('admin.timeline', compact('sections', 'lang', 'categories', 'langs'));
    }

    public function searchProducts(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = DB::table('products_data as p')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->select('p.id', 'p.name');
        if ($q !== '') {
            $query->where('p.name', 'ilike', "%{$q}%");
        }
        $results = $query->orderBy('p.name')->limit(20)->get();
        return response()->json($results);
    }

    public function livePreview(Request $request)
    {
        $lang = $request->input('lang', 'en');
        $sections = $this->getLayout($lang);
        $categories = DB::table('categories2')->orderBy('name')->get(['id', 'name']);
        $langs = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->pluck('lang')->filter()->unique()->values();
        if ($langs->isEmpty()) $langs = collect(['en']);
        if (!$langs->contains($lang)) $lang = $langs->first();
        return view('admin.live_preview', compact('sections', 'categories', 'lang', 'langs'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'lang'    => 'required|string|max:10',
            'payload' => 'required|json',
        ]);

        $lang    = $request->input('lang');
        $payload = $request->input('payload');

        // Validate it's a JSON array
        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return response()->json(['error' => 'Invalid JSON array'], 422);
        }

        $exists = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', $lang)
            ->exists();

        if ($exists) {
            DB::table('app_configs')
                ->where('config_key', 'horizon_layout')
                ->where('lang', $lang)
                ->update([
                    'value'      => $payload,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('app_configs')->insert([
                'config_key'   => 'horizon_layout',
                'config_group' => 'layout',
                'lang'         => $lang,
                'value'        => $payload,
                'is_public'    => true,
                'sort_order'   => 0,
                'updated_at'   => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Timeline saved for '.$lang]);
    }
}
