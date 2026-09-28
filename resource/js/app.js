import { ThemeManager } from "./ui/ThemeManager.js";
import { TabManager } from "./ui/TabManager.js";
import { ScrollAnimation } from "./ui/ScrollAnimation.js";

// 알림 모달 함수
function showAlertModal(title, message, type = "info", errorArray = null) {
  const modal = document.getElementById("alert-modal");
  const modalTitle = document.getElementById("alert-modal-title");
  const modalMessage = document.getElementById("alert-modal-message");
  const modalConfirm = document.getElementById("alert-modal-confirm");
  const modalClose = modal?.querySelector(".no-alert-modal__close");
  const modalBackdrop = modal?.querySelector(".no-alert-modal__backdrop");

  if (!modal || !modalTitle || !modalMessage || !modalConfirm) return;

  // 제목 설정
  modalTitle.textContent = title;

  // 메시지 설정
  modalMessage.innerHTML = "";
  modalMessage.className = "no-alert-modal__message";

  // 메시지 타입에 따라 처리
  if (type === "error") {
    // 에러 배열이 있으면 직접 사용
    let errorList = [];

    if (errorArray && Array.isArray(errorArray) && errorArray.length > 0) {
      errorList = errorArray;
    } else {
      // 에러 메시지를 리스트로 파싱
      // 서버에서 보내는 형식: "메시지1 메시지2 메시지3" (공백으로 구분)

      // 공백으로 구분하되, 문장 단위로 분리
      // "을 입력해주세요." 또는 "을 선택해주세요." 같은 패턴으로 끝나는 문장 단위로 분리
      const sentences = message.split(
        /(?<=을 입력해주세요\.|을 선택해주세요\.|입니다\.|니다\.)/
      );

      sentences.forEach((sentence) => {
        const trimmed = sentence.trim();
        if (trimmed && trimmed.length > 0) {
          errorList.push(trimmed);
        }
      });

      // 분리가 안 되면 공백으로 분리 시도
      if (errorList.length <= 1) {
        const spaceSplit = message
          .split(/\s+/)
          .filter((msg) => msg.trim().length > 0);
        if (spaceSplit.length > 1) {
          errorList.length = 0;
          errorList.push(...spaceSplit);
        }
      }
    }

    // 한 문장으로 보이면 그대로 표시
    if (errorList.length <= 1) {
      modalMessage.textContent = message;
    } else {
      // 여러 메시지면 리스트로 표시
      modalMessage.classList.add("no-alert-modal__message--error-list");
      const ul = document.createElement("ul");
      errorList.forEach((errorMsg) => {
        const li = document.createElement("li");
        li.textContent = errorMsg;
        ul.appendChild(li);
      });
      modalMessage.appendChild(ul);
    }
  } else if (type === "success") {
    modalMessage.classList.add("no-alert-modal__message--success");
    modalMessage.textContent = message;
  } else {
    modalMessage.textContent = message;
  }

  // 모달 표시
  modal.setAttribute("aria-hidden", "false");
  document.body.style.overflow = "hidden";

  // 닫기 함수
  const closeModal = () => {
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  // 이벤트 리스너 (기존 리스너 제거 후 추가)
  const handleClose = () => closeModal();
  modalConfirm.onclick = handleClose;
  modalClose?.addEventListener("click", handleClose);
  modalBackdrop?.addEventListener("click", handleClose);

  // ESC 키로 닫기
  const handleEsc = (e) => {
    if (e.key === "Escape") {
      closeModal();
      document.removeEventListener("keydown", handleEsc);
    }
  };
  document.addEventListener("keydown", handleEsc);
}

// 모바일 감지 함수 (화면 너비 기준)
function isMobileDevice() {
  // 화면 너비 기준 (1024px 이하)
  return window.innerWidth <= 1024;
}

$(document).ready(function () {
  gsap.registerPlugin(ScrollTrigger);
  const themeManager = new ThemeManager();
  window.themeManager = themeManager; // 전역으로 노출
  const scrollAnimation = new ScrollAnimation();
  initHeader();

  // 모든 디바이스에서 Lenis 초기화 (모바일 최적화 포함)
  const lenis = initLenis();

  initSwipers();
  formHandler();
  initBaseFaq();
  initMarquee();
  initBreadcrumb();
  new TabManager(".no-sub-ui", lenis);
  initProcedure();
  initWhatsonView();
  initWhatsonFilter();
  initWhatsonList();
  initTheaterNolRolling();
  initFloatingButton(lenis);

  // Lord Icon 색상 초기화 (테마에 따라)
  setTimeout(() => {
    const theme = themeManager.getCurrentTheme();
    themeManager.updateLordIcons(theme);
  }, 1000);
});

function initBaseFaq() {
  const lists = document.querySelectorAll(".no-faq__list");
  if (!lists.length) return;

  lists.forEach((list) => {
    const items = list.querySelectorAll(".no-faq__item");
    if (!items.length) return;

    items.forEach((item) => {
      const head = item.querySelector(".no-faq__head");
      if (!head) return;

      head.addEventListener("click", () => {
        const isActive = item.classList.contains("--active");

        // 다른 항목 닫기
        items.forEach((sibling) => {
          sibling.classList.remove("--active");
          const siblingHead = sibling.querySelector(".no-faq__head");
          if (siblingHead) {
            siblingHead.setAttribute("aria-expanded", "false");
          }
        });

        // 현재 항목 토글
        if (!isActive) {
          item.classList.add("--active");
          head.setAttribute("aria-expanded", "true");
        } else {
          head.setAttribute("aria-expanded", "false");
        }
      });
    });
  });
}

function formHandler() {
  const form = document.querySelector("form");
  if (!form) {
    return;
  }

  // 캡차 새로고침
  const captchaRefresh = document.getElementById("captcha-refresh");
  const captchaImage = document.getElementById("captcha-image");

  if (captchaRefresh && captchaImage) {
    captchaRefresh.addEventListener("click", () => {
      captchaImage.src = "/captcha/default.php?t=" + new Date().getTime();
    });
  }

  // 파일 업로드 처리
  initFileUpload();

  // 대관 신청 폼 처리
  const rentalForm = document.getElementById("rental-apply-form");
  if (rentalForm) {
    rentalForm.addEventListener("submit", handleRentalFormSubmit);
  }
}

// 대관 신청 폼 제출 처리
async function handleRentalFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const submitButton = form.querySelector('button[type="submit"]');
  const originalText =
    submitButton.querySelector(".no-button__text").textContent;
  const captchaImage = document.getElementById("captcha-image");

  // 제출 버튼 비활성화
  submitButton.disabled = true;
  submitButton.querySelector(".no-button__text").textContent = "처리 중...";

  try {
    const formData = new FormData(form);

    // 파일 업로드 처리 (전역 files 배열 사용)
    if (
      typeof window.rentalFormFiles !== "undefined" &&
      window.rentalFormFiles.length > 0
    ) {
      window.rentalFormFiles.forEach((file) => {
        formData.append("files[]", file);
      });
    }

    const response = await fetch("/module/request.rental.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      form.reset();
      // 파일 리스트 초기화
      const fileList = document.getElementById("file-list");
      if (fileList) {
        fileList.innerHTML = "";
      }
      // 파일 배열 초기화
      if (typeof window.rentalFormFiles !== "undefined") {
        window.rentalFormFiles = [];
      }
      // 캡차 새로고침
      if (captchaImage) {
        captchaImage.src = "/captcha/default.php?t=" + new Date().getTime();
      }
    } else {
      // errors 배열이 있으면 사용, 없으면 message 사용
      const errorMessage =
        result.errors && result.errors.length > 0
          ? result.errors.join("\n")
          : result.message || "신청 처리 중 오류가 발생했습니다.";
      alert(errorMessage);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("신청 처리 중 오류가 발생했습니다. 다시 시도해주세요.");
  } finally {
    // 제출 버튼 활성화
    submitButton.disabled = false;
    submitButton.querySelector(".no-button__text").textContent = originalText;
  }
}

// 파일 업로드 초기화
function initFileUpload() {
  const dropzone = document.getElementById("file-dropzone");
  const fileInput = document.getElementById("file-input");
  const fileList = document.getElementById("file-list");

  if (!dropzone || !fileInput || !fileList) return;

  // 전역 파일 배열 (대관 신청 폼에서 사용)
  if (typeof window.rentalFormFiles === "undefined") {
    window.rentalFormFiles = [];
  }
  const files = window.rentalFormFiles;
  const MAX_FILES = 5;
  const MAX_SIZE = 20 * 1024 * 1024; // 20MB
  const ALLOWED_EXTENSIONS = [
    "zip",
    "xls",
    "xlsx",
    "pdf",
    "ppt",
    "pptx",
    "doc",
    "docx",
    "hwp",
  ];

  // 파일 유효성 검사
  function validateFile(file) {
    if (files.length >= MAX_FILES) {
      alert(`최대 ${MAX_FILES}개까지 첨부 가능합니다.`);
      return false;
    }

    if (file.size > MAX_SIZE) {
      alert("파일당 20MB 이하만 첨부 가능합니다.");
      return false;
    }

    const extension = file.name.split(".").pop().toLowerCase();
    if (!ALLOWED_EXTENSIONS.includes(extension)) {
      alert(
        "zip, xls, xlsx, pdf, ppt, pptx, doc, docx, hwp 확장자만 첨부 가능합니다."
      );
      return false;
    }

    return true;
  }

  // 파일 추가
  function addFile(file) {
    if (!validateFile(file)) return;

    files.push(file);
    renderFileList();
  }

  // 파일 리스트 렌더링
  function renderFileList() {
    fileList.innerHTML = "";

    files.forEach((file, index) => {
      const li = document.createElement("li");
      li.className = "no-form-file-upload__item";

      const size = formatFileSize(file.size);

      li.innerHTML = `
        <div class="no-form-file-upload__item-icon">
          <i class="fa-regular fa-file"></i>
        </div>
        <div class="no-form-file-upload__item-info">
          <div class="no-form-file-upload__item-name">${file.name}</div>
          <div class="no-form-file-upload__item-size">${size}</div>
        </div>
        <button type="button" class="no-form-file-upload__item-delete" data-index="${index}">
          <i class="fa-solid fa-xmark"></i>
        </button>
      `;

      fileList.appendChild(li);
    });
  }

  // 파일 크기 포맷
  function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
  }

  // 파일 삭제
  function removeFile(index) {
    files.splice(index, 1);
    renderFileList();
  }

  // 드롭존 클릭 (파일 입력이 직접 클릭된 경우는 제외)
  dropzone.addEventListener("click", (e) => {
    // 파일 입력이나 그 자식 요소가 클릭된 경우는 무시
    if (e.target === fileInput || fileInput.contains(e.target)) {
      return;
    }
    e.preventDefault();
    fileInput.click();
  });

  // 파일 입력 직접 클릭 방지 (드롭존 클릭으로만 열리도록)
  fileInput.addEventListener("click", (e) => {
    e.stopPropagation();
  });

  // 파일 선택
  fileInput.addEventListener("change", (e) => {
    const selectedFiles = Array.from(e.target.files);
    selectedFiles.forEach((file) => addFile(file));
    fileInput.value = ""; // 같은 파일 재선택 가능하도록
  });

  // 드래그 앤 드롭
  dropzone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropzone.classList.add("is-dragover");
  });

  dropzone.addEventListener("dragleave", () => {
    dropzone.classList.remove("is-dragover");
  });

  dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropzone.classList.remove("is-dragover");

    const droppedFiles = Array.from(e.dataTransfer.files);
    droppedFiles.forEach((file) => addFile(file));
  });

  // 파일 삭제 버튼 클릭
  fileList.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".no-form-file-upload__item-delete");
    if (deleteBtn) {
      const index = parseInt(deleteBtn.dataset.index);
      removeFile(index);
    }
  });
}

