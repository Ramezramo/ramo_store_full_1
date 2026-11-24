(() => {
  const draftKey = 'ramo-header-search-draft';
  const forms = new WeakMap();
  const headerForm = document.querySelector('.header-search-form');
  let currentForm;

  function bind(form, input, limit) {
    const suggestions = form.querySelector('[data-suggestions-url]');
    let timer;
    let activeRequest;

    function closeSuggestions() {
      clearTimeout(timer);
      activeRequest?.abort();
      suggestions.hidden = true;
      suggestions.replaceChildren();
    }

    function suggest() {
      closeSuggestions();
      const query = input.value.trim();
      if (query.length < 2 || query.length > 120) return;

      timer = setTimeout(async () => {
        const request = new AbortController();
        activeRequest = request;
        const url = new URL(suggestions.dataset.suggestionsUrl, location.href);
        url.searchParams.set('q', query);

        try {
          const response = await fetch(url, { signal: request.signal, headers: { Accept: 'application/json' } });
          if (!response.ok) return;
          const products = await response.json();
          if (request.signal.aborted || input.value.trim() !== query || !products.length) return;

          const items = products.slice(0, limit ?? products.length).map(product => {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = product.url;
            link.dir = 'auto';
            link.textContent = product.name;
            item.append(link);
            return item;
          });

          suggestions.replaceChildren(...items);
          suggestions.hidden = false;
        } catch {
          // The form still works when suggestions are unavailable.
        }
      }, 220);
    }

    input.addEventListener('input', () => {
      try { sessionStorage.setItem(draftKey, input.value); } catch {}
      suggest();
    });
    input.addEventListener('focus', () => {
      if (suggestions.hidden) suggest();
    });
    form.addEventListener('submit', closeSuggestions);
    form.addEventListener('focusout', event => {
      if (event.relatedTarget && !form.contains(event.relatedTarget)) closeSuggestions();
    });
    form.addEventListener('keydown', event => {
      if (suggestions.hidden) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        if (suggestions.contains(document.activeElement)) input.focus({ preventScroll: true });
        closeSuggestions();
      } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        const links = [...suggestions.querySelectorAll('a')];
        const index = links.indexOf(document.activeElement);
        if (index < 0 && document.activeElement !== input) return;
        event.preventDefault();
        if (event.key === 'ArrowDown') links[Math.min(index + 1, links.length - 1)]?.focus();
        else if (index > 0) links[index - 1].focus();
        else if (index === 0) input.focus({ preventScroll: true });
        else links.at(-1)?.focus();
      }
    });

    return closeSuggestions;
  }

  function prepare(form, limit) {
    const input = form.querySelector('input[name="q"]');
    if (!input.defaultValue) {
      try { input.value = sessionStorage.getItem(draftKey) ?? input.value; } catch {}
    }
    if (!forms.has(form)) forms.set(form, bind(form, input, limit));
  }

  function activate(scope) {
    const form = scope.querySelector('[data-storefront-search-form]');
    if (form !== currentForm) forms.get(currentForm)?.();
    currentForm = form;
    if (form) prepare(form);
  }

  if (headerForm) prepare(headerForm, 5);
  document.addEventListener('catalog:shown', event => {
    forms.get(headerForm)?.();
    activate(event.detail.element);
  });
  document.addEventListener('click', event => {
    for (const form of [headerForm, currentForm]) {
      if (form && !form.contains(event.target)) forms.get(form)?.();
    }
  }, true);
  activate(document);
})();
