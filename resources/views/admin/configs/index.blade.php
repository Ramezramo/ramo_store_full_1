@extends('admin.layout')
@section('title', 'App Configs')
@section('page-title', 'App Configuration')

@push('styles')
<style>
.config-page{--c-bg:var(--bg);--c-white:var(--card);--c-dark:var(--text);--c-mid:var(--muted);--c-light:var(--border);--c-orange:var(--accent);--c-green:var(--green);--c-red:var(--red);max-width:1200px}
.config-page .card{margin-bottom:20px}
.config-page .card-title{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.config-page .filter-select,.config-page .filter-search,.config-page .form-row input:not([type=checkbox]),.config-page .form-row select,.config-page .form-row textarea{padding:9px 12px;border:1px solid var(--border);border-radius:7px;font-size:13px;font-family:inherit;background:var(--bg);color:var(--text);outline:none;min-width:0}
.config-page .filter-select:focus,.config-page .filter-search:focus,.config-page .form-row :is(input,select,textarea):focus{border-color:var(--accent)}
.config-page .btn-sm{display:inline-flex;align-items:center;justify-content:center;padding:8px 14px;border:1px solid transparent;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer}
.config-page .btn-dark,.config-page .btn-outline{background:rgba(255,255,255,.06);color:var(--text);border-color:var(--border)}
.config-page .btn-orange{background:var(--accent);color:#fff}
.config-page .btn-red{background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.2)}
.config-page .config-shortcuts{display:flex;gap:8px;flex-wrap:wrap}
.config-page .config-shortcuts a{display:inline-flex;align-items:center;min-height:36px;padding:7px 12px;border:1px solid var(--border);border-radius:7px;background:var(--bg);color:var(--text);font-size:12px;font-weight:600}
.config-page .config-shortcuts a:hover,.config-page .config-shortcuts a[aria-current="page"]{color:var(--accent);border-color:var(--accent)}
.config-page #config-list{scroll-margin-top:85px}
.config-page .stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.config-page .stat-card{padding:16px}.config-page .stat-num{font-size:24px;font-weight:800}.config-page .stat-label{text-transform:uppercase;letter-spacing:.05em}
.config-page .config-table{width:100%;border-collapse:collapse}.config-page .config-table th{background:rgba(255,255,255,.03)}
.config-page .key-cell{font-family:monospace;font-size:13px;font-weight:700;overflow-wrap:anywhere}
.config-page .group-badge,.config-page .lang-badge{display:inline-block;padding:3px 8px;border-radius:20px;font-size:11px;background:rgba(255,255,255,.07);color:var(--muted);margin:2px}
.config-page .value-preview{font-family:monospace;font-size:12px;color:var(--muted);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.config-page .public-dot{width:8px;height:8px;border-radius:50%;display:inline-block}.config-page .dot-green{background:var(--green)}.config-page .dot-gray{background:#64748b}
.config-page .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:200;display:none;align-items:center;justify-content:center;padding:20px}
.config-page .modal-overlay.open{display:flex}.config-page .modal{background:var(--card);border:1px solid var(--border);border-radius:12px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.config-page .modal-head,.config-page .modal-foot{padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:10px}
.config-page .modal-head{border-bottom:1px solid var(--border)}.config-page .modal-head h3{font-size:15px}.config-page .modal-close{background:none;border:0;color:var(--muted);font-size:24px}
.config-page .modal-body{padding:20px}.config-page .modal-foot{border-top:1px solid var(--border);justify-content:flex-end}
.config-page .form-row{display:block;margin-bottom:16px}.config-page .form-row label:not(.toggle-row){display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px}
.config-page .form-row input:not([type=checkbox]),.config-page .form-row select,.config-page .form-row textarea{width:100%}
.config-page .form-row textarea{min-height:160px;font-family:monospace;line-height:1.6;resize:vertical}.config-page .form-meta{font-size:12px;color:var(--muted);margin-top:6px;overflow-wrap:anywhere}
.config-page .toggle-row{display:inline-flex;align-items:center;gap:8px;font-size:13px}.config-page input[type=checkbox],.config-page input[type=radio]{width:16px;height:16px;accent-color:var(--accent);flex-shrink:0}
.config-page .mobile-layout-form,.config-page .mobile-layout-options{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.config-page .mobile-layout-options{flex:1}
.config-page .mobile-layout-option{display:flex;align-items:flex-start;gap:10px;min-width:min(230px,100%);flex:1;padding:13px 14px;border:1px solid var(--border);border-radius:9px;background:var(--bg);cursor:pointer}
.config-page .mobile-layout-option:has(input:checked){border-color:var(--accent);background:rgba(232,93,38,.1)}.config-page .mobile-layout-option input{margin-top:2px}
.config-page .mobile-layout-option strong,.config-page .mobile-layout-option span span{display:block}.config-page .mobile-layout-option strong{font-size:13px;margin-bottom:3px}.config-page .mobile-layout-option span span{font-size:12px;color:var(--muted);line-height:1.45}
.config-page .toast-bar{position:fixed;bottom:24px;right:24px;z-index:999;display:flex;flex-direction:column;gap:8px;pointer-events:none}.config-page .toast{padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.25)}
.config-page .toast.ok{background:#166534}.config-page .toast.err{background:#991b1b}
@media(max-width:600px){.config-page .form-row-2{grid-template-columns:1fr!important}.config-page .mobile-layout-form{align-items:stretch}.config-page .mobile-layout-options{flex-basis:100%}}
</style>
@endpush

@section('content')
<div class="config-page">
  @if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
  @endif
      <div class="card">
        <div class="card-title">Configuration groups</div>
        <p style="color:var(--muted);font-size:13px;line-height:1.5;margin-bottom:12px">Choose a group to view its settings in the configuration list.</p>
        <nav class="config-shortcuts" aria-label="Configuration groups">
          @foreach(['all' => 'All settings', 'layout' => 'Layouts', 'language' => 'Languages', 'theme' => 'Theme', 'product' => 'Products', 'payment' => 'Payments'] as $groupKey => $groupLabel)
            <a href="{{ route('admin.configs', $groupKey === 'all' ? [] : ['group' => $groupKey]) }}#config-list" @if($group === $groupKey) aria-current="page" @endif>{{ $groupLabel }}</a>
          @endforeach
        </nav>
        <div class="card-title" style="margin-top:20px">Quick links</div>
        <nav class="config-shortcuts" aria-label="Related pages">
          <a href="{{ route('admin.timeline') }}">Homepage Builder</a>
          <a href="{{ route('admin.auth-settings') }}">Auth Settings</a>
          <a href="{{ route('home') }}">View Store</a>
          <a href="{{ route('shop') }}">Shop</a>
          <a href="{{ url('/api/app-config') }}" target="_blank" rel="noopener">API Preview ↗</a>
        </nav>
      </div>
      <div class="card" id="shop-display-settings">
        <div class="card-title">
          <span>Shop Categories &amp; Filters</span>
          <a href="{{ route('shop') }}" target="_blank" rel="noopener" style="font-size:12px;color:var(--c-orange)">View shop ↗</a>
        </div>
        <form method="POST" action="{{ route('admin.configs.shop-display') }}">
          @csrf
          <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:18px">
            <label style="display:grid;gap:6px;flex:1;min-width:220px;font-size:13px;font-weight:600">
              Category row
              <select class="filter-select" name="category_layout">
                <option value="scroll" @selected(old('category_layout', $shopDisplay['category_layout']) === 'scroll')>Swipeable single row</option>
                <option value="wrap" @selected(old('category_layout', $shopDisplay['category_layout']) === 'wrap')>Wrap into multiple rows</option>
                <option value="hidden" @selected(old('category_layout', $shopDisplay['category_layout']) === 'hidden')>Hide category row</option>
              </select>
            </label>
            <label style="display:grid;gap:6px;min-width:150px;font-size:13px;font-weight:600">
              Categories shown
              <input class="filter-search" type="number" name="category_limit" min="1" max="30" required value="{{ old('category_limit', $shopDisplay['category_limit']) }}" style="min-width:0">
            </label>
          </div>
          <p style="font-size:12px;color:var(--c-mid);line-height:1.5;margin-bottom:14px">Selected categories stay visible. The count excludes the All products button.</p>
          <fieldset style="border:0;margin-bottom:14px">
            <legend style="font-size:13px;font-weight:600;margin-bottom:8px">Choose categories for the row</legend>
            <div style="display:flex;flex-wrap:wrap;gap:10px 18px;max-height:170px;overflow-y:auto;padding:4px">
              @foreach($shopCategories as $category)
                <label class="toggle-row">
                  <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array((int) $category->id, array_map('intval', old('category_ids', $shopDisplay['category_ids'])), true))>
                  {{ $category->name }}
                </label>
              @endforeach
            </div>
            <p style="font-size:12px;color:var(--c-mid);line-height:1.5;margin-top:8px">Leave all unchecked to show categories in their usual order.</p>
          </fieldset>
          <div style="font-size:13px;font-weight:700;margin-bottom:10px">Filter panel</div>
          <div style="display:flex;flex-wrap:wrap;gap:14px 22px;margin-bottom:12px">
            @foreach([
              'filters_enabled' => 'Show filters button',
              'filter_price' => 'Price range',
              'filter_categories' => 'Categories',
              'filter_brands' => 'Brands',
              'filter_sort' => 'Sort by',
            ] as $settingKey => $settingLabel)
              <label class="toggle-row">
                <input type="hidden" name="{{ $settingKey }}" value="0">
                <input type="checkbox" name="{{ $settingKey }}" value="1" @checked((bool) old($settingKey, $shopDisplay[$settingKey]))>
                {{ $settingLabel }}
              </label>
            @endforeach
          </div>
          <p style="font-size:12px;color:var(--c-mid);line-height:1.5;margin-bottom:16px">Switches control which options customers see in the filter panel.</p>
          <button class="btn-sm btn-orange" type="submit">Save shop settings</button>
        </form>
      </div>

      <div class="card">
        <div class="card-title">
          <span>Shop Mobile Product Layout</span>
          <span style="font-size:12px;font-weight:400;color:var(--c-mid)">Applies on phones up to 480px wide</span>
        </div>
        <form class="mobile-layout-form" method="POST" action="{{ route('admin.configs.shop-mobile-layout') }}">
          @csrf
          <div class="mobile-layout-options">
            <label class="mobile-layout-option">
              <input type="radio" name="layout" value="grid" {{ $shopMobileLayout === 'grid' ? 'checked' : '' }}>
              <span>
                <strong>Two-column grid</strong>
                <span>Show two vertical product cards side by side.</span>
              </span>
            </label>
            <label class="mobile-layout-option">
              <input type="radio" name="layout" value="horizontal" {{ $shopMobileLayout === 'horizontal' ? 'checked' : '' }}>
              <span>
                <strong>Horizontal list</strong>
                <span>Show one product per row, with the image on the left.</span>
              </span>
            </label>
          </div>
          <button class="btn-sm btn-orange" type="submit">Save mobile layout</button>
        </form>
      </div>

      <div class="stats-row">
        @foreach($groups as $g)
        <div class="stat-card">
          <div class="stat-num">{{ $g->cnt }}</div>
          <div class="stat-label">{{ $g->config_group }}</div>
        </div>
        @endforeach
      </div>

      <div class="card" id="config-list" style="padding:16px">
        <form method="GET" action="/admin/configs" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
          <select name="group" class="filter-select" onchange="this.form.submit()">
            <option value="all" {{ $group==='all'?'selected':'' }}>All Groups</option>
            @foreach($groups as $g)
            <option value="{{ $g->config_group }}" {{ $group===$g->config_group?'selected':'' }}>{{ ucfirst($g->config_group) }} ({{ $g->cnt }})</option>
            @endforeach
          </select>
          <select name="lang" class="filter-select" onchange="this.form.submit()">
            <option value="all" {{ $lang==='all'?'selected':'' }}>All Languages</option>
            <option value="null" {{ $lang==='null'?'selected':'' }}>Language-Independent</option>
            @foreach($langs as $l)
              @if($l->lang)
              <option value="{{ $l->lang }}" {{ $lang===$l->lang?'selected':'' }}>{{ strtoupper($l->lang) }} ({{ $l->cnt }})</option>
              @endif
            @endforeach
          </select>
          <input type="text" name="search" class="filter-search" value="{{ $search }}" placeholder="Search key or label…">
          <button type="submit" class="btn-sm btn-dark">Filter</button>
          <a href="/admin/configs" class="btn-sm btn-outline">Reset</a>
          <div style="margin-left:auto">
            <button type="button" class="btn-sm btn-orange" onclick="openNewModal()">+ New Config</button>
          </div>
        </form>
      </div>

      <div class="card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
          <table class="config-table">
            <thead>
              <tr>
                <th>Key</th>
                <th>Label</th>
                <th>Group / Lang</th>
                <th>Value Preview</th>
                <th style="text-align:center;width:60px">Public</th>
                <th style="text-align:right;width:120px">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($configs as $cfg)
              <tr id="row-{{ $cfg->id }}">
                <td><span class="key-cell">{{ $cfg->config_key }}</span></td>
                <td style="font-size:13px;color:var(--c-mid);max-width:180px">{{ $cfg->label ?? '—' }}</td>
                <td>
                  <span class="group-badge">{{ $cfg->config_group }}</span>
                  @if($cfg->lang)
                    <span class="lang-badge lang-{{ $cfg->lang }}">{{ strtoupper($cfg->lang) }}</span>
                  @else
                    <span class="lang-badge lang-null">all langs</span>
                  @endif
                </td>
                <td>
                  <div class="value-preview" title="{{ e($cfg->value) }}">{{ $cfg->value }}</div>
                </td>
                <td style="text-align:center">
                  <span class="public-dot {{ $cfg->is_public ? 'dot-green' : 'dot-gray' }}" title="{{ $cfg->is_public ? 'Public' : 'Private' }}"></span>
                </td>
                <td style="text-align:right">
                  <button class="btn-sm btn-outline" style="margin-right:4px" onclick="openEdit({{ $cfg->id }}, {{ Illuminate\Support\Js::from($cfg->config_key) }}, {{ Illuminate\Support\Js::from($cfg->value) }}, {{ Illuminate\Support\Js::from($cfg->label ?? '') }}, {{ Illuminate\Support\Js::from($cfg->description ?? '') }}, {{ $cfg->is_public ? 'true' : 'false' }})">Edit</button>
                  <button class="btn-sm btn-red" onclick="deleteConfig({{ $cfg->id }}, {{ Illuminate\Support\Js::from($cfg->config_key) }})">Del</button>
                </td>
              </tr>
              @empty
              <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--c-mid)">No configs found. Try a different filter.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($configs->count() > 0)
        <div style="padding:12px 16px;border-top:1px solid var(--c-light);font-size:12px;color:var(--c-mid)">
          Showing {{ $configs->count() }} config{{ $configs->count()!=1?'s':'' }}
        </div>
        @endif
      </div>

<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <div class="modal-head">
      <h3>Edit Config: <code id="edit-modal-key" style="font-size:13px;background:var(--bg);padding:2px 8px;border-radius:4px"></code></h3>
      <button class="modal-close" onclick="closeModal('edit-modal')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-id">
      <div class="form-row">
        <label>Label <span style="font-weight:400;text-transform:none;letter-spacing:0">(human-readable name for admin)</span></label>
        <input type="text" id="edit-label" placeholder="e.g. App Name">
      </div>
      <div class="form-row">
        <label>Value <span style="font-weight:400;text-transform:none;letter-spacing:0">(JSON — strings must be quoted)</span></label>
        <textarea id="edit-value" placeholder='e.g. "hello world" or {"key": "value"} or true or 42'></textarea>
        <div class="form-meta">Examples: <code>"My App"</code> · <code>true</code> · <code>42</code> · <code>["en","ar"]</code> · <code>{"color":"#e85d26"}</code></div>
      </div>
      <div class="form-row">
        <label>Description</label>
        <input type="text" id="edit-description" placeholder="Optional description for this config">
      </div>
      <div class="form-row">
        <label class="toggle-row">
          <input type="checkbox" id="edit-public"> Expose to public API (Flutter app can read this)
        </label>
      </div>
      <div id="edit-json-error" style="color:var(--c-red);font-size:12px;margin-top:-8px;margin-bottom:12px;display:none">⚠ Invalid JSON — please fix before saving.</div>
    </div>
    <div class="modal-foot">
      <button class="btn-sm btn-outline" onclick="closeModal('edit-modal')">Cancel</button>
      <button class="btn-sm btn-dark" onclick="saveEdit()">Save Changes</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="new-modal">
  <div class="modal">
    <div class="modal-head">
      <h3>New Config</h3>
      <button class="modal-close" onclick="closeModal('new-modal')">×</button>
    </div>
    <div class="modal-body">
      <div class="form-row-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-row" style="margin:0">
          <label>Config Key *</label>
          <input type="text" id="new-key" placeholder="e.g. shipping_fee" style="font-family:monospace">
        </div>
        <div class="form-row" style="margin:0">
          <label>Group *</label>
          <select id="new-group" class="filter-select" style="width:100%;padding:9px 12px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit">
            @foreach($groups as $g)
            <option value="{{ $g->config_group }}">{{ ucfirst($g->config_group) }}</option>
            @endforeach
            <option value="general">general</option>
          </select>
        </div>
      </div>
      <div class="form-row-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
        <div class="form-row" style="margin:0">
          <label>Language</label>
          <select id="new-lang" class="filter-select" style="width:100%;padding:9px 12px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit">
            <option value="">All languages</option>
            <option value="en">en (English)</option>
            <option value="ar">ar (Arabic)</option>
            <option value="de">de (German)</option>
          </select>
        </div>
        <div class="form-row" style="margin:0">
          <label>Label</label>
          <input type="text" id="new-label" placeholder="Human-readable name">
        </div>
      </div>
      <div class="form-row" style="margin-top:14px">
        <label>Value * <span style="font-weight:400;text-transform:none;letter-spacing:0">(JSON)</span></label>
        <textarea id="new-value" placeholder='e.g. "hello world" or {"key":"val"} or true'></textarea>
      </div>
      <div class="form-row">
        <label class="toggle-row">
          <input type="checkbox" id="new-public" checked> Public API
        </label>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn-sm btn-outline" onclick="closeModal('new-modal')">Cancel</button>
      <button class="btn-sm btn-orange" onclick="createConfig()">Create</button>
    </div>
  </div>
</div>

<div class="toast-bar" id="toast-bar"></div>


</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
function showToast(msg, type='ok') { const bar = document.getElementById('toast-bar'); const t = document.createElement('div'); t.className = `toast ${type}`; t.textContent = msg; bar.appendChild(t); setTimeout(() => t.remove(), 3200); }
function openEdit(id, key, value, label, desc, isPublic) { document.getElementById('edit-id').value = id; document.getElementById('edit-modal-key').textContent = key; try { document.getElementById('edit-value').value = JSON.stringify(JSON.parse(value), null, 2); } catch(e) { document.getElementById('edit-value').value = value; } document.getElementById('edit-label').value = label; document.getElementById('edit-description').value = desc; document.getElementById('edit-public').checked = isPublic; document.getElementById('edit-json-error').style.display = 'none'; document.getElementById('edit-modal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openNewModal() { document.getElementById('new-modal').classList.add('open'); }
async function saveEdit() { const id = document.getElementById('edit-id').value; const value = document.getElementById('edit-value').value.trim(); const label = document.getElementById('edit-label').value; const isPublic = document.getElementById('edit-public').checked; try { JSON.parse(value); } catch(e) { document.getElementById('edit-json-error').style.display = 'block'; return; } document.getElementById('edit-json-error').style.display = 'none'; const res = await fetch(`/admin/configs/${id}`, { method: 'PUT', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({value, label, is_public: isPublic}) }); const data = await res.json(); if (data.success) { showToast('✓ Config saved!', 'ok'); closeModal('edit-modal'); const row = document.getElementById(`row-${id}`); if (row) { row.querySelector('.value-preview').textContent = value.substring(0, 80); row.querySelector('.value-preview').title = value; } } else { showToast(data.message || 'Save failed.', 'err'); } }
async function createConfig() { const key = document.getElementById('new-key').value.trim().replace(/\s+/g, '_').toLowerCase(); const group = document.getElementById('new-group').value; const lang = document.getElementById('new-lang').value; const value = document.getElementById('new-value').value.trim(); const label = document.getElementById('new-label').value; const isPublic = document.getElementById('new-public').checked; if (!key || !value) { showToast('Key and value are required.', 'err'); return; } try { JSON.parse(value); } catch(e) { showToast('Invalid JSON value.', 'err'); return; } const res = await fetch('/admin/configs', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify({config_key:key, config_group:group, lang: lang||null, value, label, is_public: isPublic}) }); const data = await res.json(); if (data.success) { showToast('✓ Config created!', 'ok'); closeModal('new-modal'); setTimeout(() => location.reload(), 600); } else { showToast(data.message || 'Failed.', 'err'); } }
async function deleteConfig(id, key) { if (!confirm(`Delete config "${key}"? This cannot be undone.`)) return; const res = await fetch(`/admin/configs/${id}`, { method: 'DELETE', headers: {'X-CSRF-TOKEN':CSRF} }); const data = await res.json(); if (data.success) { showToast('Deleted.', 'ok'); document.getElementById(`row-${id}`)?.remove(); } else { showToast(data.message || 'Delete failed.', 'err'); } }
document.querySelectorAll('.modal-overlay').forEach(overlay => { overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); }); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });
</script>
@endpush
