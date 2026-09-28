/**
 * TabManager
 * 탭 UI 관리 클래스
 */
class TabManager {
  constructor(containerSelector, lenisInstance = null) {
    this.container = document.querySelector(containerSelector);
    if (!this.container) return;

    this.tabs = this.container.querySelectorAll(".no-sub-ui-tab");
    this.panels = this.container.querySelectorAll(".no-sub-ui-panel");
    this.rightContainer = this.container.querySelector(".no-sub-ui-right");
    this.lenis = lenisInstance;

    if (!this.tabs.length || !this.panels.length || !this.rightContainer)
      return;

    this.init();
  }

  /**
   * 초기화
   */
  init() {
    // URL 쿼리스트링에서 초기 탭 설정
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get("tab");

    // 초기 탭 설정
    if (initialTab) {
      const targetTab = Array.from(this.tabs).find(
        (tab) => tab.getAttribute("data-target") === initialTab
      );
      if (targetTab) {
        this.activateTab(targetTab, false);
      } else {
        // 쿼리스트링의 탭이 없으면 초기 활성 탭 사용
        const activeTab = this.container.querySelector(
          ".no-sub-ui-tab.is-active"
        );
        if (activeTab) {
          this.updateContainerHeight();
        }
      }
    } else {
      // 쿼리스트링이 없으면 초기 활성 탭 사용
      const activeTab = this.container.querySelector(
        ".no-sub-ui-tab.is-active"
      );
      if (activeTab) {
        this.updateContainerHeight();
      }
    }

    // 리사이즈 이벤트
    this.setupResizeListener();

    // 탭 클릭 이벤트
    this.setupTabListeners();
  }

  /**
   * 활성 패널 높이 설정
   */
  updateContainerHeight() {
    const activePanel = this.rightContainer.querySelector(
      ".no-sub-ui-panel.is-active"
    );
    if (!activePanel) return;

    // panel-inner 요소의 높이 측정
    const panelInner = activePanel.querySelector(".no-sub-ui-panel-inner");
    if (panelInner) {
      // 강제 리플로우
      void panelInner.offsetHeight;
      
      // 높이 측정 및 설정 (패널과 컨테이너 모두 설정)
      const height = panelInner.scrollHeight;
      activePanel.style.height = `${height}px`;
      this.rightContainer.style.height = `${height}px`;
      
      // 이미지가 있는 경우, 이미지 로드 후 높이 재계산
      const images = activePanel.querySelectorAll("img");
      if (images.length > 0) {
        let loadedCount = 0;
        const totalImages = images.length;
        
        const updateHeightAfterImages = () => {
          loadedCount++;
          if (loadedCount === totalImages) {
            // 모든 이미지 로드 완료 후 높이 재계산
            setTimeout(() => {
              void panelInner.offsetHeight;
              const newHeight = panelInner.scrollHeight;
              activePanel.style.height = `${newHeight}px`;
              this.rightContainer.style.height = `${newHeight}px`;
            }, 50);
          }
        };
        
        images.forEach((img) => {
          if (img.complete) {
            updateHeightAfterImages();
          } else {
            img.addEventListener("load", updateHeightAfterImages, { once: true });
            img.addEventListener("error", updateHeightAfterImages, { once: true });
          }
        });
      }
    } else {
      // panel-inner가 없으면 패널의 높이 사용
      const height = activePanel.scrollHeight;
      activePanel.style.height = `${height}px`;
      this.rightContainer.style.height = `${height}px`;
    }
  }

  /**
   * 탭 활성화
   * @param {HTMLElement} targetTab - 활성화할 탭 요소
   * @param {boolean} updateUrl - URL 업데이트 여부 (기본값: true)
   */
  activateTab(targetTab, updateUrl = true) {
    const target = targetTab.getAttribute("data-target");
    if (!target) return;

    // 탭 활성화 토글
    this.tabs.forEach((t) => t.classList.remove("is-active"));
    targetTab.classList.add("is-active");

    // 패널 전환
    let activePanel = null;
    this.panels.forEach((panel) => {
      const panelKey = panel.getAttribute("data-panel");
      if (panelKey === target) {
        // 비활성 패널의 높이를 0으로 초기화
        panel.style.height = "0px";
        panel.classList.add("is-active");
        activePanel = panel;
      } else {
        panel.classList.remove("is-active");
        // 비활성 패널의 높이를 0으로 설정
        panel.style.height = "0px";
      }
    });

    // 쿼리스트링 업데이트
    if (updateUrl) {
      const url = new URL(window.location);
      url.searchParams.set("tab", target);
      window.history.pushState({}, "", url);
    }

    // 페이지 최상단(0)으로 스크롤 (모바일 최적화)
    if (activePanel) {
      const isMobile = window.innerWidth <= 1024;
      console.log('TabManager - 모바일 체크:', isMobile, '화면 너비:', window.innerWidth);
      
      if (isMobile) {
        // 모바일: window.scrollTo 사용
        console.log('모바일 - window.scrollTo 사용');
        window.scrollTo({ top: 0, behavior: "smooth" });
      } else {
        // 데스크톱: Lenis 사용
        if (window.lenis) {
          console.log('데스크톱 - window.lenis 사용');
          window.lenis.scrollTo(0, {
            duration: 0.8,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
          });
        } else if (this.lenis) {
          console.log('데스크톱 - this.lenis 사용');
          this.lenis.scrollTo(0, {
            duration: 0.8,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
          });
        } else {
          console.log('데스크톱 - Lenis 없음, window.scrollTo 사용');
          window.scrollTo({ top: 0, behavior: "smooth" });
        }
      }
    }

    // 높이 업데이트 (애니메이션 완료 후)
    // 즉시 한 번, 그리고 애니메이션 완료 후 한 번 더 호출하여 정확도 향상
    this.updateContainerHeight();
    setTimeout(() => {
      this.updateContainerHeight();
    }, 350); // transition duration에 맞춰 조정
  }

  /**
   * 리사이즈 이벤트 리스너 설정
   */
  setupResizeListener() {
    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        this.updateContainerHeight();
      }, 100);
    });
  }

  /**
   * 탭 클릭 이벤트 리스너 설정
   */
  setupTabListeners() {
    this.tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        this.activateTab(tab);
      });
    });
  }
}

export { TabManager };
