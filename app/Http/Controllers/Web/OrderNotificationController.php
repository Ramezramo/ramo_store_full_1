<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderNotificationController extends Controller
{
    public function index(Request $request)
    {
        $orders = $this->inbox($request)->select('id', 'status', 'read_at', 'event', 'order_id', 'created_at');
        $vendor = $this->vendorInbox($request)->select('id', 'status', 'read_at')
            ->selectRaw("'vendor_application' as event, null as order_id, created_at AT TIME ZONE 'UTC' as created_at");
        $notifications = DB::query()->fromSub($orders->unionAll($vendor), 'inbox')
            ->orderByRaw('created_at DESC NULLS LAST')->orderByDesc('id')->orderBy('event')->paginate(20);
        $unread = $this->unreadCount($request);

        return response()->view('web.account.notifications', compact('notifications', 'unread'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function unread(Request $request)
    {
        $count = fn () => $this->unreadCount($request);
        $unread = DB::transactionLevel() > 0
            ? $count()
            : Cache::remember($this->unreadCacheKey($request), now()->addSeconds(25), $count);

        return response()->json(['unread' => $unread])->header('Cache-Control', 'private, no-store');
    }

    public function markAllRead(Request $request)
    {
        DB::transaction(function () use ($request) {
            $readAt = now();
            $this->inbox($request)->whereNull('read_at')->update(['read_at' => $readAt]);
            $this->vendorInbox($request)->whereNull('read_at')->update(['read_at' => $readAt]);
        });

        Cache::forget($this->unreadCacheKey($request));

        return $request->expectsJson() ? $this->unread($request) : redirect()->route('account.notifications');
    }

    private function unreadCacheKey(Request $request): string
    {
        return 'notifications:unread:'.$request->user()->getAuthIdentifier();
    }

    private function unreadCount(Request $request): int
    {
        return $this->inbox($request)->whereNull('read_at')->count()
            + $this->vendorInbox($request)->whereNull('read_at')->count();
    }

    public function openVendor(Request $request, int $notification)
    {
        $entry = $this->vendorInbox($request)->where('id', $notification);
        abort_unless((clone $entry)->exists(), 404);
        $entry->whereNull('read_at')->update(['read_at' => now()]);
        Cache::forget($this->unreadCacheKey($request));
        return redirect()->route('vendor.register');
    }

    private function vendorInbox(Request $request): Builder
    {
        return DB::table('vendor_application_notifications')->where('user_id', $request->user()->getAuthIdentifier());
    }

    public function open(Request $request, int $notification)
    {
        $entry = $this->inbox($request)->where('id', $notification);
        $orderId = (clone $entry)->value('order_id');
        abort_if($orderId === null, 404);

        $entry->whereNull('read_at')->update(['read_at' => now()]);
        Cache::forget($this->unreadCacheKey($request));

        return redirect()->route('account.order', $orderId);
    }

    private function inbox(Request $request): Builder
    {
        return DB::table('order_notifications')
            ->where('customer_id', $request->user()->getAuthIdentifier())
            ->whereExists(fn (Builder $order) => $order->selectRaw('1')->from('orders')
                ->whereColumn('orders.id', 'order_notifications.order_id')
                ->whereColumn('orders.customer_id', 'order_notifications.customer_id'));
    }
}
