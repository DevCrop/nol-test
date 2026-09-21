<!-- 로고 마퀴 -->
<?php
$cacheDate = date('YmdHis'); // 연월일시분초
// 공연장 링크 설정
$marqueeLinks = [
    1 => 'https://www.bluesquare.kr', // 블루스퀘어
    2 => '#', // NOL 씨어터 대학로
    3 => 'https://www.coexartium.co.kr', // NOL 씨어터 코엑스 우리은행홀
    4 => 'http://www.solpay-square.com', // NOL 씨어터 합정
    5 => 'http://sohyangtheater.co.kr', // 소향
];
?>
<div class="no-section-sm no-marquee-section">
    <div class="no-marquee" data-marquee-duration="30">
        <div class="no-marquee__inner">
            <div class="no-marquee__content">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php $link = $marqueeLinks[$i] ?? '#'; ?>
                    <div class="no-marquee__logo">
                        <?php if ($link !== '#'): ?>
                            <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener noreferrer">
                            <?php endif; ?>
                            <div class="no-theme-change">
                                <img src="<?= base_path('/resource/images/logo/marquee_img_white_' . $i . '.png?v=' . $cacheDate) ?>"
                                    alt="NOL 씨어터 대학로 공연장" class="dark">
                                <img src="<?= base_path('/resource/images/logo/marquee_img_color_' . $i . '.png?v=' . $cacheDate) ?>"
                                    alt="NOL 씨어터 대학로 공연장" class="light">
                            </div>
                            <?php if ($link !== '#'): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>