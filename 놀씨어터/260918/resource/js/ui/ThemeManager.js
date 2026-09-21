export class ThemeManager {
  constructor(buttonId = "theme-toggle") {
    this.button = document.getElementById(buttonId);
    this.themes = { light: "light", dark: "dark" };
    this.defaultTheme = this.themes.dark;
    this.storageKey = "theme";
    this.transitionClass = "is-transition";

    if (!this.button) return;

    this.init();
  }

  getCurrentTheme() {
    return localStorage.getItem(this.storageKey) || this.defaultTheme;
  }

  setTheme(theme) {
    if (!Object.values(this.themes).includes(theme)) {
      theme = this.defaultTheme;
    }

    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem(this.storageKey, theme);
    this.updateButton(theme);
    this.toggleImages(theme);
    this.updateLordIcons(theme);
  }

  toggleImages(theme) {
    // 1) --toggle-images 컨테이너 내부의 --light-img, --dark-img 처리
    const toggleContainers = document.querySelectorAll(".--toggle-images");

    toggleContainers.forEach((container) => {
      const lightImg = container.querySelector(".--light-img");
      const darkImg = container.querySelector(".--dark-img");

      if (theme === "light") {
        if (lightImg) lightImg.style.display = "block";
        if (darkImg) darkImg.style.display = "none";
      } else {
        if (lightImg) lightImg.style.display = "none";
        if (darkImg) darkImg.style.display = "block";
      }
    });

    // 2) light, dark 클래스를 직접 가진 모든 요소 처리
    const lightElements = document.querySelectorAll(".light");
    const darkElements = document.querySelectorAll(".dark");

    if (theme === "light") {
      lightElements.forEach((el) => (el.style.display = "block"));
      darkElements.forEach((el) => (el.style.display = "none"));
    } else {
      lightElements.forEach((el) => (el.style.display = "none"));
      darkElements.forEach((el) => (el.style.display = "block"));
    }
  }

  updateButton(theme) {
    const config = {
      dark: {
        label: "라이트 모드로 전환",
      },
      light: {
        label: "다크 모드로 전환",
      },
    };

    const { label } = config[theme] || config.light;
    this.button.setAttribute("aria-label", label);
  }

  updateLordIcons(theme) {
    const lordIcons = document.querySelectorAll("lord-icon.lord-icon-theme");

    let primaryColor, secondaryColor;
    if (theme === "dark") {
      primaryColor = "#fff";
      secondaryColor = "#fff";
    } else {
      primaryColor = "#3549ff"; // primary-def 색상
      secondaryColor = "#000";
    }

    lordIcons.forEach((icon) => {
      const colorsValue = `primary:${primaryColor},secondary:${secondaryColor}`;
      if (icon.lottie) {
        // Lord Icon이 이미 로드된 경우
        icon.colors = colorsValue;
      } else {
        // 아직 로드되지 않은 경우 속성 업데이트
        icon.setAttribute("colors", colorsValue);
      }
    });
  }

  toggle() {
    const current = this.getCurrentTheme();
    const next =
      current === this.themes.dark ? this.themes.light : this.themes.dark;
    this.setTheme(next);
  }

  init() {
    if (!localStorage.getItem(this.storageKey)) {
      localStorage.setItem(this.storageKey, this.defaultTheme);
    }

    this.setTheme(this.getCurrentTheme());
    document.documentElement.classList.remove(this.transitionClass);
    this.button.addEventListener("click", () => this.toggle());

    // Lord Icon 로드 후 색상 업데이트
    this.initLordIcons();
  }

  initLordIcons() {
    // Lord Icon이 로드될 때까지 대기
    const checkLordIcons = () => {
      const lordIcons = document.querySelectorAll("lord-icon.lord-icon-theme");
      if (lordIcons.length > 0) {
        const theme = this.getCurrentTheme();
        this.updateLordIcons(theme);
      }
    };

    // DOMContentLoaded 후 확인
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", checkLordIcons);
    } else {
      // 이미 로드된 경우
      setTimeout(checkLordIcons, 100);
    }

    // Lord Icon 스크립트 로드 후에도 확인
    window.addEventListener("load", () => {
      setTimeout(checkLordIcons, 500);
    });
  }
}
