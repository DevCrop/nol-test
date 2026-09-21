gsap.registerPlugin(ScrollTrigger);

function tooltip() {
  const button = document.querySelector("#button");
  const tooltip = document.querySelector("#tooltip");

  if (!button || !tooltip) {
    return;
  }

  // 설정 객체로 분리
  const config = {
    mobile: {
      placement: "top",
      offset: [0, 7],
    },
    desktop: {
      placement: "right",
      offset: [-64, 8],
    },
  };

  // 미디어 쿼리 매칭 함수
  const getDeviceConfig = () =>
    window.matchMedia("(max-width: 544px)").matches
      ? config.mobile
      : config.desktop;

  // Popper 인스턴스 생성
  const { placement, offset } = getDeviceConfig();
  const popperInstance = Popper.createPopper(button, tooltip, {
    placement,
    modifiers: [
      {
        name: "offset",
        options: { offset },
      },
      {
        name: "arrow",
        options: {
          element: "[data-popper-arrow]",
        },
      },
    ],
  });

  // 이벤트 핸들러 함수
  const handlers = {
    show() {
      tooltip.setAttribute("data-show", "");
      tooltip.style.cssText = "opacity: 1; transform: translateX(0)";
      this.updatePopper(true);
    },

    hide() {
      tooltip.removeAttribute("data-show");
      tooltip.style.cssText = "opacity: 0; transform: translateX(-10px)";
      this.updatePopper(false);
    },

    updatePopper(enabled) {
      popperInstance.setOptions((options) => ({
        ...options,
        modifiers: [...options.modifiers, { name: "eventListeners", enabled }],
      }));
      popperInstance.update();
    },
  };

  // 이벤트 리스너 등록
  const events = {
    show: ["mouseenter", "focus"],
    hide: ["mouseleave", "blur"],
  };

  events.show.forEach((event) =>
    button.addEventListener(event, handlers.show.bind(handlers))
  );

  events.hide.forEach((event) =>
    button.addEventListener(event, handlers.hide.bind(handlers))
  );

  // 반응형 처리
  window.addEventListener("resize", () => {
    const { placement, offset } = getDeviceConfig();
    popperInstance.setOptions((options) => ({
      ...options,
      placement,
      modifiers: [
        ...options.modifiers,
        { name: "offset", options: { offset } },
      ],
    }));
  });
}

function subPageIntroAnimation() {
	
  const splitText = new SplitType(".show-1 .txt h2", { types: "chars" });
  const gradientImg = document.querySelectorAll(".move-gradient img");
  const tl = gsap.timeline();

  // 초기 상태 설정
  gsap.set(".show-1", {
    rotate: "180deg",
  });

  gsap.set(splitText.chars, {
    opacity: 0,
    y: "100%",
    visibility: "hidden",
  });
// 인트로 애니메이션
tl.to(".show-1", {
  y: "-50%",
  rotate: "0deg",
  opacity: 1,
  duration: 1.6,
  ease: "expo.out",
})
.to(".show-1", {
  width: "100%",
  height: "100vh",
  duration: 1.4,
  y: "-50%",
  ease: "expo.out",
}, "start")
    .to(gradientImg, {
      opacity: 0.72,
    })
    .to(
      ".show-1",
      {
        borderWidth: 0,
        duration: 2.4,
        ease: "expo.out",
      },
      "start"
    )
    .to(
      ".show-1 img",
      {
        scale: 1.05,
        duration: 3,
        ease: "none",
      },
      "start"
    )
    .to(
      ".show-1 .img",
      {
        opacity: 1,
        duration: 2.4,
        ease: "expo.out",
      },
      "start"
    )
    .to(
      splitText.chars,
      {
        opacity: 1,
        visibility: "visible",
        duration: 1,
        y: 0,
        stagger: 0.08,
        ease: "power3.out",
      },
      "start"
    )
    .to(
      ".show-1-bg",
      {
        opacity: 1,
        duration: 1,
        ease: "power3.out",
      },
      "start"
    )
    .to(
      ".show-1 .scroll-down",
      {
        opacity: 1,
        visibility: "visible",
        duration: 1,
        ease: "power3.out",
      },
      "start"
    );
  let mm = gsap.matchMedia();

  mm.add("(min-width: 1024px)", () => {
    gsap
      .timeline({
        scrollTrigger: {
          trigger: ".no-sub-intro-wrap",
          start: "top top",
          end: "bottom top",
          scrub: 1,
          pin: true,
          anticipatePin: 1,
        },
      })
      .to(".no-sub-intro-bluesquare", {
        y: 0,
        ease: "power2.out",
      })
      .to(".no-sub-intro-bluesquare > ul:nth-child(even)", {
        y: "-100vh",
        ease: "power2.out",
      });

    return () => {
      // Cleanup function: 필요하면 여기에 제거 로직 추가
    };
  });
}

function divAnimation() {
  const intro = document.getElementById("intro");
  if (!intro) return;

  const colors = ["#4326CE", "#018EC8"];
  const shapes = [];
  const minDistance = 200; // Minimum distance between shapes
  const numShapes = 4; // Number of shapes
  const maxRadius = 150; // Maximum radius
  const minRadius = 50; // Minimum radius

  // Helper function to calculate distance between two points
  function calculateDistance(x1, y1, x2, y2) {
    return Math.sqrt((x1 - x2) ** 2 + (y1 - y2) ** 2);
  }

  // Create and position shapes ensuring minimum distance
  for (let i = 0; i < numShapes; i++) {
    let newShape;
    let isValidPosition;

    do {
      isValidPosition = true;

      const radius = Math.random() * (maxRadius - minRadius) + minRadius;

      newShape = {
        x: Math.random() * (window.innerWidth - radius * 2) + radius,
        y: Math.random() * (window.innerHeight - radius * 2) + radius,
        radius,
        color: colors[Math.floor(Math.random() * colors.length)],
        dx: Math.random() * 4 - 2, // Speed in x direction
        dy: Math.random() * 4 - 2, // Speed in y direction
      };

      // Check if the new shape is too close to any existing shape
      for (const shape of shapes) {
        const distance = calculateDistance(
          newShape.x,
          newShape.y,
          shape.x,
          shape.y
        );
        if (distance < minDistance + newShape.radius + shape.radius) {
          isValidPosition = false;
          break;
        }
      }
    } while (!isValidPosition);

    shapes.push(newShape);

    // Create the shape as a div element
    const div = document.createElement("div");
    div.classList.add("shape");
    div.style.width = `${newShape.radius * 2}px`;
    div.style.height = `${newShape.radius * 2}px`;
    div.style.backgroundColor = newShape.color;
    div.style.left = `${newShape.x - newShape.radius}px`;
    div.style.top = `${newShape.y - newShape.radius}px`;
    div.style.filter = `blur(120px)`;
    div.style.webkitFilter = `blur(120px)`; // Safari 호환성 추가

    intro.appendChild(div);
    newShape.element = div;
  }

  // Animate shapes
  function animateShapes() {
    shapes.forEach((shape) => {
      shape.x += shape.dx;
      shape.y += shape.dy;

      // Bounce shapes off edges
      if (
        shape.x - shape.radius < 0 ||
        shape.x + shape.radius > window.innerWidth
      ) {
        shape.dx *= -1;
      }
      if (
        shape.y - shape.radius < 0 ||
        shape.y + shape.radius > window.innerHeight
      ) {
        shape.dy *= -1;
      }

      // Update shape position
      shape.element.style.transform = `translate(${shape.x - shape.radius}px, ${
        shape.y - shape.radius
      }px)`;
    });

    requestAnimationFrame(animateShapes);
  }

  animateShapes();
}

