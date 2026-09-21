<?php
$title = $title ?? '제목을 입력하세요';
$showBreadcrumb = $showBreadcrumb ?? true; // 기본값은 true
?>
<section class="no-sub-visual">
    <div class="no-container-xl">
        <div class="no-sub-visual__inner">
            <h1 <?= AOS_TITLE ?> class="f-display-1 --semibold"><?= e($title) ?></h1>
            <?php if ($showBreadcrumb): ?>
            <?= include_view('components.breadcrumb') ?>
            <?php endif; ?>
        </div>
    </div>
</section>