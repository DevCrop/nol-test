<?php section('content') ?>

<div class="no-section-md">
    <section class="no-sub-whatson-view">
        <div class="no-container-xl">
            <div class="no-sub-whatson-view-inner">
                <div class="no-sub-whatson-view__head">
                    <a href="<?= route('whatson.index') ?>" class="no-sub-whatson-view__back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>

                <div class="no-sub-whatson-view__layout">
                    <div class="no-sub-whatson-view__content">
                        <div class="no-sub-whatson-view__header">
                            <?php if (!empty($work['title'])): ?>
                            <h1 class="no-sub-whatson-view__title f-heading-2 --bold">
                                <?= htmlspecialchars($work['title']) ?>
                            </h1>
                            <?php endif; ?>

                            <?php if (!empty($work['subtitle'])): ?>
                            <h2 class="no-sub-whatson-view__subtitle f-heading-5 --regular">
                                <?= htmlspecialchars($work['subtitle']) ?>
                            </h2>
                            <?php endif; ?>
                        </div>

                        <div class="no-sub-whatson-view__meta">
                            <?php
                            $hasVisibleText = static function ($value) {
                                return trim(strip_tags((string) ($value ?? ''))) !== '';
                            };
                            ?>

                            <?php if ($hasVisibleText($work['genre_name'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">장르</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['genre_name']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['venue_name'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">공연장</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['venue_name']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['period'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">공연기간</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['period']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['running_time'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">러닝타임</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['running_time']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['age_rating'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">관람연령</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['age_rating']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['inquiry'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">문의</span>
                                <span class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= htmlspecialchars($work['inquiry']) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['note'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">비고</span>
                                <div class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= sanitize_html_fragment($work['note'] ?? '') ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasVisibleText($work['seat_prices'] ?? '')): ?>
                            <div class="no-sub-whatson-view__meta-item">
                                <span class="no-sub-whatson-view__meta-label f-body-1 --regular">가격</span>
                                <div class="no-sub-whatson-view__meta-value f-body-1 --semibold">
                                    <?= sanitize_html_fragment($work['seat_prices'] ?? '') ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($work['content_html'])): ?>
                        <div class="no-sub-whatson-view__contents">
                            <h3 class="no-sub-whatson-view__contents-title f-heading-5 --bold">내용</h3>
                            <div class="no-sub-whatson-view__contents-content">
                                <?= sanitize_html_fragment($work['content_html'] ?? '') ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($work['poster_long_html'])): ?>
                        <div class="no-sub-whatson-view__long-poster-wrapper">
                            <div class="no-sub-whatson-view__long-poster" id="longPoster">
                                <div class="no-sub-whatson-view__long-poster-content">
                                    <?= sanitize_html_fragment($work['poster_long_html'] ?? '') ?>
                                </div>
                            </div>
                            <button type="button" class="no-sub-whatson-view__long-poster-toggle" id="longPosterToggle"
                                aria-label="포스터 펼치기">
                                <span class="no-sub-whatson-view__long-poster-toggle-text">펼치기</span>
                                <i class="fa-regular fa-chevron-down"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <aside class="no-sub-whatson-view__sidebar">
                        <div class="no-sub-whatson-view__sidebar-inner">
                            <div class="no-sub-whatson-view__poster-sticky">
                                <figure class="no-sub-whatson-view__poster-img">
                                    <img src="<?= e(safe_asset_url($work['thumb_image'] ?? '')) ?>"
                                        alt="<?= htmlspecialchars($work['title']) ?>">
                                </figure>
                            </div>

                            <?php $safeTicketUrl = safe_url($work['ticket_url'] ?? '', ['http', 'https'], true); ?>
                            <?php if ($safeTicketUrl !== ''): ?>
                            <div class="no-sub-whatson-view__ticket">
                                <a href="<?= e($safeTicketUrl) ?>" target="_blank"
                                    rel="noopener noreferrer" class="no-sub-whatson-view__ticket-link">
                                    <span class="f-body-1 --bold">예매하기</span>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</div>

<?php end_section() ?>

<?php section('portal') ?>
<?= include_view('components.popup'); ?>
<?php end_section() ?>

<?php section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const longPosterToggle = document.getElementById('longPosterToggle');

    if (!longPosterToggle) {
        return;
    }

    longPosterToggle.addEventListener('click', function() {
        const longPoster = document.getElementById('longPoster');
        const toggleText = this.querySelector('.no-sub-whatson-view__long-poster-toggle-text');
        const toggleIcon = this.querySelector('i');

        if (longPoster.classList.contains('is-expanded')) {
            longPoster.classList.remove('is-expanded');
            toggleText.textContent = '펼치기';
            toggleIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        } else {
            longPoster.classList.add('is-expanded');
            toggleText.textContent = '접기';
            toggleIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        }
    });
});
</script>
<?php end_section() ?>
