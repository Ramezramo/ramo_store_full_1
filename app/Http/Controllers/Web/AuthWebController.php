<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use App\Http\Traits\CartTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthWebController extends Controller
{
    use CartTrait;

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

        if (Auth::attempt(['email' => $r->email, 'password' => $r->password], $r->boolean('remember'))) {
            $r->session()->regenerate();
            $user = Auth::user();

            if (AuthConfig::val('require_email_verification', false) && !$user->email_verified_at && !str_ends_with($user->email, '@ramostore.local')) {
                return redirect()->route('email.verify.notice');
            }

            $this->mergeGuestSessionOnLogin($user->id);

            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
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

        $this->mergeGuestSessionOnLogin($user->id);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $r)
    {
        $locale = $r->session()->get('locale', 'en');

        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        $r->session()->put('locale', $locale);

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
