<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\ReadsNotificationInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderNotificationController extends Controller
{
    use ReadsNotificationInbox;

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
        $unread = $this->cachedUnreadCount($request);

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

    public function openVendor(Request $request, int $notification)
    {
        $entry = $this->vendorInbox($request)->where('id', $notification);
        abort_unless((clone $entry)->exists(), 404);
        $entry->whereNull('read_at')->update(['read_at' => now()]);
        Cache::forget($this->unreadCacheKey($request));
        return redirect()->route('vendor.register');
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
}
