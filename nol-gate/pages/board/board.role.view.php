<?php
    include_once "../../../inc/lib/base.class.php";
    $role->redirectIfReadOnly();

    $connect = DB::getInstance();
    
    $no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);

    try {
        $stmt = $connect->prepare("SELECT no, title, skin, regdate, view_yn, secret_yn, sort_no, list_size, comment_yn 
                                   FROM nb_board_manage 
                                   WHERE no = :no");
        $stmt->execute([':no' => $no]);
        $board_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$board_info) {
            throw new Exception("정보를 찾을 수 없습니다");
        }

    } catch (Exception $e) {
        ClientFault::abortPage($e);
        exit;
    }

    include_once "../../inc/admin.title.php";
    include_once "../../inc/admin.css.php";
    include_once "../../inc/admin.js.php";
?>

<style>
#board_no-button {
    display: none;
}
</style>
</head>

<body>
    <div class="no-wrap">
        <!-- Header -->
        <?php include_once "../../inc/admin.header.php"; ?>

        <!-- Main -->
        <main class="no-app no-container">
            <!-- Drawer -->
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <!-- Contents -->
            <form id="frm" name="frm" method="post">
                <input type="hidden" name="mode" id="mode">
                <input type="hidden" name="board_no" id="board_no" value="<?=$no?>">
                <section class="no-content">
                    <!-- Page Title -->
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?=$board_info['title']?> 게시판 권한</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span>게시판</span></li>
                                        <li class="no-breadcrumb-item"><span>게시판 권한</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contents -->
                    <div class="no-content-container">
                        <div class="no-card">
                            <div class="no-card-header">
                                <h2 class="no-card-title">게시글 관리</h2>
                            </div>

                            <div class="no-card-body">
                                <div class="no-table-responsive no-check-box-center">
                                    <table class="no-table">
                                        <caption class="no-blind">번호, 게시판 이름, 공지, 제목, 작성자, 작성일, 조회수, 관리로 구성된 게시글 관리표
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th scope="col">등급</th>
                                                <th scope="col">목록</th>
                                                <th scope="col">쓰기</th>
                                                <th scope="col">읽기</th>
                                                <th scope="col">수정</th>
                                                <th scope="col">삭제</th>
                                                <th scope="col">댓글</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $stmt = $connect->prepare("SELECT no, board_no, lev_no, role_write, role_edit, role_view, role_list, role_delete, role_comment 
                                                                           FROM nb_board_lev_manage 
                                                                           WHERE sitekey = :sitekey AND lev_no = 0 AND board_no = :board_no");
                                                $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $no]);
                                                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                                                if ($data && is_array($data)) {
                                                    $role_checked = [
                                                        'list' => ($data['role_list'] == "Y") ? "checked" : "",
                                                        'write' => ($data['role_write'] == "Y") ? "checked" : "",
                                                        'view' => ($data['role_view'] == "Y") ? "checked" : "",
                                                        'edit' => ($data['role_edit'] == "Y") ? "checked" : "",
                                                        'delete' => ($data['role_delete'] == "Y") ? "checked" : "",
                                                        'comment' => ($data['role_comment'] == "Y") ? "checked" : "",
                                                    ];
                                                } else {
                                                    // 데이터가 없을 때 기본값 처리
                                                    $role_checked = [
                                                        'list' => "",
                                                        'write' => "",
                                                        'view' => "",
                                                        'edit' => "",
                                                        'delete' => "",
                                                        'comment' => "",
                                                    ];
                                                }

                                            } catch (Exception $e) {
                                                ClientFault::abortPage($e);
                                            }
                                            
                                            ?>
                                            <tr>
                                                <td><span>비회원 <input type="hidden" name="nb_auth_lev_no[]"
                                                            value="0"></span></td>
                                                <?php foreach ($role_checked as $role => $checked): ?>
                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label for="role_<?=$role?>">
                                                            <input type="checkbox" name="role_<?=$role?>[0]"
                                                                class="no-chk" id="role_<?=$role?>" value="Y"
                                                                <?=$checked?> />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <?php endforeach; ?>
                                            </tr>

                                            <?php
                                            // 멤버 레벨별 권한 가져오기
                                            try {
                                                $stmt = $connect->prepare("SELECT no, lev_name FROM nb_member_level WHERE sitekey = :sitekey ORDER BY no ASC");
                                                $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY]);
                                                $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                foreach ($levels as $index => $level) {
                                                    $level_no = $level['no'];
                                                    $stmt = $connect->prepare("SELECT role_write, role_edit, role_view, role_list, role_delete, role_comment 
                                                                               FROM nb_board_lev_manage 
                                                                               WHERE sitekey = :sitekey AND lev_no = :lev_no AND board_no = :board_no");
                                                    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':lev_no' => $level_no, ':board_no' => $no]);
                                                    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                                                    $role_checked = [
                                                        'list' => ($data['role_list'] ?? "") == "Y" ? "checked" : "",
                                                        'write' => ($data['role_write'] ?? "") == "Y" ? "checked" : "",
                                                        'view' => ($data['role_view'] ?? "") == "Y" ? "checked" : "",
                                                        'edit' => ($data['role_edit'] ?? "") == "Y" ? "checked" : "",
                                                        'delete' => ($data['role_delete'] ?? "") == "Y" ? "checked" : "",
                                                        'comment' => ($data['role_comment'] ?? "") == "Y" ? "checked" : "",
                                                    ];
                                            ?>
                                            <tr>
                                                <td><span><?=$level['lev_name']?><input type="hidden"
                                                            name="nb_auth_lev_no[]" value="<?=$level_no?>"></span></td>
                                                <?php foreach ($role_checked as $role => $checked): ?>
                                                <td class="no-check">
                                                    <div class="no-checkbox-form">
                                                        <label for="role_<?=$role?><?=$index?>">
                                                            <input type="checkbox" name="role_<?=$role?>[<?=$index?>]"
                                                                class="no-chk" id="role_<?=$role?><?=$index?>" value="Y"
                                                                <?=$checked?> />
                                                            <span><i class="bx bxs-check-square"></i></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <?php
                                                }
                                            } catch (Exception $e) {
                                                ClientFault::abortPage($e);
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="no-items-center center">
                                    <a href="./board.role.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <a href="javascript:void(0);" class="no-btn no-btn--big no-btn--main"
                                        onClick="doEditSave();">저장</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <!-- Footer -->
        <script type="text/javascript" src="./js/board.role.process.js?c=<?=$STATIC_ADMIN_JS_MODIFY_DATE?>"></script>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>
</body>

</html>