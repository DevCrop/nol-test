(() => {
  const form = document.querySelector('#required-password-form');
  if (!form) return;
  const button = form.querySelector('[type="submit"]');
  const error = document.querySelector('#required-password-error');
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (button.disabled) return;
    error.textContent = '';
    const data = new FormData(form);
    if (data.get('new_password') !== data.get('new_password_confirm')) { error.textContent = '새 비밀번호와 확인 값이 일치하지 않습니다.'; return; }
    button.disabled = true;
    try {
      const response = await fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', cache: 'no-store' });
      const result = await response.json();
      if (response.status === 401) { location.replace(window.NO_ADMIN_BASE || '/'); return; }
      if (!response.ok || result.result !== 'success') throw new Error(result.msg || result.message || '변경하지 못했습니다. 다시 시도하세요.');
      form.reset(); alert(result.msg); location.replace(window.NO_ADMIN_BASE || '/');
    } catch (e) { error.textContent = e.message || '요청에 실패했습니다.'; }
    finally { button.disabled = false; }
  });
})();
