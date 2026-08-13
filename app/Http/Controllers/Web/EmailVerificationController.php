<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\CartTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    use CartTrait;
    private int $tokenExpiryMinutes = 60;

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }

    private function generateAndStore(string $email): string
    {
        $token = Str::random(64);

        DB::table('email_verification_tokens')->upsert(
            ['email' => $email, 'token' => Hash::make($token), 'created_at' => now()],
            ['email'],
            ['token', 'created_at']
        );

        return $token;
    }

    private function sendOrShow(User $user, string $token): ?string
    {
        $url = route('email.verify', ['token' => $token, 'email' => $user->email]);

        $canSendMail = false;
        try {
            $mailer = config('mail.default');
            $host   = config('mail.mailers.smtp.host', '');
            $canSendMail = ($mailer === 'smtp' && !empty($host) && $host !== 'mailpit' && $host !== 'localhost')
                        || ($mailer !== 'log' && $mailer !== 'array');
        } catch (\Throwable) {}

        if ($canSendMail) {
            try {
                Mail::send('emails.verify-email', ['url' => $url, 'user' => $user], function ($m) use ($user) {
                    $m->to($user->email, $user->name)
                      ->subject('Verify your Ramo Store email');
                });
                return null;
            } catch (\Throwable) {}
        }

        return $url;
    }

    public function notice()
    {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        if ($user->email_verified_at) return redirect()->route('account.profile');
        if (str_ends_with($user->email, '@ramostore.local')) return redirect()->route('account.profile');

        return view('web.auth.verify-email', ['email' => $user->email]);
    }

    public function resend(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();

        if ($user->email_verified_at) {
            return redirect()->route('account.profile');
        }

        $existing = DB::table('email_verification_tokens')->where('email', $user->email)->first();
        if ($existing) {
            $ageSeconds = now()->diffInSeconds($existing->created_at);
            if ($ageSeconds < 60) {
                return back()->with('resend_error', $this->localized('Please wait a moment before requesting another link.', 'استنى شوية قبل ما تطلب لينك جديد.'));
            }
        }

        $token  = $this->generateAndStore($user->email);
        $devUrl = $this->sendOrShow($user, $token);

        return back()->with([
            'resent'   => true,
            'dev_url'  => $devUrl,
        ]);
    }

    public function verify(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        if (!$email || !$token) {
            return redirect()->route('login')->withErrors(['email' => $this->localized('Invalid verification link.', 'لينك التأكيد مش صحيح.')]);
        }

        $record = DB::table('email_verification_tokens')->where('email', $email)->first();

        if (!$record) {
            return redirect()->route('login')->withErrors(['email' => $this->localized('Verification link not found. Please request a new one.', 'مش لاقيين لينك التأكيد. اطلب لينك جديد.')]);
        }

        $ageMinutes = now()->diffInMinutes($record->created_at);
        if ($ageMinutes > $this->tokenExpiryMinutes) {
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return redirect()->route('email.verify.notice')
                ->with('expired', true)
                ->with('info', $this->localized('Your verification link has expired. Please request a new one.', 'لينك التأكيد انتهت صلاحيته. اطلب لينك جديد.'));
        }

        if (!Hash::check($token, $record->token)) {
            return redirect()->route('login')->withErrors(['email' => $this->localized('Invalid verification link.', 'لينك التأكيد مش صحيح.')]);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => $this->localized('Account not found.', 'مش لاقيين الحساب ده.')]);
        }

        $user->update(['email_verified_at' => now()]);
        DB::table('email_verification_tokens')->where('email', $email)->delete();

        if (!Auth::check()) {
            Auth::login($user);
            $request->session()->regenerate();
            $this->mergeGuestSessionOnLogin($user->id);
        }

        return redirect()->route('account.profile')
            ->with('success', $this->localized('Email verified! Welcome to Ramo Store.', 'الإيميل اتأكد بنجاح! أهلاً بيك في Ramo Store.'));
    }
}
