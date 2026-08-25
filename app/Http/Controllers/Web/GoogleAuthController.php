<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\CartTrait;
use App\Models\User;
use App\Helpers\AuthConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    use CartTrait;
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

        $state = Str::random(16);
        session(['google_oauth_state' => $state]);

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
        $cfg = AuthConfig::get();

        if (!$cfg['google_login']) {
            return redirect()->route('login')->withErrors(['email' => 'Google login is disabled.']);
        }

        if ($request->input('state') !== session('google_oauth_state')) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid OAuth state. Please try again.']);
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not return an authorization code.']);
        }

        // Exchange code for token
        $tokenResponse = $this->exchangeCode($code);
        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            return redirect()->route('login')->withErrors(['email' => 'Failed to retrieve access token from Google.']);
        }

        // Fetch user info
        $profile = $this->fetchProfile($tokenResponse['access_token']);
        if (!$profile || empty($profile['email'])) {
            return redirect()->route('login')->withErrors(['email' => 'Failed to retrieve profile from Google.']);
        }
        if (filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
            return redirect()->route('login')->withErrors(['email' => 'Google email is not verified.']);
        }

        $googleId = $profile['sub'] ?? null;
        $email    = $profile['email'];
        $name     = $profile['name'] ?? '';
        $avatar   = $profile['picture'] ?? null;

        // Find existing user by google provider_id OR email
        $user = User::where('provider', 'google')->where('provider_id', $googleId)->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'provider'    => 'google',
                'provider_id' => $googleId,
                'avatar'      => $user->avatar ?: $avatar,
            ]);
        } elseif ($cfg['auto_register_google']) {
            $parts = explode(' ', $name, 2);
            $first = $parts[0];
            $last  = $parts[1] ?? '';

            $user = new User([
                'name'                => $name,
                'first_name'          => $first,
                'last_name'           => $last,
                'firstname'           => $first,
                'lastname'            => $last,
                'email'               => $email,
                'password'            => null,
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
            // Customer privileges are assigned by trusted server code, never mass assigned.
            $user->role = json_encode(['customer']);
            $user->capabilities = json_encode(['customer' => true]);
            $user->save();
        } else {
            return redirect()->route('login')->withErrors(['email' => 'No account found with this Google email.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestSessionOnLogin($user->id);

        return redirect()->intended(route('home'));
    }

    private function exchangeCode(string $code): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => config('services.google.redirect'),
                'grant_type'    => 'authorization_code',
            ]),
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true);
    }

    private function fetchProfile(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$accessToken}"],
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true);
    }
}
