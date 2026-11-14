<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ShopDisplayConfig;
use App\Services\ReferralSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfigAdminController extends Controller
{
    private function isAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        $roles = is_array($user->role) ? $user->role : json_decode($user->role, true) ?? [];
        return in_array('admin', $roles);
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

        $rawMobileLayout = DB::table('app_configs')
            ->where('config_key', 'shop_mobile_product_layout')
            ->whereNull('lang')
            ->value('value');
        $decodedMobileLayout = is_string($rawMobileLayout) ? json_decode($rawMobileLayout, true) : null;
        $shopMobileLayout = is_string($decodedMobileLayout)
            ? $decodedMobileLayout
            : trim((string) $rawMobileLayout, " \t\n\r\0\x0B\"");
        if (!in_array($shopMobileLayout, ['grid', 'horizontal'], true)) {
            $shopMobileLayout = 'horizontal';
        }

        return view('admin.configs.index', compact(
            'configs', 'groups', 'langs', 'group', 'lang', 'search', 'shopMobileLayout'
        ) + [
            'shopDisplay' => ShopDisplayConfig::get(),
            'shopCategories' => DB::table('categories2')->where(function ($query) {
                $query->whereNull('parent')->orWhere('parent', 0);
            })->orderBy('menu_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $record = DB::table('app_configs')->where('id', $id)->first(['config_key', 'lang']);
        if ($record && $record->config_key === 'referral_settings') {
            if ($record->lang !== null) {
                throw ValidationException::withMessages(['lang' => 'Referral settings must be shared across languages.']);
            }
            app(ReferralSettingsService::class)->save($this->referralPayload($request));
            return response()->json(['success' => true, 'message' => 'Referral settings updated.']);
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

        if ($request->config_key === 'referral_settings') {
            if ($request->filled('lang')) {
                throw ValidationException::withMessages(['lang' => 'Referral settings must be shared across languages.']);
            }
            app(ReferralSettingsService::class)->save($this->referralPayload($request));
            $id = DB::table('app_configs')->where('config_key', 'referral_settings')->whereNull('lang')->value('id');
            return response()->json(['success' => true, 'id' => $id, 'message' => 'Referral settings created.']);
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

    public function updateShopMobileLayout(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/login')->with('error', 'Admin access required.');
        }

        $request->validate([
            'layout' => 'required|in:grid,horizontal',
        ]);

        DB::table('app_configs')->updateOrInsert(
            [
                'config_key' => 'shop_mobile_product_layout',
                'lang' => null,
            ],
            [
                'config_group' => 'layout',
                'value' => json_encode($request->input('layout')),
                'label' => 'Shop mobile product layout',
                'description' => 'Choose two products per row or a horizontal product row on narrow phones.',
                'is_public' => false,
                'sort_order' => 100,
                'updated_at' => now(),
            ]
        );

        return redirect()->route('admin.configs', ['group' => 'layout'])
            ->with('success', 'Shop mobile layout saved.');
    }

    public function updateShopDisplay(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/login')->with('error', 'Admin access required.');
        }

        $data = $request->validate([
            'category_layout' => 'required|in:scroll,wrap,hidden',
            'category_limit' => 'required|integer|min:1|max:30',
            'category_ids' => 'nullable|array|max:100',
            'category_ids.*' => 'integer|distinct|exists:categories2,id',
            'filters_enabled' => 'required|boolean',
            'filter_price' => 'required|boolean',
            'filter_categories' => 'required|boolean',
            'filter_brands' => 'required|boolean',
            'filter_sort' => 'required|boolean',
        ]);
        $data['category_limit'] = (int) $data['category_limit'];
        $data['category_ids'] = array_map('intval', $data['category_ids'] ?? []);
        foreach (['filters_enabled', 'filter_price', 'filter_categories', 'filter_brands', 'filter_sort'] as $key) {
            $data[$key] = $request->boolean($key);
        }

        DB::table('app_configs')->updateOrInsert(
            ['config_key' => ShopDisplayConfig::KEY, 'lang' => null],
            [
                'config_group' => 'layout',
                'value' => json_encode($data),
                'label' => 'Shop categories and filters',
                'description' => 'Control the category row and visible shop filters.',
                'is_public' => false,
                'sort_order' => 101,
                'updated_at' => now(),
            ]
        );

        return redirect()->route('admin.configs', ['group' => 'layout'])
            ->with('success', 'Shop category and filter settings saved.');
    }

    public function destroy(int $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $key = DB::table('app_configs')->where('id', $id)->value('config_key');
        DB::table('app_configs')->where('id', $id)->delete();
        if ($key === 'referral_settings') Cache::forget('referral_settings');
        return response()->json(['success' => true, 'message' => 'Config deleted.']);
    }
    private function referralPayload(Request $request): array
    {
        $request->validate(['value' => ['required', 'string', 'json']]);
        $decoded = json_decode($request->input('value'));
        if (! is_object($decoded)) {
            throw ValidationException::withMessages(['value' => 'Referral settings must be a JSON object.']);
        }
        $data = (array) $decoded;
        $known = array_keys(app(ReferralSettingsService::class)->defaults());
        if (array_diff(array_keys($data), $known)) {
            throw ValidationException::withMessages(['value' => 'Unknown referral setting.']);
        }
        return Validator::make($data, [
            'referral_enabled' => ['sometimes', 'boolean'],
            'referral_min_order_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:1000000'],
            'referral_commission_type' => ['sometimes', 'required', 'in:flat,percentage'],
            'referral_commission_value' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'referral_commission_scope' => ['sometimes', 'required', 'in:first_order,all_orders'],
            'referral_expiry_days' => ['sometimes', 'required', 'integer', 'min:1', 'max:3650'],
        ])->validate();
    }

}
