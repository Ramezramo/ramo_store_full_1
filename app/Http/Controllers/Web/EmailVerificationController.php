<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    private int $tokenExpiryMinutes = 60;

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }

    public function notice()
    {
        if (! Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        if ($user->email_verified_at || ! $this->hasDeliverableEmail($user)) {
            return redirect()->route('account.profile');
        }
        return view('web.auth.verify-email', ['email' => $user->email]);
    }

    private function hasDeliverableEmail(User $user): bool
    {
        $email = (string) $user->email;
        return filter_var($email, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with($email, '@ramostore.local')
            && ! str_ends_with($email, '@phone.user');
    }

    public function resend(Request $request)
    {
        if (! Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        if ($user->email_verified_at || ! $this->hasDeliverableEmail($user)) {
            return redirect()->route('account.profile');
        }
        $existing = DB::table('email_verification_tokens')->where('email', $user->email)->first();
        if ($existing?->created_at && Carbon::parse($existing->created_at)->isAfter(now()->subSeconds(60))) {
            return back()->with('resend_error', $this->localized('Please wait a moment before requesting another link.', 'استنى شوية قبل ما تطلب لينك جديد.'));
        }

        $token = Str::random(64);
        DB::table('email_verification_tokens')->upsert(
            ['email' => $user->email, 'token' => Hash::make($token), 'created_at' => now()],
            ['email'], ['token', 'created_at']
        );
        $url = route('email.verify', ['token' => $token, 'email' => $user->email]);
        try {
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                throw new \RuntimeException('Email delivery is not configured.');
            }
            Mail::send('emails.verify-email', ['url' => $url, 'user' => $user], function ($mail) use ($user) {
                $mail->to($user->email, $user->name)->subject('Verify your Ramo Store email');
            });
        } catch (\Throwable) {
            // The bearer link must never be returned to an unverified requester.
            return back()->with('resend_error', $this->localized(
                'We could not send the verification email. Please try again later.',
                'مش قادرين نبعت إيميل التأكيد دلوقتي. جرّب تاني بعد شوية.'
            ));
        }
        return back()->with('resent', true);
    }

    public function verify(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! is_string($token) || strlen($token) !== 64) {
            return redirect()->route('login')->withErrors(['email' => $this->localized('Invalid verification link.', 'لينك التأكيد مش صحيح.')]);
        }

        $verified = DB::transaction(function () use ($email, $token): bool {
            $user = User::query()->where('email', $email)->lockForUpdate()->first();
            $record = DB::table('email_verification_tokens')->where('email', $email)->lockForUpdate()->first();
            if (! $user || ! $record) return false;
            if (! $record->created_at || Carbon::parse($record->created_at)->isBefore(now()->subMinutes($this->tokenExpiryMinutes))) {
                DB::table('email_verification_tokens')->where('email', $email)->delete();
                return false;
            }
            if (! Hash::check($token, $record->token)) return false;

            // This is a trusted ownership proof; keep the field guarded in User.
            $user->forceFill(['email_verified_at' => now()])->save();
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return true;
        });
        if (! $verified) {
            return redirect()->route('login')->withErrors(['email' => $this->localized(
                'Invalid or expired verification link. Please request a new one.',
                'لينك التأكيد مش صحيح أو انتهت صلاحيته. اطلب لينك جديد.'
            )]);
        }

        // An email confirmation is not an account sign-in or an account-linking action.
        return redirect()->route(Auth::check() ? 'account.profile' : 'login')
            ->with('success', $this->localized('Email verified successfully.', 'الإيميل اتأكد بنجاح.'));
    }
}
