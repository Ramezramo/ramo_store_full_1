@extends('admin.layout')
@section('title', 'Categories, Brands & Requests')
@section('page-title', 'Categories, Brands & Requests')

@push('styles')
<style>
/* ── Page tabs ──────────────────────────────────────────── */
.page-tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:0}
.page-tab{padding:9px 18px;font-size:13px;font-weight:600;color:var(--muted);border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;border-radius:6px 6px 0 0;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.page-tab:hover{color:var(--text)}
.page-tab.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-badge{background:var(--accent);color:#fff;font-size:9px;font-weight:800;padding:1px 5px;border-radius:10px;min-width:16px;text-align:center}
.tab-panel{display:none}
.tab-panel.active{display:block}

/* ── Summary chips ──────────────────────────────────────── */
.summary-row{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.summary-chip{display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--border);border-radius:8px;padding:10px 14px;flex:1;min-width:120px;text-decoration:none;color:inherit;transition:.13s}
.summary-chip:hover{border-color:var(--accent)}
.summary-chip-value{font-size:22px;font-weight:800;line-height:1}
.summary-chip-label{font-size:11px;color:var(--muted)}

/* ── Cat/brand item rows ────────────────────────────────── */
.cat-tree{display:flex;flex-direction:column;gap:2px}
.cat-row{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;border:1px solid transparent;transition:.13s;background:var(--card);cursor:pointer}
.cat-row:hover{border-color:var(--border);background:rgba(255,255,255,.03)}
.cat-row.is-child{margin-left:28px;border-left:2px solid var(--border);border-radius:0 8px 8px 0}

/* ── Thumbnail in row ───────────────────────────────────── */
.cat-thumb{width:36px;height:36px;border-radius:6px;object-fit:cover;background:rgba(255,255,255,.06);border:1px solid var(--border);flex-shrink:0}
.cat-thumb-placeholder{width:36px;height:36px;border-radius:6px;background:rgba(255,255,255,.06);border:1px dashed var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--muted)}

.cat-row-name{flex:1;font-size:13px;font-weight:600;min-width:0}
.cat-row-meta{font-size:11px;color:var(--muted);white-space:nowrap}
.cat-row-actions{display:flex;gap:5px;flex-shrink:0;align-items:center}

/* ── Inline forms ───────────────────────────────────────── */
.inline-edit-form{display:none;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:16px;margin-top:4px;margin-bottom:4px}
.inline-edit-form.is-child{margin-left:28px}
.inline-edit-form.open{display:block}
.inline-form-row{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end}
.inline-form-row .form-group{flex:1;min-width:140px}
.inline-form-row input,.inline-form-row select{width:100%;padding:7px 10px;font-size:12px}

/* ── Image upload widget ────────────────────────────────── */
.img-upload-zone{position:relative}
.img-preview-wrap{position:relative;display:inline-block}
.img-preview{width:72px;height:72px;border-radius:8px;object-fit:cover;border:2px solid var(--border);display:block}
.img-remove-btn{position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:var(--red);color:#fff;border:none;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;line-height:1}
.img-drop-area{width:72px;height:72px;border-radius:8px;border:2px dashed var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:.15s;background:rgba(255,255,255,.03);font-size:10px;color:var(--muted);gap:4px;text-align:center}
.img-drop-area:hover{border-color:var(--accent);color:var(--accent);background:rgba(232,93,38,.05)}
.img-drop-area svg{opacity:.5}
.img-file-input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}

/* ── Add card ───────────────────────────────────────────── */
.add-card{background:rgba(232,93,38,.05);border:1px dashed rgba(232,93,38,.3);border-radius:10px;padding:16px;margin-bottom:20px;display:none}
.add-card.open{display:block}
.add-card-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:12px}

/* ── Delete confirm strip ───────────────────────────────── */
.del-confirm{display:none;gap:5px;align-items:center;margin-top:4px;padding:6px 8px;background:rgba(239,68,68,.06);border-radius:6px}
.del-confirm.open{display:flex}

/* ── Requests ───────────────────────────────────────────── */
.req-note-form{display:none;margin-top:8px}
.req-note-form.open{display:flex;flex-direction:column;gap:6px}
.req-note-form input,.req-note-form select{padding:5px 8px;border-radius:5px;border:1px solid var(--border);background:var(--card);color:var(--text);font-size:12px;width:220px}