// 캡차 새로고침 함수 (전역으로 노출)
window.refreshCaptcha = function () {
  const captchaImg = document.getElementById("captcha-img");
  if (captchaImg) {
    captchaImg.src = "/captcha/default.php?t=" + new Date().getTime();
  }
};

function initHeader() {
  const header = document.getElementById("main-header");

  if (!header) return;

  const nav = header.querySelector(".no-header__nav");

  if (!nav) return;

  // 타임라인 생성 함수
  const createTimeline = () => {
    const timeline = gsap.timeline({ paused: true });
    timeline.to(header, {
      height: () => {
        return nav.offsetHeight + "px";
      },
      ease: "power3.inOut",
    });
    return timeline;
  };

  let tl = createTimeline();

  // 모바일/데스크톱 감지 함수
  const isMobile = () => window.innerWidth <= 1024;

  // 헤더 높이 초기화 함수
  const resetHeaderHeight = () => {
    if (tl.isActive()) {
      tl.progress(0);
      tl.pause();
    }
    gsap.set(header, { clearProps: "height" });
    header.removeAttribute("aria-expanded");
  };

  const onEnter = () => {
    if (isMobile()) return; // 모바일에서는 작동하지 않음
    header.setAttribute("aria-expanded", true);
    tl.play();
    // Header active 시 backdrop 표시
    if (backdrop && backdropTl) {
      backdrop.setAttribute("aria-hidden", "false");
      backdropTl.play();
    }
  };

  const onLeave = () => {
    if (isMobile()) return; // 모바일에서는 작동하지 않음
    header.removeAttribute("aria-expanded");
    tl.reverse();
    // Header 비활성화 시 backdrop 숨김 (다른 요소가 열려있지 않을 때만)
    if (backdrop && backdropTl) {
      const drawerOpen = drawer && drawer.getAttribute("data-state") === "open";
      const searchOpen = search && search.getAttribute("data-state") === "open";
      if (!drawerOpen && !searchOpen) {
        backdropTl.reverse();
      }
    }
  };

  // 이벤트 리스너 관리
  let mouseEnterHandler = null;
  let mouseLeaveHandler = null;

  const setupHeaderEvents = () => {
    // 기존 이벤트 리스너 제거
    if (mouseEnterHandler) {
      nav.removeEventListener("mouseenter", mouseEnterHandler);
      nav.removeEventListener("mouseleave", mouseLeaveHandler);
    }

    if (!isMobile()) {
      // 데스크톱: nav에 마우스 이벤트 추가
      mouseEnterHandler = onEnter;
      mouseLeaveHandler = onLeave;
      nav.addEventListener("mouseenter", mouseEnterHandler);
      nav.addEventListener("mouseleave", mouseLeaveHandler);
    } else {
      // 모바일: 헤더 높이 초기화
      resetHeaderHeight();
    }
  };

  // 헤더 리사이즈 처리 함수
  const handleHeaderResize = () => {
    const wasMobile = mouseEnterHandler === null;
    const nowMobile = isMobile();

    // 모바일/데스크톱 전환 시에만 재설정
    if (wasMobile !== nowMobile) {
      setupHeaderEvents();
    } else if (nowMobile) {
      // 모바일에서 리사이즈 시에도 헤더 높이 초기화
      resetHeaderHeight();
    } else {
      // 데스크톱에서 리사이즈 시: 타임라인 재생성 및 이벤트 재설정
      // 현재 진행 상태 저장
      const wasPlaying = tl.isActive();
      const currentProgress = tl.progress();

      // 타임라인 재생성
      tl.kill();
      tl = createTimeline();

      // 이전 상태 복원
      if (wasPlaying) {
        tl.progress(currentProgress);
        tl.play();
      }

      // 이벤트 리스너 재설정
      setupHeaderEvents();
    }
  };

  // 초기 설정
  setupHeaderEvents();

  // 리사이즈 처리 (디바운스 적용)
  let resizeTimer = null;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      handleHeaderResize();
    }, 150);
  });

  const drawer = document.getElementById("main-drawer");
  const search = document.getElementById("main-search");
  const backdrop = document.getElementById("backdrop");
  const modalCloseBtn = document.querySelector(".no-modal__close");

  let drawerTl = null;
  let searchTl = null;
  let backdropTl = null;

  // Backdrop 애니메이션 설정
  if (backdrop) {
    backdropTl = gsap.timeline({ paused: true });
    backdropTl.set(backdrop, { visibility: "visible" });
    backdropTl.to(backdrop, {
      opacity: 1,
      ease: "power2.inOut",
      duration: 0.3,
    });
    backdropTl.eventCallback("onReverseComplete", () => {
      gsap.set(backdrop, { visibility: "hidden" });
      backdrop.setAttribute("aria-hidden", "true");
    });
  }

  if (drawer) {
    drawerTl = gsap.timeline({ paused: true });
    // 열기 애니메이션: visibility 먼저 설정 후 transform, opacity 애니메이션
    drawerTl.set(drawer, { visibility: "visible" });
    drawerTl.to(drawer, {
      x: "0%",
      opacity: 1,
      ease: "power3.inOut",
      duration: 0.3,
    });
    // 닫기 완료 시 visibility 숨김 (한 번만 설정)
    drawerTl.eventCallback("onReverseComplete", () => {
      gsap.set(drawer, { visibility: "hidden" });
    });
  }

  if (search) {
    searchTl = gsap.timeline({ paused: true });
    // 열기 애니메이션: visibility 먼저 설정 후 opacity 애니메이션
    searchTl.set(search, { visibility: "visible" });
    searchTl.to(search, {
      opacity: 1,
      ease: "power3.inOut",
      duration: 0.3,
    });
    // 닫기 완료 시 visibility 숨김 (한 번만 설정)
    searchTl.eventCallback("onReverseComplete", () => {
      gsap.set(search, { visibility: "hidden" });
      search.classList.remove("is-open");
    });
  }

  // Backdrop 제어 함수 (범용적으로 사용 가능)
  const openBackdrop = () => {
    if (!backdrop || !backdropTl) return;
    backdrop.setAttribute("aria-hidden", "false");
    backdropTl.play();
  };

  const closeBackdrop = (force = false) => {
    if (!backdrop || !backdropTl) return;
    // force가 true이거나 다른 요소가 열려있지 않을 때만 닫기
    if (force) {
      backdropTl.reverse();
      return;
    }
    const drawerOpen = drawer && drawer.getAttribute("data-state") === "open";
    const searchOpen = search && search.getAttribute("data-state") === "open";
    const headerOpen =
      header && header.getAttribute("aria-expanded") === "true";
    if (!drawerOpen && !searchOpen && !headerOpen) {
      backdropTl.reverse();
    }
  };

  // 공통 열기/닫기 함수들
  const openDrawer = () => {
    if (!drawer || !drawerTl) return;
    document.body.classList.add("--hidden");
    document.documentElement.classList.add("scroll-lock");
    document.body.classList.add("scroll-lock");

    // 스크롤 방지 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
    if (window.lenis) {
      window.lenis.stop();
    } else {
      document.body.style.overflow = "hidden";
    }

    drawer.setAttribute("data-state", "open");
    drawerTl.play();
    openBackdrop();
    if (modalCloseBtn) {
      modalCloseBtn.classList.add("is-visible");
    }
  };

  const closeDrawer = () => {
    if (!drawer || !drawerTl) return;
    drawerTl.reverse();
    drawer.setAttribute("data-state", "closed");
    document.body.classList.remove("--hidden");
    document.documentElement.classList.remove("scroll-lock");
    document.body.classList.remove("scroll-lock");

    // 스크롤 복원 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
    if (window.lenis) {
      window.lenis.start();
    } else {
      document.body.style.overflow = "";
    }

    closeBackdrop(); // 다른 요소 확인 후 닫기
    if (modalCloseBtn) {
      modalCloseBtn.classList.remove("is-visible");
    }
  };

  const openSearch = () => {
    if (!search || !searchTl) return;
    document.body.classList.add("--hidden");
    document.documentElement.classList.add("scroll-lock");
    document.body.classList.add("scroll-lock");

    // 스크롤 방지 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
    if (window.lenis) {
      window.lenis.stop();
    } else {
      document.body.style.overflow = "hidden";
    }

    search.setAttribute("data-state", "open");
    search.classList.add("is-open");
    searchTl.play();
    openBackdrop();
    // 모달 닫기 버튼 표시 (search 내부의 버튼도 확인)
    const searchCloseBtn = search.querySelector(".no-modal__close");
    if (searchCloseBtn) {
      searchCloseBtn.classList.add("is-visible");
    }
    if (modalCloseBtn) {
      modalCloseBtn.classList.add("is-visible");
    }
    // 포커스를 검색 입력 필드로 이동 (모바일에서는 제외)
    const searchInput = search.querySelector(".no-search__input");
    if (searchInput && !isMobileDevice()) {
      setTimeout(() => {
        searchInput.focus();
      }, 100);
    }
    // 검색 히스토리 로드 (사용 안 함)
    // loadSearchHistory();
  };

  const closeSearch = () => {
    if (!search || !searchTl) return;
    searchTl.reverse();
    search.setAttribute("data-state", "closed");
    document.body.classList.remove("--hidden");
    document.documentElement.classList.remove("scroll-lock");
    document.body.classList.remove("scroll-lock");

    // 스크롤 복원 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
    if (window.lenis) {
      window.lenis.start();
    } else {
      document.body.style.overflow = "";
    }

    closeBackdrop(); // 다른 요소 확인 후 닫기
    // 모달 닫기 버튼 숨김 (search 내부의 버튼도 확인)
    const searchCloseBtn = search.querySelector(".no-modal__close");
    if (searchCloseBtn) {
      searchCloseBtn.classList.remove("is-visible");
    }
    if (modalCloseBtn) {
      modalCloseBtn.classList.remove("is-visible");
    }
  };

  // 검색 히스토리 관련 함수들 (사용 안 함)
  // async function loadSearchHistory() {
  //   const historyContainer = document.getElementById("searchHistory");
  //   const historyList = document.getElementById("searchHistoryList");
  //
  //   if (!historyContainer || !historyList) return;
  //
  //   try {
  //     const response = await fetch("/api/search-history-list.php");
  //     const result = await response.json();
  //
  //     if (result.success && result.data && result.data.length > 0) {
  //       historyList.innerHTML = "";
  //       result.data.forEach((item) => {
  //         const li = document.createElement("li");
  //         li.className = "no-search__history-item";
  //         li.innerHTML = `
  //           <a href="/search?q=${encodeURIComponent(
  //             item.search_keyword
  //           )}" class="no-search__history-keyword">
  //             ${escapeHtml(item.search_keyword)}
  //           </a>
  //           <button type="button" class="no-search__history-delete" data-id="${
  //             item.id
  //           }" aria-label="삭제">
  //             <i class="fa-regular fa-xmark"></i>
  //           </button>
  //         `;
  //         historyList.appendChild(li);
  //       });
  //       historyContainer.style.display = "block";
  //     } else {
  //       historyContainer.style.display = "none";
  //     }
  //   } catch (error) {
  //     console.error("검색 히스토리 로드 실패:", error);
  //     historyContainer.style.display = "none";
  //   }
  // }
  //
  // function escapeHtml(text) {
  //   const div = document.createElement("div");
  //   div.textContent = text;
  //   return div.innerHTML;
  // }
  //
  // async function deleteSearchHistory(historyId) {
  //   try {
  //     const response = await fetch("/api/search-history-delete.php", {
  //       method: "POST",
  //       headers: {
  //         "Content-Type": "application/json",
  //       },
  //       body: JSON.stringify({ id: historyId }),
  //     });
  //
  //     const result = await response.json();
  //
  //     if (result.success) {
  //       loadSearchHistory(); // 히스토리 다시 로드
  //     } else {
  //       alert(result.message || "삭제에 실패했습니다.");
  //     }
  //   } catch (error) {
  //     console.error("검색 히스토리 삭제 실패:", error);
  //     alert("삭제 중 오류가 발생했습니다.");
  //   }
  // }
  //
  // async function clearAllSearchHistory() {
  //   if (!confirm("모든 검색 히스토리를 삭제하시겠습니까?")) {
  //     return;
  //   }
  //
  //   const historyList = document.getElementById("searchHistoryList");
  //   if (!historyList) return;
  //
  //   const items = historyList.querySelectorAll(".no-search__history-item");
  //   const deletePromises = Array.from(items).map((item) => {
  //     const deleteBtn = item.querySelector(".no-search__history-delete");
  //     const historyId = deleteBtn?.getAttribute("data-id");
  //     if (historyId) {
  //       return deleteSearchHistory(historyId);
  //     }
  //   });
  //
  //   await Promise.all(deletePromises);
  //   loadSearchHistory(); // 히스토리 다시 로드
  // }
  //
  // // 검색 히스토리 이벤트 리스너
  // if (search) {
  //   // 개별 삭제
  //   search.addEventListener("click", (e) => {
  //     const deleteBtn = e.target.closest(".no-search__history-delete");
  //     if (deleteBtn) {
  //       e.preventDefault();
  //       e.stopPropagation();
  //       const historyId = deleteBtn.getAttribute("data-id");
  //       if (historyId) {
  //         deleteSearchHistory(historyId);
  //       }
  //     }
  //   });
  //
  //   // 전체 삭제
  //   const clearAllBtn = document.getElementById("clearAllHistory");
  //   if (clearAllBtn) {
  //     clearAllBtn.addEventListener("click", (e) => {
  //       e.preventDefault();
  //       clearAllSearchHistory();
  //     });
  //   }
  // }

  // Drawer 열기
  if (drawer && drawerTl) {
    const drawerOpenBtn = header.querySelector(".no-header__toggle");

    if (drawerOpenBtn) {
      drawerOpenBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        // 스크롤 방지 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
        if (window.lenis) {
          window.lenis.stop();
        } else {
          document.body.style.overflow = "hidden";
        }

        // search가 열려있으면 먼저 닫기
        if (search && search.getAttribute("data-state") === "open") {
          closeSearch();
          // search가 닫힐 때까지 대기 후 drawer 열기
          setTimeout(() => {
            openDrawer();
          }, 300);
        } else {
          openDrawer();
        }
      });
    }
  }

  // Search 열기
  if (search && searchTl) {
    const searchOpenBtn = header.querySelector(".no-header__search-toggle");

    if (searchOpenBtn) {
      searchOpenBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        // 스크롤 방지 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
        if (window.lenis) {
          window.lenis.stop();
        } else {
          document.body.style.overflow = "hidden";
        }

        // drawer가 열려있으면 먼저 닫기
        if (drawer && drawer.getAttribute("data-state") === "open") {
          closeDrawer();
          // drawer가 닫힐 때까지 대기 후 search 열기
          setTimeout(() => {
            openSearch();
          }, 300);
        } else {
          openSearch();
        }
      });
    }
  }

  // Backdrop 클릭 시 닫기
  if (backdrop) {
    backdrop.addEventListener("click", (e) => {
      // backdrop 자체를 클릭했을 때만 닫기 (자식 요소 클릭은 무시)
      if (e.target === backdrop) {
        // 열려있는 요소 확인 후 닫기 (우선순위: search > drawer > header)
        if (search && search.getAttribute("data-state") === "open") {
          closeSearch();
        } else if (drawer && drawer.getAttribute("data-state") === "open") {
          closeDrawer();
        } else if (header && header.getAttribute("aria-expanded") === "true") {
          // Header 닫기 (마우스가 나가면 자동으로 닫히지만, backdrop 클릭 시에도 닫기)
          header.removeAttribute("aria-expanded");
          if (tl) {
            tl.reverse();
          }
          closeBackdrop();
        }
      }
    });
  }

  // 공통 닫기 버튼 이벤트 (헤더의 버튼)
  if (modalCloseBtn) {
    modalCloseBtn.addEventListener("click", () => {
      // search가 열려있으면 search 닫기 (우선순위)
      if (search && search.getAttribute("data-state") === "open") {
        closeSearch();
      } else if (drawer && drawer.getAttribute("data-state") === "open") {
        // drawer가 열려있으면 drawer 닫기
        closeDrawer();
      }
    });
  }

  // Search 내부의 닫기 버튼 이벤트
  if (search) {
    const searchCloseBtn = search.querySelector(".no-modal__close");
    if (searchCloseBtn) {
      searchCloseBtn.addEventListener("click", () => {
        closeSearch();
      });
    }
  }

  // ESC 키로 닫기
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      // 우선순위: search > drawer > header
      if (search && search.getAttribute("data-state") === "open") {
        closeSearch();
      } else if (drawer && drawer.getAttribute("data-state") === "open") {
        closeDrawer();
      } else if (header && header.getAttribute("aria-expanded") === "true") {
        header.removeAttribute("aria-expanded");
        if (tl) {
          tl.reverse();
        }
        closeBackdrop();
      }
    }
  });

  // Drawer 내 다국어 드롭다운 초기화
  if (drawer) {
    const drawerLangBtn = drawer.querySelector(".no-drawer__lang-btn");
    const drawerLang = drawer.querySelector(".no-drawer__lang");
    const drawerLangDropdown = drawer.querySelector(
      ".no-drawer__lang-dropdown"
    );

    if (drawerLangBtn && drawerLang && drawerLangDropdown) {
      drawerLangBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isOpen = drawerLang.classList.contains("is-open");

        if (isOpen) {
          drawerLang.classList.remove("is-open");
          drawerLangBtn.setAttribute("aria-expanded", "false");
        } else {
          drawerLang.classList.add("is-open");
          drawerLangBtn.setAttribute("aria-expanded", "true");
        }
      });

      // 외부 클릭 시 닫기
      document.addEventListener("click", (e) => {
        if (!drawerLang.contains(e.target)) {
          drawerLang.classList.remove("is-open");
          drawerLangBtn.setAttribute("aria-expanded", "false");
        }
      });
    }
  }

  // IntersectionObserver로 no-main-hero 감지
  const heroSection = document.querySelector(".no-main-hero");
  if (heroSection) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            header.classList.add("visual");
          } else {
            header.classList.remove("visual");
          }
        });
      },
      {
        threshold: 0.1, // 10% 이상 보이면 감지
        rootMargin: "0px",
      }
    );

    observer.observe(heroSection);
  }
}

