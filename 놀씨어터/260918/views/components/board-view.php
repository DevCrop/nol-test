<?php
// Props 기본값 설정
$backUrl = $backUrl ?? 'javascript:history.back();';
$title = $title ?? '';
$date = $date ?? '';
$content = $content ?? '';
$writeName = $writeName ?? '';
$thumbImage = $thumbImage ?? '';
$files = $files ?? [];
$isFile = $isFile ?? 'N';
$boardNo = $boardNo ?? null;
$directUrl = $directUrl ?? '';
$prevPost = $prevPost ?? null; // ['title' => '...', 'url' => '...']
$nextPost = $nextPost ?? null; // ['title' => '...', 'url' => '...']
$showNav = $showNav ?? true; // prev/next 네비게이션 표시 여부

// 첨부파일 필터링 (빈 파일 제거)
$attachedFiles = array_filter($files, function ($file) {
    return !empty($file['file']);
});
?>

<div class="no-board-view">
    <!-- Head: 뒤로가기 버튼 -->
    <div class="no-board-view__head">
        <a href="<?= e($backUrl) ?>" class="no-board-view__back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>

    <!-- Head: 제목, 날짜 -->
    <?php if (!empty($title) || !empty($date)): ?>
        <div class="no-board-view__header">
            <?php if (!empty($title)): ?>
                <h1 class="no-board-view__title f-heading-4 --bold">
                    <?= e($title) ?>
                </h1>
            <?php endif; ?>
            <div class="no-board-view__meta">
                <?php if (!empty($date)): ?>
                    <div class="no-board-view__date">
                        <span class="f-body-2 --regular"><?= e($date) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 썸네일 이미지 -->
    <?php if (!empty($thumbImage)): ?>
        <div class="no-board-view__thumbnail">
            <img src="<?= base_path($thumbImage) ?>" alt="<?= e($title) ?>">
        </div>
    <?php endif; ?>

    <!-- Contents: 에디터 내용 -->
    <?php if (!empty($content)): ?>
        <div class="no-board-view__contents">
            <div class="no-board-view__content">
                <?= html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 첨부파일 -->
    <?php
    // 파일 배열을 인덱스와 함께 처리
    $attachedFilesWithIndex = [];
    if (is_array($files)) {
        foreach ($files as $index => $file) {
            if (!empty($file['file'])) {
                $attachedFilesWithIndex[] = [
                    'file' => $file['file'],
                    'origin' => $file['origin'] ?? '',
                    'index' => $index + 1 // 1부터 시작하는 인덱스 (file_attach_1, file_attach_2, ...)
                ];
            }
        }
    }
    ?>
    <?php if (count($attachedFilesWithIndex) > 0): ?>
        <div class="no-board-view__files">
            <h3 class="no-board-view__files-title f-body-1 --bold">첨부파일</h3>
            <ul class="no-board-view__files-list">
                <?php foreach ($attachedFilesWithIndex as $file): ?>
                    <li class="no-board-view__files-item">
                        <?php if ($boardNo): ?>
                            <a href="/inc/lib/board.file.download.php?no=<?= htmlspecialchars($boardNo) ?>&fld=attach<?= $file['index'] ?>"
                                class="no-board-view__files-link" target="_blank">
                                <i class="fa-solid fa-file"></i>
                                <span class="f-body-2 --regular"><?= e($file['origin'] ?: basename($file['file'])) ?></span>
                                <i class="fa-solid fa-download"></i>
                            </a>
                        <?php else: ?>
                            <a href="/uploads/board/<?= e($file['file']) ?>"
                                download="<?= e($file['origin'] ?: basename($file['file'])) ?>" class="no-board-view__files-link">
                                <i class="fa-solid fa-file"></i>
                                <span class="f-body-2 --regular"><?= e($file['origin'] ?: basename($file['file'])) ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- 직접 연결 URL -->
    <?php if (!empty($directUrl)): ?>
        <div class="no-board-view__direct-url">
            <a href="<?= e($directUrl) ?>" target="_blank" rel="noopener noreferrer" class="no-board-view__direct-link">
                <i class="fa-solid fa-external-link"></i>
                <span class="f-body-2 --regular">관련 링크 바로가기</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- Prev/Next: 이전글/다음글 -->
    <?php if ($showNav): ?>
        <div class="no-board-view__nav">
            <?php if ($prevPost): ?>
                <a href="<?= e($prevPost['url'] ?? '#') ?>" class="no-board-view__nav-item --prev">
                    <div class="no-board-view__nav-button">
                        <i class="fa-solid fa-chevron-left"></i>
                    </div>
                    <span class="no-board-view__nav-title"><?= e($prevPost['title'] ?? '') ?></span>
                </a>
            <?php else: ?>
                <div class="no-board-view__nav-item --prev --reserve">
                    <div class="no-board-view__nav-button">
                        <i class="fa-solid fa-chevron-left"></i>
                    </div>
                    <span class="no-board-view__nav-title">이전글이 없습니다</span>
                </div>
            <?php endif; ?>

            <?php if ($nextPost): ?>
                <a href="<?= e($nextPost['url'] ?? '#') ?>" class="no-board-view__nav-item --next">
                    <span class="no-board-view__nav-title"><?= e($nextPost['title'] ?? '') ?></span>
                    <div class="no-board-view__nav-button">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </a>
            <?php else: ?>
                <div class="no-board-view__nav-item --next --reserve">
                    <span class="no-board-view__nav-title">다음글이 없습니다</span>
                    <div class="no-board-view__nav-button">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>