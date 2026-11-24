<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\CartTrait;
use App\Http\Traits\ReadsNotificationInbox;
use App\Http\Traits\WishlistTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontStateController extends Controller
{
    use CartTrait, ReadsNotificationInbox, WishlistTrait {
        WishlistTrait::mergeGuestWishlistToDb insteadof CartTrait;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user && ! $user instanceof User, 403);

        return response()->json([
            'cart' => ['count' => $this->cartLineCount()],
            'wishlist' => $this->wishlistState(),
            'unread' => $user ? $this->cachedUnreadCount($request) : 0,
        ])->header('Cache-Control', 'private, no-store');
    }
}
