<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaymentConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{
    public function index()
    {
        return view('admin.payment-methods', ['settings' => PaymentConfig::get()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'wallet_number' => 'nullable|string|max:100',
            'instapay_number' => 'nullable|string|max:100',
            'instapay_link' => 'nullable|url|max:500',
        ]);

        PaymentConfig::save([
            'wallet_enabled' => $request->boolean('wallet_enabled'),
            'wallet_number' => trim($data['wallet_number'] ?? ''),
            'instapay_enabled' => $request->boolean('instapay_enabled'),
            'instapay_number' => trim($data['instapay_number'] ?? ''),
            'instapay_link' => trim($data['instapay_link'] ?? ''),
        ]);

        return back()->with('success', 'Payment methods updated.');
    }
}