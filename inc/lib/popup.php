<?php

// "오늘 하루 그만 보기" 쿠키 확인
$popupClosed = isset($_COOKIE['popupClosed']) && $_COOKIE['popupClosed'] === 'true';
$db = DB::getInstance(); 

if (!$popupClosed) {
    // 데이터베이스에서 팝업 데이터 가져오기
    $sql = "SELECT * FROM nb_popup WHERE p_view = 'Y' ORDER BY p_idx ASC"; 
    $stmt = $db->query($sql);
    $popups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $popupCount = count($popups); // 팝업 개수 가져오기

    if (!empty($popups)) {
        // 클래스 설정
        $containerClass = "";
        if ($popupCount >= 3) {
            $containerClass = "no-container-xl";
        } elseif ($popupCount == 2) {
            $containerClass = "no-container-md";
        } elseif ($popupCount == 1) {
            $containerClass = "no-container-xs";
        }
        ?>
		<div class="main-popup-wrap" data-popup-index="<?= $popupCount ?>">
			<div class="main-popup <?= $containerClass; ?>">
				<div class="main-popup-top">
					<ul class="swiper-component">
						<li class="swiper-button-prev popup-prev arrow" data-index="prev">
							<i class="fa-duotone fa-thin fa-angle-up fa-rotate-270" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i>
						</li>
						<li class="swiper-button-next popup-next arrow" data-index="next">
							<i class="fa-duotone fa-thin fa-angle-up fa-rotate-90" style="--fa-primary-color: #ffffff; --fa-secondary-color: #ffffff; --fa-secondary-opacity: 1;"></i>
						</li>
					</ul>
				</div>

				<div class="main-popup-mid main-popup-slide">
					<ul class="swiper-wrapper">
						<?php foreach ($popups as $index => $popup): ?>
							<li class="swiper-slide" data-index="<?= $index ?>">
								<figure class="img-box">
									<a href="<?= htmlspecialchars($popup['p_link'], ENT_QUOTES, 'UTF-8'); ?>" target="<?= $popup['p_target'] === '_none' ? '_self' : '_blank' ?>">
										<img src="<?= htmlspecialchars('/uploads/popup/' . $popup['p_img'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($popup['p_title'], ENT_QUOTES, 'UTF-8'); ?>">
									</a>
								</figure>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="main-popup-bottom">
                    <label for="closeCheck" id="toggleLabel">
                        <input type="checkbox" id="closeCheck">
                        <img src="/resource/images/icon/popupcheck.svg" alt="popup check icon" id="popupImage">
                        <img src="/resource/images/icon/popupcheck-on.svg" alt="popup check icon on" id="popupImage-on">
                        오늘 하루 그만 보기
                    </label>
                    <button type="button" class="popup-close" data-index="close">닫기
                        <i class="fa-light fa-xmark" style="color: #ffffff;"></i>
                    </button>
                </div>
            </div>
            <div class="main-popup-bg"></div>
        </div>

        <script>
			document.addEventListener("DOMContentLoaded", () => {
				// data-popup-index에서 팝업 개수 가져오기
				var popupCount = parseInt(document.querySelector(".main-popup-wrap").getAttribute("data-popup-index"), 10);
				console.log("Popup Count:", popupCount);

				// 동적으로 slidesPerView 값을 설정하는 함수
				function getSlidesPerView() {
					var width = window.innerWidth;

					if (width >= 1024) {
						return popupCount >= 3 ? 3 : popupCount; // 1200px 이상이면 최대 3개
					} else if (width >= 544) {
						return popupCount >= 2 ? 2 : popupCount; // 768px 이상이면 최대 2개
					} else {
						return 1; // 767px 이하에서는 무조건 1개
					}
				}

				// Swiper 초기화
				var main_popup = new Swiper('.main-popup-slide', {
					pagination: {
						el: '.swiper-pagination',
						clickable: true,
					},
					navigation: {
						nextEl: '[data-index="next"]',
						prevEl: '[data-index="prev"]',
					},
					speed: 1000,
					spaceBetween: 15,
					slidesPerView: getSlidesPerView(), // 초기 slidesPerView 설정
				});

				// 창 크기가 변경될 때 Swiper 업데이트
				window.addEventListener("resize", () => {
					var newSlidesPerView = getSlidesPerView();
					console.log("Updated slidesPerView:", newSlidesPerView);

					main_popup.params.slidesPerView = newSlidesPerView;
					main_popup.update();
				});

				// 팝업 닫기 버튼 로직
				const closeButton = document.querySelector(".popup-close");
				const mainPopup = document.querySelector(".main-popup-wrap");
				const checkbox = document.getElementById("closeCheck");
				const popupImage = document.getElementById("popupImage");
				const popupImageOn = document.getElementById("popupImage-on");

				if (closeButton && mainPopup) {
					closeButton.addEventListener("click", () => {
						mainPopup.style.display = "none";
						if (checkbox && checkbox.checked) {
							const expires = new Date();
							expires.setHours(23, 59, 59, 999);
							document.cookie = `popupClosed=true; expires=${expires.toUTCString()}; path=/`;
						}
					});
				}

				// "오늘 하루 그만 보기" 체크박스 토글 로직
				if (checkbox && popupImage && popupImageOn) {
					checkbox.addEventListener("change", () => {
						const isChecked = checkbox.checked;
						popupImage.style.display = isChecked ? "none" : "inline";
						popupImageOn.style.display = isChecked ? "inline" : "none";
					});
				}
			});
        </script>

        <?php
    }
}
?>
