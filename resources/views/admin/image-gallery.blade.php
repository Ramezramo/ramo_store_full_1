@extends('admin.layout')

@section('title', 'Image Gallery')
@section('page-title', 'Image Gallery')

@push('styles')
<style>
.gallery-upload{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:center;margin-bottom:24px}
.gallery-dropzone{position:relative;border:1.5px dashed rgba(232,93,38,.55);background:linear-gradient(135deg,rgba(232,93,38,.11),rgba(249,115,22,.035));border-radius:12px;padding:26px;min-height:136px;display:flex;align-items:center;justify-content:center;text-align:center;transition:.18s;cursor:pointer}
.gallery-dropzone:hover,.gallery-dropzone.dragover{border-color:var(--accent);background:rgba(232,93,38,.16);transform:translateY(-1px)}
.gallery-dropzone input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
.gallery-drop-icon{width:42px;height:42px;margin:0 auto 9px;border-radius:11px;background:rgba(232,93,38,.16);color:var(--accent);display:flex;align-items:center;justify-content:center}
.gallery-drop-icon svg{width:22px;height:22px}
.gallery-drop-title{font-size:14px;font-weight:700}
.gallery-drop-help{font-size:12px;color:var(--muted);margin-top:5px;line-height:1.45}
.gallery-upload-actions{display:flex;flex-direction:column;align-items:flex-start;gap:9px;min-width:176px}
.gallery-upload-count{font-size:12px;color:var(--muted);line-height:1.45;max-width:190px}
.gallery-upload-count.has-files{color:var(--text)}
.gallery-help{font-size:12px;color:var(--muted);margin:0 0 20px;line-height:1.5}
.gallery-help code{background:rgba(255,255,255,.07);padding:2px 5px;border-radius:4px;color:var(--text)}
.gallery-toolbar{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.gallery-total{font-size:13px;color:var(--muted)}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px}
.gallery-card{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;min-width:0;transition:.18s}
.gallery-card:hover{border-color:rgba(232,93,38,.55);transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.14)}
.gallery-thumb{display:block;position:relative;aspect-ratio:4 / 3;background:#111827;overflow:hidden}
.gallery-thumb img{display:block;width:100%;height:100%;object-fit:cover;transition:.2s}
.gallery-card:hover .gallery-thumb img{transform:scale(1.03)}
.gallery-dimensions{position:absolute;right:8px;bottom:8px;background:rgba(15,17,23,.78);color:#fff;padding:3px 6px;border-radius:4px;font-size:10px;font-weight:700;backdrop-filter:blur(4px)}
.gallery-card-body{padding:12px}
.gallery-name{font-size:12px;font-weight:650;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:8px}
.gallery-url-row{display:flex;gap:6px;align-items:center}
.gallery-url{min-width:0;flex:1;border:1px solid var(--border);background:rgba(255,255,255,.035);color:var(--muted);border-radius:6px;padding:6px 7px;font-size:10.5px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gallery-card-actions{display:flex;gap:7px;margin-top:10px}
.gallery-card-actions .btn{flex:1;justify-content:center}
.gallery-empty{grid-column:1 / -1;border:1px dashed var(--border);border-radius:10px;padding:42px 20px;text-align:center;color:var(--muted)}
.gallery-empty strong{display:block;color:var(--text);font-size:14px;margin-bottom:6px}
@media(max-width:700px){.gallery-upload{grid-template-columns:1fr}.gallery-upload-actions{flex-direction:row;align-items:center;justify-content:space-between;width:100%}.gallery-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}.gallery-toolbar form{width:100%}}
</style>
@endpush

@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="card" style="margin-bottom:20px">
  <div class="card-title">Upload Images</div>
  <form id="gallery-upload-form" method="POST" action="{{ route('admin.image-gallery.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="gallery-upload">
      <label class="gallery-dropzone" id="gallery-dropzone">
        <input id="gallery-image-input" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" multiple required>
        <div>
          <div class="gallery-drop-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
          <div class="gallery-drop-title">Choose images or drag them here</div>
          <div class="gallery-drop-help">Upload one image or many at once. JPG, PNG, WEBP, GIF, or AVIF; maximum 10 MB per image.</div>
        </div>
      </label>
      <div class="gallery-upload-actions">
        <div id="gallery-upload-count" class="gallery-upload-count">No images selected. Choose images first, then add them to the gallery.</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button id="gallery-choose-button" class="btn btn-ghost" type="button">Choose Images</button>
          <button id="gallery-upload-button" class="btn btn-primary" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M12 5v14M5 12h14"/></svg>
            Add to Gallery
          </button>
        </div>
      </div>
    </div>
  </form>
  <p class="gallery-help">Every uploaded image is kept in this gallery. Select <strong>Copy URL</strong> below any image, then paste that URL into a banner, Flexible Banner Grid card, or any other image field.</p>
</div>

<div class="gallery-toolbar">
  <div class="gallery-total">{{ number_format($images->total()) }} image{{ $images->total() === 1 ? '' : 's' }} in the gallery</div>
  <form method="GET" action="{{ route('admin.image-gallery') }}" style="display:flex;gap:8px;align-items:center">
    <input type="search" name="search" value="{{ $search }}" placeholder="Search image file names" style="width:230px;max-width:100%">
    <button class="btn btn-ghost" type="submit">Search</button>
    @if($search !== '')<a class="btn btn-ghost" href="{{ route('admin.image-gallery') }}">Clear</a>@endif
  </form>
</div>

<div class="gallery-grid">
  @forelse($images as $image)
    <article class="gallery-card">
      <a class="gallery-thumb" href="{{ $image->url }}" target="_blank" rel="noopener" title="Open image in a new tab">
        <img src="{{ $image->url }}" alt="{{ $image->original_name }}" loading="lazy">
        @if($image->width && $image->height)<span class="gallery-dimensions">{{ $image->width }} × {{ $image->height }}</span>@endif
      </a>
      <div class="gallery-card-body">
        <div class="gallery-name" title="{{ $image->original_name }}">{{ $image->original_name }}</div>
        <div class="gallery-url-row">
          <div class="gallery-url" id="gallery-url-{{ $image->id }}" title="{{ $image->url }}">{{ $image->url }}</div>
          <button class="btn btn-ghost btn-sm" type="button" onclick="copyGalleryUrl({{ $image->id }}, this)">Copy URL</button>
        </div>
        <div class="gallery-card-actions">
          <a href="{{ $image->url }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Open</a>
          <form method="POST" action="{{ route('admin.image-gallery.destroy', $image) }}" style="flex:1" onsubmit="return confirm('Remove this image from the gallery? Existing banner links to this image will no longer work.')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm" type="submit" style="width:100%;justify-content:center">Delete</button>
          </form>
        </div>
      </div>
    </article>
  @empty
    <div class="gallery-empty">
      <strong>{{ $search !== '' ? 'No images match your search.' : 'Your Image Gallery is empty.' }}</strong>
      {{ $search !== '' ? 'Try a different file name or clear the search.' : 'Upload your first banner, product, or campaign image above.' }}
    </div>
  @endforelse
</div>

@if($images->hasPages())
  <div class="pagination">{{ $images->links('admin.pagination') }}</div>
@endif
@endsection

@push('scripts')
<script>
(() => {
  const input = document.getElementById('gallery-image-input');
  const dropzone = document.getElementById('gallery-dropzone');
  const count = document.getElementById('gallery-upload-count');
  const button = document.getElementById('gallery-upload-button');
  const chooseButton = document.getElementById('gallery-choose-button');
  const form = document.getElementById('gallery-upload-form');

  function updateSelection(files) {
    const total = files ? files.length : 0;
    count.classList.toggle('has-files', total > 0);
    if (!total) {
      count.textContent = 'No images selected. Choose images first, then add them to the gallery.';
    } else if (total > 30) {
      count.textContent = `${total} images selected. Select no more than 30 images at once.`;
    } else {
      count.textContent = `${total} image${total === 1 ? '' : 's'} selected and ready to add to the gallery.`;
      button.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M12 5v14M5 12h14"/></svg> Add ' + total + ' to Gallery';
    }
  }

  chooseButton.addEventListener('click', () => input.click());
  input.addEventListener('change', () => updateSelection(input.files));
  ['dragenter', 'dragover'].forEach(eventName => dropzone.addEventListener(eventName, event => {
    event.preventDefault();
    dropzone.classList.add('dragover');
  }));
  ['dragleave', 'drop'].forEach(eventName => dropzone.addEventListener(eventName, event => {
    event.preventDefault();
    dropzone.classList.remove('dragover');
  }));
  dropzone.addEventListener('drop', event => {
    const files = event.dataTransfer.files;
    if (!files.length) return;
    try {
      const transfer = new DataTransfer();
      Array.from(files).forEach(file => transfer.items.add(file));
      input.files = transfer.files;
      updateSelection(input.files);
    } catch (_) {
      count.textContent = 'Drag-and-drop could not select these files. Please use Choose Images instead.';
    }
  });
  form.addEventListener('submit', event => {
    if (!input.files || input.files.length === 0) {
      event.preventDefault();
      count.textContent = 'Choose at least one image before adding it to the gallery.';
      input.click();
      return;
    }
    button.disabled = true;
    button.textContent = 'Uploading…';
  });
})();

function copyGalleryUrl(id, button) {
  const value = document.getElementById(`gallery-url-${id}`).textContent.trim();
  const copied = () => {
    const previous = button.textContent;
    button.textContent = 'Copied';
    setTimeout(() => { button.textContent = previous; }, 1500);
  };

  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(value).then(copied).catch(() => fallbackCopy(value, copied));
  } else {
    fallbackCopy(value, copied);
  }
}

function fallbackCopy(value, done) {
  const area = document.createElement('textarea');
  area.value = value;
  area.style.position = 'fixed';
  area.style.opacity = '0';
  document.body.appendChild(area);
  area.select();
  document.execCommand('copy');
  area.remove();
  done();
}
</script>
@endpush
