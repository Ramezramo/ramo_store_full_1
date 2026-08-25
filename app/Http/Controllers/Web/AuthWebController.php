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
use App\Support\EgyptianPhoneNumber;

class AuthWebController extends Controller
{
    use CartTrait;

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }

    public function showLogin(Request $request)
    {
        if (Auth::check()) return redirect()->route('account.profile');
        if ($request->boolean('checkout')) {
            $request->session()->put('url.intended', route('checkout'));
        }
        $authConfig = AuthConfig::get();
        $referralInviterName = $this->referralInviterName($request);
        return view('web.auth.login', compact('authConfig', 'referralInviterName'));
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

        $otpAccount = User::query()
            ->where('email', $r->email)
            ->where('registration_method', 'phone_otp')
            ->exists();
        if ($otpAccount) {
            return back()->withErrors([
                'email' => $this->localized(
                    'This account uses phone sign-in. Use the phone OTP option, or set a password from your account profile first.',
                    'الحساب ده بيسجل دخول برقم الموبايل. استخدم كود OTP، أو اعمل كلمة سر من إعدادات حسابك الأول.'
                ),
            ])->withInput();
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) return redirect()->route('account.profile');

        $authConfig = AuthConfig::get();
        $referralInviterName = $this->referralInviterName($request);
        $referralQuery = $referralInviterName && $request->filled('ref')
            ? ['ref' => strtoupper(trim((string) $request->query('ref')))]
            : [];
        $phoneOtpEnabled = (bool) ($authConfig['phone_otp_login'] ?? false);
        $emailEnabled = (bool) ($authConfig['email_login'] ?? true);
        $googleEnabled = (bool) ($authConfig['google_login'] ?? false);

        // The registration link must enter the same customer auth method the
        // admin configured. OTP verification then continues to complete-profile.
        if ($phoneOtpEnabled) {
            return redirect()->route('login', $referralQuery);
        }

        if (! $phoneOtpEnabled && ! $emailEnabled && $googleEnabled && ($authConfig['auto_register_google'] ?? false)) {
            return redirect()->route('auth.google', $referralQuery);
        }

        return view('web.auth.register', compact('authConfig', 'referralInviterName'));
    }

    private function referralInviterName(Request $request): ?string
    {
        $code = strtoupper(trim((string) $request->query('ref', '')));
        if ($code === '') {
            return null;
        }

        $referrer = User::query()
            ->where('referral_code', $code)
            ->first(['first_name', 'name']);

        if (! $referrer) {
            return null;
        }

        $name = trim((string) ($referrer->first_name ?: $referrer->name));
        return $name !== '' ? $name : null;
    }

    public function register(Request $r)
    {
        $r->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email|max:255',
            'phone'      => [
                'required',
                'string',
                'max:20',
                'unique:users,phone',
                function ($attribute, $value, $fail) {
                    if (! EgyptianPhoneNumber::isValid((string) $value)) {
                        $fail('Please enter a valid Egyptian mobile number.');
                    }
                },
            ],
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $normalizedPhone = EgyptianPhoneNumber::normalize((string) $r->phone);
        if (! $normalizedPhone) {
            return back()->withErrors(['phone' => 'Please enter a valid Egyptian mobile number.'])->withInput();
        }
        if (User::query()->where('phone', $normalizedPhone)->exists()) {
            return back()->withErrors(['phone' => 'That phone number is already in use.'])->withInput();
        }

        $user = new User([
            'name'                => $r->first_name . ' ' . $r->last_name,
            'first_name'          => $r->first_name,
            'last_name'           => $r->last_name,
            'email'               => $r->email,
            'phone'               => $normalizedPhone,
            'password'            => Hash::make($r->password),
            'nicename'            => strtolower(str_replace(' ', '-', $r->first_name . ' ' . $r->last_name)),
            'firstname'           => $r->first_name,
            'lastname'            => $r->last_name,
            'registered'          => now()->toDateTimeString(),
            'description'         => '',
            'shipping'            => json_encode([]),
            'registration_method' => 'email_password',
            'email_verified_at'   => now(),
        ]);
        // Customer privileges are assigned by trusted server code, never mass assigned.
        $user->role = 'normal_user';
        $user->capabilities = json_encode(['customer' => true]);
        $user->save();

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
