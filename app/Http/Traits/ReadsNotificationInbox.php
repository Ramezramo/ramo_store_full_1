<?php

namespace App\Http\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ReadsNotificationInbox
{
    protected function cachedUnreadCount(Request $request): int
    {
        $count = fn () => $this->unreadCount($request);
        return DB::transactionLevel() > 0
            ? $count()
            : Cache::remember($this->unreadCacheKey($request), now()->addSeconds(25), $count);
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

    private function inbox(Request $request): Builder
    {
        return DB::table('order_notifications')
            ->where('customer_id', $request->user()->getAuthIdentifier())
            ->whereExists(fn (Builder $order) => $order->selectRaw('1')->from('orders')
                ->whereColumn('orders.id', 'order_notifications.order_id')
                ->whereColumn('orders.customer_id', 'order_notifications.customer_id'));
    }

    private function vendorInbox(Request $request): Builder
    {
        return DB::table('vendor_application_notifications')->where('user_id', $request->user()->getAuthIdentifier());
    }
}
