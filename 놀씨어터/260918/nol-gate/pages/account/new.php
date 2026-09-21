<?php
include_once "../../../inc/lib/base.class.php";
require_once dirname(__DIR__, 2) . "/Model/AccountModel.php";
$role->redirectIfCannotView();

try {
    $db = DB::getInstance();
} catch (Exception $e) {
    ClientFault::abortPage($e);
    exit;
}

include_once "../../inc/admin.title.php";
include_once "../../inc/admin.css.php";
include_once "../../inc/admin.js.php";
?>

</head>

<body data-page="account">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>

        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>

            <form id="frm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="save">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 등록</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 등록</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">신규 계정 등록</h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">권한</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php
                                            $superCount = AccountModel::countByRole(1);
                                            $superFull = $superCount >= (int) Role::getRoleLimit(1);
                                            foreach ($admin_roles as $id => $info):
                                                if ($id !== 1 && $id !== 2) {
                                                    continue;
                                                }
                                                $superLocked = $id === 1 && $superFull;
                                            ?>
                                            <label for="role<?= $id ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="role_id" id="role<?= $id ?>" value="<?= $id ?>"
                                                        <?= $id === 2 ? 'checked' : '' ?>
                                                        <?= $superLocked ? 'disabled' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text"><?= htmlspecialchars($id === 1 ? '최고 관리자' : '일반 관리자') ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="no-admin-acl__hint">최고 관리자를 고르면 메뉴 권한은 숨고 모든 메뉴를 쓸 수 있습니다. 최고는 고객 1명, 나인원랩스 1명만 둡니다.<?= !empty($superFull) ? ' (현재 2명)' : '' ?></p>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="uid">아이디</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="uid" id="uid" class="no-input--detail"
                                            placeholder="아이디를 입력하세요" required minlength="4" maxlength="20"
                                            pattern="^[a-zA-Z0-9_]+$"
                                            title="영문, 숫자, 언더바(_)만 허용하며 4~20자까지 가능합니다.">
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="upwd">비밀번호</label></h3>
                                    <div class="no-admin-content">
                                        <input type="password" name="upwd" id="upwd" class="no-input--detail"
                                            placeholder="비밀번호를 입력하세요" required minlength="8" maxlength="30"
                                            title="비밀번호 정책에 맞게 입력해주세요.">
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="upwd_confirm">비밀번호 확인</label></h3>
                                    <div class="no-admin-content">
                                        <input type="password" name="upwd_confirm" id="upwd_confirm" class="no-input--detail"
                                            placeholder="비밀번호를 다시 입력하세요" required minlength="8" maxlength="30"
                                            title="비밀번호와 동일하게 입력해주세요.">
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="uname">이름</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="uname" id="uname" class="no-input--detail"
                                            placeholder="이름을 입력하세요" required minlength="2" maxlength="20" pattern="^[가-힣a-zA-Z\s]+$"
                                            title="한글 또는 영문만 입력 가능합니다.">
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="email">이메일</label></h3>
                                    <div class="no-admin-content">
                                        <input type="email" name="email" id="email" class="no-input--detail"
                                            placeholder="예: admin@example.com" required>
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="phone">연락처</label></h3>
                                    <div class="no-admin-content">
                                        <input type="tel" name="phone" id="phone" class="no-input--detail"
                                            data-phone="true" placeholder="숫자만 입력 (예: 01012345678)"
                                            pattern="^01[016789]-?\d{3,4}-?\d{4}$"
                                            title="올바른 연락처를 입력해주세요. (예: 010-1234-5678)">
                                    </div>
                                </div>

                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="active_status">상태</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="activeY">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="active_status" id="activeY" value="Y" checked>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">활성</span>
                                            </label>
                                            <label for="activeN">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="active_status" id="activeN" value="N">
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">비활성</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <?php $aclGrants = []; include_once dirname(__DIR__, 2) . '/inc/admin.acl.php'; ?>

                                <div class="no-items-center center">
                                    <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="button" class="no-btn no-btn--big no-btn--main" id="submitBtn">등록</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </main>
        <?php include_once "../../inc/admin.footer.php"; ?>
    </div>

</body>

</html>
