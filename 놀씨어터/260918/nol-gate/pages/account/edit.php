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
$id = isset($_GET['no']) ? (int) $_GET['no'] : 0;
$account = null;

if ($id < 1) {
    $role->redirectWithError('수정할 계정을 선택하세요.', $NO_ADMIN_PAGES_BASE . '/account/index.php');
}

$stmt = $db->prepare("SELECT * FROM nb_admin WHERE no = :no");
$stmt->execute([':no' => $id]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    $role->redirectWithError('존재하지 않는 계정입니다.', $NO_ADMIN_PAGES_BASE . '/account/index.php');
}

$me = (int) ($_SESSION['no_adm_login_no'] ?? 0);
$isSelf = $me === (int) $account['no'];
$layerId = ((int) ($account['role_id'] ?? 3) === 1) ? 1 : 2;
$superCount = AccountModel::countByRole(1);
$superFull = $superCount >= (int) Role::getRoleLimit(1);
$lastSuper = $layerId === 1 && $superCount <= 1;
$aclStartHidden = $layerId === 1;



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
                <input type="hidden" name="mode" value="update">
                <input type="hidden" name="no" value="<?= $id ?>">

                <section class="no-content">
                    <div class="no-toolbar">
                        <div class="no-toolbar-container no-flex-stack">
                            <div class="no-page-indicator">
                                <h1 class="no-page-title"><?= $pageName ?> 변경</h1>
                                <div class="no-breadcrumb-container">
                                    <ul class="no-breadcrumb-list">
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?></span></li>
                                        <li class="no-breadcrumb-item"><span><?= $pageName ?> 변경</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-toolbar-container">
                        <div class="no-card">
                            <div class="no-card-header no-card-header--detail">
                                <h2 class="no-card-title">계정 변경</h2>
                            </div>

                            <div class="no-card-body no-admin-column no-admin-column--detail">

                                <!-- 권한 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title">권한</h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <?php
                                            foreach ($admin_roles as $rid => $info):
                                                if ($rid !== 1 && $rid !== 2) {
                                                    continue;
                                                }
                                                $superLocked = $rid === 1 && $superFull && $layerId !== 1;
                                                $generalLocked = $rid === 2 && $lastSuper;
                                            ?>
                                            <label for="role<?= $rid ?>">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="role_id" id="role<?= $rid ?>"
                                                        value="<?= $rid ?>"
                                                        <?= $layerId == $rid ? 'checked' : '' ?>
                                                        <?= ($superLocked || $generalLocked) ? 'disabled' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span
                                                    class="no-radio-text"><?= htmlspecialchars($rid === 1 ? '최고 관리자' : '일반 관리자') ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ($lastSuper): ?>
                                        <input type="hidden" name="role_id" value="1">
                                        <p class="no-admin-acl__hint">마지막 최고 관리자는 일반으로 내릴 수 없습니다. 다른 최고 관리자를 만든 뒤 바꾸세요. 최고는 메뉴 권한 설정이 없습니다.</p>
                                        <?php else: ?>
                                        <p class="no-admin-acl__hint">최고↔일반을 바꿀 수 있습니다. 최고로 두면 메뉴 권한은 숨고 모든 메뉴를 씁니다. 일반으로 내리면 메뉴 권한을 다시 고릅니다. 최고는 최대 2명입니다.<?= $isSelf ? ' 본인을 일반으로 바꾸면 계정 메뉴는 더 이상 쓸 수 없습니다.' : '' ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>



                                <!-- 아이디 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="uid">아이디</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="uid" id="uid" class="no-input--detail"
                                            value="<?= htmlspecialchars($account['uid'] ?? '') ?>" required
                                            minlength="4" maxlength="20" pattern="^[a-zA-Z0-9_]+$"
                                            title="영문, 숫자, 언더바(_)만 허용하며 4~20자까지 가능합니다.">
                                    </div>
                                </div>

                                <!-- 비밀번호 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="upwd">비밀번호</label></h3>
                                    <div class="no-admin-content">
                                        <input type="password" name="upwd" id="upwd" class="no-input--detail"
                                            placeholder="수정할 경우 새 비밀번호 입력" <?= $id ? '' : 'required' ?> minlength="8"
                                            maxlength="30" title="비밀번호 정책에 맞게 입력해주세요.">
                                    </div>
                                </div>

                                <!-- 비밀번호 확인 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="upwd_confirm">비밀번호 확인</label></h3>
                                    <div class="no-admin-content">
                                        <input type="password" name="upwd_confirm" id="upwd_confirm"
                                            class="no-input--detail" placeholder="비밀번호를 다시 입력해주세요"
                                            <?= $id ? '' : 'required' ?> minlength="8" maxlength="30"
                                            title="비밀번호를 동일하게 입력해주세요.">
                                    </div>
                                </div>


                                <!-- 이름 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="uname">이름</label></h3>
                                    <div class="no-admin-content">
                                        <input type="text" name="uname" id="uname" class="no-input--detail"
                                            value="<?= htmlspecialchars($account['uname'] ?? '') ?>"
                                            placeholder="이름을 입력하세요" required minlength="2" maxlength="20" pattern="^[가-힣a-zA-Z\s]+$"
                                            title="한글 또는 영문만 입력 가능합니다.">
                                    </div>
                                </div>

                                <!-- 이메일 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="email">이메일</label></h3>
                                    <div class="no-admin-content">
                                        <input type="email" name="email" id="email" class="no-input--detail"
                                            value="<?= htmlspecialchars($account['email'] ?? '') ?>"
                                            placeholder="예: admin@example.com" required>
                                    </div>
                                </div>

                                <!-- 휴대폰 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="phone">휴대폰</label></h3>
                                    <div class="no-admin-content">
                                        <input type="tel" name="phone" id="phone" class="no-input--detail"
                                            value="<?= htmlspecialchars($account['phone'] ?? '') ?>" data-phone="true"
                                            placeholder="숫자만 입력 (예: 01012345678)" pattern="^01[016789]-?\d{3,4}-?\d{4}$"
                                            title="올바른 휴대폰 번호를 입력해주세요. (예: 010-1234-5678)">
                                    </div>
                                </div>


                                <!-- 상태 -->
                                <div class="no-admin-block">
                                    <h3 class="no-admin-title"><label for="active_status">상태</label></h3>
                                    <div class="no-admin-content">
                                        <div class="no-radio-form no-list">
                                            <label for="activeY">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="active_status" id="activeY" value="Y"
                                                        <?= ($account['active_status'] ?? 'Y') === 'Y' ? 'checked' : '' ?>
                                                        <?= $isSelf ? 'disabled' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">활성</span>
                                            </label>
                                            <label for="activeN">
                                                <div class="no-radio-box">
                                                    <input type="radio" name="active_status" id="activeN" value="N"
                                                        <?= ($account['active_status'] ?? '') === 'N' ? 'checked' : '' ?>
                                                        <?= $isSelf ? 'disabled' : '' ?>>
                                                    <span><i class="bx bx-radio-circle-marked"></i></span>
                                                </div>
                                                <span class="no-radio-text">비활성</span>
                                            </label>
                                        </div>
                                        <?php if ($isSelf): ?>
                                        <input type="hidden" name="active_status" value="<?= htmlspecialchars((string) ($account['active_status'] ?? 'Y'), ENT_QUOTES, 'UTF-8') ?>">
                                        <p class="no-admin-acl__hint">본인 계정은 비활성화할 수 없습니다.</p>
                                        <?php endif; ?>
                                        <?php if (trim((string) ($account['idle_locked_at'] ?? '')) !== ''): ?>
                                        <p class="no-admin-acl__hint">장기 미접속으로 잠긴 계정입니다. 마지막 로그인: <?= htmlspecialchars((string) ($account['last_login_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (!$isSelf): ?>
                                        <button type="button" class="no-btn no-btn--sm no-btn--main" id="unlockIdleBtn" data-id="<?= (int) $account['no'] ?>">미접속 잠금 해제</button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php
                                $aclGrants = $role->acl()->grantsFor((int) ($account['no'] ?? 0));
                                include dirname(__DIR__, 2) . '/inc/admin.acl.php';
                                ?>

                                <div class="no-items-center center">
                                    <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                    <button type="button" class="no-btn no-btn--big no-btn--main"
                                        id="editBtn">수정</button>
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
