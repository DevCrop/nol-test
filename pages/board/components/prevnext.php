<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/inc/lib/base.class.php";

try {
    // Obtain PDO instance
    $db = DB::getInstance();

    // Previous post query
    $query = "SELECT title, no FROM nb_board 
              WHERE board_no = :board_no AND is_notice != 'Y' AND no < :no 
              ORDER BY no DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':board_no', $board_no, PDO::PARAM_INT);
    $stmt->bindParam(':no', $no, PDO::PARAM_INT);
    $stmt->execute();
    $r1 = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set previous post title and link
    $prev_title = $r1 ? cutstr(stripslashes(htmlspecialchars($r1['title'])), 35) : "이전글이 없습니다.";
    $prev_link = $r1 ? $_SERVER['PHP_SELF'] . "?no=" . htmlspecialchars($r1['no'], ENT_QUOTES, 'UTF-8') . "&board_no=" . htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') : 'javascript:void(0)';
    $prevBack = $r1 ? '' : 'onClick="redirectToList();"';

    // Next post query
    $query = "SELECT title, no FROM nb_board 
              WHERE board_no = :board_no AND is_notice != 'Y' AND no > :no 
              ORDER BY no ASC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':board_no', $board_no, PDO::PARAM_INT);
    $stmt->bindParam(':no', $no, PDO::PARAM_INT);
    $stmt->execute();
    $r2 = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set next post title and link
    $next_title = $r2 ? cutstr(stripslashes(htmlspecialchars($r2['title'])), 35) : "다음글이 없습니다.";
    $next_link = $r2 ? $_SERVER['PHP_SELF'] . "?no=" . htmlspecialchars($r2['no'], ENT_QUOTES, 'UTF-8') . "&board_no=" . htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') : 'javascript:void(0)';
    $nextBack = $r2 ? '' : 'onClick="redirectToList();"';

} catch (PDOException $e) {
    echo "데이터를 불러오는 중 오류가 발생했습니다: " . htmlspecialchars(blue_safe_error($e), ENT_QUOTES, 'UTF-8');
    exit;
}

?>

<div class="no-board-nav mb60">
    <ul class="no-board-nav__items">
        <li>
            <a href="<?= $prev_link ?>" <?= $prevBack ?> class="no-board-nav__link">
                <div class="no-board-nav__division">
                    <i class="fa-sharp fa-regular fa-angle-up" style="color: #000000;"></i>
                    <p>이전글</p>
                    <span class="no-board-nav__title"><?= $prev_title ?></span>
                </div>
                <span class="no-board-nav__date"><?= getChangeDate($data['regdate'], "Y.m.d") ?></span>
            </a>
        </li>

        <li>
            <a href="<?= $next_link ?>" <?= $nextBack ?> class="no-board-nav__link">
                <div class="no-board-nav__division">
                    <i class="fa-sharp fa-regular fa-angle-down" style="color: #000000;"></i>
                    <p>다음글</p>
                    <span class="no-board-nav__title"><?= $next_title ?></span>
                </div>
                <span class="no-board-nav__date"><?= getChangeDate($data['regdate'], "Y.m.d") ?></span>
            </a>
        </li>
    </ul>

    <div class="view-btn">
        <a href="./board.list.php?board_no=<?= htmlspecialchars($board_no, ENT_QUOTES, 'UTF-8') ?>&category_no=<?= htmlspecialchars($cate_no ?? '', ENT_QUOTES, 'UTF-8') ?>&RtsearchKeyword=<?= htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8') ?>&RtsearchColumn=<?= htmlspecialchars($searchColumn, ENT_QUOTES, 'UTF-8') ?>&page=<?= htmlspecialchars($page ?? 1, ENT_QUOTES, 'UTF-8') ?>">목록</a>
    </div>
</div>

<?php
$cate_no = $_GET['category_no'] ?? '';
?>

<script>
function redirectToList() {
    alert('정보를 찾을 수 없습니다.');
    const board_no = searchParam('board_no');
    location.href = './board.list.php?board_no=' + board_no;
}

function searchParam(key) {
    return new URLSearchParams(location.search).get(key);
}
</script>
