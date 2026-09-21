<?php

/**
 * Guide Component
 * 
 * @param string $title 제목
 * @param array $items 안내사항 항목 배열
 *   - title: 섹션 제목
 *   - points: 서브 포인트 배열
 */

$title = $title ?? '안내사항';
$items = $items ?? [];
$class = trim((string) ($class ?? ''));
?>

<div class="no-guide<?= $class !== '' ? ' ' . e($class) : '' ?>">
    <?php if ($title): ?>
        <h2 class="no-guide__title f-heading-4 --bold"><?= e($title) ?></h2>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
        <ul class="no-guide__list">
            <?php foreach ($items as $item): ?>
                <li class="no-guide__item">
                    <div class="no-guide__item-icon">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </div>
                    <div class="no-guide__item-content">
                        <h3 class="no-guide__item-title f-body-2 --semibold"><?= e($item['title']) ?></h3>
                        <?php if (!empty($item['points'])): ?>
                            <ul class="no-guide__item-points">
                                <?php foreach ($item['points'] as $point): ?>
                                    <li class="no-guide__item-point f-body-3 --regular"><?= e($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>