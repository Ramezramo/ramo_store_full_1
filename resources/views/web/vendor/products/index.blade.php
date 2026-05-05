@extends('web.vendor.layout')
@section('title', 'My Products')
@section('page-title', 'My Products')

@push('styles')
<style>
.prod-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;background:#f3f4f6;border:1px solid var(--light);flex-shrink:0}
.prod-thumb-ph{width:44px;height:44px;border-radius:8px;background:#f3f4f6;border:1px solid var(--light);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.badge-publish{background:#dcfce7;color:#166534;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-draft{background:#f3f4f6;color:#6b7280;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-pending{background:#fef9c3;color:#92400e;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-approved{background:#dcfce7;color:#166534;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.badge-rejected{background:#fee2e2;color:#991b1b;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.action-link{font-size:12px;font-weight:600;color:var(--orange);text-decoration:none}
.action-link:hover{text-decoration:underline}
.action-link.danger{color:var(--red)}
.empty-state{text-align:center;padding:60px 20px;color:var(--mid)}
.empty-state svg{opacity:.25;margin-bottom:16px}
.empty-state h3{font-size:16px;font-weight:700;color:var(--dark);margin-bottom:6px}
.empty-state p{font-size:13px;margin-bottom:20px}

/* ── Bulk selection ────────────────────────────────────────────── */
.chk-col{width:38px;padding-left:14px!important;padding-right:0!important}
.prod-check{width:15px;height:15px;accent-color:var(--orange);cursor:pointer;flex-shrink:0}
tr.row-selected > td{background:#fff8f5}

/* ── Floating bulk bar ─────────────────────────────────────────── */
.bulk-bar{
  position:fixed;bottom:0;left:230px;right:0;z-index:200;
  background:#1e2435;color:#e2e8f0;
  padding:13px 24px;
  display:flex;align-items:center;gap:14px;flex-wrap:wrap;
  border-top:2px solid var(--orange);
  box-shadow:0 -6px 30px rgba(0,0,0,.25);
  transform:translateY(100%);
  transition:transform .22s cubic-bezier(.4,0,.2,1);
}
.bulk-bar.open{transform:translateY(0)}
.bulk-sel-count{
  font-weight:700;font-size:13px;white-space:nowrap;
  background:var(--orange);color:#fff;
  padding:3px 11px;border-radius:20px;
}
.bulk-divider{width:1px;height:28px;background:#2a3347;flex-shrink:0}
.bulk-label{font-size:12px;color:#94a3b8;white-space:nowrap}
.bulk-select{
  background:#2a3347;color:#e2e8f0;
  border:1px solid #3d4f6a;border-radius:8px;
  padding:7px 12px;font-size:13px;min-width:240px;cursor:pointer;
  outline:none;
}
.bulk-select:focus{border-color:var(--orange)}
.bulk-val-wrap{display:flex;align-items:center;gap:6px}
.bulk-val{
  background:#2a3347;color:#e2e8f0;
  border:1px solid #3d4f6a;border-radius:8px;
  padding:7px 10px;width:90px;font-size:13px;outline:none;
}
.bulk-val:focus{border-color:var(--orange)}
.bulk-val-unit{font-size:12px;color:#64748b;font-weight:700}
.bulk-apply{
  background:var(--orange);color:#fff;
  border:none;border-radius:8px;
  padding:8px 20px;font-weight:700;font-size:13px;
  cursor:pointer;transition:.15s;white-space:nowrap;
}
.bulk-apply:hover:not(:disabled){background:var(--orange2)}
.bulk-apply:disabled{opacity:.45;cursor:not-allowed}
.bulk-clear{
  background:none;border:1px solid #3d4f6a;
  color:#94a3b8;border-radius:8px;
  padding:7px 14px;font-size:13px;
  transition:.15s;white-space:nowrap;
}
.bulk-clear:hover{color:#e2e8f0;border-color:#94a3b8}
.bulk-feedback{font-size:12px;font-weight:700;white-space:nowrap;display:none}
.bulk-feedback.ok{color:#4ade80}
.bulk-feedback.err{color:#f87171}

/* ── Price cell updated flash ──────────────────────────────────── */
@keyframes priceFlash{0%{background:#fff3cd}100%{background:transparent}}
.price-updated{animation:priceFlash .8s ease}
.disc-badge{font-size:10px;color:var(--orange);font-weight:700;margin-top:2px}
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
  <div>
    <div style="font-size:22px;font-weight:800">Products</div>
    <div style="font-size:13px;color:var(--mid);margin-top:2px">
      {{ $products->count() }} product{{ $products->count() === 1 ? '' : 's' }} in your store
      @if($products->count() > 0)
        <span style="margin-left:10px;font-size:11px;color:var(--orange);font-weight:600;cursor:pointer" onclick="toggleSelectAll()">Select All</span>
      @endif
    </div>
  </div>
  <a href="{{ route('vendor.products.create') }}" class="vs-btn vs-btn-primary">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Product
  </a>
</div>

@if($products->isEmpty())
  <div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
    <h3>No products yet</h3>
    <p>Add your first product to start selling on RamoStore.</p>
    <a href="{{ route('vendor.products.create') }}" class="vs-btn vs-btn-primary">Add Your First Product</a>
  </div>
@else
  <div class="vs-table-wrap">
    <table class="vs-table" id="products-table">
      <thead>
        <tr>
          <th class="chk-col">
            <input type="checkbox" id="select-all-chk" class="prod-check" title="Select all" onchange="toggleAll(this.checked)">
          </th>
          <th>Product</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Variations</th>
          <th>Status</th>
          <th>Approval</th>
          <th>Added</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $p)
          @php
            $imgs     = json_decode($p->images ?? '{}', true);
            $thumb    = $imgs['thumbnail'] ?? null;
            $thumbUrl = $thumb ? \Illuminate\Support\Facades\Storage::url($thumb) : null;
            $pr       = $priceRanges[$p->id] ?? null;
            $discPct  = (float)($p->discount_percentage ?? 0);
          @endphp
          <tr data-id="{{ $p->id }}">
            <td class="chk-col">
              <input type="checkbox" class="prod-check row-check" value="{{ $p->id }}" onchange="onRowCheck(this)">
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                @if($thumbUrl)
                  <img src="{{ $thumbUrl }}" class="prod-thumb" alt="">
                @else
                  <div class="prod-thumb-ph">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                @endif
                <div>
                  <div style="font-weight:600;font-size:13px;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->name }}</div>
                  @if($p->sku)<div style="font-size:11px;color:var(--mid)">SKU: {{ $p->sku }}</div>@endif
                </div>
              </div>
            </td>
            <td>
              <div class="price-cell" data-id="{{ $p->id }}">
                @if($pr)
                  <div style="font-weight:700">
                    @if($pr->min_price == $pr->max_price)
                      {{ number_format($pr->min_price, 2) }} EGP
                    @else
                      {{ number_format($pr->min_price, 2) }} – {{ number_format($pr->max_price, 2) }} EGP
                    @endif
                  </div>
                  @if($discPct > 0)
                    <div class="disc-badge">{{ $discPct }}% off</div>
                  @endif
                @else
                  <span style="color:var(--mid)">—</span>
                @endif
              </div>
            </td>
            <td>
              @php $qty = $pr?->total_stock ?? 0; @endphp
              @if($qty > 10)
                <span style="color:var(--green);font-weight:600">{{ $qty }}</span>
              @elseif($qty > 0)
                <span style="color:var(--yellow);font-weight:600">{{ $qty }}</span>
              @else
                <span style="color:var(--red);font-weight:600">0</span>
              @endif
            </td>
            <td>
              @php $vc = $pr?->var_count ?? 0; @endphp
              @if($vc > 1)
                <span style="font-size:12px;color:var(--orange);font-weight:600">{{ $vc }} variations</span>
              @else
                <span style="font-size:12px;color:var(--mid)">Simple</span>
              @endif
            </td>
            <td><span class="badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
            <td><span class="badge-{{ $p->acceptance_status ?? 'pending' }}">{{ ucfirst($p->acceptance_status ?? 'pending') }}</span></td>
            <td style="color:var(--mid);font-size:12px;white-space:nowrap">
              {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y') : '—' }}
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:14px">
                <a href="{{ route('vendor.products.show', $p->id) }}" class="action-link">View</a>
                <a href="{{ route('vendor.products.edit', $p->id) }}" class="action-link" style="color:var(--mid)">Edit All</a>
                <form method="POST" action="{{ route('vendor.products.destroy', $p->id) }}" onsubmit="return confirm('Delete this product? This cannot be undone.')">
                  @csrf @method('DELETE')
                  <button type="submit" class="action-link danger" style="background:none;border:none;padding:0">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

{{-- ── Floating Bulk Price Bar ───────────────────────────────────────── --}}
<div class="bulk-bar" id="bulk-bar">

  {{-- Selection count badge --}}
  <span class="bulk-sel-count" id="bulk-count">0</span>
  <span class="bulk-label">products selected</span>

  <div class="bulk-divider"></div>

  {{-- Action selector --}}
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <select class="bulk-select" id="bulk-action" onchange="onActionChange()">
      <option value="">— Choose action —</option>
      <option value="set_discount">Set Discount %</option>
      <option value="remove_discount">Remove All Discounts</option>
      <option value="increase_price">Increase Regular Price by %</option>
      <option value="decrease_price">Decrease Regular Price by %</option>
    </select>

    {{-- Value input (hidden for remove_discount) --}}
    <div class="bulk-val-wrap" id="bulk-val-wrap" style="display:none">
      <input type="number" class="bulk-val" id="bulk-value"
             min="0.01" step="0.01" placeholder="0"
             oninput="updateApplyBtn()">
      <span class="bulk-val-unit">%</span>
    </div>

    <button class="bulk-apply" id="bulk-apply" onclick="applyBulk()" disabled>
      Apply
    </button>
  </div>

  {{-- Feedback message --}}
  <div class="bulk-feedback" id="bulk-feedback"></div>

  <div style="flex:1"></div>

  <button class="bulk-clear" onclick="clearSelection()">✕ Clear</button>
</div>

<script>
const BULK_URL  = '{{ route("vendor.products.bulk-price") }}';
const CSRF_TOK  = document.querySelector('meta[name="csrf-token"]')?.content || '';

let selectedIds = new Set();

// ── Select / deselect ────────────────────────────────────────────
function toggleAll(checked) {
  document.querySelectorAll('.row-check').forEach(cb => {
    cb.checked = checked;
    cb.closest('tr').classList.toggle('row-selected', checked);
    if (checked) selectedIds.add(cb.value);
    else         selectedIds.delete(cb.value);
  });
  updateBar();
}

function toggleSelectAll() {
  const allChk = document.getElementById('select-all-chk');
  allChk.checked = !allChk.checked;
  toggleAll(allChk.checked);
}

function onRowCheck(cb) {
  if (cb.checked) {
    selectedIds.add(cb.value);
    cb.closest('tr').classList.add('row-selected');
  } else {
    selectedIds.delete(cb.value);
    cb.closest('tr').classList.remove('row-selected');
    document.getElementById('select-all-chk').checked = false;
  }
  updateBar();
}

function clearSelection() {
  selectedIds.clear();
  document.querySelectorAll('.row-check').forEach(cb => {
    cb.checked = false;
    cb.closest('tr').classList.remove('row-selected');
  });
  document.getElementById('select-all-chk').checked = false;
  updateBar();
}

function updateBar() {
  const n   = selectedIds.size;
  const bar = document.getElementById('bulk-bar');
  document.getElementById('bulk-count').textContent = n;
  if (n > 0) bar.classList.add('open');
  else        bar.classList.remove('open');
  hideFeedback();
  updateApplyBtn();
}

// ── Action change ─────────────────────────────────────────────────
function onActionChange() {
  const action   = document.getElementById('bulk-action').value;
  const valWrap  = document.getElementById('bulk-val-wrap');
  const valInput = document.getElementById('bulk-value');
  const needsVal = ['set_discount', 'increase_price', 'decrease_price'].includes(action);

  valWrap.style.display = needsVal ? '' : 'none';

  if (action === 'set_discount')    { valInput.max = '80';  valInput.placeholder = '0 – 80'; }
  if (action === 'increase_price')  { valInput.max = '100'; valInput.placeholder = '0 – 100'; }
  if (action === 'decrease_price')  { valInput.max = '50';  valInput.placeholder = '0 – 50'; }

  hideFeedback();
  updateApplyBtn();
}

function updateApplyBtn() {
  const action   = document.getElementById('bulk-action').value;
  const value    = parseFloat(document.getElementById('bulk-value').value);
  const needsVal = ['set_discount', 'increase_price', 'decrease_price'].includes(action);
  const apply    = document.getElementById('bulk-apply');
  apply.disabled = !action || selectedIds.size === 0 || (needsVal && (isNaN(value) || value <= 0));
}

function hideFeedback() {
  const fb = document.getElementById('bulk-feedback');
  fb.style.display = 'none';
  fb.className = 'bulk-feedback';
}

// ── Apply bulk ────────────────────────────────────────────────────
async function applyBulk() {
  const action  = document.getElementById('bulk-action').value;
  const value   = parseFloat(document.getElementById('bulk-value').value) || 0;
  const apply   = document.getElementById('bulk-apply');
  const feedback = document.getElementById('bulk-feedback');
  const n       = selectedIds.size;

  if (!action || n === 0) return;

  const labels = {
    set_discount:   `set a ${value}% discount on`,
    remove_discount:`remove all discounts from`,
    increase_price: `increase prices by ${value}% on`,
    decrease_price: `decrease prices by ${value}% on`,
  };
  if (!confirm(`This will ${labels[action]} ${n} product${n !== 1 ? 's' : ''}. Continue?`)) return;

  apply.disabled    = true;
  apply.textContent = 'Applying…';
  hideFeedback();

  try {
    const resp = await fetch(BULK_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOK },
      body:    JSON.stringify({ product_ids: [...selectedIds], action, value }),
    });

    const data = await resp.json();

    if (!resp.ok || data.error) throw new Error(data.error || `Server error ${resp.status}`);

    // ── Update price cells inline ─────────────────────────────
    if (data.prices) {
      Object.entries(data.prices).forEach(([id, p]) => {
        const cell = document.querySelector(`.price-cell[data-id="${id}"]`);
        if (!cell) return;

        const priceStr = p.min === p.max
          ? `${p.min.toFixed(2)} EGP`
          : `${p.min.toFixed(2)} – ${p.max.toFixed(2)} EGP`;

        const discHtml = p.discount > 0
          ? `<div class="disc-badge">${p.discount}% off</div>`
          : '';

        cell.innerHTML = `<div style="font-weight:700">${priceStr}</div>${discHtml}`;
        cell.classList.remove('price-updated');
        void cell.offsetWidth; // reflow to restart animation
        cell.classList.add('price-updated');
      });
    }

    // ── Show success ──────────────────────────────────────────
    feedback.textContent = `✓ ${data.updated} product${data.updated !== 1 ? 's' : ''} updated`;
    feedback.className   = 'bulk-feedback ok';
    feedback.style.display = '';

    // Auto-clear after 2 s
    setTimeout(() => {
      clearSelection();
      document.getElementById('bulk-action').value = '';
      onActionChange();
    }, 2000);

  } catch (err) {
    feedback.textContent = `✗ ${err.message || 'Something went wrong'}`;
    feedback.className   = 'bulk-feedback err';
    feedback.style.display = '';
  } finally {
    apply.disabled    = false;
    apply.textContent = 'Apply';
    updateApplyBtn();
  }
}
</script>

@endsection