/* ── Misc ───────────────────────────────────────────────── */
.filter-bar{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;align-items:center}
.filter-bar input[type=search]{flex:1;min-width:180px;padding:7px 12px;font-size:13px}
</style>
@endpush

@section('content')

@php $activeTab = session('tab', request('tab', 'categories')); @endphp

@if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">✗ {{ session('error') }}</div>@endif

{{-- ── Tabs ──────────────────────────────────────────────── --}}
<div class="page-tabs">
  <a href="?tab=categories" class="page-tab {{ $activeTab==='categories' ? 'active':'' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Categories
    <span class="tab-badge" style="background:rgba(255,255,255,.1);color:var(--muted)">{{ $parentCats->count()+$childCats->flatten()->count() }}</span>
  </a>
  <a href="?tab=brands" class="page-tab {{ $activeTab==='brands' ? 'active':'' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="12" cy="12" r="10"/><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"/></svg>
    Brands
    <span class="tab-badge" style="background:rgba(255,255,255,.1);color:var(--muted)">{{ $brands->count() }}</span>
  </a>
  <a href="?tab=requests" class="page-tab {{ $activeTab==='requests' ? 'active':'' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Vendor Requests
    @if($counts['pending']>0)<span class="tab-badge">{{ $counts['pending'] }}</span>@endif
  </a>
</div>