function startBluesquareAnimation() {
  let mm = gsap.matchMedia();

  mm.add("(min-width: 1024px)", () => {
    const bluesquareImg = document.querySelectorAll(
      ".no-sub-intro-bluesquare .img img"
    );
    const paragraphs = document.querySelectorAll(
      ".no-sub-intro-bluesquare .show-2-text p"
    );
    const logo = document.querySelectorAll(
      ".no-sub-intro-bluesquare .show-2-logo"
    );

    if (!bluesquareImg.length || !paragraphs.length || !logo.length) {
      console.warn("필요한 요소가 존재하지 않습니다.");
      return;
    }

    // 타임라인 생성 및 ScrollTrigger 적용
    const show2Tl = gsap.timeline({
      scrollTrigger: {
        trigger: ".no-sub-intro-bluesquare", // 해당 섹션 트리거
        start: "top 90%", // 섹션의 시작점
        end: "bottom center", // 섹션의 끝점
        toggleActions: "play none none none", // 스크롤 시 애니메이션 재생
      },
    });

    // 1. 이미지 올라오기
    show2Tl.to(bluesquareImg, {
      y: "0%",
      opacity: 1,
      duration: 1.5,
      ease: "power4.out",
    });

    // 2. 로고 보이기
    show2Tl.to(
      logo,
      {
        y: "0%",
        opacity: 1,
        duration: 1.2,
        ease: "power4.out",
      },
      "<0.5" // 이미지 애니메이션 종료 0.5초 전에 시작
    );

    // 3. 이미지 커지면서 p 요소 올라오기
    show2Tl
      .to(
        bluesquareImg,
        {
          scale: 1.1,
          duration: 1.6,
          ease: "power3.out",
        },
        "<" // 로고와 동시에 시작
      )
      .to(
        paragraphs,
        {
          y: "0%",
          opacity: 1,
          duration: 1,
          ease: "power3.out",
          stagger: 0.2, // 요소 간의 간격 설정
        },
        "<" // 이미지 커지는 동안 p 요소 애니메이션도 실행
      );

    return () => {
      // 필요 시 cleanup 가능 (예: 요소 초기화)
    };
  });
}

function pallexImage() {
  const images = document.querySelector(".no-interpark-img img");
  console.log(images);
  gsap.set(images, {
    scale: 1.3,
  });

  // 애니메이션 설정
  gsap.fromTo(
    images,
    { yPercent: -10 },
    {
      yPercent: 10,
      scrollTrigger: {
        trigger: images,
        start: "top bottom",
        end: "bottom top",
        scrub: true,
      },
    }
  );
}

function moreInfo() {
  const moreInfoButton = document.querySelector(
    ".more-info-wrap-button button"
  );
  const wrapper = document.querySelector(".more-info-wrap");
  const buttonText = document.querySelector(".button-text");

  if (!moreInfoButton) {
    return;
  }

  moreInfoButton.addEventListener("click", function () {
    wrapper.classList.toggle("active");
    moreInfoButton.classList.toggle("active");

    if (wrapper.classList.contains("active")) {
      buttonText.textContent = "페이지 닫기";
    } else {
      buttonText.textContent = "더보기";
    }
  });
}

function inputLineAnimation() {
  const radioContainer = document.querySelector(".no-form-radio-line");
  const rect = document.querySelector(".no-form-radio-line-rect");

  if (!radioContainer || !rect) return;

  const radioInputs = radioContainer.querySelectorAll("input[type='radio']");

  radioInputs.forEach((radio) => {
    radio.addEventListener("change", () => {
      const label = radio.closest("label");

      if (label) {
        const labelBounds = label.getBoundingClientRect();
        const containerBounds = radioContainer.getBoundingClientRect();

        gsap.to(rect, {
          x: labelBounds.left - containerBounds.left, // 라벨의 x 위치
          width: labelBounds.width, // 라벨의 너비에 맞춤
          duration: 0.8,
          ease: "power3.out",
        });
      }
    });
  });

  // 초기 rect 위치 설정 (첫 번째 선택된 라디오 버튼)
  const selectedRadio = radioContainer.querySelector(
    "input[type='radio']:checked"
  );
  if (selectedRadio) {
    const label = selectedRadio.closest("label");
    if (label) {
      const labelBounds = label.getBoundingClientRect();
      const containerBounds = radioContainer.getBoundingClientRect();

      gsap.set(rect, {
        x: labelBounds.left - containerBounds.left,
        width: labelBounds.width,
      });
    }
  }
}

