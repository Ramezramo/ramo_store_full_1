@extends('admin.layout')
@section('title', 'Payment Methods')
@section('page-title', 'Payment Methods')

@section('content')
<style>
  .pm-switch{display:flex;align-items:center;gap:10px;font-weight:700;cursor:pointer}
  .pm-switch input{position:absolute;opacity:0;pointer-events:none}
  .pm-switch-track{position:relative;width:40px;height:22px;border-radius:999px;background:#64748b;transition:.18s;flex:none}
  .pm-switch-track::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:.18s}
  .pm-switch input:checked + .pm-switch-track{background:#22c55e}
  .pm-switch input:checked + .pm-switch-track::after{transform:translateX(18px)}
</style>
<div style="max-width:820px">
  @if(session('success'))
    <div class="card" style="border-color:rgba(34,197,94,.35);color:var(--green);margin-bottom:18px">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="card" style="border-color:rgba(239,68,68,.35);color:var(--red);margin-bottom:18px">{{ $errors->first() }}</div>
  @endif

  <div class="card" style="margin-bottom:18px">
    <div class="card-title">Checkout payment methods</div>
    <p style="color:var(--muted);line-height:1.7;margin-bottom:18px">
      Turn each method on or off. Any text entered in a method’s details box will appear inside that payment option during checkout.
    </p>
    <form method="POST" action="{{ route('admin.payment-methods.update') }}">
      @csrf @method('PUT')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
        @php
          $standardMethods = [
            ['key' => 'cod', 'title' => 'Cash on Delivery', 'description' => 'Pay when the order arrives', 'data_label' => 'Details', 'placeholder' => 'Pay when your order arrives'],
            ['key' => 'vodafone_cash', 'title' => 'Vodafone Cash', 'description' => 'Mobile wallet transfer', 'data_label' => 'Transfer details', 'placeholder' => 'Phone number or transfer instructions'],
            ['key' => 'bank_transfer', 'title' => 'Bank Transfer', 'description' => 'Transfer to your bank account', 'data_label' => 'Bank details', 'placeholder' => 'Bank name, account number, IBAN, or instructions'],
            ['key' => 'fawry', 'title' => 'Fawry', 'description' => 'Pay at any Fawry outlet', 'data_label' => 'Details', 'placeholder' => 'Fawry reference or payment instructions'],
            ['key' => 'credit_card', 'title' => 'Credit Card', 'description' => 'Visa / Mastercard', 'data_label' => 'Details', 'placeholder' => 'Payment gateway or card payment instructions'],
          ];
        @endphp
        @foreach($standardMethods as $method)
          <div style="border:1px solid var(--border);border-radius:10px;padding:18px">
            <label class="pm-switch" style="margin-bottom:14px">
              <input type="checkbox" name="{{ $method['key'] }}_enabled" value="1" {{ $settings[$method['key'].'_enabled'] ? 'checked' : '' }}>
              <span class="pm-switch-track"></span>
              {{ $method['title'] }}
            </label>
            <div style="font-size:12px;color:var(--muted);margin-bottom:12px">{{ $method['description'] }}</div>
            <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px">{{ $method['data_label'] }}</label>
            <textarea name="{{ $method['key'] }}_data" rows="2" placeholder="{{ $method['placeholder'] }}"
              style="width:100%;padding:11px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);resize:vertical">{{ old($method['key'].'_data', $settings[$method['key'].'_data']) }}</textarea>
          </div>
        @endforeach
        <div style="border:1px solid var(--border);border-radius:10px;padding:18px">
          <label class="pm-switch" style="margin-bottom:16px">
            <input type="checkbox" name="wallet_enabled" value="1" {{ $settings['wallet_enabled'] ? 'checked' : '' }}>
            <span class="pm-switch-track"></span>
            Pay by Wallet
          </label>
          <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px">Wallet number</label>
          <input name="wallet_number" value="{{ old('wallet_number', $settings['wallet_number']) }}" placeholder="01xxxxxxxxx"
                 style="width:100%;padding:11px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text)">
          <div style="font-size:12px;color:var(--muted);margin-top:8px">Egyptian mobile wallet number customers transfer to.</div>
        </div>
        <div style="border:1px solid var(--border);border-radius:10px;padding:18px">
          <label class="pm-switch" style="margin-bottom:16px">
            <input type="checkbox" name="instapay_enabled" value="1" {{ $settings['instapay_enabled'] ? 'checked' : '' }}>
            <span class="pm-switch-track"></span>
            Pay by InstaPay
          </label>
          <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px">InstaPay number / alias</label>
          <input name="instapay_number" value="{{ old('instapay_number', $settings['instapay_number']) }}" placeholder="InstaPay address or number"
                 style="width:100%;padding:11px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);margin-bottom:12px">
          <label style="display:block;color:var(--muted);font-size:12px;margin-bottom:6px">InstaPay link (optional)</label>
          <input type="url" name="instapay_link" value="{{ old('instapay_link', $settings['instapay_link']) }}" placeholder="https://..."
                 style="width:100%;padding:11px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text)">
        </div>
      </div>
      <button class="btn btn-primary" style="margin-top:20px">Save Payment Methods</button>
    </form>
  </div>

  <div class="card">
    <div class="card-title">Verification workflow</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;color:var(--muted);font-size:12px;line-height:1.5">
      <div><strong style="display:block;color:var(--text);margin-bottom:4px">Pending Payment</strong>Order created, receipt not uploaded.</div>
      <div><strong style="display:block;color:var(--text);margin-bottom:4px">Pending Verification</strong>Receipt uploaded and waiting for review.</div>
      <div><strong style="display:block;color:var(--text);margin-bottom:4px">Confirmed</strong>Receipt approved and payment marked paid.</div>
      <div><strong style="display:block;color:var(--text);margin-bottom:4px">Rejected</strong>Customer can upload a replacement receipt.</div>
    </div>
  </div>
</div>
@endsection