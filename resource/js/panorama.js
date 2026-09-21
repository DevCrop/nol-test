document.addEventListener("DOMContentLoaded", function () {
  if (window.angular) return false;

  const wrapper = document
    .querySelector("#code_block-36-88")
    .closest(".ct-div-block")
    .querySelector(".swiper");

  const swiperWrapper = wrapper.querySelector(".swiper-wrapper");
  const slides = swiperWrapper.children;

  // 현재 슬라이드 개수 확인 (기본 3개)
  const totalSlides = slides.length;

  // 1개, 2개, 3개인 경우 → 9개가 되도록 복제
  if (totalSlides <= 3) {
    while (swiperWrapper.children.length < 9) {
      const clonedSlide = slides[swiperWrapper.children.length % totalSlides].cloneNode(true);
      swiperWrapper.appendChild(clonedSlide);
    }
  }


   // 슬라이드 개수가 6개 이하일 때, 2배로 복제
  if (totalSlides <= 6) {
    const newSlides = [];
    for (let i = 0; i < totalSlides; i++) {
      newSlides.push(slides[i].cloneNode(true)); // 슬라이드 복제
    }
    newSlides.forEach((slide) => swiperWrapper.appendChild(slide));
  }

  function b({ swiper: a, extendParams: s, on: o }) {
    s({
      panorama: {
        depth: 200,
        rotate: 30,
      },
    }),
      o("beforeInit", () => {
        if (a.params.effect !== "panorama") return;
        a.classNames.push(`${a.params.containerModifierClass}panorama`),
          a.classNames.push(`${a.params.containerModifierClass}3d`);
        const r = {
          watchSlidesProgress: !0,
        };
        Object.assign(a.params, r), Object.assign(a.originalParams, r);
      }),
      o("progress", () => {
        if (a.params.effect !== "panorama") return;
        const r = a.slidesSizesGrid,
          { depth: e = 200, rotate: t = 30 } = a.params.panorama,
          f = (t * Math.PI) / 180 / 2,
          p = 1 / (180 / t);
        for (let i = 0; i < a.slides.length; i += 1) {
          const d = a.slides[i],
            u = d.progress,
            c = r[i],
            g = a.params.centeredSlides
              ? 0
              : (a.params.slidesPerView - 1) * 0.5,
            l = u + g,
            m = 1 - Math.cos(l * p * Math.PI),
            h = `${l * (c / 3) * m}px`,
            P = l * t,
            y = `${((c * 0.5) / Math.sin(f)) * m - e}px`;
          d.style.transform = `translateX(${h}) translateZ(${y}) rotateY(${P}deg)`;
        }
      }),
      o("setTransition", (r, e) => {
        a.params.effect === "panorama" &&
          a.slides.forEach((t) => {
            t.style.transition = `${e}ms`;
          });
      });
  }

  const v = new Swiper(wrapper, {
    modules: [b],
    effect: "panorama",
    slidesPerView: 1.5,
    speed: 1800,
    centeredSlides: true,
    spaceBetween: 10,
	loop : true,
    autoplay: {
      delay: 2500,
      disableOnInteraction: false,
    },
    panorama: {
      depth: 150,
      rotate: 45,
    },
    breakpoints: {
      480: {
        slidesPerView: 2,
        panorama: {
          rotate: 35,
          depth: 150,
        },
      },
      640: {
        slidesPerView: 3,
        panorama: {
          rotate: 30,
          depth: 150,
        },
      },
      1024: {
        slidesPerView: 4,
        panorama: {
          rotate: 30,
          depth: 200,
        },
      },
      1200: {
        slidesPerView: 5,
        panorama: {
          rotate: 25,
          depth: 250,
        },
      },
      1664: {
        slidesPerView: 7,
        panorama: {
          rotate: 30,
          depth: 80,
        },
      },
      1700: {
        slidesPerView: 6,
        panorama: {
          rotate: 30,
          depth: 80,
        },
      },
      1921: {
        slidesPerView: 7,
        panorama: {
          rotate: 30,
          depth: 80,
        },
      },
    },
  });
});
