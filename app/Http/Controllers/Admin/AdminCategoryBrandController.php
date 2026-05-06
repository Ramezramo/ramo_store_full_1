<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCategoryBrandController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->input('tab', 'categories');
        $status = $request->input('status', 'pending');
        $type   = $request->input('type', '');

        $reqQuery = DB::table('category_brand_requests')->orderByDesc('id');
        if ($status) $reqQuery->where('status', $status);
        if ($type)   $reqQuery->where('type', $type);
        $requests = $reqQuery->paginate(30)->withQueryString();

        $counts = [
            'pending'  => DB::table('category_brand_requests')->where('status', 'pending')->count(),
            'approved' => DB::table('category_brand_requests')->where('status', 'approved')->count(),
            'rejected' => DB::table('category_brand_requests')->where('status', 'rejected')->count(),
        ];

        $allCategories = DB::table('categories2')->orderBy('menu_order')->orderBy('name')->get();
        $parentCats    = $allCategories->filter(fn($c) => $c->parent == 0 || $c->parent === null)->values();
        $childCats     = $allCategories->filter(fn($c) => $c->parent > 0)->groupBy('parent');

        $catCounts = DB::table('product_category as pc')
            ->join('products_data as p', 'p.id', '=', 'pc.product_id')
            ->select('pc.category_id', DB::raw('count(*) as cnt'))
            ->groupBy('pc.category_id')
            ->pluck('cnt', 'category_id');

        $brands = DB::table('brands')->orderBy('name')->get();

        $brandCounts = DB::table('products_data')
            ->whereNotNull('brand_id')
            ->select('brand_id', DB::raw('count(*) as cnt'))
            ->groupBy('brand_id')
            ->pluck('cnt', 'brand_id');

        return view('admin.category-brand-requests.index', compact(
            'tab', 'requests', 'status', 'type', 'counts',
            'allCategories', 'parentCats', 'childCats', 'catCounts',
            'brands', 'brandCounts'
        ));
    }

    // ── Vendor request: Approve ───────────────────────────────
    public function approve(Request $request, int $id)
    {
        $req = DB::table('category_brand_requests')->where('id', $id)->first();
        if (!$req) return back()->with('error', 'Request not found.');
        if ($req->status !== 'pending') return back()->with('error', 'Already processed.');

        $note     = $request->input('admin_note', '');
        $parentId = $request->input('parent_category_id') ?: ($req->parent_category_id ?: null);

        if ($req->type === 'category') {
            $exists = DB::table('categories2')->whereRaw('LOWER(name) = ?', [strtolower($req->name)])->exists();
            if (!$exists) {
                DB::table('categories2')->insertGetId([
                    'name'        => $req->name,
                    'slug'        => Str::slug($req->name),
                    'parent'      => $parentId ?? 0,
                    'display'     => 'visible',
                    'menu_order'  => 0,
                    'count'       => 0,
                    'has_children'=> 0,
                    'description' => $req->description ?? '',
                ]);
                if ($parentId) {
                    DB::table('categories2')->where('id', $parentId)->update(['has_children' => 1]);
                }
            }
        } else {
            $exists = DB::table('brands')->whereRaw('LOWER(name) = ?', [strtolower($req->name)])->exists();
            if (!$exists) {
                DB::table('brands')->insert(['name' => $req->name]);
            }
        }

        DB::table('category_brand_requests')->where('id', $id)->update([
            'status'     => 'approved',
            'admin_note' => $note,
            'updated_at' => now(),
        ]);

        return back()->with('success', ucfirst($req->type) . ' "' . $req->name . '" approved and added.');
    }

    // ── Vendor request: Reject ────────────────────────────────
    public function reject(Request $request, int $id)
    {
        $req = DB::table('category_brand_requests')->where('id', $id)->first();
        if (!$req) return back()->with('error', 'Request not found.');
        if ($req->status !== 'pending') return back()->with('error', 'Already processed.');

        DB::table('category_brand_requests')->where('id', $id)->update([
            'status'     => 'rejected',
            'admin_note' => $request->input('admin_note', ''),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Request rejected.');
    }

    // ── Category: Create ──────────────────────────────────────
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $name     = trim($request->name);
        $parentId = $request->input('parent_id') ?: null;
        $exists   = DB::table('categories2')->whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
        if ($exists) return back()->with('error', "Category \"$name\" already exists.")->with('tab', 'categories');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        DB::table('categories2')->insert([
            'name'        => $name,
            'slug'        => Str::slug($name),
            'parent'      => $parentId ?? 0,
            'display'     => 'visible',
            'menu_order'  => (int) $request->input('menu_order', 0),
            'count'       => 0,
            'has_children'=> 0,
            'description' => $request->input('description', ''),
            'image'       => $imagePath,
        ]);

        if ($parentId) {
            DB::table('categories2')->where('id', $parentId)->update(['has_children' => 1]);
        }

        return back()->with('success', "Category \"$name\" created.")->with('tab', 'categories');
    }

    // ── Category: Update ──────────────────────────────────────
    public function updateCategory(Request $request, int $id)
    {
        $request->validate([
            'name'  => 'required|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $name     = trim($request->name);
        $parentId = $request->input('parent_id') ?: null;
        $old      = DB::table('categories2')->where('id', $id)->first();
        if (!$old) return back()->with('error', 'Category not found.')->with('tab', 'categories');

        $imagePath = $old->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('categories', 'public');
        } elseif ($request->input('remove_image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        DB::table('categories2')->where('id', $id)->update([
            'name'        => $name,
            'slug'        => Str::slug($name),
            'parent'      => $parentId ?? 0,
            'menu_order'  => (int) $request->input('menu_order', $old->menu_order ?? 0),
            'description' => $request->input('description', $old->description ?? ''),
            'image'       => $imagePath,
        ]);

        if ($old->parent && $old->parent != ($parentId ?? 0)) {
            $hasOtherChildren = DB::table('categories2')->where('parent', $old->parent)->where('id', '!=', $id)->exists();
            if (!$hasOtherChildren) {
                DB::table('categories2')->where('id', $old->parent)->update(['has_children' => 0]);
            }
        }
        if ($parentId) {
            DB::table('categories2')->where('id', $parentId)->update(['has_children' => 1]);
        }

        return back()->with('success', 'Category updated.')->with('tab', 'categories');
    }

    // ── Category: Delete ──────────────────────────────────────
    public function destroyCategory(Request $request, int $id)
    {
        $cat = DB::table('categories2')->where('id', $id)->first();
        if (!$cat) return back()->with('error', 'Category not found.')->with('tab', 'categories');

        $childCount = DB::table('categories2')->where('parent', $id)->count();
        if ($childCount > 0 && !$request->input('force')) {
            return back()->with('error', "Cannot delete \"{$cat->name}\" — it has {$childCount} sub-categories. Use force delete.")->with('tab', 'categories');
        }

        if ($childCount > 0) DB::table('categories2')->where('parent', $id)->update(['parent' => 0]);
        DB::table('product_category')->where('category_id', $id)->delete();
        if ($cat->image) Storage::disk('public')->delete($cat->image);
        DB::table('categories2')->where('id', $id)->delete();

        if ($cat->parent > 0) {
            if (!DB::table('categories2')->where('parent', $cat->parent)->exists()) {
                DB::table('categories2')->where('id', $cat->parent)->update(['has_children' => 0]);
            }
        }

        return back()->with('success', "Category \"{$cat->name}\" deleted.")->with('tab', 'categories');
    }

    // ── Brand: Create ─────────────────────────────────────────
    public function storeBrand(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $name   = trim($request->name);
        $exists = DB::table('brands')->whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
        if ($exists) return back()->with('error', "Brand \"$name\" already exists.")->with('tab', 'brands');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('brands', 'public');
        }

        DB::table('brands')->insert(['name' => $name, 'image' => $imagePath]);
        return back()->with('success', "Brand \"$name\" created.")->with('tab', 'brands');
    }

    // ── Brand: Update ─────────────────────────────────────────
    public function updateBrand(Request $request, int $id)
    {
        $request->validate([
            'name'  => 'required|string|max:120',
            'image' => 'nullable|image|max:2048',
        ]);

        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) return back()->with('error', 'Brand not found.')->with('tab', 'brands');

        $imagePath = $brand->image ?? null;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('brands', 'public');
        } elseif ($request->input('remove_image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        DB::table('brands')->where('id', $id)->update(['name' => trim($request->name), 'image' => $imagePath]);
        return back()->with('success', 'Brand updated.')->with('tab', 'brands');
    }

    // ── Brand: Delete ─────────────────────────────────────────
    public function destroyBrand(Request $request, int $id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) return back()->with('error', 'Brand not found.')->with('tab', 'brands');
        if ($brand->image ?? null) Storage::disk('public')->delete($brand->image);
        DB::table('brands')->where('id', $id)->delete();
        return back()->with('success', "Brand \"{$brand->name}\" deleted.")->with('tab', 'brands');
    }
}
