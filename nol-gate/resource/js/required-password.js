(() => {
  const form = document.querySelector('#required-password-form') || document.querySelector('#password-change-form');
  if (!form) return;
  const button = form.querySelector('[type="submit"]');
  const error = document.querySelector('#required-password-error');
  const showError = message => {
    error.textContent = message;
    error.hidden = !message;
  };
  form.querySelectorAll('[data-password-toggle]').forEach(toggle => {
    toggle.addEventListener('click', () => {
      const input = document.getElementById(toggle.dataset.passwordToggle);
      if (!input || !form.contains(input)) return;
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(visible));
      const label = input.labels[0]?.textContent.trim() || '비밀번호';
      toggle.setAttribute('aria-label', label + (visible ? ' 숨기기' : ' 보기'));
      toggle.querySelector('.no-pwd-text').textContent = visible ? '숨기기' : '보기';
      toggle.querySelector('i').className = visible ? 'bx bx-hide' : 'bx bx-show';
    });
  });
  window.addEventListener('pagehide', () => {
    form.querySelectorAll('[autocomplete$="-password"]').forEach(input => {
      input.type = 'password';
      input.value = '';
    });
  });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (button.disabled) return;
    showError('');
    const data = new FormData(form);
    if (data.get('pwd_new') !== data.get('pwd_new_confirm')) {
      showError('새 비밀번호와 확인 값이 일치하지 않습니다.');
      form.querySelector('#pwd_new_confirm')?.focus();
      return;
    }
    button.disabled = true;
    try {
      const response = await fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', cache: 'no-store' });
      if (response.status === 401) {
        location.replace(window.NO_ADMIN_BASE || '/');
        return;
      }
      if (!response.headers.get('Content-Type')?.includes('application/json')) {
        showError('서버 응답을 확인할 수 없습니다. 잠시 후 다시 시도해 주세요.');
        return;
      }
      const result = await response.json();
      if (!response.ok || result.result !== 'success') {
        showError(result.msg || result.message || '변경하지 못했습니다. 다시 시도하세요.');
        return;
      }
      form.reset();
      alert(result.msg);
      location.replace(window.NO_ADMIN_BASE || '/');
    } catch {
      showError('요청에 실패했습니다. 잠시 후 다시 시도해 주세요.');
    } finally {
      button.disabled = false;
    }
  });
})();
