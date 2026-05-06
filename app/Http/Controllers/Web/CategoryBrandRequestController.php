<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryBrandRequestController extends Controller
{
    public function index()
    {
        $vendor = auth()->guard('vendor_web')->user();
        $requests = DB::table('category_brand_requests')
            ->where('vendor_user_id', $vendor->id)
            ->orderByDesc('id')
            ->paginate(20);

        return view('web.vendor.requests.index', compact('requests'));
    }

    public function create()
    {
        return view('web.vendor.requests.create');
    }

    public function store(Request $request)
    {
        $vendor = auth()->guard('vendor_web')->user();

        $validated = $request->validate([
            'type'        => 'required|in:category,brand',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $duplicate = DB::table('category_brand_requests')
            ->where('vendor_user_id', $vendor->id)
            ->where('type', $validated['type'])
            ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
            ->where('status', 'pending')
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'You already have a pending request for this ' . $validated['type'] . ' name.');
        }

        DB::table('category_brand_requests')->insert([
            'type'          => $validated['type'],
            'name'          => trim($validated['name']),
            'description'   => $validated['description'] ?? null,
            'status'        => 'pending',
            'vendor_user_id'=> $vendor->id,
            'vendor_name'   => $vendor->shop_name ?? $vendor->name,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('vendor.requests')->with('success', 'Your request has been submitted and is pending admin approval.');
    }
}
