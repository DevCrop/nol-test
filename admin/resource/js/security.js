(() => {
  if (window.BlueAdminLoading) return;
  const loading = new Set();
  const renderLoading = () => {
    if (loading.size) { document.body.classList.add('is-loading'); document.body.setAttribute('aria-busy', 'true'); }
    else { document.body.classList.remove('is-loading'); document.body.removeAttribute('aria-busy'); }
  };
  const beginLoading = () => {
    const request = {}; loading.add(request); renderLoading();
    return () => { loading.delete(request); renderLoading(); };
  };
  window.BlueAdminLoading = Object.freeze({ begin: beginLoading });
  let navigation = null;
  // A microtask checkpoint can run between native event listeners. A task waits
  // until the entire submit dispatch (including preventDefault) has finished.
  document.addEventListener('submit', event => setTimeout(() => {
    // Native attachment responses keep this document alive; no navigation completion fires.
    if (event.target?.hasAttribute?.('data-native-download') || event.submitter?.hasAttribute?.('data-native-download')) return;
    const target = event.submitter?.formTarget || event.target?.target || '_self';
    if (!event.defaultPrevented && target === '_self' && !navigation) navigation = beginLoading();
  }, 0), true);
  window.addEventListener('pageshow', event => {
    if (event.persisted) { loading.clear(); navigation = null; renderLoading(); }
  });
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  document.querySelectorAll('form[method="post" i]').forEach((form) => {
    if (form.querySelector('input[name="_csrf"]')) return;
    const input = document.createElement('input'); input.type = 'hidden'; input.name = '_csrf'; input.value = token; form.appendChild(input);
  });
  if (window.jQuery) {
    window.jQuery(document).ajaxSend((_e, xhr, options) => {
      if (!options.crossDomain && (options.type || 'GET').toUpperCase() !== 'GET') xhr.setRequestHeader('X-CSRF-Token', token);
    });
    // Register cleanup before page success callbacks. A callback exception must
    // not strand the loader or rely on jQuery's global active counter recovering.
    window.jQuery.ajaxPrefilter((options, _original, xhr) => {
      if (options.global !== false) xhr.always(beginLoading());
    });
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
    const release = silent ? () => {} : beginLoading();
    try { return Promise.resolve(originalFetch.call(window, input, init)).finally(release); }
    catch (error) { release(); return Promise.reject(error); }
  };

  const timer = document.querySelector('[data-session-timer]');
  if (timer) {
    timer.dataset.activityUrl = (window.BLUE_ADMIN_BASE ?? '/admin') + '/lib/session/ping.php';
    timer.dataset.logoutUrl = (window.BLUE_ADMIN_BASE ?? '/admin') + '/index.php';
    import('./SessionIdleTimer.js?v=20260921').then(({SessionIdleTimer}) => new SessionIdleTimer(timer).init());
  }

  document.querySelectorAll('[data-pii-lock]').forEach((root) => {
    ['copy', 'cut', 'dragstart', 'contextmenu'].forEach((name) => root.addEventListener(name, (event) => {
      if (event.target.closest('input:not([readonly]):not([disabled]),textarea:not([readonly]):not([disabled]),select:not([disabled]),button,[contenteditable="true"]')) return; event.preventDefault();
    }));
  });
})();