// Lenis 인스턴스 전역 변수
let lenisInstance = null;

function initLenis() {
  const isMobile = isMobileDevice();
  
  // 모바일 최적화 옵션
  const lenisOptions = {
    smooth: true,
    smoothTouch: isMobile, // 모바일에서 터치 스크롤 부드럽게
    touchMultiplier: isMobile ? 1.5 : 2, // 모바일에서 터치 감도 조정
    duration: isMobile ? 1.0 : 1.2, // 모바일에서 더 빠른 스크롤
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
  };

  lenisInstance = new Lenis(lenisOptions);
  window.lenis = lenisInstance; // 전역 접근 가능하도록

  lenisInstance.on("scroll", ScrollTrigger.update);

  gsap.ticker.add((time) => {
    lenisInstance.raf(time * 1000);
  });

  gsap.ticker.lagSmoothing(0);

  return lenisInstance;
}

function initSwipers() {
  // Main Hero Swiper
  const mainHeroSwiperEl = document.querySelector(".no-main-hero-swiper");
  if (mainHeroSwiperEl) {
    const currentEl = document.querySelector(
      ".no-main-hero-swiper-pagination__current"
    );
    const totalEl = document.querySelector(
      ".no-main-hero-swiper-pagination__total"
    );
    const progressBar = document.getElementById("heroProgressBar");

    let progressInterval = null;

    const updateProgress = () => {
      if (progressBar) {
        progressBar.style.transition = "none";
        progressBar.style.width = "0%";
        setTimeout(() => {
          progressBar.style.transition = "width linear 5s";
          progressBar.style.width = "100%";
        }, 10);
      }
    };

    const triggerHeroAnimations = () => {
      // 모든 슬라이드의 텍스트와 이미지 초기화
      const allSlides = mainHeroSwiperEl.querySelectorAll(".swiper-slide");
      allSlides.forEach((slide) => {
        const txt = slide.querySelector(".no-main-hero-txt");
        const img = slide.querySelector(".no-main-hero-img img");
        if (txt) {
          txt.style.transition = "none";
          txt.style.opacity = "0";
          txt.style.transform = "translateY(60px)";
        }
        if (img) {
          img.style.transition = "none";
          img.style.transform = "scale(1.1)";
        }
      });

      // Active 슬라이드에 애니메이션 적용 (슬라이드 전환 완료 후)
      setTimeout(() => {
        const activeSlide = mainHeroSwiperEl.querySelector(
          ".swiper-slide-active"
        );
        if (activeSlide) {
          const activeTxt = activeSlide.querySelector(".no-main-hero-txt");
          const activeImg = activeSlide.querySelector(".no-main-hero-img img");

          // Transition 복원 (더 역동적인 easing)
          if (activeTxt) {
            activeTxt.style.transition =
              "opacity 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s, transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s";
          }
          if (activeImg) {
            activeImg.style.transition = "";
          }

          // 이미지 애니메이션
          if (activeImg) {
            requestAnimationFrame(() => {
              activeImg.style.transform = "scale(1)";
            });
          }

          // 텍스트 애니메이션
          if (activeTxt) {
            requestAnimationFrame(() => {
              setTimeout(() => {
                activeTxt.style.opacity = "1";
                activeTxt.style.transform = "translateY(0)";
              }, 50);
            });
          }
        }
      }, 150);
    };

    const mainHeroSwiper = new Swiper(mainHeroSwiperEl, {
      slidesPerView: 1,
      spaceBetween: 0,
      loop: true,
      speed: 1500,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      on: {
        init: function () {
          // 전체 슬라이드 수 설정 (실제 슬라이드 수)
          const realSlides = this.slides.filter(
            (slide) => !slide.classList.contains("swiper-slide-duplicate")
          ).length;
          if (totalEl) {
            totalEl.textContent = realSlides;
          }
          // 현재 슬라이드 번호 업데이트
          if (currentEl) {
            currentEl.textContent = this.realIndex + 1;
          }
          // 프로그레스 바 시작
          updateProgress();
          // 초기 애니메이션 트리거
          setTimeout(() => {
            triggerHeroAnimations();
          }, 100);
        },
        slideChange: function () {
          // 슬라이드 변경 시 현재 번호 업데이트
          if (currentEl) {
            currentEl.textContent = this.realIndex + 1;
          }
          // 프로그레스 바 리셋 및 재시작
          updateProgress();
        },
        slideChangeTransitionEnd: function () {
          // 슬라이드 전환이 완료된 후 애니메이션 트리거
          triggerHeroAnimations();
        },
        autoplayStart: function () {
          updateProgress();
        },
        autoplayStop: function () {
          if (progressBar) {
            progressBar.style.width = "100%";
          }
        },
      },
    });
  }

  // Whatson Swiper
  const whatsonSwiperEl = document.querySelector(".no-main-whatson-swiper");
  if (whatsonSwiperEl) {
    const prevBtn = document.querySelector(".no-main-whatson-pagination__prev");
    const nextBtn = document.querySelector(".no-main-whatson-pagination__next");
    const currentEl = document.querySelector(
      ".no-main-whatson-pagination__current"
    );
    const totalEl = document.querySelector(
      ".no-main-whatson-pagination__total"
    );

    new Swiper(whatsonSwiperEl, {
      slidesPerView: 1.2,
      spaceBetween: 12,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: nextBtn,
        prevEl: prevBtn,
      },
      breakpoints: {
        348: {
          slidesPerView: 1.2,
          spaceBetween: 12,
        },
        544: {
          slidesPerView: 1.5,
          spaceBetween: 16,
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 2.2,
          spaceBetween: 22,
        },
        1500: {
          slidesPerView: 3,
          spaceBetween: 24,
        },
      },
      on: {
        init: function () {
          if (totalEl) {
            totalEl.textContent = String(this.slides.length).padStart(2, "0");
          }
          if (currentEl) {
            currentEl.textContent = String(this.activeIndex + 1).padStart(
              2,
              "0"
            );
          }
          updateWhatsonButtons(this);
        },
        slideChange: function () {
          if (currentEl) {
            currentEl.textContent = String(this.activeIndex + 1).padStart(
              2,
              "0"
            );
          }
          updateWhatsonButtons(this);
        },
      },
    });

    function updateWhatsonButtons(swiper) {
      if (prevBtn) {
        prevBtn.disabled = swiper.isBeginning;
      }
      if (nextBtn) {
        nextBtn.disabled = swiper.isEnd;
      }
    }
  }

  // Facilities Swiper
  const facSwiperEls = document.querySelectorAll(".no-sub-ui-swiper");
  if (facSwiperEls.length) {
    facSwiperEls.forEach((swiperEl) => {
      const wrapper = swiperEl.closest(".no-sub-ui-swiper-wrapper");
      const prevBtn = wrapper?.querySelector(".no-sub-ui-swiper-nav__prev");
      const nextBtn = wrapper?.querySelector(".no-sub-ui-swiper-nav__next");

      const swiper = new Swiper(swiperEl, {
        slidesPerView: "auto",
        spaceBetween: 24,
        loop: true,
        speed: 600,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        navigation: {
          nextEl: nextBtn,
          prevEl: prevBtn,
        },
        breakpoints: {
          320: {
            slidesPerView: "auto",
            spaceBetween: 12,
          },
          768: {
            slidesPerView: "auto",
            spaceBetween: 16,
          },
          1024: {
            slidesPerView: "auto",
            spaceBetween: 24,
          },
        },
      });
    });
  }

  // Category Swiper (모바일에서만 초기화)
  const categorySwiperEl = document.querySelector(".no-category-swiper");
  if (categorySwiperEl) {
    let categorySwiper = null;

    const initCategorySwiper = () => {
      const isMobile = window.innerWidth < 768; // md breakpoint

      if (isMobile && !categorySwiper) {
        // 모바일: Swiper 초기화
        categorySwiper = new Swiper(categorySwiperEl, {
          slidesPerView: "auto",
          spaceBetween: 8,
          freeMode: true,
        });
      } else if (!isMobile && categorySwiper) {
        // 데스크톱: Swiper 제거
        categorySwiper.destroy(true, true);
        categorySwiper = null;
      }
    };

    // 초기화
    initCategorySwiper();

    // 리사이즈 이벤트 (디바운스)
    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        initCategorySwiper();
      }, 250);
    });
  }

  // Sub UI Tabs Swiper (모바일에서만 초기화, 범용)
  const tabsSwiperEls = document.querySelectorAll(".no-sub-ui-tabs-swiper");
  if (tabsSwiperEls.length) {
    const tabsSwipers = new Map();

    tabsSwiperEls.forEach((swiperEl) => {
      let tabsSwiper = null;

      const initTabsSwiper = () => {
        const isMobile = window.innerWidth < 768; // md breakpoint

        if (isMobile && !tabsSwiper) {
          // active 탭 찾기
          const activeSlide = swiperEl.querySelector(".swiper-slide.is-active");
          const activeIndex = activeSlide
            ? Array.from(swiperEl.querySelectorAll(".swiper-slide")).indexOf(
                activeSlide
              )
            : 0;

          tabsSwiper = new Swiper(swiperEl, {
            slidesPerView: "auto",
            spaceBetween: 8,
            freeMode: true,
            touchEventsTarget: "container",
            initialSlide: activeIndex,
            on: {
              init: function () {
                // 초기화 시 active 탭으로 이동
                if (activeIndex >= 0) {
                  this.slideTo(activeIndex, 0);
                }
              },
            },
          });

          // 탭 클릭 시 해당 탭으로 스크롤
          const tabLinks = swiperEl.querySelectorAll(
            ".swiper-slide .no-sub-ui-tab__btn"
          );
          tabLinks.forEach((link, index) => {
            link.addEventListener("click", (e) => {
              // 기본 동작은 유지 (페이지 이동)
              // 하지만 모바일에서 클릭 시 해당 탭으로 스크롤
              if (isMobile && tabsSwiper) {
                const slide = link.closest(".swiper-slide");
                const slideIndex = Array.from(
                  swiperEl.querySelectorAll(".swiper-slide")
                ).indexOf(slide);
                if (slideIndex >= 0) {
                  tabsSwiper.slideTo(slideIndex, 300);
                }
              }
            });
          });
        } else if (!isMobile && tabsSwiper) {
          // 데스크톱: Swiper 제거
          tabsSwiper.destroy(true, true);
          tabsSwiper = null;
        }
      };

      // 초기화
      initTabsSwiper();

      // 리사이즈 이벤트 (디바운스)
      let resizeTimer;
      window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          initTabsSwiper();
        }, 250);
      });

      tabsSwipers.set(swiperEl, tabsSwiper);
    });
  }
}

