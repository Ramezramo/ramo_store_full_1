<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminCategoryBrandController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $type   = $request->input('type', '');

        $query = DB::table('category_brand_requests')->orderByDesc('id');

        if ($status) $query->where('status', $status);
        if ($type)   $query->where('type', $type);

        $requests = $query->paginate(30)->withQueryString();

        $counts = [
            'pending'  => DB::table('category_brand_requests')->where('status', 'pending')->count(),
            'approved' => DB::table('category_brand_requests')->where('status', 'approved')->count(),
            'rejected' => DB::table('category_brand_requests')->where('status', 'rejected')->count(),
        ];

        $allCategories = DB::table('categories2')->orderBy('name')->get(['id', 'name', 'parent']);

        return view('admin.category-brand-requests.index', compact('requests', 'status', 'type', 'counts', 'allCategories'));
    }

    public function approve(Request $request, int $id)
    {
        $req = DB::table('category_brand_requests')->where('id', $id)->first();
        if (!$req) return back()->with('error', 'Request not found.');
        if ($req->status !== 'pending') return back()->with('error', 'This request has already been processed.');

        $note = $request->input('admin_note', '');
        $parentId = $request->input('parent_category_id') ?: ($req->parent_category_id ?: null);

        if ($req->type === 'category') {
            $slug = Str::slug($req->name);
            $exists = DB::table('categories2')->whereRaw('LOWER(name) = ?', [strtolower($req->name)])->exists();
            if (!$exists) {
                $newId = DB::table('categories2')->insertGetId([
                    'name'        => $req->name,
                    'slug'        => $slug,
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

        return back()->with('success', ucfirst($req->type) . ' "' . $req->name . '" approved and added to the system.');
    }

    public function reject(Request $request, int $id)
    {
        $req = DB::table('category_brand_requests')->where('id', $id)->first();
        if (!$req) return back()->with('error', 'Request not found.');
        if ($req->status !== 'pending') return back()->with('error', 'This request has already been processed.');

        DB::table('category_brand_requests')->where('id', $id)->update([
            'status'     => 'rejected',
            'admin_note' => $request->input('admin_note', ''),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Request rejected.');
    }
}
