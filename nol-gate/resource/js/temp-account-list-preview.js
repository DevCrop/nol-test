// UI prototype: read/copy fixed synthetic samples only. No server requests or storage.
export function sharingText(uid, password) {
  return `디자인 예시 — 실제 로그인 불가\n아이디: ${uid}\n임시 비밀번호: ${password}\n로그인 후 이메일 인증을 완료하고 새 비밀번호로 변경해 주세요.`;
}
document.querySelectorAll('[data-preview-toggle]').forEach(button => button.addEventListener('click', () => {
  const input = document.getElementById(button.dataset.previewToggle);
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  button.textContent = show ? '숨기기' : '보기';
  button.setAttribute('aria-pressed', String(show));
}));
document.querySelectorAll('[data-preview-copy]').forEach(button => button.addEventListener('click', async () => {
  const input = document.getElementById(button.dataset.previewCopy);
  const uid = button.closest('tr').dataset.previewUid;
  const text = sharingText(uid, input.value);
  const status = document.querySelector('#sample-copy-status');
  const fallback = document.querySelector('#sample-copy-fallback');
  try {
    if (!globalThis.isSecureContext || !navigator.clipboard?.writeText) throw new Error('manual-copy');
    await navigator.clipboard.writeText(text);
    fallback.hidden = true;
    status.textContent = '예시 로그인 정보가 복사되었습니다. 실제 사용 가능한 계정은 아닙니다.';
  } catch {
    fallback.hidden = false;
    const area = document.querySelector('#sample-copy-text');
    area.value = text;
    area.focus();
    area.select();
    status.textContent = '아래 선택된 예시 정보를 복사해 주세요.';
  }
}));
