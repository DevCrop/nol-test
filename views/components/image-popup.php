<?php

use Database\DB;

// "오늘 하루 그만 보기" 쿠키 확인
$popupClosed = isset($_COOKIE['imagePopupClosed']) && $_COOKIE['imagePopupClosed'] === 'true';
$db = DB::getInstance();

if (!$popupClosed) {
    // 현재 경로 가져오기
    $currentPath = '/';

    if (function_exists('current_route')) {
        $currentRoute = current_route();
        if ($currentRoute) {
            $currentPath = $currentRoute->getUri();
        } else {
            $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        }
    } else {
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    // 경로 정규화
    $currentPath = '/' . trim($currentPath, '/');
    if ($currentPath === '') {
        $currentPath = '/';
    }

    $sql = "
        SELECT *
        FROM nb_popups
        WHERE is_active = 1
          AND popup_path = :current_path
          AND popup_type = 1
          AND (
                is_unlimited = 1
                OR (
                    is_unlimited = 0
                    AND (start_at IS NULL OR start_at <= NOW())
                    AND (end_at IS NULL OR end_at >= NOW())
                )
              )
        ORDER BY sort_no ASC, id ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':current_path' => $currentPath]);
    $popups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $popupCount = count($popups);

    if (!empty($popups)) {
        $containerClass = ($popupCount >= 3) ? "no-container-lg" : (($popupCount == 2) ? "no-container-xs" : "no-container-xs");
?>

<div class="image-popup-wrap" data-popup-index="<?= (int)$popupCount ?>">
    <div class="image-popup <?= $containerClass ?>">
        <?php if ($popupCount > 1): ?>
        <div class="image-popup-top">
            <ul class="swiper-component">
                <li class="swiper-button-prev arrow" data-index="prev">‹</li>
                <li class="swiper-button-next arrow" data-index="next">›</li>
            </ul>
        </div>
        <?php endif; ?>

        <div class="image-popup-mid image-popup-slide">
            <ul class="swiper-wrapper">
                <?php foreach ($popups as $popup): ?>
                <?php
                            $title  = htmlspecialchars((string)($popup['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $img    = htmlspecialchars((string)($popup['popup_image'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $link   = !empty($popup['link_url']) ? safe_url((string)$popup['link_url'], ['http', 'https'], true) : '';
                            $hasLink = (int)($popup['has_link'] ?? 2) === 1 && $link !== '';
                            $isTarget = (int)($popup['is_target'] ?? 1);
                            $target = $isTarget === 1 ? '_blank' : '_self';
                            ?>
                <li class="swiper-slide">
                    <figure class="img-box">
                        <?php if ($hasLink): ?>
                        <a href="<?= e($link) ?>" target="<?= e($target) ?>"
                            <?php if ($target === '_blank'): ?>rel="noopener noreferrer" <?php endif; ?>>
                            <img src="<?= e('/uploads/popups/' . ltrim($img, '/\\')) ?>" alt="<?= $title ?>" loading="lazy"
                                decoding="async">
                        </a>
                        <?php else: ?>
                        <img src="<?= e('/uploads/popups/' . ltrim($img, '/\\')) ?>" alt="<?= $title ?>" loading="lazy"
                            decoding="async">
                        <?php endif; ?>
                    </figure>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="image-popup-bottom">
            <label id="imageToggleLabel" class="image-popup-checkbox-label">
                <input type="checkbox" id="imageCloseCheck">
                <svg class="image-popup-icon image-popup-icon--check" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640" aria-hidden="true" focusable="false">
                    <path
                        d="M480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160C96 124.7 124.7 96 160 96L480 96zM438 209.7C427.3 201.9 412.3 204.3 404.5 215L285.1 379.2L233 327.1C223.6 317.7 208.4 317.7 199.1 327.1C189.8 336.5 189.7 351.7 199.1 361L271.1 433C276.1 438 283 440.5 289.9 440C296.8 439.5 303.3 435.9 307.4 430.2L443.3 243.2C451.1 232.5 448.7 217.5 438 209.7z" />
                </svg>
                <span>오늘 하루 그만 보기</span>
            </label>
            <button type="button" class="image-popup-close" data-index="close">
                <span>닫기</span>
                <svg class="image-popup-icon image-popup-icon--close" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640" aria-hidden="true" focusable="false">
                    <path
                        d="M320 112C434.9 112 528 205.1 528 320C528 434.9 434.9 528 320 528C205.1 528 112 434.9 112 320C112 205.1 205.1 112 320 112zM320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM231 231C221.6 240.4 221.6 255.6 231 264.9L286 319.9L231 374.9C221.6 384.3 221.6 399.5 231 408.8C240.4 418.1 255.6 418.2 264.9 408.8L319.9 353.8L374.9 408.8C384.3 418.2 399.5 418.2 408.8 408.8C418.1 399.4 418.2 384.2 408.8 374.9L353.8 319.9L408.8 264.9C418.2 255.5 418.2 240.3 408.8 231C399.4 221.7 384.2 221.6 374.9 231L319.9 286L264.9 231C255.5 221.6 240.3 221.6 231 231z" />
                </svg>
            </button>
        </div>
    </div>
    <div class="image-popup-bg"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const wrap = document.querySelector(".image-popup-wrap");
    const bg = document.querySelector(".image-popup-bg");
    const closeButton = document.querySelector(".image-popup-close");
    const checkbox = document.getElementById("imageCloseCheck");

    if (!wrap) return;

    const popupCount = parseInt(wrap.getAttribute('data-popup-index') || '1');

    // Swiper 초기화
    const swiperConfig = {
        slidesPerView: 1,
        speed: 500,
        spaceBetween: 10,
    };

    // 팝업이 1개 이상일 때만 네비게이션 추가
    if (popupCount > 1) {
        swiperConfig.navigation = {
            nextEl: '[data-index="next"]',
            prevEl: '[data-index="prev"]',
        };
    }

    const swiper = new Swiper('.image-popup-slide', swiperConfig);

    // 팝업 닫기
    function closePopup() {
        wrap.style.display = "none";
        if (checkbox && checkbox.checked) {
            const expires = new Date();
            expires.setHours(23, 59, 59, 999);
            document.cookie = `imagePopupClosed=true; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
        }
    }

    closeButton?.addEventListener("click", closePopup);
    bg?.addEventListener("click", closePopup);
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closePopup();
    });
});
</script>

<?php
    }
}
?>
