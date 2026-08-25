@extends('admin.layout')

@section('title', 'Referral Program')
@section('page-title', 'Referral Program')

@push('styles')
<style>
.referral-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:20px;margin-bottom:20px}
.referral-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;min-width:0}
.referral-card-wide{grid-column:1/-1}
.referral-card h2{margin:0 0 6px;font-size:16px}
.referral-card p.help{margin:0 0 18px;color:var(--muted);font-size:12px;line-height:1.5}
.referral-field{margin-bottom:14px}.referral-field label{display:block;margin-bottom:6px;font-size:12px;font-weight:700;color:var(--text)}
.referral-field input,.referral-field select{width:100%;min-height:40px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text)}
.referral-field input:focus,.referral-field select:focus{outline:2px solid color-mix(in srgb,var(--accent) 35%,transparent);border-color:var(--accent)}
.referral-toggle{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;padding:12px;border:1px solid var(--border);border-radius:9px;background:var(--bg)}
.referral-toggle label{font-size:13px;font-weight:700}.referral-toggle small{display:block;margin-top:3px;color:var(--muted);font-size:11px;font-weight:400}
.referral-toggle input{width:20px;height:20px;accent-color:var(--accent)}
.referral-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.referral-table-wrap{overflow:auto}.referral-table{width:100%;border-collapse:collapse;font-size:12px;min-width:760px}.referral-table th,.referral-table td{padding:11px 9px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}.referral-table th{color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.05em}.referral-table td strong{display:block;font-size:12px}.referral-table td small{display:block;color:var(--muted);margin-top:3px}.referral-badge{display:inline-flex;padding:4px 7px;border-radius:999px;background:rgba(148,163,184,.15);color:var(--text);font-size:10px;font-weight:800}.referral-badge.pending{background:rgba(234,179,8,.15);color:#a16207}.referral-badge.qualified,.referral-badge.approved{background:rgba(34,197,94,.15);color:#15803d}.referral-badge.rejected,.referral-badge.clawed_back{background:rgba(239,68,68,.15);color:#b91c1c}
.referral-inline-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap}.referral-inline-form input,.referral-inline-form select{width:auto;min-height:32px;padding:5px 7px;font-size:11px}.referral-actions{display:flex;gap:6px;flex-wrap:wrap}.referral-actions .btn{min-height:30px;padding:5px 9px;font-size:11px}.referral-alert{margin-bottom:16px;padding:11px 13px;border-radius:9px;font-size:12px}.referral-alert.success{background:rgba(34,197,94,.12);color:#15803d}.referral-alert.error{background:rgba(239,68,68,.12);color:#b91c1c}.referral-error{margin:4px 0 0;color:#b91c1c;font-size:11px}
@media(max-width:1000px){.referral-grid{grid-template-columns:1fr}.referral-card-wide{grid-column:auto}}
@media(max-width:600px){.referral-card{padding:15px}.referral-form-grid{grid-template-columns:1fr}.referral-table{min-width:700px}}
</style>
@endpush

@section('content')
@if(session('success'))<div class="referral-alert success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="referral-alert error">{{ $errors->first() }}</div>@endif

<div class="referral-grid">
  <section class="referral-card">
    <h2>Program Settings</h2>
    <p class="help">Keep the program disabled until you finish QA. A commission is created as pending and always requires manual approval.</p>
    <form method="POST" action="{{ route('admin.referral.settings.update') }}">
      @csrf @method('PUT')
      <div class="referral-toggle">
        <div><label for="referral_enabled">Enable referral program</label><small>Only enabled programs can create commissions.</small></div>
        <input type="checkbox" id="referral_enabled" name="referral_enabled" value="1" {{ old('referral_enabled', $settings['referral_enabled'] ?? false) ? 'checked' : '' }}>
      </div>
      <div class="referral-field"><label for="referral_min_order_amount">Minimum completed order (EGP)</label><input id="referral_min_order_amount" name="referral_min_order_amount" type="number" min="0" step="0.01" value="{{ old('referral_min_order_amount', $settings['referral_min_order_amount'] ?? 700) }}" required></div>
      <div class="referral-form-grid">
        <div class="referral-field"><label for="referral_commission_type">Commission type</label><select id="referral_commission_type" name="referral_commission_type"><option value="percentage" {{ old('referral_commission_type', $settings['referral_commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option><option value="flat" {{ old('referral_commission_type', $settings['referral_commission_type'] ?? '') === 'flat' ? 'selected' : '' }}>Flat amount</option></select></div>
        <div class="referral-field"><label for="referral_commission_value">Commission value</label><input id="referral_commission_value" name="referral_commission_value" type="number" min="0" step="0.01" value="{{ old('referral_commission_value', $settings['referral_commission_value'] ?? 5) }}" required></div>
      </div>
      <div class="referral-field"><label for="referral_commission_scope">Commission order scope</label><select id="referral_commission_scope" name="referral_commission_scope"><option value="first_order" {{ old('referral_commission_scope', $settings['referral_commission_scope'] ?? 'first_order') === 'first_order' ? 'selected' : '' }}>First completed order only (recommended)</option><option value="all_orders" {{ old('referral_commission_scope', $settings['referral_commission_scope'] ?? 'first_order') === 'all_orders' ? 'selected' : '' }}>Every qualifying completed order</option></select><small style="display:block;margin-top:5px;color:var(--muted);font-size:11px">The default is first completed order only. In all-orders mode, every later completed order meeting the minimum can create a separate commission.</small></div>
      <button class="btn btn-primary" type="submit">Save referral settings</button>
    </form>
  </section>

  <section class="referral-card">
    <h2>Manual Referral</h2>
    <p class="help">Use only for genuine offline referrals. The same fraud checks run before a record is created.</p>
    <form method="POST" action="{{ route('admin.referral.store') }}">
      @csrf
      <div class="referral-field"><label for="referrer_id">Referrer</label><select id="referrer_id" name="referrer_id" required><option value="">Select referrer</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ old('referrer_id') == $user->id ? 'selected' : '' }}>{{ $user->name ?: 'User #'.$user->id }} — {{ $user->email ?: $user->phone }}</option>@endforeach</select></div>
      <div class="referral-field"><label for="referred_id">Referred customer</label><select id="referred_id" name="referred_id" required><option value="">Select referred customer</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ old('referred_id') == $user->id ? 'selected' : '' }}>{{ $user->name ?: 'User #'.$user->id }} — {{ $user->email ?: $user->phone }}</option>@endforeach</select></div>
      <button class="btn btn-primary" type="submit">Add referral</button>
    </form>
  </section>

  <section class="referral-card referral-card-wide">
    <h2>Referral Links</h2>
    <p class="help">The selected commission scope controls whether a referral qualifies on the first completed order only or can earn on every qualifying completed order.</p>
    <div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>Referrer</th><th>Referred customer</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
    @forelse($referrals as $referral)
      <tr><td><strong>{{ $referral->referrer?->name ?: 'User #'.$referral->referrer_id }}</strong><small>{{ $referral->referrer?->email }}</small></td><td><strong>{{ $referral->referred?->name ?: 'User #'.$referral->referred_id }}</strong><small>{{ $referral->referred?->email }}</small></td><td><span class="referral-badge {{ $referral->status }}">{{ $referral->status }}</span>@if($referral->rejection_reason)<small>{{ $referral->rejection_reason }}</small>@endif</td><td>{{ optional($referral->created_at)->format('Y-m-d H:i') }}</td><td><form class="referral-inline-form" method="POST" action="{{ route('admin.referral.update', $referral) }}">@csrf @method('PATCH')<input type="hidden" name="referrer_id" value="{{ $referral->referrer_id }}"><select name="status"><option value="pending" {{ $referral->status === 'pending' ? 'selected' : '' }}>pending</option><option value="qualified" {{ $referral->status === 'qualified' ? 'selected' : '' }}>qualified</option><option value="rejected" {{ $referral->status === 'rejected' ? 'selected' : '' }}>rejected</option><option value="expired" {{ $referral->status === 'expired' ? 'selected' : '' }}>expired</option></select><button class="btn btn-secondary" type="submit">Update</button></form><form method="POST" action="{{ route('admin.referral.destroy', $referral) }}" style="margin-top:6px">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Cancel</button></form></td></tr>
    @empty<tr><td colspan="5">No referrals have been recorded yet.</td></tr>@endforelse
    </tbody></table></div>{{ $referrals->links() }}
  </section>

  <section class="referral-card referral-card-wide">
    <h2>Commissions</h2>
    <p class="help">Pending commissions must be reviewed manually. Paid status is intentionally not exposed here because no automatic payout exists in this phase.</p>
    <div class="referral-table-wrap"><table class="referral-table"><thead><tr><th>Customer / referrer</th><th>Order</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>
    @forelse($commissions as $commission)
      <tr><td><strong>{{ $commission->referral?->referred?->name ?: 'User #'.$commission->referral?->referred_id }}</strong><small>Referrer: {{ $commission->referral?->referrer?->name ?: 'User #'.$commission->referral?->referrer_id }}</small></td><td><strong>#{{ $commission->order_id }}</strong><small>{{ number_format((float) ($commission->order?->final_total ?? 0), 2) }} EGP · {{ $commission->order?->status }}</small></td><td><strong>{{ number_format((float) $commission->amount, 2) }} EGP</strong><small>{{ optional($commission->created_at)->format('Y-m-d H:i') }}</small></td><td><span class="referral-badge {{ $commission->status }}">{{ $commission->status }}</span></td><td><div class="referral-actions">@if($commission->status === 'pending')<form class="referral-inline-form" method="POST" action="{{ route('admin.referral.commissions.update', $commission) }}">@csrf @method('PATCH')<input type="number" name="amount" min="0.01" step="0.01" value="{{ $commission->amount }}" aria-label="Commission amount"><button class="btn btn-secondary" type="submit">Save amount</button></form><form method="POST" action="{{ route('admin.referral.commissions.approve', $commission) }}">@csrf @method('PATCH')<button class="btn btn-primary" type="submit">Approve</button></form>@endif @if(in_array($commission->status, ['pending','approved'], true))<form method="POST" action="{{ route('admin.referral.commissions.reject', $commission) }}">@csrf @method('PATCH')<button class="btn btn-danger" type="submit">Reject</button></form>@endif</div></td></tr>
    @empty<tr><td colspan="5">No commissions have been created yet.</td></tr>@endforelse
    </tbody></table></div>{{ $commissions->links() }}
  </section>
</div>
@endsection
