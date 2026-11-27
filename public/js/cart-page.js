(function () {
  'use strict';
  if (window.ramoCartPageBound) return;
  window.ramoCartPageBound = true;
  let pending = false;
  let toastTimer;
  const isArabic = () => document.querySelector('.cart-screen')?.dir === 'rtl';
  function showMessage(message, error) {
    const toast = document.getElementById('cart-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.setAttribute('role', error ? 'alert' : 'status');
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 5000);
  }
  document.addEventListener('submit', async function (event) {
    const form = event.target.closest('form[data-cart-request]');
    if (!form || !window.fetch || !window.FormData) return;
    event.preventDefault();
    if (pending) return;
    pending = true;
    const body = new FormData(form);
    const buttons = [...document.querySelectorAll('.cart-screen button')].map(button => [button, button.disabled]);
    buttons.forEach(([button]) => { button.disabled = true; });
    const region = form.closest('.cart-item-card') || form;
    region.setAttribute('aria-busy', 'true');
    form.setAttribute('aria-busy', 'true');
    const checkoutLinks = [...document.querySelectorAll('[data-cart-checkout]')];
    checkoutLinks.forEach(link => link.setAttribute('aria-busy', 'true'));
    const couponOpen = document.querySelector('.cart-promo')?.open;
    const action = form.action;
    try {
      const response = await fetch(action, {
        method: 'POST', body, credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || (isArabic() ? 'مش قادرين نحدّث السلة. جرّب تاني.' : 'Unable to update your cart. Please try again.'));
      }
      if (typeof data.html !== 'string') throw new Error(isArabic() ? 'حدّث الصفحة عشان تتأكد من السلة قبل ما تعيد المحاولة.' : 'Refresh to check your cart before trying again.');
      const template = document.createElement('template');
      template.innerHTML = data.html;
      const next = template.content.querySelector('.cart-screen');
      if (!next) throw new Error(isArabic() ? 'مش قادرين نعرض السلة.' : 'Unable to display the cart.');
      document.querySelector('.cart-screen').replaceWith(next);
      const promo = next.querySelector('.cart-promo');
      if (promo && couponOpen) promo.open = true;
      const focusTarget = [...next.querySelectorAll('form')].find(item => item.action === action)?.querySelector('button:not(:disabled)');
      focusTarget?.focus({ preventScroll: true });
      window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.count } }));
      if (window.parent !== window) {
        window.parent.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.count } }));
      }
      if (data.coupon_message || data.message) showMessage(data.coupon_message || data.message, Boolean(data.coupon_invalid));
    } catch (error) {
      showMessage(error instanceof SyntaxError
        ? (isArabic() ? 'انتهت الجلسة أو حصل خطأ. حدّث الصفحة قبل ما تعيد المحاولة.' : 'Your session expired or the server returned an error. Refresh before retrying.')
        : error.message, true);
    } finally {
      buttons.forEach(([button, disabled]) => { if (button.isConnected) button.disabled = disabled; });
      region.removeAttribute('aria-busy');
      form.removeAttribute('aria-busy');
      checkoutLinks.forEach(link => link.removeAttribute('aria-busy'));
      pending = false;
    }
  });
  document.addEventListener('click', event => {
    if (pending && event.target.closest('[data-cart-checkout]')) event.preventDefault();
  });
})();
