// NOL 인증 입력 구조와 동일하게 처리하되 블루의 일반 POST 흐름을 유지한다.
(() => {
  'use strict';
  const form = document.getElementById('login_form');
  if (!form) return;
  const password = form.querySelector('#upwd');
  const toggle = form.querySelector('.no-pwd-btn');
  const submit = form.querySelector('[type="submit"]');
  const fields = [
    [form.querySelector('#uid'), '아이디를 입력하세요.'],
    [password, '비밀번호를 입력하세요.'],
    [form.querySelector('#r_captcha'), '보안코드 5자리를 입력하세요.'],
  ];
  let submitting = false;
  toggle.addEventListener('click', () => {
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    toggle.setAttribute('aria-pressed', String(visible));
    toggle.setAttribute('aria-label', visible ? '비밀번호 숨기기' : '비밀번호 보기');
    toggle.querySelector('.no-pwd-text').textContent = visible ? '숨기기' : '보기';
    toggle.querySelector('.no-pwd-icon').classList.toggle('fa-eye', !visible);
    toggle.querySelector('.no-pwd-icon').classList.toggle('fa-eye-slash', visible);
  });
  const setError = (input, message) => {
    const error = input.closest('.no-auth-field').querySelector('.no-invalid');
    input.classList.toggle('invalid', !!message);
    input.setAttribute('aria-invalid', String(!!message));
    error.classList.toggle('show', !!message);
    if (message) error.querySelector('span').textContent = message;
  };
  fields.forEach(([input]) => input.addEventListener('input', () => setError(input, '')));
  form.addEventListener('submit', (event) => {
    if (submitting) { event.preventDefault(); return; }
    let firstInvalid = null;
    fields.forEach(([input, message]) => {
      const valid = input.id === 'r_captcha'
        ? /^[0-9]{5}$/.test(input.value.trim())
        : input.value.trim() !== '';
      setError(input, valid ? '' : message);
      if (!valid && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid) {
      event.preventDefault();
      firstInvalid.focus();
      return;
    }
    submitting = true;
    submit.disabled = true;
    form.setAttribute('aria-busy', 'true');
    document.body.classList.add('is-loading');
  });
  window.addEventListener('pageshow', () => {
    submitting = false;
    submit.disabled = false;
    form.removeAttribute('aria-busy');
    document.body.classList.remove('is-loading');
  });
})();
