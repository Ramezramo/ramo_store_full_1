(() => {
  const draftKey = 'ramo-header-search-draft';
  const forms = new WeakMap();
  let currentForm;

  function bind(form, input) {
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

          const items = products.map(product => {
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
    form.addEventListener('submit', closeSuggestions);
    form.addEventListener('focusout', event => {
      if (event.relatedTarget && !form.contains(event.relatedTarget)) closeSuggestions();
    });
    form.addEventListener('keydown', event => {
      if (event.key !== 'Escape' || suggestions.hidden) return;
      event.preventDefault();
      if (suggestions.contains(document.activeElement)) input.focus({ preventScroll: true });
      closeSuggestions();
    });

    return closeSuggestions;
  }

  function activate(scope) {
    const form = scope.querySelector('[data-storefront-search-form]');
    if (form !== currentForm) forms.get(currentForm)?.();
    currentForm = form;
    if (!form) return;

    const input = form.querySelector('input[name="q"]');
    if (!input.defaultValue) {
      try { input.value = sessionStorage.getItem(draftKey) ?? input.value; } catch {}
    }
    if (!forms.has(form)) forms.set(form, bind(form, input));
  }

  document.addEventListener('catalog:shown', event => activate(event.detail.element));
  document.addEventListener('click', event => {
    if (currentForm && !currentForm.contains(event.target)) forms.get(currentForm)?.();
  }, true);
  activate(document);
})();
