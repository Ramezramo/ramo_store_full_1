<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->where('email', 'not like', '%@ramostore.local')
            ->first();

        if (!$user) {
            return back()->with('status', $this->localized('If that email is registered, a reset link has been sent.', 'لو الإيميل ده مسجل، بعتناله لينك لتغيير كلمة السر.'));
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->upsert(
            [
                'email'      => $user->email,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ],
            ['email'],
            ['token', 'created_at']
        );

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $user->email]);
        $isDevMode = config('app.debug') && strtolower((string) env('MAIL_MAILER', 'smtp')) === 'smtp'
                     && env('MAIL_HOST') === 'mailpit';

        if ($isDevMode) {
            return back()->with([
                'status'        => $this->localized('Reset link generated (dev mode — no real email sent).', 'لينك تغيير كلمة السر اتجهز (وضع تجريبي — مفيش إيميل حقيقي اتبعت).'),
                'dev_reset_url' => $resetUrl,
            ]);
        }

        try {
            Mail::send('emails.password-reset', ['url' => $resetUrl, 'user' => $user], function ($m) use ($user) {
                $m->to($user->email, $user->name)
                  ->subject(session('locale') === 'ar' ? 'غيّر كلمة السر بتاعة Ramo Store' : 'Reset your Ramo Store password');
            });
        } catch (\Throwable $e) {
            return back()->with([
                'status'        => $this->localized('Could not send email — showing link for dev use.', 'مش قادرين نبعت الإيميل دلوقتي — بنعرض اللينك للتجربة.'),
                'dev_reset_url' => $resetUrl,
            ]);
        }

        return back()->with('status', $this->localized('If that email is registered, a reset link has been sent.', 'لو الإيميل ده مسجل، بعتناله لينك لتغيير كلمة السر.'));
    }

    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('password.forgot')->withErrors(['email' => $this->localized('Invalid or missing reset link.', 'لينك تغيير كلمة السر مش صحيح أو ناقص.')]);
        }

        return view('web.auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => $this->localized('Invalid or expired reset link. Please request a new one.', 'لينك تغيير كلمة السر مش صحيح أو انتهت صلاحيته. اطلب لينك جديد.')]);
        }

        $ageMinutes = now()->diffInMinutes($record->created_at);
        if ($ageMinutes > $this->tokenExpiryMinutes) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => $this->localized('This reset link has expired. Please request a new one.', 'لينك تغيير كلمة السر انتهت صلاحيته. اطلب لينك جديد.')]);
        }

        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => $this->localized('Invalid reset link. Please request a new one.', 'لينك تغيير كلمة السر مش صحيح. اطلب لينك جديد.')]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => $this->localized('No account found with this email address.', 'مفيش حساب بالإيميل ده.')]);
        }

        $user->update([
            'password'            => Hash::make($request->password),
            'registration_method' => 'email_password',
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', $this->localized('Password reset successfully. You can now sign in.', 'كلمة السر اتغيرت بنجاح. تقدر تسجّل دخول دلوقتي.'));
    }
}
