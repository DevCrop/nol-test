import { AuditController } from "./AuditController.js?v=20261020";
import { API } from "../core/apiRoutes.js";

// Shared read-only log filters, request ordering and pagination; privacy-specific rows.
export class PrivacyAccessController extends AuditController {
  constructor() {
    super({ rows: "#privacy-access-rows", pager: "#privacy-access-pager",
      total: "#privacy-access-total", endpoint: API.PRIVACY_ACCESS, title: "개인정보 접속기록" });
  }

  renderRows(rows) {
    if (!this.tbody) return;
    if (!rows.length) {
      this.message("조건에 맞는 개인정보 접속기록이 없습니다.", "검색어 또는 필터를 변경하거나 초기화해 주세요.");
      return;
    }
    const entities = { admin_account: "관리자 계정", inquiry: "대관신청" };
    this.tbody.innerHTML = rows.map(row => {
      const download = row.action === "download";
      const view = row.action === "view";
      const label = download ? "다운로드" : view ? "열람" : "기타";
      const badge = download ? "download" : view ? "view" : "other";
      const [date, time] = String(row.created_at || "").split(" ");
      const entity = Object.prototype.hasOwnProperty.call(entities, row.entity) ? entities[row.entity] : "기타 대상";
      const reason = String(row.reason || "").trim();
      const reasonText = reason || (download ? "다운로드 사유 미기록" : "다운로드 사유 해당 없음");
      return `<tr>
        <td><span class="audit-date">${this.esc(date || "—")}</span><span class="audit-sub">${this.esc(time || "")}</span></td>
        <td><span class="audit-actor">${this.esc(row.actor_uid || "작업자 미기록")}</span><span class="audit-sub audit-ip">${this.esc(row.actor_ip || "IP 미기록")}</span></td>
        <td><span class="no-badge audit-badge-${badge}"><span aria-hidden="true">${download ? "↓" : view ? "◉" : "·"}</span> ${label}</span></td>
        <td><span class="audit-entity">${this.esc(entity)}</span><span class="audit-sub">${this.esc(row.subject_label || "정보주체 미기록")}${Number(row.target_no) > 0 ? ` · #${Number(row.target_no)}` : ""}</span></td>
        <td class="no-td-title"><span class="audit-actor">${this.esc(row.task || "수행업무 미기록")}</span><span class="audit-sub ${download && !reason ? "audit-missing" : ""}">${reason ? "사유: " : ""}${this.esc(reasonText)}</span></td>
      </tr>`;
    }).join("");
  }
}
