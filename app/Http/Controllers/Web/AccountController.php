<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function hub()
    {
        $user = Auth::user();
        return view('web.account.hub', compact('user'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('web.account.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $hasPlaceholderEmail = str_ends_with($user->email ?? '', '@ramostore.local');

        $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'email'            => $hasPlaceholderEmail
                                    ? 'nullable|email|unique:users,email,'.$user->id
                                    : 'required|email|unique:users,email,'.$user->id,
            'phone'            => 'nullable|string|max:30',
            'current_password' => 'nullable|string',
            'new_password'     => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $resolvedEmail = $request->filled('email')
            ? $request->email
            : $user->email;

        $data = [
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name ?? '',
            'email'      => $resolvedEmail,
            'phone'      => $request->phone ?? '',
            'name'       => trim($request->first_name.' '.($request->last_name ?? '')),
        ];

        $isOtpUser = ($user->registration_method === 'phone_otp');

        if ($request->filled('new_password')) {
            if (!$isOtpUser) {
                if (! $request->filled('current_password') || ! Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
                }
            }
            $data['password'] = Hash::make($request->new_password);
            if ($isOtpUser) {
                $data['registration_method'] = 'email_password';
            }
        }

        DB::table('users')->where('id', $user->id)->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function orders()
    {
        $orders = DB::table('orders')
            ->where('customer_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('web.account.orders', compact('orders'));
    }

    public function reviews()
    {
        $reviews = DB::table('product_reviews as r')
            ->where('r.user_id', Auth::id())
            ->leftJoin('products_data as p', 'p.id', '=', 'r.product_id')
            ->orderByDesc('r.created_at')
            ->get(['r.id', 'r.rating', 'r.title', 'r.body', 'r.approved',
                   'r.created_at', 'r.helpful_count', 'r.is_verified_purchase',
                   'p.id as product_id', 'p.name as product_name', 'p.slug as product_slug']);

        return view('web.account.reviews', compact('reviews'));
    }

    public function orderDetail($id)
    {
        $order = DB::table('orders')
            ->where('id', $id)
            ->where('customer_id', Auth::id())
            ->first();

        if (! $order) abort(404);

        $lineItems = json_decode($order->line_items ?? '[]', true);
        $billing   = json_decode($order->billing   ?? '{}', true);

        // Load sub-orders with vendor info
        $subOrders = DB::table('order_sub_orders as s')
            ->where('s.parent_order_id', $id)
            ->leftJoin('vendor_users as v', 'v.id', '=', 's.vendor_id')
            ->select(['s.*', 'v.shop_name as vendor_shop_name', 'v.id as v_id'])
            ->orderBy('s.id')
            ->get()
            ->map(function ($sub) use ($id) {
                $sub->items    = json_decode($sub->line_items ?? '[]', true) ?: [];
                $sub->timeline = json_decode($sub->timeline ?? '[]', true) ?: [];
                $sub->step_index = match($sub->status) {
                    'pending' => 0,
                    'processing' => 1,
                    'shipped' => 2,
                    'delivered' => 3,
                    'completed' => 3,
                    default => 0,
                };
                // Load messages for this sub-order / vendor
                $sub->messages = DB::table('order_messages as m')
                    ->leftJoin('vendor_users as v', 'v.id', '=', 'm.vendor_id')
                    ->where('m.order_id', $id)
                    ->where(function ($q) use ($sub) {
                        $q->where('m.sub_order_id', $sub->id)
                          ->orWhere(function ($q2) use ($sub) {
                              $q2->whereNull('m.sub_order_id')
                                 ->where('m.vendor_id', $sub->vendor_id);
                          });
                    })
                    ->orderBy('m.id')
                    ->get(['m.*', 'v.shop_name as vendor_shop_name']);
                return $sub;
            });

        // Legacy: if no sub-orders, pass empty collection — view will fall back to lineItems
        $messages = collect(); // kept for backward compat if any view still uses it

        return view('web.account.order-detail', compact('order', 'lineItems', 'billing', 'subOrders', 'messages'));
    }
}
