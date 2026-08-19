<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use App\Jobs\SendOtpSms;
use App\Http\Traits\CartTrait;
use App\Models\OtpVerification;
use App\Models\User;
use App\Services\Sms\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpAuthController extends Controller
{
    use CartTrait;
    private function localized(string $english, string $arabic): string
    {
        return session('locale') === 'ar' ? $arabic : $english;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+2' . $phone;
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

    private function generateOtp(int $length = 6): string
    {
        return str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
    }

    private function sendSms(string $phone, string $otp): bool
    {
        $message = 'Your Ramo Store OTP code is ' . $otp;
        try {
            return app(SmsGateway::class)->send($phone, $message);
        } catch (\Throwable $e) {
            Log::error('OTP SMS send failed', ['phone' => $phone, 'message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function sendOtp(Request $request)
    {
        $cfg = AuthConfig::get();

        if ($request->input('context') === 'checkout') {
            $request->session()->put('url.intended', route('checkout'));
        }

        if (!$cfg['phone_otp_login']) {
            return response()->json(['success' => false, 'message' => $this->localized('Phone OTP login is disabled.', 'تسجيل الدخول بكود الموبايل مش متاح دلوقتي.')], 403);
        }

        $request->validate(['phone' => 'required|string|min:9|max:20']);
        $phone = $this->normalizePhone($request->phone);

        if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
            return response()->json(['success' => false, 'message' => $this->localized('Please enter a valid phone number.', 'اكتب رقم موبايل صحيح.')], 422);
        }

        $maxResends     = (int) $cfg['max_resends_per_hour'];
        $cooldownSecs   = (int) $cfg['resend_cooldown_seconds'];
        $expiryMinutes  = (int) $cfg['otp_expiry_minutes'];
        $otpLength      = (int) $cfg['otp_length'];

        $existing = OtpVerification::where('phone', $phone)->where('verified', false)->latest()->first();

        if ($existing) {
            $ageSeconds = abs(now()->diffInSeconds($existing->created_at, false));
            if ($ageSeconds < $cooldownSecs) {
                $wait = $cooldownSecs - $ageSeconds;
                return response()->json(['success' => false, 'message' => $this->localized("Please wait {$wait} seconds before requesting a new OTP.", "استنى {$wait} ثانية قبل ما تطلب كود جديد."), 'wait' => $wait], 429);
            }

            $windowStart = $existing->resend_window_start ?? $existing->created_at;
            if (now()->diffInSeconds($windowStart) < 3600 && $existing->resend_count >= $maxResends) {
                return response()->json(['success' => false, 'message' => $this->localized('Too many OTP requests. Please try again in an hour.', 'طلبات كود كتير. جرّب تاني بعد ساعة.')], 429);
            }

            $newResendCount = $existing->resend_count + 1;
            $windowStart    = ($existing->resend_window_start && now()->diffInSeconds($existing->resend_window_start) < 3600)
                ? $existing->resend_window_start
                : now();
            $existing->delete();

            $otp = $this->generateOtp($otpLength);
            $otpVerification = OtpVerification::create([
                'phone'               => $phone,
                'otp_code'            => $otp,
                'expires_at'          => now()->addMinutes($expiryMinutes),
                'attempts'            => 0,
                'resend_count'        => $newResendCount,
                'resend_window_start' => $windowStart,
                'verified'            => false,
            ]);
        } else {
            $otp = $this->generateOtp($otpLength);
            $otpVerification = OtpVerification::create([
                'phone'        => $phone,
                'otp_code'     => $otp,
                'expires_at'   => now()->addMinutes($expiryMinutes),
                'attempts'     => 0,
                'resend_count' => 0,
                'verified'     => false,
            ]);
        }

        $isLogDriver = strtolower((string) config('sms.driver', 'log')) === 'log';

        if ($isLogDriver) {
            try {
                $this->sendSms($phone, $otp);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => $this->localized($e->getMessage(), 'حصلت مشكلة وإحنا بنبعت الكود. جرّب تاني.')], 500);
            }
        } else {
            // Real provider calls are intentionally asynchronous. The development
            // log driver remains synchronous so the visible OTP fallback is unchanged.
            SendOtpSms::dispatch($otpVerification->id)->afterCommit();
        }

        $response = ['success' => true, 'message' => $this->localized('OTP sent successfully.', 'الكود اتبعت بنجاح.'), 'expires_in' => $expiryMinutes * 60];

        // The preview is an explicit, default-off development aid. It is never
        // returned for a real SMS provider, and the production template keeps
        // OTP_DEVELOPMENT_PREVIEW disabled even if the log driver is selected.
        if ($isLogDriver && config('sms.development_preview')) {
            $response['dev_otp']  = $otp;
            $response['dev_note'] = 'Development OTP preview — not sent via real SMS.';
        }

        return response()->json($response);
    }

    public function verifyOtp(Request $request)
    {
        $cfg = AuthConfig::get();

        $request->validate(['phone' => 'required|string', 'otp' => 'required|string']);

        $phone       = $this->normalizePhone($request->phone);
        $maxAttempts = (int) $cfg['max_otp_attempts'];

        $record = OtpVerification::where('phone', $phone)->where('verified', false)->latest()->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => $this->localized('No OTP found for this phone number. Please request a new one.', 'مش لاقيين كود للرقم ده. اطلب كود جديد.')], 404);
        }

        if ($record->isExpired()) {
            $record->delete();
            return response()->json(['success' => false, 'message' => $this->localized('OTP has expired. Please request a new one.', 'الكود انتهت صلاحيته. اطلب كود جديد.')], 410);
        }

        if ($record->isExhausted($maxAttempts)) {
            return response()->json(['success' => false, 'message' => $this->localized('Too many incorrect attempts. Please request a new OTP.', 'محاولات غلط كتير. اطلب كود جديد.')], 429);
        }

        if ($record->otp_code !== $request->otp) {
            $record->increment('attempts');
            $remaining = $maxAttempts - $record->attempts;
            return response()->json(['success' => false, 'message' => $this->localized("Incorrect OTP. {$remaining} attempt(s) remaining.", "الكود مش صحيح. فاضل {$remaining} محاولة."), 'remaining' => $remaining], 422);
        }

        $record->update(['verified' => true]);

        $user = User::where('phone', $phone)->first();

        if ($user) {
            $user->update(['is_phone_verified' => true]);
            Auth::login($user);
            $request->session()->regenerate();
            $this->mergeGuestSessionOnLogin($user->id);
            return response()->json([
                'success'  => true,
                'new_user' => false,
                'redirect' => $request->session()->pull('url.intended', route('home')),
            ]);
        }

        if (!$cfg['auto_register_otp']) {
            return response()->json(['success' => false, 'message' => $this->localized('No account found with this phone number. Please register first.', 'مفيش حساب بالرقم ده. اعمل حساب الأول.')], 403);
        }

        $tempToken = Str::random(40);
        session([
            'otp_temp_token' => $tempToken,
            'otp_temp_phone' => $phone,
        ]);

        if ($cfg['require_name_on_register']) {
            return response()->json([
                'success'  => true,
                'new_user' => true,
                'redirect' => route('auth.complete-profile'),
            ]);
        }

        $user = $this->createUserFromPhone($phone);
        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestSessionOnLogin($user->id);

        return response()->json([
            'success'  => true,
            'new_user' => true,
            'redirect' => $request->session()->pull('url.intended', route('home')),
        ]);
    }

    public function showCompleteProfile(Request $request)
    {
        if (!session('otp_temp_token') || !session('otp_temp_phone')) {
            return redirect()->route('login')->withErrors(['phone' => $this->localized('Session expired. Please start over.', 'الجلسة انتهت. ابدأ من الأول.')]);
        }
        return view('web.auth.complete-profile');
    }

    public function completeProfile(Request $request)
    {
        $tempToken = session('otp_temp_token');
        $phone     = session('otp_temp_phone');

        if (!$tempToken || !$phone || $request->input('temp_token') !== $tempToken) {
            return redirect()->route('login')->withErrors(['phone' => $this->localized('Session expired. Please start over.', 'الجلسة انتهت. ابدأ من الأول.')]);
        }

        $request->validate([
            'name'  => 'required|string|min:2|max:100',
            'email' => 'nullable|email|unique:users,email',
        ]);

        $existingUser = User::where('phone', $phone)->first();
        if ($existingUser) {
            session()->forget(['otp_temp_token', 'otp_temp_phone']);
            Auth::login($existingUser);
            $request->session()->regenerate();
            $this->mergeGuestSessionOnLogin($existingUser->id);
            return redirect()->to($request->session()->pull('url.intended', route('home')));
        }

        $user = $this->createUserFromPhone($phone, $request->input('name'), $request->input('email'));

        session()->forget(['otp_temp_token', 'otp_temp_phone']);
        Auth::login($user);
        $request->session()->regenerate();
        $this->mergeGuestSessionOnLogin($user->id);

        return redirect()->to($request->session()->pull('url.intended', route('home')))
            ->with('success', $this->localized('Welcome to Ramo Store!', 'أهلاً بيك في Ramo Store!'));
    }

    private function createUserFromPhone(string $phone, ?string $name = null, ?string $email = null): User
    {
        $name      = $name ?: 'User ' . substr($phone, -4);
        $parts     = explode(' ', $name, 2);
        $firstName = $parts[0];
        $lastName  = $parts[1] ?? '';

        $unusablePassword = \Illuminate\Support\Facades\Hash::make(Str::random(32));

        $user = new User([
            'name'                => $name,
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'firstname'           => $firstName,
            'lastname'            => $lastName,
            // Phone OTP accounts do not need an email address. Keep it null
            // until the customer chooses to add one from their profile.
            'email'               => $email ?: null,
            'password'            => $unusablePassword,
            'phone'               => $phone,
            'nicename'            => Str::slug($name . '-' . substr($phone, -4)),
            'registered'          => now()->toDateTimeString(),
            'description'         => '',
            'shipping'            => json_encode([]),
            'registration_method' => 'phone_otp',
            'is_phone_verified'   => true,
        ]);
        // Customer privileges are assigned by trusted server code, never mass assigned.
        $user->role = json_encode(['customer']);
        $user->capabilities = json_encode(['customer' => true]);
        $user->save();

        return $user;
    }
}