function header(lenis) {
  const headerEl = document.querySelector(`header`);
  const headerSearch = headerEl.querySelector(".no-header__search button");
  const searchView = document.querySelector(".no-search-view");
  const sitemapView = document.querySelector(".no-sitemap");
  const headerDarkModeToggle = headerEl.querySelector(".no-header__theme");
  const headerToggleButton = headerEl.querySelector(".no-header__toggle");
  const headerMenu = headerEl.querySelector("nav"); // 메뉴 엘리먼트 선택
  const headLine = document.querySelector(".no-headline");

  function init() {
    saveTheme();
    darkMode();
    searchToggle();
    toggleMode();
    menuOver();
    menuLeave();

    window.addEventListener("scroll", () => {
      if (window.scrollY > 80) {
        headerEl.classList.add("shadow");
        if (headLine) {
          headerEl.classList.add("hasHeadLine");
        }
      } else {
        headerEl.classList.remove("shadow");
        headerEl.classList.remove("hasHeadLine");
      }
    });
  }

  // 메뉴 마우스 오버
  function menuOver() {
    headerMenu.addEventListener("mouseenter", () => {
      headerEl.classList.add("active");
      gsap.to(headerMenu, {
        duration: 0.5,
        ease: "power3.out",
      });
    });
  }

  // 메뉴 마우스 리브
  function menuLeave() {
    headerMenu.addEventListener("mouseleave", () => {
      headerEl.classList.remove("active");
      gsap.to(headerMenu, {
        duration: 0.5,
        ease: "power3.out",
      });
    });
  }

  // 쿠키 설정 및 읽기 함수
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
  }

  function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (let i = 0; i < cookies.length; i++) {
      const [key, value] = cookies[i].split("=");
      if (key === name) {
        return value;
      }
    }
    return null;
  }

  // 테마 저장 및 적용 함수
  function saveTheme() {
    const savedTheme = getCookie("theme");
    if (savedTheme) {
      document.documentElement.setAttribute("data-theme", savedTheme);
      toggleImages(savedTheme);
    } else {
      document.documentElement.setAttribute("data-theme", "dark");
      setCookie("theme", "dark", 365);
      toggleImages("dark");
    }
  }

  function setTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    setCookie("theme", theme, 365);
    toggleImages(theme);
  }

  // 다크 모드 이미지 관리
  function toggleImages(theme) {
    const toggleImagesContainers =
      document.querySelectorAll(".--toggle-images");

    toggleImagesContainers.forEach((container) => {
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
  }

  // 다크 모드 토글
  function darkMode() {
    if (!headerDarkModeToggle) return;

    const lightButton = headerDarkModeToggle.querySelector(
      "[data-toggle-light]"
    );
    const darkButton = headerDarkModeToggle.querySelector("[data-toggle-dark]");
    const circle = headerDarkModeToggle.querySelector(".circle");

	const updateTransform = (button) => {
	  const isSmallScreen = window.matchMedia("(max-width: 768px)").matches;

	  // 기존 토글 버튼 UI 이동
	  if (button === lightButton) {
		circle.style.transform = isSmallScreen
		  ? "translate(10px, -12px)"
		  : "translate(10px, -16px)";
		setTheme("light");

		// ✅ 라이트 모드일 때 SVG 색상 변경
		updateSvgFill("light");

	  } else if (button === darkButton) {
		circle.style.transform = isSmallScreen
		  ? "translate(43px, -12px)"
		  : "translate(50px, -16px)";
		setTheme("dark");

		// ✅ 다크 모드일 때 SVG 색상 변경
		updateSvgFill("dark");
	  }
	};

	function updateSvgFill(theme) {
	  const svgPaths = document.querySelectorAll(
		".no-header__etc--interpark svg path"
	  );

	  svgPaths.forEach((path) => {
		const fill = path.getAttribute("fill");
		if (theme === "light") {
		  if (fill && (fill.toLowerCase() === "#fff" || fill.toLowerCase() === "white")) {
			path.setAttribute("fill", "#000");
		  }
		} else {
		  if (fill && fill.toLowerCase() === "#000") {
			path.setAttribute("fill", "#fff");
		  }
		}
	  });
	}




    const setActiveButton = (button) => {
      lightButton.classList.remove("active");
      darkButton.classList.remove("active");
      button.classList.add("active");
      updateTransform(button);
    };

    const initTheme = () => {
      const savedTheme = getCookie("theme");
      if (savedTheme === "light") {
        setActiveButton(lightButton);
      } else {
        setActiveButton(darkButton);
      }
    };

    lightButton.addEventListener("click", () => {
      setActiveButton(lightButton);
    });

    darkButton.addEventListener("click", () => {
      setActiveButton(darkButton);
    });

    window.addEventListener("resize", () => {
      const activeButton = lightButton.classList.contains("active")
        ? lightButton
        : darkButton;
      updateTransform(activeButton);
    });

    initTheme();
  }

  // 검색 토글
  function searchToggle() {
    headerSearch.addEventListener("click", () => {
      const isSearched = headerEl.classList.contains("searched");

      resetState();

      if (!isSearched) {
        headerEl.classList.add("searched");
        searchView.classList.add("active");
        gsap.to(searchView.querySelector(".line"), {
          width: "100%",
          duration: 1,
          ease: "power3.out",
        });
      }
    });
  }

  // 토글 모드
  function toggleMode() {
    headerToggleButton.addEventListener("click", () => {
      lenis.start();
      const isToggled = headerEl.classList.contains("toggle");
      const tl = gsap.timeline();
      tl.to(".no-sitemap .line", {
        width: "100%",
        duration: 1.6,
        ease: "power3.out",
        stagger: 0.2,
      })
        .to(
          ".no-sitemap__gnb--link-title span",
          { duration: 1.3, y: 0, ease: "power3.out" },
          0.3
        )
        .to(
          ".no-sitemap__lnb--item a",
          { duration: 1.2, y: 0, ease: "power3.out" },
          0.4
        );

      resetState();

      if (!isToggled) {
        headerToggleButton.classList.add("toggle");
        lenis.stop();
        console.log("lenis");
        headerEl.classList.add("toggle");
        sitemapView.classList.add("active");
      }
    });
  }

  // 상태 초기화
  function resetState() {
    headerEl.classList.remove("searched", "toggle");
    searchView.classList.remove("active");
    sitemapView.classList.remove("active");
    headerToggleButton.classList.remove("toggle");
  }

  // 초기화 실행
  init();
}

function initTheme() {
  const buttons = document.querySelectorAll(
    ".no-header__theme input[name=theme]"
  );

  function initLocalStorage() {
    if (!localStorage.getItem("theme")) {
      localStorage.setItem("theme", buttons[0].value);
    }

    const theme = localStorage.getItem("theme");
    buttons.forEach((button) => {
      button.removeAttribute("checked");
      if (button.value === theme) {
        button.checked = true;
      }
    });
    setTheme(theme);

    document.documentElement.classList.remove("is-transition");
  }

  function setTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);
  }

  buttons.forEach((button) => {
    button.addEventListener("change", () => {
      setTheme(button.value);

      console.log(button.value);
    });
  });

  initLocalStorage();
}

function marquee() {
  const marquees = document.querySelectorAll('[wb-data="marquee"]');

  if (!marquees.length) {
    return;
  }

  marquees.forEach((marquee) => {
    const duration = parseInt(marquee.getAttribute("duration"), 10) || 5;
    const marqueeContent = marquee.querySelector(".no-main-marquee__content");

    if (!marqueeContent) {
      return;
    }

    const marqueeContentClone = marqueeContent.cloneNode(true);
    marquee.append(marqueeContentClone);

    let tween;

    const playMarquee = () => {
      let progress = tween ? tween.progress() : 0;
      tween && tween.progress(0).kill();

      const width = parseInt(
        getComputedStyle(marqueeContent).getPropertyValue("width"),
        10
      );
      const gap = parseInt(
        getComputedStyle(marqueeContent).getPropertyValue("column-gap"),
        10
      );
      const distanceToTranslate = -1 * (gap + width);

      tween = gsap.fromTo(
        marquee.children,
        { x: 0 },
        { x: distanceToTranslate, duration, ease: "none", repeat: -1 }
      );
      tween.progress(progress);
    };

    playMarquee();

    function debounce(func) {
      let timer;
      return function (e) {
        if (timer) clearTimeout(timer);
        timer = setTimeout(
          () => {
            func();
          },
          500,
          e
        );
      };
    }

    window.addEventListener("resize", debounce(playMarquee));
    /*
    marquee.addEventListener("mouseover", () => {
      gsap.to(tween, { timeScale: 0.1, duration: 1 }); // 천천히 재생
    });

    marquee.addEventListener("mouseleave", () => {
      gsap.to(tween, { timeScale: 1, duration: 1 }); // 다시 원래 속도로
    });*/
  });
}

function setupModalTriggers(lenis) {
  const modalElements = {
    shinhan: {
      buttonSelector: ".shinhan-card .no-sub-floor-info-buttons button",
      modalSelector: ".no-modal-shinhan",
    },
    master: {
      buttonSelector: ".master-card .no-sub-floor-info-buttons button",
      modalSelector: ".no-modal-master",
    },
    privacy: {
      buttonSelector: ".no-modal-privacy-btn",
      modalSelector: ".no-modal-privacy",
    },
  };

  Object.keys(modalElements).forEach((key) => {
    const { buttonSelector, modalSelector } = modalElements[key];
    const buttons = document.querySelectorAll(buttonSelector);
    const modal = document.querySelector(modalSelector);
    const modalItems = modal?.querySelectorAll(".modal-item");
    const modalContainer = document.querySelector("#modal");

    if (!buttons.length || !modal || !modalItems?.length || !modalContainer)
      return;

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        const dataModalId = button.dataset.modalId;
        console.log(dataModalId);

        // Hide all modal items
        modalItems.forEach((item) => {
          item.classList.remove("visible");
        });

        // Stop Lenis scrolling
        if (lenis?.stop) lenis.stop();

        // Show the corresponding modal item based on data-modal-id
        const matchingModal = Array.from(modalItems).find(
          (item) => item.dataset.modalId === dataModalId
        );

        if (matchingModal) {
          matchingModal.classList.add("visible");
          modal.classList.add("visible");
          modalContainer.classList.add("visible"); // Add visible class to #modal
        }
      });
    });

    // Add close functionality
    const closeButton = modalContainer.querySelector(".modal-close-btn");
    if (closeButton) {
      closeButton.addEventListener("click", () => {
        modal.classList.remove("visible");
        modalContainer.classList.remove("visible"); // Remove visible class from #modal
        modalItems.forEach((item) => {
          item.classList.remove("visible");
        });

        // Resume Lenis scrolling
        if (lenis?.start) lenis.start();
      });
    }
  });
}

