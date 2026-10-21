<?php
// Isolated UI proposal. Do not attach account creation/reset handlers to this page.
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit;
}
include_once "../../../inc/lib/base.class.php";
$role->redirectIfCannotView();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
$editing = ($_GET['view'] ?? '') === 'edit';
$issuedPreview = ($_GET['stage'] ?? '') === 'issued';
$temporary = $editing && ($_GET['password'] ?? 'temp') !== 'changed';
$passwordPlaceholder = !$editing ? '계정 등록 시 임시 비밀번호가 자동 발급됩니다.' : ($temporary ? '임시 비밀번호가 발급된 상태입니다.' : '비밀번호는 직접 수정할 수 없습니다.');
$confirmationPlaceholder = !$editing || $temporary ? '사용자가 로그인 후 직접 변경해야 합니다.' : '변경이 필요하면 임시 비밀번호를 재발급해 주세요.';
$previewTitle = $issuedPreview ? ($editing ? '임시 비밀번호 재발급 완료' : '계정 등록 완료') : ($editing ? '계정 수정' : '신규 계정 등록');
$adminAccountPreviewStyles = true;
include_once "../../inc/admin.head.php";
?>
<body data-page="temp-account-preview">
    <div class="no-wrap">
        <?php include_once "../../inc/admin.header.php"; ?>
        <main class="no-app no-container">
            <?php include_once "../../inc/admin.drawer.php"; ?>
            <section class="no-content">
                <div class="no-toolbar">
                    <div class="no-toolbar-container no-flex-stack">
                        <div class="no-page-indicator">
                            <h1 class="no-page-title"><?= $previewTitle ?></h1>
                            <div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span><?= $previewTitle ?></span></li></ul></div>
                        </div>
                        <div class="no-items-center">
                            <a class="no-btn no-btn--big <?= $editing ? 'no-btn--normal' : 'no-btn--main' ?>" href="?view=new">등록 화면</a>
                            <a class="no-btn no-btn--big no-btn--normal" href="./temp-account-list-preview.php">계정 목록 · 전달 UI</a>
                            <a class="no-btn no-btn--big <?= $editing ? 'no-btn--main' : 'no-btn--normal' ?>" href="?view=edit">수정 화면</a>
                        </div>
                    </div>
                </div>
                <div class="no-toolbar-container" id="preview-account-fields"<?= $issuedPreview ? ' hidden' : '' ?>>
                    <div class="no-card">
                        <div class="no-card-header no-card-header--detail"><h2 class="no-card-title"><?= $previewTitle ?></h2></div>
                        <div class="no-card-body no-admin-column no-admin-column--detail">
                            <p class="no-admin-acl__hint">디자인 미리보기입니다. 입력하거나 버튼을 눌러도 실제 계정·비밀번호·세션은 변경되지 않습니다.</p>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title">권한</h3>
                                <div class="no-admin-content"><div class="no-radio-form no-list">
                                    <?php foreach (['super' => '최고 관리자', 'general' => '일반 관리자'] as $code => $label): ?>
                                    <label for="preview-role-<?= $code ?>"><div class="no-radio-box"><input type="radio" name="preview-role" id="preview-role-<?= $code ?>" value="<?= $code ?>" <?= $code === 'general' ? 'checked' : '' ?>><span><i class="bx bx-radio-circle-marked"></i></span></div><span class="no-radio-text"><?= $label ?></span></label>
                                    <?php endforeach; ?>
                                </div></div>
                            </div>
                            <div class="no-admin-block"><h3 class="no-admin-title"><label for="preview-uid">아이디</label></h3><div class="no-admin-content"><input type="text" id="preview-uid" class="no-input--detail" value="<?= $editing ? 'preview_admin' : '' ?>" placeholder="아이디를 입력하세요" autocomplete="off" maxlength="20"></div></div>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title"><label for="preview-password">비밀번호</label></h3>
                                <div class="no-admin-content">
                                    <input type="password" id="preview-password" class="no-input--detail" disabled placeholder="<?= htmlspecialchars($passwordPlaceholder, ENT_QUOTES, 'UTF-8') ?>" aria-describedby="preview-password-help">
                                    <p class="no-admin-acl__hint" id="preview-password-help"><?= !$editing || $temporary ? '사용자는 임시 비밀번호로 로그인한 뒤 이메일 인증을 완료하고 직접 새 비밀번호로 변경해야 합니다.' : '사용자가 본인 비밀번호로 변경한 상태의 예시입니다. 기본정보만 수정하면 비밀번호는 유지됩니다.' ?></p>
                                    <?php if ($editing): ?>
                                    <button type="button" class="no-btn no-btn--sm no-btn--delete-outline" id="preview-reset-password">임시 비밀번호 재발급</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="no-admin-block">
                                <h3 class="no-admin-title"><label for="preview-password-confirm">비밀번호 확인</label></h3>
                                <div class="no-admin-content">
                                    <input type="password" id="preview-password-confirm" class="no-input--detail" disabled placeholder="<?= htmlspecialchars($confirmationPlaceholder, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                            <div class="no-admin-block"><h3 class="no-admin-title"><label for="preview-name">이름</label></h3><div class="no-admin-content"><input type="text" id="preview-name" class="no-input--detail" value="<?= $editing ? '예시 관리자' : '' ?>" placeholder="이름을 입력하세요" autocomplete="off"></div></div>
                            <div class="no-admin-block"><h3 class="no-admin-title"><label for="preview-email">인증 이메일</label></h3><div class="no-admin-content"><input type="email" id="preview-email" class="no-input--detail" value="<?= $editing ? 'admin@example.com' : '' ?>" placeholder="admin@example.com" autocomplete="off"><p class="no-admin-acl__hint">로그인 시 MFA 인증에 사용할 관리자 이메일입니다.</p></div></div>
                            <div class="no-admin-block"><h3 class="no-admin-title"><label for="preview-phone">연락처</label></h3><div class="no-admin-content"><input type="tel" id="preview-phone" class="no-input--detail" placeholder="연락처를 입력하세요" autocomplete="off"></div></div>
                            <div class="no-admin-block"><h3 class="no-admin-title">상태</h3><div class="no-admin-content"><div class="no-radio-form no-list">
                                <?php foreach (['Y' => '활성', 'N' => '비활성'] as $code => $label): ?>
                                <label for="preview-active-<?= $code ?>"><div class="no-radio-box"><input type="radio" name="preview-active" id="preview-active-<?= $code ?>" value="<?= $code ?>" <?= $code === 'Y' ? 'checked' : '' ?>><span><i class="bx bx-radio-circle-marked"></i></span></div><span class="no-radio-text"><?= $label ?></span></label>
                                <?php endforeach; ?>
                            </div></div></div>
                            <?php $aclGrants = []; include dirname(__DIR__, 2) . '/inc/admin.acl.php'; ?>
                            <div class="no-items-center center">
                                <a href="./index.php" class="no-btn no-btn--big no-btn--normal">목록</a>
                                <?php if ($editing): ?>
                                <button type="button" class="no-btn no-btn--big no-btn--main" disabled title="디자인 미리보기: 정보 저장 기능 없음">수정</button>
                                <?php else: ?>
                                <button type="button" class="no-btn no-btn--big no-btn--main" id="preview-create-account">등록</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="no-toolbar-container" id="preview-confirm" aria-labelledby="preview-confirm-title" hidden>
                    <div class="no-card">
                        <div class="no-card-header no-card-header--detail"><h2 class="no-card-title" id="preview-confirm-title" tabindex="-1">임시 비밀번호를 재발급하시겠습니까?</h2></div>
                        <div class="no-card-body">
                            <p class="no-admin-acl__hint">실제 기능 연동 시에는 기존 비밀번호를 사용할 수 없게 되고 해당 계정의 로그인 세션도 종료됩니다.<br>사용자는 임시 비밀번호로 로그인한 뒤 이메일 인증과 비밀번호 변경을 진행해야 합니다.</p>
                            <div class="no-items-center center"><button type="button" class="no-btn no-btn--normal" id="preview-cancel">취소</button><button type="button" class="no-btn no-btn--delete-outline" id="preview-confirm-reset">재발급 화면 보기</button></div>
                        </div>
                    </div>
                </section>
                <section class="no-toolbar-container" id="preview-issued" aria-labelledby="preview-issued-title"<?= $issuedPreview ? '' : ' hidden' ?>>
                    <div class="no-card">
                        <div class="no-card-header no-card-header--detail"><h2 class="no-card-title" id="preview-issued-title" tabindex="-1"><?= $editing ? '임시 비밀번호 재발급 완료' : '계정 등록 완료' ?></h2></div>
                        <div class="no-card-body no-admin-column no-admin-column--detail">
                            <p class="no-admin-acl__hint">화면 예시입니다. 실제 계정 등록이나 비밀번호 발급은 이루어지지 않았습니다.</p>
                            <div class="no-admin-block"><h3 class="no-admin-title">대상 계정</h3><div class="no-admin-content"><strong id="preview-issued-uid">preview_admin</strong></div></div>
                            <div class="no-admin-block"><h3 class="no-admin-title"><label for="preview-issued-password">임시 비밀번호</label></h3><div class="no-admin-content"><input type="text" id="preview-issued-password" class="no-input--detail" readonly value="DEMO-ONLY!482" aria-describedby="preview-issued-hint"><p class="no-admin-acl__hint" id="preview-issued-hint">실제 사용이 불가능한 고정 예시입니다. 실제 기능 연동 시 임시 비밀번호는 이 화면에서 한 번만 표시합니다.</p><button type="button" class="no-btn no-btn--sm" disabled title="복사 기능 미연동">복사</button></div></div>
                            <div class="no-admin-block"><h3 class="no-admin-title">사용자 안내</h3><div class="no-admin-content"><p class="no-admin-acl__hint">임시 비밀번호를 사용자에게 안전하게 전달해 주세요.<br>로그인 → 이메일 인증 → 비밀번호 변경 → 관리자 이용 순서로 진행합니다.</p></div></div>
                            <div class="no-items-center center"><button type="button" class="no-btn no-btn--main" id="preview-close-issued">확인</button><a href="./password-preview.php" class="no-btn no-btn--normal">사용자 변경 화면 보기</a></div>
                        </div>
                    </div>
                </section>
            </section>
        </main>
    </div>
    <script type="module" src="<?= htmlspecialchars($NO_ADMIN_RESOURCE_BASE, ENT_QUOTES, 'UTF-8') ?>/js/temp-account-preview.js?v=20261020-visible-fix"></script>
    <?php include_once "../../inc/admin.footer.php"; ?>
