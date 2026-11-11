<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderNotificationController extends Controller
{
    public function index(Request $request)
    {
        $inbox = $this->inbox($request);
        $unread = (clone $inbox)->whereNull('read_at')->count() + $this->vendorInbox($request)->whereNull('read_at')->count();
        $notifications = $inbox->orderByDesc('created_at')->orderByDesc('id')->paginate(20);
        $vendorNotifications = $this->vendorInbox($request)->orderByDesc('created_at')->orderByDesc('id')->limit(20)->get();

        return response()->view('web.account.notifications', compact('notifications', 'vendorNotifications', 'unread'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function unread(Request $request)
    {
        $unread = $this->inbox($request)->whereNull('read_at')->count() + $this->vendorInbox($request)->whereNull('read_at')->count();

        return response()->json(['unread' => $unread])->header('Cache-Control', 'private, no-store');
    }

    public function markAllRead(Request $request)
    {
        $this->inbox($request)->whereNull('read_at')->update(['read_at' => now()]);
        $this->vendorInbox($request)->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('account.notifications');
    }

    public function openVendor(Request $request, int $notification)
    {
        $entry = $this->vendorInbox($request)->where('id', $notification);
        abort_unless((clone $entry)->exists(), 404);
        $entry->whereNull('read_at')->update(['read_at' => now()]);
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
