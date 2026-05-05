<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppConfigController extends Controller
{
    public function index(Request $request)
    {
        $lang  = $request->input('lang', 'en');
        $group = $request->input('group');

        $query = DB::table('app_configs')->where('is_public', true);

        if ($group) {
            $query->where('config_group', $group);
        }

        // Fetch lang-independent + requested language, ordered so lang-specific comes last (overrides)
        $query->whereRaw("(lang IS NULL OR lang = ?)", [$lang])
              ->orderByRaw("CASE WHEN lang IS NULL THEN 0 ELSE 1 END")
              ->orderBy('sort_order');

        $rows   = $query->get();
        $result = [];

        foreach ($rows as $row) {
            $parsed = json_decode($row->value, true);
            $result[$row->config_key] = $parsed ?? $row->value;
        }

        // Inject dynamic server_config base URL
        if (!$group || $group === 'server') {
            $result['server_config'] = array_merge($result['server_config'] ?? [], [
                'baseUrl'  => config('app.url'),
                'imageurl' => rtrim(\App\Constants\AppConstants::imageBase(), '/'),
            ]);
        }

        return response()->json([
            'success' => true,
            'lang'    => $lang,
            'data'    => $result,
        ]);
    }

    public function show(Request $request, string $key)
    {
        $lang = $request->input('lang', 'en');

        // Try language-specific first, then fall back to null
        $row = DB::table('app_configs')
            ->where('config_key', $key)
            ->where('is_public', true)
            ->whereRaw("(lang IS NULL OR lang = ?)", [$lang])
            ->orderByRaw("CASE WHEN lang = ? THEN 0 ELSE 1 END", [$lang])
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Config not found'], 404);
        }

        $parsed = json_decode($row->value, true);

        return response()->json([
            'success' => true,
            'lang'    => $lang,
            'key'     => $key,
            'data'    => $parsed ?? $row->value,
        ]);
    }

    public function groups()
    {
        $groups = DB::table('app_configs')
            ->where('is_public', true)
            ->selectRaw('config_group, COUNT(*) as count')
            ->groupBy('config_group')
            ->orderBy('config_group')
            ->get();

        return response()->json(['success' => true, 'data' => $groups]);
    }
}
