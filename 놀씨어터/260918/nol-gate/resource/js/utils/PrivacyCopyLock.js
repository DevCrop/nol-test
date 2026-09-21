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
      if (e.target.closest("#pii-reason-modal")) {
        return;
      }
      e.preventDefault();
    };
    ["copy", "cut", "dragstart", "contextmenu", "selectstart"].forEach((type) => {
      this.root.addEventListener(type, block);
    });
  }
}
