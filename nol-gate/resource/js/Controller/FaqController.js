import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";
import { initCheckboxManager } from "../utils/initCheckboxManager.js";
import { summernoteInit } from "../core/summernoteInit.js";

const ADMIN_PAGES_BASE = window.NO_ADMIN_PAGES_BASE || "/pages";

export class FaqController {
  constructor({
    formSelector = "#frm",
    insertBtnSelector = "#submitBtn",
    updateBtnSelector = "#editBtn",
    deleteBtnSelector = ".delete-btn",
    sortBtnSelector = ".sort-btn",
  } = {}) {
    this.form = document.querySelector(formSelector);
    this.insertBtn = document.querySelector(insertBtnSelector);
    this.updateBtn = document.querySelector(updateBtnSelector);
    this.deleteButtons = document.querySelectorAll(deleteBtnSelector);
    this.sortButtons = document.querySelectorAll(sortBtnSelector);
  }

  init() {
    console.log("[FaqController.js] 초기화됨");

    // Summernote 초기화
    summernoteInit("#answer");

    if (this.form && this.insertBtn) {
      this.insertBtn.addEventListener("click", this.insert.bind(this));
    }

    if (this.form && this.updateBtn) {
      this.updateBtn.addEventListener("click", this.update.bind(this));
    }

    this.attachDeleteEvents();
    this.attachSortEvents();

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
    await this.sendRequest(formData, "FAQ가 등록되었습니다.");
  }

  async update(e) {
    e.preventDefault();
    const formData = new FormData(this.form);
    formData.set("mode", "update");
    await this.sendRequest(formData, "FAQ가 수정되었습니다.");
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

  attachSortEvents() {
    this.sortButtons.forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        if (!id || !action) return;

        const targetSortNo = parseInt(btn.dataset.no, 10);
        if (isNaN(targetSortNo) || targetSortNo <= 0) {
          alert("잘못된 순서 번호입니다.");
          return;
        }

        const formData = new FormData();
        formData.set("mode", "sort");
        formData.set("id", id);
        formData.set("new_no", targetSortNo);

        await this.sendRequest(formData, "순서가 변경되었습니다.");
      });
    });
  }

  async sendRequest(formData, successMessage) {
    try {
      const res = await fetcher(API.FAQ, formData);
      const mode = formData.get("mode");
      const page = formData.get("page") || "";

      if (["insert", "update", "delete", "delete_array"].includes(mode)) {
        alert(successMessage);
        if (mode === "delete" || mode === "delete_array") {
          location.reload();
        } else {
          const pageParam = page ? `?page=${page}` : "";
          location.href = `${ADMIN_PAGES_BASE}/faq/index.php${pageParam}`;
        }
        return;
      }

      if (mode === "sort") {
        if (
          res.message === "제일 높은 순서의 게시물입니다." ||
          res.message === "제일 낮은 순서의 게시물입니다."
        ) {
          alert(res.message);
        }
        location.reload();
        return;
      }

      // 그 외 기본 처리 (필요 시)
      const pageParam = page ? `?page=${page}` : "";
      location.href = `${ADMIN_PAGES_BASE}/faq/index.php${pageParam}`;
    } catch (err) {
      alert(err.message || "처리 중 오류가 발생했습니다.");
    }
  }
}