function tabManager(lenis) {
  function initTabClickEvents() {
    const tabButtons = document.querySelectorAll(".no-sub-tab button");
    const tabContents = document.querySelectorAll(
      ".no-sub-tab-contents > ul > li"
    );

    if (tabContents.length > 0) {
      tabContents[0].style.display = "block";
      activateNestedContent(tabContents[0]);
    }

    tabButtons.forEach((button, index) => {
      button.addEventListener("click", () => {
        activateTab(index, tabButtons, tabContents);
      });
    });
  }

  function activateTab(index, tabButtons, tabContents) {
    tabButtons.forEach((btn) => btn.classList.remove("active"));

    tabButtons[index].classList.add("active");

    tabContents.forEach((content) => {
      content.style.display = "none";
    });

    const activeContent = tabContents[index];
    activeContent.style.display = "block";
    activateNestedContent(activeContent);
    resetNestedButtons(activeContent);
  }

  function activateNestedContent(tabContent) {
    const nestedItems = tabContent.querySelectorAll(
      ".no-nest-tab-contents > ul > li"
    );

    if (nestedItems.length > 0) {
      nestedItems.forEach((item) => item.classList.remove("active"));
      nestedItems[0].classList.add("active");
    }
  }

  function resetNestedButtons(tabContent) {
    const nestedButtons = tabContent.querySelectorAll(
      ".no-nest-tab-wrap .no-nest-btn"
    );

    if (nestedButtons.length > 0) {
      nestedButtons.forEach((button) => button.classList.remove("active"));
      nestedButtons[0].classList.add("active");
    }
  }

  function initNoNestTabClickEvents() {
    const tabButtons = document.querySelectorAll(
      ".no-nest-tab-wrap .no-nest-btn"
    );
    const tabContents = Array.from(
      document.querySelectorAll(".no-nest-tab-contents > ul > li")
    );

    if (tabContents.length > 0) {
      tabContents.forEach((content, idx) => {
        content.classList.toggle("active", idx === 0); // Add 'active' class to the first content
      });
      tabButtons[0]?.classList.add("active"); // Add 'active' to the first button
    }

    tabButtons.forEach((button, index) => {
      button.addEventListener("click", () => {
        activateNoNestTab(index, tabButtons, tabContents);
      });
    });
  }

  function activateNoNestTab(index, tabButtons, tabContents) {
    tabButtons.forEach((btn) => btn.classList.remove("active"));

    tabButtons[index].classList.add("active");

    // Remove 'active' class from all contents
    tabContents.forEach((content) => content.classList.remove("active"));

    // Add 'active' class to the corresponding content
    const activeContent = tabContents[index];
    activeContent.classList.add("active");
  }

  // Initialize both tab click event handlers
  initTabClickEvents();
  initNoNestTabClickEvents();
}

function handleTabNavigation() {
  function initializeTabFromURL() {
    const params = new URLSearchParams(window.location.search);
    const targetTab = params.get("tab");

    if (!targetTab) return;

    const tabButtons = document.querySelectorAll(".no-sub-tab button");
    const tabContents = document.querySelectorAll(
      ".no-sub-tab-contents > ul > li"
    );

    activateTab(targetTab, tabButtons, tabContents);
  }

  function activateTab(targetId, tabButtons, tabContents) {
    // 모든 버튼의 'active' 클래스 제거
    tabButtons.forEach((btn) => btn.classList.remove("active"));

    // 클릭된 버튼에 'active' 클래스 추가
    const activeButton = Array.from(tabButtons).find(
      (btn) => btn.dataset.id === targetId
    );
    if (activeButton) activeButton.classList.add("active");

    // 모든 콘텐츠 숨기기
    tabContents.forEach((content) => (content.style.display = "none"));

    // 해당 ID의 콘텐츠 표시
    const activeContent = Array.from(tabContents).find(
      (content) => content.id === targetId
    );
    if (activeContent) activeContent.style.display = "block";
  }

  function updateURL(targetId) {
    const newUrl = `${window.location.pathname}?tab=${targetId}`;
    window.history.pushState({ tab: targetId }, "", newUrl);
  }

  function attachListeners() {
    const tabButtons = document.querySelectorAll(".no-sub-tab button");

    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const targetId = button.dataset.id;
        updateURL(targetId);
      });
    });
  }

  // Initialize tab based on URL on page load
  initializeTabFromURL();

  // Attach click listeners to update URL
  attachListeners();

  // Handle browser navigation (back/forward)
  window.addEventListener("popstate", (event) => {
    const targetTab = event.state?.tab || null;

    if (targetTab) {
      const tabButtons = document.querySelectorAll(".no-sub-tab button");
      const tabContents = document.querySelectorAll(
        ".no-sub-tab-contents > ul > li"
      );
      activateTab(targetTab, tabButtons, tabContents);
    }
  });
}

function sectionTitleAnimation() {
  const sectionTitles = document.querySelectorAll(".no-section-title");
  if (!sectionTitles.length) return;
  // GSAP 애니메이션 설정
  sectionTitles.forEach((title) => {
    gsap.to(
      title, // h2 요소 전체에 적용
      {
        opacity: 1,
        y: 0,
        duration: 1,
        ease: "power4.out",
        scrollTrigger: {
          trigger: title, // h2 요소를 트리거로 사용
          start: "top bottom", // 뷰포트 상단에서 80% 지점에 있을 때 시작
          end: "top 60%", // 뷰포트 상단에서 60% 지점에 있을 때 끝
          scrub: false, // 스크롤에 따른 애니메이션 진행 비활성화
          once: true, // 한 번만 애니메이션 실행
        },
      }
    );
  });
}

function sectionCntAnimation() {
  const sectionContents = document.querySelectorAll(".no-section-content");
  if (!sectionContents.length) return;
  // GSAP 애니메이션 설정
  sectionContents.forEach((content) => {
    gsap.to(
      content, // 콘텐츠 전체에 적용
      {
        opacity: 1, // 완전히 보이게
        y: 0,
        duration: 1.4, // 애니메이션 지속 시간
        ease: "power4.out",
        scrollTrigger: {
          trigger: content, // 콘텐츠를 트리거로 사용
          start: "top bottom", // 뷰포트 상단에서 90% 지점에 있을 때 시작
          end: "top 70%", // 뷰포트 상단에서 70% 지점에 있을 때 끝
          scrub: false, // 스크롤에 따른 애니메이션 진행 비활성화
          once: true, // 한 번만 애니메이션 실행
        },
      }
    );
  });
}

function setThemeAnimation() {
  const theme = document.documentElement.dataset.theme; // 현재 theme 값
  const images = document.querySelectorAll(".move-gradient img");

  images.forEach((img, index) => {
    if (theme === "light") {
      // Light theme에서는 애니메이션 제거
      img.style.animation = "";
    } else {
      // Dark theme에서는 이미지별로 다른 애니메이션 적용
      img.style.animation = `moveImage${
        index + 1
      } 20s infinite alternate ease-in-out`;
    }
  });
}

// 페이지 로드 시 테마 설정
setThemeAnimation();

// 테마가 동적으로 변경될 때를 감지
const observer = new MutationObserver(() => {
  setThemeAnimation();
});

