export class InquiryDownloadReason {
  constructor(root) {
    this.root = root;
    this.form = root?.querySelector("#pii-download-form");
    this.modal = root?.querySelector("#pii-reason-modal");
    this.input = root?.querySelector("#pii-reason");
    this.pending = null;
  }

  init() {
    if (!this.root || !this.form || !this.modal) {
      return;
    }
    this.root.querySelectorAll("[data-pii-download]").forEach((el) => {
      el.addEventListener("click", (e) => {
        e.preventDefault();
        this.pending = {
          no: el.getAttribute("data-no") || "",
          slot: el.getAttribute("data-slot") || "",
          file: el.getAttribute("data-file") || "",
        };
        if (this.input) {
          this.input.value = "";
        }
        this.modal.hidden = false;
        this.input?.focus();
      });
    });
    this.modal.querySelector("[data-pii-cancel]")?.addEventListener("click", () => {
      this.modal.hidden = true;
      this.pending = null;
    });
    this.modal.querySelector("[data-pii-confirm]")?.addEventListener("click", () => {
      const reason = (this.input?.value || "").trim();
      if (reason.length < 2) {
        alert("다운로드 사유를 입력하세요.");
        return;
      }
      if (!this.pending) {
        return;
      }
      this.form.querySelector("[name=no]").value = this.pending.no;
      this.form.querySelector("[name=slot]").value = this.pending.slot;
      this.form.querySelector("[name=file]").value = this.pending.file;
      this.form.querySelector("[name=reason]").value = reason;
      this.modal.hidden = true;
      this.form.submit();
    });
  }
}
