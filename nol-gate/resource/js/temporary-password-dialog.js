// Development-only UI. Values are fixed synthetic samples, not account credentials.
const dialog = document.querySelector('#temporary-ui-dialog');
const password = document.querySelector('#temporary-ui-password');
const toggle = document.querySelector('#temporary-ui-toggle');
const status = document.querySelector('#temporary-ui-status');
function resetDisplay() {
  password.type = 'password';
  toggle.textContent = '보기';
  toggle.setAttribute('aria-pressed', 'false');
  status.textContent = '디자인 예시 · 실제 로그인에는 사용할 수 없습니다.';
}
document.querySelectorAll('[data-temporary-ui-open]').forEach(button => button.addEventListener('click', () => {
  resetDisplay(); dialog.showModal();
}));
document.querySelectorAll('[data-temporary-ui-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
dialog.addEventListener('close', resetDisplay);
toggle.addEventListener('click', () => {
  const show = password.type === 'password';
  password.type = show ? 'text' : 'password';
  toggle.textContent = show ? '숨기기' : '보기';
  toggle.setAttribute('aria-pressed', String(show));
});
function copyWithoutClipboardApi(text) {
  // HTTP-compatible fallback, contained in the active modal and removed immediately.
  const previous = document.activeElement;
  const buffer = document.createElement('textarea');
  buffer.value = text;
  buffer.setAttribute('data-temporary-ui-copy-buffer', '');
  buffer.setAttribute('readonly', '');
  dialog.appendChild(buffer);
  try {
    buffer.focus();
    buffer.select();
    return document.execCommand('copy') === true;
  } finally {
    buffer.value = '';
    buffer.remove();
    previous?.focus();
  }
}
document.querySelector('#temporary-ui-copy').addEventListener('click', async () => {
  const text = `아이디 : ${document.querySelector('#temporary-ui-uid').value}\n임시비밀번호 : ${password.value}`;
  try {
    let copied = false;
    if (globalThis.isSecureContext && navigator.clipboard?.writeText) {
      try { await navigator.clipboard.writeText(text); copied = true; } catch { /* Try HTTP-compatible copy. */ }
    }
    if (!copied) copied = copyWithoutClipboardApi(text);
    if (!copied) throw new Error('copy-failed');
    window.alert('복사되었습니다.');
  } catch {
    window.alert('복사하지 못했습니다. 브라우저의 클립보드 권한을 확인해 주세요.');
  }
});
if (new URLSearchParams(window.location.search).get('temporary-ui') === 'open') {
  resetDisplay(); dialog.showModal();
}
