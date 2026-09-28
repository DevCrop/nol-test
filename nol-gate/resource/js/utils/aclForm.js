export class AclForm {
  constructor(root = document.querySelector("[data-acl-form]")) {
    this.root = root;
    this.radios = document.querySelectorAll('input[name="role_id"]');
    this.error = root ? root.querySelector("[data-acl-error]") : null;
  }

  init() {
    if (!this.root) {
      return this;
    }
    this.radios.forEach((radio) => radio.addEventListener("change", () => this.sync()));
    this.root.querySelectorAll("[data-acl-row]").forEach((row) => {
      row.addEventListener("change", (e) => this.onRowChange(row, e.target));
    });
    this.sync();
    return this;
  }

  isSuper() {
    return Array.from(this.radios).some((radio) => radio.checked && radio.value === "1");
  }

  sync() {
    const hide = this.isSuper();
    this.root.hidden = hide;
    this.root.classList.toggle("is-disabled", hide);
    this.root.querySelectorAll('input[type="checkbox"]').forEach((input) => {
      input.disabled = hide;
    });
    this.clearError();
  }

  onRowChange(row, input) {
    if (!(input instanceof HTMLInputElement) || input.dataset.aclAction === undefined) {
      return;
    }
    const view = row.querySelector('[data-acl-action="view"]');
    const writes = row.querySelectorAll('[data-acl-action="create"], [data-acl-action="update"], [data-acl-action="delete"]');
    if (input.dataset.aclAction !== "view" && input.checked && view instanceof HTMLInputElement) {
      view.checked = true;
    }
    if (input.dataset.aclAction === "view" && !input.checked) {
      writes.forEach((box) => {
        box.checked = false;
      });
    }
    this.clearError();
  }

  validate() {
    this.clearError();
    if (!this.root || this.isSuper() || this.root.hidden) {
      return true;
    }
    const views = this.root.querySelectorAll('[data-acl-action="view"]:checked');
    if (views.length === 0) {
      this.fail("일반 관리자는 메뉴 권한을 1개 이상 선택하세요.");
      return false;
    }
    let ok = true;
    this.root.querySelectorAll("[data-acl-row]").forEach((row) => {
      const view = row.querySelector('[data-acl-action="view"]');
      const writes = row.querySelectorAll('[data-acl-action="create"]:checked, [data-acl-action="update"]:checked, [data-acl-action="delete"]:checked');
      if (writes.length > 0 && view instanceof HTMLInputElement && !view.checked) {
        ok = false;
      }
    });
    if (!ok) {
      this.fail("등록·수정·삭제는 해당 메뉴 조회가 있어야 합니다.");
      return false;
    }
    return true;
  }

  fail(message) {
    if (this.error) {
      this.error.hidden = false;
      this.error.textContent = message;
    }
    this.root.classList.add("is-invalid");
    this.root.scrollIntoView({ behavior: "smooth", block: "center" });
    alert(message);
  }

  clearError() {
    this.root.classList.remove("is-invalid");
    if (this.error) {
      this.error.hidden = true;
      this.error.textContent = "";
    }
  }
}
