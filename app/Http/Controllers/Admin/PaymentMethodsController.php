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
            'cod_data' => 'nullable|string|max:2000',
            'cod_fee' => 'required|numeric|min:0|max:100000',
            'vodafone_cash_data' => 'nullable|string|max:2000',
            'bank_transfer_data' => 'nullable|string|max:2000',
            'fawry_data' => 'nullable|string|max:2000',
            'credit_card_data' => 'nullable|string|max:2000',
        ]);

        PaymentConfig::save([
            'cod_enabled' => $request->boolean('cod_enabled'),
            'cod_data' => trim($data['cod_data'] ?? ''),
            'cod_fee' => round((float) $data['cod_fee'], 2),
            'vodafone_cash_enabled' => $request->boolean('vodafone_cash_enabled'),
            'vodafone_cash_data' => trim($data['vodafone_cash_data'] ?? ''),
            'bank_transfer_enabled' => $request->boolean('bank_transfer_enabled'),
            'bank_transfer_data' => trim($data['bank_transfer_data'] ?? ''),
            'fawry_enabled' => $request->boolean('fawry_enabled'),
            'fawry_data' => trim($data['fawry_data'] ?? ''),
            'credit_card_enabled' => $request->boolean('credit_card_enabled'),
            'credit_card_data' => trim($data['credit_card_data'] ?? ''),
            'wallet_enabled' => $request->boolean('wallet_enabled'),
            'wallet_number' => trim($data['wallet_number'] ?? ''),
            'instapay_enabled' => $request->boolean('instapay_enabled'),
            'instapay_number' => trim($data['instapay_number'] ?? ''),
            'instapay_link' => trim($data['instapay_link'] ?? ''),
        ]);

        return back()->with('success', 'Payment methods updated.');
    }
}