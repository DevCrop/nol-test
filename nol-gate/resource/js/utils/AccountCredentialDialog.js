export class AccountCredentialDialog {
  constructor() {
    this.dialog = document.querySelector('#account-credential-dialog');
    this.uid = document.querySelector('#credential-uid');
    this.password = document.querySelector('#credential-password');
    this.toggle = document.querySelector('#credential-toggle');
    this.onClose = null;
    if (!this.dialog) return;
    this.dialog.querySelectorAll('[data-credential-close]').forEach(button => button.addEventListener('click', () => this.dialog.close()));
    this.dialog.addEventListener('close', () => { this.clear(); const callback = this.onClose; this.onClose = null; callback?.(); });
    this.toggle.addEventListener('click', () => {
      const show = this.password.type === 'password'; this.password.type = show ? 'text' : 'password';
      this.toggle.textContent = show ? '숨기기' : '보기'; this.toggle.setAttribute('aria-pressed', String(show));
    });
    document.querySelector('#credential-copy').addEventListener('click', () => this.copy());
    window.addEventListener('pagehide', () => { this.onClose = null; this.clear(); if (this.dialog.open) this.dialog.close(); });
  }
  show(credential, onClose) {
    if (!this.dialog || !credential?.temporary_password) return false;
    this.clear();
    this.uid.value = credential.uid;
    this.password.value = credential.temporary_password;
    credential.temporary_password = '';
    document.querySelector('#credential-expires').textContent = `임시 비밀번호 유효기간: ${credential.expires_at}`;
    this.onClose = onClose;
    this.dialog.showModal();
    return true;
  }
  clear() {
    if (!this.dialog) return;
    this.uid.value = ''; this.password.value = ''; this.password.type = 'password';
    this.toggle.textContent = '보기'; this.toggle.setAttribute('aria-pressed', 'false');
    document.querySelector('#credential-expires').textContent = '';
  }
  async copy() {
    if (!this.password.value) return;
    let text = `아이디 : ${this.uid.value}\n임시비밀번호 : ${this.password.value}`;
    try {
      let copied = false;
      if (globalThis.isSecureContext && navigator.clipboard?.writeText) {
        try { await navigator.clipboard.writeText(text); copied = true; } catch { /* HTTP fallback. */ }
      }
      if (!copied) {
        const previous = document.activeElement;
        const buffer = document.createElement('textarea'); buffer.value = text; buffer.readOnly = true;
        buffer.setAttribute('data-copy-buffer', ''); this.dialog.appendChild(buffer);
        try { buffer.focus(); buffer.select(); copied = document.execCommand('copy') === true; }
        finally { buffer.value = ''; buffer.remove(); previous?.focus(); }
      }
      alert(copied ? '복사되었습니다.' : '복사하지 못했습니다. 브라우저 권한을 확인해 주세요.');
    } catch { alert('복사하지 못했습니다. 브라우저 권한을 확인해 주세요.'); }
    finally { text = ''; }
  }
}
