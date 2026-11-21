(() => {
  if (customElements.get('ramo-home-hero')) return;

  class HomepageHero extends HTMLElement {
    connectedCallback() {
      const view = this.closest('.catalog-view');
      const scroller = view && document.documentElement.classList.contains('catalog-keep-alive') ? view : window;
      this.events = new AbortController();
      const options = { signal: this.events.signal };
      const onScroll = () => {
        const offset = scroller === window ? window.scrollY : scroller.scrollTop;
        this.toggleAttribute('data-scrolled', offset >= 40);
      };
      scroller.addEventListener('scroll', onScroll, { ...options, passive: true });
      onScroll();

      const video = this.querySelector('video');
      if (!video) return;
      const button = this.querySelector('.home-hero-playback');
      const motion = matchMedia('(prefers-reduced-motion: reduce)');
      let userPaused = false;
      const label = () => { button.textContent = video.paused ? button.dataset.play : button.dataset.pause; };
      const update = () => {
        const active = !document.hidden && (!view || view.dataset.catalogActive === '1');
        const play = active && !motion.matches && !userPaused;
        video.autoplay = play;
        button.hidden = motion.matches;
        if (play) video.play().catch(label);
        else video.pause();
        label();
      };
      const motionChanged = () => {
        if (motion.matches && video.poster) {
          video.autoplay = false;
          video.load(); // Restore the still poster if the preference changes during playback.
        }
        update();
      };
      button.addEventListener('click', () => { userPaused = !video.paused; update(); }, options);
      video.addEventListener('play', label, options);
      video.addEventListener('pause', label, options);
      motion.addEventListener('change', motionChanged, options);
      document.addEventListener('visibilitychange', update, options);
      window.addEventListener('pageshow', update, options);
      if (view) {
        this.viewObserver = new MutationObserver(update);
        this.viewObserver.observe(view, { attributes: true, attributeFilter: ['data-catalog-active'] });
      }
      update();
    }

    disconnectedCallback() {
      this.events.abort();
      this.viewObserver?.disconnect();
      this.querySelector('video')?.pause();
    }
  }

  customElements.define('ramo-home-hero', HomepageHero);
})();
