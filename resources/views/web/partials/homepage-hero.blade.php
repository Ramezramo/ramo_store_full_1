<ramo-home-hero dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  @if($hero->media_type === 'video')
    <video class="home-hero-media" muted loop playsinline preload="{{ $hero->poster_path ? 'none' : 'metadata' }}" src="{{ $hero->mediaUrl() }}" @if($hero->poster_path) poster="{{ $hero->posterUrl() }}" @endif aria-hidden="true"></video>
    <button type="button" class="home-hero-playback" hidden
            data-pause="{{ $isAr ? 'إيقاف الفيديو مؤقتًا' : 'Pause video' }}"
            data-play="{{ $isAr ? 'تشغيل الفيديو' : 'Play video' }}"></button>
  @else
    <img class="home-hero-media" src="{{ $hero->mediaUrl() }}" alt="" fetchpriority="high">
  @endif
  @if($hero->headline || $hero->subtext || ($hero->button_label && $hero->button_link))
    <div class="home-hero-copy">
      @if($hero->headline)<h1>{{ $hero->headline }}</h1>@endif
      @if($hero->subtext)<p>{{ $hero->subtext }}</p>@endif
      @if($hero->button_label && $hero->button_link)<a href="{{ $hero->button_link }}">{{ $hero->button_label }}</a>@endif
    </div>
  @endif
</ramo-home-hero>

@push('scripts')
<script defer src="{{ asset('js/homepage-hero.js') }}?v={{ filemtime(public_path('js/homepage-hero.js')) }}"></script>
@endpush
