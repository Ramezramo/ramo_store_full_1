@php
  $headline = $isAr ? $hero->headline_ar : $hero->headline;
  $subtext = $isAr ? $hero->subtext_ar : $hero->subtext;
  $buttonLabel = $isAr ? $hero->button_label_ar : $hero->button_label;
  $showButton = filled($buttonLabel) && filled($hero->button_link);
@endphp
<ramo-home-hero dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  @if($hero->media_type === 'video')
    <video class="home-hero-media" muted loop playsinline preload="{{ $hero->poster_path ? 'none' : 'metadata' }}" src="{{ $hero->mediaUrl() }}" @if($hero->poster_path) poster="{{ $hero->posterUrl() }}" @endif aria-hidden="true"></video>
    <button type="button" class="home-hero-playback" hidden
            data-pause="{{ $isAr ? 'إيقاف الفيديو مؤقتًا' : 'Pause video' }}"
            data-play="{{ $isAr ? 'تشغيل الفيديو' : 'Play video' }}"></button>
  @else
    <div class="home-hero-image-track">
      <img class="home-hero-media" src="{{ $hero->mediaUrl() }}" alt="" fetchpriority="high">
    </div>
  @endif
  @if(filled($headline) || filled($subtext) || $showButton)
    <div class="home-hero-copy">
      @if(filled($headline))<h1>{{ $headline }}</h1>@endif
      @if(filled($subtext))<p>{{ $subtext }}</p>@endif
      @if($showButton)<a href="{{ $hero->button_link }}">{{ $buttonLabel }}</a>@endif
    </div>
  @endif
</ramo-home-hero>

@push('scripts')
<script defer src="{{ asset('js/homepage-hero.js') }}?v={{ filemtime(public_path('js/homepage-hero.js')) }}"></script>
@endpush