function initMarquee() {
  const marquees = document.querySelectorAll(".no-marquee");

  if (!marquees.length) return;

  marquees.forEach((item) => {
    const marqueeInner = item.querySelector(".no-marquee__inner");
    const marqueeContent = marqueeInner.querySelector(".no-marquee__content");

    if (!marqueeContent) return;

    // Duration
    const duration =
      parseFloat(item.getAttribute("data-marquee-duration")) || 30;

    // Content width 계산
    const contentWidth = marqueeContent.offsetWidth;

    // Clone 생성 (무한 루프를 위해 여러 개 생성)
    for (let i = 0; i < 2; i++) {
      const clone = marqueeContent.cloneNode(true);
      marqueeInner.appendChild(clone);
    }

    // 전체 inner를 애니메이션
    gsap.to(marqueeInner, {
      x: -contentWidth,
      duration: duration,
      ease: "none",
      repeat: -1,
      modifiers: {
        x: gsap.utils.unitize((x) => {
          const num = parseFloat(x);
          // 첫 번째 content가 완전히 사라지면 리셋
          return num <= -contentWidth ? num + contentWidth : num;
        }),
      },
    });
  });
}

// ============================================
// Breadcrumb 드롭다운 초기화
// ============================================
function initBreadcrumb() {
  const breadcrumbMenus = document.querySelectorAll(".breadcrumb-has-menu");

  if (!breadcrumbMenus.length) return;

  breadcrumbMenus.forEach((menuItem) => {
    const toggle = menuItem.querySelector(".breadcrumb-toggle");
    const dropdown = menuItem.querySelector(".breadcrumb-menu");

    if (!toggle || !dropdown) return;

    // 클릭 이벤트 (모바일 대응)
    toggle.addEventListener("click", (e) => {
      e.stopPropagation();

      // 다른 드롭다운 닫기
      breadcrumbMenus.forEach((other) => {
        if (other !== menuItem) {
          other.classList.remove("is-open");
        }
      });

      // 현재 드롭다운 토글
      menuItem.classList.toggle("is-open");
    });

    // 외부 클릭 시 드롭다운 닫기
    document.addEventListener("click", (e) => {
      if (!menuItem.contains(e.target)) {
        menuItem.classList.remove("is-open");
      }
    });
  });
}

