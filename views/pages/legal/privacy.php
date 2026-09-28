<?php section('content') ?>

<?php
// 라우트에서 전달받은 데이터
$policies = $policies ?? [];
$tabs = $tabs ?? [];
$selectedPolicy = $selectedPolicy ?? null;
$selectedId = $selectedId ?? 0;
?>

<div class="no-section-md">
    <div class="no-container-xl">
        <div class="no-sub-notice-inner">
            <div class="no-sub-ui">
                <div class="no-sub-ui-inner">
                    <!-- 적용 날짜 탭 (좌측) -->
                    <?php if (count($tabs) > 0): ?>
                    <aside class="left no-sub-ui-left" <?= AOS_DEFAULT ?>>
                        <div class="no-sub-ui-left-swiper">
                            <div class="swiper no-sub-ui-tabs-swiper">
                                <ul class="swiper-wrapper no-sub-ui-tabs">
                                    <?php foreach ($tabs as $tab): ?>
                                    <li class="swiper-slide no-sub-ui-tab <?= $tab['is_active'] ? 'is-active' : '' ?>"
                                        data-id="<?= $tab['id'] ?>">
                                        <button type="button" class="no-sub-ui-tab__btn privacy-tab-btn">
                                            <span class="no-sub-ui-tab__bullet"></span>
                                            <span
                                                class="no-sub-ui-tab__label"><?= htmlspecialchars($tab['date']) ?></span>
                                        </button>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </aside>
                    <?php endif; ?>

                    <!-- 내용 영역 (우측) -->
                    <div class="no-sub-ui-right-wrap" <?= AOS_MEDIUM ?>>
                        <div class="right no-sub-ui-right">
                            <?php if ($selectedPolicy): ?>
                            <div class="no-sub-ui-panel is-active" data-panel-id="<?= $selectedId ?>">
                                <div class="no-sub-notice-view-inner">
                                    <?= include_view('components.board-view', [
                                            'backUrl' => '/',
                                            'title' => htmlspecialchars($selectedPolicy['title']),
                                            'date' => $selectedPolicy['apply_date'] ? date('Y.m.d', strtotime($selectedPolicy['apply_date'])) : '',
                                            'content' => $selectedPolicy['content'] ?? '',
                                            'prevPost' => null,
                                            'nextPost' => null,
                                            'showNav' => false,
                                        ]) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php foreach ($policies as $policy): ?>
                            <?php if ((int)$policy['id'] !== $selectedId): ?>
                            <div class="no-sub-ui-panel" data-panel-id="<?= (int)$policy['id'] ?>">
                                <div class="no-sub-notice-view-inner">
                                    <?= include_view('components.board-view', [
                                                'backUrl' => '/',
                                                'title' => htmlspecialchars($policy['title']),
                                                'date' => $policy['apply_date'] ? date('Y.m.d', strtotime($policy['apply_date'])) : '',
                                                'content' => $policy['content'] ?? '',
                                                'prevPost' => null,
                                                'nextPost' => null,
                                                'showNav' => false,
                                            ]) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>

<?php section('script') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.privacy-tab-btn');
    const panels = document.querySelectorAll('.no-sub-ui-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabItem = this.closest('.no-sub-ui-tab');
            const targetId = parseInt(tabItem.dataset.id);

            // 탭 활성화
            document.querySelectorAll('.no-sub-ui-tab').forEach(tab => {
                tab.classList.remove('is-active');
            });
            tabItem.classList.add('is-active');

            // 패널 표시
            panels.forEach(panel => {
                const panelId = parseInt(panel.dataset.panelId);
                if (panelId === targetId) {
                    panel.classList.add('is-active');
                } else {
                    panel.classList.remove('is-active');
                }
            });

            // URL 업데이트 (히스토리 API 사용)
            const url = new URL(window.location);
            url.searchParams.set('id', targetId);
            window.history.pushState({
                id: targetId
            }, '', url);
        });
    });

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