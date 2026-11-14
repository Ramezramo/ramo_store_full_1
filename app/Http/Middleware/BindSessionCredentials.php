<?php

namespace App\Http\Middleware;

use App\Models\VendorUser;
use Closure;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** A browser session may not outlive the credentials that originally created it. */
class BindSessionCredentials
{
    private const GUARDS = ['web', 'vendor_web'];

    public static function sessionKey(string $guard): string
    {
        return 'auth_password_fingerprint_'.$guard;
    }

    public static function recordLogin(Login $event): void
    {
        if (! in_array($event->guard, self::GUARDS, true) || ! request()->hasSession()) return;

        // Registered, password, OTP, Google and remembered logins all fire Login.
        request()->session()->put(self::sessionKey($event->guard), self::fingerprint($event->guard, $event->user));
    }

    private static function fingerprint(string $guard, Authenticatable $user): string
    {
        return hash_hmac('sha256', $guard.'|'.$user->getAuthIdentifier().'|'.(string) $user->getAuthPassword(), (string) config('app.key'));
    }

    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasSession()) return $next($request);

        foreach (self::GUARDS as $name) {
            $guard = Auth::guard($name);
            if (! $guard instanceof SessionGuard) continue;
            $storedId = $request->session()->get($guard->getName());
            $hasRecaller = $request->cookies->has($guard->getRecallerName());
            // actingAs/in-process guards without an actual login session are not
            // persisted browser authentication. Never invent a baseline for them.
            if ($storedId === null && ! $hasRecaller) continue;

            $user = $guard->user();
            if (! $user && $storedId === null) {
                $guard->logoutCurrentDevice();
                $request->session()->forget(self::sessionKey($name));
                continue;
            }
            $fresh = $user?->fresh();
            $binding = $request->session()->get(self::sessionKey($name));
            $currentId = $request->session()->get($guard->getName());
            if (! $fresh || (string) $currentId !== (string) $fresh->getAuthIdentifier()
                || $fresh->is_blocked || ($fresh instanceof VendorUser && $fresh->status !== 'approved')
                || ! is_string($binding) || ! hash_equals(self::fingerprint($name, $fresh), $binding)) {
                return $this->requireFreshLogin($request, $guard, $name);
            }

            // SessionGuard validates the remember token; also bind its password
            // fingerprint so a copied cookie cannot survive a password change.
            if ($guard->viaRemember()) {
                $parts = explode('|', (string) $request->cookies->get($guard->getRecallerName(), ''));
                $cookieHash = $parts[2] ?? '';
                if ($cookieHash === '' || ! hash_equals($guard->hashPasswordForCookie((string) $fresh->getAuthPassword()), $cookieHash)) {
                    return $this->requireFreshLogin($request, $guard, $name);
                }
            }
        }

        // Do not refresh this binding after a controller changes credentials.
        // The old session must prove ownership again on its next request.
        return $next($request);
    }

    private function requireFreshLogin(Request $request, SessionGuard $guard, string $name)
    {
        $locale = $request->session()->get('locale', 'en');
        $guard->logoutCurrentDevice();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put(['locale' => $locale, 'locale_source' => 'manual']);
        $message = $locale === 'ar' ? 'سجّل دخول تاني عشان تكمّل بأمان.' : 'Please sign in again to continue securely.';
        $target = route($name === 'vendor_web' ? 'vendor.login' : 'login');
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'redirect' => $target], 401);
        }

        return redirect()->to($target)->withErrors(['email' => $message]);
    }
}
