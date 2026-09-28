(() => {
  'use strict';
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!token) return;
  const local = url => new URL(url || location.href, location.href).origin === location.origin;
  const unsafe = method => !/^(GET|HEAD|OPTIONS)$/i.test(method || 'GET');
  const addField = form => {
    if (!local(form.action) || !unsafe(form.method)) return;
    let input = form.querySelector('input[name="_csrf"]');
    if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = '_csrf'; form.append(input); }
    input.value = token;
  };
  const nativeSubmit = HTMLFormElement.prototype.submit;
  HTMLFormElement.prototype.submit = function () { addField(this); return nativeSubmit.call(this); };
  document.addEventListener('submit', event => addField(event.target), true);
  document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('form').forEach(addField));
  const nativeFetch = window.fetch;
  window.fetch = function (input, options = {}) {
    const request = input instanceof Request ? input : null;
    if (local(request ? request.url : input) && unsafe(options.method || request?.method)) {
      options = {...options, headers: new Headers(options.headers || request?.headers)};
      options.headers.set('X-CSRF-Token', token);
    }
    return nativeFetch.call(this, input, options);
  };
  // Covers both jQuery and the legacy raw XMLHttpRequest callers.
  const nativeOpen = XMLHttpRequest.prototype.open, nativeSend = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.open = function (method, url, ...rest) {
    this._csrfLocalWrite = local(url) && unsafe(method);
    return nativeOpen.call(this, method, url, ...rest);
  };
  XMLHttpRequest.prototype.send = function (body) {
    if (this._csrfLocalWrite) this.setRequestHeader('X-CSRF-Token', token);
    return nativeSend.call(this, body);
  };
  window.adminPost = url => {
    if (!local(url)) return;
    const form = document.createElement('form'); form.method = 'POST'; form.action = url;
    document.body.append(form); form.submit();
  };
  document.addEventListener('click', event => {
    const link = event.target.closest('a[data-admin-post]');
    if (link) { event.preventDefault(); window.adminPost(link.href); }
  });
})();