{{-- ════════════════════════════════════════════════════════
     TAB: CATEGORIES
════════════════════════════════════════════════════════ --}}
<div class="tab-panel {{ $activeTab==='categories' ? 'active':'' }}">

  <div class="summary-row">
    <div class="summary-chip"><div><div class="summary-chip-value">{{ $parentCats->count() }}</div><div class="summary-chip-label">Parent Categories</div></div></div>
    <div class="summary-chip"><div><div class="summary-chip-value">{{ $childCats->flatten()->count() }}</div><div class="summary-chip-label">Sub-categories</div></div></div>
    <div class="summary-chip" style="flex:2;justify-content:flex-end">
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-cat-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Category
      </button>
    </div>
  </div>

  {{-- Add Category form --}}
  <div class="add-card" id="add-cat-form">
    <div class="add-card-title">New Category</div>
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="inline-form-row" style="align-items:flex-start">

        {{-- Image upload --}}
        <div class="form-group" style="flex:0 0 auto">
          <label>Image</label>
          <div class="img-upload-zone">
            <div class="img-preview-wrap" id="add-cat-preview-wrap" style="display:none">
              <img src="" id="add-cat-preview-img" class="img-preview">
              <button type="button" class="img-remove-btn" onclick="clearImage('add-cat')">✕</button>
            </div>
            <label class="img-drop-area" id="add-cat-drop" for="add-cat-file">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Upload
            </label>
            <input type="file" name="image" id="add-cat-file" accept="image/*" class="img-file-input" style="position:static;opacity:1;width:0;height:0;overflow:hidden" onchange="previewImage(this,'add-cat')">
          </div>
        </div>

        <div class="form-group" style="flex:2;min-width:160px">
          <label>Name *</label>
          <input type="text" name="name" required placeholder="e.g. Summer Collection">
        </div>
        <div class="form-group">
          <label>Parent Category</label>
          <select name="parent_id">
            <option value="">— Top-level —</option>
            @foreach($parentCats as $pc)
              <option value="{{ $pc->id }}">{{ $pc->name }}</option>
              @if(isset($childCats[$pc->id]))
                @foreach($childCats[$pc->id] as $cc)
                  <option value="{{ $cc->id }}">&nbsp;&nbsp;↳ {{ $cc->name }}</option>
                @endforeach
              @endif
            @endforeach
          </select>
        </div>
        <div class="form-group" style="max-width:80px">
          <label>Order</label>
          <input type="number" name="menu_order" value="0" min="0">
        </div>
        <div class="form-group" style="flex:2">
          <label>Description</label>
          <input type="text" name="description" placeholder="Optional">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
        </div>
      </div>
    </form>
  </div>

  <div class="filter-bar">
    <input type="search" placeholder="Filter categories…" oninput="filterItems('cat-tree','.cat-item',this.value)">
    <span style="font-size:12px;color:var(--muted)">Click a row to edit inline</span>
  </div>

  <div class="cat-tree" id="cat-tree">
    @foreach($parentCats as $parent)
      @php
        $pCount    = $catCounts[$parent->id] ?? 0;
        $childList = $childCats[$parent->id] ?? collect();
        $hasKids   = $childList->count() > 0;
        $imgUrl    = $parent->image ? Storage::disk('public')->url($parent->image) : null;
      @endphp

      {{-- Parent row --}}
      <div class="cat-row cat-item" data-name="{{ strtolower($parent->name) }}"
           onclick="toggleEdit('edit-{{ $parent->id }}')">
        @if($imgUrl)
          <img src="{{ $imgUrl }}" class="cat-thumb" alt="">
        @else
          <div class="cat-thumb-placeholder">🖼</div>
        @endif
        <div class="cat-row-name">
          {{ $parent->name }}
          @if($hasKids)<span class="badge badge-blue" style="margin-left:6px;font-size:9px">{{ $childList->count() }} sub</span>@endif
        </div>
        <div class="cat-row-meta">{{ $pCount }} products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-{{ $parent->id }}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('del-{{ $parent->id }}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
          </button>
        </div>
      </div>

      {{-- Inline edit: parent --}}
      <div class="inline-edit-form" id="edit-{{ $parent->id }}" onclick="event.stopPropagation()">
        <form method="POST" action="{{ route('admin.categories.update', $parent->id) }}" enctype="multipart/form-data">
          @csrf @method('PATCH')
          <div class="inline-form-row" style="align-items:flex-start">

            <div class="form-group" style="flex:0 0 auto">
              <label>Image</label>
              <div class="img-upload-zone">
                <div class="img-preview-wrap" id="ec{{ $parent->id }}-preview-wrap" style="{{ $imgUrl ? '' : 'display:none' }}">
                  <img src="{{ $imgUrl ?? '' }}" id="ec{{ $parent->id }}-preview-img" class="img-preview">
                  <button type="button" class="img-remove-btn" onclick="clearImage('ec{{ $parent->id }}')">✕</button>
                </div>
                <label class="img-drop-area" id="ec{{ $parent->id }}-drop" for="ec{{ $parent->id }}-file"
                       style="{{ $imgUrl ? 'display:none' : '' }}">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  Upload
                </label>
                <input type="file" name="image" id="ec{{ $parent->id }}-file" accept="image/*"
                       style="width:0;height:0;overflow:hidden;position:absolute"
                       onchange="previewImage(this,'ec{{ $parent->id }}')">
                <input type="hidden" name="remove_image" id="ec{{ $parent->id }}-remove" value="">
              </div>
            </div>

            <div class="form-group" style="flex:2;min-width:140px">
              <label>Name *</label>
              <input type="text" name="name" value="{{ $parent->name }}" required>
            </div>
            <div class="form-group">
              <label>Parent</label>
              <select name="parent_id">
                <option value="">— Top-level —</option>
                @foreach($parentCats as $pc)
                  @if($pc->id===$parent->id)@continue@endif
                  <option value="{{ $pc->id }}" {{ $parent->parent==$pc->id ? 'selected':'' }}>{{ $pc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group" style="max-width:80px">
              <label>Order</label>
              <input type="number" name="menu_order" value="{{ $parent->menu_order ?? 0 }}" min="0">
            </div>
            <div class="form-group" style="flex:2">
              <label>Description</label>
              <input type="text" name="description" value="{{ $parent->description ?? '' }}">
            </div>
            <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-{{ $parent->id }}')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      {{-- Delete confirm --}}
      <div class="del-confirm" id="del-{{ $parent->id }}">
        <span style="font-size:12px;color:var(--red)">Delete "{{ $parent->name }}"?@if($hasKids) ({{ $childList->count() }} sub-cats will move to top-level)@endif</span>
        <form method="POST" action="{{ route('admin.categories.destroy', $parent->id) }}" style="display:contents">
          @csrf @method('DELETE')
          @if($hasKids)<input type="hidden" name="force" value="1">@endif
          <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-{{ $parent->id }}')">Cancel</button>
      </div>

      {{-- Children --}}
      @foreach($childList as $child)
        @php
          $cCount  = $catCounts[$child->id] ?? 0;
          $cImgUrl = $child->image ? Storage::disk('public')->url($child->image) : null;
        @endphp
        <div class="cat-row cat-item is-child" data-name="{{ strtolower($child->name) }}"
             onclick="toggleEdit('edit-{{ $child->id }}')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11" style="color:var(--muted);flex-shrink:0"><polyline points="9 18 3 12 9 6"/></svg>
          @if($cImgUrl)
            <img src="{{ $cImgUrl }}" class="cat-thumb" alt="" style="width:28px;height:28px">
          @else
            <div class="cat-thumb-placeholder" style="width:28px;height:28px;font-size:11px">🖼</div>
          @endif
          <div class="cat-row-name" style="font-weight:500;color:var(--muted)">{{ $child->name }}</div>
          <div class="cat-row-meta">{{ $cCount }} products</div>
          <div class="cat-row-actions" onclick="event.stopPropagation()">
            <button class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-{{ $child->id }}')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('del-{{ $child->id }}')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
            </button>
          </div>
        </div>

        <div class="inline-edit-form is-child" id="edit-{{ $child->id }}" onclick="event.stopPropagation()">
          <form method="POST" action="{{ route('admin.categories.update', $child->id) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="inline-form-row" style="align-items:flex-start">

              <div class="form-group" style="flex:0 0 auto">
                <label>Image</label>
                <div class="img-upload-zone">
                  <div class="img-preview-wrap" id="ec{{ $child->id }}-preview-wrap" style="{{ $cImgUrl ? '' : 'display:none' }}">
                    <img src="{{ $cImgUrl ?? '' }}" id="ec{{ $child->id }}-preview-img" class="img-preview">
                    <button type="button" class="img-remove-btn" onclick="clearImage('ec{{ $child->id }}')">✕</button>
                  </div>
                  <label class="img-drop-area" id="ec{{ $child->id }}-drop" for="ec{{ $child->id }}-file"
                         style="{{ $cImgUrl ? 'display:none' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Upload
                  </label>
                  <input type="file" name="image" id="ec{{ $child->id }}-file" accept="image/*"
                         style="width:0;height:0;overflow:hidden;position:absolute"
                         onchange="previewImage(this,'ec{{ $child->id }}')">
                  <input type="hidden" name="remove_image" id="ec{{ $child->id }}-remove" value="">
                </div>
              </div>

              <div class="form-group" style="flex:2;min-width:130px">
                <label>Name *</label>
                <input type="text" name="name" value="{{ $child->name }}" required>
              </div>
              <div class="form-group">
                <label>Parent</label>
                <select name="parent_id">
                  <option value="">— Top-level —</option>
                  @foreach($parentCats as $pc)
                    <option value="{{ $pc->id }}" {{ $child->parent==$pc->id ? 'selected':'' }}>{{ $pc->name }}</option>
                    @if(isset($childCats[$pc->id]))
                      @foreach($childCats[$pc->id] as $cc)
                        @if($cc->id===$child->id)@continue@endif
                        <option value="{{ $cc->id }}">&nbsp;&nbsp;↳ {{ $cc->name }}</option>
                      @endforeach
                    @endif
                  @endforeach
                </select>
              </div>
              <div class="form-group" style="max-width:80px">
                <label>Order</label>
                <input type="number" name="menu_order" value="{{ $child->menu_order ?? 0 }}" min="0">
              </div>
              <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
                <button type="submit" class="btn btn-success btn-sm">Save</button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('edit-{{ $child->id }}')">Cancel</button>
              </div>
            </div>
          </form>
        </div>

        <div class="del-confirm is-child" id="del-{{ $child->id }}">
          <span style="font-size:12px;color:var(--red)">Delete "{{ $child->name }}"?</span>
          <form method="POST" action="{{ route('admin.categories.destroy', $child->id) }}" style="display:contents">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
          </form>
          <button class="btn btn-ghost btn-sm" onclick="toggleDel('del-{{ $child->id }}')">Cancel</button>
        </div>
      @endforeach

    @endforeach
  </div>
</div>{{-- /tab categories --}}


{{-- ════════════════════════════════════════════════════════
     TAB: BRANDS
════════════════════════════════════════════════════════ --}}
<div class="tab-panel {{ $activeTab==='brands' ? 'active':'' }}">

  <div class="summary-row">
    <div class="summary-chip"><div><div class="summary-chip-value">{{ $brands->count() }}</div><div class="summary-chip-label">Total Brands</div></div></div>
    <div class="summary-chip" style="flex:2;justify-content:flex-end">
      <button class="btn btn-primary btn-sm" onclick="toggleAddForm('add-brand-form')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Brand
      </button>
    </div>
  </div>

  <div class="add-card" id="add-brand-form">
    <div class="add-card-title">New Brand</div>
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="inline-form-row" style="align-items:flex-start">
        <div class="form-group" style="flex:0 0 auto">
          <label>Logo / Image</label>
          <div class="img-upload-zone">
            <div class="img-preview-wrap" id="add-brand-preview-wrap" style="display:none">
              <img src="" id="add-brand-preview-img" class="img-preview">
              <button type="button" class="img-remove-btn" onclick="clearImage('add-brand')">✕</button>
            </div>
            <label class="img-drop-area" id="add-brand-drop" for="add-brand-file">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Upload
            </label>
            <input type="file" name="image" id="add-brand-file" accept="image/*"
                   style="width:0;height:0;overflow:hidden;position:absolute"
                   onchange="previewImage(this,'add-brand')">
          </div>
        </div>
        <div class="form-group" style="flex:1;min-width:160px">
          <label>Brand Name *</label>
          <input type="text" name="name" required placeholder="e.g. Nike">
        </div>
        <div class="form-group" style="justify-content:flex-end">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
        </div>
      </div>
    </form>
  </div>

  <div class="filter-bar">
    <input type="search" placeholder="Filter brands…" oninput="filterItems('brand-tree','.brand-item',this.value)">
  </div>

  <div class="cat-tree" id="brand-tree">
    @forelse($brands as $brand)
      @php
        $bCount  = $brandCounts[$brand->id] ?? 0;
        $bImgUrl = ($brand->image ?? null) ? Storage::disk('public')->url($brand->image) : null;
      @endphp
      <div class="cat-row brand-item" data-name="{{ strtolower($brand->name) }}"
           onclick="toggleEdit('bedit-{{ $brand->id }}')">
        @if($bImgUrl)
          <img src="{{ $bImgUrl }}" class="cat-thumb" alt="" style="background:#fff">
        @else
          <div class="cat-thumb-placeholder">🏷</div>
        @endif
        <div class="cat-row-name">{{ $brand->name }}</div>
        <div class="cat-row-meta">{{ $bCount }} products</div>
        <div class="cat-row-actions" onclick="event.stopPropagation()">
          <button class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-{{ $brand->id }}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="btn btn-danger btn-sm" onclick="event.stopPropagation();toggleDel('bdel-{{ $brand->id }}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
          </button>
        </div>
      </div>

      <div class="inline-edit-form" id="bedit-{{ $brand->id }}" onclick="event.stopPropagation()">
        <form method="POST" action="{{ route('admin.brands.update', $brand->id) }}" enctype="multipart/form-data">
          @csrf @method('PATCH')
          <div class="inline-form-row" style="align-items:flex-start">
            <div class="form-group" style="flex:0 0 auto">
              <label>Logo / Image</label>
              <div class="img-upload-zone">
                <div class="img-preview-wrap" id="eb{{ $brand->id }}-preview-wrap" style="{{ $bImgUrl ? '' : 'display:none' }}">
                  <img src="{{ $bImgUrl ?? '' }}" id="eb{{ $brand->id }}-preview-img" class="img-preview" style="background:#fff">
                  <button type="button" class="img-remove-btn" onclick="clearImage('eb{{ $brand->id }}')">✕</button>
                </div>
                <label class="img-drop-area" id="eb{{ $brand->id }}-drop" for="eb{{ $brand->id }}-file"
                       style="{{ $bImgUrl ? 'display:none' : '' }}">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  Upload
                </label>
                <input type="file" name="image" id="eb{{ $brand->id }}-file" accept="image/*"
                       style="width:0;height:0;overflow:hidden;position:absolute"
                       onchange="previewImage(this,'eb{{ $brand->id }}')">
                <input type="hidden" name="remove_image" id="eb{{ $brand->id }}-remove" value="">
              </div>
            </div>
            <div class="form-group" style="flex:1;min-width:140px">
              <label>Name *</label>
              <input type="text" name="name" value="{{ $brand->name }}" required>
            </div>
            <div class="form-group" style="flex-direction:row;gap:5px;justify-content:flex-end">
              <button type="submit" class="btn btn-success btn-sm">Save</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit('bedit-{{ $brand->id }}')">Cancel</button>
            </div>
          </div>
        </form>
      </div>

      <div class="del-confirm" id="bdel-{{ $brand->id }}">
        <span style="font-size:12px;color:var(--red)">Delete "{{ $brand->name }}"?</span>
        <form method="POST" action="{{ route('admin.brands.destroy', $brand->id) }}" style="display:contents">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
        </form>
        <button class="btn btn-ghost btn-sm" onclick="toggleDel('bdel-{{ $brand->id }}')">Cancel</button>
      </div>
    @empty
      <div class="card" style="text-align:center;padding:32px;color:var(--muted)">No brands yet.</div>
    @endforelse
  </div>
</div>{{-- /tab brands --}}


{{-- ════════════════════════════════════════════════════════
     TAB: VENDOR REQUESTS
════════════════════════════════════════════════════════ --}}
<div class="tab-panel {{ $activeTab==='requests' ? 'active':'' }}">

  <div class="summary-row">
    @foreach(['pending'=>['Pending','badge-yellow'],'approved'=>['Approved','badge-green'],'rejected'=>['Rejected','badge-red']] as $s=>[$label,$cls])
    <a href="?tab=requests&status={{ $s }}" class="summary-chip" style="{{ $status===$s ? 'border-color:var(--accent)':'' }}">
      <div><div class="summary-chip-value">{{ $counts[$s] }}</div><div class="summary-chip-label">{{ $label }}</div></div>
      @if($status===$s)<span class="badge {{ $cls }}" style="margin-left:auto">Active</span>@endif
    </a>
    @endforeach
  </div>

  <form method="GET" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="tab" value="requests">
    <div class="form-group">
      <label>Status</label>
      <select name="status" onchange="this.form.submit()" style="min-width:130px">
        <option value="pending"  {{ $status==='pending'  ? 'selected':'' }}>Pending</option>
        <option value="approved" {{ $status==='approved' ? 'selected':'' }}>Approved</option>
        <option value="rejected" {{ $status==='rejected' ? 'selected':'' }}>Rejected</option>
        <option value=""         {{ $status===''         ? 'selected':'' }}>All</option>
      </select>
    </div>
    <div class="form-group">
      <label>Type</label>
      <select name="type" onchange="this.form.submit()" style="min-width:130px">
        <option value=""         {{ $type===''         ? 'selected':'' }}>All Types</option>
        <option value="category" {{ $type==='category' ? 'selected':'' }}>Category</option>
        <option value="brand"    {{ $type==='brand'    ? 'selected':'' }}>Brand</option>
      </select>
    </div>
  </form>

  @if($requests->isEmpty())
    <div class="card" style="text-align:center;padding:48px">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="color:var(--muted);margin:0 auto 12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <p style="color:var(--muted)">No {{ $status ?: '' }} requests found.</p>
    </div>
  @else
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Type</th><th>Name</th><th>Parent</th>
            <th>Vendor</th><th>Status</th><th>Date</th><th>Actions / Note</th>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $req)
          <tr>
            <td style="color:var(--muted)">{{ $req->id }}</td>
            <td>@if($req->type==='category')<span class="badge badge-blue">Category</span>@else<span class="badge badge-purple">Brand</span>@endif</td>
            <td style="font-weight:600">{{ $req->name }}</td>
            <td style="font-size:12px">
              @if($req->type==='category')
                @if($req->parent_category_name)<span class="badge badge-green">↳ {{ $req->parent_category_name }}</span>
                @else<span style="color:var(--muted)">Top-level</span>@endif
              @else<span style="color:var(--muted)">—</span>@endif
            </td>
            <td style="font-size:12px">{{ $req->vendor_name ?? '—' }}</td>
            <td>@if($req->status==='pending')<span class="badge badge-yellow">Pending</span>@elseif($req->status==='approved')<span class="badge badge-green">Approved</span>@else<span class="badge badge-red">Rejected</span>@endif</td>
            <td style="color:var(--muted);font-size:12px;white-space:nowrap">{{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}</td>
            <td>
              @if($req->status==='pending')
              <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-start">
                <div style="display:flex;gap:5px">
                  <button onclick="toggleNote('req-a-{{ $req->id }}')" class="btn btn-sm btn-success">✓ Approve</button>
                  <button onclick="toggleNote('req-r-{{ $req->id }}')" class="btn btn-sm btn-danger">✗ Reject</button>
                </div>
                <div id="req-a-{{ $req->id }}" class="req-note-form">
                  <form method="POST" action="{{ route('admin.cbr.approve', $req->id) }}">
                    @csrf @method('PATCH')
                    @if($req->type==='category')
                    <div>
                      <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:2px">Override parent</label>
                      <select name="parent_category_id">
                        <option value="">— Keep as requested —</option>
                        @foreach($parentCats as $cat)
                          <option value="{{ $cat->id }}" {{ $req->parent_category_id==$cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
                          @if(isset($childCats[$cat->id]))
                            @foreach($childCats[$cat->id] as $cc)
                              <option value="{{ $cc->id }}" {{ $req->parent_category_id==$cc->id ? 'selected':'' }}>&nbsp;&nbsp;↳ {{ $cc->name }}</option>
                            @endforeach
                          @endif
                        @endforeach
                      </select>
                    </div>
                    @endif
                    <input type="text" name="admin_note" placeholder="Note (optional)">
                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                  </form>
                </div>
                <div id="req-r-{{ $req->id }}" class="req-note-form">
                  <form method="POST" action="{{ route('admin.cbr.reject', $req->id) }}">
                    @csrf @method('PATCH')
                    <input type="text" name="admin_note" placeholder="Reason (optional)">
                    <button type="submit" class="btn btn-sm btn-danger">Confirm</button>
                  </form>
                </div>
              </div>
              @else
                <span style="color:var(--muted);font-size:12px">{{ $req->admin_note ?: '—' }}</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($requests->hasPages())<div class="pagination" style="margin-top:16px">{{ $requests->links() }}</div>@endif
  @endif
</div>{{-- /tab requests --}}

<script>
function toggleEdit(id){const el=document.getElementById(id);el.classList.toggle('open');if(el.classList.contains('open')){const i=el.querySelector('input[name="name"]');if(i)i.focus()}}
function toggleDel(id){document.getElementById(id).classList.toggle('open')}
function toggleAddForm(id){const el=document.getElementById(id);el.classList.toggle('open');if(el.classList.contains('open')){const i=el.querySelector('input[name="name"]');if(i)i.focus()}}
function toggleNote(id){document.getElementById(id).classList.toggle('open')}
function filterItems(treeId,selector,val){val=val.toLowerCase();document.querySelectorAll('#'+treeId+' '+selector).forEach(function(el){el.style.display=el.dataset.name.includes(val)?'':'none'})}

/* ── Image preview / clear ──────────────────────────────── */
function previewImage(input, prefix) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById(prefix+'-preview-img').src = e.target.result;
    document.getElementById(prefix+'-preview-wrap').style.display = '';
    const drop = document.getElementById(prefix+'-drop');
    if (drop) drop.style.display = 'none';
    const rm = document.getElementById(prefix+'-remove');
    if (rm) rm.value = '';
  };
  reader.readAsDataURL(input.files[0]);
}

function clearImage(prefix) {
  document.getElementById(prefix+'-preview-img').src = '';
  document.getElementById(prefix+'-preview-wrap').style.display = 'none';
  const drop = document.getElementById(prefix+'-drop');
  if (drop) drop.style.display = '';
  const fileInput = document.getElementById(prefix+'-file');
  if (fileInput) fileInput.value = '';
  const rm = document.getElementById(prefix+'-remove');
  if (rm) rm.value = '1';
}
</script>
@endsection
