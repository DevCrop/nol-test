(() => {
  const form = document.querySelector('#required-password-form');
  if (!form) return;
  const button = form.querySelector('[type="submit"]');
  const error = document.querySelector('#required-password-error');
  const label = button.textContent;
  const showError = message => {
    error.textContent = message;
    error.hidden = !message;
    error.classList.toggle('show', !!message);
  };
  form.querySelectorAll('[data-password-toggle]').forEach(toggle => {
    const input = document.getElementById(toggle.dataset.passwordToggle);
    if (!input) return;
    const name = input.placeholder;
    toggle.addEventListener('click', () => {
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(visible));
      toggle.setAttribute('aria-label', name + (visible ? ' 숨기기' : ' 보기'));
      toggle.querySelector('.no-pwd-text').textContent = visible ? '숨기기' : '보기';
      toggle.querySelector('i').classList.toggle('bx-show', !visible);
      toggle.querySelector('i').classList.toggle('bx-hide', visible);
    });
  });
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (button.disabled) return;
    showError('');
    const data = new FormData(form);
    if (data.get('new_password') !== data.get('new_password_confirm')) { showError('새 비밀번호와 확인 값이 일치하지 않습니다.'); return; }
    button.disabled = true;
    button.textContent = '변경 중…';
    form.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
      if (response.status === 401) { location.replace(window.NO_ADMIN_BASE || '/'); return; }
      if (!(response.headers.get('Content-Type') || '').toLowerCase().includes('application/json')) {
        throw new Error('인증 상태를 확인할 수 없습니다. 다시 로그인해 주세요.');
      }
      let result;
      try { result = await response.json(); } catch { throw new Error('응답을 처리하지 못했습니다. 새 비밀번호로 로그인되는지 먼저 확인해 주세요.'); }
      if (!result || typeof result !== 'object') throw new Error('응답을 처리하지 못했습니다. 새 비밀번호로 로그인되는지 먼저 확인해 주세요.');
      if (!response.ok || result.result !== 'success') throw new Error(result.msg || result.message || '변경하지 못했습니다. 다시 시도하세요.');
      form.reset(); alert(result.msg); location.replace(result.redirect || window.NO_ADMIN_BASE || '/');
    } catch (e) { showError(e?.name === 'TypeError' ? '요청 결과를 확인하지 못했습니다. 새 비밀번호로 로그인되는지 먼저 확인해 주세요.' : e.message || '요청에 실패했습니다.'); }
    finally { button.disabled = false; button.textContent = label; form.removeAttribute('aria-busy'); }
  });
})();
