<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>App Config — Ramo Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--c-bg:#f4f4f0;--c-white:#fff;--c-dark:#111;--c-mid:#555;--c-light:#e4e4e0;--c-orange:#e85d26;--c-green:#22a35c;--c-red:#e02020;--radius:10px;--shadow:0 2px 12px rgba(0,0,0,.07)}
body{font-family:'Inter',sans-serif;background:var(--c-bg);color:var(--c-dark);font-size:14px;min-height:100vh}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}

.admin-wrap{display:flex;min-height:100vh}
.sidebar{width:220px;background:var(--c-dark);color:#fff;flex-shrink:0;display:flex;flex-direction:column;padding:0}
.sidebar-brand{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.1);font-size:15px;font-weight:800}
.sidebar-brand span{color:var(--c-orange)}
.sidebar-nav{padding:12px 0;flex:1}
.sidebar-nav a{display:flex;align-items:center;gap:10px;padding:10px 18px;font-size:13px;color:rgba(255,255,255,.7);transition:all .15s}
.sidebar-nav a:hover,.sidebar-nav a.active{background:rgba(255,255,255,.08);color:#fff}
.sidebar-nav a .icon{font-size:16px;width:20px;text-align:center}
.sidebar-footer{padding:16px 18px;border-top:1px solid rgba(255,255,255,.1);font-size:12px;color:rgba(255,255,255,.4)}
.main{flex:1;min-width:0}
.topbar{background:var(--c-white);border-bottom:1.5px solid var(--c-light);padding:0 28px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.topbar-title{font-size:16px;font-weight:700}
.topbar-user{font-size:13px;color:var(--c-mid)}
.content{padding:28px}
.card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius);padding:20px;margin-bottom:20px}
.card-title{font-size:14px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between}
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center}
.filter-bar form{display:flex;gap:8px;flex-wrap:wrap;align-items:center;flex:1}
.filter-select{padding:8px 10px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit;background:var(--c-white);outline:none;color:var(--c-dark)}
.filter-select:focus{border-color:#888}
.filter-search{padding:8px 12px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit;background:var(--c-white);outline:none;color:var(--c-dark);min-width:180px}
.filter-search:focus{border-color:#888}
.btn-sm{padding:7px 14px;font-size:12.5px;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:all .12s}
.btn-dark{background:var(--c-dark);color:#fff}
.btn-dark:hover{background:#333}
.btn-orange{background:var(--c-orange);color:#fff}
.btn-orange:hover{opacity:.9}
.btn-outline{background:var(--c-white);border:1.5px solid var(--c-light);color:var(--c-dark)}
.btn-outline:hover{border-color:#888}
.btn-red{background:#fff0f0;color:var(--c-red);border:1.5px solid #fdd}
.btn-red:hover{background:#ffe0e0}
.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;margin-bottom:20px}
.stat-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius);padding:16px}
.stat-num{font-size:28px;font-weight:800;margin-bottom:2px}
.stat-label{font-size:12px;color:var(--c-mid);text-transform:uppercase;letter-spacing:.5px}
.config-table{width:100%;border-collapse:collapse}
.config-table th{padding:10px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--c-mid);border-bottom:2px solid var(--c-light);background:var(--c-bg)}
.config-table td{padding:12px 14px;border-bottom:1px solid var(--c-light);vertical-align:top}
.config-table tr:hover td{background:#fafaf8}
.config-table tr:last-child td{border-bottom:none}
.key-cell{font-family:monospace;font-size:13px;font-weight:700;color:var(--c-dark)}
.group-badge{display:inline-block;padding:2px 8px;border-radius:50px;font-size:11px;font-weight:600;background:var(--c-bg);color:var(--c-mid);border:1px solid var(--c-light)}
.lang-badge{display:inline-block;padding:2px 8px;border-radius:50px;font-size:11px;font-weight:600;margin-top:3px}
.lang-en{background:#e8f4fd;color:#1a56a0}
.lang-ar{background:#fff5e8;color:#b45309}
.lang-de{background:#edf7ee;color:#145c34}
.lang-null{background:var(--c-bg);color:#888}
.value-preview{font-family:monospace;font-size:12px;color:var(--c-mid);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.public-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.dot-green{background:var(--c-green)}
.dot-gray{background:#ccc}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:none;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal{background:var(--c-white);border-radius:12px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.modal-head{padding:20px 24px 16px;border-bottom:1px solid var(--c-light);display:flex;align-items:center;justify-content:space-between}
.modal-head h3{font-size:15px;font-weight:700}
.modal-close{background:none;border:none;font-size:22px;color:var(--c-mid);cursor:pointer;padding:0 4px}
.modal-body{padding:20px 24px}
.modal-foot{padding:16px 24px;border-top:1px solid var(--c-light);display:flex;gap:10px;justify-content:flex-end}
.form-row{margin-bottom:16px}
.form-row label{display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--c-mid);margin-bottom:6px}
.form-row input,.form-row select,.form-row textarea{width:100%;padding:9px 12px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:monospace;outline:none;background:var(--c-bg);color:var(--c-dark);transition:border-color .12s;resize:vertical}
.form-row textarea{min-height:160px;font-size:12px;line-height:1.6}
.form-row input:focus,.form-row textarea:focus,.form-row select:focus{border-color:#888;background:#fff}
.form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-meta{font-size:12px;color:var(--c-mid);margin-top:4px}
.toggle-row{display:flex;align-items:center;gap:8px;font-size:13px}
input[type=checkbox]{width:16px;height:16px;accent-color:var(--c-dark)}
.toast-bar{position:fixed;bottom:24px;right:24px;z-index:999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;background:var(--c-dark);box-shadow:0 4px 16px rgba(0,0,0,.15);animation:toastIn .25s ease;pointer-events:auto}
.toast.ok{background:var(--c-green)}
.toast.err{background:var(--c-red)}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.new-config-toggle{font-size:13px;color:var(--c-orange);cursor:pointer;font-weight:600;display:flex;align-items:center;gap:5px}
@media(max-width:900px){
  .admin-wrap{flex-direction:column}
  .sidebar{width:100%;flex-direction:row;align-items:center;padding:0 16px}
  .sidebar-brand{border-bottom:none;border-right:1px solid rgba(255,255,255,.1);padding:16px 16px 16px 0;margin-right:16px}
  .sidebar-nav{display:flex;flex-direction:row;padding:0}
  .sidebar-nav a{padding:16px 14px}
  .sidebar-footer{display:none}
}
</style>
</head>
<body>
<div class="admin-wrap">
  <aside class="sidebar">
    <div class="sidebar-brand">Ramo<span>Admin</span></div>
    <nav class="sidebar-nav">
      <a href="/admin/configs" class="active"><span class="icon">⚙️</span> App Config</a>
      <a href="/admin/auth-settings"><span class="icon">🔐</span> Auth Settings</a>
      <a href="/"><span class="icon">🏪</span> View Store</a>
      <a href="/shop"><span class="icon">🛍️</span> Shop</a>
      <a href="/admin/configs?group=layout"><span class="icon">📐</span> Layouts</a>
      <a href="/admin/configs?group=language"><span class="icon">🌐</span> Languages</a>
      <a href="/admin/configs?group=theme"><span class="icon">🎨</span> Theme</a>
      <a href="/admin/configs?group=product"><span class="icon">📦</span> Products</a>
      <a href="/admin/configs?group=payment"><span class="icon">💳</span> Payments</a>
      <a href="/logout" onclick="event.preventDefault();document.getElementById('logout-form').submit()"><span class="icon">🚪</span> Logout</a>
    </nav>
    <form id="logout-form" action="/logout" method="POST" style="display:none">@csrf</form>
    <div class="sidebar-footer">Signed in as {{ auth()->user()->name }}</div>
  </aside>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">App Configuration</div>
      <div style="display:flex;align-items:center;gap:16px">
        <a href="/admin/auth-settings" class="btn-sm btn-orange" style="font-size:12px;padding:6px 12px;border-radius:7px;display:inline-flex;align-items:center;gap:5px">🔐 Auth Settings</a>
        <a href="/api/app-config" target="_blank" class="btn-sm btn-outline" style="font-size:12px;padding:6px 12px;border-radius:7px;border:1.5px solid var(--c-light);display:inline-flex;align-items:center;gap:5px">📡 API Preview</a>
        <span class="topbar-user">{{ auth()->user()->email }}</span>
      </div>
    </div>

    <div class="content">
      <div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);border-radius:12px;padding:24px 28px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px">
        <div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--c-orange);margin-bottom:6px">Homepage Builder</div>
          <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:6px">Manage Home Page Layout & Widgets</div>
          <div style="font-size:13px;color:rgba(255,255,255,.6)">Add stats bars, promo blocks, testimonials, newsletters, banners, product grids, and more — drag & drop to reorder.</div>
        </div>
        <a href="/admin/timeline" style="flex-shrink:0;background:var(--c-orange);color:#fff;padding:12px 22px;border-radius:9px;font-size:13px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:7px;white-space:nowrap">📐 Open Homepage Builder →</a>
      </div>
      <div class="stats-row">
        @foreach($groups as $g)
        <div class="stat-card">
          <div class="stat-num">{{ $g->cnt }}</div>
          <div class="stat-label">{{ $g->config_group }}</div>
        </div>
        @endforeach
      </div>

      <div class="card" style="padding:16px">
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
                  <button class="btn-sm btn-outline" style="margin-right:4px" onclick="openEdit({{ $cfg->id }}, {{ json_encode($cfg->config_key) }}, {{ json_encode($cfg->value) }}, {{ json_encode($cfg->label ?? '') }}, {{ json_encode($cfg->description ?? '') }}, {{ $cfg->is_public ? 'true' : 'false' }})">Edit</button>
                  <button class="btn-sm btn-red" onclick="deleteConfig({{ $cfg->id }}, '{{ $cfg->config_key }}')">Del</button>
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
    </div>
  </div>
</div>

<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <div class="modal-head">
      <h3>Edit Config: <code id="edit-modal-key" style="font-size:13px;background:#f4f4f0;padding:2px 8px;border-radius:4px"></code></h3>
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
</body>
</html>