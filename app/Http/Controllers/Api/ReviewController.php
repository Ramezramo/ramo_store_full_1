<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }
    public function index($productId)
    {
        $reviews = DB::table('product_reviews as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.product_id', $productId)
            ->where('r.approved', true)
            ->select(
                'r.*',
                DB::raw("COALESCE(
                    NULLIF(TRIM(COALESCE(u.first_name,'') || ' ' || COALESCE(u.last_name,'')), ''),
                    NULLIF(TRIM(COALESCE(u.firstname,'') || ' ' || COALESCE(u.lastname,'')), ''),
                    u.name, 'Customer'
                ) as reviewer_name"),
                'u.avatar'
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        $avg = $reviews->avg('rating');
        $distribution = $reviews->groupBy('rating')->map->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'reviews'        => $reviews,
                'average_rating' => round($avg, 1),
                'total'          => $reviews->count(),
                'distribution'   => $distribution,
            ]
        ]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'product_id' => 'required|integer|exists:products_data,id',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'nullable|string|max:150',
            'body'       => 'required|string|min:5|max:1000',
        ]);

        $existing = DB::table('product_reviews')
            ->where('user_id', Auth::id())
            ->where('product_id', $r->product_id)
            ->exists();

        if ($existing) {
            return response()->json(['success' => false, 'message' => $this->localized('You already reviewed this product.', 'إنت قيّمت المنتج ده قبل كده.')], 422);
        }

        $verified = DB::table('orders')
            ->where('customer_id', Auth::id())
            ->whereRaw("line_items::text LIKE ?", ['%"product_id":' . $r->product_id . '%'])
            ->whereIn('general_order_status', ['completed'])
            ->exists();

        $id = DB::table('product_reviews')->insertGetId([
            'product_id'           => $r->product_id,
            'user_id'              => Auth::id(),
            'rating'               => $r->rating,
            'title'                => $r->title,
            'body'                 => $r->body,
            'approved'             => true,
            'is_verified_purchase' => $verified,
            'helpful_count'        => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return response()->json(['success' => true, 'message' => $this->localized('Review submitted!', 'تقييمك اتبعت!'), 'id' => $id], 201);
    }

    public function webStore(Request $r)
    {
        $r->validate([
            'product_id' => 'required|integer|exists:products_data,id',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'nullable|string|max:150',
            'body'       => 'required|string|min:5|max:1000',
        ]);

        $existing = DB::table('product_reviews')
            ->where('user_id', Auth::id())
            ->where('product_id', $r->product_id)
            ->exists();

        if ($existing) {
            return redirect()->route('product', $r->product_id)->with('error', $this->localized('You already reviewed this product.', 'إنت قيّمت المنتج ده قبل كده.'));
        }

        $verified = DB::table('orders')
            ->where('customer_id', Auth::id())
            ->whereRaw("line_items::text LIKE ?", ['%"product_id":' . $r->product_id . '%'])
            ->whereIn('general_order_status', ['completed'])
            ->exists();

        DB::table('product_reviews')->insert([
            'product_id'           => $r->product_id,
            'user_id'              => Auth::id(),
            'rating'               => $r->rating,
            'title'                => $r->title,
            'body'                 => $r->body,
            'approved'             => true,
            'is_verified_purchase' => $verified,
            'helpful_count'        => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return redirect()->route('product', $r->product_id)->with('success', $this->localized('Your review has been published!', 'تقييمك اتنشر!'));
    }

    public function destroy(Request $r, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) return response()->json(['success' => false, 'message' => $this->localized('Not found.', 'مش لاقيين التقييم ده.')], 404);

        // Must be the reviewer or admin
        $user    = Auth::user();
        $roles   = $user ? (is_array($user->role) ? $user->role : json_decode($user->role, true) ?? []) : [];
        $isAdmin = in_array('admin', $roles);
        $isOwner = Auth::check() && Auth::id() === (int)$review->user_id;

        if (!$isOwner && !$isAdmin) {
            return response()->json(['success' => false, 'message' => $this->localized('Unauthorized.', 'مش مسموحلك تعمل الإجراء ده.')], 403);
        }

        DB::table('product_reviews')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => $this->localized('Review deleted.', 'التقييم اتمسح.')]);
    }

    public function helpful(Request $r, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) return response()->json(['success' => false], 404);

        $voted = session('review_helpful_voted', []);
        if (in_array((int)$id, $voted)) {
            return response()->json(['success' => false, 'message' => $this->localized('Already voted.', 'إنت صوّت قبل كده.'), 'count' => $review->helpful_count]);
        }

        DB::table('product_reviews')->where('id', $id)->increment('helpful_count');
        $voted[] = (int)$id;
        session(['review_helpful_voted' => $voted]);

        return response()->json(['success' => true, 'count' => $review->helpful_count + 1]);
    }
}
