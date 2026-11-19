import { fetcher } from "../core/fetcher.js";
import { API } from "../core/apiRoutes.js";

export class AuditController {
  constructor(options = {}) {
    this.form = document.querySelector("#frm");
    this.entity = document.querySelector("#entity");
    this.action = document.querySelector("#action");
    this.keyword = document.querySelector("#keyword");
    this.tbody = document.querySelector(options.rows || "#audit-rows");
    this.pager = document.querySelector(options.pager || "#audit-pager");
    this.total = document.querySelector(options.total || "#audit-total");
    this.endpoint = options.endpoint || API.AUDIT;
    this.title = options.title || "작업이력";
    this.page = 1;
    this.request = 0;
    this.filters = { entity: "", action: "", keyword: "" };
  }

  init() {
    const search = () => {
      this.filters = { entity: this.entity?.value || "", action: this.action?.value || "", keyword: this.keyword?.value.trim() || "" };
      this.page = 1;
      this.load();
    };
    this.form?.addEventListener("submit", (event) => { event.preventDefault(); search(); });
    this.entity?.addEventListener("change", search);
    this.action?.addEventListener("change", search);
    document.querySelector("#audit-reset")?.addEventListener("click", () => {
      this.form?.reset();
      search();
    });
    this.pager?.addEventListener("click", (event) => {
      const button = event.target.closest("button[data-audit-page]");
      if (!button || button.disabled) return;
      const next = Number(button.dataset.auditPage);
      if (!Number.isInteger(next) || next < 1 || next === this.page) return;
      this.page = next;
      this.load();
    });
    search();
  }

  async load() {
    const request = ++this.request;
    const formData = new FormData();
    formData.set("mode", "list");
    formData.set("page", String(this.page));
    Object.entries(this.filters).forEach(([key, value]) => formData.set(key, value));
    this.tbody?.setAttribute("aria-busy", "true");
    this.message(`${this.title} 조회 중입니다.`, "잠시만 기다려 주세요.");
    if (this.total) this.total.textContent = "조회 중";
    if (this.pager) this.pager.innerHTML = "";
    try {
      const res = await fetcher(this.endpoint, formData);
      if (request !== this.request) return;
      this.page = Number(res.page) || 1;
      this.renderRows(res.rows || []);
      const total = Number(res.total) || 0;
      const size = Number(res.perpage) || 10;
      const pages = Number(res.pages) || 1;
      if (this.total) {
        const range = total ? ` · ${(this.page - 1) * size + 1}-${Math.min(this.page * size, total)}건 · ${this.page}/${pages}페이지` : "";
        this.total.textContent = `총 ${total.toLocaleString("ko-KR")}건${range}`;
      }
      this.renderPager(this.page, pages, total);
    } catch (err) {
      if (request !== this.request) return;
      this.message(`${this.title} 조회에 실패했습니다.`, err.message || "잠시 후 다시 검색해 주세요.");
      if (this.total) this.total.textContent = "조회 실패";
    } finally {
      if (request === this.request) this.tbody?.setAttribute("aria-busy", "false");
    }
  }

  message(title, description) {
    if (this.tbody) this.tbody.innerHTML = `<tr><td colspan="5" class="admin-log-empty"><strong>${this.esc(title)}</strong>${this.esc(description)}</td></tr>`;
  }

  renderRows(rows) {
    if (!this.tbody) return;
    if (!rows.length) {
      this.message("조건에 맞는 작업이력이 없습니다.", "검색어 또는 필터를 변경하거나 초기화해 주세요.");
      return;
    }
    const actions = { create: ["생성", "생성했습니다"], update: ["수정", "수정했습니다"], delete: ["삭제", "삭제했습니다"] };
    this.tbody.innerHTML = rows.map((row) => {
      const known = Object.prototype.hasOwnProperty.call(actions, row.action);
      const [label, verb] = known ? actions[row.action] : ["기타", "처리했습니다"];
      const action = known ? row.action : "other";
      const [date, time] = String(row.created_at || "").split(" ");
      const target = String(row.target_label || "").trim();
      const entity = row.entity_label || "대상";
      const title = target || row.summary || "대상명 미기록";
      const description = target ? `${entity} 항목을 ${verb}.${Number(row.target_no) > 0 ? ` 대상 번호 #${Number(row.target_no)}` : ""}` : "기록 당시 저장된 내용을 표시합니다.";
      const authority = Array.isArray(row.authority_lines) && row.authority_lines.length ? `<details class="admin-log-detail"><summary>계정·권한 변경 상세</summary>${row.authority_lines.map(line=>`<span class="admin-log-secondary">${this.esc(line)}</span>`).join('')}</details>` : '';
      return `<tr>
        <td data-label="일시"><time>${this.esc(date || "—")} ${this.esc(time || "")}</time></td>
        <td data-label="작업자 / 접속 IP"><strong>${this.esc(row.actor_uid || "작업자 미기록")}</strong><span class="admin-log-secondary">${this.esc(row.actor_ip || "IP 미기록")}</span></td>
        <td data-label="유형"><span class="admin-log-badge admin-log-badge--${action}">${label}</span></td>
        <td data-label="작업 대상"><strong>${this.esc(entity)}</strong></td>
        <td data-label="작업 내용"><strong>${this.esc(title)}</strong><span class="admin-log-secondary">${this.esc(description)}</span>${authority}</td>
      </tr>`;
    }).join("");
  }

  renderPager(page, pages, total = 0) {
    if (!this.pager) return;
    if (!total) { this.pager.innerHTML = ""; return; }
    const start = Math.max(1, Math.min(page - 2, pages - 4));
    const end = Math.min(pages, start + 4);
    const button = (target, label, name) => `<button type="button" class="no-btn no-btn--normal" data-audit-page="${target}" aria-label="${name}">${label}</button>`;
    let links = page > 1 ? button(1, "처음", "첫 페이지") + button(page - 1, "이전", "이전 페이지") : "";
    for (let number = start; number <= end; number++) {
      links += number === page
        ? `<span class="admin-log-page-current" aria-current="page">${number}</span>`
        : button(number, number, `${number} 페이지`);
    }
    if (page < pages) links += button(page + 1, "다음", "다음 페이지") + button(pages, "마지막", "마지막 페이지");
    this.pager.innerHTML = links;
  }

  esc(value) {
    return String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
}
