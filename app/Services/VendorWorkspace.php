<?php

namespace App\Services;

use App\Models\VendorUser;
use Illuminate\Support\Facades\Auth;

class VendorWorkspace
{
    public const SESSION_KEY = 'admin_vendor_workspace_id';

    public function isAdminManaging(): bool
    {
        return Auth::guard('web')->check()
            && $this->isAdmin(Auth::guard('web')->user())
            && session()->has(self::SESSION_KEY);
    }

    public function vendor(): ?VendorUser
    {
        if ($this->isAdminManaging()) {
            return VendorUser::query()->find(session(self::SESSION_KEY));
        }

        return Auth::guard('vendor_web')->user();
    }

    public function isAdmin($user): bool
    {
        if (! $user) {
            return false;
        }

        $decodedRoles = is_array($user->role ?? null)
            ? $user->role
            : json_decode((string) ($user->role ?? ''), true);
        $roles = is_array($decodedRoles) ? $decodedRoles : [(string) ($user->role ?? '')];

        return in_array('admin', $roles, true);
    }
}
