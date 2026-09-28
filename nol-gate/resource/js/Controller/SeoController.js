import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";
import { initCheckboxManager } from "../utils/initCheckboxManager.js";

const ADMIN_PAGES_BASE = window.NO_ADMIN_PAGES_BASE || "/pages";

export class SeoController {
  constructor({
    formSelector = "#frm",
    insertBtnSelector = "#submitBtn",
    updateBtnSelector = "#editBtn",
    deleteBtnSelector = ".delete-btn",
    pathSelector = "#path",
  } = {}) {
    this.form = document.querySelector(formSelector);
    this.insertBtn = document.querySelector(insertBtnSelector);
    this.updateBtn = document.querySelector(updateBtnSelector);
    this.deleteButtons = document.querySelectorAll(deleteBtnSelector);
    this.pathSelect = document.querySelector(pathSelector);
  }

  init() {
    console.log("[SeoController.js] 초기화됨");

    if (this.form && this.insertBtn) {
      this.insertBtn.addEventListener("click", this.insert.bind(this));
    }

    if (this.form && this.updateBtn) {
      this.updateBtn.addEventListener("click", this.update.bind(this));
    }

    this.attachDeleteEvents();

    // PHP에서 이미 옵션을 추가했지만, edit 페이지에서는 동적으로 로드 필요
    // new 페이지는 PHP에서 이미 옵션이 있으므로 로드 불필요
    if (this.pathSelect && this.pathSelect.options.length <= 1) {
      this.loadMenuOptions();
    }

    initCheckboxManager(async (selectedIds) => {
      const formData = new FormData();
      formData.set("mode", "delete_array");
      formData.set("ids", JSON.stringify(selectedIds));
      await this.sendRequest(formData, "선택 항목이 삭제되었습니다.");
    });
  }

  async insert(e) {
    e.preventDefault();
    const formData = new FormData(this.form);
    formData.set("mode", "insert");

    await this.sendRequest(formData, "등록되었습니다.");
  }

  async update(e) {
    e.preventDefault();
    const formData = new FormData(this.form);
    formData.set("mode", "update");

    const page = this.form.querySelector("input[name='page']")?.value || 1;

    try {
      const res = await fetcher(API.SEO, formData);
      alert(res.message || "수정되었습니다.");
      location.href = `${ADMIN_PAGES_BASE}/setting/seo.php?page=${page}`;
    } catch (err) {
      alert(err.message || "처리 중 오류가 발생했습니다.");
    }
  }

  attachDeleteEvents() {
    this.deleteButtons.forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        if (!id) return;
        if (!confirm("정말 삭제하시겠습니까?")) return;

        const formData = new FormData();
        formData.set("mode", "delete");
        formData.set("id", id);

        await this.sendRequest(formData, "삭제되었습니다.");
      });
    });
  }

  async sendRequest(formData, successMessage) {
    try {
      const res = await fetcher(API.SEO, formData);
      alert(res.message || successMessage);

      const mode = formData.get("mode");
      if (mode === "delete" || mode === "delete_array") {
        location.reload();
      } else {
        location.href = `${ADMIN_PAGES_BASE}/setting/seo.php`;
      }
    } catch (err) {
      alert(err.message || "처리 중 오류가 발생했습니다.");
    }
  }
  async loadMenuOptions() {
    if (!this.pathSelect) return;

    // 현재 선택값 보존 (data-current 우선)
    const current =
      this.pathSelect.getAttribute("data-current") ||
      this.pathSelect.value ||
      "";

    // 초기화
    this.pathSelect.innerHTML = '<option value="">페이지 경로 선택</option>';

    // PHP에서 전달받은 라우트 목록 사용
    if (window.routeOptions && Array.isArray(window.routeOptions)) {
      window.routeOptions.forEach((route) => {
        const opt = document.createElement("option");
        opt.value = route.uri;
        opt.textContent = route.display;
        this.pathSelect.appendChild(opt);
      });

      // 현재 선택값 복원
      if (
        current &&
        Array.from(this.pathSelect.options).some((o) => o.value === current)
      ) {
        this.pathSelect.value = current;
      }
    } else {
      console.warn("라우트 목록을 불러올 수 없습니다.");
    }
  }
}
