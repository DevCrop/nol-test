<?php

use Database\DB;

// "오늘 하루 그만 보기" 쿠키 확인
$popupClosed = isset($_COOKIE['textPopupClosed']) && $_COOKIE['textPopupClosed'] === 'true';
$db = DB::getInstance();

if (!$popupClosed) {
    // 현재 경로 가져오기 (라우트 시스템 사용 또는 REQUEST_URI)
    $currentPath = '/';

    // 라우트 시스템이 있으면 현재 라우트의 URI 사용
    if (function_exists('current_route')) {
        $currentRoute = current_route();
        if ($currentRoute) {
            $currentPath = $currentRoute->getUri();
        } else {
            // 라우트를 찾지 못한 경우 REQUEST_URI 사용
            $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        }
    } else {
        // 라우트 시스템이 없는 경우 REQUEST_URI 사용
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    // 경로 정규화 (앞뒤 슬래시 정리)
    $currentPath = '/' . trim($currentPath, '/');
    if ($currentPath === '') {
        $currentPath = '/';
    }

    $sql = "
        SELECT id, title, description, popup_path
        FROM nb_popups
        WHERE is_active = 1
          AND popup_path = :current_path
          AND popup_type = 2
          AND (
                is_unlimited = 1
                OR (
                    is_unlimited = 0
                    AND (start_at IS NULL OR start_at <= NOW())
                    AND (end_at IS NULL OR end_at >= NOW())
                )
              )
        ORDER BY sort_no ASC, id ASC
        LIMIT 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':current_path' => $currentPath]);
    $popup = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($popup && !empty($popup['title'])) {
        $title = htmlspecialchars((string)($popup['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = !empty($popup['description']) ? sanitize_html_fragment((string)$popup['description']) : '';
?>

<div class="text-popup-wrap">
    <div class="text-popup">
        <div class="text-popup-header">
            <h3 class="text-popup-title"><?= $title ?></h3>
        </div>
        <?php if (!empty($description)): ?>
        <div class="text-popup-body">
            <div class="text-popup-description"><?= $description ?></div>
        </div>
        <?php endif; ?>
        <div class="text-popup-footer">
            <label class="text-popup-checkbox-label">
                <input type="checkbox" id="textPopupCloseCheck">
                <span class="text-popup-checkbox-box" aria-hidden="true">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                </span>
                <span class="text-popup-checkbox-text">오늘 하루 그만 보기</span>
            </label>
            <button type="button" class="text-popup-close" data-action="close">닫기</button>
        </div>
    </div>
    <div class="text-popup-bg"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const wrap = document.querySelector(".text-popup-wrap");
    const bg = document.querySelector(".text-popup-bg");
    const closeButton = document.querySelector(".text-popup-close");
    const checkbox = document.getElementById("textPopupCloseCheck");

    if (!wrap) return;

    // 팝업 닫기
    function closePopup() {
        wrap.style.display = "none";
        if (checkbox && checkbox.checked) {
            const expires = new Date();
            expires.setHours(23, 59, 59, 999); // 오늘 밤 23:59:59까지
            document.cookie = `textPopupClosed=true; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
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
