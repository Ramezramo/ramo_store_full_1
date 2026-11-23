(() => {
  const form = document.querySelector('[data-product-wizard]');
  if (!form || typeof productStepIssues !== 'function') return;

  const header = form.querySelector('[data-wizard-header]');
  const title = header.querySelector('[data-wizard-title]');
  const hint = header.querySelector('[data-wizard-hint]');
  const count = header.querySelector('[data-wizard-count]');
  const errors = header.querySelector('[data-wizard-errors]');
  const tabs = [...header.querySelectorAll('[data-wizard-jump]')];
  const steps = tabs.map(tab => tab.dataset.wizardJump);
  const sections = [...form.querySelectorAll('[data-product-step]')];
  const review = form.querySelector('.product-wizard-review');
  const thumbnail = review.querySelector('[data-review-thumbnail]');
  const cancel = form.querySelector('[data-wizard-cancel]');
  const back = form.querySelector('[data-wizard-back]');
  const next = form.querySelector('[data-wizard-next]');
  const save = form.querySelector('#product-submit-btn');
  const saveLabel = save.querySelector('[data-wizard-save-label]');
  const status = form.elements.namedItem('status');
  const last = steps.length - 1;
  let current = Math.max(0, steps.indexOf(form.dataset.initialStep));
  let reached = current;
  let thumbnailUrl = null;
  let messageId = 0;
  let returnToReview = false;

  function pointToField(field, reason) {
    field.setAttribute('aria-invalid', 'true');
    let message = field.parentElement.querySelector('.vs-err');
    const temporary = !message;
    if (temporary) {
      message = document.createElement('p');
      message.className = 'vs-err';
      message.textContent = reason;
      field.after(message);
    }
    message.id ||= 'product-wizard-field-error-' + (++messageId);
    field.setAttribute('aria-describedby', message.id);
    field.scrollIntoView({ block: 'center', behavior: 'instant' });
    field.focus({ preventScroll: true });
    field.addEventListener('input', () => {
      field.removeAttribute('aria-invalid');
      field.removeAttribute('aria-describedby');
      if (temporary) message.remove();
    }, { once: true });
  }

  function updateReview() {
    const value = name => form.elements.namedItem(name)?.value.trim() ?? '';
    const set = (key, text) => { review.querySelector('[data-review-' + key + ']').textContent = text; };
    const uploads = [...form.querySelectorAll('#section-images input[type="file"]')]
      .reduce((total, input) => total + input.files.length, 0);
    const linkedCover = value('thumbnail_url');
    const photos = uploads + Number(Boolean(linkedCover))
      + [value('other_image_urls'), value('natural_image_urls')].flatMap(links => links.split(/\r?\n/).filter(link => link.trim())).length;
    const categories = [...form.querySelectorAll('input[name="categories[]"]:checked')]
      .map(input => input.closest('label').querySelector('[data-category-name]').textContent.trim());
    const variants = form.querySelectorAll('#section-pricing .size-tag').length;
    const stock = value('stock_quantity');
    const price = value('regular_price');
    set('name', value('name') || 'Untitled product');
    set('description', value('short_description'));
    set('photos', photos ? photos + (photos === 1 ? ' photo' : ' photos') : 'No photos added');
    set('categories', categories.length ? categories.join(', ') : 'No categories selected');
    set('price', form.querySelector('#has-variations-toggle').checked
      ? variants + (variants === 1 ? ' variant' : ' variants')
      : new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP' }).format(Number(price)) + ' · ' + stock + ' in stock');

    const cover = form.elements.namedItem('thumbnail')?.files[0];
    if (cover && cover.type.startsWith('image/')) {
      thumbnailUrl = URL.createObjectURL(cover);
      thumbnail.src = thumbnailUrl;
      thumbnail.hidden = false;
    } else if (linkedCover.startsWith('https://')) {
      thumbnail.src = linkedCover;
      thumbnail.hidden = false;
    }
  }

  function show(index, moveFocus = true) {
    current = index;
    if (current === last) returnToReview = false;
    reached = Math.max(reached, index);
    const tab = tabs[index];
    sections.forEach(section => { section.hidden = section.dataset.productStep !== steps[index]; });
    tabs.forEach((button, i) => {
      button.disabled = i > reached;
      button.classList.toggle('is-complete', i < reached && i !== index);
      if (i === index) button.setAttribute('aria-current', 'step');
      else button.removeAttribute('aria-current');
    });

    count.textContent = 'Step ' + (index + 1) + ' of ' + steps.length;
    title.textContent = tab.dataset.stepTitle;
    hint.textContent = tab.dataset.stepHint;
    errors.hidden = true;
    cancel.hidden = index !== 0;
    back.hidden = index === 0;
    next.hidden = index === last;
    next.textContent = returnToReview ? 'Return to review' : 'Next';
    save.hidden = index !== last;

    if (thumbnailUrl) {
      URL.revokeObjectURL(thumbnailUrl);
      thumbnailUrl = null;
      thumbnail.removeAttribute('src');
      thumbnail.hidden = true;
    }
    if (index === last) updateReview();
    if (moveFocus) {
      header.scrollIntoView({ block: 'start', behavior: 'instant' });
      title.focus({ preventScroll: true });
    }
  }

  function validateStep(index) {
    const step = steps[index];
    const issues = productStepIssues(step);
    const invalid = [...form.elements].find(field => field.willValidate && !field.validity.valid &&
      field.closest('[data-product-step]')?.dataset.productStep === step);

    if (!issues.length && !invalid) {
      errors.hidden = true;
      return true;
    }
    if (current !== index) show(index);
    errors.replaceChildren(...issues.map(message => {
      const item = document.createElement('li');
      item.textContent = message;
      return item;
    }));
    errors.hidden = !issues.length;
    if (invalid) pointToField(invalid, invalid.validationMessage);
    else header.scrollIntoView({ block: 'start', behavior: 'instant' });
    return false;
  }

  function advance() {
    if (validateStep(current)) show(returnToReview ? last : current + 1);
  }

  function editFromReview(index) {
    returnToReview = true;
    show(index);
  }

  function updateSaveLabel() {
    saveLabel.textContent = status.value === 'draft' ? 'Save draft' : 'Submit for approval';
  }

  review.querySelector('[data-review-status]').append(form.querySelector('[data-wizard-status-field]'));
  const pricing = form.querySelector('#section-pricing');
  pricing.append(pricing.querySelector('[data-pricing-settings]'));
  status.addEventListener('change', updateSaveLabel);
  updateSaveLabel();
  form.noValidate = true;
  form.classList.add('is-guided');
  header.hidden = false;
  show(current, false);
  if (form.dataset.wizardErrorField) {
    // The original editor rebuilds variation rows when the DOM is ready.
    document.addEventListener('DOMContentLoaded', () => {
      const [name, ...path] = form.dataset.wizardErrorField.split('.');
      const field = form.elements.namedItem(name + path.map(part => '[' + part + ']').join(''));
      const summary = document.getElementById('error-summary');
      if (!(field instanceof HTMLElement) || !field.getClientRects().length) {
        summary?.focus();
        return;
      }
      pointToField(field, form.dataset.wizardErrorMessage);
    }, { once: true });
  }
  tabs.forEach((tab, index) => tab.addEventListener('click', () => {
    if (index === current) return;
    if (current === last) editFromReview(index);
    else if (index < current || validateStep(current)) show(index);
  }));
  review.querySelectorAll('[data-review-edit]').forEach(button => {
    button.addEventListener('click', () => editFromReview(steps.indexOf(button.dataset.reviewEdit)));
  });
  back.addEventListener('click', () => show(current - 1));
  next.addEventListener('click', advance);

  form.addEventListener('submit', event => {
    const onReview = current === last;
    if (onReview && steps.every((_, index) => validateStep(index))) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (!onReview) advance();
  }, true);
})();
