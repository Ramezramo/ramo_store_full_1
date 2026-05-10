<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('account.profile');
        $authConfig = AuthConfig::get();
        return view('web.auth.login', compact('authConfig'));
    }

    public function login(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Capture guest session data before session regeneration
        $guestCart     = session('ramo_cart', []);
        $guestWishlist = session('ramo_wishlist', []);

        if (Auth::attempt(['email' => $r->email, 'password' => $r->password], $r->boolean('remember'))) {
            $r->session()->regenerate();
            $user = Auth::user();

            if (!$user->email_verified_at && !str_ends_with($user->email, '@ramostore.local')) {
                return redirect()->route('email.verify.notice');
            }

            // Merge guest cart into DB cart
            $this->mergeGuestCartToDb($user->id, $guestCart);

            // Merge guest wishlist into DB wishlist
            $this->mergeGuestWishlistToDb($user->id, $guestWishlist);

            // Clear session cart/wishlist (now stored in DB)
            session()->forget(['ramo_cart', 'ramo_wishlist', 'ramo_coupon']);

            return redirect()->intended(route('account.profile'));
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    private function mergeGuestCartToDb(int $userId, array $guestCart): void
    {
        if (empty($guestCart)) return;

        foreach ($guestCart as $item) {
            $existing = DB::table('cart_items')
                ->where('user_id', $userId)
                ->where('product_id', $item['product_id'])
                ->where('variation_id', $item['variation_id'] ?? null)
                ->first();

            if ($existing) {
                DB::table('cart_items')->where('id', $existing->id)->update([
                    'qty'        => min($existing->qty + $item['qty'], $item['stock'] ?? 999),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('cart_items')->insert([
                    'user_id'      => $userId,
                    'product_id'   => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'qty'          => $item['qty'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    private function mergeGuestWishlistToDb(int $userId, array $guestWishlist): void
    {
        if (empty($guestWishlist)) return;

        foreach ($guestWishlist as $productId) {
            $exists = DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();

            if (!$exists) {
                DB::table('wishlists')->insert([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('account.profile');
        return view('web.auth.register');
    }

    public function register(Request $r)
    {
        $r->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email|max:255',
            'phone'      => 'required|string|max:20',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $guestCart     = session('ramo_cart', []);
        $guestWishlist = session('ramo_wishlist', []);

        $user = User::create([
            'name'                => $r->first_name . ' ' . $r->last_name,
            'first_name'          => $r->first_name,
            'last_name'           => $r->last_name,
            'email'               => $r->email,
            'phone'               => $r->phone,
            'password'            => Hash::make($r->password),
            'role'                => 'normal_user',
            'nicename'            => strtolower(str_replace(' ', '-', $r->first_name . ' ' . $r->last_name)),
            'firstname'           => $r->first_name,
            'lastname'            => $r->last_name,
            'registered'          => now()->toDateTimeString(),
            'description'         => '',
            'capabilities'        => json_encode(['customer' => true]),
            'shipping'            => json_encode([]),
            'registration_method' => 'email_password',
            'email_verified_at'   => now(),
        ]);

        Auth::login($user);
        $r->session()->regenerate();

        // Merge any guest session data into the new account
        $this->mergeGuestCartToDb($user->id, $guestCart);
        $this->mergeGuestWishlistToDb($user->id, $guestWishlist);
        session()->forget(['ramo_cart', 'ramo_wishlist', 'ramo_coupon']);

        return redirect()->intended(route('account.profile'));
    }

    public function logout(Request $r)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            // Clear the user's cart and wishlist from the database on logout
            DB::table('cart_items')->where('user_id', $userId)->delete();
            DB::table('wishlists')->where('user_id', $userId)->delete();
        }

        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            $u       = Auth::user();
            $isAdmin = $u->role === 'admin' || $u->email === 'adminramoui@gmail.com' || str_contains((string) $u->role, 'admin');
            if ($isAdmin) return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function adminLogin(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::check()) {
            $existing        = Auth::user();
            $existingIsAdmin = $existing->role === 'admin' || $existing->email === 'adminramoui@gmail.com' || str_contains((string) $existing->role, 'admin');
            if (!$existingIsAdmin) {
                Auth::logout();
                $r->session()->invalidate();
                $r->session()->regenerateToken();
            }
        }

        if (Auth::attempt(['email' => $r->email, 'password' => $r->password])) {
            $user    = Auth::user();
            $isAdmin = $user->role === 'admin' || $user->email === 'adminramoui@gmail.com' || str_contains((string) $user->role, 'admin');

            if ($isAdmin) {
                $r->session()->regenerate();
                return redirect()->route('admin.dashboard');
            }

            Auth::logout();
            return back()->withErrors(['email' => 'This account does not have admin access.'])->withInput();
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }
}