// MutationObserver로 data-theme 속성 변화 감지
observer.observe(document.documentElement, {
  attributes: true,
  attributeFilter: ["data-theme"],
});

function visibleSmooth() {
  const visibleSmooth = document.querySelectorAll(".visible-smooth");
  if (!visibleSmooth.length) return;

  visibleSmooth.forEach((container) => {
    // li 요소들 선택
    const items = container.querySelectorAll("li");

    gsap.fromTo(
      items,
      {
        opacity: 0,
        y: 100,
      },
      {
        opacity: 1,
        y: 0,
        duration: 1.2, // 각 아이템의 애니메이션 시간
        stagger: 0.08, // 각 아이템 사이의 지연 시간
        ease: "power3.out",
        scrollTrigger: {
          trigger: container, // 컨테이너를 트리거로 사용
          start: "top 80%",
          end: "top 60%",
          scrub: false,
          once: true,
          // markers: true, // 디버깅용 마커 (필요시 주석 해제)
        },
      }
    );
  });
}

function subVisualAnimation() {
  const subVisual = document.querySelectorAll(".no-sub-visual");
  if (!subVisual.length) return;

  const tl = gsap.timeline();
  // GSAP 애니메이션 설정
  subVisual.forEach((content) => {
    const h2 = content.querySelector("h2");
    const p = content.querySelector("p");
    tl.to(h2, {
      y: 0,
      duration: 1.5, // 애니메이션 지속 시간
      ease: "power3.out", // 부드러운 감속 효과
      scrollTrigger: {
        trigger: content, // 콘텐츠를 트리거로 사용
        start: "top 90%", // 뷰포트 상단에서 90% 지점에 있을 때 시작
        end: "top 70%", // 뷰포트 상단에서 70% 지점에 있을 때 끝
        scrub: false, // 스크롤에 따른 애니메이션 진행 비활성화
        once: true, // 한 번만 애니메이션 실행
      },
    }).to(p, {
      right: 0,
      duration: 1.5,
      visibility: "visible",
      opacity: 0.12,
      ease: "power3.out",
    });
  });
}
function mainWorksAnimation() {
  // Main Works Swiper
  const mainWorksSwiper = new Swiper(".no-main-works-slider", {
    grabCursor: true,
    centeredSlides: true,
    spaceBetween: 64,
    speed: 1200,
    initialSlide: 3,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: ".no-main-works-button .swiper-button-next",
      prevEl: ".no-main-works-button .swiper-button-prev",
    },
    pagination: {
      type: "bullets",
      el: ".no-main-works-pagination",
      clickable: true,
    },
    breakpoints: {
      321: {
        slidesPerView: 1.2,
        spaceBetween: 12,
      },
      376: {
        slidesPerView: 1.3,
        spaceBetween: 16,
      },
      544: {
        slidesPerView: 1.5,
        spaceBetween: 24,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 32,
      },
      1024: {
        slidesPerView: 3.5,
        spaceBetween: 48,
      },
      1440: {
        slidesPerView: 4,
        spaceBetween: 48,
      },
      1664: {
        slidesPerView: 5,
        spaceBetween: 48,
      },
      1920: {
        slidesPerView: 6.5,
        spaceBetween: 64,
      },
    },
  });

  // Intersection Observer 설정
  const swiperElement = document.querySelector(".no-main-works-slider");

  if (swiperElement) {
    const observerOptions = {
      root: null, // 뷰포트를 기준으로
      rootMargin: "0px",
      threshold: 0.1, // 요소의 10%가 보이면 콜백 실행
    };

    const observerCallback = (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          // 슬라이드 요소 선택
          const slides = entry.target.querySelectorAll(".swiper-slide");

          // GSAP 애니메이션 적용
          gsap.fromTo(
            slides,
            { opacity: 0, y: 100 }, // 시작 상태
            {
              opacity: 1,
              y: 0,
              delay: 0.2,
              duration: 0.5,
              stagger: 0.1, // 각 슬라이드 간 0.1초 지연
              ease: "power2.out",
            }
          );

          // 애니메이션이 한번만 실행되도록 옵저버 해제
          observer.unobserve(entry.target);
        }
      });
    };

    const observer = new IntersectionObserver(observerCallback, observerOptions);
    observer.observe(swiperElement);
  }
}


function shinhanChartButtonEvent(lenis) {
  const chartButton = document.querySelectorAll(
    "#shinhan-card .no-sub-tab-chart-slider-thum button"
  );
  if (!chartButton) {
    return;
  }
  chartButton.forEach((button) => {
    button.addEventListener("click", () => {
      // 스크롤 대상 요소
      const targetElement = document.querySelector(
        "#shinhan-card .no-sub-tab-chart"
      );
      if (targetElement) {
        const targetPosition = targetElement.offsetTop; // 타겟 요소의 위치
        const viewportHeight = window.innerHeight; // 현재 화면의 높이
        const adjustedPosition =
          targetPosition - viewportHeight / 2 + targetElement.offsetHeight / 2; // 중앙 정렬

        lenis.scrollTo(adjustedPosition); // Lenis로 스크롤
      }
    });
  });
}

function masterChartButtonEvent(lenis, position) {
  // position에 따라 선택자 결정 (:first-child 또는 :last-child)
  const childSelector = position === "first" ? ":first-child" : ":last-child";

  const chartButtons = document.querySelectorAll(
    `#master-card .no-nest-tab-contents>ul>li${childSelector} .no-sub-tab-chart-slider-thum button`
  );
  console.log(chartButtons);

  if (!chartButtons || chartButtons.length === 0) {
    return;
  }

  chartButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetElements = document.querySelectorAll(
        `#master-card .no-nest-tab-contents>ul>li${childSelector}`
      );
      console.log(targetElements);

      if (targetElements && targetElements.length > 0) {
        targetElements.forEach((targetElement) => {
          const targetPosition = targetElement.offsetTop; // 타겟 요소의 위치
          const viewportHeight = window.innerHeight; // 현재 화면의 높이
          const adjustedPosition =
            targetPosition -
            viewportHeight / 2 +
            targetElement.offsetHeight / 2; // 중앙 정렬

          lenis.scrollTo(adjustedPosition); // Lenis로 스크롤
        });
      }
    });
  });
}

function initLenis() {
  try {
    const lenis = new Lenis();
    const noTopBtn = document.querySelector(".no-top-btn");

    // 페이지 로드 시 스크롤 위치 확인
    window.addEventListener("DOMContentLoaded", () => {
      if (noTopBtn) {
        if (window.scrollY > 80) {
          noTopBtn.classList.add("active");
        } else {
          noTopBtn.classList.remove("active");
        }
      }
    });

    // Lenis 이벤트와 ScrollTrigger 연동
    lenis.on("scroll", (e) => {
      // 스크롤 이벤트에서 top 버튼 상태 업데이트
      if (noTopBtn) {
        if (window.scrollY > 80) {
          noTopBtn.classList.add("active");
        } else {
          noTopBtn.classList.remove("active");
        }
      }
    });

    lenis.on("scroll", ScrollTrigger.update);

    gsap.ticker.add((time) => {
      lenis.raf(time * 1000);
    });

    gsap.ticker.lagSmoothing(0);

    // "no-top-btn" 버튼 클릭 이벤트 추가
    if (noTopBtn) {
      noTopBtn.addEventListener("click", () => {
        lenis.scrollTo(0, { duration: 1 }); // 탑으로 스크롤, 1초 동안 부드럽게 이동
      });
    } else {
      console.warn("no-top-btn button not found");
    }

    return lenis;
  } catch (error) {
    console.error("Failed to initialize Lenis:", error);
    return null;
  }
}
function subNavSwiper() {
  // Sub Navigation Swiper
  const subNavSwiper = new Swiper(".no-sub-nav-slider", {
    slidesPerView: "auto", // 자동 크기
    freeMode: true,
  });
  const currentSubNavIndex = subNavSwiper.slides.findIndex((slide, i) => {
    if (slide.classList.contains("active")) {
      return i;
    }
  });
  if (currentSubNavIndex > 0) {
    subNavSwiper.slideTo(currentSubNavIndex - 1, 800, false);
  }
}

