@extends('web.vendor.layout')
@section('title', 'Submit Request')
@section('page-title', 'Request New Category or Brand')

@section('content')
<div style="max-width:600px">
  <div style="background:var(--white);border:1px solid var(--light);border-radius:12px;padding:28px">
    <p style="color:var(--mid);font-size:13px;margin-bottom:24px;line-height:1.6">
      Can't find the category or brand you need? Submit a request and our admin team will review it.
      Once approved, it will be available for all vendors to use.
    </p>

    @if($errors->any())
      <div class="vs-alert vs-alert-error">
        <div>
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('vendor.requests.store') }}" id="req-form">
      @csrf

      <div class="vs-form-group">
        <label class="vs-label">Request Type <span style="color:var(--red)">*</span></label>
        <select name="type" id="req-type" class="vs-input {{ $errors->has('type') ? 'err' : '' }}" required onchange="toggleParent()">
          <option value="">— Select type —</option>
          <option value="category" {{ old('type') === 'category' ? 'selected' : '' }}>Category</option>
          <option value="brand" {{ old('type') === 'brand' ? 'selected' : '' }}>Brand</option>
        </select>
        @error('type')<div class="vs-err">{{ $message }}</div>@enderror
      </div>

      <div class="vs-form-group">
        <label class="vs-label">Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" class="vs-input {{ $errors->has('name') ? 'err' : '' }}"
               value="{{ old('name') }}" placeholder="e.g. Electronics, Nike, Furniture…" required maxlength="255">
        @error('name')<div class="vs-err">{{ $message }}</div>@enderror
      </div>

      {{-- Parent category selector — only shown when type = category --}}
      <div class="vs-form-group" id="parent-group" style="display:none">
        <label class="vs-label">
          Parent Category
          <span style="color:var(--mid);font-weight:400;text-transform:none">(leave empty for top-level)</span>
        </label>
        <select name="parent_category_id" id="parent-select" class="vs-input">
          <option value="">— No parent (top-level category) —</option>
          @php
            $topLevel = $categories->filter(fn($c) => $c->parent == 0 || $c->parent === null);
            $children = $categories->filter(fn($c) => $c->parent > 0)->keyBy('id');
          @endphp
          @foreach($topLevel as $cat)
            <option value="{{ $cat->id }}" {{ old('parent_category_id') == $cat->id ? 'selected' : '' }}>
              {{ $cat->name }}
            </option>
            @foreach($categories->where('parent', $cat->id) as $child)
              <option value="{{ $child->id }}" {{ old('parent_category_id') == $child->id ? 'selected' : '' }}>
                &nbsp;&nbsp;&nbsp;↳ {{ $child->name }}
              </option>
            @endforeach
          @endforeach
        </select>
        <div style="font-size:12px;color:var(--mid);margin-top:4px">
          Selecting a parent makes this a sub-category of the chosen one.
        </div>
        @error('parent_category_id')<div class="vs-err">{{ $message }}</div>@enderror
      </div>

      <div class="vs-form-group">
        <label class="vs-label">Description <span style="color:var(--mid);font-weight:400">(optional)</span></label>
        <textarea name="description" class="vs-input" rows="3" maxlength="1000"
                  placeholder="Briefly describe what this category or brand covers…">{{ old('description') }}</textarea>
        @error('description')<div class="vs-err">{{ $message }}</div>@enderror
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="vs-btn vs-btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Submit Request
        </button>
        <a href="{{ route('vendor.requests') }}" class="vs-btn vs-btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
function toggleParent() {
  const type = document.getElementById('req-type').value;
  const group = document.getElementById('parent-group');
  const sel = document.getElementById('parent-select');
  if (type === 'category') {
    group.style.display = 'block';
  } else {
    group.style.display = 'none';
    sel.value = '';
  }
}
// Run on load in case of old() repopulation
document.addEventListener('DOMContentLoaded', toggleParent);
</script>
@endsection
