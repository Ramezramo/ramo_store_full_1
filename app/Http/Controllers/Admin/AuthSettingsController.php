<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthSettingsController extends Controller
{
    private function isAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        $roles = is_array($user->role) ? $user->role : json_decode($user->role, true) ?? [];
        return in_array('admin', $roles);
    }

    public function index()
    {
        if (!$this->isAdmin()) return redirect('/login')->with('error', 'Admin access required.');

        $config = AuthConfig::get();
        return view('admin.auth-settings', compact('config'));
    }

    public function update(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request format.',
            ], 400);
        }

        $data = [
            'email_login'               => $request->boolean('email_login'),
            'google_login'              => $request->boolean('google_login'),
            'phone_otp_login'           => $request->boolean('phone_otp_login'),
            'guest_checkout'            => $request->boolean('guest_checkout'),
            'auto_register_google'      => $request->boolean('auto_register_google'),
            'auto_register_otp'         => $request->boolean('auto_register_otp'),
            'require_name_on_register'  => $request->boolean('require_name_on_register'),
            'require_email_on_register' => $request->boolean('require_email_on_register'),
            'otp_length'                => (int) $request->input('otp_length', 6),
            'otp_expiry_minutes'        => (int) $request->input('otp_expiry_minutes', 5),
            'max_otp_attempts'          => (int) $request->input('max_otp_attempts', 3),
            'resend_cooldown_seconds'   => (int) $request->input('resend_cooldown_seconds', 60),
            'max_resends_per_hour'      => (int) $request->input('max_resends_per_hour', 3),
            'max_login_attempts'        => (int) $request->input('max_login_attempts', 5),
            'lockout_duration_minutes'  => (int) $request->input('lockout_duration_minutes', 15),
            'session_expiry_hours'      => (int) $request->input('session_expiry_hours', 24),
        ];

        try {
            AuthConfig::save($data);
        } catch (\Throwable $e) {
            Log::error('Auth settings save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Save failed. Check server logs.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Auth settings saved successfully.']);
    }
}
