/**
 * What's ON 목록 페이지 관리 클래스
 */
class WhatsOnList {
  constructor() {
    this.state = {
      venue: "all",
      status: "all",
      genre: "all",
      year: "all",
      search: "",
      page: 1,
    };

    this.elements = {
      list: document.getElementById("whatsonList"),
      count: document.getElementById("whatsonCount"),
      pagination: document.getElementById("whatsonPagination"),
      searchInput: document.getElementById("whatsonSearchInput"),
      searchBtn: document.getElementById("whatsonSearchBtn"),
      searchReset: document.getElementById("whatsonSearchReset"),
      venueRadios: document.querySelectorAll('input[name="venue"]'),
      statusRadios: document.querySelectorAll('input[name="status"]'),
      genreRadios: document.querySelectorAll('input[name="genre"]'),
      yearMonthInput: document.getElementById("yearMonthInput"),
      yearMonthClear: document.getElementById("yearMonthClear"),
      yearMonthPicker: document.getElementById("yearMonthPicker"),
      yearMonthConfirm: document.getElementById("yearMonthConfirm"),
      yearItems: document.querySelectorAll(".no-filter-date-picker__year-item"),
      monthBtns: document.querySelectorAll(".no-filter-date-picker__month-btn"),
      monthPanel: document.getElementById("monthPanel"),
    };

    // 연도/월 선택 상태 (모달 내부 상태)
    this.pickerState = {
      selectedYear: "all",
      selectedMonths: ["all"],
    };

    this.debounceTimer = null;
    this.init();
  }

  /**
   * 초기화
   */
  async init() {
    console.log("WhatsOnList 초기화 시작");
    console.log("DOM 요소 확인:", {
      list: !!this.elements.list,
      count: !!this.elements.count,
      pagination: !!this.elements.pagination,
      searchInput: !!this.elements.searchInput,
    });

    this.bindEvents();
    this.updateYearMonthInput(); // 초기 input 업데이트
    await this.fetchWorks();
  }

