<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfigAdminController extends Controller
{
    private function isAdmin(): bool
    {
        if (!Auth::check()) return false;
        $adminEmail = DB::table('app_configs')
            ->where('config_key', 'admin_email')
            ->value('value');
        $adminEmail = $adminEmail ? trim(json_decode($adminEmail) ?? $adminEmail, '"') : null;
        return Auth::user()->email === $adminEmail || Auth::user()->email === 'adminramoui@gmail.com';
    }

    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/login')->with('error', 'Admin access required.');
        }

        $group  = $request->input('group', 'all');
        $lang   = $request->input('lang', 'all');
        $search = $request->input('search', '');

        $query = DB::table('app_configs')->orderBy('config_group')->orderBy('sort_order')->orderBy('config_key');

        if ($group && $group !== 'all') $query->where('config_group', $group);
        if ($lang === 'null') {
            $query->whereNull('lang');
        } elseif ($lang && $lang !== 'all') {
            $query->where('lang', $lang);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(config_key) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(label) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        $configs = $query->get();

        $groups = DB::table('app_configs')
            ->selectRaw('config_group, COUNT(*) as cnt')
            ->groupBy('config_group')
            ->orderBy('config_group')
            ->get();

        $langs = DB::table('app_configs')
            ->selectRaw('lang, COUNT(*) as cnt')
            ->groupBy('lang')
            ->orderByRaw('CASE WHEN lang IS NULL THEN 0 ELSE 1 END, lang')
            ->get();

        return view('admin.configs.index', compact('configs', 'groups', 'langs', 'group', 'lang', 'search'));
    }

    public function update(Request $request, int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $value    = $request->input('value', '');
        $label    = $request->input('label');
        $isPublic = $request->boolean('is_public', true);

        // Validate that value is valid JSON
        if ($value !== '') {
            $decoded = json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to store as JSON string
                $value = json_encode($value);
            }
        }

        DB::table('app_configs')->where('id', $id)->update([
            'value'      => $value,
            'label'      => $label,
            'is_public'  => $isPublic,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Config updated.']);
    }

    public function create(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'config_key'   => 'required|string|max:200',
            'config_group' => 'required|string|max:50',
            'value'        => 'required|string',
            'label'        => 'nullable|string|max:200',
        ]);

        $existing = DB::table('app_configs')
            ->where('config_key', $request->config_key)
            ->where('lang', $request->input('lang') ?: null)
            ->exists();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Key already exists for this language.'], 422);
        }

        $id = DB::table('app_configs')->insertGetId([
            'config_key'   => $request->config_key,
            'config_group' => $request->config_group,
            'lang'         => $request->input('lang') ?: null,
            'value'        => $request->value,
            'label'        => $request->label,
            'description'  => $request->input('description'),
            'is_public'    => $request->boolean('is_public', true),
            'sort_order'   => (int)$request->input('sort_order', 0),
            'updated_at'   => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id, 'message' => 'Config created.']);
    }

    public function destroy(int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        DB::table('app_configs')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Config deleted.']);
    }
}
