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
    const jq = globalThis.jQuery;
    if (jq?.fn?.selectmenu) {
      [this.entity, this.action].filter(Boolean).forEach(select => {
        jq(select).selectmenu({ width: "100%", change: search });
      });
    }
    document.querySelector("#audit-reset")?.addEventListener("click", () => {
      this.form?.reset();
      if (jq?.fn?.selectmenu) [this.entity, this.action].filter(Boolean).forEach(select => jq(select).selectmenu("refresh"));
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
      if (this.total) this.total.textContent = `검색 결과 ${total.toLocaleString("ko-KR")}건`;
      this.renderPager(this.page, Number(res.pages) || 1, total, Number(res.perpage) || 10);
    } catch (err) {
      if (request !== this.request) return;
      this.message(`${this.title} 조회에 실패했습니다.`, err.message || "잠시 후 다시 검색해 주세요.");
      if (this.total) this.total.textContent = "조회 실패";
    } finally {
      if (request === this.request) this.tbody?.setAttribute("aria-busy", "false");
    }
  }

  message(title, description) {
    if (this.tbody) this.tbody.innerHTML = `<tr><td colspan="5" class="audit-empty"><strong>${this.esc(title)}</strong>${this.esc(description)}</td></tr>`;
  }

  renderRows(rows) {
    if (!this.tbody) return;
    if (!rows.length) {
      this.message("조건에 맞는 작업이력이 없습니다.", "검색어 또는 필터를 변경하거나 초기화해 주세요.");
      return;
    }
    const actions = { create: ["생성", "+", "생성했습니다"], update: ["수정", "↻", "수정했습니다"], delete: ["삭제", "−", "삭제했습니다"] };
    this.tbody.innerHTML = rows.map((row) => {
      const known = Object.prototype.hasOwnProperty.call(actions, row.action);
      const [label, icon, verb] = known ? actions[row.action] : ["기타", "·", "처리했습니다"];
      const action = known ? row.action : "other";
      const [date, time] = String(row.created_at || "").split(" ");
      const target = String(row.target_label || "").trim();
      const entity = row.entity_label || "대상";
      const title = target || row.summary || "대상명 미기록";
      const description = target ? `${entity} 항목을 ${verb}.${Number(row.target_no) > 0 ? ` 대상 번호 #${Number(row.target_no)}` : ""}` : "기록 당시 저장된 내용을 표시합니다.";
      return `<tr>
        <td><span class="audit-date">${this.esc(date || "—")}</span><span class="audit-sub">${this.esc(time || "")}</span></td>
        <td><span class="audit-actor">${this.esc(row.actor_uid || "작업자 미기록")}</span><span class="audit-sub audit-ip">${this.esc(row.actor_ip || "IP 미기록")}</span></td>
        <td><span class="no-badge audit-badge-${action}"><span aria-hidden="true">${icon}</span> ${label}</span></td>
        <td><span class="audit-entity">${this.esc(entity)}</span></td>
        <td class="no-td-title"><span class="audit-actor">${this.esc(title)}</span><span class="audit-sub">${this.esc(description)}</span></td>
      </tr>`;
    }).join("");
  }

  renderPager(page, pages, total = 0, perpage = 10) {
    if (!this.pager) return;
    if (!total) { this.pager.innerHTML = ""; return; }
    const start = Math.max(1, Math.min(page - 2, pages - 4));
    const end = Math.min(pages, start + 4);
    const icons = { 처음: "bx-chevrons-left", 이전: "bx-chevron-left", 다음: "bx-chevron-right", 끝: "bx-chevrons-right" };
    const button = (target, label, disabled, current = false) => `<li class="no-page-item"><button type="button" class="no-page-link${current ? " active" : ""}" data-audit-page="${target}" ${disabled ? "disabled" : ""} ${current ? 'aria-current="page"' : ""} aria-label="${this.esc(label)}">${Object.prototype.hasOwnProperty.call(icons, label) ? `<i class="bx ${icons[label]}" aria-hidden="true"></i>` : this.esc(label)}</button></li>`;
    let links = button(1, "처음", page === 1) + button(Math.max(1, page - 1), "이전", page === 1);
    for (let i = start; i <= end; i++) links += button(i, String(i), false, i === page);
    links += button(Math.min(pages, page + 1), "다음", page === pages) + button(pages, "끝", page === pages);
    this.pager.innerHTML = `<div class="no-pagination"><ul class="no-page-list">${links}</ul></div><p class="audit-page-summary">전체 ${total.toLocaleString("ko-KR")}건 중 ${(page - 1) * perpage + 1}–${Math.min(page * perpage, total)}건</p>`;
  }

  esc(value) {
    return String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
}
