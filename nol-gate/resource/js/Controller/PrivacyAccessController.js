import { AuditController } from "./AuditController.js?v=20261119";
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
      const action = ["view", "download", "create", "update", "delete"].includes(row.action) ? row.action : "other";
      const label = row.action_label || "기타";
      const [date, time] = String(row.created_at || "").split(" ");
      const entity = Object.prototype.hasOwnProperty.call(entities, row.entity) ? entities[row.entity] : "기타 대상";
      const reason = String(row.reason || "").trim();
      const reasonText = reason || (download ? "다운로드 사유 미기록" : "다운로드 사유 해당 없음");
      return `<tr>
        <td data-label="일시"><time>${this.esc(date || "—")} ${this.esc(time || "")}</time></td>
        <td data-label="작업자 / 접속 IP"><strong>${this.esc(row.actor_uid || "작업자 미기록")}</strong><span class="admin-log-secondary">${this.esc(row.actor_ip || "IP 미기록")}</span></td>
        <td data-label="유형"><span class="admin-log-badge admin-log-badge--${action}">${this.esc(label)}</span></td>
        <td data-label="접근 대상 / 정보주체"><strong>${this.esc(entity)}</strong><span class="admin-log-secondary">${this.esc(row.subject_label || "정보주체 미기록")}${Number(row.target_no) > 0 ? ` · #${Number(row.target_no)}` : ""}</span></td>
        <td data-label="수행업무 / 다운로드 사유"><p class="admin-log-description">${this.esc(row.task || "수행업무 미기록")}</p><span class="admin-log-secondary ${download && !reason ? "audit-missing" : ""}">${reason ? "사유: " : ""}${this.esc(reasonText)}</span></td>
      </tr>`;
    }).join("");
  }
}