function subNavTopEvent(lenis) {
  const buttons = document.querySelectorAll(
    ".no-sub-tab-slider > ul > li button"
  );
  buttons.forEach((button) => {
    button.addEventListener("click", function () {
      lenis.scrollTo(0);
    });
  });
}

function intro(lenis, minutes = 30, callback) {
  const introSection = document.querySelector(".no-main-intro");
  if (!introSection) {
    if (callback) callback(); // 인트로가 없으면 바로 실행
    return;
  }

  const savedTime = localStorage.getItem("intro-once");
  const currentTime = new Date().getTime();

  if (savedTime && currentTime - savedTime < minutes * 60 * 1000) {
    gsap.set(introSection, { opacity: 0, pointerEvents: "none" });
    if (lenis) lenis.start();
    if (callback) callback(); // 인트로 없이 바로 실행
    return;
  }

  localStorage.setItem("intro-once", currentTime);

  const introDOM = {
    symbol: introSection.querySelector(".no-main-intro-symbol"),
    txt: introSection.querySelector(".no-main-intro-txt"),
    cover: introSection.querySelector(".no-main-intro-cover"),
    container: introSection.querySelector(".no-main-intro-container"),
  };

  if (lenis) lenis.stop();

  const tl = gsap.timeline({
    onComplete: () => {
      if (lenis) lenis.start();
      if (callback) callback(); // 인트로 애니메이션 끝난 후 main-visual 실행
    },
  });

  tl.to(introDOM.symbol, {
    opacity: 1,
    y: 0,
    duration: 0.6,
    bottom: "0",
    rotate: "0deg",
    ease: "power3.out",
  })
    .to(introDOM.symbol, {
      left: 0,
      duration: 1,
      ease: "power2.out",
    })
    .to(
      introDOM.txt,
      {
        opacity: 1,
        visibility: "visible",
        duration: 1,
        ease: "power2.out",
      },
      "<"
    )
    .to(
      introDOM.cover,
      {
        background: "#000",
      },
      "<"
    )
    .to(
      introDOM.txt.querySelectorAll(".change"),
      {
        fill: "#fff",
      },
      "<"
    )
    .to(introDOM.container, {
      y: "-60%",
      opacity: 0,
      duration: 0.6,
      ease: "power3.out",
    })
    .to(
      introSection,
      {
        clipPath: "polygon(0 0, 100% 0, 100% 0, 0 0)",
        duration: 1,
        ease: "power2.out",
      },
      "-=1"
    );
}

