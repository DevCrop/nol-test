export class PrivacyCopyLock {
  constructor(root) {
    this.root = root;
  }

  init() {
    if (!this.root) {
      return;
    }
    this.root.classList.add("no-pii-lock");
    const block = (e) => {
      if (e.target.matches("dialog[data-account-credential-dialog][open] [data-copy-buffer]") &&
          (e.type === "copy" || e.type === "selectstart")) {
        return;
      }
      if (e.target.closest("#pii-reason-modal, input:not([readonly]):not([disabled]), textarea:not([readonly]):not([disabled]), select:not([disabled]), button, [contenteditable='true']")) {
        return;
      }
      e.preventDefault();
    };
    ["copy", "cut", "dragstart", "contextmenu", "selectstart"].forEach((type) => {
      this.root.addEventListener(type, block);
    });
  }
}
