<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    private int $tokenExpiryMinutes = 60;

    private function localized(string $english, string $arabic): string
    {
        return session('locale') === 'ar' ? $arabic : $english;
    }

    public function showForgotForm()
    {
        return view('web.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);
        $status = $this->localized('If that email is registered, a reset link has been sent.', 'لو الإيميل ده مسجل، بعتناله لينك لتغيير كلمة السر.');
        $user = User::where('email', $request->email)
            ->where('email', 'not like', '%@ramostore.local')->first();
        if (! $user) return back()->with('status', $status);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->upsert([
            'email' => $user->email, 'token' => Hash::make($token), 'created_at' => now(),
        ], ['email'], ['token', 'created_at']);
        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $user->email]);
        try {
            Mail::send('emails.password-reset', ['url' => $resetUrl, 'user' => $user], function ($mail) use ($user) {
                $mail->to($user->email, $user->name)->subject('Reset your Ramo Store password');
            });
        } catch (\Throwable) {
            // Mail failure must not expose an account-recovery credential.
        }
        return back()->with('status', $status);
    }

    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        if (! is_string($token) || $token === '' || strlen($token) > 700
            || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('password.forgot')->withErrors(['email' => $this->localized('Invalid or missing reset link.', 'لينك تغيير كلمة السر مش صحيح أو ناقص.')]);
        }
        return view('web.auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255', 'token' => 'required|string|max:700',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $reset = DB::transaction(function () use ($request): bool {
            $user = User::where('email', $request->email)->lockForUpdate()->first();
            $record = DB::table('password_reset_tokens')->where('email', $request->email)->lockForUpdate()->first();
            if (! $user || ! $record) return false;
            if (! $record->created_at || Carbon::parse($record->created_at)->isBefore(now()->subMinutes($this->tokenExpiryMinutes))) {
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return false;
            }
            if (! Hash::check($request->token, $record->token)) return false;

            $user->forceFill([
                'password' => Hash::make($request->password), 'remember_token' => Str::random(60),
                'registration_method' => 'email_password', 'email_verified_at' => now(),
            ])->save();
            $user->tokens()->delete();
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return true;
        });
        if (! $reset) {
            return back()->withErrors(['email' => $this->localized(
                'Invalid or expired reset link. Please request a new one.',
                'لينك تغيير كلمة السر مش صحيح أو انتهت صلاحيته. اطلب لينك جديد.'
            )]);
        }
        return redirect()->route('login')->with('status', $this->localized('Password reset successfully. You can now sign in.', 'كلمة السر اتغيرت بنجاح. تقدر تسجّل دخول دلوقتي.'));
    }
}