// ============================================
// 대관 절차 프로세스 (순차적 active 처리)
// ============================================
function initProcedure() {
  const procedureSection = document.querySelector(".no-sub-procedure");
  if (!procedureSection) return;

  const list = procedureSection.querySelector(".no-sub-procedure-list");
  const linePlaceholder = procedureSection.querySelector(
    ".no-sub-procedure-line-placeholder"
  );
  const line = procedureSection.querySelector(".no-sub-procedure-line");
  const items = procedureSection.querySelectorAll(".no-sub-procedure-item");
  if (!items.length || !line || !linePlaceholder) return;

  // line 위치 업데이트 함수
  function updateLine() {
    const activeItem = list.querySelector(".no-sub-procedure-item.is-active");
    if (!activeItem) return;

    const listRect = list.getBoundingClientRect();
    const activeRect = activeItem.getBoundingClientRect();
    const iconRect = activeItem
      .querySelector(".no-sub-procedure-item-icon")
      .getBoundingClientRect();

    // 첫 번째 아이콘 중앙부터 active 아이콘 중앙까지의 거리 계산
    const firstIcon = items[0].querySelector(".no-sub-procedure-item-icon");
    const firstIconRect = firstIcon.getBoundingClientRect();
    const firstIconCenter =
      firstIconRect.top + firstIconRect.height / 2 - listRect.top;
    const activeIconCenter = iconRect.top + iconRect.height / 2 - listRect.top;

    // line 높이 설정
    line.style.top = `${firstIconCenter}px`;
    line.style.height = `${activeIconCenter - firstIconCenter}px`;
  }

  // 모든 active와 passed 제거
  items.forEach((item) => {
    item.classList.remove("is-active", "is-passed");
  });

  // 첫 번째 항목을 즉시 활성화
  let currentIndex = 0;
  items[currentIndex].classList.add("is-active");
  updateLine();

  // 다음 항목들을 순차적으로 활성화
  function activateNext() {
    // 이전 active를 passed로 변경
    items[currentIndex].classList.remove("is-active");
    items[currentIndex].classList.add("is-passed");

    // 다음 인덱스 계산 (마지막이면 처음으로)
    currentIndex++;
    if (currentIndex >= items.length) {
      // 마지막에 도달하면 모든 passed 제거하고 처음부터 시작
      items.forEach((item) => {
        item.classList.remove("is-active", "is-passed");
      });
      currentIndex = 0;
    }

    items[currentIndex].classList.add("is-active");
    updateLine();

    // 다음 항목 활성화를 위한 setTimeout (3초마다)
    setTimeout(activateNext, 3000);
  }

  // 3초 후 다음 항목 활성화 시작
  setTimeout(activateNext, 3000);

  // 리사이즈 시 line 위치 업데이트
  window.addEventListener("resize", updateLine);
}

