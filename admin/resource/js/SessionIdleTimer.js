export class SessionIdleTimer {
  constructor(element) {
    this.element = element;
    this.output = element.querySelector("[data-session-countdown]");
    this.timeout = Math.max(1, Number(element.dataset.sessionTimeout || element.dataset.seconds || 1800));
    this.activityUrl = element.dataset.activityUrl;
    this.logoutUrl = element.dataset.logoutUrl;
    this.deadline = Date.now() + this.timeout * 1000;
    this.lastPing = 0;
    this.expired = false;
    this.pending = false;
  }
  init() {
    ["pointerdown", "keydown", "touchstart", "scroll"].forEach(event =>
      window.addEventListener(event, () => this.onActivity(), {passive: true}));
    document.addEventListener("visibilitychange", () => { if (!document.hidden) this.sync(false); });
    this.render();
    this.sync(false);
    this.interval = window.setInterval(() => this.render(), 1000);
    this.poll = window.setInterval(() => this.sync(false), 15000);
  }
  onActivity() {
    if (this.expired) return;
    if (Date.now() - this.lastPing >= 30000) this.sync(true);
    else if (!this.trailing) this.trailing = window.setTimeout(() => {
      this.trailing = null;
      this.sync(true);
    }, 30000 - (Date.now() - this.lastPing));
  }
  async sync(activity) {
    if (this.expired || this.pending) return;
    this.pending = true;
    if (activity) this.lastPing = Date.now();
    try {
      const response = await fetch(this.activityUrl, {
        method: activity ? "POST" : "GET", credentials: "same-origin", cache: "no-store",
        headers: {"X-Requested-With": "XMLHttpRequest", "X-Loading-Silent": "1",
          "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')?.content || ""}
      });
      if (response.status === 401) { this.expire(); return; }
      const data = await response.json();
      if (response.status === 403 && data.redirect) {
        if (new URL(data.redirect, location.href).origin === location.origin) window.location.assign(data.redirect);
        return;
      }
      if (!response.ok) throw new Error("Session status unavailable");
      const remaining = Number(data.expiresIn ?? data.remaining);
      if (!Number.isFinite(remaining) || remaining < 0) throw new Error("Invalid session status");
      this.deadline = Date.now() + remaining * 1000;
      this.element.removeAttribute("data-connection-error");
      this.element.title = "마지막 활동 후 자동 로그아웃까지 남은 시간";
      if (!remaining) this.expire();
    } catch (_) {
      this.element.dataset.connectionError = "true";
      this.element.title = "서버 연결을 확인할 수 없습니다. 세션은 연장되지 않았습니다.";
    } finally { this.pending = false; }
  }
  render() {
    const remaining = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));
    this.output.textContent = `${String(Math.floor(remaining / 60)).padStart(2, "0")}:${String(remaining % 60).padStart(2, "0")}`;
    this.element.classList.toggle("is-warning", remaining <= 300);
    // Server status is authoritative: another tab may have refreshed the session.
  }
  expire() {
    if (this.expired) return;
    this.expired = true;
    window.clearInterval(this.interval);
    window.clearInterval(this.poll);
    window.clearTimeout(this.trailing);
    window.location.assign(this.logoutUrl);
  }
}
