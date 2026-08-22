<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ShippingConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShippingSettingsController extends Controller
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

        $config = ShippingConfig::get();
        return view('admin.shipping-settings', compact('config'));
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

        $request->validate([
            'free_shipping_enabled'   => 'nullable|boolean',
            'free_shipping_threshold' => 'required|numeric|min:0',
            'standard_shipping_fee'   => 'required|numeric|min:0',
            'cod_fee'                 => 'required|numeric|min:0|max:100000',
        ]);

        $data = [
            'free_shipping_enabled'   => $request->boolean('free_shipping_enabled'),
            'free_shipping_threshold' => round((float) $request->input('free_shipping_threshold', 1000), 2),
            'standard_shipping_fee'   => round((float) $request->input('standard_shipping_fee', 0), 2),
            'cod_fee'                 => round((float) $request->input('cod_fee', 40), 2),
        ];

        try {
            ShippingConfig::save($data);
        } catch (\Throwable $e) {
            Log::error('Shipping settings save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Save failed. Check server logs.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Shipping settings saved successfully.']);
    }
}
