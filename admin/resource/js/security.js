(() => {
  let loading = 0;
  const beginLoading = () => { loading += 1; document.body.classList.add('is-loading'); document.body.setAttribute('aria-busy', 'true'); };
  const endLoading = () => { loading = Math.max(0, loading - 1); if (!loading) { document.body.classList.remove('is-loading'); document.body.removeAttribute('aria-busy'); } };
  document.addEventListener('submit', beginLoading, true);
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  document.querySelectorAll('form[method="post" i]').forEach((form) => {
    if (form.querySelector('input[name="_csrf"]')) return;
    const input = document.createElement('input'); input.type = 'hidden'; input.name = '_csrf'; input.value = token; form.appendChild(input);
  });
  if (window.jQuery) {
    window.jQuery(document).ajaxSend((_e, xhr, options) => {
      if (!options.crossDomain && (options.type || 'GET').toUpperCase() !== 'GET') xhr.setRequestHeader('X-CSRF-Token', token);
    });
    window.jQuery(document).ajaxStart(beginLoading).ajaxStop(endLoading);
  }
  const originalFetch = window.fetch;
  window.fetch = (input, init = {}) => {
    const method = String(init.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
    const url = new URL(input instanceof Request ? input.url : String(input), location.href);
    const headers = new Headers(init.headers || (input instanceof Request ? input.headers : undefined));
    if (url.origin === location.origin && method !== 'GET') {
      headers.set('X-CSRF-Token', token); headers.set('X-Requested-With', 'XMLHttpRequest');
    }
    init = {...init, headers};
    const silent = headers.get('X-Loading-Silent') === '1';
    if (!silent) beginLoading();
    return originalFetch(input, init).finally(() => { if (!silent) endLoading(); });
  };

  const timer = document.querySelector('[data-session-timer]');
  if (timer) {
    timer.dataset.activityUrl = '/admin/lib/session/ping.php';
    timer.dataset.logoutUrl = '/admin/index.php';
    import('./SessionIdleTimer.js?v=20260921').then(({SessionIdleTimer}) => new SessionIdleTimer(timer).init());
  }

  document.querySelectorAll('[data-pii-lock]').forEach((root) => {
    ['copy', 'cut', 'dragstart', 'contextmenu'].forEach((name) => root.addEventListener(name, (event) => {
      if (event.target.closest('input,textarea,select,button')) return; event.preventDefault();
    }));
  });
})();
