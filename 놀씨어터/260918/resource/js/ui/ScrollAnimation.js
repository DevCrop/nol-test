export class ScrollAnimation {
  constructor() {
    this.durationMap = {
      fast: 0.6,
      default: 0.8,
      medium: 1.0,
      slow: 1.4,
      title: 0.6,
    };
    
    // 이미 처리된 요소 추적
    this.processedElements = new Set();

    this.init();
  }

  init() {
    // 모든 data-aos-* 속성을 가진 요소 찾기
    const elements = document.querySelectorAll(
      "[data-aos-title], [data-aos-fast], [data-aos-default], [data-aos-medium], [data-aos-slow]"
    );

    elements.forEach((element) => {
      this.animateElement(element);
    });
  }

  animateElement(element) {
    // 이미 애니메이션이 완료된 요소는 제외
    if (element.hasAttribute("data-scroll-animated") && 
        element.getAttribute("data-scroll-animated") === "true") {
      return;
    }

    // 처리 중인 요소도 제외
    if (this.processedElements.has(element)) {
      return;
    }

    // 즉시 처리 중 표시 (중복 방지)
    this.processedElements.add(element);
    element.setAttribute("data-scroll-animated", "processing");

    // 어떤 속성이 있는지 확인
    let duration = this.durationMap.default;

    if (element.hasAttribute("data-aos-title")) {
      duration = this.durationMap.title;
    } else if (element.hasAttribute("data-aos-fast")) {
      duration = this.durationMap.fast;
    } else if (element.hasAttribute("data-aos-default")) {
      duration = this.durationMap.default;
    } else if (element.hasAttribute("data-aos-medium")) {
      duration = this.durationMap.medium;
    } else if (element.hasAttribute("data-aos-slow")) {
      duration = this.durationMap.slow;
    }

    // ScrollTrigger 설정 - 뷰포트 하단에 조금이라도 걸치면 시작
    const scrollTriggerConfig = {
      trigger: element,
      start: "top bottom", // 요소가 뷰포트 하단에 조금이라도 걸치면 시작
      toggleActions: "play none none none", // 한번만 실행
    };

    // ScrollTrigger로 애니메이션 생성
    const animation = gsap.to(element, {
      opacity: 1,
      y: 0,
      duration: duration,
      ease: "power2.out",
      scrollTrigger: scrollTriggerConfig,
      onComplete: () => {
        // 애니메이션 완료 표시
        element.setAttribute("data-scroll-animated", "true");
        
        // ScrollTrigger 완전히 제거하여 재실행 방지
        if (animation.scrollTrigger) {
          animation.scrollTrigger.kill();
        }
      },
    });
  }

  // 동적으로 추가된 요소를 위한 refresh 메서드
  refresh() {
    const elements = document.querySelectorAll(
      "[data-aos-title], [data-aos-fast], [data-aos-default], [data-aos-medium], [data-aos-slow]"
    );

    elements.forEach((element) => {
      // 처리되지 않은 요소만 애니메이션 적용
      if (!element.hasAttribute("data-scroll-animated") && 
          !this.processedElements.has(element)) {
        this.animateElement(element);
      }
    });
  }
}
