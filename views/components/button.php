<?php

/**
 * Button Component
 * 
 * @param string $text 버튼 텍스트
 * @param string $href 링크 URL (기본값: '#')
 * @param string $bgColor 배경색 CSS 변수 또는 색상값 (기본값: 'var(--clr-primary-def)')
 * @param string $border 테두리 스타일 (기본값: 'none')
 * @param string $icon 아이콘 클래스 (기본값: 'fa-regular fa-arrow-right')
 * @param string $iconBg 아이콘 배경색 (기본값: 'var(--clr-ui-white)')
 * @param string $iconColor 아이콘 색상 (기본값: null, bgColor와 동일)
 * @param string $textColor 텍스트 색상 (기본값: 'var(--clr-ui-white)')
 * @param string $class 추가 클래스명
 */

// 기본값 설정
$text = $text ?? 'View More';
$href = $href ?? '#';
$bgColor = $bgColor ?? 'var(--clr-primary-def)';
$border = $border ?? 'none';
$icon = $icon ?? 'fa-regular fa-arrow-right';
$iconBg = $iconBg ?? 'var(--clr-ui-white)';
$iconColor = $iconColor ?? $bgColor;
$textColor = $textColor ?? 'var(--clr-ui-white)';
$class = $class ?? '';
?>

<a href="<?= e($href) ?>" class="no-button <?= e($class) ?>"
    style="--button-bg: <?= e($bgColor) ?>; --button-border: <?= e($border) ?>; --button-icon-bg: <?= e($iconBg) ?>; --button-icon-color: <?= e($iconColor) ?>; --button-text-color: <?= e($textColor) ?>;">
    <span class="no-button__text"><?= e($text) ?></span>
    <span class="no-button__icon">
        <i class="<?= e($icon) ?>"></i>
    </span>
</a>