@extends('admin.layout')
@section('title', 'Homepage Hero')
@section('page-title', 'Homepage Hero')

@push('styles')
<style>
.hero-settings{max-width:760px;display:grid;gap:20px}
.hero-settings fieldset{min-width:0;border:1px solid var(--border);border-radius:10px;padding:18px;display:grid;gap:16px;background:var(--card)}
.hero-settings legend{padding:0 6px;font-weight:650}
.hero-settings small{color:var(--muted)}
.hero-settings img,.hero-settings video{width:min(100%,320px);height:auto;border-radius:6px}
.hero-settings input[type=file]{max-width:100%}
.hero-settings [hidden]{display:none}
.hero-settings textarea{resize:vertical}
.hero-settings .btn{justify-self:start}
</style>
@endpush

@section('content')
@if($errors->any())
  <div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form class="hero-settings" method="post" action="{{ route('admin.homepage-hero.update') }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')
  <fieldset>
    <legend>Banner media</legend>
    <label><input type="hidden" name="enabled" value="0"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $hero->enabled))> Show hero on the homepage</label>
    <div class="form-group">
      <label for="hero-type">Media type</label>
      <select id="hero-type" name="media_type">
        <option value="image" @selected(old('media_type', $hero->media_type) === 'image')>Image</option>
        <option value="video" @selected(old('media_type', $hero->media_type) === 'video')>Video</option>
      </select>
    </div>
    @if($hero->media_path)
      <div class="form-group">
        <small>Saved {{ $hero->media_type }}</small>
        @if($hero->media_type === 'video')
          <video controls playsinline preload="metadata" src="{{ $hero->mediaUrl() }}" @if($hero->poster_path) poster="{{ $hero->posterUrl() }}" @endif></video>
        @else
          <img src="{{ $hero->mediaUrl() }}" alt="Saved homepage banner">
        @endif
      </div>
    @endif
    <div class="form-group">
      <label for="hero-file">{{ $hero->media_path ? 'Replace media' : 'Upload media' }}</label>
      <input type="file" name="media" id="hero-file" aria-describedby="hero-file-help">
      <small id="hero-file-help">JPG, PNG or WebP up to 5 MB. MP4 or WebM up to 20 MB.</small>
    </div>
    <div class="form-group" id="hero-poster-fields">
      <label for="hero-poster">Video poster (optional)</label>
      <input type="file" name="poster" id="hero-poster" accept="image/jpeg,image/png,image/webp">
      <small>Still image for reduced motion. JPG, PNG or WebP up to 5 MB.</small>
      @if($hero->poster_path)
        <img src="{{ $hero->posterUrl() }}" alt="Saved video poster">
        <label><input type="checkbox" name="remove_poster" value="1" @checked(old('remove_poster'))> Remove saved poster</label>
      @endif
    </div>
  </fieldset>
  <fieldset>
    <legend>Banner content (optional)</legend>
    <div class="form-group"><label for="hero-headline">Headline</label><input type="text" id="hero-headline" name="headline" maxlength="160" value="{{ old('headline', $hero->headline) }}"></div>
    <div class="form-group"><label for="hero-subtext">Subtext</label><textarea id="hero-subtext" name="subtext" maxlength="600" rows="3">{{ old('subtext', $hero->subtext) }}</textarea></div>
    <div class="form-group"><label for="hero-button">Button label</label><input type="text" id="hero-button" name="button_label" maxlength="60" value="{{ old('button_label', $hero->button_label) }}"></div>
    <div class="form-group"><label for="hero-link">Button link</label><input type="text" id="hero-link" name="button_link" maxlength="2048" placeholder="/shop" value="{{ old('button_link', $hero->button_link) }}"><small>Enter both a label and a link to show the button.</small></div>
  </fieldset>
  <button class="btn btn-primary" type="submit">Save hero</button>
</form>
@endsection

@push('scripts')
<script>
(() => {
  const type = document.getElementById('hero-type');
  const media = document.getElementById('hero-file');
  const poster = document.getElementById('hero-poster-fields');
  function selectType() {
    const video = type.value === 'video';
    media.accept = video ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp';
    poster.hidden = !video;
    poster.querySelectorAll('input').forEach(input => { input.disabled = !video; });
  }
  type.addEventListener('change', () => { media.value = ''; selectType(); });
  selectType();
})();
</script>
@endpush
