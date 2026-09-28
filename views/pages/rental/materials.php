<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '대관 자료']) ?>

    <section class="no-sub-materials">
        <div class="no-container-xl">
            <div class="no-sub-materials-inner">
                <?php
                // 라우트에서 전달받은 데이터
                $materialsByCategory = $materialsByCategory ?? [];
                $categories = $categories ?? [];
                ?>
                <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                <?php
                        $categoryNo = (int)$category['no'];
                        $categoryData = $materialsByCategory[$categoryNo] ?? null;
                        if (!$categoryData || count($categoryData['items']) === 0) {
                            continue;
                        }
                        ?>
                <div class="no-sub-materials-block">
                    <h2 class="f-heading-2 --bold" <?= AOS_TITLE ?>><?= e($categoryData['category_name']) ?></h2>
                    <ul class="no-sub-download-list">
                        <?php foreach ($categoryData['items'] as $item): ?>
                        <?php if (!empty($item['file_attach_1'])): ?>
                        <?php
                                        // 파일 경로 처리: 이미 /uploads/board/로 시작하면 그대로 사용, 아니면 추가
                                        $filePath = $item['file_attach_1'];
                                        if (strpos($filePath, '/uploads/board/') !== 0) {
                                            $filePath = '/uploads/board/' . ltrim($filePath, '/');
                                        }
                                        ?>
                        <li class="no-sub-download-list-item" <?= AOS_DEFAULT ?>>
                            <a href="<?= base_path($filePath) ?>" download="<?= e($item['file_attach_origin_1'] ?: $item['title']) ?>">
                                <h3 class="f-body-1 --medium"><?= e($item['title']) ?></h3>
                                <div class="no-button-download">
                                    <span>다운로드</span>
                                    <i class="fa-regular fa-download"></i>
                                </div>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="no-sub-materials-block">
                    <p class="f-body-2 --regular">등록된 자료가 없습니다.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>