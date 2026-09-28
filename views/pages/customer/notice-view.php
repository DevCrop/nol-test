<?php section('content') ?>

<div class=" no-section-md">
    <?= include_view('components.sub-visual', ['title' => '공지사항']) ?>

    <section class="no-sub-notice-view">
        <div class="no-container-xl">
            <div class="no-sub-notice-inner">
                <div class="no-sub-ui">
                    <div class="no-sub-ui-inner">
                        <?php
                        // 라우트에서 전달받은 데이터
                        $noticeData = $noticeData ?? null;
                        $categoryNo = $categoryNo ?? 0;
                        $categories = $categories ?? [];
                        $prevPost = $prevPost ?? null;
                        $nextPost = $nextPost ?? null;

                        if (!$noticeData) {
                            // 게시글이 없으면 목록으로 리다이렉트
                            header('Location: ' . route('customer.notice', ['category_no' => $categoryNo ?: null]));
                            exit;
                        }

                        // 목록으로 돌아가기 URL (카테고리 유지)
                        $backUrl = route('customer.notice', ['category_no' => $categoryNo ?: null]);
                        ?>

                        <!-- 카테고리 -->
                        <?php if (count($categories) > 0): ?>
                        <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                            <div class="no-sub-ui-left-swiper">
                                <div class="swiper no-sub-ui-tabs-swiper">
                                    <ul class="swiper-wrapper no-sub-ui-tabs">
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $categoryNo === 0 ? 'is-active' : '' ?>">
                                            <a href="<?= route('customer.notice') ?>" class="no-sub-ui-tab__btn">
                                                <span class="no-sub-ui-tab__bullet"></span>
                                                <span class="no-sub-ui-tab__label">전체</span>
                                            </a>
                                        </li>
                                        <?php foreach ($categories as $category): ?>
                                        <li
                                            class="swiper-slide no-sub-ui-tab <?= $categoryNo === $category['no'] ? 'is-active' : '' ?>">
                                            <a href="<?= route('customer.notice', ['category_no' => $category['no']]) ?>"
                                                class="no-sub-ui-tab__btn">
                                                <span class="no-sub-ui-tab__bullet"></span>
                                                <span class="no-sub-ui-tab__label"><?= e($category['name']) ?></span>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </aside>
                        <?php endif; ?>

                        <!-- 내용 영역 -->
                        <div class="no-sub-ui-right-wrap" <?= AOS_MEDIUM ?>>
                            <div class="right no-sub-ui-right">
                                <div class="no-sub-notice-view-inner">
                                    <?= include_view('components.board-view', [
                                        'backUrl' => $backUrl,
                                        'title' => $noticeData['title'],
                                        'date' => date('Y.m.d', strtotime($noticeData['regdate'])),
                                        'content' => $noticeData['contents'],
                                        'writeName' => $noticeData['write_name'] ?? '',
                                        'thumbImage' => $noticeData['thumb_image'] ?? '',
                                        'files' => [
                                            ['file' => $noticeData['file_attach_1'] ?? '', 'origin' => $noticeData['file_attach_origin_1'] ?? ''],
                                            ['file' => $noticeData['file_attach_2'] ?? '', 'origin' => $noticeData['file_attach_origin_2'] ?? ''],
                                            ['file' => $noticeData['file_attach_3'] ?? '', 'origin' => $noticeData['file_attach_origin_3'] ?? ''],
                                            ['file' => $noticeData['file_attach_4'] ?? '', 'origin' => $noticeData['file_attach_origin_4'] ?? ''],
                                            ['file' => $noticeData['file_attach_5'] ?? '', 'origin' => $noticeData['file_attach_origin_5'] ?? ''],
                                        ],
                                        'isFile' => $noticeData['isFile'] ?? 'N',
                                        'directUrl' => $noticeData['direct_url'] ?? '',
                                        'prevPost' => $prevPost,
                                        'nextPost' => $nextPost,
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>

<?php section('script') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Swiper 초기화 (모바일용)
    if (typeof Swiper !== 'undefined') {
        const tabsSwiper = new Swiper('.no-sub-ui-tabs-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true,
        });
    }
});
</script>
<?php end_section() ?>