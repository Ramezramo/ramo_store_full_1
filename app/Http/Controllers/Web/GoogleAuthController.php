<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\CartTrait;
use App\Models\User;
use App\Helpers\AuthConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    use CartTrait;

    private const STATE_LIFETIME_SECONDS = 600;

    public function redirect()
    {
        $cfg = AuthConfig::get();

        if (!$cfg['google_login']) {
            return redirect()->route('login')->withErrors(['email' => 'Google login is disabled.']);
        }

        $clientId    = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (!$clientId || !$redirectUri) {
            return redirect()->route('login')->withErrors(['email' => 'Google login is not configured.']);
        }

        $state = Str::random(64);
        session(['google_oauth_state' => $state, 'google_oauth_issued_at' => now()->timestamp]);

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            'state'         => $state,
            'access_type'   => 'online',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    public function callback(Request $request)
    {
        // Consume even a denied/failed callback. Authorization responses cannot
        // be replayed or accepted without a login flow initiated by this browser.
        $expectedState = $request->session()->pull('google_oauth_state');
        $issuedAt = $request->session()->pull('google_oauth_issued_at');
        $state = $request->input('state');
        $cfg = AuthConfig::get();

        if (!$cfg['google_login']) {
            return redirect()->route('login')->withErrors(['email' => 'Google login is disabled.']);
        }

        if (! is_string($state) || $state === '' || ! is_string($expectedState) || $expectedState === ''
            || ! is_int($issuedAt) || $issuedAt > now()->timestamp
            || $issuedAt < now()->timestamp - self::STATE_LIFETIME_SECONDS
            || ! hash_equals($expectedState, $state)) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid OAuth state. Please try again.']);
        }

        // Two callbacks may have loaded the same session before either writes
        // its consumed state. An atomic claim also rejects that raced snapshot.
        if (! Cache::add('google.oauth.used.'.hash('sha256', $expectedState), true, self::STATE_LIFETIME_SECONDS)) {
            return redirect()->route('login')->withErrors(['email' => 'This Google sign-in response has already been used. Please start again.']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in was not completed. Please try again.']);
        }

        $code = $request->input('code');
        if (! is_string($code) || trim($code) === '' || strlen($code) > 4096) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not return an authorization code.']);
        }

        // Exchange code for token
        $tokenResponse = $this->exchangeCode($code);
        if (! $tokenResponse || ! is_string($tokenResponse['access_token'] ?? null) || $tokenResponse['access_token'] === '') {
            return redirect()->route('login')->withErrors(['email' => 'Failed to retrieve access token from Google.']);
        }

        // Fetch user info
        $profile = $this->fetchProfile($tokenResponse['access_token']);
        if (! $profile || ! is_string($profile['email'] ?? null)
            || strlen($profile['email']) > 255 || ! filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')->withErrors(['email' => 'Failed to retrieve profile from Google.']);
        }
        if (filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
            return redirect()->route('login')->withErrors(['email' => 'Google email is not verified.']);
        }

        $googleId = $profile['sub'] ?? null;
        if (! is_string($googleId) || $googleId === '' || $googleId !== trim($googleId)
            || strlen($googleId) > 255 || preg_match('/[\x00-\x1F\x7F]/', $googleId)) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not return a valid account identity. Please try again.']);
        }

        $email = strtolower($profile['email']);
        $name = is_string($profile['name'] ?? null) ? trim($profile['name']) : '';
        $name = $name !== '' ? mb_substr($name, 0, 255) : $email;
        $avatar = is_string($profile['picture'] ?? null) && filter_var($profile['picture'], FILTER_VALIDATE_URL)
            && parse_url($profile['picture'], PHP_URL_SCHEME) === 'https' ? $profile['picture'] : null;

        try {
            $user = DB::transaction(function () use ($googleId, $email, $name, $avatar, $cfg): User {
                // Provider identity is authoritative. An email match alone never
                // transfers a password account, tokens or referral attribution.
                $matches = User::where('provider', 'google')->where('provider_id', $googleId)->lockForUpdate()->limit(2)->get();
                if ($matches->count() > 1) {
                    throw ValidationException::withMessages(['email' => 'This Google identity needs account support before it can be used.']);
                }
                $user = $matches->first();
                $emailOwner = User::whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();
                if ($emailOwner && (! $user || ! $emailOwner->is($user))) {
                    throw ValidationException::withMessages(['email' => 'An account already uses this email. Sign in with its existing method, or use Forgot password to recover it. Google sign-in has not been linked.']);
                }
                if ($user) {
                    if ($user->is_blocked) {
                        throw ValidationException::withMessages(['email' => 'This account is unavailable. Please contact support.']);
                    }
                    $user->forceFill([
                        'email' => $email,
                        'email_verified_at' => now(),
                        'avatar' => $user->avatar ?: $avatar,
                    ])->save();
                    return $user;
                }
                if (! $cfg['auto_register_google']) {
                    throw ValidationException::withMessages(['email' => 'No linked Google account was found. Sign in with your existing account or contact support.']);
                }

                $parts = explode(' ', $name, 2);
                $user = new User([
                    'name'                => $name,
                    'first_name'          => $parts[0],
                    'last_name'           => $parts[1] ?? '',
                    'firstname'           => $parts[0],
                    'lastname'            => $parts[1] ?? '',
                    'email'               => $email,
                    'password'            => Str::random(64),
                    'nicename'            => Str::slug($name ?: $email),
                    'registered'          => now()->toDateTimeString(),
                    'description'         => '',
                    'shipping'            => json_encode([]),
                    'registration_method' => 'google',
                    'provider'            => 'google',
                    'provider_id'         => $googleId,
                    'avatar'              => $avatar,
                    'is_phone_verified'   => false,
                ]);
                // Google verified the address; this guarded field and privileges
                // are assigned only by trusted server code.
                $user->forceFill([
                    'email_verified_at' => now(),
                    'role' => json_encode(['customer']),
                    'capabilities' => json_encode(['customer' => true]),
                ])->save();
                return $user;
            });
        } catch (ValidationException $e) {
            return redirect()->route('login')->withErrors($e->errors());
        } catch (UniqueConstraintViolationException) {
            return redirect()->route('login')->withErrors(['email' => 'This sign-in could not be completed safely. Start Google sign-in again, or recover your existing account.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestSessionOnLogin($user->id);

        return redirect()->intended(route('home'));
    }

    private function exchangeCode(string $code): ?array
    {
        try {
            $response = Http::asForm()->acceptJson()->connectTimeout(5)->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'code'          => $code,
                    'client_id'     => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri'  => config('services.google.redirect'),
                    'grant_type'    => 'authorization_code',
                ]);
            $data = $response->json();
            return $response->successful() && is_array($data) ? $data : null;
        } catch (ConnectionException) {
            return null;
        }
    }

    private function fetchProfile(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)->acceptJson()->connectTimeout(5)->timeout(10)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');
            $data = $response->json();
            return $response->successful() && is_array($data) ? $data : null;
        } catch (ConnectionException) {
            return null;
        }
    }
}
