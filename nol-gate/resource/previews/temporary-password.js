// UI prototype only: no network requests, browser storage, authentication or DB writes.
const form = document.querySelector('#password-preview-form');
const password = document.querySelector('#new-password');
const confirmation = document.querySelector('#confirm-password');
const error = document.querySelector('#preview-error');
const match = document.querySelector('#match-status');
const panels = ['change-panel', 'success-panel', 'admin-panel'];

export function passwordChecks(value, repeated) {
  return { length: value.length >= 8 && value.length <= 64,
    mix: /[a-zA-Z]/.test(value) && /[0-9]/.test(value) && /[^a-zA-Z0-9\s]/.test(value) && !/\s/.test(value),
    match: value.length > 0 && value === repeated };
}

function refresh() {
  const checks = passwordChecks(password.value, confirmation.value);
  for (const key of ['length', 'mix']) {
    const rule = document.querySelector('#rule-' + key);
    rule.classList.toggle('valid', checks[key]);
    rule.querySelector('span').textContent = checks[key] ? '✓' : '○';
  }
  match.className = 'match' + (confirmation.value ? checks.match ? ' valid' : ' invalid' : '');
  match.textContent = confirmation.value ? checks.match ? '비밀번호가 일치합니다.' : '비밀번호가 일치하지 않습니다.' : '동일한 비밀번호를 한 번 더 입력해 주세요.';
  password.removeAttribute('aria-invalid'); confirmation.removeAttribute('aria-invalid');
  error.hidden = true;
  return checks;
}

function show(id) {
  panels.forEach(panel => { document.getElementById(panel).hidden = panel !== id; });
  const completed = id !== 'change-panel';
  document.querySelector('#password-step').classList.toggle('done', completed);
  document.querySelector('#password-step .step-number').textContent = completed ? '✓' : '3';
  document.querySelector('#step-status').textContent = completed ? '변경 완료' : '마지막 단계';
  if (completed) document.querySelector('#password-step').removeAttribute('aria-current');
  else document.querySelector('#password-step').setAttribute('aria-current', 'step');
  const heading = document.querySelector('#' + id + ' .no-login-title');
  heading.setAttribute('tabindex', '-1'); heading.focus();
}

form.addEventListener('submit', event => {
  event.preventDefault();
  const checks = refresh();
  if (!checks.length || !checks.mix || !checks.match) {
    error.hidden = false;
    error.textContent = !checks.length || !checks.mix ? '비밀번호 조건을 확인해 주세요. 공백은 사용할 수 없습니다.' : '새 비밀번호와 확인 값이 일치하지 않습니다.';
    const invalid = !checks.length || !checks.mix ? password : confirmation;
    invalid.setAttribute('aria-invalid', 'true'); invalid.focus();
    return;
  }
  form.reset(); refresh(); show('success-panel');
});
[password, confirmation].forEach(input => input.addEventListener('input', refresh));
document.querySelectorAll('.toggle').forEach(button => button.addEventListener('click', () => {
  const input = document.getElementById(button.dataset.for);
  const visible = input.type === 'password'; input.type = visible ? 'text' : 'password';
  button.textContent = visible ? '숨기기' : '보기'; button.setAttribute('aria-pressed', String(visible));
  button.setAttribute('aria-label', (input === password ? '새 비밀번호 ' : '확인 비밀번호 ') + (visible ? '숨기기' : '보기'));
}));
function reset() {
  form.reset();
  document.querySelectorAll('.toggle').forEach(button => {
    const input = document.getElementById(button.dataset.for); input.type = 'password';
    button.textContent = '보기'; button.setAttribute('aria-pressed', 'false');
    button.setAttribute('aria-label', (input === password ? '새 비밀번호 ' : '확인 비밀번호 ') + '보기');
  });
  refresh(); show('change-panel');
}
document.querySelector('#restart').addEventListener('click', reset);
document.querySelector('#restart-bottom').addEventListener('click', reset);
document.querySelector('#show-admin').addEventListener('click', () => show('admin-panel'));
