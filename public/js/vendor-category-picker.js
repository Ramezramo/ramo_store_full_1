(() => {
  const normalize = value => value.normalize('NFKD').replace(/\p{M}/gu, '').toLocaleLowerCase().trim();

  for (const picker of document.querySelectorAll('[data-category-picker]')) {
    const controls = picker.querySelector('[data-category-controls]');
    if (!controls) continue;

    const search = picker.querySelector('[data-category-query]');
    const browse = picker.querySelector('[data-category-browse]');
    const count = picker.querySelector('[data-category-count]');
    const empty = picker.querySelector('[data-category-empty]');
    const options = [...picker.querySelectorAll('[data-category-option]')].map(option => ({
      option,
      checkbox: option.querySelector('input'),
      name: normalize(option.textContent),
    }));
    let expanded = false;

    function render() {
      const query = normalize(search.value);
      const terms = query ? query.split(/\s+/) : [];
      let selected = 0;
      let matches = 0;

      for (const { option, checkbox, name } of options) {
        if (checkbox.checked) selected++;
        const visible = query ? terms.every(term => name.includes(term)) : expanded || checkbox.checked;
        option.hidden = !visible;
        if (visible) matches++;
      }

      const showingAll = expanded && !query;
      browse.textContent = showingAll ? 'Show selected' : 'View all';
      browse.setAttribute('aria-expanded', String(showingAll));
      count.textContent = selected ? selected + ' selected' : 'No categories selected.';
      empty.hidden = !query || matches > 0;
    }

    search.addEventListener('input', render);
    search.addEventListener('keydown', event => {
      if (event.key === 'Enter' && !event.isComposing) event.preventDefault();
    });
    browse.addEventListener('click', () => {
      expanded = Boolean(normalize(search.value)) || !expanded;
      search.value = '';
      render();
    });
    picker.addEventListener('change', event => {
      if (event.target.matches('input[type="checkbox"]')) render();
    });
    window.addEventListener('pageshow', render);

    render();
    picker.classList.add('is-enhanced');
    controls.hidden = false;
    count.hidden = false;
  }
})();
