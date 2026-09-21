import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";

export class PrivacyAccessController {
  constructor() {
    this.tbody = document.querySelector("#privacy-access-rows");
    this.pager = document.querySelector("#privacy-access-pager");
    this.page = 1;
  }

  init() {
    this.pager?.addEventListener("click", (e) => {
      const link = e.target.closest("[data-page]");
      if (!link) {
        return;
      }
      e.preventDefault();
      const next = Number(link.dataset.page);
      if (!Number.isFinite(next) || next < 1) {
        return;
      }
      this.page = next;
      this.load();
    });
    this.load();
  }

  async load() {
    const formData = new FormData();
    formData.set("mode", "list");
    formData.set("page", String(this.page));
    try {
      const res = await fetcher(API.PRIVACY_ACCESS, formData);
      this.renderRows(res.rows || []);
      this.renderPager(Number(res.page) || 1, Number(res.pages) || 1);
    } catch (err) {
      if (this.tbody) {
        this.tbody.innerHTML = `<tr><td colspan="7">${this.esc(err.message || "불러오기 실패")}</td></tr>`;
      }
    }
  }

  renderRows(rows) {
    if (!this.tbody) {
      return;
    }
    if (!rows.length) {
      this.tbody.innerHTML = `<tr><td colspan="7">기록이 없습니다.</td></tr>`;
      return;
    }
    this.tbody.innerHTML = rows
      .map(
        (row) => `<tr>
          <td>${this.esc(row.created_at)}</td>
          <td>${this.esc(row.actor_uid)}</td>
          <td>${this.esc(row.actor_ip)}</td>
          <td>${this.esc(row.action_label)}</td>
          <td>${this.esc(row.subject_label)}</td>
          <td>${this.esc(row.task)}</td>
          <td>${this.esc(row.reason)}</td>
        </tr>`
      )
      .join("");
  }

  renderPager(page, pages) {
    if (!this.pager) {
      return;
    }
    const prev = page <= 1;
    const next = page >= pages;
    this.pager.innerHTML = `<div class="no-pagination"><ul class="no-page-list">
      <li class="no-page-item ${prev ? "disabled" : ""}">
        <a href="javascript:void(0);" class="no-page-link" data-page="${Math.max(1, page - 1)}"><i class="bx bx-chevron-left"></i></a>
      </li>
      <li class="no-page-item disabled">
        <a href="javascript:void(0);" class="no-page-link">${page} / ${pages}</a>
      </li>
      <li class="no-page-item ${next ? "disabled" : ""}">
        <a href="javascript:void(0);" class="no-page-link" data-page="${Math.min(pages, page + 1)}"><i class="bx bx-chevron-right"></i></a>
      </li>
    </ul></div>`;
  }

  esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
}
