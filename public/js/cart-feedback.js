/* Shared feedback for cards, featured products, and product purchase controls. */
(() => {
  'use strict';
  const pending = new WeakMap();

  function announce(message) {
    const status = document.getElementById('cart-request-status');
    if (status) status.textContent = message;
  }

  function begin(button, label) {
    if (!button) return true;
    if (pending.has(button)) return false;
    pending.set(button, {
      children: [...button.childNodes],
      disabled: button.disabled,
      busy: button.getAttribute('aria-busy'),
      label: button.getAttribute('aria-label'),
    });
    const content = document.createElement('span');
    content.className = 'cart-button-content';
    const spinner = document.createElement('span');
    spinner.className = 'cart-button-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    const text = document.createElement('span');
    text.textContent = label;
    content.append(spinner, text);
    button.replaceChildren(content);
    button.classList.add('cart-button-pending');
    button.dataset.adding = 'true';
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.setAttribute('aria-label', [pending.get(button).label, label].filter(Boolean).join(' — '));
    announce(label);
    return true;
  }

  function end(button) {
    const previous = button && pending.get(button);
    if (!previous) return;
    button.replaceChildren(...previous.children);
    button.disabled = previous.disabled;
    button.classList.remove('cart-button-pending');
    delete button.dataset.adding;
    for (const [name, value] of [['aria-busy', previous.busy], ['aria-label', previous.label]]) {
      if (value === null) button.removeAttribute(name);
      else button.setAttribute(name, value);
    }
    pending.delete(button);
  }

  async function request(url, payload, csrfToken) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 30000);
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload),
        signal: controller.signal,
      });
      if (response.status === 401 || response.status === 419) {
        const error = new Error('Session expired');
        error.status = response.status;
        throw error;
      }
      return await response.json();
    } finally {
      clearTimeout(timeout);
    }
  }

  window.CartFeedback = { begin, end, announce, request };
})();
