<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => DB::table('users')->count(),
            'blocked_users'  => DB::table('users')->where('is_blocked', true)->count(),
            'total_orders'   => DB::table('orders')->count(),
            'pending_orders' => DB::table('orders')->where('status', 'pending')->count(),
            'total_revenue'  => DB::table('orders')->whereNotIn('status', ['cancelled', 'refunded'])->sum('final_total'),
            'total_products'   => DB::table('products_data')->count(),
            'pending_products' => DB::table('products_data')->where('acceptance_status', 'pending')->count(),
            'total_vendors'  => DB::table('vendor_users')->count(),
            'pending_vendors'=> DB::table('vendor_users')->where('status', 'pending')->count(),
            'total_devices'  => DB::table('device_access_tokens')->count(),
            'blocked_devices'=> DB::table('device_access_tokens')->where('blocked', 1)->count(),
        ];

        $recent_orders = DB::table('orders')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'status', 'final_total', 'customer_id', 'payment_method_title', 'date_created', 'currency_symbol']);

        $recent_users = DB::table('users')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'name', 'email', 'role', 'created_at', 'is_blocked']);

        return view('admin.dashboard', compact('stats', 'recent_orders', 'recent_users'));
    }

    // ── USERS ──────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $search = $request->input('search', '');
        $role   = $request->input('role', '');
        $status = $request->input('status', '');

        $query = DB::table('users')->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(phone) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }
        if ($role) $query->whereRaw('LOWER(role) LIKE ?', ['%'.strtolower($role).'%']);
        if ($status === 'blocked') $query->where('is_blocked', true);
        if ($status === 'active')  $query->where('is_blocked', false);

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users', compact('users', 'search', 'role', 'status'));
    }

    public function blockUser(Request $request, int $id)
    {
        DB::table('users')->where('id', $id)->update(['is_blocked' => true, 'updated_at' => now()]);
        DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
        return back()->with('success', 'User blocked successfully.');
    }

    public function unblockUser(int $id)
    {
        DB::table('users')->where('id', $id)->update(['is_blocked' => false, 'updated_at' => now()]);
        return back()->with('success', 'User unblocked successfully.');
    }

    public function deleteUser(int $id)
    {
        DB::table('users')->where('id', $id)->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function updateUserRole(Request $request, int $id)
    {
        $role = $request->input('role', 'customer');
        DB::table('users')->where('id', $id)->update(['role' => $role, 'updated_at' => now()]);
        return back()->with('success', 'User role updated.');
    }

    // ── ORDERS ─────────────────────────────────────────────────────
    public function orders(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = DB::table('orders')->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(id AS TEXT) LIKE ?', ['%'.$search.'%'])
                  ->orWhereRaw('LOWER(payment_method_title) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('CAST(customer_id AS TEXT) LIKE ?', ['%'.$search.'%']);
            });
        }
        if ($status) $query->where('status', $status);

        $orders = $query->paginate(20)->withQueryString();

        $statuses = collect(['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed', 'on-hold']);

        return view('admin.orders', compact('orders', 'search', 'status', 'statuses'));
    }

    public function orderDetail(int $id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) abort(404);

        $customer = $order->customer_id ? DB::table('users')->where('id', $order->customer_id)->first(['id', 'name', 'email', 'phone']) : null;

        $billing   = $order->billing    ? json_decode($order->billing,    true) : [];
        $shipping  = $order->shipping   ? json_decode($order->shipping,   true) : [];
        $lineItems = $order->line_items ? json_decode($order->line_items, true) : [];
        $timeline  = $order->timeline   ? json_decode($order->timeline,   true) : [];

        $subOrders = DB::table('order_sub_orders as s')
            ->where('s.parent_order_id', $id)
            ->leftJoin('vendor_users as v', 'v.id', '=', 's.vendor_id')
            ->select(['s.*', 'v.shop_name as vendor_shop_name'])
            ->orderBy('s.id')
            ->get();

        return view('admin.order-detail', compact('order', 'customer', 'billing', 'shipping', 'lineItems', 'timeline', 'subOrders'));
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $status = $request->input('status');
        $allowed = ['pending', 'processing', 'on-hold', 'shipped', 'completed', 'cancelled', 'refunded', 'failed'];
        if (!in_array($status, $allowed)) return back()->with('error', 'Invalid status.');

        DB::table('orders')->where('id', $id)->update([
            'status'        => $status,
            'date_modified' => now(),
            'updated_at'    => now(),
        ]);
        return back()->with('success', 'Order status updated to '.$status.'.');
    }

    // ── VENDORS ────────────────────────────────────────────────────
    public function vendors(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = DB::table('vendor_users')->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(shop_name) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(first_name) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }
        if ($status) $query->where('status', $status);

        $vendors = $query->paginate(20)->withQueryString();

        return view('admin.vendors', compact('vendors', 'search', 'status'));
    }

    public function vendorShow(int $id)
    {
        $vendor = DB::table('vendor_users')->where('id', $id)->first();
        abort_if(!$vendor, 404);

        $subOrders = DB::table('order_sub_orders')
            ->where('vendor_id', $id)
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id', 'parent_order_id', 'customer_id', 'status', 'subtotal',
                'discount_total', 'total', 'tracking_number', 'tracking_carrier', 'created_at'
            ]);

        $products = DB::table('products_data')
            ->where('vendor_id', $id)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'name', 'status', 'acceptance_status', 'created_at']);

        $productCount = DB::table('products_data')->where('vendor_id', $id)->count();

        return view('admin.vendors.show', compact('vendor', 'products', 'productCount', 'subOrders'));
    }

    public function approveVendor(int $id)
    {
        DB::table('vendor_users')->where('id', $id)->update(['status' => 'approved', 'updated_at' => now()]);
        return back()->with('success', 'Vendor approved.');
    }

    public function blockVendor(int $id)
    {
        DB::table('vendor_users')->where('id', $id)->update(['status' => 'blocked', 'updated_at' => now()]);
        return back()->with('success', 'Vendor blocked.');
    }

    public function rejectVendor(int $id)
    {
        DB::table('vendor_users')->where('id', $id)->update(['status' => 'rejected', 'updated_at' => now()]);
        return back()->with('success', 'Vendor rejected.');
    }

    public function deleteVendor(int $id)
    {
        DB::table('vendor_users')->where('id', $id)->delete();
        return redirect()->route('admin.vendors')->with('success', 'Vendor deleted.');
    }

    // ── PRODUCTS ───────────────────────────────────────────────────
    public function products(Request $request)
    {
        $search     = $request->input('search', '');
        $acceptance = $request->input('acceptance', '');
        $status     = $request->input('status', '');

        $query = DB::table('products_data')
            ->leftJoin('vendor_users', 'products_data.vendor_id', '=', 'vendor_users.id')
            ->leftJoinSub(
                DB::table('product_variations')
                    ->select('product_id', DB::raw('MIN(price) as pv_min'), DB::raw('MAX(price) as pv_max'))
                    ->groupBy('product_id'),
                'pv',
                'pv.product_id', '=', 'products_data.id'
            )
            ->orderByDesc('products_data.id')
            ->select(
                'products_data.id', 'products_data.name', 'products_data.status',
                'products_data.acceptance_status', 'products_data.vendor_id',
                'products_data.created_at', 'products_data.stock_status',
                'products_data.stock_quantity', 'products_data.sku',
                'products_data.product_type',
                'vendor_users.shop_name',
                DB::raw('COALESCE(pv.pv_min, 0) as min_price'),
                DB::raw('COALESCE(pv.pv_max, 0) as max_price')
            );

        if ($search)     $query->whereRaw('LOWER(products_data.name) LIKE ?', ['%'.strtolower($search).'%']);
        if ($acceptance) $query->where('products_data.acceptance_status', $acceptance);
        if ($status)     $query->where('products_data.status', $status);

        $products = $query->paginate(20)->withQueryString();

        return view('admin.products', compact('products', 'search', 'acceptance', 'status'));
    }

    public function approveProduct(int $id)
    {
        DB::table('products_data')->where('id', $id)->update([
            'acceptance_status' => 'approved',
            'status'            => 'publish',
            'updated_at'        => now(),
        ]);
        return back()->with('success', 'Product approved and published.');
    }

    public function rejectProduct(int $id)
    {
        DB::table('products_data')->where('id', $id)->update([
            'acceptance_status' => 'rejected',
            'status'            => 'draft',
            'updated_at'        => now(),
        ]);
        return back()->with('success', 'Product rejected and hidden from store.');
    }

    public function deleteProduct(int $id)
    {
        DB::table('products_data')->where('id', $id)->delete();
        DB::table('product_variations')->where('product_id', $id)->delete();
        DB::table('product_category')->where('product_id', $id)->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function toggleProductStatus(Request $request, int $id)
    {
        $status = $request->input('status', 'publish');
        DB::table('products_data')->where('id', $id)->update(['status' => $status, 'updated_at' => now()]);
        return back()->with('success', 'Product status updated.');
    }

    public function bulkProducts(Request $request)
    {
        $request->validate([
            'bulk_action' => 'required|in:approve,reject,delete',
            'ids'         => 'required|array|min:1',
            'ids.*'       => 'integer',
        ]);

        $ids = array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));
        if (!$ids) return back()->with('error', 'Select at least one product.');

        $now = now();

        if ($request->bulk_action === 'approve') {
            DB::table('products_data')->whereIn('id', $ids)->update([
                'acceptance_status' => 'approved',
                'status'            => 'publish',
                'updated_at'        => $now,
            ]);
            return back()->with('success', count($ids).' product(s) approved and published.');
        }

        if ($request->bulk_action === 'reject') {
            DB::table('products_data')->whereIn('id', $ids)->update([
                'acceptance_status' => 'rejected',
                'status'            => 'draft',
                'updated_at'        => $now,
            ]);
            return back()->with('success', count($ids).' product(s) rejected.');
        }

        // delete
        DB::table('product_variations')->whereIn('product_id', $ids)->delete();
        DB::table('product_category')->whereIn('product_id', $ids)->delete();
        DB::table('products_data')->whereIn('id', $ids)->delete();
        return back()->with('success', count($ids).' product(s) deleted.');
    }

    public function showProduct(int $id)
    {
        $product = DB::table('products_data')->where('id', $id)->first();
        abort_if(!$product, 404);

        $vendor = $product->vendor_id
            ? DB::table('vendor_users')->where('id', $product->vendor_id)->first(['id','first_name','last_name','email','shop_name'])
            : null;

        $dbVariations = DB::table('product_variations')
            ->where('product_id', $id)
            ->orderByDesc('main_variation')
            ->orderBy('id')
            ->get()
            ->map(function ($v) {
                $v->attributes = is_string($v->attributes)
                    ? (json_decode($v->attributes, true) ?? [])
                    : (array) $v->attributes;
                $v->images = is_string($v->images)
                    ? (json_decode($v->images, true) ?? [])
                    : (array) ($v->images ?? []);
                return $v;
            });

        $hasVariations = (bool) $product->has_variations
            || $dbVariations->count() > 1
            || ($dbVariations->count() === 1 && !empty($dbVariations->first()->attributes));

        $categories   = DB::table('categories2')->orderBy('parent')->orderBy('name')->get();
        $brands       = DB::table('brands')->orderBy('name')->get();
        $selectedCats = DB::table('product_category')->where('product_id', $id)->pluck('category_id')->toArray();
        $images       = json_decode($product->images ?? '{}', true) ?: [];
        $translations = json_decode($product->translations ?? '[]', true) ?: [];
        $tags         = json_decode($product->tags ?? '[]', true) ?: [];
        $attributes   = json_decode($product->attributes ?? '[]', true) ?: [];
        $whatsapp     = json_decode($product->whatsapp ?? '{}', true) ?: [];
        $unit         = json_decode($product->unit ?? '{}', true) ?: [];
        $unitType     = $unit ? array_key_first($unit) : 'piece';
        $unitAmount   = $unit ? ($unit[$unitType] ?? 1) : 1;
        $whatsappData = $whatsapp['whatsapp'] ?? [];
        $imgBase      = \Illuminate\Support\Facades\Storage::url('');

        $allCoupons = DB::table('coupons')->where('status', 'publish')->orderBy('code')->get(['id', 'code', 'amount', 'discount_type']);
        $attachedCoupon = null;
        foreach (DB::table('coupons')->where('status', 'publish')->get(['id', 'code', 'amount', 'discount_type', 'product_ids']) as $c) {
            $cpids = json_decode($c->product_ids ?? '[]', true) ?? [];
            if (in_array($id, array_map('intval', $cpids))) { $attachedCoupon = $c; break; }
        }

        $colorGroups = [];
        foreach ($dbVariations as $dv) {
            $attrs = $dv->attributes ?? [];
            $color = $attrs['Color'] ?? 'Default';
            $size  = $attrs['Size'] ?? 'Default';
            if (!isset($colorGroups[$color])) $colorGroups[$color] = [];
            $colorGroups[$color][] = ['size' => $size, 'price' => $dv->price, 'reg' => $dv->regular_price, 'sale' => $dv->sale_price, 'stock' => $dv->stock_quantity, 'main' => $dv->main_variation];
        }
        $editColorRows = [];
        foreach ($colorGroups as $colorName => $rows) {
            $sizes    = array_column($rows, 'size');
            $priceMap = []; $stockMap = [];
            foreach ($rows as $r) { $priceMap[$r['size']] = $r['reg']; $stockMap[$r['size']] = $r['stock']; }
            $editColorRows[] = ['name' => $colorName, 'sizes' => $sizes, 'price_map' => $priceMap, 'stock' => $stockMap, 'sale_price' => ''];
        }

        $variation   = $dbVariations->first();
        $priceRange  = $dbVariations->count()
            ? ['min' => $dbVariations->min('price'), 'max' => $dbVariations->max('price')]
            : null;
        $totalStock  = $dbVariations->sum('stock_quantity');
        $discountPct = (float) ($product->discount_percentage ?? 0);

        return view('admin.products.show', compact(
            'product', 'vendor', 'variation', 'dbVariations', 'hasVariations',
            'categories', 'brands', 'selectedCats',
            'images', 'translations', 'tags', 'attributes', 'whatsappData',
            'unitType', 'unitAmount', 'imgBase',
            'colorGroups', 'editColorRows', 'priceRange', 'totalStock', 'discountPct',
            'allCoupons', 'attachedCoupon'
        ));
    }

    public function adminUpdateProductSection(Request $request, int $id)
    {
        $product = DB::table('products_data')->where('id', $id)->first();
        abort_if(!$product, 404);

        $section = $request->input('_section');
        $now     = now();

        switch ($section) {
            case 'basic':
                $request->validate([
                    'name'              => 'required|string|max:500',
                    'status'            => 'required|in:publish,draft,private',
                    'acceptance_status' => 'required|in:pending,approved,rejected',
                    'sku'               => 'nullable|string|max:100',
                    'brand_id'          => 'nullable|integer',
                    'product_type'      => 'nullable|in:physical,digital',
                    'unit'              => 'nullable|string|max:50',
                    'unit_amount'       => 'nullable|numeric|min:0.01',
                    'short_description' => 'nullable|string|max:1000',
                    'description'       => 'nullable|string',
                ]);
                $productType = $request->input('product_type', 'physical');
                $unitType    = $request->input('unit', 'piece');
                $unitAmount  = (float) $request->input('unit_amount', 1);
                DB::table('products_data')->where('id', $id)->update([
                    'name'              => $request->input('name'),
                    'status'            => $request->input('status'),
                    'acceptance_status' => $request->input('acceptance_status'),
                    'sku'               => $request->input('sku', ''),
                    'brand_id'          => $request->input('brand_id') ?: '',
                    'product_type'      => $productType,
                    'type'              => $productType,
                    'unit'              => json_encode([$unitType => $unitAmount]),
                    'short_description' => $request->input('short_description', ''),
                    'description'       => $request->input('description', ''),
                    'shipping_required' => $productType === 'physical',
                    'virtual'           => $productType === 'digital',
                    'updated_at'        => $now,
                ]);
                break;

            case 'translations':
                $raw = (array) $request->input('translations', []);
                $translations = [];
                foreach ($raw as $tr) {
                    $locale = strtolower(trim($tr['locale'] ?? ''));
                    $name   = trim($tr['name'] ?? '');
                    if (!$locale || !$name) continue;
                    $translations[] = ['locale' => $locale, 'name' => $name, 'description' => trim($tr['description'] ?? '')];
                }
                $locales = array_merge(['en'], array_column($translations, 'locale'));
                DB::table('products_data')->where('id', $id)->update([
                    'translations' => json_encode($translations),
                    'lang'         => json_encode(array_values(array_unique($locales))),
                    'updated_at'   => $now,
                ]);
                break;

            case 'pricing':
                $request->validate([
                    'discount_percentage'   => 'nullable|numeric|min:0|max:100',
                    'minimum_order_qty'     => 'nullable|integer|min:1|max:1000',
                    'max_orders_per_person' => 'nullable|integer|min:0|max:1000',
                ]);
                $discountPct = (float) $request->input('discount_percentage', 0);
                DB::table('products_data')->where('id', $id)->update([
                    'discount_percentage'   => (string) $discountPct,
                    'on_sale'               => $discountPct > 0,
                    'minimum_order_qty'     => (int) $request->input('minimum_order_qty', 1),
                    'max_orders_per_person' => (int) $request->input('max_orders_per_person', 0),
                    'updated_at'            => $now,
                ]);
                if ($discountPct > 0) {
                    foreach (DB::table('product_variations')->where('product_id', $id)->get() as $v) {
                        $newSale = round((float)$v->regular_price * (1 - $discountPct / 100), 2);
                        DB::table('product_variations')->where('id', $v->id)->update(['sale_price' => $newSale, 'price' => $newSale, 'updated_at' => $now]);
                    }
                }
                break;

            case 'variations':
                $hasVariations = $request->boolean('has_variations');
                $rows = []; $totalStock = 0;
                if (!$hasVariations) {
                    $reg   = (float) $request->input('regular_price', 0);
                    $sale  = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;
                    $price = ($sale && $sale < $reg) ? $sale : $reg;
                    $stock = (int) $request->input('stock_quantity', 0);
                    $totalStock = $stock;
                    $rows[] = ['attributes' => '{}', 'price' => $price, 'regular_price' => $reg, 'sale_price' => $sale, 'stock_quantity' => $stock];
                } else {
                    foreach ((array) $request->input('colors', []) as $colorData) {
                        $colorName = ucfirst(trim($colorData['name'] ?? ''));
                        if (!$colorName) continue;
                        $sizes    = array_filter(array_map('trim', (array)($colorData['sizes'] ?? [])));
                        $priceMap = (array)($colorData['price_map'] ?? []);
                        $stockMap = (array)($colorData['stock'] ?? []);
                        foreach ($sizes as $size) {
                            $reg   = isset($priceMap[$size]) && $priceMap[$size] !== '' ? (float)$priceMap[$size] : 0;
                            $stock = isset($stockMap[$size]) ? (int)$stockMap[$size] : 0;
                            $totalStock += $stock;
                            $rows[] = ['_color' => $colorName, 'attributes' => json_encode(['Color' => $colorName, 'Size' => $size]), 'price' => $reg, 'regular_price' => $reg, 'sale_price' => $reg, 'stock_quantity' => $stock];
                        }
                    }
                }
                DB::table('product_variations')->where('product_id', $id)->delete();
                foreach ($rows as $i => $row) {
                    unset($row['_color']);
                    DB::table('product_variations')->insert(array_merge($row, ['product_id' => $id, 'main_variation' => $i === 0, 'images' => '[]', 'created_at' => $now, 'updated_at' => $now]));
                }
                DB::table('products_data')->where('id', $id)->update(['has_variations' => $hasVariations, 'stock_quantity' => $totalStock, 'stock_status' => $totalStock > 0 ? 'instock' : 'outofstock', 'updated_at' => $now]);
                break;

            case 'attributes':
                $attrs = [];
                foreach ((array) $request->input('prod_attributes', []) as $attr) {
                    $name = trim($attr['name'] ?? ''); $values = trim($attr['values'] ?? '');
                    if (!$name || !$values) continue;
                    $attrs[] = ['name' => $name, 'values' => array_values(array_filter(array_map('trim', explode(',', $values))))];
                }
                DB::table('products_data')->where('id', $id)->update(['attributes' => json_encode($attrs), 'updated_at' => $now]);
                break;

            case 'tags':
                $raw  = trim($request->input('tags_input', ''));
                $tags = $raw ? array_values(array_filter(array_map('trim', explode(',', $raw)))) : [];
                DB::table('products_data')->where('id', $id)->update(['tags' => json_encode($tags), 'updated_at' => $now]);
                break;

            case 'categories':
                DB::table('product_category')->where('product_id', $id)->delete();
                foreach (array_filter((array) $request->input('categories', [])) as $catId) {
                    DB::table('product_category')->insert(['product_id' => $id, 'category_id' => (int)$catId]);
                }
                break;

            case 'images':
                $existing = json_decode($product->images ?? '{}', true) ?: [];

                // Remove thumbnail
                if ($request->boolean('delete_thumbnail') && !empty($existing['thumbnail'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($existing['thumbnail']);
                    unset($existing['thumbnail']);
                }

                // Replace thumbnail
                if ($request->hasFile('thumbnail')) {
                    if (!empty($existing['thumbnail'])) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existing['thumbnail']);
                    }
                    $file = $request->file('thumbnail');
                    $name = \Illuminate\Support\Str::random(40) . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('products/thumbnails', $file, $name);
                    $existing['thumbnail'] = 'products/thumbnails/' . $name;
                }

                // Delete individual other_images
                foreach ((array) $request->input('delete_other_images', []) as $delPath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($delPath);
                    $existing['other_images'] = array_values(
                        array_filter($existing['other_images'] ?? [], fn($p) => $p !== $delPath)
                    );
                }

                // Add new other_images
                if ($request->hasFile('other_images')) {
                    if (!isset($existing['other_images'])) $existing['other_images'] = [];
                    foreach ($request->file('other_images') as $file) {
                        $name = \Illuminate\Support\Str::random(40) . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('products/other_images', $file, $name);
                        $existing['other_images'][] = 'products/other_images/' . $name;
                    }
                }

                // Delete individual natural_images
                foreach ((array) $request->input('delete_natural_images', []) as $delPath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($delPath);
                    $existing['natural_images'] = array_values(
                        array_filter($existing['natural_images'] ?? [], fn($p) => $p !== $delPath)
                    );
                }

                // Add new natural_images
                if ($request->hasFile('natural_images')) {
                    if (!isset($existing['natural_images'])) $existing['natural_images'] = [];
                    foreach ($request->file('natural_images') as $file) {
                        $name = \Illuminate\Support\Str::random(40) . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('products/natural_images', $file, $name);
                        $existing['natural_images'][] = 'products/natural_images/' . $name;
                    }
                }

                DB::table('products_data')->where('id', $id)->update(['images' => json_encode($existing), 'updated_at' => $now]);
                break;

            case 'var_images':
                $varId = (int) $request->input('variation_id');
                $variation = DB::table('product_variations')
                    ->where('id', $varId)
                    ->where('product_id', $id)
                    ->first();
                if (!$variation) abort(404);

                $varImgs = is_string($variation->images)
                    ? (json_decode($variation->images, true) ?? [])
                    : (array) ($variation->images ?? []);

                foreach ((array) $request->input('delete_images', []) as $delPath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($delPath);
                    $varImgs = array_values(array_filter($varImgs, fn($p) => $p !== $delPath));
                }

                if ($request->hasFile('new_images')) {
                    $varAttrs  = is_string($variation->attributes)
                        ? (json_decode($variation->attributes, true) ?? [])
                        : (array) ($variation->attributes ?? []);
                    $colorName = $varAttrs['Color'] ?? 'default';
                    $folder    = 'products/variations/' . \Illuminate\Support\Str::slug($colorName);
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($folder);
                    foreach ($request->file('new_images') as $file) {
                        if ($file && $file->isValid()) {
                            $fname = \Illuminate\Support\Str::random(40) . '.jpg';
                            \Illuminate\Support\Facades\Storage::disk('public')->putFileAs($folder, $file, $fname);
                            $varImgs[] = $folder . '/' . $fname;
                        }
                    }
                }

                DB::table('product_variations')->where('id', $varId)->update([
                    'images'     => json_encode(array_values($varImgs)),
                    'updated_at' => $now,
                ]);
                break;

            case 'whatsapp':
                $available = $request->boolean('whatsapp_available');
                $number    = trim($request->input('whatsapp_number', ''));
                DB::table('products_data')->where('id', $id)->update([
                    'whatsapp'   => json_encode(['whatsapp' => ['available' => $available, 'number' => $available ? $number : null]]),
                    'updated_at' => $now,
                ]);
                break;

            case 'coupon':
                $couponId = (int) $request->input('coupon_id');
                // Remove this product from all coupons first
                foreach (DB::table('coupons')->get(['id', 'product_ids']) as $c) {
                    $cpids = json_decode($c->product_ids ?? '[]', true) ?? [];
                    $cpids = array_values(array_filter(array_map('intval', $cpids), fn($p) => $p !== $id));
                    DB::table('coupons')->where('id', $c->id)->update(['product_ids' => json_encode($cpids)]);
                }
                // Add to the selected coupon
                if ($couponId > 0) {
                    $coupon = DB::table('coupons')->where('id', $couponId)->first();
                    if ($coupon) {
                        $cpids = json_decode($coupon->product_ids ?? '[]', true) ?? [];
                        $cpids = array_values(array_map('intval', $cpids));
                        if (!in_array($id, $cpids)) $cpids[] = $id;
                        DB::table('coupons')->where('id', $couponId)->update(['product_ids' => json_encode($cpids)]);
                    }
                }
                break;
        }

        return redirect()->route('admin.products.show', $id)
            ->with('success', 'Section updated successfully.');
    }

    // ── ANALYTICS ──────────────────────────────────────────────────
    public function analytics()
    {
        $kpis = [
            'total_revenue'    => DB::table('orders')->whereNotIn('status', ['cancelled','refunded','failed'])->sum('final_total'),
            'total_orders'     => DB::table('orders')->count(),
            'total_users'      => DB::table('users')->count(),
            'avg_order_value'  => DB::table('orders')->avg('final_total') ?? 0,
            'completed_orders' => DB::table('orders')->where('status', 'completed')->count(),
            'cancelled_orders' => DB::table('orders')->whereIn('status', ['cancelled','refunded','failed'])->count(),
        ];

        $user_growth = DB::table('users')
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', created_at), 'Mon YY') as month, COUNT(*) as cnt")
            ->whereNotNull('created_at')
            ->groupByRaw("DATE_TRUNC('month', created_at)")
            ->orderByRaw("DATE_TRUNC('month', created_at)")
            ->get();

        $order_statuses = DB::table('orders')
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->get();

        $revenue_by_month = DB::table('orders')
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', date_created), 'Mon YY') as month, SUM(final_total) as revenue")
            ->whereNotNull('date_created')
            ->groupByRaw("DATE_TRUNC('month', date_created)")
            ->orderByRaw("DATE_TRUNC('month', date_created)")
            ->get();

        $payment_methods = DB::table('orders')
            ->selectRaw('COALESCE(payment_method_title, \'Unknown\') as method, COUNT(*) as cnt')
            ->groupBy('payment_method_title')
            ->get();

        $top_products = DB::table('products_data')
            ->orderByDesc('min_price')
            ->limit(8)
            ->get(['id', 'name', 'min_price', 'max_price', 'stock_status', 'acceptance_status', 'total_sales']);

        $user_roles = DB::table('users')
            ->selectRaw('role, COUNT(*) as cnt')
            ->groupBy('role')
            ->orderByDesc('cnt')
            ->get()
            ->map(function ($r) {
                $r->label = trim(str_replace(['"', '[', ']', '\\'], '', $r->role)) ?: 'Unknown';
                return $r;
            });

        return view('admin.analytics', compact(
            'kpis', 'user_growth', 'order_statuses',
            'revenue_by_month', 'payment_methods', 'top_products', 'user_roles'
        ));
    }

    // ── COUPONS ────────────────────────────────────────────────────
    public function coupons(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $vendor = $request->input('vendor', '');

        $query = DB::table('coupons')
            ->leftJoin('vendor_users', 'coupons.vendor_id', '=', 'vendor_users.id')
            ->orderByDesc('coupons.id')
            ->select('coupons.*', 'vendor_users.shop_name as vendor_shop_name');
        if ($search) $query->whereRaw('LOWER(code) LIKE ?', ['%'.strtolower($search).'%']);
        if ($status) $query->where('status', $status);
        if ($vendor === 'global') $query->whereNull('vendor_id');
        if (is_numeric($vendor)) $query->where('vendor_id', (int) $vendor);

        $coupons = $query->paginate(20)->withQueryString();
        $vendors = DB::table('vendor_users')->orderBy('shop_name')->get(['id', 'shop_name']);
        return view('admin.coupons', compact('coupons', 'search', 'status', 'vendor', 'vendors'));
    }

    public function createCoupon(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|max:50',
            'amount'        => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,fixed_cart',
            'vendor_id'     => 'nullable|integer|exists:vendor_users,id',
        ]);

        $exists = DB::table('coupons')->whereRaw('LOWER(code) = ?', [strtolower($request->code)])->exists();
        if ($exists) return back()->with('error', 'Coupon code already exists.')->withInput();

        DB::table('coupons')->insert([
            'code'                        => strtoupper($request->code),
            'vendor_id'                   => $request->filled('vendor_id') ? (int) $request->vendor_id : null,
            'amount'                      => $request->amount,
            'discount_type'               => $request->discount_type,
            'status'                      => 'publish',
            'usage_count'                 => 0,
            'usage_limit'                 => $request->filled('usage_limit') ? (int)$request->usage_limit : null,
            'minimum_amount'              => $request->input('minimum_amount', 0),
            'maximum_amount'              => 0,
            'date_expires'                => $request->filled('date_expires') ? $request->date_expires : null,
            'date_created'                => now(),
            'date_created_gmt'            => now(),
            'date_modified'               => now(),
            'date_modified_gmt'           => now(),
            'individual_use'              => false,
            'free_shipping'               => false,
            'exclude_sale_items'          => false,
            'product_ids'                 => '[]',
            'excluded_product_ids'        => '[]',
            'product_categories'          => '[]',
            'excluded_product_categories' => '[]',
            'email_restrictions'          => '[]',
            'used_by'                     => '[]',
            'meta_data'                   => '[]',
        ]);

        return back()->with('success', 'Coupon '.strtoupper($request->code).' created.');
    }

    public function toggleCoupon(int $id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();
        if (!$coupon) abort(404);
        $newStatus = $coupon->status === 'publish' ? 'draft' : 'publish';
        DB::table('coupons')->where('id', $id)->update(['status' => $newStatus, 'date_modified' => now()]);
        return back()->with('success', 'Coupon '.($newStatus === 'publish' ? 'enabled' : 'disabled').'.');
    }

    public function deleteCoupon(int $id)
    {
        DB::table('coupons')->where('id', $id)->delete();
        return back()->with('success', 'Coupon deleted.');
    }

    // ── REFUND REQUESTS ────────────────────────────────────────────
    public function refunds(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $type   = $request->input('type', '');

        $query = DB::table('refund_requests as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.customer_id')
            ->leftJoin('vendor_users as v', 'v.id', '=', 'r.vendor_id')
            ->orderByDesc('r.id')
            ->select('r.*', 'u.name as customer_name', 'u.email as customer_email', 'v.shop_name as vendor_shop_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(r.order_id AS TEXT) LIKE ?', ['%'.$search.'%'])
                  ->orWhereRaw('LOWER(u.name) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(u.email) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }
        if ($status) $query->where('r.status', $status);
        if ($type)   $query->where('r.type', $type);

        $refunds = $query->paginate(20)->withQueryString();
        return view('admin.refunds', compact('refunds', 'search', 'status', 'type'));
    }

    public function showRefund(int $id)
    {
        $refund = DB::table('refund_requests as r')
            ->where('r.id', $id)
            ->leftJoin('users as u', 'u.id', '=', 'r.customer_id')
            ->leftJoin('vendor_users as v', 'v.id', '=', 'r.vendor_id')
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->first([
                'r.*',
                'u.name as customer_name', 'u.email as customer_email', 'u.phone as customer_phone',
                'v.shop_name as vendor_shop_name',
                'o.final_total as order_total',
            ]);

        if (! $refund) abort(404);
        return view('admin.refunds.show', compact('refund'));
    }

    public function updateRefund(Request $request, int $id)
    {
        $request->validate([
            'status'     => 'required|in:pending,approved,rejected,completed',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        DB::table('refund_requests')->where('id', $id)->update([
            'status'     => $request->status,
            'admin_note' => $request->admin_note,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Refund request updated to '.ucfirst($request->status).'.');
    }

    // ── REVIEWS ────────────────────────────────────────────────────
    public function reviews(Request $request)
    {
        $search   = $request->input('search', '');
        $rating   = $request->input('rating', '');
        $approved = $request->input('approved', '');

        $query = DB::table('product_reviews')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->leftJoin('products_data', 'product_reviews.product_id', '=', 'products_data.id')
            ->orderByDesc('product_reviews.id')
            ->select(
                'product_reviews.*',
                'users.name as user_name',
                'products_data.name as product_name'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(product_reviews.body) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(product_reviews.title) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }
        if ($rating)            $query->where('product_reviews.rating', (int)$rating);
        if ($approved === '1')  $query->where('product_reviews.approved', true);
        if ($approved === '0')  $query->where('product_reviews.approved', false);

        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews', compact('reviews', 'search', 'rating', 'approved'));
    }

    public function toggleReview(int $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) abort(404);
        DB::table('product_reviews')->where('id', $id)->update(['approved' => !$review->approved, 'updated_at' => now()]);
        return back()->with('success', 'Review '.(!$review->approved ? 'approved' : 'unapproved').'.');
    }

    public function deleteReview(int $id)
    {
        DB::table('product_reviews')->where('id', $id)->delete();
        return back()->with('success', 'Review deleted.');
    }

    // ── DEVICES ────────────────────────────────────────────────────
    public function devices(Request $request)
    {
        $search  = $request->input('search', '');
        $blocked = $request->input('blocked', '');

        $query = DB::table('device_access_tokens')->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(device_id) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(identifier) LIKE ?', ['%'.strtolower($search).'%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }
        if ($blocked === '1') $query->where('blocked', 1);
        if ($blocked === '0') $query->where('blocked', 0);

        $devices = $query->paginate(20)->withQueryString();

        return view('admin.devices', compact('devices', 'search', 'blocked'));
    }

    public function blockDevice(int $id)
    {
        DB::table('device_access_tokens')->where('id', $id)->update(['blocked' => 1, 'updated_at' => now()]);
        return back()->with('success', 'Device blocked.');
    }

    public function unblockDevice(int $id)
    {
        DB::table('device_access_tokens')->where('id', $id)->update(['blocked' => 0, 'updated_at' => now()]);
        return back()->with('success', 'Device unblocked.');
    }

    public function deleteDevice(int $id)
    {
        DB::table('device_access_tokens')->where('id', $id)->delete();
        return back()->with('success', 'Device token deleted.');
    }

    public function blockDeviceByDeviceId(Request $request)
    {
        $deviceId = $request->input('device_id');
        if (!$deviceId) return back()->with('error', 'Device ID required.');
        $count = DB::table('device_access_tokens')->where('device_id', $deviceId)->update(['blocked' => 1, 'updated_at' => now()]);
        return back()->with('success', "All tokens for device [{$deviceId}] blocked ({$count} tokens).");
    }
}