function swiperManager() {
	
	const swiperElement = document.querySelector(".no-main-visual-slider");
	const progressCircle = document.querySelector(".autoplay-progress svg");
	const progressContent = document.querySelector(".autoplay-progress span");

	if (swiperElement) {

		const mainVisualSwiper = new Swiper(".no-main-visual-slider", {
		  slidesPerView: "auto",
		  grabCursor: true,
		  speed: 1200,
		  parallax: true,
		  navigation: {
			nextEl: ".no-main-visual-slider .swiper-button-next",
			prevEl: ".no-main-visual-slider .swiper-button-prev",
		  },
		  breakpoints: {
			320: {
			  pagination: {
				el: ".no-main-visual-pagination",
				type: "fraction",
			  },
			},
			768: {
			  pagination: {
				el: ".no-main-visual-pagination",
				type: "bullets",
				clickable: true,
			  },
			},
		  },
		  autoplay: {
			delay: 3000,
			disableOnInteraction: false,
		  },
		  scrollbar: {
			el: ".swiper-scrollbar",
		  },
		  on: {
			autoplayTimeLeft(s, time, progress) {
			  // ✅ 슬라이드 개수가 1개 이하이면 progress 숨김
			  if (s.slides.length <= 1) {
				progressCircle.style.display = "none";
				progressContent.style.display = "none";
			  } else {
				progressCircle.style.display = "block";
				progressContent.style.display = "block";
				progressCircle.style.setProperty("--progress", 1 - progress);
				progressContent.textContent = `${Math.ceil(time / 1000)}s`;
			  }
			},
		  },
		});

		// ✅ 초기 로드시 슬라이드 개수 체크 후 progress 숨김
		if (mainVisualSwiper.slides.length <= 1) {
		  progressCircle.style.display = "none";
		  progressContent.style.display = "none";
		}
	}



  const mainNoticeSwiper = new Swiper(".no-main-notice-slider", {
    spaceBetween: 24,
    speed: 1200,
    navigation: {
      nextEl: ".no-main-notice-slider .swiper-button-next",
      prevEl: ".no-main-notice-slider .swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "progressbar",
    },
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    breakpoints: {
      321: {
        slidesPerView: 1.2,
      },
      544: {
        slidesPerView: 1.5,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });

  const modalDateSwiper = new Swiper(".no-filter-modal-date-swiper", {
    speed: 800,
    slidesPerView: "auto",
    freeMode: true,
    spaceBetween: 12,
  });

  const mainSponsorSwiper2 = new Swiper(".no-main-sponsor-slider-2", {
    slidesPerView: 4, // 기본값 (데스크톱 등 큰 화면에서 적용)
    grabCursor: true,
    speed: 1200,
    navigation: {
      nextEl: ".no-main-sponsor-button-next",
      prevEl: ".no-main-sponsor-button-prev",
    },
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    breakpoints: {
      320: {
        slidesPerView: 2, // 모바일 화면에서 슬라이드 1개
        spaceBetween: 10,
      },
      // 768px 이상 (태블릿 화면)
      768: {
        slidesPerView: 3, // 태블릿 화면에서 슬라이드 2개
        spaceBetween: 16,
      },
      // 1024px 이상 (데스크톱 화면)
      1024: {
        slidesPerView: 4, // 데스크톱 화면에서 슬라이드 4개
        spaceBetween: 24,
      },
    },
  });

  // Shinhan Content Swiper
  const shinahnContentSwiper = new Swiper(
    ".shinhan-card .no-sub-tab-contents-slider",
    {
      slidesPerView: "1.5",
      grabCursor: true,
      speed: 1200,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".shinhan-card .swiper-button-next",
        prevEl: ".shinhan-card .swiper-button-prev",
      },
      breakpoints: {
        321: {
          spaceBetween: 12,
          slidesPerView: "1.2",
        },
        322: {
          spaceBetween: 20,
          slidesPerView: "1.5",
        },
        1024: {
          spaceBetween: 24,
        },
      },
    }
  );

  // Master Content Swiper
  const masterContentSwiper = new Swiper(
    ".master-card .no-sub-tab-contents-slider",
    {
      slidesPerView: "1.5",
      grabCursor: true,
      spaceBetween: 24,
      speed: 1200,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".master-card .swiper-button-next",
        prevEl: ".master-card .swiper-button-prev",
      },
      breakpoints: {
        321: {
          spaceBetween: 12,
          slidesPerView: "1.2",
        },
        322: {
          spaceBetween: 20,
          slidesPerView: "1.5",
        },
        1024: {
          spaceBetween: 24,
        },
      },
    }
  );

  // Master Content Swiper
  const nemoContentSwiper = new Swiper(".nemo .no-sub-tab-contents-slider", {
    slidesPerView: "1.5",
    grabCursor: true,
    spaceBetween: 24,
    speed: 1200,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: ".nemo .swiper-button-next",
      prevEl: ".nemo .swiper-button-prev",
    },
    breakpoints: {
      321: {
        spaceBetween: 12,
        slidesPerView: "1.2",
      },
      322: {
        spaceBetween: 20,
        slidesPerView: "1.5",
      },
      1024: {
        spaceBetween: 24,
      },
    },
  });

  // Master Content Swiper
  const practiceContentSwiper = new Swiper(
    ".practice .no-sub-tab-contents-slider",
    {
      slidesPerView: "1.5",
      grabCursor: true,
      spaceBetween: 24,
      speed: 1200,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".practice .swiper-button-next",
        prevEl: ".practice .swiper-button-prev",
      },
      breakpoints: {
        321: {
          spaceBetween: 12,
          slidesPerView: "1.2",
        },
        322: {
          spaceBetween: 20,
          slidesPerView: "1.5",
        },
        1024: {
          spaceBetween: 24,
        },
      },
    }
  );

  // Sub Tab Swiper
  const subTabSwiper = new Swiper(".no-sub-tab-slider", {
    slidesPerView: "auto", // 자동 크기
    spaceBetween: 24, // 슬라이드 간 간격
    freeMode: true,
    breakpoints: {
      321: {
        spaceBetween: 12,
      },
      768: {
        spaceBetween: 20,
      },
      1024: {
        spaceBetween: 24,
      },
    },
  });

  const subCategorySwiper = new Swiper(".no-sub-category-slider", {
    slidesPerView: "auto",
    grabCursor: true,
    spaceBetween: 24,
    speed: 1200,
    breakpoints: {
      321: {
        spaceBetween: 12,
      },
      768: {
        spaceBetween: 20,
      },
      1024: {
        spaceBetween: 24,
      },
    },
  });

  // Shinhan Card Thumbnail Swiper
  const shinhanChartSliderThum = new Swiper(
    ".shinhan-card .no-sub-tab-chart-slider-thum",
    {
      slidesPerView: 3,
      spaceBetween: 24,
      speed: 1200,
      watchSlidesVisibility: true,
      watchSlidesProgress: true,
      breakpoints: {
        321: { slidesPerView: 2.4, spaceBetween: 12 },
        544: { slidesPerView: 3, spaceBetween: 20 },
        768: { slidesPerView: 3, spaceBetween: 24 },
      },
    }
  );

  // Shinhan Card Chart Swiper
  const shinhanChartSlider = new Swiper(
    ".shinhan-card .no-sub-tab-chart-slider",
    {
      slidesPerView: 1,
      grabCursor: true,
      spaceBetween: 24,
      speed: 1200,
      effect: "fade",
      fadeEffect: {
        crossFade: true, // 슬라이드 간 겹치지 않도록 crossFade 활성화
      },
      autoHeight: true,
      navigation: {
        nextEl: ".shinhan-card .no-sub-tab-chart-slider .swiper-button-next",
        prevEl: ".shinhan-card .no-sub-tab-chart-slider .swiper-button-prev",
      },
      thumbs: {
        swiper: shinhanChartSliderThum,
      },
    }
  );

  // Master Card Thumbnail Swiper
  const masterChartSliderGroundThum = new Swiper(
    ".master-card .no-sub-tab-chart-slider-thum-ground",
    {
      slidesPerView: "auto",
      spaceBetween: 24,
      speed: 1200,
      watchSlidesVisibility: true,
      watchSlidesProgress: true,
      breakpoints: {
        321: { slidesPerView: 2 },
      },
    }
  );

  // Master Card Chart Swiper
  const masterChartSliderGround = new Swiper(
    ".master-card .no-sub-tab-chart-slider-ground",
    {
      slidesPerView: "auto",
      grabCursor: true,
      spaceBetween: 24,
      speed: 1200,
      effect: "fade",
      fadeEffect: {
        crossFade: true, // 슬라이드 간 겹치지 않도록 crossFade 활성화
      },
      autoHeight: true,
      navigation: {
        nextEl:
          ".master-card .no-sub-tab-chart-slider-ground .swiper-button-next",
        prevEl:
          ".master-card .no-sub-tab-chart-slider-ground .swiper-button-prev",
      },
      thumbs: {
        swiper: masterChartSliderGroundThum,
      },
    }
  );

  // Master Card Thumbnail Swiper
  const masterChartSliderStandThum = new Swiper(
    ".master-card .no-sub-tab-chart-slider-thum-stand",
    {
      slidesPerView: "auto",
      spaceBetween: 24,
      speed: 1200,
      watchSlidesVisibility: true,
      watchSlidesProgress: true,
      breakpoints: {
        321: { slidesPerView: 2 },
      },
    }
  );

  // Master Card Chart Swiper
  const masterChartSliderStand = new Swiper(
    ".master-card .no-sub-tab-chart-slider-stand",
    {
      slidesPerView: "auto",
      grabCursor: true,
      spaceBetween: 24,
      speed: 1200,
      effect: "fade",
      fadeEffect: {
        crossFade: true, // 슬라이드 간 겹치지 않도록 crossFade 활성화
      },
      autoHeight: true,
      navigation: {
        nextEl:
          ".master-card .no-sub-tab-chart-slider-stand .swiper-button-next",
        prevEl:
          ".master-card .no-sub-tab-chart-slider-stand .swiper-button-prev",
      },
      thumbs: {
        swiper: masterChartSliderStandThum,
      },
    }
  );

  // FNB Stage Swiper
  const fnbStageSwiper = new Swiper(".fnb-swiper", {
    slidesPerView: "1.5",
    grabCursor: true,
    spaceBetween: 24,
    speed: 1600,
    autoplay: {
      delay: 4000,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: ".fnb-swiper .swiper-button-next",
      prevEl: ".fnb-swiper .swiper-button-prev",
    },
    breakpoints: {
      breakpoints: {
        321: {
          spaceBetween: 12,
          slidesPerView: "1.2",
        },
        322: {
          spaceBetween: 20,
          slidesPerView: "1.5",
        },
        1024: {
          spaceBetween: 24,
        },
      },
    },
  });
  //Modal
  const modalSwiper = new Swiper(".no-modal-swiper", {
    slidesPerView: "auto",
    grabCursor: true,
    spaceBetween: 0,
    speed: 1600,
    autoplay: {
      delay: 4000,
      disableOnInteraction: false,
    },
    pagination: {
      type: "bullets",
      el: ".no-modal-swiper .swiper-pagination",
      clickable: true,
    },
  });
}

function isSticky(selector) {
  // 대상 요소 선택
  const targetElement = document.querySelector(selector);

  if (!targetElement) {
    console.warn(`${selector} 요소가 존재하지 않습니다.`);
    return;
  }

  // 요소의 초기 위치 계산
  const initialTop =
    targetElement.getBoundingClientRect().top + window.pageYOffset;

  // 스크롤 이벤트 핸들러 등록
  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll >= initialTop) {
      targetElement.classList.add("sticky"); // sticky 클래스 추가
    } else {
      targetElement.classList.remove("sticky"); // sticky 클래스 제거
    }
  });
}

