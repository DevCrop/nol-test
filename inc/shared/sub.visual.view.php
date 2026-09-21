<?php if (!empty($data['detail_page_top_image'])) : ?>
    <!-- Render this section if detail_page_top_image exists -->
    <section class="no-board-view-visual">
        <div class="no-board-view-visual-txt">
            <div class="no-container-xl no-clr-text-white">
                <h2 class="no-heading-sitemap"><?= htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="no-body-xl no-pd-sm--t">
                    <?= htmlspecialchars($data['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>
		<div class="no-board-view-visual-img">
			<img src="/uploads/works/<?=$data['detail_page_top_image']?>" alt="">
		</div>
        <div class="no-board-view-visual-bg"></div>
    </section>
<?php else : ?>
    <!-- Render this section if detail_page_top_image does not exist -->
    <section class="no-sub-visual no-sub-visual-works">
        <div class="no-container-xl">
            <div class="no-sub-visual-txt">
                <div class="no-sub-visual-txt-h2">
                    <h2 class="no-display-md">
                        WHAT'S ON
                    </h2>
                </div>
                <div class="no-sub-visual-txt-p">
                    <p class="no-display-xl">WHAT'S ON</p>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="no-board-view-prev">
    <div class="no-container-xl">
        <?php
        // 현재 GET 파라미터 가져오기
        $query_params = $_GET;
        unset($query_params['id']); // 'id' 파라미터는 필요 없으므로 제거

        // 기본 URL 설정
        $base_url = "http://www.bluesquare.kr/pages/whatson/whatson.php";
        
        // GET 파라미터가 있는 경우 쿼리 스트링 생성, 없으면 기본 URL로 설정
        $query_string = !empty($query_params) ? '?' . http_build_query($query_params) : '?search_term=&year=all&place=all&genre=all&end_date=all&page=1';

        // 최종 이동할 URL
        $back_url = $base_url . $query_string;
        ?>
        <a href="<?= htmlspecialchars($back_url) ?>" class="no-btn no-btn__fill --radius-thin" id="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            목록보기
        </a>
    </div>
</div>