function initTheaterNolRolling() {
  const leftList = document.querySelector(
    ".no-sub-theater-nol__rolling-list--left"
  );
  const rightList = document.querySelector(
    ".no-sub-theater-nol__rolling-list--right"
  );

  if (!leftList || !rightList) return;

  // Wrapper 생성 및 구조 설정
  const setupWrapper = (list) => {
    const wrapper = document.createElement("div");
    wrapper.className = "no-sub-theater-nol__rolling-wrapper";
    list.parentNode.insertBefore(wrapper, list);
    wrapper.appendChild(list);

    // 리스트 복제하여 무한 루프 구현
    const clone = list.cloneNode(true);
    wrapper.appendChild(clone);

    return { wrapper, list, clone, height: list.offsetHeight };
  };

  // 이미지 로드 대기
  const waitForImages = () => {
    return new Promise((resolve) => {
      const images = [
        ...leftList.querySelectorAll("img"),
        ...rightList.querySelectorAll("img"),
      ];

      if (images.length === 0) {
        resolve();
        return;
      }

      let loaded = 0;
      const checkComplete = () => {
        loaded++;
        if (loaded === images.length) resolve();
      };

      images.forEach((img) => {
        if (img.complete) {
          checkComplete();
        } else {
          img.addEventListener("load", checkComplete);
          img.addEventListener("error", checkComplete);
        }
      });
    });
  };

  // 애니메이션 초기화
  waitForImages().then(() => {
    const left = setupWrapper(leftList);
    const right = setupWrapper(rightList);

    const duration = 50;

    // 왼쪽: 위로 스크롤
    gsap.to(left.list, {
      y: -left.height,
      duration: duration,
      ease: "none",
      repeat: -1,
    });
    gsap.to(left.clone, {
      y: -left.height,
      duration: duration,
      ease: "none",
      repeat: -1,
    });

    // 오른쪽: 아래로 스크롤 (초기 위치를 음수로 설정)
    gsap.set(right.list, { y: -right.height });
    gsap.set(right.clone, { y: -right.height });
    gsap.to(right.list, {
      y: 0,
      duration: duration,
      ease: "none",
      repeat: -1,
    });
    gsap.to(right.clone, {
      y: 0,
      duration: duration,
      ease: "none",
      repeat: -1,
    });
  });
}

function initFloatingButton(lenis) {
  const floatingButton = document.querySelector(".no-floating-button");
  if (!floatingButton) return;

  const topButton = floatingButton.querySelector(".no-floating-button__top");
  if (!topButton) return;

  // 탑 버튼 클릭 시 맨 위로 스크롤
  topButton.addEventListener("click", () => {
    if (window.lenis) {
      window.lenis.scrollTo(0, {
        duration: isMobileDevice() ? 0.8 : 1.2, // 모바일에서 더 빠르게
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      });
    } else {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });

  // 스크롤 위치에 따라 탑 버튼 표시/숨김
  const handleScroll = () => {
    const scrollY = window.scrollY || window.pageYOffset;
    const threshold = 300; // 300px 이상 스크롤 시 표시

    // Footer 요소 찾기
    const footer = document.querySelector(".no-footer");
    let footerTop = null;

    if (footer) {
      const footerRect = footer.getBoundingClientRect();
      footerTop = scrollY + footerRect.top;

      // 버튼 높이와 여백 고려 (버튼이 footer top에 닿기 전에 숨김)
      const buttonHeight = topButton.offsetHeight || 56;
      const buttonBottom = scrollY + window.innerHeight - buttonHeight - 20; // bottom 여백 20px

      // Footer top에 닿으면 숨김
      if (buttonBottom >= footerTop) {
        topButton.classList.remove("is-visible");
        return;
      }
    }

    if (scrollY > threshold) {
      topButton.classList.add("is-visible");
    } else {
      topButton.classList.remove("is-visible");
    }
  };

  // Lenis 스크롤 이벤트 또는 일반 스크롤 이벤트
  if (window.lenis) {
    window.lenis.on(
      "scroll",
      ({ scroll, limit, velocity, direction, progress }) => {
        handleScroll();
      }
    );
  } else {
    window.addEventListener("scroll", handleScroll, { passive: true });
  }

  // 초기 상태 확인
  handleScroll();
}

function initWhatsonView() {
  const longPoster = document.getElementById("longPoster");
  const longPosterToggle = document.getElementById("longPosterToggle");
  const toggleText = longPosterToggle?.querySelector(
    ".no-sub-whatson-view__long-poster-toggle-text"
  );
  const toggleIcon = longPosterToggle?.querySelector("i");

  if (longPoster && longPosterToggle) {
    longPosterToggle.addEventListener("click", function () {
      const isExpanded = longPoster.classList.toggle("--expanded");

      if (isExpanded) {
        toggleText.textContent = "접기";
        toggleIcon.classList.remove("fa-chevron-down");
        toggleIcon.classList.add("fa-chevron-up");
      } else {
        toggleText.textContent = "펼치기";
        toggleIcon.classList.remove("fa-chevron-up");
        toggleIcon.classList.add("fa-chevron-down");
      }
    });
  }
}

function initWhatsonFilter() {
  const filter = document.getElementById("whatson-filter");
  const filterToggle = document.querySelector(".no-sub-whatson-filter-toggle");
  const filterClose = document.querySelector(".no-sub-whatson-filter__close");

  if (!filter || !filterToggle) return;

  const openFilter = () => {
    filter.classList.add("is-open");
    document.documentElement.classList.add("scroll-lock");
    document.body.classList.add("scroll-lock");

    // 스크롤 방지 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
    if (window.lenis) {
      window.lenis.stop();
    } else {
      document.body.style.overflow = "hidden";
    }
  };

  const closeFilter = () => {
    // 트랜지션이 완료될 때까지 기다린 후 스크롤 복원
    const handleTransitionEnd = (e) => {
      if (e.target === filter && !filter.classList.contains("is-open")) {
        document.documentElement.classList.remove("scroll-lock");
        document.body.classList.remove("scroll-lock");

        // 스크롤 복원 (Lenis가 있으면 Lenis로, 없으면 일반 방식)
        if (window.lenis) {
          window.lenis.start();
        } else {
          document.body.style.overflow = "";
        }

        // 모바일에서 필터 닫을 때 스크롤을 맨 위로 이동
        if (isMobileDevice()) {
          if (window.lenis) {
            window.lenis.scrollTo(0, {
              duration: 0.5,
              easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            });
          } else {
            window.scrollTo({ top: 0, behavior: "smooth" });
          }
        }

        filter.removeEventListener("transitionend", handleTransitionEnd);
      }
    };

    filter.addEventListener("transitionend", handleTransitionEnd);
    filter.classList.remove("is-open");

    // 트랜지션이 없을 경우를 대비한 폴백
    setTimeout(() => {
      if (!filter.classList.contains("is-open")) {
        document.documentElement.classList.remove("scroll-lock");
        document.body.classList.remove("scroll-lock");

        if (window.lenis) {
          window.lenis.start();
        } else {
          document.body.style.overflow = "";
        }

        // 모바일에서 필터 닫을 때 스크롤을 맨 위로 이동
        if (isMobileDevice()) {
          if (window.lenis) {
            window.lenis.scrollTo(0, {
              duration: 0.5,
              easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            });
          } else {
            window.scrollTo({ top: 0, behavior: "smooth" });
          }
        }
      }
    }, 450); // transition duration (400ms) + 여유시간
  };

  // 필터 열기
  filterToggle.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    openFilter();
  });

  // 필터 닫기
  if (filterClose) {
    filterClose.addEventListener("click", (e) => {
      e.preventDefault();
      closeFilter();
    });
  }

  // ESC 키로 닫기
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && filter.classList.contains("is-open")) {
      closeFilter();
    }
  });
}

/**
 * 연도/월 선택기 클래스 (단일 책임: 연도/월 선택 UI 관리)
 */
class YearMonthPicker {
  constructor(containerId, onApplyCallback) {
    this.container = document.getElementById(containerId);
    if (!this.container) {
      // 컨테이너가 없으면 기능을 건너뜁니다. (콘솔 노이즈 방지)
      return;
    }

    this.onApplyCallback = onApplyCallback || (() => {});

    // 내부 상태 (캡슐화)
    this.state = {
      selectedYear: "all",
      selectedMonths: ["all"],
    };

    // DOM 요소 캐싱
    this.elements = this.initializeElements();

    // 초기 상태 설정 (닫힘 상태)
    this.initializeState();

    // 이벤트 바인딩
    this.bindEvents();
  }

  /**
   * 초기 상태 설정
   */
  initializeState() {
    if (this.elements.picker) {
      // 초기에는 닫힘 상태
      this.elements.picker.style.display = "none";
    }
  }

  /**
   * DOM 요소 초기화
   */
  initializeElements() {
    return {
      input: this.container.querySelector("#yearMonthInput"),
      clearBtn: this.container.querySelector("#yearMonthClear"),
      picker: this.container.querySelector("#yearMonthPicker"),
      confirmBtn: this.container.querySelector("#yearMonthConfirm"),
      yearItems: this.container.querySelectorAll(
        ".no-filter-date-picker__year-item"
      ),
      monthBtns: this.container.querySelectorAll(
        ".no-filter-date-picker__month-btn"
      ),
      monthPanel: this.container.querySelector("#monthPanel"),
      yearList: this.container.querySelector(
        ".no-filter-date-picker__year-list"
      ),
      inner: this.container.querySelector(".no-filter-date-picker__inner"),
    };
  }

