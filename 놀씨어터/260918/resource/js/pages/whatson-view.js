/**
 * What's ON 상세 페이지 관리 클래스
 */
class WhatsOnView {
  constructor() {
    this.workId = this.getWorkId();
    this.container = document.getElementById("whatsonViewContent");
    this.init();
  }

  /**
   * 초기화
   */
  async init() {
    if (!this.workId) {
      this.renderInvalidAccess();
      return;
    }

    await this.fetchWork();
  }

  /**
   * URL에서 Work ID 가져오기
   */
  getWorkId() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get("id");
  }

  /**
   * 데이터 가져오기
   */
  async fetchWork() {
    try {
      const response = await fetch(`/api/get_work.php?id=${this.workId}`);
      const result = await response.json();

      if (result.success && result.data) {
        this.renderWork(result.data);
        this.bindEvents();
      } else {
        throw new Error(result.message || "데이터를 불러올 수 없습니다.");
      }
    } catch (error) {
      console.error("Error fetching work:", error);
      this.renderError("공연 정보를 불러오는 중 오류가 발생했습니다.");
    }
  }

  /**
   * 공연 정보 렌더링
   */
  renderWork(work) {
    const metaItems = this.buildMetaItems(work);
    const contentHtml = this.buildContentHtml(work);
    const longPosterHtml = this.buildLongPosterHtml(work);
    const posterImage =
      work.thumb_image || "/resource/images/works/poster_img_1.png";
    const ticketHtml = this.buildTicketHtml(work);
    const seatPricesHtml = this.buildSeatPricesHtml(work);

    this.container.innerHTML = `
            <div class="no-sub-whatson-view__head">
                <a href="/whatson" class="no-sub-whatson-view__back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>

            <div class="no-sub-whatson-view__layout">
                <div class="no-sub-whatson-view__content">
                    ${this.buildHeaderHtml(work)}
                    <div class="no-sub-whatson-view__meta">
                        ${metaItems}
                    </div>
                    ${contentHtml}
                    ${longPosterHtml}
                </div>

                <aside class="no-sub-whatson-view__sidebar">
                    <div class="no-sub-whatson-view__sidebar-inner">
                        <div class="no-sub-whatson-view__poster-sticky">
                            <figure class="no-sub-whatson-view__poster-img">
                                <img src="${posterImage}" alt="${this.escapeHtml(
      work.title
    )}">
                            </figure>
                        </div>
                        ${ticketHtml}
                    </div>
                    ${seatPricesHtml}
                </aside>
            </div>
        `;
  }

  /**
   * 헤더 HTML 빌드
   */
  buildHeaderHtml(work) {
    const subtitleHtml = work.subtitle
      ? `
            <h2 class="no-sub-whatson-view__subtitle f-heading-5 --regular">
                ${this.escapeHtml(work.subtitle)}
            </h2>
        `
      : "";

    return `
            <div class="no-sub-whatson-view__header">
                <h1 class="no-sub-whatson-view__title f-heading-2 --bold">
                    ${this.escapeHtml(work.title)}
                </h1>
                ${subtitleHtml}
            </div>
        `;
  }

  /**
   * 메타 정보 빌드
   */
  buildMetaItems(work) {
    const items = [];

    if (work.genre_name) items.push({ label: "장르", value: work.genre_name });
    if (work.venue_name)
      items.push({ label: "공연장", value: work.venue_name });
    if (work.period) items.push({ label: "공연기간", value: work.period });
    if (work.running_time)
      items.push({ label: "러닝타임", value: work.running_time });
    if (work.age_rating)
      items.push({ label: "관람연령", value: work.age_rating });
    if (work.inquiry) items.push({ label: "문의", value: work.inquiry });
    if (work.note) items.push({ label: "비고", value: work.note });

    return items
      .map(
        (item) => `
            <div class="no-sub-whatson-view__meta-item">
                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">${this.escapeHtml(
                  item.label
                )}</span>
                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">${this.escapeHtml(
                  item.value
                )}</span>
            </div>
        `
      )
      .join("");
  }

  /**
   * 내용 HTML 빌드
   */
  buildContentHtml(work) {
    if (!work.content_html) return "";

    return `
            <div class="no-sub-whatson-view__contents">
                <h3 class="no-sub-whatson-view__contents-title f-heading-5 --bold">내용</h3>
                <div class="no-sub-whatson-view__contents-content">
                    ${work.content_html}
                </div>
            </div>
        `;
  }

  /**
   * 긴 포스터 HTML 빌드
   */
  buildLongPosterHtml(work) {
    if (!work.poster_long_html) return "";

    return `
            <div class="no-sub-whatson-view__long-poster-wrapper">
                <div class="no-sub-whatson-view__long-poster" id="longPoster">
                    <div class="no-sub-whatson-view__long-poster-content">
                        ${work.poster_long_html}
                    </div>
                </div>
                <button type="button" class="no-sub-whatson-view__long-poster-toggle" id="longPosterToggle"
                    aria-label="포스터 펼치기">
                    <span class="no-sub-whatson-view__long-poster-toggle-text">펼치기</span>
                    <i class="fa-regular fa-chevron-down"></i>
                </button>
            </div>
        `;
  }

  /**
   * 티켓 HTML 빌드
   */
  buildTicketHtml(work) {
    if (!work.ticket_url) return "";

    return `
            <div class="no-sub-whatson-view__ticket">
                <a href="${this.escapeHtml(
                  work.ticket_url
                )}" target="_blank" rel="noopener noreferrer"
                    class="no-sub-whatson-view__ticket-link">
                    <span>예약하기</span>
                    <i class="fa-regular fa-arrow-right"></i>
                </a>
            </div>
        `;
  }

  /**
   * 좌석 가격 HTML 빌드
   */
  buildSeatPricesHtml(work) {
    if (!work.seat_prices) return "";

    return `
            <div class="no-sub-whatson-view__price">
                <h3 class="no-sub-whatson-view__price-title f-heading-5 --bold">좌석별 가격</h3>
                <div class="no-sub-whatson-view__price-content">
                    ${work.seat_prices}
                </div>
            </div>
        `;
  }

  /**
   * 이벤트 바인딩
   */
  bindEvents() {
    const longPosterToggle = document.getElementById("longPosterToggle");
    if (longPosterToggle) {
      longPosterToggle.addEventListener("click", () => {
        this.handleLongPosterToggle();
      });
    }
  }

  /**
   * 긴 포스터 펼치기/접기 처리
   */
  handleLongPosterToggle() {
    const longPoster = document.getElementById("longPoster");
    const toggleBtn = document.getElementById("longPosterToggle");
    const toggleText = toggleBtn.querySelector(
      ".no-sub-whatson-view__long-poster-toggle-text"
    );
    const toggleIcon = toggleBtn.querySelector("i");

    if (longPoster.classList.contains("is-expanded")) {
      longPoster.classList.remove("is-expanded");
      toggleText.textContent = "펼치기";
      toggleIcon.classList.replace("fa-chevron-up", "fa-chevron-down");
    } else {
      longPoster.classList.add("is-expanded");
      toggleText.textContent = "접기";
      toggleIcon.classList.replace("fa-chevron-down", "fa-chevron-up");
    }
  }

  /**
   * 잘못된 접근 렌더링
   */
  renderInvalidAccess() {
    this.container.innerHTML = `
            <div style="text-align: center; padding: 4rem;">
                <p class="f-body-2 --regular">잘못된 접근입니다.</p>
                <a href="/whatson" class="no-btn no-btn--main" style="margin-top: 2rem;">목록으로</a>
            </div>
        `;
  }

  /**
   * 에러 렌더링
   */
  renderError(message) {
    this.container.innerHTML = `
            <div style="text-align: center; padding: 4rem;">
                <p class="f-body-2 --regular">${message}</p>
                <a href="/whatson" class="no-btn no-btn--main" style="margin-top: 2rem;">목록으로</a>
            </div>
        `;
  }

  /**
   * HTML 이스케이프
   */
  escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// 페이지 로드 시 초기화
document.addEventListener("DOMContentLoaded", () => {
  new WhatsOnView();
});