function infiniteRotate(selector) {
  const target = document.querySelectorAll(selector);
  if (!target) {
    return;
  }

  gsap.to(target, {
    rotate: 360, // 360도 회전
    duration: 5, // 5초 동안 회전
    repeat: -1, // 무한 반복
    ease: "linear", // 일정한 속도
  });
}

function faqItems() {
  const faqItems = [...document.querySelectorAll("[data-faq-item]")];

  if (!faqItems.length) return;

  faqItems.forEach((item) => {
    const head = item.querySelector("header button");
    const body = item.querySelector("section");
    const arrow = item.querySelector(".no-skin-faq-item__arrow");

    $(head).click(function () {
      const siblings = $(item).siblings();

      // Toggle the active class for the clicked item
      $(item).toggleClass("--active");
      $(body).stop().slideToggle(200);

      // Manage the arrow active class
      if ($(item).hasClass("--active")) {
        $(arrow).addClass("--active");
      } else {
        $(arrow).removeClass("--active");
      }

      // Remove active class from siblings
      siblings.removeClass("--active");
      siblings.find("section").stop().slideUp(200);
      siblings.find(".no-skin-faq-item__arrow").removeClass("--active");
    });
  });
}

function processManager() {
  const processItems = document.querySelectorAll(".--process > ul > li");

  // 요소가 있는지 확인
  if (processItems.length > 0) {
    let currentIndex = 0;

    // 초기 애니메이션 지연 시간 1초 설정
    const firstDelay = 1000;
    const subsequentDelay = 3000;

    function activateNextItem() {
      // 모든 항목에서 active 클래스 제거
      processItems.forEach((item) => item.classList.remove("active"));

      // 현재 항목에 active 클래스 추가
      if (processItems[currentIndex]) {
        processItems[currentIndex].classList.add("active");
      }

      // 다음 인덱스 계산
      currentIndex = (currentIndex + 1) % processItems.length;

      // 첫 번째 이후의 딜레이로 setTimeout 재설정
      setTimeout(activateNextItem, subsequentDelay);
    }

    // 첫 번째 애니메이션 시작
    setTimeout(activateNextItem, firstDelay);
  }
}

function input() {
  let $fileInfoList = $(".--file-info ul"); // 파일 리스트를 추가할 ul 선택
  let fileCount = 0; // 파일 ID 및 name을 관리하는 변수

  $(".field-file").on("change", function () {
    let files = $(this)[0].files;

    for (let file of files) {
      fileCount++; // 파일 카운트 증가
      if (fileCount > 10) {
        alert("첨부 파일은 최대 10개까지 가능합니다.");
        fileCount--;
        return;
      }

      let fileName = file.name;
      // <li> 요소 생성
      let $li = $(
        `<li>
                    <span>${fileName}</span>
                    <button class="remove-file">x</button>
                    <input type="file" name="file_${fileCount}" id="file_${fileCount}" value="${fileName}">
                </li>`
      );

      // 파일 리스트에 추가
      $fileInfoList.append($li);

      // 삭제 버튼 클릭 이벤트 추가
      $li.find(".remove-file").on("click", function () {
        $(this).closest("li").remove(); // li 제거
        fileCount--; // 파일 카운트 감소
      });
    }

    // 입력값 초기화 (같은 파일을 다시 업로드할 수 있도록)
    $(this).val("");
  });
}

function buttonManager(buttonSelector, listSelector, startYear = 2011) {
  try {
    const buttonElement = document.querySelector(buttonSelector);
    const listElement = document.querySelector(listSelector);

    // 버튼 또는 리스트가 존재하지 않으면 에러 메시지 출력 후 종료
    if (!buttonElement) {
      console.error(
        `buttonManager Error: Button element not found for selector "${buttonSelector}"`
      );
      return;
    }
    if (!listElement) {
      console.error(
        `buttonManager Error: List element not found for selector "${listSelector}"`
      );
      return;
    }

    // 버튼 클릭 이벤트 등록
    buttonElement.addEventListener("click", function () {
      listElement.classList.toggle("visible");
      this.classList.toggle("active");
    });

    // 리스트 내 input 요소의 checked 상태 처리
    const inputs = listElement.querySelectorAll(
      'input[type="radio"], input[type="checkbox"]'
    );
    if (inputs.length === 0) {
      console.warn(
        `buttonManager Warning: No input elements (radio/checkbox) found in "${listSelector}"`
      );
    }

    inputs.forEach((input) => {
      input.addEventListener("change", function () {
        if (this.checked) {
          listElement.classList.remove("visible"); // visible 클래스 제거
          buttonElement.classList.remove("active"); // 버튼 active 클래스도 제거
        }
      });
    });
  } catch (error) {
    console.error("buttonManager Error:", error);
  }
}

function curstomCursor() {
  const cursor = new MouseFollower({
	     speed: 0.25, // 기본값은 0.55, 0.9로 설정하면 훨씬 빠르게 따라옴
    stateDetection: {
      container: "body",
      // '-pointer': '.no-btn',
    },
  });
}

function filingRadio(selector) {
  const elements = document.querySelectorAll(selector);

  elements.forEach((element) => {
    element.addEventListener("change", (e) => {
      const parent = e.target.closest(".no-filing-btn");
      document.querySelectorAll(".no-filing-btn").forEach((btn) => {
        btn.classList.remove("active");
      });
      if (e.target.checked) {
        parent.classList.add("active");
      }
    });
  });
}

class App {
  static lenis;

  static init() {
  AOS.init({
    once: true // 모든 요소에 대해 한 번만 애니메이션 적용
  });
    this.lenis = initLenis();
    header(this.lenis);
    //canvasAnimation(this);
    marquee(this);
    tabManager(this.lenis);
    handleTabNavigation();
    //tabId(this.lenis);
    //initTabHashUpdater();
    subNavSwiper();
    shinhanChartButtonEvent(this.lenis);
    masterChartButtonEvent(this.lenis, "last");
    masterChartButtonEvent(this.lenis, "first");
    //filterModalEvent(this.lenis);
    filingRadio('.no-filing-btn input[type="radio"]');
    buttonManager(".no-date-btn", ".no-date-list"); // 연도 버튼 렌더링
    buttonManager(".no-footer__privacy button", ".no-footer__privacy-list"); // 연도 버튼 렌더링
    processManager();
    //input();
    faqItems();
    subNavTopEvent(this.lenis);
    intro(this.lenis, 30, swiperManager); // 인트로 후에 Swiper 실행
    infiniteRotate(".no-text-circle");
    inputLineAnimation();
    tooltip();
    moreInfo();
    mainWorksAnimation();
    subPageIntroAnimation();
    curstomCursor();
    sectionTitleAnimation();
    isSticky(".no-sub-tab");
    sectionCntAnimation();
    visibleSmooth();
    divAnimation();
    subVisualAnimation();
    startBluesquareAnimation();
    setupModalTriggers(this.lenis);
  }
}

App.init();

document.addEventListener("DOMContentLoaded", function () {
  const scrollContainer = document.querySelector(".no-sitemap__gnb--wrap");
  const parentElement = document.querySelector(".no-sitemap");

  if (!scrollContainer || !parentElement) {
    console.warn("스크롤 컨테이너 또는 .no-sitemap 요소를 찾을 수 없습니다.");
    return;
  }

  scrollContainer.addEventListener("scroll", () => {
    const isAtBottom =
      scrollContainer.scrollTop + scrollContainer.clientHeight >=
      scrollContainer.scrollHeight - 1;

    if (isAtBottom) {
      parentElement.classList.add("scroll-end");
    } else {
      parentElement.classList.remove("scroll-end");
    }
  });
});
