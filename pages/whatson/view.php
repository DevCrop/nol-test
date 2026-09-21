<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/base.class.php';
include_once $STATIC_ROOT . '/inc/layouts/head.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "유효한 ID가 아닙니다.";
    exit;
}

// SQL 쿼리 작성
$sql = "SELECT * FROM nb_works WHERE id = :id";
$data = DB::query($sql, [':id' => $id]);

if (empty($data)) {
    echo "데이터를 조회할 수 없습니다.";
    exit;
}

$data = $data[0]; // 첫 번째 결과만 사용
?>

<?php include_once $STATIC_ROOT . '/inc/layouts/header.php'; ?>
<?php include_once $STATIC_ROOT . '/inc/shared/sub.visual.view.php'; ?>

<main class="no-sub no-board">
    <section class="no-pd-2xl--y">
        <div class="no-board-view-works">
            <div class="no-container-xl">
                <div class="cnt">
                    <ul>
                        <li class="left">
                            <div>
                                <img src="/uploads/works/<?= htmlspecialchars($data['poster_image'] ?? '') ?>" alt="<?= htmlspecialchars($data['title'] ?? '') ?>">
                                <div class="no-board-view-btn">
                                    <?php if (!empty($data['ticket_link'])): ?>
                                        <a href="<?= htmlspecialchars($data['ticket_link']) ?>" target="_blank" class="no-btn no-btn__fill--primary --radius-thin">티켓 예매하기</a>
                                    <?php endif; ?>

                                    <?php if (!empty($data['opera_glass_link'])): ?>
                                        <a href="<?= htmlspecialchars($data['opera_glass_link']) ?>" class="no-btn no-btn__outline--white --radius-thin">
                                            <i class="fa-duotone fa-solid fa-glasses"></i>
                                            오페라글라스 예약하기
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
			
                        <li class="right">
                            <div class="no-board-view-info">
                                <h2 class="no-heading-lg">공연 정보</h2>
                                <div class="no-pd-lg--t">
                                    <ul class="no-board-view-info__wrap">
										<?php if (!empty($data['title'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">제목</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?=$data['title']?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!empty($data['genre'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">장르</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars($genres[$data['genre']] ?? '알 수 없음') ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['start_date']) && !empty($data['end_date'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">일정</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars($data['start_date']) ?> - <?= htmlspecialchars($data['end_date']) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['show_time'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">공연 시간 정보</h4>
                                                <ul class="time">
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= nl2br(htmlspecialchars($data['show_time'])) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['place'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">장소</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars(getPlaceDisplayName($data['place'], $data['start_date'] ?? null)) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['running_time'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">러닝타임</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars($data['running_time']) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                
                                        <?php if (!empty($data['price'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">티켓가격</h4>
                                                <div class="price">
                                                    <div>
                                                        <?= htmlspecialchars_decode($data['price']) ?>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['viewing_age'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">관람연령</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars($data['viewing_age']) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['contact'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">문의</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= htmlspecialchars($data['contact']) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>

                                        <?php if (!empty($data['notes'])): ?>
                                            <li>
                                                <h4 class="no-heading-md">비고</h4>
                                                <ul>
                                                    <li>
                                                        <p class="no-body-md">
                                                            <?= nl2br(htmlspecialchars($data['notes'])) ?>
                                                        </p>
                                                    </li>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <?php if (!empty($data['contents'])): ?>
                                <div class="no-board-view-img">
                                    <h2 class="no-heading-lg">상세내용</h2>
                                    <div class="no-pd-lg--t">
                                        <div class="more-info-wrap">
                                            <?= \Security\HtmlSanitizer::clean((string) $data['contents']) ?>
                                            <div class="more-info-wrap-bg"></div>
                                        </div>
                                        <div class="more-info-wrap-button">
                                            <button class="no-btn" type="button">
                                                <span class="button-text">더보기</span>
                                                <i class="fa-regular fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<script>


document.addEventListener("DOMContentLoaded", function () {
    const toggleTopButtons = () => {
        if (window.location.pathname === "/pages/whatson/view.php") {
            const elements = document.querySelectorAll(".no-top-btn");
            if (window.innerWidth < 768) {
                elements.forEach(element => element.style.display = "none");
            } else {
                elements.forEach(element => element.style.display = "");
            }
        }
    };

    // 초기 실행
    toggleTopButtons();

    // 윈도우 리사이즈 이벤트 최적화 (디바운스 적용)
    let resizeTimer;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(toggleTopButtons, 150);
    });
});



</script>

<?php include_once $STATIC_ROOT . '/inc/layouts/footer.php'; ?>
