import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";

const ADMIN_PAGES_BASE = window.NO_ADMIN_PAGES_BASE || "/pages";

export class BoardController {
  constructor({ sortBtnSelector = ".sort-btn" } = {}) {
    this.sortButtons = document.querySelectorAll(sortBtnSelector);
  }

  init() {
    console.log("[BoardController] Initialized");
    this.attachSortEvents();
  }

  attachSortEvents() {
    this.sortButtons.forEach((btn) => {
      btn.addEventListener("click", async () => {
        const no = btn.dataset.id; // 여기서 id 대신 no 값이 들어옴
        const targetSortNo = parseInt(btn.dataset.no, 10);

        if (!no || isNaN(targetSortNo) || targetSortNo <= 0) {
          alert("잘못된 데이터입니다.");
          return;
        }

        const formData = new FormData();
        formData.set("mode", "sort");
        formData.set("no", no); // id → no 변경
        formData.set("new_no", targetSortNo);

        await this.sendRequest(formData);
      });
    });
  }

  async sendRequest(formData, successMessage) {
    try {
      const res = await fetcher(API.BOARD, formData);
      const mode = formData.get("mode");

      if (["insert", "update", "delete", "delete_array"].includes(mode)) {
        alert(successMessage);
        if (mode === "delete" || mode === "delete_array") {
          location.reload();
        } else {
          location.href = `${ADMIN_PAGES_BASE}/board/board.list.php`;
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
      location.href = `${ADMIN_PAGES_BASE}/board/board.list.php`;
    } catch (err) {
      alert(err.message || "처리 중 오류가 발생했습니다.");
    }
  }
}
