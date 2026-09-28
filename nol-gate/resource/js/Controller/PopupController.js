import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";
import { initCheckboxManager } from "../utils/initCheckboxManager.js";
import { attachRadioToggle } from "../utils/initRadioToggle.js";

const ADMIN_PAGES_BASE = window.NO_ADMIN_PAGES_BASE || "/pages";

export class PopupController {
  constructor({
    formSelector = "#frm",
    insertBtnSelector = "#submitBtn",
    sortBtnSelector = ".sort-btn",
    updateBtnSelector = "#editBtn",
    deleteBtnSelector = ".delete-btn",
  } = {}) {
    this.form = document.querySelector(formSelector);
    this.sortButtons = document.querySelectorAll(sortBtnSelector);
    this.insertBtn = document.querySelector(insertBtnSelector);
    this.updateBtn = document.querySelector(updateBtnSelector);
    this.deleteButtons = document.querySelectorAll(deleteBtnSelector);
  }

  init() {
    console.log("[PopupController] Initialized");

    this.bindFormEvents();
    this.attachDeleteEvents();
    this.attachSortEvents();

    initCheckboxManager(async (selectedIds) => {
      const formData = new FormData();
      formData.set("mode", "delete_array");
      formData.set("ids", JSON.stringify(selectedIds));
      await this.sendRequest(formData, "선택된 팝업이 삭제되었습니다.");
    });

    attachRadioToggle({
      radioName: "has_link",
      targetIds: ["link_url_block", "link_target_block"],
      toggleOnValue: "1",
      mode: "visibility",
    });

    attachRadioToggle({
      radioName: "is_unlimited",
      targetIds: ["display_period"],
      toggleOnValue: "2",
      mode: "visibility",
    });
  }

  bindFormEvents() {
    if (this.form && this.insertBtn) {
      this.insertBtn.addEventListener("click", this.insert.bind(this));
    }

    if (this.form && this.updateBtn) {
      this.updateBtn.addEventListener("click", this.update.bind(this));
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
        await this.sendRequest(formData, "팝업이 삭제되었습니다.");
      });
    });
  }

  async insert(e) {
    e.preventDefault();
    const formData = new FormData(this.form);
    formData.set("mode", "insert");
    await this.sendRequest(formData, "팝업이 등록되었습니다.");
  }

  async update(e) {
    e.preventDefault();
    const formData = new FormData(this.form);
    formData.set("mode", "update");
    await this.sendRequest(formData, "팝업이 수정되었습니다.");
  }

  attachSortEvents() {
    this.sortButtons.forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        console.log(id, action);

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
      const res = await fetcher(API.POPUP, formData);
      const mode = formData.get("mode");

      if (["insert", "update", "delete", "delete_array"].includes(mode)) {
        alert(successMessage);
        if (mode === "delete" || mode === "delete_array") {
          location.reload();
        } else {
          location.href = `${ADMIN_PAGES_BASE}/design/popup.list.php`;
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
      location.href = `${ADMIN_PAGES_BASE}/design/popup.list.php`;
    } catch (err) {
      alert(err.message || "처리 중 오류가 발생했습니다.");
    }
  }
}
