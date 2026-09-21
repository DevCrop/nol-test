<?php
// ✅ 게시판 권한 확인
$role_info = getBoardRole($board_no, $NO_USR_LEV);

// ✅ $role_info가 존재하는지 체크 후 접근 (PHP 7.4에서 Undefined Index 방지)
if (isset($role_info[0]['role_view']) && $role_info[0]['role_view'] === "N") {
    alert("접근 권한이 없습니다.");
}

// ✅ 데이터 기본값 설정 (PHP 7.4에서 Undefined Index 방지)
$data = isset($data) ? $data : [];
$data['title'] = isset($data['title']) ? $data['title'] : '';
$data['regdate'] = isset($data['regdate']) ? $data['regdate'] : '';
$data['contents'] = isset($data['contents']) ? $data['contents'] : '';
$searchKeyword = isset($searchKeyword) ? $searchKeyword : '';
$page = isset($page) ? (int)$page : 1;
?>

<section class="no-pd-2xl--y no-board-view-default">
    <div class="no-container-xl">
        <div class="no-board-view-body">
            <div class="no-board-view-title">
                <h3 class="no-heading-lg"><?= htmlspecialchars_decode($data['title'], ENT_QUOTES | ENT_HTML5) ?></h3>
                <span class="no-body-base no-pd-sm--t"><?= !empty($data['regdate']) ? date("Y.m.d", strtotime($data['regdate'])) : '' ?></span>
            </div>
            <div class="no-board-view-content no-pd-xl--t no-pd-lg--b">
                <div>
                    <div><?= \Security\HtmlSanitizer::clean((string) $data['contents']) ?></div>
                </div>
				<?php include_once $STATIC_ROOT . '/pages/board/components/download.php'; ?>
            </div>
        </div>

        <div class="no-pd-xl--t --flex-center">
            <a href="./board.list.php?board_no=<?= (int)$board_no ?>&RtsearchKeyword=<?= htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8') ?>&page=<?= (int)$page ?>"  
               class="no-btn no-btn__fill --radius-thin">
                목록으로  
            </a>
        </div>
    </div>
</section>
