<?php include_once $_SERVER['DOCUMENT_ROOT'].'/inc/lib/base.class.php'; ?>

<!-- dev -->
<?php include_once $STATIC_ROOT.'/inc/layouts/head.php'; ?>

<!-- css, js -->
<?php include_once $STATIC_ROOT.'/inc/layouts/header.php'; ?>

<?php
try {
    // ✅ DB 인스턴스 가져오기
    $connect = DB::getInstance();

    // ✅ GET 파라미터 가져오기
    $q = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
    $rows = [];

    if (!empty($q)) {
        // ✅ Query 작성
        $query = "
            SELECT
                '공지사항' AS type,
                nb.no AS id, 
                nb.title AS title, 
                nb.regdate AS regdate
            FROM nb_board AS nb
            WHERE nb.board_no = 8
            AND nb.title LIKE :title

            UNION ALL

            SELECT
                '작품' AS type,
                nw.id AS id, 
                nw.title AS title, 
                nw.start_date AS regdate
            FROM nb_works AS nw
            WHERE nw.title LIKE :title
            ORDER BY regdate DESC
        ";

        // ✅ Query 준비 및 실행
        $stmt = $connect->prepare($query);
        $stmt->bindValue(':title', "%$q%", PDO::PARAM_STR);
        $stmt->execute();

        // ✅ 결과 가져오기
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Database Error: " . htmlspecialchars(blue_safe_error($e), ENT_QUOTES, 'UTF-8');
} catch (Exception $e) {
    echo "General Error: " . htmlspecialchars(blue_safe_error($e), ENT_QUOTES, 'UTF-8');
}
?>

<!-- contents -->
<main class="no-sub no-pd-md--t">
    <section class="no-pd-2xl--y">
        <div class="no-container-md">
            <div class="no-sub-search">
                <div class="no-sub-search__input">
                    <form method="GET">
                        <h4 class="no-heading-lg">Your search</h4>
                        <div class="no-pd-sm--t">
                            <div class="no-form-search no-form-search__type_A">
                                <input type="text" name="search_term" id="search_term" placeholder="검색어를 입력해주세요."
                                    value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="no-form-search__type_A-icon">
                                    <i class="fa-light fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="no-sub-search__result no-pd-xl--t">
                    <h4 class="no-heading-lg">검색 결과</h4>
                    <div class="no-pd-md--t">
                        <?php if (!empty($q) && !empty($rows)) : ?>
                            <ul class="no-sub-search__list f fd-c no-gap-sm">
                                <?php foreach ($rows as $row) :
                                    // ✅ 각 타입에 따라 링크와 제목 구성
                                    $pageUrl = ($row['type'] === '공지사항') 
                                        ? "/pages/board/board.view.php?board_no=8&no=" . (int)$row['id']
                                        : "/pages/whatson/view.php?id=" . (int)$row['id'];

                                    // ✅ 제목의 HTML 인코딩을 제거하여 원래 문자열 복원
                                    $decodedTitle = htmlspecialchars_decode($row['title'], ENT_QUOTES | ENT_HTML5);

                                    // ✅ 검색어 강조 표시
                                    if (stripos($decodedTitle, $q) !== false) {
                                        $highlightedTitle = str_ireplace($q, "<strong>$q</strong>", $decodedTitle);
                                    } else {
                                        $highlightedTitle = $decodedTitle;
                                    }
                                ?>
                                <li class="no-sub-search__item --card">
                                    <a href="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>">
                                        <h5 class="no-pd-xs--b no-body-lg">
                                            [<?= htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') ?>] <?= $highlightedTitle ?>
                                        </h5>
                                        <span class="no-body-sm"><?= htmlspecialchars($row['regdate'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif (!empty($q)) : ?>
                            <p class="clr-base-white no-body-lg">데이터가 없습니다.</p>
                        <?php else : ?>
                            <p class="clr-base-white no-body-lg">검색어를 입력해주세요.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once $STATIC_ROOT.'/inc/layouts/footer.php'; ?>