  /**
   * 이벤트 바인딩
   */
  bindEvents() {
    if (!this.elements.input) return;

    // Input 클릭
    this.elements.input.addEventListener("click", (e) => {
      e.stopPropagation();
      this.toggle();
    });

    // 초기화 버튼
    if (this.elements.clearBtn) {
      this.elements.clearBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        this.reset();
      });
    }

    // 연도 선택
    if (this.elements.yearItems) {
      this.elements.yearItems.forEach((item) => {
        item.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.selectYear(item.dataset.year);
        });
      });
    }

    // 월 선택
    if (this.elements.monthBtns) {
      this.elements.monthBtns.forEach((btn) => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.toggleMonth(btn.dataset.month);
        });
      });
    }

    // 확인 버튼
    if (this.elements.confirmBtn) {
      this.elements.confirmBtn.addEventListener("click", () => {
        this.apply();
      });
    }

    // 외부 클릭 시 닫기
    document.addEventListener("click", (e) => {
      if (
        this.elements.picker &&
        !this.elements.picker.contains(e.target) &&
        this.elements.input &&
        !this.elements.input.contains(e.target)
      ) {
        this.close();
      }
    });
  }

  /**
   * 현재 선택된 값 가져오기
   */
  getValue() {
    return {
      year: this.state.selectedYear,
      months: [...this.state.selectedMonths],
    };
  }

  /**
   * 값 설정
   */
  setValue(year, months) {
    this.state.selectedYear = year || "all";
    this.state.selectedMonths = months || ["all"];
    this.updateDisplay();
  }

  /**
   * 초기화
   */
  reset() {
    this.setValue("all", ["all"]);
    this.close();
    this.onApplyCallback("all", ["all"]);
  }

  /**
   * 모달 열기/닫기
   */
  toggle() {
    if (!this.elements.picker) return;
    const isOpen = this.elements.picker.classList.contains("is-open");
    isOpen ? this.close() : this.open();
  }

  /**
   * 모달 열기
   */
  open() {
    if (!this.elements.picker) return;

    // 먼저 표시 (리플로우를 위해)
    this.elements.picker.style.display = "block";

    // 약간의 지연 후 애니메이션 시작
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        // 현재 상태로 초기화
        this.updateYearSelection();
        this.updatePickerLayout(this.state.selectedYear);
        this.updateMonthSelection();

        // 열림 상태 클래스 추가
        this.elements.picker.classList.add("is-open");

        // 연도 선택 picker로 스크롤 (lenis 사용)
        this.scrollToPicker();
      });
    });
  }

  /**
   * 모달 닫기
   */
  close() {
    if (!this.elements.picker) return;

    // 닫힘 상태 클래스 제거
    this.elements.picker.classList.remove("is-open");

    setTimeout(() => {
      this.elements.picker.style.display = "none";
    }, 300);
  }

  /**
   * 연도 선택 picker로 스크롤 (lenis 사용)
   */
  scrollToPicker() {
    if (!this.elements.picker || !this.elements.yearList) return;

    // 약간의 지연 후 스크롤 (애니메이션이 시작된 후)
    setTimeout(() => {
      if (isMobileDevice()) {
        // 모바일: 필터 폼 내부에서 연도 선택 리스트로 스크롤
        const filterForm = this.elements.picker.closest(".no-filter-form");
        if (filterForm) {
          const yearListRect = this.elements.yearList.getBoundingClientRect();
          const formRect = filterForm.getBoundingClientRect();
          const relativeTop = yearListRect.top - formRect.top;

          // 부드러운 스크롤
          filterForm.scrollTo({
            top: filterForm.scrollTop + relativeTop - 20,
            behavior: "smooth",
          });
        }
      } else {
        // 데스크탑: Lenis로 스크롤
        if (window.lenis && window.lenis.scrollTo) {
          const pickerRect = this.elements.picker.getBoundingClientRect();
          const yearListRect = this.elements.yearList.getBoundingClientRect();
          const currentScroll = window.scrollY || window.pageYOffset;

          // 연도 리스트가 보이는 위치로 스크롤 (상단 여백 20px)
          const targetScroll = currentScroll + yearListRect.top - 20;

          window.lenis.scrollTo(targetScroll, {
            duration: 0.6,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // easeOutExpo
          });
        }
      }
    }, 100);
  }

  /**
   * 연도 선택
   */
  selectYear(year) {
    this.state.selectedYear = year;
    this.updateYearSelection();
    this.updatePickerLayout(year);

    if (year !== "all") {
      this.state.selectedMonths = ["all"];
      this.updateMonthSelection();

      // 연도 선택 후 월 선택 패널로 스크롤 (모바일)
      if (isMobileDevice() && this.elements.monthPanel) {
        setTimeout(() => {
          const filterForm = this.elements.picker?.closest(".no-filter-form");
          if (filterForm && this.elements.monthPanel) {
            const monthPanelRect =
              this.elements.monthPanel.getBoundingClientRect();
            const formRect = filterForm.getBoundingClientRect();
            const relativeTop = monthPanelRect.top - formRect.top;

            // 부드러운 스크롤
            filterForm.scrollTo({
              top: filterForm.scrollTop + relativeTop - 20,
              behavior: "smooth",
            });
          }
        }, 300); // 월 패널 애니메이션 후 스크롤
      }
    } else {
      this.state.selectedMonths = ["all"];
    }
  }

  /**
   * 월 토글
   */
  toggleMonth(month) {
    if (month === "all") {
      this.state.selectedMonths = ["all"];
    } else {
      this.state.selectedMonths = this.state.selectedMonths.filter(
        (m) => m !== "all"
      );
      const index = this.state.selectedMonths.indexOf(month);
      if (index > -1) {
        this.state.selectedMonths.splice(index, 1);
      } else {
        this.state.selectedMonths.push(month);
      }
      if (this.state.selectedMonths.length === 0) {
        this.state.selectedMonths = ["all"];
      }
    }
    this.updateMonthSelection();
  }

  /**
   * 적용
   */
  apply() {
    this.updateDisplay();
    this.close();
    this.onApplyCallback(this.state.selectedYear, this.state.selectedMonths);
  }

  /**
   * 표시 업데이트
   */
  updateDisplay() {
    if (!this.elements.input) return;

    if (this.state.selectedYear === "all") {
      this.elements.input.value = "";
      this.elements.input.placeholder = "전체";
      this.toggleClearButton(false);
    } else {
      this.elements.input.value = this.formatDisplayText();
      this.elements.input.placeholder = "";
      this.toggleClearButton(true);
    }
  }

  /**
   * 표시 텍스트 포맷팅
   */
  formatDisplayText() {
    let text = `${this.state.selectedYear}년`;
    if (
      this.state.selectedMonths.length > 0 &&
      !this.state.selectedMonths.includes("all")
    ) {
      const monthTexts = this.state.selectedMonths
        .sort((a, b) => parseInt(a) - parseInt(b))
        .map((m) => `${m}월`)
        .join(", ");
      text += ` ${monthTexts}`;
    } else if (this.state.selectedMonths.includes("all")) {
      text += " 전체";
    }
    return text;
  }

  /**
   * 초기화 버튼 표시/숨김
   */
  toggleClearButton(show) {
    if (!this.elements.clearBtn) return;
    this.elements.clearBtn.style.display = show ? "flex" : "none";
  }

  /**
   * 연도 선택 상태 업데이트
   */
  updateYearSelection() {
    if (!this.elements.yearItems) return;

    let selectedItem = null;
    this.elements.yearItems.forEach((item) => {
      if (item.dataset.year === this.state.selectedYear) {
        item.classList.add("is-selected");
        selectedItem = item;
      } else {
        item.classList.remove("is-selected");
      }
    });

    if (selectedItem && this.elements.yearList) {
      this.scrollToYearItem(selectedItem, this.elements.yearList);
    }
  }

  /**
   * 월 선택 상태 업데이트
   */
  updateMonthSelection() {
    if (!this.elements.monthBtns) return;

    this.elements.monthBtns.forEach((btn) => {
      const month = btn.dataset.month;
      if (this.state.selectedMonths.includes(month)) {
        btn.classList.add("is-selected");
      } else {
        btn.classList.remove("is-selected");
      }
    });
  }

  /**
   * 모달 레이아웃 업데이트
   */
  updatePickerLayout(year) {
    if (!this.elements.picker) return;

    if (year !== "all") {
      this.expandPicker();
      if (this.elements.inner) {
        this.elements.inner.classList.remove("is-full-width");
      }
      this.showMonthPanel();
    } else {
      this.collapsePicker();
      if (this.elements.inner) {
        this.elements.inner.classList.add("is-full-width");
      }
      this.hideMonthPanel();
    }
  }

  /**
   * 모달 확장
   */
  expandPicker() {
    if (this.elements.picker) {
      this.elements.picker.classList.add("is-expanded");
    }
  }

  /**
   * 모달 축소
   */
  collapsePicker() {
    if (this.elements.picker) {
      this.elements.picker.classList.remove("is-expanded");
    }
  }

  /**
   * 월 패널 표시
   */
  showMonthPanel() {
    if (!this.elements.monthPanel) return;

    if (this.elements.monthPanel.classList.contains("is-hidden")) {
      this.elements.monthPanel.style.display = "flex";
      requestAnimationFrame(() => {
        this.elements.monthPanel.classList.remove("is-hidden");
      });
    } else {
      this.elements.monthPanel.classList.remove("is-hidden");
    }
  }

  /**
   * 월 패널 숨김
   */
  hideMonthPanel() {
    if (!this.elements.monthPanel) return;

    this.elements.monthPanel.classList.add("is-hidden");
    setTimeout(() => {
      if (this.state.selectedYear === "all") {
        this.elements.monthPanel.style.display = "none";
      }
    }, 300);
  }

  /**
   * 연도 항목으로 스크롤
   */
  scrollToYearItem(item, container) {
    if (!item || !container) return;

    const itemOffsetTop = item.offsetTop;
    const itemHeight = item.offsetHeight;
    const containerHeight = container.clientHeight;
    const targetScroll = itemOffsetTop - containerHeight / 2 + itemHeight / 2;

    const startScroll = container.scrollTop;
    const scrollDistance = targetScroll - startScroll;
    let startTime = null;
    const duration = 400;

    const animateScroll = (timestamp) => {
      if (!startTime) startTime = timestamp;
      const elapsed = timestamp - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const easeOutCubic = 1 - Math.pow(1 - progress, 3);
      const currentScroll = startScroll + scrollDistance * easeOutCubic;
      container.scrollTop = currentScroll;

      if (progress < 1) {
        requestAnimationFrame(animateScroll);
      }
    };

    requestAnimationFrame(animateScroll);
  }
}

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
      months: ["all"],
      search: "",
      page: 1,
    };

    this.elements = {
      list: document.getElementById("whatsonList"),
      count: document.getElementById("whatsonCount"),
      pagination: document.getElementById("whatsonPagination"),
      searchInput: document.getElementById("whatsonSearchInput"),
      searchBtn: document.getElementById("whatsonSearchInput"),
      searchReset: document.getElementById("whatsonSearchReset"),
      venueRadios: document.querySelectorAll('input[name="venue"]'),
      statusRadios: document.querySelectorAll('input[name="status"]'),
      genreRadios: document.querySelectorAll('input[name="genre"]'),
    };

    // 필수 DOM 요소가 없으면 리턴
    if (!this.elements.list) {
      return;
    }

    // 연도/월 선택기 인스턴스 생성 (의존성 주입)
    this.yearMonthPicker = new YearMonthPicker(
      "yearMonthFilterContainer",
      (year, months) => this.handleYearMonthChange(year, months)
    );

    this.debounceTimer = null;
    this.init();
  }

  /**
   * 초기화
   */
  async init() {
    this.bindEvents();
    // 연도/월 선택기 초기값 동기화
    this.syncYearMonthPicker();
    await this.fetchWorks();
  }

  /**
   * 연도/월 선택기 초기값 동기화
   */
  syncYearMonthPicker() {
    if (this.yearMonthPicker && this.yearMonthPicker.setValue) {
      this.yearMonthPicker.setValue(this.state.year, this.state.months);
    }
  }

  /**
   * 연도/월 변경 핸들러 (콜백)
   */
  handleYearMonthChange(year, months) {
    this.state.year = year;
    this.state.months = months;
    this.state.page = 1;
    this.fetchWorks();
  }

  /**
   * 이벤트 바인딩
   */
  bindEvents() {
    // 검색어 입력 (디바운스)
    if (this.elements.searchInput) {
      this.elements.searchInput.addEventListener("input", () => {
        this.handleSearchDebounce();
      });
    }

    // 검색 버튼
    if (this.elements.searchBtn) {
      this.elements.searchBtn.addEventListener("click", () => {
        this.handleSearch();
      });
    }

    // 검색 초기화
    if (this.elements.searchReset) {
      this.elements.searchReset.addEventListener("click", () => {
        this.handleReset();
      });
    }

    // 공연장 필터
    if (this.elements.venueRadios && this.elements.venueRadios.length > 0) {
      this.elements.venueRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
          if (radio.checked) {
            this.state.venue = radio.value;
            this.state.page = 1;
            this.fetchWorks();
          }
        });
      });
    }

    // 진행현황 필터
    if (this.elements.statusRadios && this.elements.statusRadios.length > 0) {
      this.elements.statusRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
          if (radio.checked) {
            this.state.status = radio.value;
            this.state.page = 1;
            this.fetchWorks();
          }
        });
      });
    }

    // 장르 필터
    if (this.elements.genreRadios && this.elements.genreRadios.length > 0) {
      this.elements.genreRadios.forEach((radio) => {
        radio.addEventListener("change", () => {
          if (radio.checked) {
            this.state.genre = radio.value;
            this.state.page = 1;
            this.fetchWorks();
          }
        });
      });
    }
  }

  /**
   * 검색어 입력 처리 (디바운스)
   */
  handleSearchDebounce() {
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => {
      this.handleSearch();
    }, 500);
  }

  /**
   * 검색 처리
   */
  handleSearch() {
    if (this.elements.searchInput) {
      this.state.search = this.elements.searchInput.value.trim();
    }
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

    if (this.elements.searchInput) {
      this.elements.searchInput.value = "";
    }
    if (this.elements.venueRadios && this.elements.venueRadios.length > 0) {
      this.elements.venueRadios.forEach((r) => (r.checked = r.value === "all"));
    }
    if (this.elements.statusRadios && this.elements.statusRadios.length > 0) {
      this.elements.statusRadios.forEach(
        (r) => (r.checked = r.value === "all")
      );
    }
    if (this.elements.genreRadios && this.elements.genreRadios.length > 0) {
      this.elements.genreRadios.forEach((r) => (r.checked = r.value === "all"));
    }

    // 연도/월 선택기 초기화
    if (this.yearMonthPicker) {
      this.yearMonthPicker.reset();
    }

    this.fetchWorks();
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
      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        this.renderWorks(data.data);
        this.renderPagination(data.pagination);
        this.updateCount(data.pagination.total);
      } else {
        throw new Error(data.message || "데이터를 불러올 수 없습니다.");
      }
    } catch (error) {
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
    if (!this.elements.list) return;

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
            <img src="${thumbnail}" alt="${this.escapeHtml(work.title)}">
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
    if (!this.elements.pagination) return;

    const { currentPage, lastPage } = pagination;
    const side = 2; // 좌우 표시할 개수
    const start = Math.max(1, currentPage - side);
    const end = Math.min(lastPage, currentPage + side);

    let html = '<div class="no-pagination">';

    // 맨 처음 버튼
    const firstDisabled = currentPage === 1 || lastPage === 1;
    html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --first" data-page="1" ${
      firstDisabled ? 'style="pointer-events: none; opacity: 0.5;"' : ""
    }>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="11 17 6 12 11 7"></polyline>
        <polyline points="18 17 13 12 18 7"></polyline>
      </svg>
    </a>`;

    // 이전 버튼
    const prevDisabled = currentPage === 1 || lastPage === 1;
    html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --prev" data-page="${Math.max(
      1,
      currentPage - 1
    )}" ${prevDisabled ? 'style="pointer-events: none; opacity: 0.5;"' : ""}>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
    </a>`;

    // 페이지 번호들
    html += '<div class="no-pagination__numbers">';

    // 시작 부분 처리
    if (start > 1) {
      html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num" data-page="1">1</a>`;
      if (start > 2) {
        html += `<span class="no-pagination__dots">...</span>`;
      }
    }

    // 중앙 페이지 출력
    for (let i = start; i <= end; i++) {
      const activeClass = i === currentPage ? " is-active" : "";
      html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num${activeClass}" data-page="${i}">${i}</a>`;
    }

    // 끝 부분 처리
    if (end < lastPage) {
      if (end < lastPage - 1) {
        html += `<span class="no-pagination__dots">...</span>`;
      }
      html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--num" data-page="${lastPage}">${lastPage}</a>`;
    }

    html += "</div>";

    // 다음 버튼
    const nextDisabled = currentPage === lastPage || lastPage === 1;
    html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --next" data-page="${Math.min(
      lastPage,
      currentPage + 1
    )}" ${nextDisabled ? 'style="pointer-events: none; opacity: 0.5;"' : ""}>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6"></polyline>
      </svg>
    </a>`;

    // 맨 끝 버튼
    const lastDisabled = currentPage === lastPage || lastPage === 1;
    html += `<a href="javascript:void(0);" class="no-pagination__link no-pagination__link--arrow --last" data-page="${lastPage}" ${
      lastDisabled ? 'style="pointer-events: none; opacity: 0.5;"' : ""
    }>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="13 17 18 12 13 7"></polyline>
        <polyline points="6 17 11 12 6 7"></polyline>
      </svg>
    </a>`;

    html += "</div>";
    this.elements.pagination.innerHTML = html;

    // 페이지네이션 버튼 이벤트
    this.elements.pagination
      .querySelectorAll(".no-pagination__link[data-page]")
      .forEach((link) => {
        link.addEventListener("click", (e) => {
          e.preventDefault();
          const page = parseInt(link.dataset.page);
          if (page && page !== this.state.page && !link.style.pointerEvents) {
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
    if (this.elements.count) {
      this.elements.count.innerHTML = `총 <strong>${total.toLocaleString()}</strong>건`;
    }
  }

  /**
   * 에러 렌더링
   */
  renderError(message) {
    if (this.elements.list) {
      this.elements.list.innerHTML = `
        <li class="no-sub-whatson-item" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
          <span class="f-body-2 --regular">${message}</span>
        </li>
      `;
    }
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

// What's ON 목록 초기화
function initWhatsonList() {
  const listElement = document.getElementById("whatsonList");
  if (!listElement) return;

  try {
    new WhatsOnList();
  } catch (error) {
    console.error("WhatsOnList 초기화 오류:", error);
  }
}