  /**
   * 이벤트 바인딩
   */
  bindEvents() {
    // 검색어 입력 (디바운스)
    this.elements.searchInput.addEventListener("input", () => {
      this.handleSearchDebounce();
    });

    // Enter 키 입력 (디바운스)
    this.elements.searchInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        // 디바운스 타이머가 있으면 취소하고 즉시 실행
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
          this.debounceTimer = null;
        }
        this.handleSearch();
      }
    });

    // 검색 버튼
    this.elements.searchBtn.addEventListener("click", () => {
      // 디바운스 타이머가 있으면 취소하고 즉시 실행
      if (this.debounceTimer) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = null;
      }
      this.handleSearch();
    });

    // 검색 초기화
    this.elements.searchReset.addEventListener("click", () => {
      this.handleReset();
    });

    // 공연장 필터
    this.elements.venueRadios.forEach((radio) => {
      radio.addEventListener("change", () => {
        if (radio.checked) {
          this.state.venue = radio.value;
          this.state.page = 1;
          this.fetchWorks();
        }
      });
    });

    // 진행현황 필터
    this.elements.statusRadios.forEach((radio) => {
      radio.addEventListener("change", () => {
        if (radio.checked) {
          this.state.status = radio.value;
          this.state.page = 1;
          this.fetchWorks();
        }
      });
    });

    // 장르 필터
    this.elements.genreRadios.forEach((radio) => {
      radio.addEventListener("change", () => {
        if (radio.checked) {
          this.state.genre = radio.value;
          this.state.page = 1;
          this.fetchWorks();
        }
      });
    });

    // 연도/월 input 클릭
    if (this.elements.yearMonthInput) {
      this.elements.yearMonthInput.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleYearMonthPicker();
      });
    }

    // 연도/월 초기화 버튼
    if (this.elements.yearMonthClear) {
      this.elements.yearMonthClear.addEventListener("click", (e) => {
        e.stopPropagation();
        this.clearYearMonth();
      });
    }

    // 연도 선택
    if (this.elements.yearItems) {
      this.elements.yearItems.forEach((item) => {
        item.addEventListener("click", () => {
          this.selectYear(item.dataset.year);
        });
      });
    }

    // 월 선택
    if (this.elements.monthBtns) {
      this.elements.monthBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
          this.toggleMonth(btn.dataset.month);
        });
      });
    }

    // 확인 버튼
    if (this.elements.yearMonthConfirm) {
      this.elements.yearMonthConfirm.addEventListener("click", () => {
        this.applyYearMonth();
      });
    }

    // 외부 클릭 시 모달 닫기
    document.addEventListener("click", (e) => {
      if (
        this.elements.yearMonthPicker &&
        !this.elements.yearMonthPicker.contains(e.target) &&
        !this.elements.yearMonthInput.contains(e.target)
      ) {
        this.closeYearMonthPicker();
      }
    });
  }

  /**
   * 검색어 입력 처리 (디바운스)
   */
  handleSearchDebounce() {
    clearTimeout(this.debounceTimer);
    
    const searchValue = this.elements.searchInput.value.trim();
    
    // 검색어가 비어있으면 즉시 실행 (필터 초기화)
    if (searchValue === "") {
      this.handleSearch();
      return;
    }
    
    // 검색어가 있으면 디바운스 적용 (500ms)
    this.debounceTimer = setTimeout(() => {
      this.handleSearch();
    }, 500);
  }

  /**
   * 검색 처리
   */
  handleSearch() {
    this.state.search = this.elements.searchInput.value.trim();
    this.state.page = 1;
    this.fetchWorks();
  }

  /**
   * 초기화
   */
  handleReset() {
    this.state = {
      venue: "all",
      status: "all",
      genre: "all",
      year: "all",
      months: ["all"],
      search: "",
      page: 1,
    };

    this.elements.searchInput.value = "";
    this.elements.venueRadios.forEach((r) => (r.checked = r.value === "all"));
    this.elements.statusRadios.forEach((r) => (r.checked = r.value === "all"));
    this.elements.genreRadios.forEach((r) => (r.checked = r.value === "all"));

    this.clearYearMonth();
    this.fetchWorks();
  }

  /**
   * 연도/월 선택 모달 열기/닫기
   */
  toggleYearMonthPicker() {
    if (!this.elements.yearMonthPicker) return;

    const isOpen = this.elements.yearMonthPicker.style.display !== "none";
    if (isOpen) {
      this.closeYearMonthPicker();
    } else {
      this.openYearMonthPicker();
    }
  }

  /**
   * 연도/월 선택 모달 열기
   */
  openYearMonthPicker() {
    if (!this.elements.yearMonthPicker) return;

    // 현재 상태로 초기화
    this.pickerState.selectedYear = this.state.year;
    this.pickerState.selectedMonths = [...this.state.months];

    // 연도 선택 상태 업데이트
    this.updateYearSelection();

    // 월 패널 표시/숨김
    if (this.pickerState.selectedYear !== "all") {
      if (this.elements.monthPanel) {
        this.elements.monthPanel.style.display = "flex";
      }
      this.updateMonthSelection();
    } else {
      if (this.elements.monthPanel) {
        this.elements.monthPanel.style.display = "none";
      }
    }

    this.elements.yearMonthPicker.style.display = "block";
  }

  /**
   * 연도/월 선택 모달 닫기
   */
  closeYearMonthPicker() {
    if (!this.elements.yearMonthPicker) return;
    this.elements.yearMonthPicker.style.display = "none";
  }

  /**
   * 연도 선택
   */
  selectYear(year) {
    this.pickerState.selectedYear = year;
    this.updateYearSelection();

    // 연도가 '전체'가 아니면 월 패널 표시
    if (year !== "all") {
      if (this.elements.monthPanel) {
        this.elements.monthPanel.style.display = "flex";
      }
      // 월 선택 초기화
      this.pickerState.selectedMonths = ["all"];
      this.updateMonthSelection();
    } else {
      if (this.elements.monthPanel) {
        this.elements.monthPanel.style.display = "none";
      }
      this.pickerState.selectedMonths = ["all"];
    }
  }

  /**
   * 월 토글
   */
  toggleMonth(month) {
    if (month === "all") {
      // '전체' 선택 시 다른 월 모두 해제
      this.pickerState.selectedMonths = ["all"];
    } else {
      // '전체' 제거
      this.pickerState.selectedMonths = this.pickerState.selectedMonths.filter(
        (m) => m !== "all"
      );

      // 해당 월 토글
      const index = this.pickerState.selectedMonths.indexOf(month);
      if (index > -1) {
        this.pickerState.selectedMonths.splice(index, 1);
      } else {
        this.pickerState.selectedMonths.push(month);
      }

      // 아무것도 선택되지 않았으면 '전체' 선택
      if (this.pickerState.selectedMonths.length === 0) {
        this.pickerState.selectedMonths = ["all"];
      }
    }

    this.updateMonthSelection();
  }

  /**
   * 월 선택 상태 업데이트
   */
  updateMonthSelection() {
    if (!this.elements.monthBtns) return;

    this.elements.monthBtns.forEach((btn) => {
      const month = btn.dataset.month;
      if (this.pickerState.selectedMonths.includes(month)) {
        btn.classList.add("is-selected");
      } else {
        btn.classList.remove("is-selected");
      }
    });
  }


  /**
   * 연도 선택 상태 업데이트
   */
  updateYearSelection() {
    if (!this.elements.yearItems) return;

    this.elements.yearItems.forEach((item) => {
      if (item.dataset.year === this.pickerState.selectedYear) {
        item.classList.add("is-selected");
      } else {
        item.classList.remove("is-selected");
      }
    });
  }


  /**
   * 연도/월 적용
   */
  applyYearMonth() {
    this.state.year = this.pickerState.selectedYear;
    this.state.months = [...this.pickerState.selectedMonths];
    this.state.page = 1;

    // input 업데이트
    this.updateYearMonthInput();

    // 모달 닫기
    this.closeYearMonthPicker();

    // 필터 적용
    this.fetchWorks();
  }

  /**
   * 연도/월 input 업데이트
   */
  updateYearMonthInput() {
    if (!this.elements.yearMonthInput) return;

    if (this.state.year === "all") {
      this.elements.yearMonthInput.value = "";
      if (this.elements.yearMonthClear) {
        this.elements.yearMonthClear.style.display = "none";
      }
    } else {
      let displayText = `${this.state.year}년`;

      if (this.state.months.length > 0 && !this.state.months.includes("all")) {
        const monthTexts = this.state.months
          .sort((a, b) => parseInt(a) - parseInt(b))
          .map((m) => `${m}월`)
          .join(", ");
        displayText += ` ${monthTexts}`;
      } else if (this.state.months.includes("all")) {
        displayText += " 전체";
      }

      this.elements.yearMonthInput.value = displayText;
      if (this.elements.yearMonthClear) {
        this.elements.yearMonthClear.style.display = "flex";
      }
    }
  }

  /**
   * 연도/월 초기화
   */
  clearYearMonth() {
    this.state.year = "all";
    this.state.months = ["all"];
    this.pickerState.selectedYear = "all";
    this.pickerState.selectedMonths = ["all"];

    this.updateYearMonthInput();

    if (this.elements.yearMonthPicker) {
      this.elements.yearMonthPicker.style.display = "none";
    }
  }

  /**
   * 데이터 가져오기
   */
  async fetchWorks() {
    // 로딩 상태 표시
    this.showLoading();

    try {
      const params = new URLSearchParams({
        venue: this.state.venue,
        status: this.state.status,
        genre: this.state.genre,
        year: this.state.year,
        months: this.state.months.join(","),
        q: this.state.search,
        page: this.state.page,
      });

      const url = `/api/get_works.php?${params}`;
      console.log("API 요청 URL:", url);
      console.log("요청 파라미터:", {
        venue: this.state.venue,
        status: this.state.status,
        genre: this.state.genre,
        years: this.state.years,
        search: this.state.search,
        page: this.state.page,
      });

      const response = await fetch(url);
      console.log("API 응답 상태:", response.status, response.statusText);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log("API 응답 데이터:", data);
      console.log("응답 데이터 상세:", {
        success: data.success,
        dataCount: data.data?.length || 0,
        total: data.pagination?.total || 0,
        currentPage: data.pagination?.currentPage || 0,
      });

      if (data.success) {
        console.log("데이터 렌더링 시작, 작품 수:", data.data?.length || 0);
        this.renderWorks(data.data);
        this.renderPagination(data.pagination);
        this.updateCount(data.pagination.total);
      } else {
        throw new Error(data.message || "데이터를 불러올 수 없습니다.");
      }
    } catch (error) {
      console.error("Error fetching works:", error);
      console.error("Error details:", {
        message: error.message,
        stack: error.stack,
      });
      // 사용자에게는 친화적인 메시지 표시
      this.renderError("공연이 없습니다.");
    }
  }

  /**
   * 로딩 상태 표시
   */
  showLoading() {
    if (this.elements.list) {
      this.elements.list.innerHTML = `
        <li class="no-sub-whatson-item" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
          <span class="f-body-2 --regular">데이터를 불러오는 중...</span>
        </li>
      `;
    }
    if (this.elements.count) {
      this.elements.count.innerHTML = `총 <strong>-</strong>건`;
    }
    if (this.elements.pagination) {
      this.elements.pagination.innerHTML = "";
    }
  }

  /**
   * 작품 목록 렌더링
   */
  renderWorks(works) {
    if (!works || works.length === 0) {
      this.elements.list.innerHTML = `
                <li class="no-sub-whatson-item" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                    <span class="f-body-2 --regular">공연이 없습니다.</span>
                </li>
            `;
      return;
    }

    this.elements.list.innerHTML = works
      .map((work) => this.createWorkItem(work))
      .join("");
  }

  /**
   * 작품 아이템 생성
   */
  createWorkItem(work) {
    const thumbnail =
      work.thumb_image || "/resource/images/works/poster_img_1.png";
    const periodBadge =
      work.start_date_formatted && work.end_date_formatted
        ? `<div class="--badge">${work.start_date_formatted} – ${work.end_date_formatted}</div>`
        : "";
    const venueBadge = work.venue_name
      ? `<div class="--badge">${this.escapeHtml(work.venue_name)}</div>`
      : "";

    return `
            <li class="no-sub-whatson-item">
                <a href="/whatson/view?id=${work.id}">
                    <figure class="no-sub-whatson-item-img">
                        <img src="${thumbnail}" alt="${this.escapeHtml(
      work.title
    )}">
                    </figure>
                    <div class="no-sub-whatson-item-content">
                        <h3 class="f-heading-5 --bold">
                            ${this.escapeHtml(work.title)}
                        </h3>
                        <div class="no-sub-whatson-item-content__info">
                            ${periodBadge}
                            ${venueBadge}
                        </div>
                    </div>
                </a>
            </li>
        `;
  }

  /**
   * 페이지네이션 렌더링
   */
  renderPagination(pagination) {
    if (pagination.lastPage <= 1) {
      this.elements.pagination.innerHTML = "";
      return;
    }

    const { currentPage, lastPage } = pagination;
    const maxVisible = 5;
    const halfVisible = Math.floor(maxVisible / 2);

    let startPage = Math.max(1, currentPage - halfVisible);
    let endPage = Math.min(lastPage, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    let html = '<div class="no-pagination">';

    // 이전 페이지
    if (currentPage > 1) {
      html += `<button class="no-pagination__btn --prev" data-page="${
        currentPage - 1
      }">
                        <i class="fa-solid fa-chevron-left"></i>
                     </button>`;
    }

    // 페이지 번호
    for (let i = startPage; i <= endPage; i++) {
      const activeClass = i === currentPage ? "is-active" : "";
      html += `<button class="no-pagination__btn ${activeClass}" data-page="${i}">${i}</button>`;
    }

    // 다음 페이지
    if (currentPage < lastPage) {
      html += `<button class="no-pagination__btn --next" data-page="${
        currentPage + 1
      }">
                        <i class="fa-solid fa-chevron-right"></i>
                     </button>`;
    }

    html += "</div>";
    this.elements.pagination.innerHTML = html;

    // 페이지네이션 버튼 이벤트
    this.elements.pagination
      .querySelectorAll(".no-pagination__btn")
      .forEach((btn) => {
        btn.addEventListener("click", () => {
          const page = parseInt(btn.dataset.page);
          if (page && page !== this.state.page) {
            this.state.page = page;
            this.fetchWorks();
            window.scrollTo({ top: 0, behavior: "smooth" });
          }
        });
      });
  }

  /**
   * 카운트 업데이트
   */
  updateCount(total) {
    this.elements.count.innerHTML = `총 <strong>${total.toLocaleString()}</strong>건`;
  }

  /**
   * 에러 렌더링
   */
  renderError(message) {
    this.elements.list.innerHTML = `
            <li class="no-sub-whatson-item" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                <span class="f-body-2 --regular">${message}</span>
            </li>
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
  console.log("DOMContentLoaded 이벤트 발생");
  try {
    new WhatsOnList();
  } catch (error) {
    console.error("WhatsOnList 초기화 오류:", error);
  }
});
